/**
 * JavaScript Principal - Casa de Palos Cabañas (Versión Moderna)
 * Archivo centralizado para todas las funciones JavaScript del sistema con diseño moderno
 */

// ===========================
// FUNCIONALIDADES MODERNAS
// ===========================

// Inicialización moderna al cargar DOM
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar componentes modernos
    initModernComponents();
    
    // Smooth scroll para navegación
    initSmoothScroll();
    
    // Animaciones de entrada
    initScrollAnimations();
    
    // Componentes interactivos
    initInteractiveComponents();
    
    // Mejoras de UX
    initUXEnhancements();
    
    // Inicializar funciones específicas de cabañas
    initCabaniasFunctions();
});

/**
 * Inicializar componentes modernos
 */
function initModernComponents() {
    // Navbar responsive con hamburger
    initModernNavbar();
    
    // Dropdowns modernos
    initModernDropdowns();
    
    // Botones con efectos
    initButtonEffects();
    
    // Cards con hover effects
    initCardEffects();
    
    // Progress bars animadas
    initAnimatedProgress();
}

/**
 * Navbar moderno con animaciones
 */
function initModernNavbar() {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener('click', function() {
            navbarCollapse.classList.toggle('show');
            
            // Animar hamburger
            const spans = this.querySelectorAll('span');
            spans.forEach((span, index) => {
                span.style.transition = 'all 0.3s ease';
                if (navbarCollapse.classList.contains('show')) {
                    if (index === 0) span.style.transform = 'rotate(45deg) translate(5px, 5px)';
                    if (index === 1) span.style.opacity = '0';
                    if (index === 2) span.style.transform = 'rotate(-45deg) translate(7px, -6px)';
                } else {
                    span.style.transform = 'none';
                    span.style.opacity = '1';
                }
            });
        });
        
        // Cerrar menú al hacer click fuera
        document.addEventListener('click', function(e) {
            if (!navbarToggler.contains(e.target) && !navbarCollapse.contains(e.target)) {
                navbarCollapse.classList.remove('show');
                // Reset hamburger
                const spans = navbarToggler.querySelectorAll('span');
                spans.forEach(span => {
                    span.style.transform = 'none';
                    span.style.opacity = '1';
                });
            }
        });
    }
}

/**
 * Dropdowns modernos con animaciones
 */
function initModernDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (toggle && menu) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Cerrar otros dropdowns
                document.querySelectorAll('.dropdown.show').forEach(other => {
                    if (other !== dropdown) {
                        other.classList.remove('show');
                    }
                });
                
                // Toggle current dropdown
                dropdown.classList.toggle('show');
                
                // Animar flecha
                const arrow = toggle.querySelector('.dropdown-arrow');
                if (arrow) {
                    arrow.style.transition = 'transform 0.2s ease';
                    arrow.style.transform = dropdown.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
                }
                
                // Animar menú
                if (dropdown.classList.contains('show')) {
                    menu.style.opacity = '0';
                    menu.style.transform = 'translateY(-10px)';
                    menu.style.display = 'block';
                    
                    requestAnimationFrame(() => {
                        menu.style.transition = 'all 0.2s ease';
                        menu.style.opacity = '1';
                        menu.style.transform = 'translateY(0)';
                    });
                } else {
                    menu.style.transition = 'all 0.2s ease';
                    menu.style.opacity = '0';
                    menu.style.transform = 'translateY(-10px)';
                    
                    setTimeout(() => {
                        if (!dropdown.classList.contains('show')) {
                            menu.style.display = 'none';
                        }
                    }, 200);
                }
            });
        }
    });
    
    // Cerrar dropdowns al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown.show').forEach(dropdown => {
                dropdown.classList.remove('show');
                
                const arrow = dropdown.querySelector('.dropdown-arrow');
                if (arrow) {
                    arrow.style.transform = 'rotate(0deg)';
                }
                
                const menu = dropdown.querySelector('.dropdown-menu');
                if (menu) {
                    menu.style.opacity = '0';
                    menu.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        menu.style.display = 'none';
                    }, 200);
                }
            });
        }
    });
}

/**
 * Efectos modernos para botones
 */
function initButtonEffects() {
    // Ripple effect para botones
    document.querySelectorAll('.btn, .nav-link, .dropdown-item').forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
}

/**
 * Efectos para cards
 */
function initCardEffects() {
    // Hover effects para cards
    document.querySelectorAll('.feature-card, .card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 15px 35px rgba(0, 0, 0, 0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
}

/**
 * Progress bars animadas
 */
function initAnimatedProgress() {
    const progressBars = document.querySelectorAll('.progress-bar');
    
    const animateProgressBar = (bar) => {
        const width = bar.style.width;
        bar.style.width = '0%';
        bar.style.transition = 'width 2s ease-in-out';
        
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    };
    
    // Intersection Observer para animar cuando entran en viewport
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateProgressBar(entry.target);
                observer.unobserve(entry.target);
            }
        });
    });
    
    progressBars.forEach(bar => {
        observer.observe(bar);
    });
}

/**
 * Smooth scroll para navegación
 */
function initSmoothScroll() {
    // Smooth scroll para enlaces internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Animaciones cuando los elementos entran en viewport
 */
function initScrollAnimations() {
    const animatedElements = document.querySelectorAll('.feature-card, .hero-content, .section-header');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease-out';
        observer.observe(el);
    });
}

/**
 * Componentes interactivos modernos
 */
function initInteractiveComponents() {
    // Auto-resize para textareas
    document.querySelectorAll('textarea').forEach(textarea => {
        const autoResize = () => {
            textarea.style.height = 'auto';
            textarea.style.height = Math.max(textarea.scrollHeight, 100) + 'px';
        };
        
        textarea.addEventListener('input', autoResize);
        autoResize(); // Inicial
    });
    
    // Focus effects para inputs
    document.querySelectorAll('.form-control').forEach(input => {
        const label = input.previousElementSibling;
        
        const addFocusEffect = () => {
            if (label) label.style.color = 'var(--primary-500)';
            input.style.borderColor = 'var(--primary-500)';
            input.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
        };
        
        const removeFocusEffect = () => {
            if (label) label.style.color = '';
            input.style.borderColor = '';
            input.style.boxShadow = '';
        };
        
        input.addEventListener('focus', addFocusEffect);
        input.addEventListener('blur', removeFocusEffect);
    });
}

/**
 * Mejoras de experiencia de usuario
 */
function initUXEnhancements() {
    // Loading states para formularios
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                const originalText = submitBtn.innerHTML || submitBtn.value;
                submitBtn.disabled = true;
                
                if (submitBtn.tagName === 'BUTTON') {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                } else {
                    submitBtn.value = 'Procesando...';
                }
                
                // Restaurar después de 10 segundos por si hay error
                setTimeout(() => {
                    submitBtn.disabled = false;
                    if (submitBtn.tagName === 'BUTTON') {
                        submitBtn.innerHTML = originalText;
                    } else {
                        submitBtn.value = originalText;
                    }
                }, 10000);
            }
        });
    });
    
    // Confirmaciones modernas
    document.querySelectorAll('[data-confirm]').forEach(element => {
        element.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || '¿Está seguro de continuar?';
            
            e.preventDefault();
            
            // Crear modal de confirmación moderno
            showModernConfirm(message, () => {
                // Confirmar acción
                if (this.tagName === 'A') {
                    window.location.href = this.href;
                } else if (this.form) {
                    this.form.submit();
                }
            });
        });
    });
    
    // Auto-save para formularios largos (opcional)
    const autoSaveForms = document.querySelectorAll('[data-autosave]');
    autoSaveForms.forEach(form => {
        const formData = new FormData(form);
        const savedKey = `autosave_${window.location.pathname}`;
        
        // Cargar datos guardados
        const savedData = localStorage.getItem(savedKey);
        if (savedData) {
            try {
                const data = JSON.parse(savedData);
                Object.keys(data).forEach(key => {
                    const input = form.querySelector(`[name="${key}"]`);
                    if (input && input.type !== 'password') {
                        input.value = data[key];
                    }
                });
            } catch (e) {
                console.warn('Error loading autosaved data:', e);
            }
        }
        
        // Auto-guardar cada 30 segundos
        setInterval(() => {
            const formData = new FormData(form);
            const data = {};
            for (let [key, value] of formData.entries()) {
                if (form.querySelector(`[name="${key}"]`).type !== 'password') {
                    data[key] = value;
                }
            }
            localStorage.setItem(savedKey, JSON.stringify(data));
        }, 30000);
        
        // Limpiar al enviar
        form.addEventListener('submit', () => {
            localStorage.removeItem(savedKey);
        });
    });
}

/**
 * Modal de confirmación moderno
 */
function showModernConfirm(message, onConfirm, onCancel = null) {
    // Crear modal
    const modal = document.createElement('div');
    modal.className = 'modern-confirm-modal';
    modal.innerHTML = `
        <div class="modern-confirm-backdrop"></div>
        <div class="modern-confirm-content">
            <div class="modern-confirm-header">
                <i class="fas fa-question-circle"></i>
                <h3>Confirmación</h3>
            </div>
            <div class="modern-confirm-body">
                <p>${message}</p>
            </div>
            <div class="modern-confirm-actions">
                <button class="btn btn-secondary cancel-btn">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button class="btn btn-primary confirm-btn">
                    <i class="fas fa-check"></i> Confirmar
                </button>
            </div>
        </div>
    `;
    
    // Estilos inline para el modal
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;
    
    const backdrop = modal.querySelector('.modern-confirm-backdrop');
    backdrop.style.cssText = `
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
    `;
    
    const content = modal.querySelector('.modern-confirm-content');
    content.style.cssText = `
        background: white;
        border-radius: 12px;
        padding: 2rem;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        transform: scale(0.9);
        transition: transform 0.3s ease;
        position: relative;
        z-index: 1;
    `;
    
    // Agregar al DOM
    document.body.appendChild(modal);
    
    // Animar entrada
    requestAnimationFrame(() => {
        modal.style.opacity = '1';
        content.style.transform = 'scale(1)';
    });
    
    // Event listeners
    const confirmBtn = modal.querySelector('.confirm-btn');
    const cancelBtn = modal.querySelector('.cancel-btn');
    
    const closeModal = () => {
        modal.style.opacity = '0';
        content.style.transform = 'scale(0.9)';
        setTimeout(() => {
            document.body.removeChild(modal);
        }, 300);
    };
    
    confirmBtn.addEventListener('click', () => {
        closeModal();
        if (onConfirm) onConfirm();
    });
    
    cancelBtn.addEventListener('click', () => {
        closeModal();
        if (onCancel) onCancel();
    });
    
    backdrop.addEventListener('click', () => {
        closeModal();
        if (onCancel) onCancel();
    });
    
    // Cerrar con ESC
    const escHandler = (e) => {
        if (e.key === 'Escape') {
            closeModal();
            if (onCancel) onCancel();
            document.removeEventListener('keydown', escHandler);
        }
    };
    document.addEventListener('keydown', escHandler);
}

/**
 * Notificación toast moderna
 */
function showModernToast(message, type = 'info', duration = 5000) {
    const toast = document.createElement('div');
    toast.className = `modern-toast toast-${type}`;
    
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-times-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };
    
    toast.innerHTML = `
        <div class="toast-content">
            <i class="${icons[type] || icons.info}"></i>
            <span>${message}</span>
        </div>
        <button class="toast-close">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Estilos
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: 8px;
        padding: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border-left: 4px solid var(--${type === 'error' ? 'error' : type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'}-500);
        max-width: 400px;
        z-index: 10000;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 12px;
    `;
    
    document.body.appendChild(toast);
    
    // Animar entrada
    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    });
    
    // Cerrar automáticamente
    const autoClose = setTimeout(() => {
        closeToast();
    }, duration);
    
    // Función para cerrar
    const closeToast = () => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 300);
        clearTimeout(autoClose);
    };
    
    // Botón cerrar
    toast.querySelector('.toast-close').addEventListener('click', closeToast);
    
    // Pausar en hover
    toast.addEventListener('mouseenter', () => clearTimeout(autoClose));
    toast.addEventListener('mouseleave', () => {
        setTimeout(closeToast, 2000);
    });
}

// ===========================
// CONFIGURACIÓN GLOBAL SUTIL PARA SWEETALERT2
// ===========================

/**
 * Configuración por defecto más sutil para SweetAlert2
 */
window.SwalDefaults = {
    // Colores sutiles y modernos
    confirmButtonColor: '#4f46e5',
    cancelButtonColor: '#6b7280',
    
    // Fuente y tamaños más sutiles
    customClass: {
        popup: 'swal2-subtle-popup',
        header: 'swal2-subtle-header',
        title: 'swal2-subtle-title',
        content: 'swal2-subtle-content',
        actions: 'swal2-subtle-actions',
        confirmButton: 'swal2-subtle-confirm',
        cancelButton: 'swal2-subtle-cancel'
    },
    
    // Animación más suave
    showClass: {
        popup: 'animate__animated animate__fadeInUp animate__faster'
    },
    hideClass: {
        popup: 'animate__animated animate__fadeOutDown animate__faster'
    },
    
    // Configuraciones por defecto
    allowOutsideClick: true,
    allowEscapeKey: true,
    backdrop: true,
    
    // Iconos más sutiles
    iconColor: '#6b7280'
};

/**
 * Función wrapper para SweetAlert con configuración sutil
 */
window.SwalSutil = function(config) {
    // Combinar configuración por defecto con la personalizada
    const finalConfig = { ...SwalDefaults, ...config };
    return Swal.fire(finalConfig);
};

/**
 * Presets específicos para diferentes tipos de alertas
 */
window.SwalPresets = {
    // Confirmación sutil
    confirm: (title, text, callback) => {
        return SwalSutil({
            title: title || '¿Confirmar acción?',
            text: text || '¿Está seguro de continuar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar',
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed && callback) {
                callback();
            }
        });
    },
    
    // Éxito sutil
    success: (title, text, timer = 2000) => {
        return SwalSutil({
            title: title || '¡Éxito!',
            text: text || 'La operación se completó correctamente',
            icon: 'success',
            timer: timer,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    },
    
    // Error sutil
    error: (title, text) => {
        return SwalSutil({
            title: title || 'Error',
            text: text || 'Ocurrió un error inesperado',
            icon: 'error',
            confirmButtonText: 'Entendido'
        });
    },
    
    // Advertencia sutil
    warning: (title, text) => {
        return SwalSutil({
            title: title || 'Atención',
            text: text || 'Por favor revise la información',
            icon: 'warning',
            confirmButtonText: 'Entendido'
        });
    },
    
    // Información sutil
    info: (title, text) => {
        return SwalSutil({
            title: title || 'Información',
            text: text || '',
            icon: 'info',
            confirmButtonText: 'Entendido'
        });
    },
    
    // Loading sutil
    loading: (title, text) => {
        return SwalSutil({
            title: title || 'Procesando...',
            text: text || 'Por favor espere',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    },
    
    // Toast sutil (notificación discreta)
    toast: (title, icon = 'success', timer = 3000) => {
        return SwalSutil({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: title,
            showConfirmButton: false,
            timer: timer,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    }
};

// Agregar CSS para efectos modernos
const modernStyles = document.createElement('style');
modernStyles.textContent = `
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        pointer-events: none;
        animation: ripple-effect 0.6s linear;
    }
    
    @keyframes ripple-effect {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    /* Estilos sutiles para SweetAlert2 */
    .swal2-subtle-popup {
        border-radius: 16px !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
        border: 1px solid rgba(0, 0, 0, 0.05) !important;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    }
    
    .swal2-subtle-title {
        font-size: 1.25rem !important;
        font-weight: 600 !important;
        color: #374151 !important;
        margin-bottom: 0.5rem !important;
    }
    
    .swal2-subtle-content {
        color: #6b7280 !important;
        font-size: 0.9rem !important;
        line-height: 1.5 !important;
    }
    
    .swal2-subtle-confirm {
        background: linear-gradient(135deg, #4f46e5, #6366f1) !important;
        border: none !important;
        border-radius: 8px !important;
        font-weight: 500 !important;
        font-size: 0.875rem !important;
        padding: 10px 20px !important;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3) !important;
        transition: all 0.2s ease !important;
    }
    
    .swal2-subtle-confirm:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4) !important;
    }
    
    .swal2-subtle-cancel {
        background: #f3f4f6 !important;
        color: #6b7280 !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 8px !important;
        font-weight: 500 !important;
        font-size: 0.875rem !important;
        padding: 10px 20px !important;
        transition: all 0.2s ease !important;
    }
    
    .swal2-subtle-cancel:hover {
        background: #e5e7eb !important;
        color: #374151 !important;
        transform: translateY(-1px) !important;
    }
    
    .swal2-subtle-actions {
        gap: 12px !important;
        margin-top: 1.5rem !important;
    }
    
    /* Toast sutiles */
    .swal2-toast.swal2-show {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(0, 0, 0, 0.1) !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12) !important;
    }
    
    .swal2-toast .swal2-title {
        font-size: 0.875rem !important;
        color: #374151 !important;
    }
    
    /* Iconos más sutiles */
    .swal2-icon.swal2-success [class^="swal2-success-line"] {
        background-color: #10b981 !important;
    }
    
    .swal2-icon.swal2-success .swal2-success-ring {
        border-color: #10b981 !important;
    }
    
    .swal2-icon.swal2-error [class^="swal2-x-mark-line"] {
        background-color: #ef4444 !important;
    }
    
    .swal2-icon.swal2-warning {
        color: #f59e0b !important;
    }
    
    .swal2-icon.swal2-info {
        color: #3b82f6 !important;
    }
    
    .swal2-icon.swal2-question {
        color: #6366f1 !important;
    }
    
    .modern-confirm-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        color: var(--secondary-700);
    }
    
    .modern-confirm-header i {
        font-size: 1.5rem;
        color: var(--primary-500);
    }
    
    .modern-confirm-header h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
    }
    
    .modern-confirm-body p {
        color: var(--secondary-600);
        margin-bottom: 2rem;
        line-height: 1.5;
    }
    
    .modern-confirm-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }
    
    .modern-toast .toast-content {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
    }
    
    .modern-toast .toast-close {
        background: none;
        border: none;
        color: var(--secondary-400);
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: background 0.2s ease;
    }
    
    .modern-toast .toast-close:hover {
        background: var(--secondary-100);
        color: var(--secondary-600);
    }
`;
document.head.appendChild(modernStyles);

// ========== COMENTARIOS - FUNCIONALIDADES ==========

// Filtros de comentarios
function limpiarFiltros() {
    const form = document.querySelector('.search-container form');
    if (form) {
        form.querySelectorAll('input[type="text"], input[type="number"], select').forEach(element => {
            if (element.type === 'text' || element.type === 'number') {
                element.value = '';
            } else if (element.type === 'select-one') {
                element.selectedIndex = 0;
            }
        });
        form.submit();
    }
}

// Confirmación de acciones
function confirmarAccion(url, mensaje) {
    if (confirm(`¿Está seguro que desea ${mensaje}?`)) {
        window.location.href = url;
    }
}

// Confirmación de eliminación específica
function confirmarEliminacion(id) {
    if (confirm('¿Está seguro que desea eliminar este comentario?')) {
        window.location.href = `/proyecto_cabania/comentarios/${id}/delete`;
    }
}

// Confirmación de recuperación específica
function confirmarRecuperacion(id) {
    if (confirm('¿Está seguro que desea recuperar este comentario?')) {
        window.location.href = `/proyecto_cabania/comentarios/${id}/restore`;
    }
}

// Event listeners para comentarios
document.addEventListener('DOMContentLoaded', function() {
    // Botón limpiar filtros
    const clearButton = document.querySelector('[data-action="clear-filters"]');
    if (clearButton) {
        clearButton.addEventListener('click', limpiarFiltros);
    }

    // Botones de editar
    document.querySelectorAll('[data-action="edit"]').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            window.location.href = `/proyecto_cabania/comentarios/${id}/edit`;
        });
    });

    // Botones de eliminar
    document.querySelectorAll('[data-action="delete"]').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const message = this.getAttribute('data-message') || 'eliminar este comentario';
            if (confirm(`¿Está seguro que desea ${message}?`)) {
                window.location.href = `/proyecto_cabania/comentarios/${id}/delete`;
            }
        });
    });

    // Botones de restaurar
    document.querySelectorAll('[data-action="restore"]').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const message = this.getAttribute('data-message') || 'recuperar este comentario';
            if (confirm(`¿Está seguro que desea ${message}?`)) {
                window.location.href = `/proyecto_cabania/comentarios/${id}/restore`;
            }
        });
    });

    // Botones de navegación
    document.querySelectorAll('[data-action="navigate"]').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            if (url) {
                window.location.href = url;
            }
        });
    });

    // Confirmación de eliminación con data-action
    document.querySelectorAll('[data-action="confirm-delete"]').forEach(link => {
        link.addEventListener('click', function(e) {
            const message = this.getAttribute('data-message') || '¿Está seguro?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Auto-submit para selectores
    document.querySelectorAll('[data-action="auto-submit"]').forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
});

// ========== CONDICIONES SALUD - ESTADÍSTICAS ==========

// Función para inicializar las estadísticas de condiciones de salud
function initCondicionesSaludStats(statsData, agrupacionData, topCondicionesData) {
    // Gráfico de estado de condiciones (pie chart)
    const ctxEstadoCondiciones = document.getElementById('chartEstadoCondiciones').getContext('2d');
    new Chart(ctxEstadoCondiciones, {
        type: 'doughnut',
        data: {
            labels: ['Activas', 'Inactivas'],
            datasets: [{
                data: [statsData.activas, statsData.inactivas],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Gráfico de distribución alfabética
    const ctxDistribucionAlfabetica = document.getElementById('chartDistribucionAlfabetica').getContext('2d');
    new Chart(ctxDistribucionAlfabetica, {
        type: 'bar',
        data: {
            labels: agrupacionData.map(item => item.letra),
            datasets: [{
                label: 'Cantidad de Condiciones',
                data: agrupacionData.map(item => item.cantidad),
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Condiciones: ' + context.parsed.y;
                        }
                    }
                }
            }
        }
    });

    // Gráfico de condiciones más utilizadas
    const ctxCondicionesMasUtilizadas = document.getElementById('chartCondicionesMasUtilizadas').getContext('2d');
    new Chart(ctxCondicionesMasUtilizadas, {
        type: 'horizontalBar',
        data: {
            labels: topCondicionesData.map(item => {
                const desc = item.condicionsalud_descripcion;
                return desc.length > 25 ? desc.substring(0, 25) + '...' : desc;
            }),
            datasets: [{
                label: 'Huéspedes Asignados',
                data: topCondicionesData.map(item => item.uso_count),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(199, 199, 199, 0.8)',
                    'rgba(83, 102, 255, 0.8)',
                    'rgba(255, 99, 255, 0.8)',
                    'rgba(99, 255, 132, 0.8)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 205, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(199, 199, 199, 1)',
                    'rgba(83, 102, 255, 1)',
                    'rgba(255, 99, 255, 1)',
                    'rgba(99, 255, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            const index = context[0].dataIndex;
                            return topCondicionesData[index].condicionsalud_descripcion;
                        },
                        label: function(context) {
                            return 'Huéspedes: ' + context.parsed.x;
                        }
                    }
                }
            }
        }
    });

    // Efecto de animación en las tarjetas
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// ========== CONSUMOS - FUNCIONALIDADES ==========

// Funciones de confirmación para consumos
function confirmarEliminacion(id) {
    if (confirm('¿Está seguro de eliminar este consumo? Esta acción no se puede deshacer.')) {
        window.location.href = '/consumos/' + id + '/delete';
    }
}

function confirmarRestaurar(id) {
    if (confirm('¿Está seguro de restaurar este consumo?')) {
        window.location.href = '/consumos/' + id + '/restore';
    }
}

// Event listeners específicos para consumos
document.addEventListener('DOMContentLoaded', function() {
    // Botones de eliminar consumo
    document.querySelectorAll('[data-action="delete"]').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            if (confirm('¿Está seguro de eliminar este consumo? Esta acción no se puede deshacer.')) {
                window.location.href = '/consumos/' + id + '/delete';
            }
        });
    });

    // Botones de restaurar consumo  
    document.querySelectorAll('[data-action="restore"]').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            if (confirm('¿Está seguro de restaurar este consumo?')) {
                window.location.href = '/consumos/' + id + '/restore';
            }
        });
    });

    // Auto-submit para filtros de consumos
    document.querySelectorAll('.filters-form select').forEach(select => {
        select.addEventListener('change', function() {
            // Opcional: auto-submit cuando cambian los selectores
            // this.form.submit();
        });
    });
});

// Inicialización del formulario de consumos
function initConsumoForm() {
    // Elementos del formulario
    const reservaSelect = document.getElementById('rela_reserva');
    const productoSelect = document.getElementById('rela_producto');
    const cantidadInput = document.getElementById('consumo_cantidad');
    const precioInput = document.getElementById('consumo_precio_unitario');
    const subtotalInput = document.getElementById('consumo_subtotal');
    const stockInput = document.getElementById('stock_disponible');
    const observacionesTextarea = document.getElementById('consumo_observaciones');
    const obsCounter = document.getElementById('obs_counter');
    
    // Información de la reserva
    const reservaInfo = document.getElementById('reservaInfo');
    const infoHuesped = document.getElementById('infoHuesped');
    const infoCabania = document.getElementById('infoCabania');

    if (!reservaSelect || !productoSelect) return; // No es página de formulario

    // Mostrar información de la reserva seleccionada
    function actualizarInfoReserva() {
        const selected = reservaSelect.selectedOptions[0];
        if (selected && selected.value) {
            const huesped = selected.dataset.huesped || '-';
            const cabania = selected.dataset.cabania || '-';
            
            infoHuesped.textContent = huesped;
            infoCabania.textContent = cabania;
            reservaInfo.style.display = 'block';
        } else {
            reservaInfo.style.display = 'none';
        }
    }

    // Actualizar precio y stock cuando se selecciona un producto
    function actualizarProductoInfo() {
        const selected = productoSelect.selectedOptions[0];
        if (selected && selected.value) {
            const precio = parseFloat(selected.dataset.precio) || 0;
            const stock = parseInt(selected.dataset.stock) || 0;
            
            precioInput.value = precio.toFixed(2);
            stockInput.value = stock;
            
            // Validar cantidad máxima
            cantidadInput.max = stock;
            
            calcularSubtotal();
        } else {
            precioInput.value = '';
            stockInput.value = '';
            cantidadInput.max = '';
            subtotalInput.value = '';
        }
    }

    // Calcular subtotal automáticamente
    function calcularSubtotal() {
        const cantidad = parseFloat(cantidadInput.value) || 0;
        const precio = parseFloat(precioInput.value) || 0;
        const subtotal = cantidad * precio;
        
        subtotalInput.value = subtotal.toFixed(2);
        
        // Actualizar estilo visual
        if (subtotal > 0) {
            subtotalInput.classList.add('calculated');
        } else {
            subtotalInput.classList.remove('calculated');
        }
    }

    // Contador de caracteres para observaciones
    function actualizarContador() {
        const longitud = observacionesTextarea.value.length;
        obsCounter.textContent = longitud;
        
        if (longitud > 500) {
            obsCounter.style.color = '#dc3545';
            observacionesTextarea.value = observacionesTextarea.value.substring(0, 500);
            obsCounter.textContent = '500';
        } else if (longitud > 450) {
            obsCounter.style.color = '#ffc107';
        } else {
            obsCounter.style.color = '#6c757d';
        }
    }

    // Validación de stock
    function validarStock() {
        const cantidad = parseInt(cantidadInput.value) || 0;
        const stockDisponible = parseInt(stockInput.value) || 0;
        
        if (cantidad > stockDisponible && stockDisponible > 0) {
            cantidadInput.setCustomValidity(`La cantidad no puede superar el stock disponible (${stockDisponible})`);
            cantidadInput.classList.add('is-invalid');
        } else {
            cantidadInput.setCustomValidity('');
            cantidadInput.classList.remove('is-invalid');
        }
    }

    // Event listeners
    reservaSelect.addEventListener('change', actualizarInfoReserva);
    productoSelect.addEventListener('change', actualizarProductoInfo);
    cantidadInput.addEventListener('input', function() {
        validarStock();
        calcularSubtotal();
    });
    precioInput.addEventListener('input', calcularSubtotal);
    observacionesTextarea.addEventListener('input', actualizarContador);

    // Inicializar contadores y valores
    actualizarInfoReserva();
    actualizarProductoInfo();
    actualizarContador();
    calcularSubtotal();

    // Validación del formulario
    const form = document.getElementById('consumoForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            validarStock();
            
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            this.classList.add('was-validated');
        });
    }
}

// Auto-inicializar el formulario de consumos
document.addEventListener('DOMContentLoaded', initConsumoForm);

// ===========================
// DOCUMENTACIÓN DE ALERTAS SUTILES
// ===========================

/**
 * GUÍA DE USO DE ALERTAS SUTILES
 * 
 * Este sistema reemplaza las alertas tradicionales con versiones más elegantes y sutiles.
 * 
 * USO BÁSICO:
 * 
 * 1. Confirmaciones simples:
 *    SwalPresets.confirm('¿Confirmar acción?', 'Descripción opcional', callback);
 * 
 * 2. Mensajes de éxito:
 *    SwalPresets.success('¡Éxito!', 'Operación completada');
 *    SwalPresets.toast('Guardado correctamente', 'success');
 * 
 * 3. Errores:
 *    SwalPresets.error('Error', 'Descripción del error');
 * 
 * 4. Warnings:
 *    SwalPresets.warning('Atención', 'Revise la información');
 * 
 * 5. Loading/Procesando:
 *    SwalPresets.loading('Procesando', 'Por favor espere...');
 * 
 * UTILIDADES PARA CRUDs:
 * 
 * 1. Cambiar estado:
 *    CrudUtils.changeStatus(id, newStatus, 'entidad', '/endpoint');
 * 
 * 2. Eliminar:
 *    CrudUtils.delete(id, 'entidad', '/endpoint');
 * 
 * 3. Éxito con recarga:
 *    AlertUtils.successWithReload('Mensaje de éxito', 1500);
 * 
 * EJEMPLOS PRÁCTICOS:
 * 
 * // Cambiar estado de una categoría
 * function cambiarEstado(id, nuevoEstado) {
 *     CrudUtils.changeStatus(id, nuevoEstado, 'categoría', '/categorias');
 * }
 * 
 * // Confirmación personalizada
 * function eliminarElemento(id) {
 *     SwalPresets.confirm(
 *         '¿Eliminar elemento?',
 *         'Esta acción no se puede deshacer',
 *         () => {
 *             // Lógica de eliminación aquí
 *             AlertUtils.successWithReload('Elemento eliminado');
 *         }
 *     );
 * }
 * 
 * // Toast discreto
 * function guardarFormulario() {
 *     // ... lógica de guardado
 *     SwalPresets.toast('Formulario guardado', 'success', 2000);
 * }
 * 
 * CARACTERÍSTICAS:
 * 
 * ✓ Diseño moderno y sutil (esquinas redondeadas, sombras suaves)
 * ✓ Colores menos saturados y más elegantes
 * ✓ Animaciones suaves de entrada y salida
 * ✓ Toast notifications discretas en la esquina
 * ✓ Loading spinners menos intrusivos
 * ✓ Consistencia visual en todo el proyecto
 * ✓ Responsive y accesible
 * 
 * MIGRACIÓN DESDE ALERTAS TRADICIONALES:
 * 
 * Reemplazar:
 * - confirm() → SwalPresets.confirm()
 * - alert() → SwalPresets.info() o SwalPresets.toast()
 * - Swal.fire() básico → usar presets correspondientes
 */

/**
 * Función para mostrar mensajes de confirmación
 */
function confirmarAccion(mensaje = '¿Está seguro de realizar esta acción?') {
    return confirm(mensaje);
}

/**
 * Función para eliminar un registro con confirmación
 */
function eliminarRegistro(url, mensaje = '¿Está seguro de que desea eliminar este registro?') {
    if (confirmarAccion(mensaje)) {
        window.location.href = url;
    }
}

/**
 * Función para mostrar/ocultar elementos
 */
function toggleElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.style.display = element.style.display === 'none' ? 'block' : 'none';
    }
}

/**
 * Función para validar formularios
 */
function validarFormulario(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const campos = form.querySelectorAll('[required]');
    let valido = true;
    
    campos.forEach(campo => {
        if (!campo.value.trim()) {
            campo.style.borderColor = '#dc3545';
            valido = false;
        } else {
            campo.style.borderColor = '#28a745';
        }
    });
    
    return valido;
}

// ===========================
// FUNCIONES DE FORMULARIOS
// ===========================

/**
 * Auto-submit de formularios de filtros
 */
function autoSubmitFilter(element) {
    element.form.submit();
}

/**
 * Limpiar filtros de búsqueda
 */
function limpiarFiltros(formId) {
    const form = document.getElementById(formId);
    if (form) {
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (input.type === 'text' || input.type === 'search' || input.type === 'email') {
                input.value = '';
            } else if (input.type === 'select-one') {
                input.selectedIndex = 0;
            }
        });
        form.submit();
    }
}

// ===========================
// FUNCIONES DE TABLAS
// ===========================

/**
 * Ordenar tabla por columna
 */
function ordenarTabla(tabla, columna, direccion = 'asc') {
    const tbody = tabla.querySelector('tbody');
    const filas = Array.from(tbody.querySelectorAll('tr'));
    
    filas.sort((a, b) => {
        const valorA = a.cells[columna].textContent.trim();
        const valorB = b.cells[columna].textContent.trim();
        
        if (direccion === 'asc') {
            return valorA.localeCompare(valorB);
        } else {
            return valorB.localeCompare(valorA);
        }
    });
    
    filas.forEach(fila => tbody.appendChild(fila));
}

/**
 * Filtrar tabla en tiempo real
 */
function filtrarTabla(inputId, tablaId) {
    const input = document.getElementById(inputId);
    const tabla = document.getElementById(tablaId);
    
    if (!input || !tabla) return;
    
    input.addEventListener('keyup', function() {
        const filtro = this.value.toLowerCase();
        const filas = tabla.getElementsByTagName('tr');
        
        for (let i = 1; i < filas.length; i++) { // Empezar en 1 para saltar el header
            let mostrar = false;
            const celdas = filas[i].getElementsByTagName('td');
            
            for (let j = 0; j < celdas.length; j++) {
                if (celdas[j].textContent.toLowerCase().indexOf(filtro) > -1) {
                    mostrar = true;
                    break;
                }
            }
            
            filas[i].style.display = mostrar ? '' : 'none';
        }
    });
}

// ===========================
// FUNCIONES DE UI
// ===========================

/**
 * Mostrar/ocultar loading spinner
 */
function toggleLoading(show = true, mensaje = 'Cargando...') {
    let loader = document.getElementById('loading-overlay');
    
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'loading-overlay';
        loader.innerHTML = `
            <div class="loading-content">
                <div class="spinner"></div>
                <p>${mensaje}</p>
            </div>
        `;
        document.body.appendChild(loader);
    }
    
    loader.style.display = show ? 'flex' : 'none';
}

/**
 * Mostrar notificaciones toast
 */
function mostrarToast(mensaje, tipo = 'info', duracion = 3000) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.textContent = mensaje;
    
    // Posicionar el toast
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.style.padding = '15px 20px';
    toast.style.borderRadius = '5px';
    toast.style.color = 'white';
    toast.style.fontWeight = 'bold';
    
    // Colores según el tipo
    switch(tipo) {
        case 'success':
            toast.style.backgroundColor = '#28a745';
            break;
        case 'error':
            toast.style.backgroundColor = '#dc3545';
            break;
        case 'warning':
            toast.style.backgroundColor = '#ffc107';
            toast.style.color = '#212529';
            break;
        default:
            toast.style.backgroundColor = '#17a2b8';
    }
    
    document.body.appendChild(toast);
    
    // Remover después de la duración especificada
    setTimeout(() => {
        toast.remove();
    }, duracion);
}

// ===========================
// FUNCIONES DE VALIDACIÓN
// ===========================

/**
 * Validar email
 */
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Validar teléfono
 */
function validarTelefono(telefono) {
    const regex = /^[0-9+\-\s()]+$/;
    return regex.test(telefono);
}

/**
 * Validar fecha
 */
function validarFecha(fecha) {
    const regex = /^\d{4}-\d{2}-\d{2}$/;
    return regex.test(fecha);
}

// ===========================
// FUNCIONES DE GRÁFICOS
// ===========================

/**
 * Configuración base para gráficos Chart.js
 */
function getChartBaseConfig() {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                titleColor: 'white',
                bodyColor: 'white',
                borderColor: 'rgba(255,255,255,0.1)',
                borderWidth: 1
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.1)'
                }
            },
            x: {
                grid: {
                    color: 'rgba(0,0,0,0.1)'
                }
            }
        }
    };
}

/**
 * Crear gráfico de barras
 */
function crearGraficoBarras(canvasId, datos, opciones = {}) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    const config = {
        type: 'bar',
        data: datos,
        options: {
            ...getChartBaseConfig(),
            ...opciones
        }
    };
    
    return new Chart(ctx, config);
}

/**
 * Crear gráfico de líneas
 */
function crearGraficoLineas(canvasId, datos, opciones = {}) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    const config = {
        type: 'line',
        data: datos,
        options: {
            ...getChartBaseConfig(),
            ...opciones
        }
    };
    
    return new Chart(ctx, config);
}

/**
 * Crear gráfico de dona
 */
function crearGraficoDona(canvasId, datos, opciones = {}) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    const config = {
        type: 'doughnut',
        data: datos,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                }
            },
            ...opciones
        }
    };
    
    return new Chart(ctx, config);
}

// ===========================
// INICIALIZACIÓN
// ===========================

/**
 * Funciones que se ejecutan cuando el DOM está listo
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips si existe Bootstrap
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Auto-focus en primer input de formularios
    const primerInput = document.querySelector('input[type="text"], input[type="email"], input[type="password"]');
    if (primerInput) {
        primerInput.focus();
    }
    
    // Confirmar eliminaciones automáticamente
    document.querySelectorAll('.btn-delete, .eliminar').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirmarAccion('¿Está seguro de que desea eliminar este elemento?')) {
                e.preventDefault();
            }
        });
    });
    
    // Inicializar validaciones específicas
    if (document.getElementById('tiposervicio_descripcion')) {
        setupTipoServicioValidation();
    }
    if (document.getElementById('tipocontacto_descripcion')) {
        setupTipoContactoValidation();
    }
    if (document.getElementById('metodoPagoForm')) {
        setupMetodoPagoValidation();
    }
    if (document.getElementById('estadoreserva_descripcion')) {
        setupEstadoReservaValidation();
    }
    if (document.getElementById('condicionsalud_descripcion')) {
        setupCondicionSaludValidation();
    }
    if (document.getElementById('formSalida')) {
        setupSalidaValidation();
    }
    if (document.getElementById('periodo_descripcion')) {
        setupPeriodoValidation();
    }
    
    // Inicializar auto-refresh para listados si es necesario
    setupAutoRefresh();
});

// ===========================
// FUNCIONES DE AJAX
// ===========================

/**
 * Realizar petición AJAX simple
 */
function ajax(url, method = 'GET', data = null, callback = null) {
    const xhr = new XMLHttpRequest();
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                if (callback) callback(xhr.responseText);
            } else {
                console.error('Error en petición AJAX:', xhr.status);
            }
        }
    };
    
    xhr.open(method, url, true);
    
    if (method === 'POST' && data) {
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send(data);
    } else {
        xhr.send();
    }
}

/**
 * Cargar contenido dinámico
 */
function cargarContenido(url, containerId, callback = null) {
    ajax(url, 'GET', null, function(response) {
        document.getElementById(containerId).innerHTML = response;
        if (callback) callback();
    });
}

// ===========================
// FUNCIONES DE UTILIDAD PARA ALERTAS SUTILES
// ===========================

/**
 * Funciones de conveniencia para las alertas más comunes
 */
window.AlertUtils = {
    // Confirmación de eliminación
    confirmDelete: (entityName, callback) => {
        SwalPresets.confirm(
            `¿Eliminar ${entityName}?`,
            'Esta acción no se puede deshacer',
            callback
        );
    },
    
    // Confirmación de cambio de estado
    confirmStatusChange: (action, entityName, callback) => {
        SwalPresets.confirm(
            `¿${action.charAt(0).toUpperCase() + action.slice(1)} ${entityName}?`,
            `El ${entityName} será ${action === 'activar' ? 'activado' : 'desactivado'}`,
            callback
        );
    },
    
    // Éxito con recarga automática
    successWithReload: (message, delay = 1500) => {
        SwalPresets.toast(message, 'success', delay);
        setTimeout(() => location.reload(), delay);
    },
    
    // Validación de formulario fallida
    validationError: (message = 'Por favor revise los campos marcados') => {
        SwalPresets.warning('Datos incompletos', message);
    },
    
    // Operación guardada exitosamente
    saveSuccess: (entityName = 'registro') => {
        SwalPresets.toast(`${entityName.charAt(0).toUpperCase() + entityName.slice(1)} guardado correctamente`, 'success');
    },
    
    // Error de servidor
    serverError: (message = 'Error de conexión con el servidor') => {
        SwalPresets.error('Error del servidor', message);
    },
    
    // Procesando con loading
    processing: (message = 'Procesando información...') => {
        SwalPresets.loading('Procesando', message);
    },
    
    // Cerrar cualquier alert abierto
    close: () => {
        Swal.close();
    }
};

/**
 * Funciones específicas para CRUDs comunes
 */
window.CrudUtils = {
    // Cambio de estado estándar
    changeStatus: (id, newStatus, entityName, endpoint) => {
        const action = newStatus == 1 ? 'activar' : 'desactivar';
        
        AlertUtils.confirmStatusChange(action, entityName, () => {
            AlertUtils.processing('Actualizando estado...');
            
            fetch(`${endpoint}/${id}/estado`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ estado: newStatus })
            })
            .then(response => response.json())
            .then(data => {
                AlertUtils.close();
                
                if (data.success) {
                    AlertUtils.successWithReload(`${entityName.charAt(0).toUpperCase() + entityName.slice(1)} actualizado correctamente`);
                } else {
                    SwalPresets.error('Error', data.message || 'Error al cambiar el estado');
                }
            })
            .catch(error => {
                AlertUtils.close();
                console.error('Error:', error);
                AlertUtils.serverError();
            });
        });
    },
    
    // Eliminar con confirmación
    delete: (id, entityName, endpoint) => {
        AlertUtils.confirmDelete(entityName, () => {
            AlertUtils.processing(`Eliminando ${entityName}...`);
            
            fetch(`${endpoint}/${id}/delete`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                AlertUtils.close();
                
                if (data.success) {
                    AlertUtils.successWithReload(`${entityName.charAt(0).toUpperCase() + entityName.slice(1)} eliminado correctamente`);
                } else {
                    SwalPresets.error('Error', data.message || 'Error al eliminar');
                }
            })
            .catch(error => {
                AlertUtils.close();
                console.error('Error:', error);
                AlertUtils.serverError();
            });
        });
    }
};

// ========================================
// VALIDACIÓN DE FORMULARIOS ESPECÍFICOS
// ========================================

/**
 * Configurar validación específica para tipo de servicio
 */
function setupTipoServicioValidation() {
    const descripcionInput = document.getElementById('tiposervicio_descripcion');
    if (!descripcionInput) return;
    
    descripcionInput.addEventListener('input', function() {
        const value = this.value.trim();
        
        if (value.length === 0) {
            this.classList.add('is-invalid');
        } else if (value.length > 100) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        }
    });

    // Validación antes del envío
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const descripcion = descripcionInput.value.trim();
            
            let isValid = true;
            
            if (descripcion.length === 0 || descripcion.length > 100) {
                descripcionInput.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                
                // Mostrar mensaje de error
                let errorDiv = document.querySelector('.validation-error');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger validation-error mt-3';
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Por favor corrija los errores en el formulario antes de continuar.';
                    form.appendChild(errorDiv);
                }
                
                // Hacer scroll al primer error
                const firstError = form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            } else {
                // Remover mensaje de error de validación si existe
                const errorDiv = document.querySelector('.validation-error');
                if (errorDiv) {
                    errorDiv.remove();
                }
            }
        });
    }
}

/**
 * Configurar validación específica para tipo de contacto
 */
function setupTipoContactoValidation() {
    const descripcionInput = document.getElementById('tipocontacto_descripcion');
    if (!descripcionInput) return;
    
    descripcionInput.addEventListener('input', function() {
        const value = this.value.trim();
        
        if (value.length === 0) {
            this.classList.add('is-invalid');
        } else if (value.length > 100) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        }
    });

    // Validación antes del envío
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const descripcion = descripcionInput.value.trim();
            
            let isValid = true;
            
            if (descripcion.length === 0 || descripcion.length > 100) {
                descripcionInput.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                
                // Mostrar mensaje de error
                let errorDiv = document.querySelector('.validation-error');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger validation-error mt-3';
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Por favor corrija los errores en el formulario antes de continuar.';
                    form.appendChild(errorDiv);
                }
                
                // Hacer scroll al primer error
                const firstError = form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            } else {
                // Remover mensaje de error de validación si existe
                const errorDiv = document.querySelector('.validation-error');
                if (errorDiv) {
                    errorDiv.remove();
                }
            }
        });
    }
}

/**
 * Configurar validación específica para método de pago
 */
function setupMetodoPagoValidation() {
    const form = document.getElementById('metodoPagoForm');
    const submitBtn = document.getElementById('submitBtn');
    const descripcionInput = document.getElementById('metododepago_descripcion');
    
    if (!form || !descripcionInput) return;
    
    // Validación del formulario
    form.addEventListener('submit', function(e) {
        const descripcion = descripcionInput.value.trim();
        
        if (!descripcion) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Campo requerido',
                    text: 'Por favor ingrese la descripción del método de pago.'
                });
            } else {
                alert('Por favor ingrese la descripción del método de pago.');
            }
            return;
        }
        
        if (descripcion.length < 2) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Descripción muy corta',
                    text: 'La descripción debe tener al menos 2 caracteres.'
                });
            } else {
                alert('La descripción debe tener al menos 2 caracteres.');
            }
            return;
        }
        
        // Mostrar indicador de carga
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        }
    });
    
    // Contador de caracteres
    const maxLength = 45;
    
    descripcionInput.addEventListener('input', function() {
        const remaining = maxLength - this.value.length;
        let counter = document.getElementById('charCounter');
        
        if (!counter) {
            counter = document.createElement('small');
            counter.id = 'charCounter';
            counter.className = 'form-text';
            this.parentNode.appendChild(counter);
        }
        
        counter.textContent = `${remaining} caracteres restantes`;
        counter.className = remaining < 10 ? 'form-text text-danger' : 'form-text text-muted';
    });
}

/**
 * Configurar validación específica para periodos
 */
function setupPeriodoValidation() {
    const descripcionInput = document.getElementById('periodo_descripcion');
    const fechaInicioInput = document.getElementById('periodo_fechainicio');
    const fechaFinInput = document.getElementById('periodo_fechafin');
    const durationInfo = document.getElementById('duration-info');
    const durationText = document.getElementById('duration-text');
    
    if (!descripcionInput || !fechaInicioInput || !fechaFinInput) return;

    // Función para calcular y mostrar duración
    function updateDuration() {
        const fechaInicio = fechaInicioInput.value;
        const fechaFin = fechaFinInput.value;

        if (fechaInicio && fechaFin && durationInfo && durationText) {
            const inicio = new Date(fechaInicio);
            const fin = new Date(fechaFin);
            
            if (fin > inicio) {
                const diffTime = Math.abs(fin - inicio);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                durationText.textContent = `Duración del periodo: ${diffDays} días`;
                durationInfo.style.display = 'block';
                durationInfo.className = 'alert alert-info';
            } else if (fin <= inicio) {
                durationText.textContent = 'La fecha de fin debe ser posterior a la fecha de inicio';
                durationInfo.style.display = 'block';
                durationInfo.className = 'alert alert-warning';
            }
        } else if (durationInfo) {
            durationInfo.style.display = 'none';
        }
    }

    // Validación en tiempo real
    descripcionInput.addEventListener('input', function() {
        const value = this.value.trim();
        
        if (value.length === 0) {
            this.classList.add('is-invalid');
        } else if (value.length > 100) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        }
    });

    fechaInicioInput.addEventListener('change', function() {
        if (this.value) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        }
        updateDuration();
    });

    fechaFinInput.addEventListener('change', function() {
        if (this.value) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        }
        updateDuration();
    });

    // Validación antes del envío
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const descripcion = descripcionInput.value.trim();
            const fechaInicio = fechaInicioInput.value;
            const fechaFin = fechaFinInput.value;
            
            let isValid = true;
            
            // Validar descripción
            if (descripcion.length === 0 || descripcion.length > 100) {
                descripcionInput.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validar fechas
            if (!fechaInicio) {
                fechaInicioInput.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!fechaFin) {
                fechaFinInput.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validar que fecha fin sea posterior a fecha inicio
            if (fechaInicio && fechaFin) {
                const inicio = new Date(fechaInicio);
                const fin = new Date(fechaFin);
                
                if (fin <= inicio) {
                    fechaFinInput.classList.add('is-invalid');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                
                // Mostrar mensaje de error
                let errorDiv = document.querySelector('.validation-error');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger validation-error mt-3';
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Por favor corrija los errores en el formulario antes de continuar.';
                    form.appendChild(errorDiv);
                }
                
                // Hacer scroll al primer error
                const firstError = form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            } else {
                // Remover mensaje de error de validación si existe
                const errorDiv = document.querySelector('.validation-error');
                if (errorDiv) {
                    errorDiv.remove();
                }
            }
        });
    }

    // Calcular duración inicial si hay datos
    updateDuration();
}

/**
 * Configurar validación específica para estado de reserva
 */
function setupEstadoReservaValidation() {
    const descripcionInput = document.getElementById('estadoreserva_descripcion');
    if (!descripcionInput) return;
    
    descripcionInput.addEventListener('input', function() {
        const value = this.value.trim();
        
        if (value.length === 0) {
            this.classList.add('is-invalid');
        } else if (value.length > 100) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        }
    });

    // Validación antes del envío
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const descripcion = descripcionInput.value.trim();
            
            let isValid = true;
            
            if (descripcion.length === 0 || descripcion.length > 100) {
                descripcionInput.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                
                // Mostrar mensaje de error
                let errorDiv = document.querySelector('.validation-error');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger validation-error mt-3';
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Por favor corrija los errores en el formulario antes de continuar.';
                    form.appendChild(errorDiv);
                }
                
                // Hacer scroll al primer error
                const firstError = form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            } else {
                // Remover mensaje de error de validación si existe
                const errorDiv = document.querySelector('.validation-error');
                if (errorDiv) {
                    errorDiv.remove();
                }
            }
        });
    }
}

/**
 * Configurar validación específica para condición de salud
 */
function setupCondicionSaludValidation() {
    const descripcionInput = document.getElementById('condicionsalud_descripcion');
    const charCount = document.getElementById('char-count');
    const previewCard = document.getElementById('preview-card');
    const previewDescription = document.getElementById('preview-description');
    const previewIcon = document.getElementById('preview-icon');
    const previewWarning = document.getElementById('preview-warning');

    if (!descripcionInput) return;

    // Palabras clave para condiciones críticas
    const palabrasCriticas = [
        'alergia', 'alergico', 'alergica', 'diabetes', 'diabetico', 'diabetica',
        'cardiaco', 'cardiaca', 'corazon', 'epilepsia', 'epileptico', 'epileptica',
        'asma', 'asmatico', 'asmatica', 'hipertension', 'presion', 'renal', 'riñon',
        'hepatico', 'hepatica', 'higado', 'cancer', 'tumor', 'quimioterapia'
    ];

    // Función para detectar condiciones críticas
    function esCritica(texto) {
        const textoLower = texto.toLowerCase();
        return palabrasCriticas.some(palabra => textoLower.includes(palabra));
    }

    // Función para actualizar contador de caracteres
    function updateCharCount() {
        const length = descripcionInput.value.length;
        if (charCount) {
            charCount.textContent = `${length}/250`;
            
            charCount.className = '';
            if (length > 200) {
                charCount.classList.add('warning');
            }
            if (length >= 250) {
                charCount.classList.add('danger');
            }
        }
    }

    // Función para actualizar vista previa
    function updatePreview() {
        if (!previewCard || !previewDescription) return;
        
        const descripcion = descripcionInput.value.trim();
        
        if (descripcion.length > 0) {
            previewDescription.textContent = descripcion;
            
            const critica = esCritica(descripcion);
            
            if (previewIcon) {
                if (critica) {
                    previewIcon.innerHTML = '<i class="fas fa-exclamation-triangle text-warning" title="Condición Crítica"></i>';
                    if (previewWarning) previewWarning.style.display = 'block';
                } else {
                    previewIcon.innerHTML = '<i class="fas fa-info-circle text-info" title="Condición Estándar"></i>';
                    if (previewWarning) previewWarning.style.display = 'none';
                }
            }
            
            previewCard.style.display = 'block';
        } else {
            previewCard.style.display = 'none';
        }
    }

    // Event listeners
    descripcionInput.addEventListener('input', function() {
        const value = this.value.trim();
        
        // Validación en tiempo real
        if (value.length === 0) {
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
        } else if (value.length < 3) {
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
        } else if (value.length > 250) {
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
        } else {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        }

        updateCharCount();
        updatePreview();
        
        // Auto-resize del textarea
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 150) + 'px';
    });

    // Validación antes del envío
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const descripcion = descripcionInput.value.trim();
            
            let isValid = true;
            
            // Validar descripción
            if (descripcion.length < 3 || descripcion.length > 250) {
                descripcionInput.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                
                // Mostrar mensaje de error
                let errorDiv = document.querySelector('.validation-error');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger validation-error mt-3';
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Por favor corrija los errores en el formulario antes de continuar.';
                    form.appendChild(errorDiv);
                }
                
                // Hacer scroll al primer error
                const firstError = form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            } else {
                // Remover mensaje de error de validación si existe
                const errorDiv = document.querySelector('.validation-error');
                if (errorDiv) {
                    errorDiv.remove();
                }
            }
        });
    }

    // Inicializar contador y preview
    updateCharCount();
    updatePreview();
}

/**
 * Configurar validación específica para salidas/checkout
 */
function setupSalidaValidation() {
    const form = document.getElementById('formSalida');
    const btnConfirmar = document.getElementById('btnConfirmar');
    const radioButtons = document.querySelectorAll('.reserva-radio');

    if (!form || !btnConfirmar) return;

    // Habilitar/deshabilitar botón según selección
    function updateButton() {
        const selected = document.querySelector('.reserva-radio:checked');
        btnConfirmar.disabled = !selected;
        
        if (selected) {
            btnConfirmar.innerHTML = '<i class="fas fa-check"></i> Confirmar Check-out';
        } else {
            btnConfirmar.innerHTML = '<i class="fas fa-check"></i> Selecciona una reserva';
        }
    }

    radioButtons.forEach(radio => {
        radio.addEventListener('change', updateButton);
    });

    // Estado inicial
    updateButton();

    // Confirmación antes del envío
    form.addEventListener('submit', function(e) {
        const selected = document.querySelector('.reserva-radio:checked');
        if (!selected) {
            e.preventDefault();
            alert('Debe seleccionar una reserva para procesar el check-out.');
            return;
        }

        const reservaId = selected.value;
        const confirmMessage = '¿Confirma que desea procesar el check-out de la reserva #' + reservaId + '?\n\n' +
                              'Esta acción:\n' +
                              '• Cambiará el estado de la reserva\n' +
                              '• Liberará la cabaña\n' +
                              '• No se puede deshacer';

        if (!confirm(confirmMessage)) {
            e.preventDefault();
        } else {
            // Mostrar indicador de carga
            btnConfirmar.disabled = true;
            btnConfirmar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        }
    });

    // Auto-refresh cada 60 segundos si no hay modales abiertos
    setInterval(function() {
        if (!document.querySelector('.modal.show') && 
            !document.querySelector('.swal2-container') && 
            document.hasFocus()) {
            window.location.reload();
        }
    }, 60000);
}

/**
 * Función para calcular pagos en tiempo real (salidas)
 */
function actualizarCalculos(reservaId) {
    // Esta función podría hacer una llamada AJAX para obtener
    // los datos más actualizados de pagos y consumos
    console.log('Actualizando cálculos para reserva: ' + reservaId);
}

/**
 * Configurar actualización automática para listados en tiempo real
 */
function setupAutoRefresh(intervalMinutes = 0.5) {
    // Solo activar en páginas de listado que lo necesiten
    const isListado = window.location.pathname.includes('listado') || 
                     window.location.pathname.includes('salidas');
    
    if (!isListado) return;
    
    const intervalMs = intervalMinutes * 60 * 1000;
    
    setInterval(function() {
        // Solo si no hay modales abiertos
        if (!document.querySelector('.modal.show') && 
            !document.querySelector('.swal2-container') && 
            document.hasFocus()) {
            window.location.reload();
        }
    }, intervalMs);
}

// =====================================================
// FUNCIONES PARA SALIDAS/BUSQUEDA - FILTROS Y EXPORTACIÓN  
// =====================================================

// Funciones para filtros rápidos de salidas
function limpiarFiltros() {
    document.getElementById('formBusqueda').reset();
    window.location.href = '/salidas/busqueda';
}

function buscarHoy() {
    const hoy = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_desde').value = hoy;
    document.getElementById('fecha_hasta').value = hoy;
    document.getElementById('formBusqueda').submit();
}

function buscarSemana() {
    const hoy = new Date();
    const inicioSemana = new Date(hoy.setDate(hoy.getDate() - hoy.getDay()));
    const finSemana = new Date(hoy.setDate(hoy.getDate() - hoy.getDay() + 6));
    
    document.getElementById('fecha_desde').value = inicioSemana.toISOString().split('T')[0];
    document.getElementById('fecha_hasta').value = finSemana.toISOString().split('T')[0];
    document.getElementById('formBusqueda').submit();
}

function buscarPendientes() {
    document.getElementById('estado').value = 'pendiente de pago';
    document.getElementById('formBusqueda').submit();
}

function buscarFinalizadas() {
    const hoy = new Date();
    const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    
    document.getElementById('estado').value = 'finalizada';
    document.getElementById('fecha_desde').value = inicioMes.toISOString().split('T')[0];
    document.getElementById('fecha_hasta').value = hoy.toISOString().split('T')[0];
    document.getElementById('formBusqueda').submit();
}

function buscarUltimaSemana() {
    const hoy = new Date();
    const unaSemanaAtras = new Date(hoy.getTime() - 7 * 24 * 60 * 60 * 1000);
    
    document.getElementById('fecha_desde').value = unaSemanaAtras.toISOString().split('T')[0];
    document.getElementById('fecha_hasta').value = new Date().toISOString().split('T')[0];
    document.getElementById('formBusqueda').submit();
}

function exportarResultados() {
    const tabla = document.getElementById('tablaSalidas');
    if (!tabla) return;
    
    let csv = 'ID,Huésped,Cabaña,Check-in,Check-out,Días,Estado\n';
    
    const filas = tabla.querySelectorAll('tbody tr');
    filas.forEach(fila => {
        const celdas = fila.querySelectorAll('td');
        const datos = [
            celdas[0].textContent.trim(),
            celdas[1].textContent.trim(),
            celdas[2].textContent.trim(),
            celdas[3].textContent.trim(),
            celdas[4].textContent.trim(),
            celdas[5].textContent.trim(),
            celdas[6].textContent.trim()
        ];
        csv += datos.map(dato => '"' + dato.replace(/"/g, '""') + '"').join(',') + '\n';
    });
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'salidas_' + new Date().toISOString().split('T')[0] + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Auto-init para salidas/busqueda - validación de fechas
if (window.location.pathname.includes('/salidas/busqueda')) {
    document.addEventListener('DOMContentLoaded', function() {
        const fechaDesde = document.getElementById('fecha_desde');
        const fechaHasta = document.getElementById('fecha_hasta');
        
        if (fechaDesde && fechaHasta) {
            fechaDesde.addEventListener('change', function() {
                if (fechaHasta.value && fechaDesde.value > fechaHasta.value) {
                    fechaHasta.value = fechaDesde.value;
                }
                fechaHasta.min = fechaDesde.value;
            });
            
            fechaHasta.addEventListener('change', function() {
                if (fechaDesde.value && fechaHasta.value < fechaDesde.value) {
                    fechaDesde.value = fechaHasta.value;
                }
                fechaDesde.max = fechaHasta.value;
            });
        }
    });
}

// =====================================================
// FUNCIONES PARA SALIDAS/DETALLE - IMPRESIÓN
// =====================================================

// Auto-init para salidas/detalle - funcionalidad de impresión
if (window.location.pathname.includes('/salidas/detalle')) {
    // Funcionalidad para imprimir con título dinámico
    window.addEventListener('beforeprint', function() {
        // Generar título dinámico basado en la reserva
        const reservaId = new URLSearchParams(window.location.search).get('id');
        document.title = 'Detalle de Salida - Reserva #' + (reservaId || 'N/A');
    });

    window.addEventListener('afterprint', function() {
        // Restaurar título original
        document.title = document.querySelector('title')?.textContent || 'Cabañas System';
    });
}

// =====================================================
// FUNCIONES PARA REPORTES - PAGINACIÓN Y FILTROS
// =====================================================

// Función global para cambiar elementos por página en reportes  
function changePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
}

// Auto-init para reportes/comentarios - filtros automáticos
if (window.location.pathname.includes('/reportes/comentarios')) {
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit form cuando cambien las fechas
        const fechaDesde = document.getElementById('fecha_desde');
        const fechaHasta = document.getElementById('fecha_hasta'); 
        const form = document.querySelector('.filters-form');
        
        if (fechaDesde && fechaHasta && form) {
            fechaDesde.addEventListener('change', function() {
                if (fechaHasta.value) {
                    form.submit();
                }
            });

            fechaHasta.addEventListener('change', function() {
                if (fechaDesde.value) {
                    form.submit();
                }
            });
        }
    });
}

// ========== ESTADOS PERSONAS - FUNCIONALIDADES ==========

// Función para confirmar eliminación de estado persona
function confirmarEliminacionEstado() {
    const estadoData = document.querySelector('[data-estado]');
    if (estadoData) {
        const descripcion = estadoData.dataset.estadoDescripcion;
        if (confirm(`¿Está seguro de eliminar el estado "${descripcion}"?\n\nEsta acción no se puede deshacer.`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.location.href + '/delete';
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    }
}

// Funciones para listado de estados personas
function confirmarEliminarEstado(id, descripcion, totalPersonas) {
    if (totalPersonas > 0) {
        alert(`No se puede eliminar el estado "${descripcion}" porque tiene ${totalPersonas} persona(s) asignada(s).\n\nPrimero debe reasignar las personas a otro estado.`);
        return;
    }
    
    if (confirm(`¿Está seguro de eliminar el estado "${descripcion}"?\n\nEsta acción no se puede deshacer.`)) {
        window.location.href = '/estados-personas/' + id + '/delete';
    }
}

function confirmarRestaurarEstado(id, descripcion) {
    if (confirm(`¿Está seguro de restaurar el estado "${descripcion}"?`)) {
        window.location.href = '/estados-personas/' + id + '/restore';
    }
}

// Inicialización de formulario de estados personas
function initEstadoPersonaForm() {
    // Contadores de caracteres
    const setupCharCounter = (inputId, counterId) => {
        const input = document.getElementById(inputId);
        const counter = document.getElementById(counterId);
        
        if (input && counter) {
            const updateCounter = () => {
                const current = input.value.length;
                const currentSpan = counter.querySelector('.current');
                if (currentSpan) {
                    currentSpan.textContent = current;
                    const maxSpan = counter.querySelector('.max');
                    const max = parseInt(maxSpan?.textContent || '0');
                    
                    if (current > max * 0.9) {
                        counter.classList.add('warning');
                    } else {
                        counter.classList.remove('warning');
                    }
                }
            };
            
            input.addEventListener('input', updateCounter);
            updateCounter();
        }
    };
    
    setupCharCounter('estadopersona_descripcion', 'descripcionCounter');
    setupCharCounter('estadopersona_observaciones', 'observacionesCounter');
    
    // Sincronización de color picker
    const colorPicker = document.getElementById('estadopersona_color');
    const colorText = document.getElementById('estadopersona_color_text');
    const previewDot = document.getElementById('previewColorDot');
    
    if (colorPicker && colorText && previewDot) {
        colorPicker.addEventListener('input', function() {
            colorText.value = this.value;
            previewDot.style.backgroundColor = this.value;
        });
        
        colorText.addEventListener('input', function() {
            if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                colorPicker.value = this.value;
                previewDot.style.backgroundColor = this.value;
            }
        });
    }
    
    // Vista previa del nombre
    const descripcionInput = document.getElementById('estadopersona_descripcion');
    const previewName = document.getElementById('previewStateName');
    
    if (descripcionInput && previewName) {
        descripcionInput.addEventListener('input', function() {
            previewName.textContent = this.value || 'Nuevo Estado';
        });
    }
    
    // Vista previa de observaciones
    const observacionesInput = document.getElementById('estadopersona_observaciones');
    const previewDescription = document.getElementById('previewDescription');
    
    if (observacionesInput && previewDescription) {
        observacionesInput.addEventListener('input', function() {
            previewDescription.innerHTML = this.value 
                ? this.value.replace(/\n/g, '<br>')
                : '<em>Sin observaciones adicionales</em>';
        });
    }
    
    // Vista previa del acceso
    const accesoCheckbox = document.getElementById('estadopersona_permite_acceso');
    const previewAccess = document.getElementById('previewAccessIndicator');
    
    if (accesoCheckbox && previewAccess) {
        accesoCheckbox.addEventListener('change', function() {
            if (this.checked) {
                previewAccess.innerHTML = '<i class="fas fa-check-circle text-success" title="Permite acceso"></i>';
            } else {
                previewAccess.innerHTML = '<i class="fas fa-times-circle text-danger" title="No permite acceso"></i>';
            }
        });
    }
    
    // Validación del formulario
    const form = document.getElementById('estadoPersonaForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validar descripción
            const descripcion = document.getElementById('estadopersona_descripcion');
            if (!descripcion.value.trim()) {
                isValid = false;
                descripcion.classList.add('is-invalid');
            } else {
                descripcion.classList.remove('is-invalid');
            }
            
            // Validar color si está presente
            if (colorText && colorText.value && !/^#[0-9A-Fa-f]{6}$/.test(colorText.value)) {
                isValid = false;
                colorText.classList.add('is-invalid');
            } else if (colorText) {
                colorText.classList.remove('is-invalid');
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor corrija los campos marcados con error.');
            }
        });
    }
}

// Event listeners para estados personas
document.addEventListener('DOMContentLoaded', function() {
    // Auto-inicializar formulario de estados personas si existe
    if (document.getElementById('estadoPersonaForm')) {
        initEstadoPersonaForm();
    }
    
    // Botón de eliminar en detalle con data-attribute
    const deleteButton = document.querySelector('[data-action="delete-estado"]');
    if (deleteButton) {
        deleteButton.addEventListener('click', confirmarEliminacionEstado);
    }
    
    // Botones de eliminar en listado (usar event delegation)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-danger[data-estado-delete]')) {
            const button = e.target.closest('.btn-danger[data-estado-delete]');
            const id = button.dataset.estadoId;
            const descripcion = button.dataset.estadoDescripcion;
            const totalPersonas = parseInt(button.dataset.estadoTotalPersonas) || 0;
            confirmarEliminarEstado(id, descripcion, totalPersonas);
        }
        
        if (e.target.closest('.btn-success[data-estado-restore]')) {
            const button = e.target.closest('.btn-success[data-estado-restore]');
            const id = button.dataset.estadoId;
            const descripcion = button.dataset.estadoDescripcion;
            confirmarRestaurarEstado(id, descripcion);
        }
    });
});

// ========== ESTADOS PRODUCTOS - FUNCIONALIDADES ==========

// Funciones de confirmación para estados productos
function confirmarDesactivarEstadoProducto(id) {
    if (confirm('¿Está seguro de desactivar este estado?')) {
        window.location.href = `/proyecto_cabania/estados-productos/${id}/delete`;
    }
    return false;
}

function confirmarActivarEstadoProducto(id) {
    if (confirm('¿Está seguro de activar este estado?')) {
        window.location.href = `/proyecto_cabania/estados-productos/${id}/restore`;
    }
    return false;
}

// Inicialización de gráfico de estadísticas para estados productos
function initEstadosProductosChart(chartData) {
    const canvas = document.getElementById('distribucionChart');
    if (!canvas || !chartData) return;

    const ctx = canvas.getContext('2d');
    const chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: chartData.labels,
            datasets: [{
                data: chartData.data,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                    '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF',
                    '#4BC0C0', '#FF6384', '#36A2EB', '#FFCE56'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} productos (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// Event listeners para estados productos
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar gráfico si existe el canvas y los datos
    const chartCanvas = document.getElementById('distribucionChart');
    if (chartCanvas && window.estadosProductosChartData) {
        initEstadosProductosChart(window.estadosProductosChartData);
    }
    
    // Event delegation para botones de desactivar/activar
    document.addEventListener('click', function(e) {
        if (e.target.closest('[data-action="deactivate-estado-producto"]')) {
            e.preventDefault();
            const button = e.target.closest('[data-action="deactivate-estado-producto"]');
            const id = button.dataset.estadoId;
            confirmarDesactivarEstadoProducto(id);
        }
        
        if (e.target.closest('[data-action="activate-estado-producto"]')) {
            e.preventDefault();
            const button = e.target.closest('[data-action="activate-estado-producto"]');
            const id = button.dataset.estadoId;
            confirmarActivarEstadoProducto(id);
        }
    });
});

// ========== ESTADOS RESERVAS - FUNCIONALIDADES ==========

// Función para confirmar desactivación de estado de reserva
function confirmarDesactivarEstadoReserva(id) {
    if (confirm('¿Está seguro de desactivar este estado de reserva?')) {
        window.location.href = `/estados_reservas/eliminar/${id}`;
        return true;
    }
    return false;
}

// Función para confirmar activación de estado de reserva
function confirmarActivarEstadoReserva(id) {
    if (confirm('¿Está seguro de activar este estado de reserva?')) {
        window.location.href = `/estados_reservas/restaurar/${id}`;
        return true;
    }
    return false;
}

// Función para confirmar cambio de estado
function confirmarCambiarEstadoReserva(id) {
    if (confirm('¿Está seguro de cambiar el estado?')) {
        window.location.href = `/estados_reservas/toggle/${id}`;
        return true;
    }
    return false;
}

// Función para inicializar gráfico de distribución de estados de reservas
function initEstadosReservasDistributionChart(data) {
    const ctx = document.getElementById('distributionChart').getContext('2d');
    
    const colors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
        '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
    ];
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(e => e.estadoreserva_descripcion),
            datasets: [{
                data: data.map(e => e.reservas_count),
                backgroundColor: colors.slice(0, data.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                            return context.label + ': ' + context.raw + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

// Función para inicializar gráfico mensual de estados de reservas
function initEstadosReservasMonthlyChart(estadosData, monthlyData) {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    
    const colors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
        '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
    ];
    
    const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    
    // Preparar datos para gráfico mensual
    const monthlyChartData = {};
    
    // Inicializar datos
    estadosData.forEach(estado => {
        if (estado.estadoreserva_estado == 1) { // Solo estados activos
            monthlyChartData[estado.estadoreserva_descripcion] = new Array(12).fill(0);
        }
    });
    
    // Llenar con datos reales
    monthlyData.forEach(item => {
        if (item.mes && monthlyChartData[item.estadoreserva_descripcion]) {
            monthlyChartData[item.estadoreserva_descripcion][item.mes - 1] = parseInt(item.cantidad);
        }
    });

    // Crear datasets para el gráfico
    const datasets = Object.keys(monthlyChartData).map((estado, index) => ({
        label: estado,
        data: monthlyChartData[estado],
        borderColor: colors[index],
        backgroundColor: colors[index] + '20',
        borderWidth: 3,
        fill: false,
        tension: 0.1
    }));

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Número de Reservas'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Mes'
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
}

// Event listeners para estados reservas
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar gráficos si existen los canvas y los datos
    if (document.getElementById('distributionChart') && window.estadosReservasData) {
        initEstadosReservasDistributionChart(window.estadosReservasData);
    }
    
    if (document.getElementById('monthlyChart') && window.estadosReservasData && window.monthlyReservasData) {
        initEstadosReservasMonthlyChart(window.estadosReservasData, window.monthlyReservasData);
    }
    
    // Event delegation para botones de estados reservas
    document.addEventListener('click', function(e) {
        if (e.target.closest('[data-action="deactivate-estado-reserva"]')) {
            e.preventDefault();
            const button = e.target.closest('[data-action="deactivate-estado-reserva"]');
            const id = button.dataset.estadoId;
            confirmarDesactivarEstadoReserva(id);
        }
        
        if (e.target.closest('[data-action="activate-estado-reserva"]')) {
            e.preventDefault();
            const button = e.target.closest('[data-action="activate-estado-reserva"]');
            const id = button.dataset.estadoId;
            confirmarActivarEstadoReserva(id);
        }
        
        if (e.target.closest('[data-action="toggle-estado-reserva"]')) {
            e.preventDefault();
            const button = e.target.closest('[data-action="toggle-estado-reserva"]');
            const id = button.dataset.estadoId;
            confirmarCambiarEstadoReserva(id);
        }
    });
});

// ========== INGRESOS - FUNCIONALIDADES ==========

// Función para confirmar ingreso de reserva (confirmación simple)
function confirmarIngresoReserva(reservaId) {
    if (confirm('¿Confirmar el ingreso de esta reserva?')) {
        // Buscar el formulario correspondiente y enviarlo
        const form = document.querySelector(`form input[value="${reservaId}"]`).closest('form');
        if (form) {
            form.submit();
        }
        return true;
    }
    return false;
}

// Función para confirmar ingreso con mensaje complejo
function confirmarIngresoComplejoReserva(reservaId) {
    const mensaje = '¿Confirmar el ingreso al complejo para esta reserva?\n\n' +
                   'Esto cambiará:\n' +
                   '- Estado de reserva a \'en curso\'\n' +
                   '- Estado de cabaña a \'ocupada\'';
    
    if (confirm(mensaje)) {
        // Buscar el formulario correspondiente y enviarlo
        const form = document.querySelector(`form input[value="${reservaId}"]`).closest('form');
        if (form) {
            form.submit();
        }
        return true;
    }
    return false;
}

// Función para imprimir detalle
function imprimirDetalle() {
    window.print();
}

// Función para actualizar página
function actualizarPagina() {
    window.location.reload();
}

// Event listeners para ingresos
document.addEventListener('DOMContentLoaded', function() {
    // Event delegation para botones de ingresos
    document.addEventListener('click', function(e) {
        // Confirmación de ingreso simple
        if (e.target.closest('[data-action="confirmar-ingreso"]')) {
            e.preventDefault();
            const button = e.target.closest('[data-action="confirmar-ingreso"]');
            const reservaId = button.dataset.reservaId;
            confirmarIngresoReserva(reservaId);
        }
        
        // Confirmación de ingreso complejo
        if (e.target.closest('[data-action="confirmar-ingreso-complejo"]')) {
            e.preventDefault();
            const button = e.target.closest('[data-action="confirmar-ingreso-complejo"]');
            const reservaId = button.dataset.reservaId;
            confirmarIngresoComplejoReserva(reservaId);
        }
        
        // Imprimir detalle
        if (e.target.closest('[data-action="imprimir"]')) {
            e.preventDefault();
            imprimirDetalle();
        }
        
        // Actualizar página
        if (e.target.closest('[data-action="actualizar"]')) {
            e.preventDefault();
            actualizarPagina();
        }
    });
});

// ========== MARCAS - FUNCIONALIDADES ==========

// Función para confirmar desactivación de marca
function confirmarDesactivarMarca(marcaId) {
    if (confirm('¿Está seguro de desactivar esta marca?')) {
        window.location.href = `/marcas/toggle/${marcaId}`;
        return true;
    }
    return false;
}

// Función para confirmar activación de marca
function confirmarActivarMarca(marcaId) {
    if (confirm('¿Está seguro de activar esta marca?')) {
        window.location.href = `/marcas/toggle/${marcaId}`;
        return true;
    }
    return false;
}

// Función para inicializar gráfico de estados de marcas (pie chart)
function initMarcasEstadosChart(datosEstados) {
    const ctxPie = document.getElementById('estadosPieChart');
    if (ctxPie && datosEstados) {
        const ctx = ctxPie.getContext('2d');
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Activas', 'Inactivas'],
                datasets: [{
                    data: [datosEstados.activas, datosEstados.inactivas],
                    backgroundColor: ['#28a745', '#6c757d'],
                    borderColor: ['#ffffff', '#ffffff'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '70%'
            }
        });
    }
}

// Event listeners para marcas
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar gráfico si existe el canvas y los datos
    if (document.getElementById('estadosPieChart') && window.marcasEstadosData) {
        initMarcasEstadosChart(window.marcasEstadosData);
    }
    
    // Event delegation para botones de marcas
    document.addEventListener('click', function(e) {
        if (e.target.closest('[data-action="desactivar-marca"]')) {
            e.preventDefault();
            const button = e.target.closest('[data-action="desactivar-marca"]');
            const marcaId = button.dataset.marcaId;
            confirmarDesactivarMarca(marcaId);
        }
        
        if (e.target.closest('[data-action="activar-marca"]')) {
            e.preventDefault();
            const button = e.target.closest('[data-action="activar-marca"]');
            const marcaId = button.dataset.marcaId;
            confirmarActivarMarca(marcaId);
        }
    });
});

// ========== MENUS - FUNCIONALIDADES ==========

// Función para confirmar desactivación de menú
function confirmarDesactivarMenu(menuId) {
    if (confirm('¿Está seguro de desactivar este menú?')) {
        window.location.href = `/menus/toggle/${menuId}`;
        return true;
    }
    return false;
}

// Función para confirmar activación de menú
function confirmarActivarMenu(menuId) {
    if (confirm('¿Está seguro de reactivar este menú?')) {
        window.location.href = `/menus/toggle/${menuId}`;
        return true;
    }
    return false;
}

// Función para validación de formulario de menús
function initMenuFormValidation() {
    const form = document.getElementById('menuForm');
    const nombreInput = document.getElementById('menu_nombre');
    const ordenInput = document.getElementById('menu_orden');

    if (form && nombreInput && ordenInput) {
        // Validación en tiempo real del nombre
        nombreInput.addEventListener('input', function() {
            const valor = this.value.trim();
            if (valor.length > 45) {
                this.setCustomValidity('El nombre no puede exceder 45 caracteres');
            } else if (valor.length < 2) {
                this.setCustomValidity('El nombre debe tener al menos 2 caracteres');
            } else {
                this.setCustomValidity('');
            }
        });

        // Validación del orden
        ordenInput.addEventListener('input', function() {
            const valor = parseInt(this.value);
            if (isNaN(valor) || valor < 1) {
                this.setCustomValidity('El orden debe ser un número mayor a 0');
            } else if (valor > 999) {
                this.setCustomValidity('El orden no puede ser mayor a 999');
            } else {
                this.setCustomValidity('');
            }
        });

        // Validación antes del envío
        form.addEventListener('submit', function(e) {
            const nombre = nombreInput.value.trim();
            const orden = parseInt(ordenInput.value);

            if (nombre.length < 2 || nombre.length > 45) {
                e.preventDefault();
                alert('El nombre del menú debe tener entre 2 y 45 caracteres');
                nombreInput.focus();
                return;
            }

            if (isNaN(orden) || orden < 1 || orden > 999) {
                e.preventDefault();
                alert('El orden debe ser un número entre 1 y 999');
                ordenInput.focus();
                return;
            }
        });
    }
}

// Función para inicializar reordenamiento de menús (SortableJS)
function initMenuReordering() {
    const sortableList = document.getElementById('sortableList');
    const changeIndicator = document.getElementById('changeIndicator');
    const resetButton = document.getElementById('resetOrder');
    const saveButton = document.getElementById('saveChanges');
    const orderPreview = document.getElementById('orderPreview');
    
    let originalOrder = [];
    let hasChanges = false;
    
    if (sortableList && window.Sortable) {
        // Guardar orden original
        const items = Array.from(sortableList.children);
        originalOrder = items.map(item => ({
            id: item.dataset.id,
            order: item.dataset.order,
            name: item.querySelector('h6').textContent.trim()
        }));
        
        // Inicializar SortableJS
        const sortable = Sortable.create(sortableList, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                updateOrderNumbers();
                updatePreview();
                checkForChanges();
            }
        });
        
        // Actualizar números de orden
        function updateOrderNumbers() {
            const items = Array.from(sortableList.children);
            items.forEach((item, index) => {
                const newOrder = index + 1;
                const badge = item.querySelector('.order-badge .badge');
                const input = item.querySelector('.order-input');
                
                badge.textContent = newOrder;
                input.value = newOrder;
            });
        }
        
        // Actualizar vista previa
        function updatePreview() {
            if (!orderPreview) return;
            
            const items = Array.from(sortableList.children);
            orderPreview.innerHTML = '';
            
            items.forEach((item, index) => {
                const li = document.createElement('li');
                li.dataset.id = item.dataset.id;
                
                const name = item.querySelector('h6').textContent.trim();
                const order = index + 1;
                
                li.innerHTML = `${name} <small class="text-muted">(${order})</small>`;
                orderPreview.appendChild(li);
            });
        }
        
        // Verificar si hay cambios
        function checkForChanges() {
            const items = Array.from(sortableList.children);
            const currentOrder = items.map(item => item.dataset.id);
            const originalOrderIds = originalOrder.map(item => item.id);
            
            hasChanges = !currentOrder.every((id, index) => id === originalOrderIds[index]);
            
            if (hasChanges) {
                changeIndicator.style.display = 'inline-block';
                saveButton.classList.remove('btn-primary');
                saveButton.classList.add('btn-success');
                saveButton.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios Pendientes';
            } else {
                changeIndicator.style.display = 'none';
                saveButton.classList.remove('btn-success');
                saveButton.classList.add('btn-primary');
                saveButton.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
            }
        }
        
        // Restaurar orden original
        if (resetButton) {
            resetButton.addEventListener('click', function() {
                if (confirm('¿Está seguro de que desea restaurar el orden original?')) {
                    // Reordenar elementos según el orden original
                    originalOrder.forEach((item, index) => {
                        const element = sortableList.querySelector(`[data-id="${item.id}"]`);
                        if (element) {
                            sortableList.appendChild(element);
                        }
                    });
                    
                    updateOrderNumbers();
                    updatePreview();
                    checkForChanges();
                }
            });
        }
        
        // Prevenir envío accidental del formulario
        if (saveButton) {
            saveButton.addEventListener('click', function(e) {
                if (!hasChanges) {
                    e.preventDefault();
                    alert('No hay cambios para guardar.');
                    return;
                }
                
                if (!confirm('¿Está seguro de que desea guardar el nuevo orden de los menús?')) {
                    e.preventDefault();
                }
            });
        }
    }
}

// ========================================
// GESTION DE MENUS - ESTADISTICAS
// ========================================

function initMenuStats() {
    // Gráfico de estados
    const ctx = document.getElementById('estadosChart');
    if (ctx) {
        try {
            const statsData = JSON.parse(ctx.dataset.stats || '{}');
            
            if (statsData.total > 0) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Activos', 'Inactivos'],
                        datasets: [{
                            data: [statsData.activos || 0, statsData.inactivos || 0],
                            backgroundColor: ['#198754', '#ffc107'],
                            borderColor: ['#ffffff', '#ffffff'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed;
                                        const total = statsData.total || 0;
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                // Mostrar mensaje si no hay datos
                ctx.parentElement.innerHTML = '<div class="text-center text-muted py-5"><i class="fas fa-chart-pie fa-3x mb-3"></i><p>No hay datos para mostrar</p></div>';
            }
        } catch (error) {
            console.error('Error al cargar estadísticas:', error);
            ctx.parentElement.innerHTML = '<div class="text-center text-muted py-5"><i class="fas fa-exclamation-triangle fa-3x mb-3"></i><p>Error al cargar el gráfico</p></div>';
        }
    }
}

// ========================================
// GESTION DE MENUS - DETALLE
// ========================================

function initMenuDetail() {
    // Animación de entrada para las tarjetas
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s, transform 0.5s';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// ========================================
// GESTION DE MENUS - BUSQUEDA
// ========================================

function initMenuSearch() {
    // Auto-foco en el campo de búsqueda
    const searchInput = document.getElementById('q');
    if (searchInput) {
        searchInput.focus();
        // Seleccionar todo el texto para facilitar nueva búsqueda
        searchInput.select();
    }

    // Validación del formulario de búsqueda
    const searchForm = document.querySelector('form[action="/menus/search"]');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const query = searchInput.value.trim();
            if (query.length < 1) {
                e.preventDefault();
                alert('Por favor, ingrese un término de búsqueda');
                searchInput.focus();
            }
        });
    }

    // Resaltar términos de búsqueda dinámicamente
    const marks = document.querySelectorAll('mark');
    marks.forEach(mark => {
        mark.style.backgroundColor = '#fff3cd';
        mark.style.color = '#856404';
        mark.style.padding = '0.1em 0.2em';
        mark.style.borderRadius = '0.2em';
    });
}

// ========================================
// GESTION DE METODOS DE PAGOS
// ========================================

function confirmarDesactivarMetodoPago(metodoId) {
    if (confirm('¿Está seguro de desactivar este método de pago?')) {
        window.location.href = `/metodos_pagos/toggle/${metodoId}`;
    }
}

function confirmarActivarMetodoPago(metodoId) {
    if (confirm('¿Está seguro de activar este método de pago?')) {
        window.location.href = `/metodos_pagos/toggle/${metodoId}`;
    }
}

function initMetodosPagosStats() {
    const chartCanvas = document.getElementById('estadosPieChart');
    if (!chartCanvas) return;
    
    // Obtener datos del canvas
    const statsData = chartCanvas.dataset.stats;
    if (!statsData) return;
    
    try {
        const stats = JSON.parse(statsData);
        
        // Crear gráfico de estados
        const ctxPie = chartCanvas.getContext('2d');
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ['Activos', 'Inactivos'],
                datasets: [{
                    data: [stats.metodos_activos || 0, stats.metodos_inactivos || 0],
                    backgroundColor: ['#28a745', '#6c757d'],
                    borderColor: ['#ffffff', '#ffffff'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '70%'
            }
        });
    } catch (error) {
        console.error('Error al inicializar gráfico de métodos de pagos:', error);
    }
}

// ========================================
// GESTION DE MODULOS
// ========================================

function confirmarDesactivarModulo(id, descripcion) {
    if (confirm(`¿Está seguro de desactivar el módulo "${descripcion}"?\n\nEsto puede afectar la navegación del sistema.`)) {
        window.location.href = `/modulos/${id}/delete`;
    }
}

function confirmarActivarModulo(id, descripcion) {
    if (confirm(`¿Está seguro de activar el módulo "${descripcion}"?`)) {
        window.location.href = `/modulos/${id}/restore`;
    }
}

function copiarRutaModulo(ruta) {
    navigator.clipboard.writeText(ruta).then(function() {
        // Mostrar feedback visual
        const elemento = document.querySelector('.route-display');
        const original = elemento.style.backgroundColor;
        elemento.style.backgroundColor = '#d4edda';
        setTimeout(() => {
            elemento.style.backgroundColor = original;
        }, 1000);
    }).catch(() => {
        // Fallback para navegadores antiguos
        const textarea = document.createElement('textarea');
        textarea.value = ruta;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    });
}

function initModulosFormValidation() {
    // Elementos del formulario
    const descripcionInput = document.getElementById('modulo_descripcion');
    const rutaInput = document.getElementById('modulo_ruta');
    const menuSelect = document.getElementById('rela_menu');
    const iconoInput = document.getElementById('modulo_icono');
    const observacionesTextarea = document.getElementById('modulo_observaciones');
    
    // Contadores
    const descCounter = document.getElementById('desc_counter');
    const rutaCounter = document.getElementById('ruta_counter');
    const obsCounter = document.getElementById('obs_counter');
    
    // Previsualización
    const menuInfo = document.getElementById('menuInfo');
    const previewMenu = document.getElementById('previewMenu');
    const previewModule = document.getElementById('previewModule');
    const iconPreview = document.getElementById('iconPreview');

    if (!descripcionInput) return; // No estamos en formulario de módulos

    // Función para actualizar contadores
    function actualizarContador(elemento, contador, max) {
        const longitud = elemento.value.length;
        contador.textContent = longitud;
        
        if (longitud > max * 0.9) {
            contador.style.color = longitud >= max ? '#dc3545' : '#ffc107';
        } else {
            contador.style.color = '#6c757d';
        }
        
        if (longitud >= max) {
            elemento.value = elemento.value.substring(0, max);
            contador.textContent = max;
        }
    }

    // Generar ruta automáticamente desde la descripción
    function generarRuta() {
        if (rutaInput.value === '' || rutaInput.dataset.autoGenerated === 'true') {
            const descripcion = descripcionInput.value.toLowerCase()
                .replace(/[^a-z0-9\s]/g, '') // Eliminar caracteres especiales
                .replace(/\s+/g, '-') // Espacios a guiones
                .replace(/^-+|-+$/g, ''); // Eliminar guiones del inicio/final
            
            if (descripcion) {
                rutaInput.value = '/' + descripcion;
                rutaInput.dataset.autoGenerated = 'true';
            }
        }
    }

    // Actualizar vista previa del menú
    function actualizarPreviewMenu() {
        const selectedOption = menuSelect ? menuSelect.selectedOptions[0] : null;
        const descripcion = descripcionInput.value.trim();
        
        if (selectedOption && selectedOption.value && previewMenu) {
            previewMenu.textContent = selectedOption.textContent;
            menuInfo.style.display = 'block';
        } else if (descripcion && previewMenu) {
            previewMenu.textContent = 'Menú principal';
            menuInfo.style.display = 'block';
        } else if (menuInfo) {
            menuInfo.style.display = 'none';
        }
        
        if (previewModule) {
            previewModule.textContent = descripcion || 'Módulo';
        }
    }

    // Actualizar vista previa del ícono
    function actualizarIcono() {
        if (!iconPreview) return;
        
        const icono = iconoInput.value.trim();
        if (icono) {
            iconPreview.innerHTML = `<i class="${icono}"></i>`;
        } else {
            iconPreview.innerHTML = '<i class="fas fa-question"></i>';
        }
    }

    // Validar formato de ruta (solo espacios)
    function validarRuta() {
        const ruta = rutaInput.value.trim();
        
        if (ruta.includes(' ')) {
            rutaInput.setCustomValidity('La ruta no puede contener espacios');
            rutaInput.classList.add('is-invalid');
        } else {
            rutaInput.setCustomValidity('');
            rutaInput.classList.remove('is-invalid');
        }
    }

    // Event listeners
    if (descripcionInput && descCounter) {
        descripcionInput.addEventListener('input', function() {
            actualizarContador(this, descCounter, 45);
            generarRuta();
            actualizarPreviewMenu();
        });
    }

    if (rutaInput) {
        rutaInput.addEventListener('input', function() {
            if (rutaCounter) {
                actualizarContador(this, rutaCounter, 100);
            }
            validarRuta();
            this.dataset.autoGenerated = 'false';
        });

        rutaInput.addEventListener('blur', validarRuta);
    }

    if (observacionesTextarea && obsCounter) {
        observacionesTextarea.addEventListener('input', function() {
            actualizarContador(this, obsCounter, 500);
        });
    }

    if (menuSelect) {
        menuSelect.addEventListener('change', actualizarPreviewMenu);
    }

    if (iconoInput) {
        iconoInput.addEventListener('input', actualizarIcono);
    }

    // Inicializar contadores y previsualizaciones
    if (descCounter) actualizarContador(descripcionInput, descCounter, 45);
    if (rutaCounter && rutaInput) actualizarContador(rutaInput, rutaCounter, 100);
    if (obsCounter && observacionesTextarea) actualizarContador(observacionesTextarea, obsCounter, 500);
    
    actualizarPreviewMenu();
    actualizarIcono();
    if (rutaInput) validarRuta();
}

function initModulosDetail() {
    // Agregar evento de click a la ruta para copiar
    const routeDisplay = document.querySelector('.route-display');
    if (routeDisplay) {
        routeDisplay.style.cursor = 'pointer';
        routeDisplay.title = 'Clic para copiar la ruta';
        routeDisplay.addEventListener('click', function() {
            copiarRutaModulo(this.textContent);
        });
    }
}

function initModulosListado() {
    // Auto-filtrado con debounce
    const descripcionInput = document.getElementById('descripcion');
    if (descripcionInput) {
        descripcionInput.addEventListener('input', debounceModulos(function() {
            // Opcional: auto-submit después de un delay
        }, 500));
    }
}

function debounceModulos(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Event listeners para menús
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar validación de formulario de menús
    initMenuFormValidation();
    
    // Inicializar estadísticas de menús
    initMenuStats();
    
    // Inicializar estadísticas de métodos de pagos
    initMetodosPagosStats();
    
    // Inicializar funciones de módulos
    initModulosFormValidation();
    initModulosDetail();
    initModulosListado();
    
    // Inicializar funciones de perfiles
    initPerfilesFormValidation();
    initPerfilesDetail();
    initPerfilesListado();
    
    // Inicializar funciones de perfiles-módulos
    initPerfilesModulosManagePermissions();
    initPerfilesModulosCreate();
    initPerfilesModulosEdit();
    initBootstrapValidation();
    
    // Inicializar detalle de menús
    initMenuDetail();
    
    // Inicializar búsqueda de menús
    initMenuSearch();
    
    // Inicializar reordenamiento si está disponible
    initMenuReordering();
    
    // Event delegation para botones de menús
    document.addEventListener('click', function(e) {
        if (e.target.closest('[data-action="desactivar-menu"]')) {
            e.preventDefault();
            const button = e.target.closest('[data-action="desactivar-menu"]');
            const menuId = button.dataset.menuId;
            confirmarDesactivarMenu(menuId);
        }
        
        if (e.target.closest('[data-action="activar-menu"]')) {
            e.preventDefault();
            const button = e.target.closest('[data-action="activar-menu"]');
            const menuId = button.dataset.menuId;
            confirmarActivarMenu(menuId);
        }
        
        // Event delegation para métodos de pagos
        if (e.target.closest('[data-action="desactivar-metodo-pago"]')) {
            e.preventDefault();
            const button = e.target.closest('[data-action="desactivar-metodo-pago"]');
            const metodoId = button.dataset.metodoId;
            confirmarDesactivarMetodoPago(metodoId);
        }
        
        if (e.target.closest('[data-action="activar-metodo-pago"]')) {
            e.preventDefault();
            const button = e.target.closest('[data-action="activar-metodo-pago"]');
            const metodoId = button.dataset.metodoId;
            confirmarActivarMetodoPago(metodoId);
        }
        
        // Event delegation para módulos
        if (e.target.closest('[data-action="desactivar-modulo"]')) {
            e.preventDefault();
            const button = e.target.closest('[data-action="desactivar-modulo"]');
            const moduloId = button.dataset.moduloId;
            const moduloDescripcion = button.dataset.moduloDescripcion;
            confirmarDesactivarModulo(moduloId, moduloDescripcion);
        }
        
        if (e.target.closest('[data-action="activar-modulo"]')) {
            e.preventDefault();
            const button = e.target.closest('[data-action="activar-modulo"]');
            const moduloId = button.dataset.moduloId;
            const moduloDescripcion = button.dataset.moduloDescripcion;
            confirmarActivarModulo(moduloId, moduloDescripcion);
        }
    });
    
    // Event delegation para botones de perfiles
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-action="confirmar-eliminar-perfil"]') || 
            e.target.closest('[data-action="confirmar-eliminar-perfil"]')) {
            e.preventDefault();
            const button = e.target.closest('[data-action="confirmar-eliminar-perfil"]');
            const perfilId = button.dataset.id;
            const perfilDescripcion = button.dataset.descripcion;
            const totalUsuarios = parseInt(button.dataset.totalUsuarios || '0');
            confirmarEliminarPerfil(perfilId, perfilDescripcion, totalUsuarios);
        }
        
        if (e.target.matches('[data-action="confirmar-restaurar-perfil"]') || 
            e.target.closest('[data-action="confirmar-restaurar-perfil"]')) {
            e.preventDefault();
            const button = e.target.closest('[data-action="confirmar-restaurar-perfil"]');
            const perfilId = button.dataset.id;
            const perfilDescripcion = button.dataset.descripcion;
            confirmarRestaurarPerfil(perfilId, perfilDescripcion);
        }
    });
});

// ========================================
// FUNCIONES DE PERFILES
// ========================================

function initPerfilesFormValidation() {
    // Elementos del formulario
    const descripcionInput = document.getElementById('perfil_descripcion');
    const observacionesTextarea = document.getElementById('perfil_observaciones');
    const colorInput = document.getElementById('perfil_color');
    const accesoCompletoCheck = document.getElementById('perfil_acceso_completo');
    const soloLecturaCheck = document.getElementById('perfil_solo_lectura');
    const predeterminadoCheck = document.getElementById('perfil_predeterminado');
    
    // Contadores
    const descCounter = document.getElementById('desc_counter');
    const obsCounter = document.getElementById('obs_counter');
    
    // Preview de color
    const colorPreview = document.getElementById('colorPreview');

    if (!descripcionInput) return;

    // Función para actualizar contadores
    function actualizarContador(elemento, contador, max) {
        const longitud = elemento.value.length;
        contador.textContent = longitud;
        
        if (longitud > max * 0.9) {
            contador.style.color = longitud >= max ? '#dc3545' : '#ffc107';
        } else {
            contador.style.color = '#6c757d';
        }
        
        if (longitud >= max) {
            elemento.value = elemento.value.substring(0, max);
            contador.textContent = max;
        }
    }

    // Actualizar preview de color
    function actualizarColorPreview() {
        const color = colorInput.value;
        if (colorPreview) {
            colorPreview.style.backgroundColor = color;
            colorPreview.style.borderColor = color;
        }
    }

    // Validaciones lógicas entre checkboxes
    function validarCheckboxes() {
        if (accesoCompletoCheck && soloLecturaCheck && accesoCompletoCheck.checked && soloLecturaCheck.checked) {
            // No pueden estar ambos marcados
            soloLecturaCheck.checked = false;
            alert('Un perfil no puede tener acceso completo y ser solo de lectura al mismo tiempo.');
        }
        
        // Solo puede haber un perfil predeterminado
        if (predeterminadoCheck && predeterminadoCheck.checked) {
            // Aquí podrías hacer una verificación AJAX para asegurar que solo hay uno
            console.log('Configurando como perfil predeterminado');
        }
    }

    // Validar nombre único
    function validarNombreUnico() {
        const descripcion = descripcionInput.value.trim().toLowerCase();
        const perfilesSistema = ['administrador', 'admin', 'root', 'sistema'];
        
        const esSistema = document.body.dataset.perfilSistema === 'true';
        
        if (perfilesSistema.includes(descripcion) && !esSistema) {
            descripcionInput.setCustomValidity('Este nombre está reservado para perfiles del sistema');
            descripcionInput.classList.add('is-invalid');
        } else {
            descripcionInput.setCustomValidity('');
            descripcionInput.classList.remove('is-invalid');
        }
    }

    // Event listeners
    descripcionInput.addEventListener('input', function() {
        if (descCounter) actualizarContador(this, descCounter, 100);
        validarNombreUnico();
    });

    descripcionInput.addEventListener('blur', validarNombreUnico);

    if (observacionesTextarea && obsCounter) {
        observacionesTextarea.addEventListener('input', function() {
            actualizarContador(this, obsCounter, 500);
        });
    }

    if (colorInput) colorInput.addEventListener('input', actualizarColorPreview);

    if (accesoCompletoCheck) accesoCompletoCheck.addEventListener('change', validarCheckboxes);
    if (soloLecturaCheck) soloLecturaCheck.addEventListener('change', validarCheckboxes);

    // Deshabilitar campos para perfiles del sistema
    const esSistema = document.body.dataset.perfilSistema === 'true';
    if (esSistema) {
        const camposSistema = [
            'perfil_puede_crear_usuarios',
            'perfil_puede_modificar_perfiles',
            'perfil_acceso_completo',
            'perfil_estado'
        ];
        
        camposSistema.forEach(campo => {
            const elemento = document.getElementById(campo);
            if (elemento) {
                elemento.disabled = true;
                elemento.parentElement.style.opacity = '0.6';
                elemento.parentElement.title = 'No se puede modificar en perfiles del sistema';
            }
        });
    }

    // Inicializar
    if (descCounter) actualizarContador(descripcionInput, descCounter, 100);
    if (obsCounter && observacionesTextarea) actualizarContador(observacionesTextarea, obsCounter, 500);
    actualizarColorPreview();

    // Validación del formulario
    const form = document.getElementById('perfilForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            validarNombreUnico();
            validarCheckboxes();
            
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            this.classList.add('was-validated');
        });
    }
}

function initPerfilesDetail() {
    // No hay funciones específicas de detalle actualmente
    // Funcionalidad manejada por event delegation
}

function initPerfilesListado() {
    // Validación de seguridad para perfiles del sistema
    document.querySelectorAll('.system-profile .btn-danger').forEach(btn => {
        btn.disabled = true;
        btn.title = 'No se puede eliminar un perfil del sistema';
        btn.style.opacity = '0.5';
        btn.style.cursor = 'not-allowed';
    });
}

function confirmarEliminarPerfil(id, descripcion, totalUsuarios) {
    if (totalUsuarios > 0) {
        alert(`No se puede eliminar el perfil "${descripcion}" porque tiene ${totalUsuarios} usuario(s) asignado(s).\n\nPrimero debe reasignar los usuarios a otro perfil.`);
        return;
    }
    
    if (confirm(`¿Está seguro de eliminar el perfil "${descripcion}"?\n\nEsta acción no se puede deshacer y eliminará todos los permisos asociados.`)) {
        window.location.href = '/perfiles/' + id + '/delete';
    }
}

function confirmarRestaurarPerfil(id, descripcion) {
    if (confirm(`¿Está seguro de restaurar el perfil "${descripcion}"?`)) {
        window.location.href = '/perfiles/' + id + '/restore';
    }
}

// ========================================
// FUNCIONES DE PERFILES-MÓDULOS
// ========================================

function initPerfilesModulosManagePermissions() {
    // Elementos del DOM
    const selectAllCheckbox = document.getElementById('select-all');
    const moduleCheckboxes = document.querySelectorAll('.module-checkbox');
    const contadorSeleccionados = document.getElementById('contador-seleccionados');
    const changesSummary = document.getElementById('changes-summary');
    const modulesToAdd = document.getElementById('modules-to-add');
    const modulesToRemove = document.getElementById('modules-to-remove');
    
    if (!selectAllCheckbox || !moduleCheckboxes.length) return;
    
    // Módulos originalmente asignados - obtenido del data attribute
    const originalAssignments = JSON.parse(document.body.dataset.originalAssignments || '[]');
    
    // Función para actualizar contadores
    function actualizarContadores() {
        const checked = document.querySelectorAll('.module-checkbox:checked');
        const totalSeleccionados = checked.length;
        
        contadorSeleccionados.textContent = totalSeleccionados;
        
        // Actualizar estado del checkbox "Seleccionar todos"
        selectAllCheckbox.indeterminate = totalSeleccionados > 0 && totalSeleccionados < moduleCheckboxes.length;
        selectAllCheckbox.checked = totalSeleccionados === moduleCheckboxes.length;
        
        // Calcular cambios
        const currentAssignments = Array.from(checked).map(cb => parseInt(cb.value));
        const toAdd = currentAssignments.filter(id => !originalAssignments.includes(id));
        const toRemove = originalAssignments.filter(id => !currentAssignments.includes(id));
        
        // Mostrar resumen de cambios si hay cambios
        if (toAdd.length > 0 || toRemove.length > 0) {
            if (modulesToAdd) modulesToAdd.textContent = `${toAdd.length} módulos a asignar`;
            if (modulesToRemove) modulesToRemove.textContent = `${toRemove.length} módulos a desasignar`;
            if (changesSummary) changesSummary.style.display = 'block';
        } else {
            if (changesSummary) changesSummary.style.display = 'none';
        }
    }
    
    // Event listeners para checkboxes individuales
    moduleCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Cambiar color de fila según selección
            const row = this.closest('tr');
            if (this.checked) {
                row.classList.add('table-success');
                row.classList.remove('thead-light');
            } else {
                row.classList.remove('table-success');
            }
            
            actualizarContadores();
        });
    });
    
    // Event listener para "Seleccionar todos"
    selectAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        moduleCheckboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
            const row = checkbox.closest('tr');
            if (isChecked) {
                row.classList.add('table-success');
                row.classList.remove('thead-light');
            } else {
                row.classList.remove('table-success');
            }
        });
        actualizarContadores();
    });
    
    // Inicializar contadores
    actualizarContadores();
}

function initPerfilesModulosCreate() {
    // Elementos del formulario
    const perfilSelect = document.getElementById('rela_perfil');
    const moduloSelect = document.getElementById('rela_modulo');
    const previewDiv = document.getElementById('preview');
    const previewPerfil = document.getElementById('preview-perfil');
    const previewModulo = document.getElementById('preview-modulo');
    
    if (!perfilSelect || !moduloSelect) return;
    
    // Función para actualizar la vista previa
    function updatePreview() {
        const perfilSeleccionado = perfilSelect.selectedOptions[0];
        const moduloSeleccionado = moduloSelect.selectedOptions[0];
        
        if (perfilSelect.value && moduloSelect.value && previewDiv) {
            if (previewPerfil) previewPerfil.textContent = perfilSeleccionado.textContent;
            if (previewModulo) previewModulo.textContent = moduloSeleccionado.textContent;
            previewDiv.style.display = 'block';
        } else if (previewDiv) {
            previewDiv.style.display = 'none';
        }
    }
    
    // Event listeners para los selects
    perfilSelect.addEventListener('change', updatePreview);
    moduloSelect.addEventListener('change', updatePreview);
    
    // Reset del formulario
    const resetButton = document.querySelector('button[type="reset"]');
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            if (previewDiv) previewDiv.style.display = 'none';
            document.querySelector('form').classList.remove('was-validated');
        });
    }
    
    // Actualizar vista previa inicial
    updatePreview();
}

function initPerfilesModulosEdit() {
    // Valores originales - obtenidos de data attributes
    const originalPerfil = parseInt(document.body.dataset.originalPerfil || '0');
    const originalModulo = parseInt(document.body.dataset.originalModulo || '0');
    
    // Elementos del formulario
    const perfilSelect = document.getElementById('rela_perfil');
    const moduloSelect = document.getElementById('rela_modulo');
    const changesPreview = document.getElementById('changes-preview');
    const perfilChange = document.getElementById('perfil-change');
    const moduloChange = document.getElementById('modulo-change');
    
    if (!perfilSelect || !moduloSelect) return;
    
    // Función para detectar cambios
    function detectarCambios() {
        const perfilCambiado = perfilSelect.value != originalPerfil;
        const moduloCambiado = moduloSelect.value != originalModulo;
        
        if (perfilCambiado || moduloCambiado) {
            // Mostrar preview de cambios
            if (perfilCambiado && perfilChange) {
                const perfilSeleccionado = perfilSelect.selectedOptions[0];
                const originalText = document.body.dataset.originalPerfilText || '';
                perfilChange.innerHTML = `<del class="text-danger">${originalText}</del> → <strong class="text-success">${perfilSeleccionado ? perfilSeleccionado.textContent : 'Sin seleccionar'}</strong>`;
            } else if (perfilChange) {
                const originalText = document.body.dataset.originalPerfilText || '';
                perfilChange.innerHTML = `<span class="text-muted">${originalText} (sin cambios)</span>`;
            }
            
            if (moduloCambiado && moduloChange) {
                const moduloSeleccionado = moduloSelect.selectedOptions[0];
                const originalText = document.body.dataset.originalModuloText || '';
                moduloChange.innerHTML = `<del class="text-danger">${originalText}</del> → <strong class="text-success">${moduloSeleccionado ? moduloSeleccionado.textContent : 'Sin seleccionar'}</strong>`;
            } else if (moduloChange) {
                const originalText = document.body.dataset.originalModuloText || '';
                moduloChange.innerHTML = `<span class="text-muted">${originalText} (sin cambios)</span>`;
            }
            
            if (changesPreview) changesPreview.style.display = 'block';
        } else if (changesPreview) {
            changesPreview.style.display = 'none';
        }
    }
    
    // Event listeners
    perfilSelect.addEventListener('change', detectarCambios);
    moduloSelect.addEventListener('change', detectarCambios);
    
    // Reset button
    const resetButton = document.querySelector('button[type="reset"]');
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            setTimeout(() => {
                if (changesPreview) changesPreview.style.display = 'none';
                document.querySelector('form').classList.remove('was-validated');
            }, 100);
        });
    }
}

// Funciones de selección masiva para manage_permissions
function seleccionarTodosPerfilesModulos() {
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.dispatchEvent(new Event('change'));
    }
}

function deseleccionarTodosPerfilesModulos() {
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.dispatchEvent(new Event('change'));
    }
}

function invertirSeleccionPerfilesModulos() {
    const moduleCheckboxes = document.querySelectorAll('.module-checkbox');
    moduleCheckboxes.forEach(checkbox => {
        checkbox.checked = !checkbox.checked;
        checkbox.dispatchEvent(new Event('change'));
    });
}

function resetFormPerfilesModulos() {
    // Restaurar selecciones originales
    const originalAssignments = JSON.parse(document.body.dataset.originalAssignments || '[]');
    const moduleCheckboxes = document.querySelectorAll('.module-checkbox');
    
    moduleCheckboxes.forEach(checkbox => {
        const moduleId = parseInt(checkbox.value);
        checkbox.checked = originalAssignments.includes(moduleId);
        
        const row = checkbox.closest('tr');
        if (checkbox.checked) {
            row.classList.add('table-success');
            row.classList.remove('thead-light');
        } else {
            row.classList.remove('table-success');
        }
    });
    
    // Actualizar contadores
    if (moduleCheckboxes.length > 0) {
        const event = new Event('change');
        moduleCheckboxes[0].dispatchEvent(event);
    }
}

// Validación de formularios Bootstrap
function initBootstrapValidation() {
    const forms = document.getElementsByClassName('needs-validation');
    Array.prototype.filter.call(forms, function(form) {
        form.addEventListener('submit', function(event) {
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

// ========================= PERIODOS =========================

// Inicialización de estadísticas de periodos con Chart.js
function initPeriodosStats() {
    // Verificar que Chart.js esté disponible
    if (typeof Chart === 'undefined') {
        console.error('Chart.js no está disponible');
        return;
    }

    // Obtener datos de los data-attributes del body
    const periodosPorMesData = JSON.parse(document.body.dataset.periodosPorMes || '{}');
    const estadoPeriodosData = {
        activos: parseInt(document.body.dataset.periodosActivos || '0'),
        inactivos: parseInt(document.body.dataset.totalPeriodos || '0') - parseInt(document.body.dataset.periodosActivos || '0')
    };
    const duracionPeriodosData = JSON.parse(document.body.dataset.duracionPeriodos || '[]');

    // Gráfico de periodos por mes
    const ctxPeriodosPorMes = document.getElementById('chartPeriodosPorMes');
    if (ctxPeriodosPorMes) {
        new Chart(ctxPeriodosPorMes.getContext('2d'), {
            type: 'line',
            data: {
                labels: Object.keys(periodosPorMesData),
                datasets: [{
                    label: 'Periodos Creados',
                    data: Object.values(periodosPorMesData),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Periodos: ' + context.parsed.y;
                            }
                        }
                    }
                }
            }
        });
    }

    // Gráfico de estado de periodos (doughnut chart)
    const ctxEstadoPeriodos = document.getElementById('chartEstadoPeriodos');
    if (ctxEstadoPeriodos) {
        new Chart(ctxEstadoPeriodos.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Activos', 'Inactivos'],
                datasets: [{
                    data: [estadoPeriodosData.activos, estadoPeriodosData.inactivos],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Gráfico de duración de periodos
    const ctxDuracionPeriodos = document.getElementById('chartDuracionPeriodos');
    if (ctxDuracionPeriodos) {
        new Chart(ctxDuracionPeriodos.getContext('2d'), {
            type: 'bar',
            data: {
                labels: duracionPeriodosData.map(item => item.descripcion.substring(0, 20) + '...'),
                datasets: [{
                    label: 'Días de Duración',
                    data: duracionPeriodosData.map(item => item.duracion),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(199, 199, 199, 0.8)',
                        'rgba(83, 102, 255, 0.8)',
                        'rgba(255, 99, 255, 0.8)',
                        'rgba(99, 255, 132, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 205, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)',
                        'rgba(83, 102, 255, 1)',
                        'rgba(255, 99, 255, 1)',
                        'rgba(99, 255, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 5,
                            callback: function(value) {
                                return value + ' días';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                const index = context[0].dataIndex;
                                return duracionPeriodosData[index].descripcion;
                            },
                            label: function(context) {
                                return 'Duración: ' + context.parsed.y + ' días';
                            }
                        }
                    }
                }
            }
        });
    }

    // Efecto de animación en las tarjetas
    animateStatsCards();
}

// Función para animar las tarjetas de estadísticas
function animateStatsCards() {
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// Funciones para confirmaciones en listado de periodos
function confirmarDesactivarPeriodo(id) {
    return confirm('¿Está seguro de desactivar este periodo?');
}

function confirmarActivarPeriodo(id) {
    return confirm('¿Está seguro de activar este periodo?');
}

function confirmarCambiarEstado(id) {
    return confirm('¿Está seguro de cambiar el estado?');
}

// ========================= PRODUCTOS =========================

// Función para limpiar filtros de productos
function limpiarFiltrosProductos() {
    // Limpiar todos los campos del formulario de filtros
    const form = document.querySelector('form');
    if (form) {
        const inputs = form.querySelectorAll('input[type="text"], input[type="search"], select');
        inputs.forEach(input => {
            if (input.type === 'text' || input.type === 'search') {
                input.value = '';
            } else if (input.tagName === 'SELECT') {
                input.selectedIndex = 0;
            }
        });
        
        // Enviar el formulario limpio
        form.submit();
    }
}

// Funciones de navegación para productos
function navegarACrearProducto() {
    window.location.href = '/proyecto_cabania/productos/create';
}

function navegarAEditarProducto(id) {
    window.location.href = `/proyecto_cabania/productos/${id}/edit`;
}

function navegarAListadoProductos() {
    window.location.href = '/proyecto_cabania/productos';
}

// Funciones de confirmación para acciones de productos
function confirmarAccionProducto(url, accion) {
    if (confirm(`¿Está seguro de ${accion}?`)) {
        window.location.href = url;
    }
}

function confirmarEliminacionProducto(id) {
    if (confirm('¿Está seguro de eliminar este producto?')) {
        window.location.href = `/proyecto_cabania/productos/${id}/delete`;
    }
}

function confirmarRecuperacionProducto(id) {
    if (confirm('¿Está seguro de recuperar este producto?')) {
        window.location.href = `/proyecto_cabania/productos/${id}/restore`;
    }
}

// Función para auto-submit del selector de registros por página
function autoSubmitRegistrosPorPagina() {
    const selector = document.getElementById('registros_por_pagina');
    if (selector) {
        selector.addEventListener('change', function() {
            this.form.submit();
        });
    }
}

// ========================= REPORTES =========================

// Función para cambiar registros por página en reportes
function changePerPage(value) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('per_page', value);
    currentUrl.searchParams.set('page', '1'); // Reset to first page
    window.location.href = currentUrl.toString();
}

// Función para aplicar filtros globales en dashboard
function applyGlobalFilters() {
    const loading = document.getElementById('loading');
    if (loading) {
        loading.style.display = 'block';
        setTimeout(() => {
            if (typeof cargarGraficos === 'function') cargarGraficos();
            if (typeof cargarEstadisticasReportes === 'function') cargarEstadisticasReportes();
            loading.style.display = 'none';
        }, 1000);
    }
}

// Funciones específicas del dashboard - definidas globalmente para uso desde PHP
let charts = {};

function cargarGraficos() {
    if (typeof cargarGraficoComentarios === 'function') cargarGraficoComentarios();
    if (typeof cargarGraficoProductos === 'function') cargarGraficoProductos();
    if (typeof cargarGraficoIngresos === 'function') cargarGraficoIngresos();
    if (typeof cargarGraficoCabanas === 'function') cargarGraficoCabanas();
}

// Inicialización de dashboard de reportes
function initReportesDashboard() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js no está disponible');
        return;
    }
    
    // Las funciones específicas de gráficos se definirán inline en el dashboard
    // ya que dependen de datos PHP dinámicos
}

// Auto-submit para selectores de página en reportes
function initReportesPerPage() {
    const perPageSelector = document.getElementById('per_page');
    if (perPageSelector) {
        perPageSelector.addEventListener('change', function() {
            changePerPage(this.value);
        });
    }
}

// === DASHBOARD FUNCTIONS ===
// Variables globales para los gráficos del dashboard
let dashboardCharts = {};

// Función para aplicar filtros globales del dashboard
function applyGlobalDashboardFilters() {
    const loading = document.getElementById('loading');
    if (loading) {
        loading.style.display = 'block';
        setTimeout(() => {
            if (typeof cargarGraficos === 'function') {
                cargarGraficos();
            }
            if (typeof cargarEstadisticasReportes === 'function') {
                cargarEstadisticasReportes();
            }
            loading.style.display = 'none';
        }, 1000);
    }
}

// Cargar estadísticas de reportes (simuladas)
function cargarEstadisticasReportes() {
    setTimeout(() => {
        const stats = {
            'total-consumos': '1,247',
            'ingresos-consumos': '$45,890',
            'total-productos': '156',
            'categorias-activas': '12',
            'temporadas-analizadas': '8',
            'reservas-temporada': '892',
            'grupos-etarios': '5',
            'huespedes-analizados': '1,340',
            'meses-analizados': '12',
            'productos-vendidos': '3,456'
        };

        Object.entries(stats).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        });
    }, 500);
}

// Inicialización del dashboard
function initDashboard() {
    if (typeof cargarGraficos === 'function') {
        cargarGraficos();
    }
    cargarEstadisticasReportes();
}

// === SALIDAS FUNCTIONS ===
// Funciones para filtros de búsqueda
function limpiarFiltros() {
    const form = document.querySelector('form');
    if (form) {
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (input.type === 'text' || input.type === 'date' || input.type === 'number') {
                input.value = '';
            } else if (input.type === 'select-one') {
                input.selectedIndex = 0;
            }
        });
        form.submit();
    }
}

function buscarHoy() {
    const fechaHoy = new Date().toISOString().split('T')[0];
    const fechaDesde = document.querySelector('[name="fecha_desde"]');
    const fechaHasta = document.querySelector('[name="fecha_hasta"]');
    
    if (fechaDesde) fechaDesde.value = fechaHoy;
    if (fechaHasta) fechaHasta.value = fechaHoy;
    
    const form = document.querySelector('form');
    if (form) form.submit();
}

function buscarSemana() {
    const hoy = new Date();
    const hace7Dias = new Date(hoy);
    hace7Dias.setDate(hoy.getDate() - 7);
    
    const fechaDesde = document.querySelector('[name="fecha_desde"]');
    const fechaHasta = document.querySelector('[name="fecha_hasta"]');
    
    if (fechaDesde) fechaDesde.value = hace7Dias.toISOString().split('T')[0];
    if (fechaHasta) fechaHasta.value = hoy.toISOString().split('T')[0];
    
    const form = document.querySelector('form');
    if (form) form.submit();
}

function buscarPendientes() {
    const estadoSelect = document.querySelector('[name="estado"]');
    if (estadoSelect) {
        // Buscar opción "pendiente" o similar
        for (let option of estadoSelect.options) {
            if (option.text.toLowerCase().includes('pendiente')) {
                option.selected = true;
                break;
            }
        }
        const form = document.querySelector('form');
        if (form) form.submit();
    }
}

function buscarFinalizadas() {
    const estadoSelect = document.querySelector('[name="estado"]');
    if (estadoSelect) {
        // Buscar opción "finalizada" o similar
        for (let option of estadoSelect.options) {
            if (option.text.toLowerCase().includes('finalizada')) {
                option.selected = true;
                break;
            }
        }
        const form = document.querySelector('form');
        if (form) form.submit();
    }
}

function buscarUltimaSemana() {
    buscarSemana(); // Usar la misma lógica
}

// Funciones de exportación
function exportarResultados() {
    window.print();
}

function exportarEstadisticas() {
    window.print();
}

// Auto-refresh para estadísticas (cada 5 minutos) - DESHABILITADO
function initAutoRefresh() {
    // setInterval(function() {
    //     if (!document.querySelector('.modal.show')) {
    //         window.location.reload();
    //     }
    // }, 300000);
}

// Inicialización de funciones de salidas
function initSalidas() {
    // Event delegation para botones de acciones rápidas
    document.addEventListener('click', function(e) {
        if (e.target.hasAttribute('data-action')) {
            const action = e.target.getAttribute('data-action');
            switch (action) {
                case 'limpiar-filtros':
                    limpiarFiltros();
                    break;
                case 'buscar-hoy':
                    buscarHoy();
                    break;
                case 'buscar-semana':
                    buscarSemana();
                    break;
                case 'buscar-pendientes':
                    buscarPendientes();
                    break;
                case 'buscar-finalizadas':
                    buscarFinalizadas();
                    break;
                case 'buscar-ultima-semana':
                    buscarUltimaSemana();
                    break;
                case 'exportar-resultados':
                    exportarResultados();
                    break;
                case 'exportar-estadisticas':
                    exportarEstadisticas();
                    break;
                case 'print':
                    window.print();
                    break;
                case 'reload':
                    window.location.reload();
                    break;
            }
        }
    });
}

// === TIPOS_CONTACTOS FUNCTIONS ===
// Funciones de confirmación
function confirmarDesactivar() {
    return confirm('¿Está seguro de desactivar este tipo de contacto?');
}

function confirmarActivar() {
    return confirm('¿Está seguro de activar este tipo de contacto?');
}

function confirmarCambioEstado() {
    return confirm('¿Está seguro de cambiar el estado?');
}

// Funciones para Chart.js de tipos de contactos
function initTiposContactosCharts(tiposData, monthlyData, usageSummary) {
    const colors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
        '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384',
        '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
    ];

    // Gráfico de distribución (donut)
    const activeTypesWithUsage = usageSummary.filter(t => t.total > 0);
    
    if (activeTypesWithUsage.length > 0) {
        const distributionCtx = document.getElementById('distributionChart');
        if (distributionCtx) {
            new Chart(distributionCtx, {
                type: 'doughnut',
                data: {
                    labels: activeTypesWithUsage.map(t => t.tipocontacto_descripcion),
                    datasets: [{
                        data: activeTypesWithUsage.map(t => t.total),
                        backgroundColor: colors.slice(0, activeTypesWithUsage.length),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                    return context.label + ': ' + context.raw + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // Gráfico mensual (líneas)
    if (monthlyData && monthlyData.length > 0) {
        const monthlyChartData = {};
        const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        
        // Inicializar datos para tipos activos
        usageSummary.forEach(tipo => {
            monthlyChartData[tipo.tipocontacto_descripcion] = new Array(12).fill(0);
        });
        
        // Llenar con datos reales
        monthlyData.forEach(item => {
            if (item.mes && monthlyChartData[item.tipocontacto_descripcion]) {
                monthlyChartData[item.tipocontacto_descripcion][item.mes - 1] = parseInt(item.cantidad);
            }
        });

        const monthlyCtx = document.getElementById('monthlyChart');
        if (monthlyCtx) {
            const datasets = Object.keys(monthlyChartData).map((tipo, index) => ({
                label: tipo,
                data: monthlyChartData[tipo],
                borderColor: colors[index],
                backgroundColor: colors[index] + '20',
                borderWidth: 3,
                fill: false,
                tension: 0.1
            }));

            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Número de Contactos'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Mes'
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        }
    }
}

// Inicialización de tipos_contactos
function initTiposContactos() {
    // Event delegation para confirmaciones
    document.addEventListener('click', function(e) {
        if (e.target.hasAttribute('data-confirm-action')) {
            const action = e.target.getAttribute('data-confirm-action');
            let confirmed = false;
            
            switch (action) {
                case 'desactivar':
                    confirmed = confirmarDesactivar();
                    break;
                case 'activar':
                    confirmed = confirmarActivar();
                    break;
                case 'cambiar-estado':
                    confirmed = confirmarCambioEstado();
                    break;
            }
            
            if (!confirmed) {
                e.preventDefault();
                return false;
            }
        }
    });
}

// ============================================
// TIPOS_SERVICIOS - Confirmation Functions
// ============================================
function confirmarDesactivarTipoServicio() {
    return confirm('¿Está seguro de desactivar este tipo de servicio?');
}

function confirmarActivarTipoServicio() {
    return confirm('¿Está seguro de activar este tipo de servicio?');
}

function confirmarCambioEstadoTipoServicio() {
    return confirm('¿Está seguro de cambiar el estado?');
}

// Chart.js Functions para Tipos de Servicios
function initTiposServiciosCharts(data) {
    // Colores para los gráficos
    const colors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
        '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384',
        '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
    ];

    // Gráfico de distribución (donut) - solo tipos activos con servicios
    const activeTypesWithServices = data.tipos.filter(t => t.tiposervicio_estado == 1 && t.servicios_count > 0);
    
    if (activeTypesWithServices.length > 0) {
        const distributionCtx = document.getElementById('distributionChart').getContext('2d');
        new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: activeTypesWithServices.map(t => t.tiposervicio_descripcion),
                datasets: [{
                    data: activeTypesWithServices.map(t => t.servicios_count),
                    backgroundColor: colors.slice(0, activeTypesWithServices.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + context.raw + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Gráfico mensual (líneas) - solo si hay datos y está disponible monthlyData
    if (data.monthly && data.monthly.length > 0) {
        const monthlyChartData = {};
        const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        
        // Inicializar datos para tipos activos
        data.tipos.forEach(tipo => {
            if (tipo.tiposervicio_estado == 1) {
                monthlyChartData[tipo.tiposervicio_descripcion] = new Array(12).fill(0);
            }
        });
        
        // Llenar con datos reales
        data.monthly.forEach(item => {
            if (item.mes && monthlyChartData[item.tiposervicio_descripcion]) {
                monthlyChartData[item.tiposervicio_descripcion][item.mes - 1] = parseInt(item.cantidad);
            }
        });

        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const datasets = Object.keys(monthlyChartData).map((tipo, index) => ({
            label: tipo,
            data: monthlyChartData[tipo],
            borderColor: colors[index],
            backgroundColor: colors[index] + '20',
            borderWidth: 3,
            fill: false,
            tension: 0.1
        }));

        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Número de Servicios'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Mes (' + data.year + ')'
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    }
}

function initTiposServicios() {
    // Event delegation para confirmation dialogs
    document.addEventListener('click', function(e) {
        if (e.target.closest('[data-confirm-action="desactivar-tipo-servicio"]')) {
            if (!confirmarDesactivarTipoServicio()) {
                e.preventDefault();
                return false;
            }
        } else if (e.target.closest('[data-confirm-action="activar-tipo-servicio"]')) {
            if (!confirmarActivarTipoServicio()) {
                e.preventDefault();
                return false;
            }
        } else if (e.target.closest('[data-confirm-action="cambiar-estado-tipo-servicio"]')) {
            if (!confirmarCambioEstadoTipoServicio()) {
                e.preventDefault();
                return false;
            }
        }
    });
}

// ===========================
// CATÁLOGO PÚBLICO - FUNCIONALIDADES
// ===========================

/**
 * Funcionalidad específica para el catálogo público de cabañas
 * Maneja filtros, búsqueda, disponibilidad y reservas
 */

// Variables globales para el catálogo
let catalogData = {
    currentCabinId: null,
    currentCabinName: '',
    currentCabinPrice: 0,
    selectedDates: {
        checkin: null,
        checkout: null
    }
};

// Inicialización específica del catálogo
document.addEventListener('DOMContentLoaded', function() {
    // Solo inicializar si estamos en la página del catálogo
    if (document.querySelector('.catalog-results')) {
        initCatalogFunctionality();
    }
});

/**
 * Inicializar toda la funcionalidad del catálogo
 */
function initCatalogFunctionality() {
    console.log('Inicializando funcionalidad del catálogo público');
    
    // Inicializar filtros
    initCatalogFilters();
    
    // Inicializar botones de disponibilidad
    initAvailabilityButtons();
    
    // Inicializar modal de disponibilidad
    initAvailabilityModal();
    
    // Inicializar botones de reserva
    initReservationButtons();
    
    // Inicializar efectos visuales
    initCatalogVisualEffects();
    
    console.log('Catálogo público inicializado correctamente');
}

/**
 * Inicializar sistema de filtros del catálogo
 */
function initCatalogFilters() {
    const filterForm = document.querySelector('.minimal-form');
    const clearFiltersBtn = document.querySelector('.btn-clear');
    
    if (!filterForm) {
        console.warn('Formulario de filtros no encontrado');
        return;
    }
    
    console.log('Inicializando filtros del catálogo');
    
    // Auto-submit en cambio de select
    const selectInputs = filterForm.querySelectorAll('select');
    selectInputs.forEach(select => {
        select.addEventListener('change', function() {
            console.log('Select cambiado:', this.name, '=', this.value);
            showFilterLoading();
            filterForm.submit();
        });
    });
    
    // Submit manual para campos de texto y número
    const submitBtn = filterForm.querySelector('.btn-search');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Botón de búsqueda clickeado');
            showFilterLoading();
            filterForm.submit();
        });
    }
    
    // Submit en Enter para campos de texto
    const textInputs = filterForm.querySelectorAll('input[type="text"], input[type="number"]');
    textInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                console.log('Enter presionado en:', this.name);
                showFilterLoading();
                filterForm.submit();
            }
        });
    });
    
    // Limpiar filtros
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Limpiando filtros');
            
            // Limpiar todos los inputs
            const inputs = filterForm.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.type === 'text' || input.type === 'number') {
                    input.value = '';
                } else if (input.type === 'select-one') {
                    input.selectedIndex = 0;
                }
            });
            
            // Mostrar loading y redirigir
            showFilterLoading();
            window.location.href = '/catalogo';
        });
    }
    
    console.log('Sistema de filtros inicializado correctamente');
}

/**
 * Mostrar indicador de carga para filtros
 */
function showFilterLoading() {
    const filterForm = document.querySelector('.minimal-form');
    if (!filterForm) return;
    
    // Crear overlay de carga si no existe
    let loadingOverlay = document.getElementById('filter-loading');
    if (!loadingOverlay) {
        loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'filter-loading';
        loadingOverlay.innerHTML = `
            <div class="loading-content">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Buscando...</span>
                </div>
                <p class="mt-2 mb-0">Buscando cabañas...</p>
            </div>
        `;
        loadingOverlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(248, 250, 252, 0.9);
            backdrop-filter: blur(5px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        const loadingContent = loadingOverlay.querySelector('.loading-content');
        loadingContent.style.cssText = `
            text-align: center;
            color: var(--secondary-700);
        `;
        
        document.body.appendChild(loadingOverlay);
    }
    
    // Mostrar overlay
    loadingOverlay.style.display = 'flex';
    requestAnimationFrame(() => {
        loadingOverlay.style.opacity = '1';
    });
}

/**
 * Inicializar botones de disponibilidad
 */
function initAvailabilityButtons() {
    const availabilityButtons = document.querySelectorAll('.btn-availability');
    
    availabilityButtons.forEach(button => {
        button.addEventListener('click', function() {
            const cabinId = this.dataset.cabinId;
            const cabinName = this.dataset.cabinName;
            const cabinPrice = parseFloat(this.dataset.cabinPrice) || 0;
            
            console.log('Abriendo disponibilidad para cabaña:', cabinId, cabinName);
            
            // Guardar datos de la cabaña actual
            catalogData.currentCabinId = cabinId;
            catalogData.currentCabinName = cabinName;
            catalogData.currentCabinPrice = cabinPrice;
            
            // Mostrar modal de disponibilidad
            showAvailabilityModal();
        });
    });
    
    console.log(`Inicializados ${availabilityButtons.length} botones de disponibilidad`);
}

/**
 * Mostrar modal de disponibilidad
 */
function showAvailabilityModal() {
    const modal = document.getElementById('availabilityModal');
    const modalTitle = document.getElementById('modalCabinName');
    
    if (!modal) {
        console.error('Modal de disponibilidad no encontrado');
        return;
    }
    
    // Actualizar título del modal
    if (modalTitle) {
        modalTitle.textContent = catalogData.currentCabinName;
    }
    
    // Resetear fechas y resultados
    resetAvailabilityModal();
    
    // Mostrar modal
    modal.style.display = 'flex';
    modal.style.opacity = '0';
    requestAnimationFrame(() => {
        modal.style.opacity = '1';
    });
    
    console.log('Modal de disponibilidad mostrado');
}

/**
 * Resetear modal de disponibilidad
 */
function resetAvailabilityModal() {
    // Limpiar fechas
    const checkinDate = document.getElementById('checkinDate');
    const checkoutDate = document.getElementById('checkoutDate');
    
    if (checkinDate) checkinDate.value = '';
    if (checkoutDate) checkoutDate.value = '';
    
    // Limpiar resultados
    const availabilityResult = document.getElementById('availabilityResult');
    if (availabilityResult) {
        availabilityResult.innerHTML = '';
    }
    
    // Deshabilitar botón de proceder
    const proceedBtn = document.querySelector('.btn-proceed-reservation');
    if (proceedBtn) {
        proceedBtn.disabled = true;
    }
    
    // Resetear datos seleccionados
    catalogData.selectedDates.checkin = null;
    catalogData.selectedDates.checkout = null;
}

/**
 * Inicializar modal de disponibilidad
 */
function initAvailabilityModal() {
    const modal = document.getElementById('availabilityModal');
    if (!modal) return;
    
    // Botón cerrar modal
    const closeButtons = modal.querySelectorAll('.close, [data-dismiss="modal"]');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            hideAvailabilityModal();
        });
    });
    
    // Cerrar modal al hacer click en el backdrop
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            hideAvailabilityModal();
        }
    });
    
    // Botón consultar disponibilidad
    const checkAvailabilityBtn = modal.querySelector('.btn-check-availability');
    if (checkAvailabilityBtn) {
        checkAvailabilityBtn.addEventListener('click', function() {
            checkCabinAvailability();
        });
    }
    
    // Event listeners para cambios de fecha
    const checkinDate = document.getElementById('checkinDate');
    const checkoutDate = document.getElementById('checkoutDate');
    
    if (checkinDate) {
        checkinDate.addEventListener('change', function() {
            // Actualizar fecha mínima de checkout
            if (checkoutDate && this.value) {
                const nextDay = new Date(this.value);
                nextDay.setDate(nextDay.getDate() + 1);
                checkoutDate.min = nextDay.toISOString().split('T')[0];
                
                // Si checkout es anterior a checkin + 1, resetear checkout
                if (checkoutDate.value && checkoutDate.value <= this.value) {
                    checkoutDate.value = '';
                }
            }
        });
    }
    
    // Botón proceder con reserva
    const proceedBtn = modal.querySelector('.btn-proceed-reservation');
    if (proceedBtn) {
        proceedBtn.addEventListener('click', function() {
            proceedWithReservation();
        });
    }
    
    console.log('Modal de disponibilidad inicializado');
}

/**
 * Ocultar modal de disponibilidad
 */
function hideAvailabilityModal() {
    const modal = document.getElementById('availabilityModal');
    if (!modal) return;
    
    modal.style.opacity = '0';
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
    
    console.log('Modal de disponibilidad ocultado');
}

/**
 * Consultar disponibilidad de la cabaña
 */
function checkCabinAvailability() {
    const checkinDate = document.getElementById('checkinDate');
    const checkoutDate = document.getElementById('checkoutDate');
    const availabilityResult = document.getElementById('availabilityResult');
    
    if (!checkinDate || !checkoutDate || !availabilityResult) {
        console.error('Elementos de disponibilidad no encontrados');
        return;
    }
    
    const checkin = checkinDate.value;
    const checkout = checkoutDate.value;
    
    if (!checkin || !checkout) {
        showAvailabilityError('Por favor seleccione las fechas de entrada y salida.');
        return;
    }
    
    if (checkout <= checkin) {
        showAvailabilityError('La fecha de salida debe ser posterior a la fecha de entrada.');
        return;
    }
    
    console.log('Consultando disponibilidad:', checkin, 'a', checkout);
    
    // Mostrar loading
    availabilityResult.innerHTML = `
        <div class="availability-loading">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Consultando...</span>
            </div>
            <p class="mt-2">Consultando disponibilidad...</p>
        </div>
    `;
    
    // Simular consulta de disponibilidad (reemplazar con llamada AJAX real)
    setTimeout(() => {
        showAvailabilitySuccess(checkin, checkout);
    }, 1500);
}

/**
 * Mostrar resultado exitoso de disponibilidad
 */
function showAvailabilitySuccess(checkin, checkout) {
    const availabilityResult = document.getElementById('availabilityResult');
    if (!availabilityResult) return;
    
    // Calcular noches
    const checkinDate = new Date(checkin);
    const checkoutDate = new Date(checkout);
    const nights = Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24));
    const totalPrice = nights * catalogData.currentCabinPrice;
    
    // Guardar fechas seleccionadas
    catalogData.selectedDates.checkin = checkin;
    catalogData.selectedDates.checkout = checkout;
    
    availabilityResult.innerHTML = `
        <div class="availability-success">
            <div class="alert alert-success">
                <h5><i class="fas fa-check-circle"></i> ¡Disponible!</h5>
                <p class="mb-2">La cabaña está disponible para las fechas seleccionadas.</p>
                
                <div class="reservation-summary">
                    <div class="summary-row">
                        <span><i class="fas fa-calendar-check"></i> Entrada:</span>
                        <strong>${formatDate(checkin)}</strong>
                    </div>
                    <div class="summary-row">
                        <span><i class="fas fa-calendar-times"></i> Salida:</span>
                        <strong>${formatDate(checkout)}</strong>
                    </div>
                    <div class="summary-row">
                        <span><i class="fas fa-moon"></i> Noches:</span>
                        <strong>${nights}</strong>
                    </div>
                    <div class="summary-row">
                        <span><i class="fas fa-dollar-sign"></i> Precio por noche:</span>
                        <strong>$${catalogData.currentCabinPrice.toFixed(2)}</strong>
                    </div>
                    <div class="summary-row summary-total">
                        <span><i class="fas fa-calculator"></i> Total:</span>
                        <strong>$${totalPrice.toFixed(2)}</strong>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Habilitar botón de proceder
    const proceedBtn = document.querySelector('.btn-proceed-reservation');
    if (proceedBtn) {
        proceedBtn.disabled = false;
    }
    
    console.log('Disponibilidad confirmada para', nights, 'noches');
}

/**
 * Mostrar error de disponibilidad
 */
function showAvailabilityError(message) {
    const availabilityResult = document.getElementById('availabilityResult');
    if (!availabilityResult) return;
    
    availabilityResult.innerHTML = `
        <div class="availability-error">
            <div class="alert alert-danger">
                <h5><i class="fas fa-times-circle"></i> Error</h5>
                <p class="mb-0">${message}</p>
            </div>
        </div>
    `;
    
    // Deshabilitar botón de proceder
    const proceedBtn = document.querySelector('.btn-proceed-reservation');
    if (proceedBtn) {
        proceedBtn.disabled = true;
    }
}

/**
 * Proceder con la reserva
 */
function proceedWithReservation() {
    if (!catalogData.selectedDates.checkin || !catalogData.selectedDates.checkout) {
        console.error('Fechas no seleccionadas');
        return;
    }
    
    console.log('Procediendo con la reserva:', catalogData);
    
    // Llenar el formulario oculto con los datos
    const reservationForm = document.getElementById('reservationForm');
    const cabinIdInput = document.getElementById('reserveCabinId');
    const dateStartInput = document.getElementById('reserveDateStart');
    const dateEndInput = document.getElementById('reserveDateEnd');
    
    if (!reservationForm || !cabinIdInput || !dateStartInput || !dateEndInput) {
        console.error('Formulario de reserva no encontrado');
        return;
    }
    
    // Llenar campos
    cabinIdInput.value = catalogData.currentCabinId;
    dateStartInput.value = catalogData.selectedDates.checkin;
    dateEndInput.value = catalogData.selectedDates.checkout;
    
    // Mostrar confirmación antes de enviar
    if (confirm('¿Está seguro de que desea proceder con esta reserva?')) {
        // Cerrar modal
        hideAvailabilityModal();
        
        // Mostrar loading
        showFilterLoading();
        
        // Enviar formulario
        reservationForm.submit();
    }
}

/**
 * Inicializar botones de reserva directa
 */
function initReservationButtons() {
    const reserveButtons = document.querySelectorAll('.btn-reserve');
    
    reserveButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Por ahora los botones están deshabilitados
            // Se habilitarán cuando se seleccionen fechas
            console.log('Botón de reserva clickeado (deshabilitado)');
        });
    });
    
    console.log(`Inicializados ${reserveButtons.length} botones de reserva`);
}

/**
 * Inicializar efectos visuales del catálogo
 */
function initCatalogVisualEffects() {
    // Animaciones de entrada para las cards
    const cabinCards = document.querySelectorAll('.cabin-card');
    
    if (cabinCards.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 100);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        
        cabinCards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s ease-out';
            observer.observe(card);
        });
    }
    
    // Efectos hover mejorados
    cabinCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
            this.style.boxShadow = '0 15px 35px rgba(0, 0, 0, 0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '';
        });
    });
    
    console.log('Efectos visuales del catálogo inicializados');
}

/**
 * Formatear fecha para mostrar
 */
function formatDate(dateString) {
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    return new Date(dateString).toLocaleDateString('es-ES', options);
}

console.log('Sistema de JavaScript del catálogo cargado');

// ============================================
// RESERVAS - Functions and Event Handlers
// ============================================
function confirmarAnularReserva() {
    return confirm('¿Está seguro de anular esta reserva?');
}

function confirmarReactivarReserva() {
    return confirm('¿Está seguro de reactivar esta reserva?');
}

function confirmarAccionReserva(accion) {
    const mensajes = {
        'anular': '¿Está seguro de anular esta reserva?',
        'reactivar': '¿Está seguro de reactivar esta reserva?',
        'eliminar': '¿Está seguro de eliminar esta reserva?'
    };
    return confirm(mensajes[accion] || '¿Está seguro de realizar esta acción?');
}

// Validación de fechas para reservas
function validarFechasReserva() {
    const fechaInicio = document.querySelector('input[name="reserva_fhinicio"]');
    const fechaFin = document.querySelector('input[name="reserva_fhfin"]');
    
    if (!fechaInicio || !fechaFin) return true;
    
    const inicio = new Date(fechaInicio.value);
    const fin = new Date(fechaFin.value);
    const hoy = new Date();
    
    if (inicio < hoy) {
        alert('La fecha de inicio no puede ser anterior a hoy');
        fechaInicio.focus();
        return false;
    }
    
    if (fin <= inicio) {
        alert('La fecha de fin debe ser posterior a la fecha de inicio');
        fechaFin.focus();
        return false;
    }
    
    return true;
}

// Validación para formulario de reserva
function validarFormularioReserva(form) {
    // Validación DNI comentada: campo persona_dni no existe en BD
    // const dni = form.querySelector('input[name="persona_dni"]');
    // if (dni && dni.value) {
    //     const dniValue = parseInt(dni.value);
    //     if (isNaN(dniValue) || dniValue <= 0) {
    //         alert('Ingrese un DNI válido');
    //         dni.focus();
    //         return false;
    //     }
    // }
    
    // Validar fechas
    if (!validarFechasReserva()) {
        return false;
    }
    
    // Validar cabaña seleccionada (si existe el campo)
    const cabania = form.querySelector('select[name="rela_cabania"]');
    if (cabania && cabania.value === '') {
        alert('Debe seleccionar una cabaña');
        cabania.focus();
        return false;
    }
    
    return true;
}

// Limpiar formulario de búsqueda específico para reservas
function limpiarFormularioReservas(form) {
    if (!form) return;
    
    // Limpiar campos específicos de reservas
    const campos = form.querySelectorAll('input[type="datetime-local"], select');
    campos.forEach(campo => {
        if (campo.tagName === 'SELECT') {
            campo.selectedIndex = 0;
        } else {
            campo.value = '';
        }
    });
    
    // Recargar página sin parámetros
    window.location.href = window.location.pathname;
}

// Auto-submit para selector de registros por página
function autoSubmitPaginacion(select) {
    if (select && select.form) {
        select.form.submit();
    }
}

// Confirmar acción con mensaje personalizado
function confirmarAccion(url, accion) {
    if (confirm(`¿Está seguro de ${accion}?`)) {
        window.location.href = url;
        return true;
    }
    return false;
}

// Inicialización para módulo de reservas
function initReservas() {
    // Event delegation para confirmation dialogs
    document.addEventListener('click', function(e) {
        const target = e.target;
        
        if (target.matches('[data-confirm-action="anular-reserva"]')) {
            if (!confirmarAnularReserva()) {
                e.preventDefault();
                return false;
            }
        } else if (target.matches('[data-confirm-action="reactivar-reserva"]')) {
            if (!confirmarReactivarReserva()) {
                e.preventDefault();
                return false;
            }
        }
    });
    
    // Validación automática de formularios de reserva
    const formsReserva = document.querySelectorAll('form[data-form="reserva"]');
    formsReserva.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validarFormularioReserva(this)) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // Auto-submit para selector de paginación
    const paginationSelect = document.querySelector('select[name="registros_por_pagina"]');
    if (paginationSelect) {
        paginationSelect.addEventListener('change', function() {
            autoSubmitPaginacion(this);
        });
    }
    
    // Configurar límites de fecha mínima
    const fechasInput = document.querySelectorAll('input[type="datetime-local"]');
    fechasInput.forEach(input => {
        if (!input.hasAttribute('min')) {
            const hoy = new Date();
            const fechaMinima = hoy.toISOString().slice(0, 16);
            input.setAttribute('min', fechaMinima);
        }
    });
    
    // Configurar búsqueda de cabañas disponibles
    const btnBuscarCabanias = document.getElementById('buscarCabanias');
    if (btnBuscarCabanias) {
        btnBuscarCabanias.addEventListener('click', buscarCabaniasDisponibles);
    }
    
    // Auto-generar resumen cuando se selecciona cantidad de personas
    const cantidadPersonas = document.getElementById('reserva_cantidadpersonas');
    if (cantidadPersonas) {
        cantidadPersonas.addEventListener('change', function() {
            if (this.value && document.querySelector('.cabania-card.border-primary')) {
                generarResumenReserva();
            }
        });
    }
}

/**
 * Buscar cabañas disponibles (para reserva online)
 */
function buscarCabaniasDisponibles() {
    const fechaInicio = document.getElementById('reserva_fechainicio');
    const fechaFin = document.getElementById('reserva_fechafin');
    
    if (!validarFechasReserva(fechaInicio, fechaFin)) {
        return;
    }
    
    const btnBuscar = document.getElementById('buscarCabanias');
    const contenedorCabanias = document.getElementById('cabanias-disponibles');
    
    // Mostrar loading
    btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';
    btnBuscar.disabled = true;
    
    const formData = new FormData();
    formData.append('fecha_inicio', fechaInicio.value);
    formData.append('fecha_fin', fechaFin.value);
    
    fetch('/reservas/cabanias-disponibles', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(cabanias => {
        mostrarCabaniasDisponibles(cabanias, contenedorCabanias);
        mostrarPaso(2); // Mostrar el siguiente paso del formulario
    })
    .catch(error => {
        console.error('Error al buscar cabañas:', error);
        mostrarNotificacion('Error al buscar cabañas disponibles', 'error');
    })
    .finally(() => {
        btnBuscar.innerHTML = '<i class="fas fa-search"></i> Buscar Cabañas Disponibles';
        btnBuscar.disabled = false;
    });
}

/**
 * Mostrar cabañas disponibles en el contenedor
 */
function mostrarCabaniasDisponibles(cabanias, contenedor) {
    if (!contenedor) return;
    
    let html = '';
    
    if (cabanias.length === 0) {
        html = `
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    No hay cabañas disponibles para las fechas seleccionadas. 
                    Por favor, seleccione otras fechas.
                </div>
            </div>
        `;
    } else {
        cabanias.forEach(cabania => {
            html += `
                <div class="col-md-6 mb-3">
                    <div class="card cabania-card" data-cabania-id="${cabania.id_cabania}">
                        <div class="card-body">
                            <h6 class="card-title">${cabania.cabania_nombre}</h6>
                            <p class="card-text">
                                <small class="text-muted">Código: ${cabania.cabania_codigo}</small><br>
                                <small class="text-muted">Capacidad: ${cabania.cabania_capacidad} personas</small>
                            </p>
                            <button type="button" class="btn btn-outline-primary btn-sm seleccionar-cabania" 
                                    data-cabania-id="${cabania.id_cabania}" 
                                    data-cabania-nombre="${cabania.cabania_nombre}">
                                <i class="fas fa-check"></i> Seleccionar
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    
    contenedor.innerHTML = html;
    
    // Configurar event listeners para selección de cabaña
    contenedor.querySelectorAll('.seleccionar-cabania').forEach(btn => {
        btn.addEventListener('click', function() {
            seleccionarCabania(this);
        });
    });
}

/**
 * Seleccionar una cabaña
 */
function seleccionarCabania(boton) {
    const contenedor = boton.closest('#cabanias-disponibles');
    const cabaniasCards = contenedor.querySelectorAll('.cabania-card');
    
    // Limpiar selecciones previas
    cabaniasCards.forEach(card => {
        card.classList.remove('border-primary');
        const btn = card.querySelector('.seleccionar-cabania');
        btn.innerHTML = '<i class="fas fa-check"></i> Seleccionar';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline-primary');
    });
    
    // Marcar la cabaña seleccionada
    const cardSeleccionada = boton.closest('.cabania-card');
    cardSeleccionada.classList.add('border-primary');
    boton.innerHTML = '<i class="fas fa-check-circle"></i> Seleccionada';
    boton.classList.add('btn-primary');
    boton.classList.remove('btn-outline-primary');
    
    // Establecer el valor en el campo oculto
    const inputCabania = document.getElementById('rela_cabania');
    if (inputCabania) {
        inputCabania.value = boton.dataset.cabaniasId;
    }
    
    // Mostrar el siguiente paso
    mostrarPaso(3);
    
    // Mostrar notificación
    mostrarNotificacion(`Cabaña "${boton.dataset.cabaniasNombre}" seleccionada`, 'success');
}

/**
 * Mostrar paso del formulario (para reserva online)
 */
function mostrarPaso(numeroPaso) {
    const pasos = document.querySelectorAll('fieldset[id^="paso"]');
    
    pasos.forEach((paso, index) => {
        if (index + 1 <= numeroPaso) {
            paso.style.display = 'block';
        }
    });
    
    // Scroll suave al paso actual
    const pasoActual = document.getElementById(`paso${numeroPaso}`);
    if (pasoActual) {
        pasoActual.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

/**
 * Generar resumen de reserva
 */
function generarResumenReserva() {
    const fechaInicio = document.getElementById('reserva_fechainicio');
    const fechaFin = document.getElementById('reserva_fechafin');
    const cantidadPersonas = document.getElementById('reserva_cantidadpersonas');
    const metodoPago = document.getElementById('rela_metodopago');
    const observaciones = document.getElementById('reserva_observaciones');
    
    const cabaniaNombre = document.querySelector('.cabania-card.border-primary .card-title')?.textContent || 'No seleccionada';
    
    if (!fechaInicio?.value || !fechaFin?.value) return;
    
    const inicio = new Date(fechaInicio.value);
    const fin = new Date(fechaFin.value);
    const dias = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));
    
    const resumen = document.getElementById('resumen-reserva');
    if (!resumen) return;
    
    const metodoPagoTexto = metodoPago?.selectedOptions[0]?.text || 'No especificado';
    const observacionesTexto = observaciones?.value || 'Ninguna';
    
    resumen.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-calendar"></i> Fechas</h6>
                <p class="mb-1"><strong>Llegada:</strong> ${inicio.toLocaleDateString('es-ES')} ${inicio.toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'})}</p>
                <p class="mb-1"><strong>Salida:</strong> ${fin.toLocaleDateString('es-ES')} ${fin.toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'})}</p>
                <p class="mb-3"><em>Total: ${dias} día${dias > 1 ? 's' : ''}</em></p>
                
                <h6><i class="fas fa-home"></i> Cabaña</h6>
                <p class="mb-3">${cabaniaNombre}</p>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-users"></i> Huéspedes</h6>
                <p class="mb-3">${cantidadPersonas?.value || 'No especificado'} persona${(cantidadPersonas?.value > 1) ? 's' : ''}</p>
                
                <h6><i class="fas fa-credit-card"></i> Pago</h6>
                <p class="mb-3">${metodoPagoTexto}</p>
                
                ${observacionesTexto !== 'Ninguna' ? `
                    <h6><i class="fas fa-comment"></i> Observaciones</h6>
                    <p class="mb-3">${observacionesTexto}</p>
                ` : ''}
            </div>
        </div>
    `;
    
    mostrarPaso(4);
}

// ===============================================
// FUNCIONES ESPECÍFICAS PARA MÓDULO DE CABAÑAS
// ===============================================

/**
 * Inicializar funcionalidades específicas del módulo de cabañas
 */
function initCabaniasFunctions() {
    // Solo ejecutar en páginas de cabañas
    if (!window.location.pathname.includes('/cabanias')) {
        return;
    }

    // Inicializar formulario de cabañas si existe
    const formCabania = document.getElementById('formCabania');
    if (formCabania) {
        initCabaniaForm(formCabania);
    }

    // Inicializar filtros de búsqueda si existen
    const filtrosCabania = document.querySelector('.form-filtros');
    if (filtrosCabania) {
        initCabaniaFilters(filtrosCabania);
    }

    // Inicializar vista previa de imágenes
    initImagePreview();

    // Inicializar validaciones personalizadas
    initCabaniaValidations();
}

/**
 * Inicializar formulario de cabañas con validaciones
 */
function initCabaniaForm(form) {
    // Event listeners para validación en tiempo real
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateCabaniaField(this);
        });
        
        input.addEventListener('input', function() {
            clearFieldError(this);
        });
    });

    // Contador de caracteres para descripción
    const descripcionField = form.querySelector('#cabania_descripcion');
    if (descripcionField) {
        initDescriptionCounter(descripcionField);
    }

    // Validación de código único
    const codigoField = form.querySelector('#cabania_codigo');
    if (codigoField) {
        initCodigoValidation(codigoField);
    }

    // Formateo de precio
    const precioField = form.querySelector('#cabania_precio');
    if (precioField) {
        initPriceFormatting(precioField);
    }

    // Validación al enviar
    form.addEventListener('submit', function(e) {
        if (!validateCabaniaForm(this)) {
            e.preventDefault();
            showValidationSummary();
        }
    });
}

/**
 * Validar campo individual de cabaña
 */
function validateCabaniaField(field) {
    const fieldName = field.name;
    const value = field.value.trim();
    let isValid = true;
    let message = '';

    // Limpiar errores previos
    clearFieldError(field);

    switch (fieldName) {
        case 'cabania_codigo':
            if (!value) {
                message = 'El código es obligatorio';
                isValid = false;
            } else if (!/^[A-Z0-9-]+$/.test(value)) {
                message = 'Use solo letras mayúsculas, números y guiones';
                isValid = false;
            } else if (value.length > 20) {
                message = 'El código no puede exceder 20 caracteres';
                isValid = false;
            }
            break;

        case 'cabania_nombre':
            if (!value) {
                message = 'El nombre es obligatorio';
                isValid = false;
            } else if (value.length < 3) {
                message = 'El nombre debe tener al menos 3 caracteres';
                isValid = false;
            } else if (value.length > 100) {
                message = 'El nombre no puede exceder 100 caracteres';
                isValid = false;
            }
            break;

        case 'cabania_descripcion':
            if (!value) {
                message = 'La descripción es obligatoria';
                isValid = false;
            } else if (value.length < 20) {
                message = 'La descripción debe ser más detallada (mínimo 20 caracteres)';
                isValid = false;
            } else if (value.length > 500) {
                message = 'La descripción no puede exceder 500 caracteres';
                isValid = false;
            }
            break;

        case 'cabania_capacidad':
            const capacidad = parseInt(value);
            if (!value || capacidad < 1) {
                message = 'La capacidad debe ser mínimo 1 persona';
                isValid = false;
            } else if (capacidad > 20) {
                message = 'La capacidad no puede exceder 20 personas';
                isValid = false;
            }
            break;

        case 'cabania_precio':
            const precio = parseFloat(value);
            if (!value || precio <= 0) {
                message = 'El precio debe ser mayor a 0';
                isValid = false;
            } else if (precio > 999999.99) {
                message = 'El precio es demasiado alto';
                isValid = false;
            }
            break;

        case 'cabania_ubicacion':
            if (!value) {
                message = 'La ubicación es obligatoria';
                isValid = false;
            } else if (value.length > 200) {
                message = 'La ubicación no puede exceder 200 caracteres';
                isValid = false;
            }
            break;

        case 'cabania_cantidadbanios':
        case 'cabania_cantidadhabitaciones':
            const cantidad = parseInt(value);
            if (!value || cantidad < 1) {
                message = 'Debe ser mínimo 1';
                isValid = false;
            } else if (cantidad > 10) {
                message = 'No puede exceder 10';
                isValid = false;
            }
            break;
    }

    if (!isValid) {
        showFieldError(field, message);
    }

    return isValid;
}

/**
 * Mostrar error en campo específico
 */
function showFieldError(field, message) {
    field.classList.add('is-invalid');
    
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.textContent = message;
        feedback.style.display = 'block';
    }
}

/**
 * Limpiar error de campo específico
 */
function clearFieldError(field) {
    field.classList.remove('is-invalid');
    
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.style.display = 'none';
    }
}

/**
 * Validar formulario completo
 */
function validateCabaniaForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!validateCabaniaField(field)) {
            isValid = false;
        }
    });

    return isValid;
}

/**
 * Mostrar resumen de errores de validación
 */
function showValidationSummary() {
    const errors = document.querySelectorAll('.is-invalid');
    if (errors.length > 0) {
        const firstError = errors[0];
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstError.focus();
        
        showToast('Por favor corrija los errores marcados en rojo', 'error');
    }
}

/**
 * Inicializar contador de caracteres para descripción
 */
function initDescriptionCounter(textarea) {
    const contador = document.getElementById('contadorDescripcion');
    if (!contador) return;

    function updateCounter() {
        const length = textarea.value.length;
        const maxLength = 500;
        contador.textContent = length;
        
        if (length > maxLength * 0.9) {
            contador.classList.add('text-warning');
        } else {
            contador.classList.remove('text-warning');
        }
        
        if (length > maxLength) {
            contador.classList.add('text-danger');
            contador.classList.remove('text-warning');
        } else {
            contador.classList.remove('text-danger');
        }
    }

    textarea.addEventListener('input', updateCounter);
    updateCounter(); // Inicializar contador
}

/**
 * Validación de código único (AJAX)
 */
function initCodigoValidation(codigoField) {
    let timeout;
    
    codigoField.addEventListener('input', function() {
        const value = this.value.trim().toUpperCase();
        this.value = value; // Convertir automáticamente a mayúsculas
        
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            if (value.length >= 3) {
                checkCodigoUnique(value, this);
            }
        }, 500);
    });
}

/**
 * Verificar si el código de cabaña es único
 */
async function checkCodigoUnique(codigo, field) {
    try {
        // Agregar indicador de carga
        const spinner = document.createElement('div');
        spinner.className = 'spinner-border spinner-border-sm position-absolute';
        spinner.style.right = '10px';
        spinner.style.top = '50%';
        spinner.style.transform = 'translateY(-50%)';
        field.parentNode.style.position = 'relative';
        field.parentNode.appendChild(spinner);
        
        const response = await fetch('/admin/cabanias/check-codigo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ codigo: codigo })
        });
        
        const data = await response.json();
        
        // Remover indicador de carga
        spinner.remove();
        
        if (!data.available) {
            showFieldError(field, 'Este código ya está en uso');
            return false;
        } else {
            clearFieldError(field);
            return true;
        }
    } catch (error) {
        console.error('Error verificando código:', error);
        return true; // Permitir continuar si hay error de conexión
    }
}

/**
 * Formateo automático del precio
 */
function initPriceFormatting(precioField) {
    precioField.addEventListener('input', function() {
        let value = this.value.replace(/[^\d.]/g, ''); // Solo números y punto decimal
        
        // Validar formato de decimal
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts[1];
        }
        
        // Limitar decimales a 2 dígitos
        if (parts[1] && parts[1].length > 2) {
            value = parts[0] + '.' + parts[1].substring(0, 2);
        }
        
        this.value = value;
    });
}

/**
 * Inicializar vista previa de imágenes
 */
function initImagePreview() {
    const fileInput = document.getElementById('cabania_foto');
    if (!fileInput) return;

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('previewImagen');
        const img = document.getElementById('imgPreview');
        const label = fileInput.nextElementSibling;
        
        if (file) {
            // Validar tipo de archivo
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                showFieldError(fileInput, 'Solo se permiten archivos JPG, PNG o GIF');
                fileInput.value = '';
                return;
            }
            
            // Validar tamaño (5MB máximo)
            const maxSize = 5 * 1024 * 1024; // 5MB en bytes
            if (file.size > maxSize) {
                showFieldError(fileInput, 'El archivo no puede exceder 5MB');
                fileInput.value = '';
                return;
            }
            
            // Mostrar vista previa
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                preview.style.display = 'block';
                label.textContent = file.name;
                clearFieldError(fileInput);
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
            label.textContent = 'Seleccionar foto';
        }
    });
}

/**
 * Inicializar filtros de búsqueda para cabañas
 */
function initCabaniaFilters(form) {
    // Auto-submit al cambiar filtros (con debounce)
    let timeout;
    const inputs = form.querySelectorAll('input, select');
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                if (input.type === 'text' && input.value.length > 0 && input.value.length < 2) {
                    return; // No buscar con menos de 2 caracteres
                }
                form.submit();
            }, 500);
        });
    });
}

/**
 * Limpiar formulario de cabaña
 */
function limpiarFormulario() {
    const form = document.getElementById('formCabania');
    if (!form) return;
    
    Swal.fire({
        title: '¿Limpiar formulario?',
        text: 'Se perderán todos los datos ingresados',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            form.reset();
            
            // Limpiar errores de validación
            const invalidFields = form.querySelectorAll('.is-invalid');
            invalidFields.forEach(field => clearFieldError(field));
            
            // Ocultar vista previa de imagen
            const preview = document.getElementById('previewImagen');
            if (preview) {
                preview.style.display = 'none';
            }
            
            // Resetear contador de caracteres
            const contador = document.getElementById('contadorDescripcion');
            if (contador) {
                contador.textContent = '0';
                contador.classList.remove('text-warning', 'text-danger');
            }
            
            // Enfocar primer campo
            const firstField = form.querySelector('input, textarea, select');
            if (firstField) {
                firstField.focus();
            }
            
            showToast('Formulario limpiado', 'info');
        }
    });
}

/**
 * Inicializar validaciones personalizadas adicionales
 */
function initCabaniaValidations() {
    // Validación de Bootstrap personalizada
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('.needs-validation');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    });
}

/**
 * Función para mostrar toast notifications
 */
function showToast(message, type = 'info') {
    // Implementar usando SweetAlert2 o crear sistema de toast personalizado
    const iconMap = {
        success: 'success',
        error: 'error',
        warning: 'warning',
        info: 'info'
    };
    
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: iconMap[type] || 'info',
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}
