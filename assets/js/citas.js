// Clase para manejar las operaciones de citas
class CitasManager {
    constructor() {
        this.apiUrl = '../public/api/citas.php';
        this.citas = [];
        this.init();
    }

    init() {
        this.bindEvents();
        this.cargarCitas();
        this.configurarFechaMinima();
    }

    bindEvents() {
        // Formulario de nueva cita
        const formulario = document.getElementById('formulario-citas');
        if (formulario) {
            formulario.addEventListener('submit', (e) => this.manejarEnvioFormulario(e));
        }

        // Filtro de mascotas
        const filtroMascota = document.getElementById('filtro-mascota');
        if (filtroMascota) {
            filtroMascota.addEventListener('change', () => this.filtrarCitas());
        }

        // Botón de exportar
        const btnExportar = document.getElementById('btn-exportar');
        if (btnExportar) {
            btnExportar.addEventListener('click', () => this.exportarHistorial());
        }
    }

    configurarFechaMinima() {
        const inputFecha = document.getElementById('fecha');
        if (inputFecha) {
            const ahora = new Date();
            const fechaMinima = ahora.toISOString().slice(0, 16);
            inputFecha.min = fechaMinima;
        }
    }

    async cargarCitas() {
        try {
            const respuesta = await fetch(this.apiUrl);
            const resultado = await respuesta.json();
            
            if (resultado.exito) {
                this.citas = resultado.datos || [];
                this.mostrarCitas();
            } else {
                this.mostrarError('Error al cargar las citas: ' + resultado.mensaje);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error de conexión al cargar las citas');
        }
    }

    async manejarEnvioFormulario(evento) {
        evento.preventDefault();
        
        const formulario = evento.target;
        const datos = new FormData(formulario);
        
        // Convertir FormData a objeto
        const datosCita = {};
        for (let [clave, valor] of datos.entries()) {
            datosCita[clave] = valor;
        }

        try {
            const respuesta = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(datosCita)
            });

            const resultado = await respuesta.json();
            
            if (resultado.exito) {
                this.mostrarExito('Cita creada exitosamente');
                formulario.reset();
                // Notificar al dashboard que hay una nueva cita
                try {
                    const creada = resultado.cita || { ...datosCita };
                    // Normalizar estado para el dashboard si fuera necesario
                    if (!creada.estado && creada.status) creada.estado = creada.status;
                    // Emitir evento dentro de la misma pestaña
                    window.dispatchEvent(new CustomEvent('appointment:created', { detail: creada }));
                    // Señal entre pestañas/ventanas
                    localStorage.setItem('alaska_appointments_updated', String(Date.now()));
                } catch (e) {
                    console.warn('No se pudo emitir evento de creación de cita:', e);
                }

                this.cargarCitas(); // Recargar la lista
            } else {
                this.mostrarError('Error al crear la cita: ' + resultado.mensaje);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error de conexión al crear la cita');
        }
    }

    mostrarCitas() {
        const contenedor = document.getElementById('lista-citas');
        if (!contenedor) return;

        const filtro = document.getElementById('filtro-mascota')?.value || 'todas';
        let citasFiltradas = this.citas;

        if (filtro !== 'todas') {
            citasFiltradas = this.citas.filter(cita => cita.tipo_mascota === filtro);
        }

        if (citasFiltradas.length === 0) {
            contenedor.innerHTML = `
                <div class="sin-citas">
                    <i class="fas fa-calendar-times fa-3x"></i>
                    <p>No hay citas registradas</p>
                </div>
            `;
            return;
        }

        contenedor.innerHTML = citasFiltradas.map(cita => this.crearTarjetaCita(cita)).join('');
        this.bindEventosTarjetas();
    }

    crearTarjetaCita(cita) {
        const fecha = new Date(cita.fecha_cita);
        const fechaFormateada = fecha.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
        const horaFormateada = fecha.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit'
        });

        const estadoClass = this.obtenerClaseEstado(cita.estado);
        const estadoTexto = this.obtenerTextoEstado(cita.estado);

        // Mostrar nombre de la mascota si existe, si no, mostrar tipo de mascota
        const nombreMascota = cita.nombre_mascota ? `${cita.nombre_mascota} (${cita.tipo_mascota})` : cita.tipo_mascota;

        return `
            <div class="tarjeta-visita" data-id="${cita.id}">
                <div class="cabecera-tarjeta">
                    <div class="fecha-visita">
                        <i class="far fa-calendar-alt"></i>
                        <span class="fecha">${fechaFormateada} ${horaFormateada}</span>
                    </div>
                    <div class="mascota">
                        <i class="fas fa-paw"></i>
                        <span class="nombre-mascota">${nombreMascota}</span>
                    </div>
                    <span class="estado ${estadoClass}">${estadoTexto}</span>
                </div>
                <div class="cuerpo-tarjeta">
                    <div class="detalle">
                        <h4>Motivo</h4>
                        <p class="motivo">${cita.motivo}</p>
                    </div>
                    ${cita.diagnostico ? `
                        <div class="detalle">
                            <h4>Diagnóstico</h4>
                            <p class="diagnostico">${cita.diagnostico}</p>
                        </div>
                    ` : ''}
                    ${cita.tratamiento ? `
                        <div class="detalle">
                            <h4>Tratamiento</h4>
                            <p class="tratamiento">${cita.tratamiento}</p>
                        </div>
                    ` : ''}
                    ${cita.veterinario ? `
                        <div class="detalle">
                            <h4>Veterinario</h4>
                            <p class="veterinario">${cita.veterinario}</p>
                        </div>
                    ` : ''}
                    <button class="boton-detalles" aria-expanded="false">
                        <i class="fas fa-chevron-down"></i> Ver detalles
                    </button>
                    <div class="detalles-adicionales" aria-hidden="true">
                        ${cita.notas ? `
                            <h5>Notas adicionales:</h5>
                            <p class="notas">${cita.notas}</p>
                        ` : ''}
                        <div class="acciones">
                            <button class="boton-texto" onclick="citasManager.editarCita(${cita.id})">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button class="boton-texto" onclick="citasManager.eliminarCita(${cita.id})">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                            <button class="boton-texto" onclick="citasManager.imprimirCita(${cita.id})">
                                <i class="fas fa-print"></i> Imprimir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    bindEventosTarjetas() {
        // Botones de detalles
        document.querySelectorAll('.boton-detalles').forEach(boton => {
            boton.addEventListener('click', (e) => {
                const tarjeta = e.target.closest('.tarjeta-visita');
                const detalles = tarjeta.querySelector('.detalles-adicionales');
                const isExpanded = boton.getAttribute('aria-expanded') === 'true';
                
                boton.setAttribute('aria-expanded', !isExpanded);
                detalles.setAttribute('aria-hidden', isExpanded);
                
                // Rotar icono
                const icono = boton.querySelector('i');
                icono.style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(180deg)';
            });
        });
    }

    obtenerClaseEstado(estado) {
        const estados = {
            'programada': 'pendiente',
            'completada': 'completada',
            'cancelada': 'cancelada'
        };
        return estados[estado] || 'pendiente';
    }

    obtenerTextoEstado(estado) {
        const estados = {
            'programada': 'Programada',
            'completada': 'Completada',
            'cancelada': 'Cancelada'
        };
        return estados[estado] || 'Programada';
    }

    filtrarCitas() {
        this.mostrarCitas();
    }

    async editarCita(id) {
        const cita = this.citas.find(c => c.id == id);
        if (!cita) return;

        // Aquí podrías abrir un modal de edición
        // Por ahora, solo mostramos un prompt simple
        const nuevoEstado = prompt('Nuevo estado (programada/completada/cancelada):', cita.estado);
        if (nuevoEstado && nuevoEstado !== cita.estado) {
            await this.actualizarCita(id, { estado: nuevoEstado });
        }
    }

    async actualizarCita(id, datos) {
        try {
            const respuesta = await fetch(this.apiUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id, ...datos })
            });

            const resultado = await respuesta.json();
            
            if (resultado.exito) {
                this.mostrarExito('Cita actualizada exitosamente');
                this.cargarCitas();
            } else {
                this.mostrarError('Error al actualizar la cita: ' + resultado.mensaje);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error de conexión al actualizar la cita');
        }
    }

    async eliminarCita(id) {
        if (!confirm('¿Estás seguro de que quieres eliminar esta cita?')) {
            return;
        }

        try {
            const respuesta = await fetch(`${this.apiUrl}?id=${id}`, {
                method: 'DELETE'
            });

            const resultado = await respuesta.json();
            
            if (resultado.exito) {
                this.mostrarExito('Cita eliminada exitosamente');
                this.cargarCitas();
            } else {
                this.mostrarError('Error al eliminar la cita: ' + resultado.mensaje);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error de conexión al eliminar la cita');
        }
    }

    imprimirCita(id) {
        const cita = this.citas.find(c => c.id == id);
        if (!cita) return;

        const ventanaImpresion = window.open('', '_blank');
        const fecha = new Date(cita.fecha_cita);
        const mascota = cita.nombre_mascota ? `${cita.nombre_mascota} (${cita.tipo_mascota})` : `${cita.tipo_mascota}`;
        const estadoTexto = this.obtenerTextoEstado(cita.estado);
        const estadoClase = this.obtenerClaseEstado(cita.estado);

        ventanaImpresion.document.write(`
            <html>
                <head>
                    <meta charset="utf-8" />
                    <meta name="viewport" content="width=device-width, initial-scale=1" />
                    <title>Informe de Cita - ${mascota}</title>
                    <style>
                        :root{ --primary:#1976d2; --text:#1f2a44; --muted:#6b7280; --border:#e5e7eb; }
                        *{ box-sizing:border-box; }
                        html,body{ height:100%; }
                        body{ margin:0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color:var(--text); background:#f6f8fb; }
                        .sheet{ width: 920px; max-width: 92%; margin: 32px auto; background:#fff; border:1px solid var(--border); border-radius:16px; box-shadow:0 10px 30px rgba(31,42,68,.08); overflow:hidden; }
                        .header{ display:flex; align-items:center; gap:16px; padding:28px 32px; border-bottom:3px solid var(--primary); background: linear-gradient(180deg, rgba(25,118,210,0.06), rgba(25,118,210,0)); }
                        .brand{ width:56px; height:56px; border-radius:14px; background:#fff; border:1px solid var(--border); display:flex; align-items:center; justify-content:center; overflow:hidden; }
                        .brand img{ width:100%; height:100%; object-fit:contain; border-radius:inherit; }
                        .titles h1{ margin:0; font-size:20px; letter-spacing:.5px; }
                        .titles p{ margin:2px 0 0; color:var(--muted); font-size:12px; }

                        .section{ padding:22px 32px; }
                        .section h2{ margin:0 0 12px; font-size:14px; color:var(--primary); text-transform:uppercase; letter-spacing:.1em; }

                        .info-grid{ display:grid; grid-template-columns: 1fr 1fr; gap:14px 24px; }
                        .info-item{ display:flex; gap:8px; align-items:baseline; }
                        .label{ width:140px; min-width:140px; color:var(--muted); font-weight:600; font-size:12px; text-transform:uppercase; letter-spacing:.06em; }
                        .value{ color:var(--text); font-size:14px; }

                        .status{ display:inline-flex; align-items:center; gap:8px; font-weight:600; font-size:12px; padding:6px 10px; border-radius:999px; border:1px solid var(--border); }
                        .status.pendiente{ color:#b45309; background:#fff7ed; border-color:#fed7aa; }
                        .status.completada{ color:#166534; background:#ecfdf5; border-color:#bbf7d0; }
                        .status.cancelada{ color:#991b1b; background:#fef2f2; border-color:#fecaca; }

                        .notes{ margin-top:8px; padding:14px; border:1px dashed var(--border); border-radius:10px; background:#fafbff; }
                        .notes h3{ margin:0 0 8px; font-size:13px; color:var(--muted); text-transform:uppercase; letter-spacing:.08em; }
                        .notes p{ margin:0; line-height:1.6; font-size:14px; }

                        .footer{ display:flex; justify-content:space-between; gap:24px; padding:24px 32px 32px; }
                        .sign{ flex:1; text-align:center; }
                        .line{ margin-top:54px; border-top:1px solid var(--border); padding-top:6px; color:var(--muted); font-size:12px; }
                        .meta{ text-align:right; color:var(--muted); font-size:12px; }

                        @media print{
                            body{ background:#fff; }
                            .sheet{ width:auto; max-width:100%; margin:0; border:none; box-shadow:none; border-radius:0; }
                            .header{ border-bottom:2px solid var(--primary); }
                            .notes{ background:#fff; }
                            .footer{ padding-bottom:8px; }
                        }
                    </style>
                </head>
                <body>
                    <div class="sheet">
                        <header class="header">
                            <div class="brand"><img src="/Proyecto_Alaska/img/alaska.png" alt="Logo Alaska" /></div>
                            <div class="titles">
                                <h1>ALASKA - Clínica Veterinaria</h1>
                                <p>Informe de Cita • ${new Date().toLocaleDateString('es-ES')}</p>
                            </div>
                        </header>

                        <section class="section">
                            <h2>Resumen</h2>
                            <div class="info-grid">
                                <div class="info-item"><div class="label">Fecha</div><div class="value">${fecha.toLocaleDateString('es-ES')}</div></div>
                                <div class="info-item"><div class="label">Hora</div><div class="value">${fecha.toLocaleTimeString('es-ES', {hour:'2-digit', minute:'2-digit'})}</div></div>
                                <div class="info-item"><div class="label">Mascota</div><div class="value">${mascota}</div></div>
                                <div class="info-item"><div class="label">Estado</div><div class="value"><span class="status ${estadoClase}">${estadoTexto}</span></div></div>
                                <div class="info-item"><div class="label">Motivo</div><div class="value">${cita.motivo || '-'}</div></div>
                                ${cita.veterinario ? `<div class="info-item"><div class="label">Veterinario</div><div class="value">${cita.veterinario}</div></div>` : ''}
                                ${cita.diagnostico ? `<div class="info-item"><div class="label">Diagnóstico</div><div class="value">${cita.diagnostico}</div></div>` : ''}
                                ${cita.tratamiento ? `<div class="info-item"><div class="label">Tratamiento</div><div class="value">${cita.tratamiento}</div></div>` : ''}
                            </div>

                            ${cita.notas ? `
                                <div class="notes">
                                    <h3>Notas adicionales</h3>
                                    <p>${cita.notas}</p>
                                </div>
                            ` : ''}
                        </section>

                        <footer class="footer">
                            <div class="sign">
                                <div class="line">Firma del Veterinario</div>
                            </div>
                            <div class="meta">
                                Generado por Alaska • ${new Date().toLocaleString('es-ES')}
                            </div>
                        </footer>
                    </div>
                    <script>
                        window.addEventListener('load', function(){
                            setTimeout(function(){ window.print(); window.close(); }, 300);
                        });
                    <\/script>
                </body>
            </html>
        `);

        ventanaImpresion.document.close();
    }

    exportarHistorial() {
        const filtro = document.getElementById('filtro-mascota')?.value || 'todas';
        let citasFiltradas = this.citas;

        if (filtro !== 'todas') {
            citasFiltradas = this.citas.filter(cita => cita.tipo_mascota === filtro);
        }

        if (citasFiltradas.length === 0) {
            this.mostrarError('No hay citas para exportar');
            return;
        }

        // Crear CSV
        const headers = ['Fecha', 'Hora', 'Mascota', 'Motivo', 'Estado', 'Diagnóstico', 'Tratamiento', 'Veterinario', 'Notas'];
        const csvContent = [
            headers.join(','),
            ...citasFiltradas.map(cita => {
                const fecha = new Date(cita.fecha_cita);
                return [
                    fecha.toLocaleDateString('es-ES'),
                    fecha.toLocaleTimeString('es-ES'),
                    `"${cita.tipo_mascota}"`,
                    `"${cita.motivo}"`,
                    `"${cita.estado}"`,
                    `"${cita.diagnostico || ''}"`,
                    `"${cita.tratamiento || ''}"`,
                    `"${cita.veterinario || ''}"`,
                    `"${cita.notas || ''}"`
                ].join(',');
            })
        ].join('\n');

        // Descargar archivo
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `historial_citas_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    mostrarExito(mensaje) {
        this.mostrarMensaje(mensaje, 'exito');
    }

    mostrarError(mensaje) {
        this.mostrarMensaje(mensaje, 'error');
    }

    mostrarMensaje(mensaje, tipo) {
        // Ocultar mensajes anteriores
        document.querySelectorAll('.mensaje-exito, .mensaje-error').forEach(el => {
            el.style.display = 'none';
        });

        const elemento = document.getElementById(`mensaje-${tipo}`);
        if (elemento) {
            elemento.textContent = mensaje;
            elemento.style.display = 'block';
            
            // Ocultar después de 5 segundos
            setTimeout(() => {
                elemento.style.display = 'none';
            }, 5000);
        }
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.citasManager = new CitasManager();
});
