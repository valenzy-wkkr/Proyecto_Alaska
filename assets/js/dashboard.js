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
    }

    /**
     * Carga los datos del usuario desde localStorage
     */
    loadUserData() {
        const userData = localStorage.getItem('currentUser');
        if (userData) {
            this.currentUser = JSON.parse(userData);
            this.updateUserDisplay();
        } else {
            // Si no hay usuario, redirigir al login
            window.location.href = '../index.html';
        }
    }

    /**
     * Actualiza la visualización del usuario
     */
    updateUserDisplay() {
        const userNameElement = document.getElementById('userName');
        if (userNameElement && this.currentUser) {
            userNameElement.textContent = this.currentUser.name;
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
        } catch (error) {
            console.error('Error cargando datos del dashboard:', error);
            this.showError('Error al cargar los datos del dashboard');
        }
    }

    /**
     * Carga las mascotas del usuario
     */
    async loadPets() {
        console.log('Cargando mascotas...');
        // Por ahora, siempre usar datos de ejemplo para debug
        this.pets = [
                    {
                        id: 1,
                        name: 'Luna',
                        species: 'perro',
                        breed: 'Golden Retriever',
                        age: 3.5,
                        weight: 25.5,
                        healthStatus: 'healthy',
                        lastCheckup: '2024-02-15'
                    },
                    {
                        id: 2,
                        name: 'Mittens',
                        species: 'gato',
                        breed: 'Persa',
                        age: 2.0,
                        weight: 4.2,
                        healthStatus: 'healthy',
                        lastCheckup: '2024-01-20'
                    },
                    {
                        id: 3,
                        name: 'Max',
                        species: 'perro',
                        breed: 'Labrador',
                        age: 5.0,
                        weight: 30.0,
                        healthStatus: 'attention',
                        lastCheckup: '2024-03-01'
                    }
                ];
        console.log('Mascotas cargadas:', this.pets);
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
            // Simular carga desde API
            this.appointments = [
                {
                    id: 1,
                    petId: 1,
                    petName: 'Luna',
                    date: '2024-03-15T10:00:00',
                    reason: 'Vacunación anual',
                    status: 'programada'
                },
                {
                    id: 2,
                    petId: 2,
                    petName: 'Mittens',
                    date: '2024-03-10T14:30:00',
                    reason: 'Revisión de rutina',
                    status: 'programada'
                }
            ];
        } catch (error) {
            console.error('Error cargando citas:', error);
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
                </div>
            `;
        }).join('');
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
    openPetModal() {
        const modal = document.getElementById('petModal');
        if (modal) {
            modal.classList.add('active');
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
            const response = await fetch('recordatorios.php', {
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
            healthStatus: 'healthy',
            lastCheckup: new Date().toISOString().split('T')[0]
        };

        try {
            const response = await fetch('mascotas.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(pet)
            });

            const result = await response.json();
            
            if (result.success) {
                this.pets.push(result.mascota);
                this.renderPetsHealth();
                this.updateStats();
                this.closePetModal();
                this.showSuccess('Mascota agregada exitosamente');
            } else {
                this.showError('Error al agregar la mascota');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showError('Error al agregar la mascota');
        }
    }

    /**
     * Cierra la sesión del usuario
     */
    logout() {
        localStorage.removeItem('currentUser');
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
