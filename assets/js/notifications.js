/**
 * Sistema de notificaciones en tiempo real con Pusher
 */

// Configuración de Pusher (las credenciales se cargan desde PHP)
let pusher = null;
let channel = null;
let notificationsEnabled = false;

// Inicializar Pusher
function initPusher(appKey, cluster, userId) {
    if (!appKey || !cluster || !userId) {
        console.log('Pusher no configurado - Notificaciones deshabilitadas');
        return;
    }

    try {
        pusher = new Pusher(appKey, {
            cluster: cluster,
            encrypted: true,
            authEndpoint: '/pusher/auth',
            auth: {
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            }
        });

        // Suscribirse al canal privado del usuario
        const channelName = `private-user-${userId}`;
        channel = pusher.subscribe(channelName);
        
        // Manejar errores de suscripción
        channel.bind('pusher:subscription_error', function(status) {
            console.error('Error suscripción Pusher:', status);
            notificationsEnabled = false;
        });
        
        channel.bind('pusher:subscription_succeeded', function() {
            console.log('Suscrito correctamente a canal privado:', channelName);
            notificationsEnabled = true;
        });

        // Suscribirse a eventos de notificaciones
        channel.bind('reserva-cercana', handleReservaCercana);
        channel.bind('pago-pendiente', handlePagoPendiente);
        channel.bind('pedido-cabania', handlePedidoCabania);
        channel.bind('inconveniente-pedido', handleInconvenientePedido);

        console.log('Pusher inicializado correctamente para usuario:', userId);
    } catch (error) {
        console.error('Error inicializando Pusher:', error);
        notificationsEnabled = false;
    }
}

// Manejadores de eventos específicos

function handleReservaCercana(data) {
    console.log('Notificación: Reserva cercana', data);
    
    // Mostrar notificación visual
    showNotification(data);
    
    // Agregar a la lista de notificaciones
    addNotificationToList(data);
    
    // Reproducir sonido suave (opcional)
    playNotificationSound('info');
}

function handlePagoPendiente(data) {
    console.log('Notificación: Pago pendiente', data);
    
    showNotification(data);
    addNotificationToList(data);
    
    // Sonido de advertencia
    playNotificationSound('warning');
}

function handlePedidoCabania(data) {
    console.log('Notificación: Pedido en cabaña', data);
    
    showNotification(data);
    addNotificationToList(data);
    
    // Sonido más llamativo para pedidos urgentes
    if (data.sound) {
        playNotificationSound('success');
    }
}

function handleInconvenientePedido(data) {
    console.log('Notificación: Inconveniente con pedido', data);
    
    showNotification(data);
    addNotificationToList(data);
    
    // Sonido urgente
    if (data.sound) {
        playNotificationSound('error');
    }
}

// Mostrar notificación tipo toast
function showNotification(data) {
    const iconClass = data.icon || 'fa-bell';
    const colorClass = data.color || 'info';
    
    // Crear elemento de notificación
    const notification = $(`
        <div class="alert alert-${colorClass} alert-dismissible fade show notification-toast" 
             role="alert" 
             style="position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 350px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            <div class="d-flex align-items-center">
                <i class="fas ${iconClass} fa-2x mr-3"></i>
                <div class="flex-grow-1">
                    <strong class="d-block">${data.title}</strong>
                    <small>${data.message}</small>
                </div>
                <button type="button" class="close ml-2" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            ${data.url ? `<hr><a href="${data.url}" class="alert-link">Ver detalle <i class="fas fa-arrow-right"></i></a>` : ''}
        </div>
    `);
    
    // Agregar al DOM
    $('body').append(notification);
    
    // Auto-cerrar después de 8 segundos
    setTimeout(() => {
        notification.fadeOut(500, function() {
            $(this).remove();
        });
    }, 8000);
}

// Agregar notificación a la lista del dropdown
function addNotificationToList(data) {
    const badge = $('#notifications-badge');
    const list = $('#notifications-list');
    
    if (!list.length) return;
    
    // Incrementar contador
    let count = parseInt(badge.text()) || 0;
    count++;
    badge.text(count).removeClass('d-none');
    
    // Crear item de notificación
    const iconClass = data.icon || 'fa-bell';
    const colorClass = getBootstrapColor(data.color);
    
    const item = $(`
        <a class="dropdown-item notification-item" href="${data.url || '#'}" data-notification-id="${data.timestamp}">
            <div class="d-flex align-items-start">
                <div class="notification-icon bg-${colorClass} text-white rounded-circle mr-3 d-flex align-items-center justify-content-center" 
                     style="width: 40px; height: 40px; flex-shrink: 0;">
                    <i class="fas ${iconClass}"></i>
                </div>
                <div class="notification-content flex-grow-1">
                    <strong class="d-block">${data.title}</strong>
                    <small class="text-muted">${data.message}</small>
                    <br>
                    <small class="text-muted"><i class="fas fa-clock"></i> ${formatTimestamp(data.timestamp)}</small>
                </div>
            </div>
        </a>
    `);
    
    // Agregar al inicio de la lista
    if (list.children('.dropdown-item').first().hasClass('text-center')) {
        // Reemplazar mensaje "Sin notificaciones"
        list.empty();
    }
    
    list.prepend(item);
    
    // Limitar a 10 notificaciones
    if (list.children().length > 10) {
        list.children().last().remove();
    }
}

// Reproducir sonido de notificación
function playNotificationSound(type) {
    // Solo reproducir si el usuario ha interactuado con la página
    if (!document.hasFocus()) return;
    
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        // Frecuencias según tipo
        const frequencies = {
            'info': 523.25,      // Do
            'success': 659.25,   // Mi
            'warning': 587.33,   // Re
            'error': 698.46      // Fa#
        };
        
        oscillator.frequency.value = frequencies[type] || frequencies.info;
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.3);
    } catch (error) {
        console.log('No se pudo reproducir sonido de notificación');
    }
}

// Utilidades

function getBootstrapColor(color) {
    const colorMap = {
        'info': 'info',
        'success': 'success',
        'warning': 'warning',
        'danger': 'danger',
        'error': 'danger'
    };
    return colorMap[color] || 'secondary';
}

function formatTimestamp(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // segundos
    
    if (diff < 60) return 'Hace un momento';
    if (diff < 3600) return `Hace ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `Hace ${Math.floor(diff / 3600)} h`;
    
    return date.toLocaleDateString('es-AR', { 
        day: '2-digit', 
        month: '2-digit', 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

// Marcar notificación como leída
function markNotificationAsRead(notificationId) {
    const item = $(`.notification-item[data-notification-id="${notificationId}"]`);
    if (item.length) {
        item.fadeOut(300, function() {
            $(this).remove();
            
            // Actualizar contador
            const badge = $('#notifications-badge');
            let count = parseInt(badge.text()) || 0;
            count = Math.max(0, count - 1);
            
            if (count > 0) {
                badge.text(count);
            } else {
                badge.addClass('d-none');
                $('#notifications-list').html('<div class="dropdown-item text-center text-muted">Sin notificaciones</div>');
            }
        });
    }
}

// Limpiar todas las notificaciones
function clearAllNotifications() {
    $('#notifications-list').html('<div class="dropdown-item text-center text-muted">Sin notificaciones</div>');
    $('#notifications-badge').addClass('d-none').text('0');
}

// Exportar funciones globales
window.NotificationService = {
    init: initPusher,
    markAsRead: markNotificationAsRead,
    clearAll: clearAllNotifications
};
