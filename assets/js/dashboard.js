/**
 * Dashboard JavaScript - Panel de Control de Alaska
 * Maneja toda la funcionalidad del dashboard incluyendo recordatorios, 
 * estado de salud de mascotas y artículos del blog
 */

class Dashboard {
    constructor() {
        this.currentUser = null;
        this.pets = [];
        this.reminders = [];
        this.appointments = [];
        this.blogArticles = [];
        this.recentActivity = [];
        
        this.init();
    }

    /**
     * Inicializa el dashboard
     */
    init() {
        console.log('Inicializando dashboard...');
        this.loadUserData();
        this.setupEventListeners();
        this.loadDashboardData();
        this.updateStats();
    }

    /**
     * Configura los event listeners
     */
    setupEventListeners() {
        // Botones de agregar
        document.getElementById('btnAddReminder')?.addEventListener('click', () => this.openReminderModal());
        document.getElementById('btnAddPet')?.addEventListener('click', () => this.openPetModal());
        
        // Cerrar sesión
        document.getElementById('btnCerrarSesion')?.addEventListener('click', () => this.logout());
        
        // Modales
        document.getElementById('closeReminderModal')?.addEventListener('click', () => this.closeReminderModal());
        document.getElementById('closePetModal')?.addEventListener('click', () => this.closePetModal());
        document.getElementById('cancelReminder')?.addEventListener('click', () => this.closeReminderModal());
        document.getElementById('cancelPet')?.addEventListener('click', () => this.closePetModal());
        
        // Formularios
        document.getElementById('reminderForm')?.addEventListener('submit', (e) => this.handleReminderSubmit(e));
        document.getElementById('petForm')?.addEventListener('submit', (e) => this.handlePetSubmit(e));
        
        // Cerrar modales al hacer clic fuera
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeAllModals();
            }
        });

        // Escuchar creación de citas desde otras vistas (mismo tab)
        window.addEventListener('appointment:created', (e) => {
            try {
                const appt = e.detail || {};
                // Normalizaciones mínimas
                if (appt && (!appt.status && appt.estado)) {
                    appt.status = appt.estado;
                }
                if (appt && (!appt.petName && appt.nombre_mascota)) {
                    appt.petName = appt.nombre_mascota;
                }
                // Si no trae estado, asumir "programada" para reflejar el alta inmediata
                if (appt && !appt.status) {
                    appt.status = 'programada';
                }
                this.appointments.push(appt);
                this.updateStats();
            } catch (err) {
                console.warn('No se pudo procesar appointment:created:', err);
            }
        });

        // Escuchar señales entre pestañas/ventanas para recargar citas y actualizar contador
        window.addEventListener('storage', (e) => {
            if (e.key === 'alaska_appointments_updated' && e.newValue) {
                // Volver a cargar citas y refrescar estadísticas
                this.loadAppointments().then(() => this.updateStats()).catch(() => {});
            }
        });
    }

    /**
     * Carga los datos del usuario desde localStorage
     */
    loadUserData() {
        // Intentar obtener desde storage del flujo de autenticación del sitio
        const storedAuth = localStorage.getItem('alaska_user');
        const legacy = localStorage.getItem('currentUser');
        const userData = storedAuth || legacy;
        if (userData) {
            try { this.currentUser = JSON.parse(userData); } catch { this.currentUser = null; }
        } else {
            this.currentUser = null; // Con sesión PHP basta; no forzar redirección
        }
        this.updateUserDisplay();
    }

    /**
     * Actualiza la visualización del usuario
     */
    updateUserDisplay() {
        const userNameElement = document.getElementById('userName');
        if (!userNameElement) return;
        const currentText = (userNameElement.textContent || '').trim();
        // No sobrescribir si PHP ya imprimió el nombre de sesión
        if (currentText && currentText !== 'Usuario') return;
        if (this.currentUser) {
            const name = this.currentUser.name || this.currentUser.username || this.currentUser.email || 'Usuario';
            userNameElement.textContent = name;
        }
    }

    /**
     * Carga todos los datos del dashboard
     */
    async loadDashboardData() {
        try {
            await Promise.all([
                this.loadPets(),
                this.loadReminders(),
                this.loadAppointments(),
                this.loadBlogArticles(),
                this.loadRecentActivity()
            ]);
            
            this.renderDashboard();
            // Asegurar que las estadísticas reflejen los datos cargados inicialmente
            this.updateStats();
        } catch (error) {
            console.error('Error cargando datos del dashboard:', error);
            this.showError('Error al cargar los datos del dashboard');
        }
    }

    /**
     * Carga las mascotas del usuario
     */
    async loadPets() {
        console.log('Cargando mascotas desde API...');
        try {
            const response = await fetch('api/mascotas.php');
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            const pets = await response.json();
            this.pets = Array.isArray(pets) ? pets : [];
            console.log('Mascotas cargadas:', this.pets);
        } catch (error) {
            console.error('No se pudieron cargar las mascotas desde la API, usando lista vacía.', error);
            this.pets = [];
        }
    }

    /**
     * Carga los recordatorios del usuario
     */
    async loadReminders() {
        console.log('Cargando recordatorios...');
        // Datos de ejemplo para debug
        this.reminders = [
            {
                id: 1,
                title: 'Vacuna anual de Luna',
                date: '2024-03-15T10:00:00',
                type: 'vacuna',
                petId: 1,
                petName: 'Luna',
                notes: 'Recordatorio para vacuna anual',
                urgent: false,
                completed: false
            },
            {
                id: 2,
                title: 'Revisión dental de Mittens',
                date: '2024-03-20T14:30:00',
                type: 'cita',
                petId: 2,
                petName: 'Mittens',
                notes: 'Revisión dental y limpieza',
                urgent: false,
                completed: false
            },
            {
                id: 3,
                title: 'Medicamento para Max',
                date: '2024-03-10T08:00:00',
                type: 'medicamento',
                petId: 3,
                petName: 'Max',
                notes: 'Administrar medicamento para la artritis',
                urgent: true,
                completed: false
            }
        ];
        console.log('Recordatorios cargados:', this.reminders);
    }

    /**
     * Carga las citas del usuario
     */
    async loadAppointments() {
        try {
            // Desde dashboard (public/dashboard.php) la ruta relativa a la API es 'api/citas.php'
            const response = await fetch('api/citas.php');
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            const result = await response.json();

            // Estructura esperada desde PHP: { exito: boolean, datos: Array }
            if (result && result.exito && Array.isArray(result.datos)) {
                // Normalizar campos a la estructura interna del dashboard
                this.appointments = result.datos.map((c) => ({
                    id: c.id,
                    petId: c.pet_id || c.id_mascota || null,
                    petName: c.nombre_mascota || c.petName || '',
                    date: c.fecha_cita || c.date || '',
                    reason: c.motivo || c.reason || '',
                    status: c.estado || c.status || 'programada'
                }));
            } else {
                // Si la API no devuelve lo esperado, dejar lista vacía para no contar mal
                this.appointments = [];
            }
        } catch (error) {
            console.error('Error cargando citas desde la API:', error);
            this.appointments = [];
        }
    }

    /**
     * Carga los artículos del blog
     */
    async loadBlogArticles() {
        try {
            // Cargar desde archivo JSON
            const response = await fetch('../data/blog-articles.json');
            if (response.ok) {
                this.blogArticles = await response.json();
            } else {
                // Fallback a datos de ejemplo si no se puede cargar el archivo
                this.blogArticles = [
                    {
                        id: 1,
                        title: 'Cómo cuidar la salud dental de tu mascota',
                        excerpt: 'La salud dental es fundamental para el bienestar general de tu mascota. Descubre los mejores consejos para mantener sus dientes limpios y sanos.',
                        date: '2024-03-01',
                        category: 'Salud',
                        author: 'Dr. María González'
                    },
                    {
                        id: 2,
                        title: 'Alimentación adecuada para perros senior',
                        excerpt: 'A medida que tu perro envejece, sus necesidades nutricionales cambian. Te contamos cómo adaptar su dieta para mantenerlo saludable.',
                        date: '2024-02-28',
                        category: 'Nutrición',
                        author: 'Lic. Carlos Rodríguez'
                    },
                    {
                        id: 3,
                        title: 'Ejercicios mentales para gatos',
                        excerpt: 'Los gatos también necesitan estimulación mental. Descubre juegos y actividades que mantendrán a tu felino activo y feliz.',
                        date: '2024-02-25',
                        category: 'Bienestar',
                        author: 'Dra. Ana Martínez'
                    }
                ];
            }
        } catch (error) {
            console.error('Error cargando artículos del blog:', error);
            this.blogArticles = [];
        }
    }

    /**
     * Carga la actividad reciente
     */
    async loadRecentActivity() {
        try {
            // Simular carga desde API
            this.recentActivity = [
                {
                    id: 1,
                    type: 'appointment',
                    text: 'Cita programada para Luna el 15 de marzo',
                    time: 'Hace 2 horas',
                    icon: 'fas fa-calendar-check'
                },
                {
                    id: 2,
                    type: 'reminder',
                    text: 'Recordatorio: Vacuna de Mittens mañana',
                    time: 'Hace 4 horas',
                    icon: 'fas fa-bell'
                },
                {
                    id: 3,
                    type: 'pet',
                    text: 'Nueva mascota agregada: Luna',
                    time: 'Hace 1 día',
                    icon: 'fas fa-paw'
                },
                {
                    id: 4,
                    type: 'blog',
                    text: 'Nuevo artículo publicado: Cuidado dental',
                    time: 'Hace 2 días',
                    icon: 'fas fa-newspaper'
                }
            ];
        } catch (error) {
            console.error('Error cargando actividad reciente:', error);
            this.recentActivity = [];
        }
    }

    /**
     * Renderiza todo el dashboard
     */
    renderDashboard() {
        this.renderReminders();
        this.renderPetsHealth();
        this.renderBlogArticles();
        this.renderRecentActivity();
    }

    /**
     * Renderiza los recordatorios
     */
    renderReminders() {
        console.log('Renderizando recordatorios:', this.reminders);
        const container = document.getElementById('remindersContainer');
        if (!container) return;

        if (this.reminders.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-bell"></i>
                    <h3>No hay recordatorios</h3>
                    <p>Agrega tu primer recordatorio para empezar</p>
                </div>
            `;
            return;
        }

        // Ordenar recordatorios por fecha
        const sortedReminders = this.reminders.sort((a, b) => new Date(a.date) - new Date(b.date));
        
        container.innerHTML = sortedReminders.map(reminder => {
            const date = new Date(reminder.date);
            const isUrgent = date < new Date(Date.now() + 24 * 60 * 60 * 1000); // Menos de 24 horas
            const isOverdue = date < new Date();
            
            return `
                <div class="reminder-item ${isUrgent || isOverdue ? 'urgent' : ''}">
                    <div class="reminder-header">
                        <h4 class="reminder-title">${reminder.title}</h4>
                        <span class="reminder-date">${this.formatDate(date)}</span>
                    </div>
                    <div class="reminder-type">${this.getReminderTypeLabel(reminder.type)}</div>
                    <div class="reminder-pet">Mascota: ${reminder.petName}</div>
                    ${reminder.notes ? `<div class="reminder-notes">${reminder.notes}</div>` : ''}
                </div>
            `;
        }).join('');
    }

    /**
     * Renderiza el estado de salud de las mascotas
     */
    renderPetsHealth() {
        const container = document.getElementById('petsHealthContainer');
        if (!container) return;

        if (this.pets.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-paw"></i>
                    <h3>No hay mascotas registradas</h3>
                    <p>Agrega tu primera mascota para empezar</p>
                </div>
            `;
            return;
        }

        container.innerHTML = this.pets.map(pet => {
            const healthStatusClass = this.getHealthStatusClass(pet.healthStatus);
            const healthStatusLabel = this.getHealthStatusLabel(pet.healthStatus);
            
            return `
                <div class="pet-health-card">
                    <div class="pet-header">
                        <h3 class="pet-name">${pet.name}</h3>
                        <span class="pet-species">${this.capitalizeFirst(pet.species)}</span>
                    </div>
                    <div class="pet-info">
                        <div class="pet-info-item">
                            <div class="pet-info-label">Edad</div>
                            <div class="pet-info-value">${pet.age} años</div>
                        </div>
                        <div class="pet-info-item">
                            <div class="pet-info-label">Peso</div>
                            <div class="pet-info-value">${pet.weight} kg</div>
                        </div>
                        <div class="pet-info-item">
                            <div class="pet-info-label">Raza</div>
                            <div class="pet-info-value">${pet.breed}</div>
                        </div>
                        <div class="pet-info-item">
                            <div class="pet-info-label">Última revisión</div>
                            <div class="pet-info-value">${this.formatDate(new Date(pet.lastCheckup))}</div>
                        </div>
                    </div>
                    <div class="pet-health-status ${healthStatusClass}">
                        <i class="fas fa-heartbeat"></i>
                        ${healthStatusLabel}
                    </div>
                    <div class="pet-actions">
                        <button class="btn-inline btn-edit-pet" data-pet-id="${pet.id}">
                            <i class="fas fa-pen-to-square"></i> Editar
                        </button>
                        <button class="btn-inline btn-delete-pet" data-pet-id="${pet.id}">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        // Agregar listeners para editar mascota
        container.querySelectorAll('.btn-edit-pet').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = parseInt(e.currentTarget.getAttribute('data-pet-id'));
                this.startEditPet(id);
            });
        });

        // Agregar listeners para eliminar mascota
        container.querySelectorAll('.btn-delete-pet').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = parseInt(e.currentTarget.getAttribute('data-pet-id'));
                this.deletePet(id);
            });
        });
    }

    /**
     * Renderiza los artículos del blog
     */
    renderBlogArticles() {
        const container = document.getElementById('blogArticlesContainer');
        if (!container) return;

        if (this.blogArticles.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-newspaper"></i>
                    <h3>No hay artículos disponibles</h3>
                    <p>Pronto tendremos contenido interesante para ti</p>
                </div>
            `;
            return;
        }

        container.innerHTML = this.blogArticles.slice(0, 3).map(article => {
            return `
                <div class="blog-article" onclick="window.location.href='blog.html#article-${article.id}'">
                    <h4 class="blog-article-title">${article.title}</h4>
                    <p class="blog-article-excerpt">${article.excerpt}</p>
                    <div class="blog-article-meta">
                        <span class="blog-article-date">
                            <i class="fas fa-calendar"></i>
                            ${this.formatDate(new Date(article.date))}
                        </span>
                        <span class="blog-article-category">${article.category}</span>
                    </div>
                </div>
            `;
        }).join('');
    }

    /**
     * Renderiza la actividad reciente
     */
    renderRecentActivity() {
        const container = document.getElementById('recentActivityContainer');
        if (!container) return;

        if (this.recentActivity.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-clock"></i>
                    <h3>No hay actividad reciente</h3>
                    <p>Tu actividad aparecerá aquí</p>
                </div>
            `;
            return;
        }

        container.innerHTML = this.recentActivity.slice(0, 5).map(activity => {
            return `
                <div class="activity-item">
                    <div class="activity-icon ${activity.type}">
                        <i class="${activity.icon}"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">${activity.text}</div>
                        <div class="activity-time">${activity.time}</div>
                    </div>
                </div>
            `;
        }).join('');
    }

    /**
     * Actualiza las estadísticas del dashboard
     */
    updateStats() {
        document.getElementById('totalPets').textContent = this.pets.length;
        document.getElementById('upcomingAppointments').textContent = this.appointments.filter(a => a.status === 'programada').length;
        document.getElementById('totalReminders').textContent = this.reminders.length;
    }

    /**
     * Abre el modal de recordatorio
     */
    openReminderModal() {
        const modal = document.getElementById('reminderModal');
        if (modal) {
            modal.classList.add('active');
            this.populatePetSelect('reminderPet');
        }
    }

    /**
     * Cierra el modal de recordatorio
     */
    closeReminderModal() {
        const modal = document.getElementById('reminderModal');
        if (modal) {
            modal.classList.remove('active');
            document.getElementById('reminderForm').reset();
        }
    }

    /**
     * Abre el modal de mascota
     */
    openPetModal(pet = null) {
        const modal = document.getElementById('petModal');
        if (modal) {
            modal.classList.add('active');
            const titleEl = modal.querySelector('.modal-header h3');
            const form = document.getElementById('petForm');
            if (!form) return;
            // Campos
            const idInput = document.getElementById('petId');
            const nameInput = document.getElementById('petName');
            const speciesSelect = document.getElementById('petSpecies');
            const breedInput = document.getElementById('petBreed');
            const ageInput = document.getElementById('petAge');
            const weightInput = document.getElementById('petWeight');
            const healthSelect = document.getElementById('petHealthStatus');
            const lastCheckupInput = document.getElementById('petLastCheckup');

            if (pet) {
                if (titleEl) titleEl.textContent = 'Editar Mascota';
                if (idInput) idInput.value = pet.id;
                if (nameInput) nameInput.value = pet.name || '';
                if (speciesSelect) speciesSelect.value = pet.species || '';
                if (breedInput) breedInput.value = pet.breed || '';
                if (ageInput) ageInput.value = pet.age ?? '';
                if (weightInput) weightInput.value = pet.weight ?? '';
                if (healthSelect) healthSelect.value = pet.healthStatus || 'healthy';
                if (lastCheckupInput) lastCheckupInput.value = (pet.lastCheckup || '').slice(0, 10);
            } else {
                if (titleEl) titleEl.textContent = 'Agregar Mascota';
                form.reset();
                if (idInput) idInput.value = '';
                if (healthSelect) healthSelect.value = 'healthy';
                if (lastCheckupInput) lastCheckupInput.value = new Date().toISOString().split('T')[0];
            }
        }
    }

    /**
     * Cierra el modal de mascota
     */
    closePetModal() {
        const modal = document.getElementById('petModal');
        if (modal) {
            modal.classList.remove('active');
            document.getElementById('petForm').reset();
            const titleEl = modal.querySelector('.modal-header h3');
            if (titleEl) titleEl.textContent = 'Agregar Mascota';
        }
    }

    /**
     * Inicia edición de una mascota
     */
    startEditPet(id) {
        const pet = this.pets.find(p => p.id === id);
        if (!pet) return;
        this.openPetModal(pet);
    }

    /**
     * Elimina una mascota tras confirmación
     */
    async deletePet(id) {
        const pet = this.pets.find(p => p.id === id);
        const name = pet ? pet.name : '';
        const ok = confirm(`¿Eliminar la mascota ${name ? '"' + name + '" ' : ''}definitivamente?`);
        if (!ok) return;

        try {
            const response = await fetch(`api/mascotas.php?id=${encodeURIComponent(id)}`, {
                method: 'DELETE'
            });
            const result = await response.json();
            if (result && result.success) {
                this.pets = this.pets.filter(p => p.id !== id);
                this.renderPetsHealth();
                this.updateStats();
                this.showSuccess('Mascota eliminada exitosamente');
            } else {
                this.showError('Error al eliminar la mascota');
            }
        } catch (err) {
            console.error('Error eliminando mascota:', err);
            this.showError('Error al eliminar la mascota');
        }
    }

    /**
     * Cierra todos los modales
     */
    closeAllModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('active');
        });
    }

    /**
     * Puebla el select de mascotas
     */
    populatePetSelect(selectId) {
        const select = document.getElementById(selectId);
        if (!select) return;

        select.innerHTML = '<option value="">Seleccionar mascota</option>';
        this.pets.forEach(pet => {
            const option = document.createElement('option');
            option.value = pet.id;
            option.textContent = pet.name;
            select.appendChild(option);
        });
    }

    /**
     * Maneja el envío del formulario de recordatorio
     */
    async handleReminderSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const reminder = {
            title: formData.get('title'),
            date: formData.get('date'),
            type: formData.get('type'),
            petId: parseInt(formData.get('petId')),
            petName: this.pets.find(p => p.id === parseInt(formData.get('petId')))?.name || '',
            notes: formData.get('notes'),
            urgent: false
        };

        try {
            const response = await fetch('api/recordatorios.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(reminder)
            });

            const result = await response.json();
            
            if (result.success) {
                this.reminders.push(result.recordatorio);
                this.renderReminders();
                this.updateStats();
                this.closeReminderModal();
                this.showSuccess('Recordatorio agregado exitosamente');
            } else {
                this.showError('Error al agregar el recordatorio');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showError('Error al agregar el recordatorio');
        }
    }

    /**
     * Maneja el envío del formulario de mascota
     */
    async handlePetSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const pet = {
            name: formData.get('name'),
            species: formData.get('species'),
            breed: formData.get('breed'),
            age: parseFloat(formData.get('age')) || 0,
            weight: parseFloat(formData.get('weight')) || 0,
            healthStatus: formData.get('healthStatus') || 'healthy',
            lastCheckup: (formData.get('lastCheckup') || new Date().toISOString().split('T')[0])
        };
        const petId = parseInt(formData.get('id')) || null;

        try {
            let result;
            if (petId) {
                // Actualizar
                const response = await fetch('api/mascotas.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: petId, ...pet })
                });
                result = await response.json();
                if (result && result.success) {
                    // Actualizar en memoria
                    const idx = this.pets.findIndex(p => p.id === petId);
                    if (idx !== -1) {
                        this.pets[idx] = { id: petId, ...this.pets[idx], ...pet };
                    }
                    this.renderPetsHealth();
                    this.updateStats();
                    this.closePetModal();
                    this.showSuccess('Mascota actualizada exitosamente');
                } else {
                    this.showError('Error al actualizar la mascota');
                }
            } else {
                // Crear
                const response = await fetch('api/mascotas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(pet)
                });
                result = await response.json();
                if (result.success) {
                    this.pets.push(result.mascota);
                    this.renderPetsHealth();
                    this.updateStats();
                    this.closePetModal();
                    this.showSuccess('Mascota agregada exitosamente');
                } else {
                    this.showError('Error al agregar la mascota');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            this.showError('Error al guardar la mascota');
        }
    }

    /**
     * Cierra la sesión del usuario
     */
    logout() {
        localStorage.removeItem('currentUser');
        localStorage.removeItem('alaska_user');
        window.location.href = '../index.html';
    }

    /**
     * Formatea una fecha
     */
    formatDate(date) {
        return new Intl.DateTimeFormat('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(date);
    }

    /**
     * Obtiene la etiqueta del tipo de recordatorio
     */
    getReminderTypeLabel(type) {
        const labels = {
            'vacuna': 'Vacuna',
            'cita': 'Cita Veterinaria',
            'medicamento': 'Medicamento',
            'alimentacion': 'Alimentación',
            'paseo': 'Paseo',
            'otro': 'Otro'
        };
        return labels[type] || type;
    }

    /**
     * Obtiene la clase CSS del estado de salud
     */
    getHealthStatusClass(status) {
        const classes = {
            'healthy': 'healthy',
            'attention': 'attention',
            'warning': 'warning'
        };
        return classes[status] || 'healthy';
    }

    /**
     * Obtiene la etiqueta del estado de salud
     */
    getHealthStatusLabel(status) {
        const labels = {
            'healthy': 'Saludable',
            'attention': 'Necesita atención',
            'warning': 'Requiere revisión'
        };
        return labels[status] || 'Saludable';
    }

    /**
     * Capitaliza la primera letra
     */
    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    /**
     * Muestra un mensaje de éxito
     */
    showSuccess(message) {
        // Implementar notificación de éxito
        console.log('Éxito:', message);
    }

    /**
     * Muestra un mensaje de error
     */
    showError(message) {
        // Implementar notificación de error
        console.error('Error:', message);
    }
}

// Inicializar el dashboard cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    new Dashboard();
});
