/**
 * Components JavaScript - Casa de Palos Cabañas
 * JavaScript para componentes reutilizables
 * 
 * SISTEMA DE MENSAJES SUTILES
 * ===========================
 * 
 * Uso básico:
 * window.showMessage(type, title, text, permanent?)
 * 
 * Tipos disponibles:
 * - success: Operaciones exitosas
 * - error: Errores y problemas
 * - warning: Advertencias
 * - info: Información general
 * - loading: Procesos en curso
 * - upload/download: Transferencias de archivos
 * - save/delete/edit: Operaciones CRUD
 * 
 * Funciones de conveniencia:
 * - window.showSuccess(title, text)
 * - window.showError(title, text)
 * - window.showWarning(title, text)
 * - window.showInfo(title, text)
 * - window.showSaveSuccess(entityName?)
 * - window.showDeleteSuccess(entityName?)
 * - window.showLoadingMessage(action?)
 * - window.clearAllMessages()
 * 
 * Ejemplos:
 * window.showSuccess('Producto guardado', 'El producto se registró correctamente');
 * window.showError('Error de validación', 'Revisa los campos obligatorios');
 * window.showLoadingMessage('guardando datos');
 * window.showMessage('upload', 'Subiendo archivo...', 'Por favor espera', true);
 */

// ===========================================
// SISTEMA DE MENSAJES Y NOTIFICACIONES
// ===========================================

class MessageSystem {
    constructor() {
        this.initializeMessages();
        this.bindEvents();
    }

    initializeMessages() {
        // Auto-hide messages después de 5 segundos
        const messages = document.querySelectorAll('.message');
        messages.forEach(message => {
            if (!message.classList.contains('permanent')) {
                setTimeout(() => {
                    this.hideMessage(message);
                }, 5000);
            }
        });
    }

    bindEvents() {
        // Event listeners para botones de cierre
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('message-close')) {
                e.preventDefault();
                const message = e.target.closest('.message');
                if (message) {
                    this.hideMessage(message);
                }
            }
        });
    }

    hideMessage(messageElement) {
        // Animación de salida simple
        messageElement.style.opacity = '0';
        
        // Remover elemento del DOM
        setTimeout(() => {
            if (messageElement.parentNode) {
                messageElement.parentNode.removeChild(messageElement);
            }
        }, 200);
    }

    showMessage(type, title, text, permanent = false) {
        // Crear contenedor de mensajes si no existe
        let container = document.querySelector('.messages-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'messages-container';
            document.body.appendChild(container);
        }

        // Usar el título para mostrar el mensaje principal
        const displayText = title;
        
        const messageHTML = `
            <div class="subtle-message ${type} ${permanent ? 'permanent' : ''}" style="opacity: 0;">
                <div class="subtle-message-icon">
                    <i class="fas ${this.getMessageIcon(type)}"></i>
                </div>
                <div class="subtle-message-content">
                    <div class="subtle-message-title">${displayText}</div>
                </div>
                <div class="subtle-message-progress ${permanent ? 'hidden' : ''}"></div>
                ${!permanent ? '<button class="subtle-message-close" aria-label="Cerrar mensaje"><i class="fas fa-times"></i></button>' : ''}
            </div>
        `;

        const messageElement = document.createElement('div');
        messageElement.innerHTML = messageHTML;
        const message = messageElement.firstElementChild;
        
        container.appendChild(message);
        
        // Configurar evento de cierre
        const closeBtn = message.querySelector('.subtle-message-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.hideMessage(message));
        }

        // Animación de entrada
        setTimeout(() => {
            message.style.opacity = '1';
        }, 50);

        if (!permanent) {
            // Animación de barra de progreso
            const progressBar = message.querySelector('.subtle-message-progress');
            if (progressBar) {
                setTimeout(() => {
                    progressBar.style.width = '0%';
                }, 100);
            }

            setTimeout(() => {
                this.hideMessage(message);
            }, 5000);
        }

        return message;
    }

    getMessageIcon(type) {
        const icons = {
            'success': 'fa-check-circle',
            'error': 'fa-times-circle', 
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle',
            // Iconos adicionales para casos específicos
            'loading': 'fa-spinner fa-spin',
            'upload': 'fa-cloud-upload-alt',
            'download': 'fa-cloud-download-alt',
            'save': 'fa-save',
            'delete': 'fa-trash-alt',
            'edit': 'fa-edit',
            'notification': 'fa-bell'
        };
        return icons[type] || 'fa-info-circle';
    }
}

// ===========================================
// NAVEGACIÓN BREADCRUMBS
// ===========================================

class BreadcrumbManager {
    constructor() {
        this.initializeBreadcrumbs();
    }

    initializeBreadcrumbs() {
        const breadcrumbs = document.querySelector('.breadcrumb');
        if (!breadcrumbs) return;

        // Añadir animación de entrada
        const items = breadcrumbs.querySelectorAll('.breadcrumb-item');
        items.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-10px)';
            
            setTimeout(() => {
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, index * 100);
        });
    }

    addBreadcrumb(text, url = null) {
        const breadcrumb = document.querySelector('.breadcrumb');
        if (!breadcrumb) return;

        const item = document.createElement('div');
        item.className = 'breadcrumb-item';
        
        if (url) {
            item.innerHTML = `<a href="${url}">${text}</a>`;
        } else {
            item.innerHTML = text;
            item.classList.add('active');
        }

        // Remover active de otros items
        breadcrumb.querySelectorAll('.breadcrumb-item.active').forEach(activeItem => {
            activeItem.classList.remove('active');
        });

        breadcrumb.appendChild(item);
    }
}

// ===========================================
// SISTEMA DE TOOLTIPS
// ===========================================

class TooltipManager {
    constructor() {
        this.tooltips = [];
        this.initializeTooltips();
    }

    initializeTooltips() {
        const elements = document.querySelectorAll('[data-tooltip]');
        elements.forEach(element => {
            this.bindTooltip(element);
        });
    }

    bindTooltip(element) {
        let tooltip = null;

        const showTooltip = (e) => {
            const text = element.getAttribute('data-tooltip');
            const position = element.getAttribute('data-tooltip-position') || 'top';

            tooltip = document.createElement('div');
            tooltip.className = `tooltip tooltip-${position}`;
            tooltip.innerHTML = text;
            document.body.appendChild(tooltip);

            const rect = element.getBoundingClientRect();
            const tooltipRect = tooltip.getBoundingClientRect();

            let left, top;

            switch (position) {
                case 'bottom':
                    left = rect.left + (rect.width - tooltipRect.width) / 2;
                    top = rect.bottom + 10;
                    break;
                case 'left':
                    left = rect.left - tooltipRect.width - 10;
                    top = rect.top + (rect.height - tooltipRect.height) / 2;
                    break;
                case 'right':
                    left = rect.right + 10;
                    top = rect.top + (rect.height - tooltipRect.height) / 2;
                    break;
                default: // top
                    left = rect.left + (rect.width - tooltipRect.width) / 2;
                    top = rect.top - tooltipRect.height - 10;
            }

            tooltip.style.left = `${Math.max(10, Math.min(window.innerWidth - tooltipRect.width - 10, left))}px`;
            tooltip.style.top = `${Math.max(10, top)}px`;
            
            setTimeout(() => tooltip.classList.add('show'), 10);
        };

        const hideTooltip = () => {
            if (tooltip) {
                tooltip.classList.remove('show');
                setTimeout(() => {
                    if (tooltip && tooltip.parentNode) {
                        tooltip.parentNode.removeChild(tooltip);
                    }
                    tooltip = null;
                }, 200);
            }
        };

        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
        element.addEventListener('focus', showTooltip);
        element.addEventListener('blur', hideTooltip);
    }

    refresh() {
        this.initializeTooltips();
    }
}

// ===========================================
// SISTEMA DE MODAL
// ===========================================

class ModalManager {
    constructor() {
        this.activeModal = null;
        this.bindEvents();
    }

    bindEvents() {
        document.addEventListener('click', (e) => {
            // Abrir modal
            if (e.target.hasAttribute('data-modal')) {
                e.preventDefault();
                const modalId = e.target.getAttribute('data-modal');
                this.showModal(modalId);
            }

            // Cerrar modal
            if (e.target.classList.contains('modal-close') || 
                e.target.classList.contains('modal-overlay')) {
                this.hideModal();
            }
        });

        // Cerrar con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.activeModal) {
                this.hideModal();
            }
        });
    }

    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        this.activeModal = modal;
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';

        // Focus al primer elemento focuseable
        const focusable = modal.querySelector('input, button, textarea, select, a[href]');
        if (focusable) {
            setTimeout(() => focusable.focus(), 100);
        }
    }

    hideModal() {
        if (!this.activeModal) return;

        this.activeModal.classList.remove('show');
        document.body.style.overflow = '';
        this.activeModal = null;
    }

    createModal(id, title, content, actions = []) {
        const modalHTML = `
            <div class="modal" id="${id}">
                <div class="modal-overlay"></div>
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">${title}</h3>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                    ${actions.length > 0 ? `
                        <div class="modal-footer">
                            ${actions.map(action => `
                                <button class="${action.class || 'button-secondary'}" 
                                        onclick="${action.onclick || ''}"
                                        ${action.attributes || ''}>
                                    ${action.text}
                                </button>
                            `).join('')}
                        </div>
                    ` : ''}
                </div>
            </div>
        `;

        const modalElement = document.createElement('div');
        modalElement.innerHTML = modalHTML;
        document.body.appendChild(modalElement.firstElementChild);
    }
}

// ===========================================
// SISTEMA DE LOADING
// ===========================================

class LoadingManager {
    constructor() {
        this.loadingElements = new Set();
    }

    showButtonLoading(button) {
        if (this.loadingElements.has(button)) return;

        button.disabled = true;
        button.classList.add('button-loading');
        this.loadingElements.add(button);
    }

    hideButtonLoading(button) {
        if (!this.loadingElements.has(button)) return;

        button.disabled = false;
        button.classList.remove('button-loading');
        this.loadingElements.delete(button);
    }

    showPageLoading() {
        let loader = document.getElementById('page-loader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'page-loader';
            loader.className = 'loading-overlay';
            loader.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <div class="loading-text">Cargando...</div>
                </div>
            `;
            document.body.appendChild(loader);
        }
        loader.classList.add('show');
    }

    hidePageLoading() {
        const loader = document.getElementById('page-loader');
        if (loader) {
            loader.classList.remove('show');
            setTimeout(() => {
                if (loader.parentNode) {
                    loader.parentNode.removeChild(loader);
                }
            }, 300);
        }
    }
}

// ===========================================
// SISTEMA DE CONFIRMACIÓN
// ===========================================

class ConfirmationManager {
    constructor() {
        this.bindEvents();
    }

    bindEvents() {
        document.addEventListener('click', (e) => {
            if (e.target.hasAttribute('data-confirm')) {
                const message = e.target.getAttribute('data-confirm');
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            }

            if (e.target.hasAttribute('data-confirm-modal')) {
                e.preventDefault();
                const message = e.target.getAttribute('data-confirm-modal');
                const title = e.target.getAttribute('data-confirm-title') || 'Confirmar acción';
                this.showConfirmModal(title, message, () => {
                    // Ejecutar la acción original
                    if (e.target.href) {
                        window.location.href = e.target.href;
                    } else if (e.target.onclick) {
                        e.target.onclick();
                    }
                });
            }
        });
    }

    showConfirmModal(title, message, onConfirm) {
        const modalId = 'confirm-modal-' + Date.now();
        
        window.modalManager.createModal(modalId, title, `
            <p class="text-center" style="margin-bottom: 20px; font-size: 16px;">
                ${message}
            </p>
        `, [
            {
                text: 'Cancelar',
                class: 'button-secondary',
                onclick: `window.modalManager.hideModal()`
            },
            {
                text: 'Confirmar',
                class: 'button-danger',
                onclick: `window.modalManager.hideModal(); (${onConfirm.toString()})()`
            }
        ]);

        window.modalManager.showModal(modalId);
    }
}

// ===========================================
// FUNCIONES LEGACY PARA COMPATIBILIDAD
// ===========================================

// Funciones específicas del sistema de mensajes embebido migrado
function cerrarMensaje() {
    const mensaje = document.getElementById("mensaje-global");
    if (mensaje) {
        mensaje.classList.remove('show');
        mensaje.classList.add('fade-out');
        
        setTimeout(() => {
            mensaje.style.display = "none";
            limpiarParametrosURL();
        }, 300);
    }
}

function limpiarParametrosURL() {
    if (window.history && window.history.replaceState) {
        const url_obj = new URL(window.location.href);
        url_obj.searchParams.delete("mensaje");
        url_obj.searchParams.delete("tipo");
        window.history.replaceState({path: url_obj.toString()}, "", url_obj.toString());
    }
}

// Auto-hide específico para mensajes legacy
function initLegacyMessageSystem() {
    const alertElement = document.getElementById("mensaje-global");
    if (!alertElement) return;

    let tiempoRestante = 8000;
    const intervalo = 50;
    const progressBar = document.getElementById("progress-bar");
    
    const timer = setInterval(() => {
        tiempoRestante -= intervalo;
        const porcentaje = ((8000 - tiempoRestante) / 8000) * 100;
        
        if (progressBar) {
            progressBar.style.width = porcentaje + '%';
        }
        
        if (tiempoRestante <= 0) {
            clearInterval(timer);
            cerrarMensaje();
        }
    }, intervalo);
    
    // Pausar el timer al hacer hover
    alertElement.addEventListener('mouseenter', () => {
        clearInterval(timer);
        if (progressBar) {
            progressBar.style.animationPlayState = 'paused';
        }
    });
    
    alertElement.addEventListener('mouseleave', () => {
        // Reanudar desde donde se quedó
        const timer2 = setInterval(() => {
            tiempoRestante -= intervalo;
            const porcentaje = ((8000 - tiempoRestante) / 8000) * 100;
            
            if (progressBar) {
                progressBar.style.width = porcentaje + '%';
            }
            
            if (tiempoRestante <= 0) {
                clearInterval(timer2);
                cerrarMensaje();
            }
        }, intervalo);
    });
}

// ===========================================
// INICIALIZACIÓN
// ===========================================

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todos los sistemas de componentes
    window.messageSystem = new MessageSystem();
    window.breadcrumbManager = new BreadcrumbManager();
    window.tooltipManager = new TooltipManager();
    window.modalManager = new ModalManager();
    window.loadingManager = new LoadingManager();
    window.confirmationManager = new ConfirmationManager();

    // Inicializar sistema de mensajes legacy para compatibilidad
    initLegacyMessageSystem();

    // Funciones globales para compatibilidad
    window.showMessage = function(type, title, text, permanent = false) {
        return window.messageSystem.showMessage(type, title, text, permanent);
    };

    window.showLoading = function(button = null) {
        if (button) {
            window.loadingManager.showButtonLoading(button);
        } else {
            window.loadingManager.showPageLoading();
        }
    };

    window.hideLoading = function(button = null) {
        if (button) {
            window.loadingManager.hideButtonLoading(button);
        } else {
            window.loadingManager.hidePageLoading();
        }
    };

    // ===========================================
    // UTILIDADES ADICIONALES PARA MENSAJES
    // ===========================================

    // Funciones de conveniencia para mensajes comunes
    window.showSuccess = function(title, text, permanent = false) {
        return window.showMessage('success', title, text, permanent);
    };

    window.showError = function(title, text, permanent = false) {
        return window.showMessage('error', title, text, permanent);
    };

    window.showWarning = function(title, text, permanent = false) {
        return window.showMessage('warning', title, text, permanent);
    };

    window.showInfo = function(title, text, permanent = false) {
        return window.showMessage('info', title, text, permanent);
    };

    // Funciones para casos específicos
    window.showSaveSuccess = function(entityName = 'registro') {
        return window.showMessage('save', 'Guardado exitoso', `El ${entityName} se ha guardado correctamente`);
    };

    window.showDeleteSuccess = function(entityName = 'registro') {
        return window.showMessage('delete', 'Eliminación exitosa', `El ${entityName} se ha eliminado correctamente`);
    };

    window.showLoadingMessage = function(action = 'procesando') {
        return window.showMessage('loading', 'Procesando...', `Por favor espere mientras se está ${action}`, true);
    };

    // Función para limpiar todos los mensajes
    window.clearAllMessages = function() {
        const container = document.querySelector('.messages-container');
        if (container) {
            const messages = container.querySelectorAll('.subtle-message');
            messages.forEach(message => {
                if (window.messageSystem) {
                    window.messageSystem.hideMessage(message);
                }
            });
        }
    };

    console.log('Casa de Palos - Components System loaded successfully');
});