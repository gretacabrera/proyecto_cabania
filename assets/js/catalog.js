/**
 * JavaScript específico para el catálogo público de cabañas
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

// Estilos específicos para el catálogo
const catalogStyles = document.createElement('style');
catalogStyles.textContent = `
    /* Estilos del modal de disponibilidad */
    .availability-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .modal-dialog {
        background: white;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .modal-header {
        padding: 20px 24px 0;
        border-bottom: none;
    }
    
    .modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--secondary-800);
    }
    
    .close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--secondary-400);
        cursor: pointer;
        padding: 4px;
    }
    
    .close:hover {
        color: var(--secondary-600);
    }
    
    .modal-body {
        padding: 20px 24px;
    }
    
    .date-selection {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 20px;
    }
    
    .date-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .date-group label {
        font-weight: 600;
        color: var(--secondary-700);
        font-size: 0.9rem;
    }
    
    .btn-check-availability {
        grid-column: 1 / -1;
        background: var(--primary-500);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .btn-check-availability:hover {
        background: var(--primary-600);
        transform: translateY(-1px);
    }
    
    .availability-loading {
        text-align: center;
        padding: 40px 20px;
        color: var(--secondary-600);
    }
    
    .reservation-summary {
        background: var(--gray-50);
        border-radius: 8px;
        padding: 16px;
        margin-top: 16px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid var(--gray-200);
    }
    
    .summary-row:last-child {
        border-bottom: none;
    }
    
    .summary-total {
        font-size: 1.1rem;
        font-weight: 700;
        border-top: 2px solid var(--primary-500);
        margin-top: 8px;
        padding-top: 12px;
    }
    
    .calendar-legend {
        display: flex;
        gap: 16px;
        justify-content: center;
        margin-top: 20px;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        color: var(--secondary-600);
    }
    
    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 4px;
    }
    
    .legend-color.available {
        background: var(--success-500);
    }
    
    .legend-color.occupied {
        background: var(--error-500);
    }
    
    .legend-color.selected {
        background: var(--primary-500);
    }
    
    .modal-footer {
        padding: 0 24px 24px;
        border-top: none;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }
    
    .btn-proceed-reservation:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    /* Responsive para modal */
    @media (max-width: 768px) {
        .modal-dialog {
            width: 95%;
            margin: 20px auto;
        }
        
        .date-selection {
            grid-template-columns: 1fr;
        }
        
        .modal-footer {
            flex-direction: column;
        }
        
        .calendar-legend {
            flex-direction: column;
            gap: 8px;
        }
    }
`;
document.head.appendChild(catalogStyles);

console.log('JavaScript del catálogo público cargado');