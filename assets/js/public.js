/**
 * Public JavaScript - Casa de Palos Cabañas
 * JavaScript específico para las páginas públicas
 */

// ===========================================
// NAVEGACIÓN PÚBLICA
// ===========================================

function initPublicNavigation() {
    const navbar = document.querySelector('.public-navbar');
    if (!navbar) return;

    const toggler = navbar.querySelector('.navbar-toggler');
    const collapse = navbar.querySelector('.navbar-collapse');
    
    if (toggler && collapse) {
        toggler.addEventListener('click', function() {
            collapse.classList.toggle('show');
            
            // Animar hamburger
            const spans = toggler.querySelectorAll('span');
            spans.forEach((span, index) => {
                if (collapse.classList.contains('show')) {
                    if (index === 0) span.style.transform = 'rotate(45deg) translate(5px, 5px)';
                    if (index === 1) span.style.opacity = '0';
                    if (index === 2) span.style.transform = 'rotate(-45deg) translate(7px, -6px)';
                } else {
                    span.style.transform = '';
                    span.style.opacity = '';
                }
            });
        });
    }

    // Cerrar menú al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!navbar.contains(e.target) && collapse && collapse.classList.contains('show')) {
            collapse.classList.remove('show');
            const spans = toggler.querySelectorAll('span');
            spans.forEach(span => {
                span.style.transform = '';
                span.style.opacity = '';
            });
        }
    });

    // Scroll effect
    let lastScrollTop = 0;
    window.addEventListener('scroll', function() {
        const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (currentScrollTop > lastScrollTop && currentScrollTop > 100) {
            // Scrolling down
            navbar.style.transform = 'translateY(-100%)';
        } else {
            // Scrolling up
            navbar.style.transform = 'translateY(0)';
        }
        lastScrollTop = currentScrollTop;
    });
}

// ===========================================
// HOMEPAGE Y HERO SECTION
// ===========================================

class PublicHomepage {
    constructor() {
        this.initializeHero();
        this.initializeAnimations();
        this.bindEvents();
    }

    initializeHero() {
        const hero = document.querySelector('.hero-section');
        if (!hero) return;

        // Parallax effect
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            hero.style.transform = `translateY(${rate}px)`;
        });

        // Hero content animation
        const heroContent = hero.querySelector('.hero-content');
        if (heroContent) {
            this.animateHeroContent(heroContent);
        }
    }

    animateHeroContent(content) {
        const elements = content.children;
        
        Array.from(elements).forEach((element, index) => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                element.style.transition = 'all 0.8s ease';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 200 + 500);
        });
    }

    initializeAnimations() {
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        // Observe elements
        const animateElements = document.querySelectorAll('.feature-card, .cabana-card, .stat-card');
        animateElements.forEach(el => {
            el.classList.add('animate-on-scroll');
            observer.observe(el);
        });
    }

    bindEvents() {
        // Smooth scroll for anchor links
        const anchorLinks = document.querySelectorAll('a[href^="#"]');
        anchorLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Hero action buttons
        const heroButtons = document.querySelectorAll('.btn-hero');
        heroButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                // Add click effect
                button.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    button.style.transform = '';
                }, 150);
            });
        });
    }
}

// ===========================================
// GALERÍA DE CABAÑAS
// ===========================================

class CabanaGallery {
    constructor() {
        this.currentPage = 1;
        this.totalPages = 1;
        this.filters = {};
        this.initializeGallery();
    }

    initializeGallery() {
        this.bindFilterEvents();
        this.bindCardEvents();
        this.initializeLazyLoading();
    }

    bindFilterEvents() {
        // Filtros de precio
        const priceFilters = document.querySelectorAll('.price-filter');
        priceFilters.forEach(filter => {
            filter.addEventListener('change', () => {
                this.updateFilters();
                this.loadCabanas();
            });
        });

        // Filtros de capacidad
        const capacityFilters = document.querySelectorAll('.capacity-filter');
        capacityFilters.forEach(filter => {
            filter.addEventListener('change', () => {
                this.updateFilters();
                this.loadCabanas();
            });
        });

        // Ordenamiento
        const sortSelect = document.querySelector('.sort-select');
        if (sortSelect) {
            sortSelect.addEventListener('change', () => {
                this.updateFilters();
                this.loadCabanas();
            });
        }
    }

    bindCardEvents() {
        document.addEventListener('click', (e) => {
            // Ver detalles de cabaña
            if (e.target.classList.contains('btn-ver-detalles')) {
                e.preventDefault();
                const cabanaId = e.target.getAttribute('data-cabana-id');
                this.showCabanaDetails(cabanaId);
            }

            // Reservar cabaña
            if (e.target.classList.contains('btn-reservar')) {
                e.preventDefault();
                const cabanaId = e.target.getAttribute('data-cabana-id');
                this.showReservationForm(cabanaId);
            }
        });

        // Hover effects
        const cabanaCards = document.querySelectorAll('.cabana-card');
        cabanaCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                this.animateCardHover(card, true);
            });

            card.addEventListener('mouseleave', () => {
                this.animateCardHover(card, false);
            });
        });
    }

    animateCardHover(card, isHovering) {
        const image = card.querySelector('.image-container img');
        if (image) {
            image.style.transform = isHovering ? 'scale(1.05)' : 'scale(1)';
        }
    }

    initializeLazyLoading() {
        const images = document.querySelectorAll('img[data-src]');
        
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.getAttribute('data-src');
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }

    updateFilters() {
        this.filters = {
            minPrice: document.querySelector('#min-price')?.value || '',
            maxPrice: document.querySelector('#max-price')?.value || '',
            capacity: document.querySelector('#capacity')?.value || '',
            sort: document.querySelector('#sort')?.value || 'name'
        };
    }

    async loadCabanas() {
        try {
            window.showLoading();
            
            const params = new URLSearchParams({
                page: this.currentPage,
                ...this.filters
            });

            const response = await fetch(`/api/cabanas?${params}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderCabanas(data.cabanas);
                this.updatePagination(data.pagination);
            }
            
        } catch (error) {
            console.error('Error loading cabañas:', error);
            window.showMessage('error', 'Error', 'No se pudieron cargar las cabañas');
        } finally {
            window.hideLoading();
        }
    }

    renderCabanas(cabanas) {
        const container = document.querySelector('.cabanas-grid');
        if (!container) return;

        container.innerHTML = '';
        
        cabanas.forEach(cabana => {
            const cardHTML = this.createCabanaCard(cabana);
            container.innerHTML += cardHTML;
        });

        // Re-initialize events for new cards
        this.bindCardEvents();
    }

    createCabanaCard(cabana) {
        return `
            <div class="cabana-card animate-on-scroll">
                <div class="image-container">
                    <img data-src="${cabana.imagen}" alt="${cabana.nombre}" class="lazy-load">
                    <div class="price-tag">$${cabana.precio}/noche</div>
                </div>
                <div class="content">
                    <h3 class="title">${cabana.nombre}</h3>
                    <p class="description">${cabana.descripcion}</p>
                    <div class="features">
                        <span class="feature">
                            <i class="fas fa-users"></i>
                            ${cabana.capacidad} personas
                        </span>
                        <span class="feature">
                            <i class="fas fa-bed"></i>
                            ${cabana.habitaciones} habitaciones
                        </span>
                    </div>
                    <div class="actions">
                        <button class="btn-cabana primary btn-reservar" data-cabana-id="${cabana.id}">
                            Reservar
                        </button>
                        <button class="btn-cabana secondary btn-ver-detalles" data-cabana-id="${cabana.id}">
                            Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    async showCabanaDetails(cabanaId) {
        try {
            const response = await fetch(`/api/cabanas/${cabanaId}`);
            const data = await response.json();
            
            if (data.success) {
                this.createCabanaModal(data.cabana);
            }
            
        } catch (error) {
            console.error('Error loading cabana details:', error);
            window.showMessage('error', 'Error', 'No se pudieron cargar los detalles');
        }
    }

    createCabanaModal(cabana) {
        const modalHTML = `
            <div class="cabana-details">
                <div class="cabana-gallery">
                    ${cabana.imagenes.map(img => `<img src="${img}" alt="${cabana.nombre}">`).join('')}
                </div>
                <div class="cabana-info">
                    <h2>${cabana.nombre}</h2>
                    <p class="price">$${cabana.precio}/noche</p>
                    <p>${cabana.descripcion_completa}</p>
                    <div class="amenities">
                        <h4>Servicios incluidos:</h4>
                        <ul>
                            ${cabana.servicios.map(servicio => `<li>${servicio}</li>`).join('')}
                        </ul>
                    </div>
                </div>
            </div>
        `;

        window.modalManager.createModal(
            'cabana-details-modal',
            cabana.nombre,
            modalHTML,
            [{
                text: 'Reservar ahora',
                class: 'button-primary',
                onclick: `window.cabanaGallery.showReservationForm(${cabana.id})`
            }]
        );

        window.modalManager.showModal('cabana-details-modal');
    }

    showReservationForm(cabanaId) {
        window.modalManager.hideModal();
        
        // Scroll to reservation form
        const reservationForm = document.querySelector('.reserva-section');
        if (reservationForm) {
            reservationForm.scrollIntoView({ behavior: 'smooth' });
            
            // Pre-fill cabaña selection
            const cabanaSelect = document.querySelector('#cabana_id');
            if (cabanaSelect) {
                cabanaSelect.value = cabanaId;
            }
        }
    }

    updatePagination(pagination) {
        this.currentPage = pagination.current_page;
        this.totalPages = pagination.total_pages;
        
        // Update pagination controls if they exist
        const paginationContainer = document.querySelector('.pagination');
        if (paginationContainer && this.totalPages > 1) {
            this.renderPagination(paginationContainer);
        }
    }

    renderPagination(container) {
        let paginationHTML = '';
        
        // Previous button
        if (this.currentPage > 1) {
            paginationHTML += `<button class="page-btn" data-page="${this.currentPage - 1}">Anterior</button>`;
        }
        
        // Page numbers
        for (let i = 1; i <= this.totalPages; i++) {
            const isActive = i === this.currentPage ? 'active' : '';
            paginationHTML += `<button class="page-btn ${isActive}" data-page="${i}">${i}</button>`;
        }
        
        // Next button
        if (this.currentPage < this.totalPages) {
            paginationHTML += `<button class="page-btn" data-page="${this.currentPage + 1}">Siguiente</button>`;
        }
        
        container.innerHTML = paginationHTML;
        
        // Bind pagination events
        container.querySelectorAll('.page-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                this.currentPage = parseInt(btn.getAttribute('data-page'));
                this.loadCabanas();
            });
        });
    }
}

// ===========================================
// FORMULARIO DE RESERVA
// ===========================================

class ReservationForm {
    constructor() {
        this.form = document.querySelector('.reserva-form');
        if (!this.form) return;
        
        this.initializeForm();
    }

    initializeForm() {
        this.bindEvents();
        this.initializeDatePickers();
        this.calculatePrices();
    }

    bindEvents() {
        // Cambios en fechas
        const fechaEntrada = this.form.querySelector('#fecha_entrada');
        const fechaSalida = this.form.querySelector('#fecha_salida');
        
        if (fechaEntrada && fechaSalida) {
            fechaEntrada.addEventListener('change', () => {
                this.validateDates();
                this.checkAvailability();
                this.calculatePrices();
            });

            fechaSalida.addEventListener('change', () => {
                this.validateDates();
                this.checkAvailability();
                this.calculatePrices();
            });
        }

        // Cambio en cabaña
        const cabanaSelect = this.form.querySelector('#cabana_id');
        if (cabanaSelect) {
            cabanaSelect.addEventListener('change', () => {
                this.calculatePrices();
                this.checkAvailability();
            });
        }

        // Cambio en número de huéspedes
        const huespedes = this.form.querySelector('#numero_huespedes');
        if (huespedes) {
            huespedes.addEventListener('change', () => {
                this.calculatePrices();
            });
        }

        // Submit form
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitReservation();
        });
    }

    initializeDatePickers() {
        const dateInputs = this.form.querySelectorAll('input[type="date"]');
        const today = new Date().toISOString().split('T')[0];
        
        dateInputs.forEach(input => {
            input.min = today;
        });
    }

    validateDates() {
        const fechaEntrada = this.form.querySelector('#fecha_entrada');
        const fechaSalida = this.form.querySelector('#fecha_salida');
        
        if (!fechaEntrada?.value || !fechaSalida?.value) return;

        const entrada = new Date(fechaEntrada.value);
        const salida = new Date(fechaSalida.value);
        
        if (salida <= entrada) {
            fechaSalida.setCustomValidity('La fecha de salida debe ser posterior a la de entrada');
            window.showFieldError(fechaSalida, 'La fecha de salida debe ser posterior a la de entrada');
            return false;
        } else {
            fechaSalida.setCustomValidity('');
            window.clearFieldError(fechaSalida);
            return true;
        }
    }

    async checkAvailability() {
        const cabanaId = this.form.querySelector('#cabana_id')?.value;
        const fechaEntrada = this.form.querySelector('#fecha_entrada')?.value;
        const fechaSalida = this.form.querySelector('#fecha_salida')?.value;
        
        if (!cabanaId || !fechaEntrada || !fechaSalida) return;

        try {
            const response = await fetch('/api/disponibilidad', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cabana_id: cabanaId,
                    fecha_entrada: fechaEntrada,
                    fecha_salida: fechaSalida
                })
            });

            const data = await response.json();
            
            const availabilityMessage = this.form.querySelector('.availability-message');
            if (availabilityMessage) {
                if (data.disponible) {
                    availabilityMessage.innerHTML = '<span class="text-success">✓ Fechas disponibles</span>';
                } else {
                    availabilityMessage.innerHTML = '<span class="text-danger">✗ Fechas no disponibles</span>';
                }
            }
            
        } catch (error) {
            console.error('Error checking availability:', error);
        }
    }

    async calculatePrices() {
        const cabanaId = this.form.querySelector('#cabana_id')?.value;
        const fechaEntrada = this.form.querySelector('#fecha_entrada')?.value;
        const fechaSalida = this.form.querySelector('#fecha_salida')?.value;
        const huespedes = this.form.querySelector('#numero_huespedes')?.value || 1;
        
        if (!cabanaId || !fechaEntrada || !fechaSalida) return;

        try {
            const response = await fetch('/api/calcular-precio', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cabana_id: cabanaId,
                    fecha_entrada: fechaEntrada,
                    fecha_salida: fechaSalida,
                    numero_huespedes: huespedes
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.updatePriceSummary(data.precios);
            }
            
        } catch (error) {
            console.error('Error calculating prices:', error);
        }
    }

    updatePriceSummary(precios) {
        const summary = this.form.querySelector('.price-summary');
        if (!summary) return;

        summary.innerHTML = `
            <div class="price-breakdown">
                <div class="price-line">
                    <span>Subtotal (${precios.noches} noches)</span>
                    <span>$${precios.subtotal}</span>
                </div>
                <div class="price-line">
                    <span>Impuestos</span>
                    <span>$${precios.impuestos}</span>
                </div>
                <div class="price-line total">
                    <span><strong>Total</strong></span>
                    <span><strong>$${precios.total}</strong></span>
                </div>
            </div>
        `;
    }

    async submitReservation() {
        if (!this.validateDates()) return;

        try {
            window.showButtonLoading(this.form.querySelector('button[type="submit"]'));
            
            const formData = new FormData(this.form);
            const response = await fetch(this.form.action || '/reservas/crear', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                window.showMessage('success', 'Reserva creada', 'Tu reserva ha sido creada exitosamente');
                this.form.reset();
                
                // Redirect to confirmation page
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                }
            } else {
                window.showMessage('error', 'Error en la reserva', data.message || 'No se pudo crear la reserva');
            }
            
        } catch (error) {
            console.error('Error submitting reservation:', error);
            window.showMessage('error', 'Error', 'Ocurrió un error al procesar la reserva');
        } finally {
            window.hideButtonLoading(this.form.querySelector('button[type="submit"]'));
        }
    }
}

// ===========================================
// FUNCIONES LEGACY Y COMPATIBILIDAD
// ===========================================

// Función para inicializar las salidas (compatibility con main.js)
function initSalidas() {
    console.log('Initializing Salidas functionality');
    
    // Inicializar barras de progreso con animación
    const progressBars = document.querySelectorAll('.progress-bar[data-width]');
    progressBars.forEach(bar => {
        const width = bar.getAttribute('data-width');
        bar.style.setProperty('--progress-width', width);
        
        // Animar después de un pequeño delay
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    });
    
    // Auto-refresh si está definido
    if (typeof initAutoRefresh === 'function') {
        initAutoRefresh();
    }
}

// Auto-refresh para estadísticas
function initAutoRefresh() {
    // Refresh cada 30 segundos si la página está visible
    let refreshInterval;
    
    function startRefresh() {
        refreshInterval = setInterval(() => {
            if (!document.hidden) {
                location.reload();
            }
        }, 30000);
    }
    
    function stopRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    }
    
    // Iniciar/detener refresh basado en visibilidad
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopRefresh();
        } else {
            startRefresh();
        }
    });
    
    startRefresh();
}

// Función para inicializar estadísticas con Chart.js
function initSalidasStats(chartData) {
    // Inicializar funciones básicas primero
    initSalidas();
    initAutoRefresh();
    
    // Crear gráfico de distribución por estados
    if (chartData.distribucionEstados) {
        const ctx = document.getElementById('pieChart');
        if (ctx && window.Chart) {
            const colors = chartData.distribucionEstados.labels.map(label => 
                label.toLowerCase().includes('finalizada') ? '#1cc88a' : '#f6c23e'
            );
            const hoverColors = chartData.distribucionEstados.labels.map(label => 
                label.toLowerCase().includes('finalizada') ? '#17a673' : '#dda20a'
            );

            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: chartData.distribucionEstados.labels,
                    datasets: [{
                        data: chartData.distribucionEstados.data,
                        backgroundColor: colors,
                        hoverBackgroundColor: hoverColors,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    }
}

// ===========================================
// INICIALIZACIÓN
// ===========================================

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar navegación pública
    initPublicNavigation();
    
    // Inicializar sistemas públicos
    window.publicHomepage = new PublicHomepage();
    window.cabanaGallery = new CabanaGallery();
    window.reservationForm = new ReservationForm();

    // Inicializar funciones legacy para compatibility
    if (typeof initSalidas === 'function') {
        initSalidas();
    }

    // Inicializar catálogo de cabañas si existe
    if (typeof initCatalog === 'function') {
        initCatalog();
    }

    // CSS animations
    const style = document.createElement('style');
    style.textContent = `
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }
        
        .animate-on-scroll.animate-in {
            opacity: 1;
            transform: translateY(0);
        }
        
        .lazy-load {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .lazy-load.loaded {
            opacity: 1;
        }
    `;
    document.head.appendChild(style);

    console.log('Casa de Palos - Public System loaded successfully');
});

// ===========================================
// CATÁLOGO DE CABAÑAS
// ===========================================

/**
 * Clase para manejar el catálogo público de cabañas
 */
class VisualCalendar {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        this.currentDate = new Date();
        this.selectedDates = { start: null, end: null };
        this.occupiedDates = [];
        this.hoveredDate = null;
        
        this.options = {
            minDate: new Date(),
            maxDate: new Date(new Date().setFullYear(new Date().getFullYear() + 1)),
            onDateSelect: null,
            ...options
        };
        
        this.monthNames = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
        
        this.dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        
        this.init();
    }
    
    init() {
        if (!this.container) return;
        
        this.render();
        this.bindEvents();
    }
    
    render() {
        const calendarHeader = this.container.querySelector('.calendar-header');
        const calendarDays = this.container.querySelector('.calendar-days');
        
        if (calendarHeader) {
            calendarHeader.innerHTML = `
                <button type="button" class="calendar-nav prev" data-nav="prev">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="calendar-month">
                    ${this.monthNames[this.currentDate.getMonth()]} ${this.currentDate.getFullYear()}
                </div>
                <button type="button" class="calendar-nav next" data-nav="next">
                    <i class="fas fa-chevron-right"></i>
                </button>
            `;
        }
        
        if (calendarDays) {
            this.renderDays();
        }
    }
    
    renderDays() {
        const calendarDays = this.container.querySelector('.calendar-days');
        if (!calendarDays) return;
        
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        const firstDayOfMonth = new Date(year, month, 1);
        const lastDayOfMonth = new Date(year, month + 1, 0);
        const firstDayOfWeek = firstDayOfMonth.getDay();
        const daysInMonth = lastDayOfMonth.getDate();
        
        let html = '';
        
        // Días vacíos antes del primer día del mes
        for (let i = 0; i < firstDayOfWeek; i++) {
            html += '<div class="calendar-day empty"></div>';
        }
        
        // Días del mes
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const dateString = this.formatDateString(date);
            const classes = this.getDayClasses(date, dateString);
            const tooltip = this.getDayTooltip(date, dateString);
            
            html += `
                <div class="calendar-day ${classes}" data-date="${dateString}" title="${tooltip}">
                    ${day}
                    ${this.getDayLabel(dateString)}
                </div>
            `;
        }
        
        calendarDays.innerHTML = html;
    }
    
    getDayClasses(date, dateString) {
        const classes = [];
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        date.setHours(0, 0, 0, 0);
        
        // Día pasado
        if (date < this.options.minDate) {
            classes.push('disabled');
        }
        
        // Día futuro límite
        if (date > this.options.maxDate) {
            classes.push('disabled');
        }
        
        // Día ocupado - asegurar que occupiedDates sea un array
        if (Array.isArray(this.occupiedDates) && this.occupiedDates.includes(dateString)) {
            classes.push('occupied');
        }
        
        // Día disponible
        if (!classes.includes('disabled') && !classes.includes('occupied')) {
            classes.push('available');
        }
        
        // Fechas seleccionadas
        if (this.selectedDates.start && dateString === this.selectedDates.start) {
            classes.push('selected-checkin');
        }
        
        if (this.selectedDates.end && dateString === this.selectedDates.end) {
            classes.push('selected-checkout');
        }
        
        // Rango seleccionado
        if (this.selectedDates.start && this.selectedDates.end) {
            const startDate = new Date(this.selectedDates.start);
            const endDate = new Date(this.selectedDates.end);
            if (date > startDate && date < endDate) {
                classes.push('in-range');
            }
        }
        
        // Rango hover
        if (this.selectedDates.start && !this.selectedDates.end && this.hoveredDate) {
            const startDate = new Date(this.selectedDates.start);
            const hoverDate = new Date(this.hoveredDate);
            if (date > startDate && date <= hoverDate) {
                classes.push('hover-range');
            }
        }
        
        return classes.join(' ');
    }
    
    getDayTooltip(date, dateString) {
        if (this.occupiedDates.includes(dateString)) {
            return 'Fecha no disponible';
        }
        
        if (date < this.options.minDate) {
            return 'Fecha pasada';
        }
        
        if (this.selectedDates.start && dateString === this.selectedDates.start) {
            return 'Fecha de entrada seleccionada';
        }
        
        if (this.selectedDates.end && dateString === this.selectedDates.end) {
            return 'Fecha de salida seleccionada';
        }
        
        if (this.selectedDates.start && this.selectedDates.end) {
            const startDate = new Date(this.selectedDates.start);
            const endDate = new Date(this.selectedDates.end);
            if (date > startDate && date < endDate) {
                return 'Incluido en la estancia';
            }
        }
        
        if (this.selectedDates.start && !this.selectedDates.end) {
            return 'Seleccionar como fecha de salida';
        }
        
        return 'Fecha disponible - Click para seleccionar';
    }
    
    getDayLabel(dateString) {
        if (this.selectedDates.start && dateString === this.selectedDates.start) {
            return '<span class="day-label">Entrada</span>';
        }
        
        if (this.selectedDates.end && dateString === this.selectedDates.end) {
            return '<span class="day-label">Salida</span>';
        }
        
        return '';
    }
    
    bindEvents() {
        // Navegación de mes
        this.container.addEventListener('click', (e) => {
            if (e.target.closest('.calendar-nav')) {
                const nav = e.target.closest('.calendar-nav');
                if (nav.dataset.nav === 'prev') {
                    this.previousMonth();
                } else if (nav.dataset.nav === 'next') {
                    this.nextMonth();
                }
            }
            
            // Selección de día
            if (e.target.classList.contains('calendar-day') && 
                !e.target.classList.contains('empty') && 
                !e.target.classList.contains('disabled') && 
                !e.target.classList.contains('occupied')) {
                
                this.selectDate(e.target.dataset.date);
            }
        });
        
        // Hover para mostrar rango
        this.container.addEventListener('mouseover', (e) => {
            if (e.target.classList.contains('calendar-day') && 
                !e.target.classList.contains('empty') && 
                !e.target.classList.contains('disabled') && 
                !e.target.classList.contains('occupied')) {
                
                this.hoveredDate = e.target.dataset.date;
                this.renderDays();
            }
        });
        
        this.container.addEventListener('mouseleave', () => {
            this.hoveredDate = null;
            this.renderDays();
        });
    }
    
    previousMonth() {
        this.currentDate.setMonth(this.currentDate.getMonth() - 1);
        this.render();
    }
    
    nextMonth() {
        this.currentDate.setMonth(this.currentDate.getMonth() + 1);
        this.render();
    }
    
    selectDate(dateString) {
        const date = new Date(dateString);
        
        // Si no hay fecha de inicio o si la fecha seleccionada es anterior a la de inicio,
        // establecer como fecha de inicio
        if (!this.selectedDates.start || date < new Date(this.selectedDates.start)) {
            this.selectedDates.start = dateString;
            this.selectedDates.end = null;
            this.showDateSelectionInfo('Fecha de entrada seleccionada. Ahora selecciona la fecha de salida.');
        } else if (!this.selectedDates.end) {
            // Validar que la fecha de fin sea al menos un día después de la fecha de inicio
            const startDate = new Date(this.selectedDates.start);
            const minEndDate = new Date(startDate);
            minEndDate.setDate(startDate.getDate() + 1);
            
            if (date >= minEndDate) {
                // Si ya hay fecha de inicio pero no de fin, establecer como fecha de fin
                this.selectedDates.end = dateString;
                const nights = this.calculateNights(this.selectedDates.start, this.selectedDates.end);
                this.showDateSelectionInfo(`Estancia completa seleccionada: ${nights} ${nights === 1 ? 'noche' : 'noches'}.`);
            } else {
                // La fecha seleccionada es el mismo día o anterior, mostrar mensaje de error
                this.showDateSelectionInfo('La fecha de salida debe ser al menos un día después de la fecha de entrada.', 'warning');
                return;
            }
        } else {
            // Si ya hay ambas fechas, reiniciar con la nueva fecha como inicio
            this.selectedDates.start = dateString;
            this.selectedDates.end = null;
            this.showDateSelectionInfo('Nueva fecha de entrada seleccionada. Ahora selecciona la fecha de salida.');
        }
        
        this.renderDays();
        
        // Callback personalizado
        if (this.options.onDateSelect) {
            this.options.onDateSelect(this.selectedDates);
        }
        
        // Actualizar inputs de fecha ocultos si existen
        this.updateDateInputs();
    }
    
    calculateNights(startDate, endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays;
    }
    
    showDateSelectionInfo(message, type = 'info') {
        // Buscar o crear contenedor de información
        let infoContainer = this.container.querySelector('.calendar-info');
        if (!infoContainer) {
            infoContainer = document.createElement('div');
            infoContainer.className = 'calendar-info';
            this.container.appendChild(infoContainer);
        }
        
        infoContainer.className = `calendar-info ${type}`;
        infoContainer.textContent = message;
        
        // Auto-ocultar después de 3 segundos si es un mensaje de advertencia
        if (type === 'warning') {
            setTimeout(() => {
                if (infoContainer) {
                    infoContainer.textContent = '';
                    infoContainer.className = 'calendar-info';
                }
            }, 3000);
        }
    }
    
    updateDateInputs() {
        const checkinInput = document.getElementById('checkinDate');
        const checkoutInput = document.getElementById('checkoutDate');
        
        if (checkinInput && this.selectedDates.start) {
            checkinInput.value = this.selectedDates.start;
            checkinInput.dispatchEvent(new Event('change'));
        }
        
        if (checkoutInput && this.selectedDates.end) {
            checkoutInput.value = this.selectedDates.end;
            checkoutInput.dispatchEvent(new Event('change'));
        }
    }
    
    setOccupiedDates(dates) {
        // Asegurar que dates es un array válido
        this.occupiedDates = Array.isArray(dates) ? dates : [];
        this.renderDays();
    }
    
    formatDateString(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    reset() {
        this.selectedDates = { start: null, end: null };
        this.hoveredDate = null;
        this.renderDays();
    }
    
    getSelectedDates() {
        return { ...this.selectedDates };
    }
}

class CatalogSystem {
    constructor() {
        this.currentCabin = null;
        this.selectedDates = {
            start: null,
            end: null
        };
        this.occupiedDates = [];
        this.calendar = null;
        
        this.initializeComponents();
        this.bindEvents();
    }

    initializeComponents() {
        // Inicializar tooltips para las tarjetas de cabañas
        this.initializeCabinCards();
        
        // Inicializar modal de disponibilidad
        this.initializeAvailabilityModal();
        
        // Inicializar filtros
        this.initializeFilters();
    }

    initializeCabinCards() {
        const cabinCards = document.querySelectorAll('.cabin-card');
        
        cabinCards.forEach(card => {
            // Efecto hover para mostrar información adicional
            card.addEventListener('mouseenter', (e) => {
                this.showCabinPreview(card);
            });
            
            card.addEventListener('mouseleave', (e) => {
                this.hideCabinPreview(card);
            });
        });
    }

    initializeAvailabilityModal() {
        const modal = document.getElementById('availabilityModal');
        if (!modal) return;

        // Configurar fechas mínimas para inputs ocultos
        const checkinInput = document.getElementById('checkinDate');
        const checkoutInput = document.getElementById('checkoutDate');
        
        if (checkinInput) {
            checkinInput.min = new Date().toISOString().split('T')[0];
            checkinInput.addEventListener('change', (e) => {
                this.selectedDates.start = e.target.value;
                this.validateDateSelection();
            });
        }
        
        if (checkoutInput) {
            checkoutInput.addEventListener('change', (e) => {
                this.selectedDates.end = e.target.value;
                this.validateDateSelection();
            });
        }

        // Inicializar calendario visual
        this.initializeCalendar();
    }

    initializeCalendar() {
        this.calendar = new VisualCalendar('visualCalendar', {
            onDateSelect: (dates) => {
                this.selectedDates = dates;
                this.validateDateSelection();
            }
        });
    }

    initializeFilters() {
        const filterForm = document.querySelector('.minimal-form');
        if (!filterForm) return;

        // Auto-submit en cambios de filtros
        const selectFilters = filterForm.querySelectorAll('select');
        selectFilters.forEach(select => {
            select.addEventListener('change', () => {
                filterForm.submit();
            });
        });

        // Submit en Enter para campos de texto y número
        const textInputs = filterForm.querySelectorAll('input[type="text"], input[type="number"]');
        textInputs.forEach(input => {
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    filterForm.submit();
                }
            });
        });

        // Botón de búsqueda
        const searchBtn = filterForm.querySelector('.btn-search');
        if (searchBtn) {
            searchBtn.addEventListener('click', (e) => {
                e.preventDefault();
                filterForm.submit();
            });
        }

        // Botón de limpiar filtros
        const clearBtn = document.querySelector('.btn-clear');
        if (clearBtn) {
            clearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Limpiar todos los inputs
                const inputs = filterForm.querySelectorAll('input, select');
                inputs.forEach(input => {
                    if (input.type === 'text' || input.type === 'number') {
                        input.value = '';
                    } else if (input.type === 'select-one') {
                        input.selectedIndex = 0;
                    }
                });
                
                // Obtener la URL correcta del href del botón
                const catalogUrl = clearBtn.getAttribute('href');
                window.location.href = catalogUrl;
            });
        }
    }

    bindEvents() {
        // Botones de ver disponibilidad
        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-availability')) {
                const btn = e.target.closest('.btn-availability');
                this.showAvailabilityModal(btn);
            }
            
            // Botón de consultar disponibilidad
            if (e.target.closest('.btn-check-availability')) {
                this.checkAvailability();
            }
            
            // Botón de proceder con reserva
            if (e.target.closest('.btn-proceed-reservation')) {
                this.proceedWithReservation();
            }
            
            // Cerrar modal
            if (e.target.closest('[data-dismiss="modal"]')) {
                this.closeModal();
            }
        });

        // Botones de reservar
        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-reserve')) {
                const btn = e.target.closest('.btn-reserve');
                if (!btn.disabled) {
                    this.showAvailabilityModal(btn);
                }
            }
        });
    }

    showCabinPreview(card) {
        // Efecto visual en hover (ya se maneja con CSS)
        card.classList.add('hovered');
    }

    hideCabinPreview(card) {
        card.classList.remove('hovered');
    }

    showAvailabilityModal(triggerBtn) {
        const cabinId = triggerBtn.dataset.cabinId;
        const cabinName = triggerBtn.dataset.cabinName;
        const cabinPrice = triggerBtn.dataset.cabinPrice;

        if (!cabinId || !cabinName) {
            console.error('Datos de cabaña incompletos');
            return;
        }

        this.currentCabin = {
            id: cabinId,
            name: cabinName,
            price: parseFloat(cabinPrice)
        };

        // Actualizar modal
        document.getElementById('modalCabinName').textContent = cabinName;
        
        // Limpiar formulario
        this.resetModal();
        
        // Mostrar modal
        const modal = document.getElementById('availabilityModal');
        modal.style.display = 'flex';
        document.body.classList.add('modal-open');
        
        // Reinicializar calendario después de mostrar el modal
        setTimeout(() => {
            this.initializeCalendar();
            this.loadOccupiedDates(cabinId);
        }, 100);
    }

    resetModal() {
        document.getElementById('checkinDate').value = '';
        document.getElementById('checkoutDate').value = '';
        document.getElementById('availabilityResult').innerHTML = '';
        document.querySelector('.btn-proceed-reservation').disabled = true;
        
        this.selectedDates = { start: null, end: null };
        
        // Resetear calendario visual
        if (this.calendar) {
            this.calendar.reset();
        }
    }

    loadOccupiedDates(cabinId) {
        // Usar URL relativa para evitar problemas de CORS
        const url = `/proyecto_cabania/catalogo/getOccupiedDates?cabania_id=${cabinId}`;
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.occupied_dates) {
                this.occupiedDates = data.occupied_dates;
                
                // Actualizar calendario visual con fechas ocupadas
                if (this.calendar) {
                    this.calendar.setOccupiedDates(this.occupiedDates);
                }
            }
        })
        .catch(error => {
            console.error('Error cargando fechas ocupadas:', error);
            // Continuar sin fechas ocupadas
            this.occupiedDates = [];
            if (this.calendar) {
                this.calendar.setOccupiedDates([]);
            }
        });
    }

    checkAvailability() {
        if (!this.currentCabin || !this.selectedDates.start || !this.selectedDates.end) {
            this.showAvailabilityError('Por favor selecciona las fechas de entrada y salida');
            return;
        }

        const resultContainer = document.getElementById('availabilityResult');
        resultContainer.innerHTML = '<div class="loading">Consultando disponibilidad...</div>';

        const formData = new FormData();
        formData.append('cabania_id', this.currentCabin.id);
        formData.append('fecha_inicio', this.selectedDates.start);
        formData.append('fecha_fin', this.selectedDates.end);

        // Obtener la URL base correcta
        const baseUrl = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '');
        const url = `${baseUrl}/catalogo/checkAvailability`;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                this.showAvailabilityError(data.error);
            } else {
                this.showAvailabilityResult(data);
            }
        })
        .catch(error => {
            console.error('Error consultando disponibilidad:', error);
            this.showAvailabilityError('Error de conexión. Por favor intenta nuevamente.');
        });
    }

    showAvailabilityResult(data) {
        const resultContainer = document.getElementById('availabilityResult');
        const isAvailable = data.disponible;
        
        const resultHTML = `
            <div class="availability-status ${isAvailable ? 'available' : 'unavailable'}">
                <div class="status-icon">
                    <i class="fas ${isAvailable ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                </div>
                <div class="status-info">
                    <h4>${isAvailable ? '¡Disponible!' : 'No Disponible'}</h4>
                    <p>
                        ${isAvailable ? 
                            'La cabaña está disponible para las fechas seleccionadas.' : 
                            'La cabaña no está disponible para estas fechas. Por favor selecciona otras fechas.'
                        }
                    </p>
                </div>
            </div>
            
            ${isAvailable ? `
                <div class="reservation-summary">
                    <h5>Resumen de Reserva</h5>
                    <div class="summary-details">
                        <div class="summary-row">
                            <span>Cabaña:</span>
                            <span>${data.cabania.nombre}</span>
                        </div>
                        <div class="summary-row">
                            <span>Capacidad:</span>
                            <span>Hasta ${data.cabania.capacidad} personas</span>
                        </div>
                        <div class="summary-row">
                            <span>Fecha de entrada:</span>
                            <span>${this.formatDate(data.reserva.fecha_inicio)}</span>
                        </div>
                        <div class="summary-row">
                            <span>Fecha de salida:</span>
                            <span>${this.formatDate(data.reserva.fecha_fin)}</span>
                        </div>
                        <div class="summary-row">
                            <span>Noches:</span>
                            <span>${data.reserva.dias}</span>
                        </div>
                        <div class="summary-row">
                            <span>Precio por noche:</span>
                            <span>$${data.cabania.precio_por_noche.toFixed(2)}</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span>$${data.reserva.precio_total.toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            ` : ''}
        `;
        
        resultContainer.innerHTML = resultHTML;
        
        // Habilitar botón de proceder si está disponible
        document.querySelector('.btn-proceed-reservation').disabled = !isAvailable;
    }

    showAvailabilityError(message) {
        const resultContainer = document.getElementById('availabilityResult');
        resultContainer.innerHTML = `
            <div class="availability-status error">
                <div class="status-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="status-info">
                    <h4>Error</h4>
                    <p>${message}</p>
                </div>
            </div>
        `;
    }

    validateDateSelection() {
        const btn = document.querySelector('.btn-check-availability');
        if (this.selectedDates.start && this.selectedDates.end) {
            btn.disabled = false;
        } else {
            btn.disabled = true;
        }
    }

    proceedWithReservation() {
        if (!this.currentCabin || !this.selectedDates.start || !this.selectedDates.end) {
            return;
        }

        // Llenar form oculto y enviarlo
        document.getElementById('reserveCabinId').value = this.currentCabin.id;
        document.getElementById('reserveDateStart').value = this.selectedDates.start;
        document.getElementById('reserveDateEnd').value = this.selectedDates.end;
        
        document.getElementById('reservationForm').submit();
    }

    closeModal() {
        const modal = document.getElementById('availabilityModal');
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
        
        this.resetModal();
        this.currentCabin = null;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            weekday: 'long'
        };
        return date.toLocaleDateString('es-ES', options);
    }
}

// Inicializar catálogo si estamos en la página correspondiente
function initCatalog() {
    if (document.querySelector('.catalog-results')) {
        new CatalogSystem();
        console.log('Catalog system initialized');
    }
}