// Script global para sincronización de foto de perfil
// Este script debe incluirse en todas las páginas que muestren el avatar del usuario

(function() {
    'use strict';
    
    // Escuchar mensajes de actualización de foto de perfil
    window.addEventListener('message', function(event) {
        if (event.origin !== window.location.origin) return;
        
        if (event.data.type === 'profilePictureUpdated') {
            actualizarAvatarGlobal(event.data.imageUrl);
        }
    });
    
    // Función para actualizar todos los avatares en la página actual
    function actualizarAvatarGlobal(imageUrl) {
        const profileElements = document.querySelectorAll('.inicial-circulo');
        
        profileElements.forEach(element => {
            // Verificar si ya tiene una imagen o son iniciales
            const existingImg = element.querySelector('img');
            
            if (existingImg) {
                // Ya tiene imagen, solo actualizar src
                existingImg.src = imageUrl + '?v=' + Date.now(); // Cache busting
            } else {
                // Tiene iniciales, reemplazar con imagen
                element.innerHTML = '';
                const img = document.createElement('img');
                img.src = imageUrl + '?v=' + Date.now();
                img.alt = 'Perfil';
                img.style.cssText = 'width: 100%; height: 100%; object-fit: cover; border-radius: 50%;';
                element.appendChild(img);
            }
        });
        
        console.log('Avatar actualizado globalmente:', imageUrl);
    }
    
    // Función para verificar si hay una foto de perfil actualizada al cargar la página
    function verificarFotoPerfilActualizada() {
        // Verificar localStorage por cambios recientes
        const ultimaActualizacion = localStorage.getItem('profile_picture_last_update');
        const ultimaFoto = localStorage.getItem('profile_picture_url');
        
        if (ultimaActualizacion && ultimaFoto) {
            const tiempoTranscurrido = Date.now() - parseInt(ultimaActualizacion);
            
            // Si se actualizó en los últimos 5 minutos, aplicar la nueva foto
            if (tiempoTranscurrido < 5 * 60 * 1000) {
                actualizarAvatarGlobal(ultimaFoto);
            }
        }
    }
    
    // Ejecutar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', verificarFotoPerfilActualizada);
    } else {
        verificarFotoPerfilActualizada();
    }
    
    // Exponer funciones globalmente para uso desde otros scripts
    window.profileSync = {
        actualizarAvatar: actualizarAvatarGlobal,
        notificarCambio: function(imageUrl) {
            // Guardar en localStorage para persistencia
            localStorage.setItem('profile_picture_url', imageUrl);
            localStorage.setItem('profile_picture_last_update', Date.now().toString());
            
            // Actualizar en la página actual
            actualizarAvatarGlobal(imageUrl);
            
            // Notificar a otras ventanas/tabs
            if (window.opener) {
                window.opener.postMessage({
                    type: 'profilePictureUpdated',
                    imageUrl: imageUrl
                }, window.location.origin);
            }
            
            // Broadcast a todas las ventanas de la misma aplicación
            try {
                const bc = new BroadcastChannel('profile_updates');
                bc.postMessage({
                    type: 'profilePictureUpdated',
                    imageUrl: imageUrl
                });
            } catch (e) {
                // BroadcastChannel no soportado, usar alternativa
                console.log('BroadcastChannel no soportado');
            }
        }
    };
    
    // Escuchar BroadcastChannel si está disponible
    try {
        const bc = new BroadcastChannel('profile_updates');
        bc.addEventListener('message', function(event) {
            if (event.data.type === 'profilePictureUpdated') {
                actualizarAvatarGlobal(event.data.imageUrl);
            }
        });
    } catch (e) {
        console.log('BroadcastChannel no soportado en este navegador');
    }
})();