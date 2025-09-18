// Manejador de recordatorios
document.addEventListener('DOMContentLoaded', function() {
    // Obtener referencias a elementos del DOM
    const reminderForm = document.getElementById('reminderForm');
    const saveButton = document.querySelector('.modal-content button[type="submit"]');
    const cancelButton = document.getElementById('closeReminderModal');
    const modal = document.getElementById('reminderModal');

    // Agregar evento al botón de guardar
    if (reminderForm) {
        reminderForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Obtener datos del formulario
            const formData = new FormData(reminderForm);
            const reminder = {
                title: formData.get('title'),
                date: formData.get('date'),
                type: formData.get('type'),
                petId: parseInt(formData.get('petId')) || 0,
                notes: formData.get('notes') || ''
            };

            try {
                // Enviar datos al servidor
                const response = await fetch('php/recordatorios.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(reminder)
                });

                const result = await response.json();
                
                if (result.success) {
                    // Cerrar el modal
                    if (modal) {
                        modal.style.display = 'none';
                    }
                    
                    // Mostrar mensaje de éxito
                    alert('Recordatorio guardado exitosamente');
                    
                    // Recargar la página para mostrar el nuevo recordatorio
                    window.location.reload();
                } else {
                    alert('Error al guardar el recordatorio: ' + (result.error || 'Error desconocido'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('No se pudo guardar el recordatorio. Por favor, verifica tu conexión e intenta nuevamente.');
            }
        });
    }

    // Agregar evento al botón de cancelar
    if (cancelButton) {
        cancelButton.addEventListener('click', function() {
            if (modal) {
                modal.style.display = 'none';
            }
        });
    }
});