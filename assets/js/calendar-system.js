/**
 * NUEVO SISTEMA DE CALENDARIO REDISEÑADO
 * Sistema simplificado y funcional para selección de fechas
 */

class NewCalendarSystem {
    constructor() {
        this.currentDate = new Date();
        this.selectedStartDate = null;
        this.selectedEndDate = null;
        this.occupiedDates = [];
        this.currentCabin = null;
        
        this.monthNames = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
        
        this.init();
    }
    
    init() {
        // Verificar si estamos en la página del catálogo
        if (!document.getElementById('availabilityModal')) {
            return;
        }
        
        this.bindEvents();
        console.log('✓ Nuevo sistema de calendario inicializado correctamente');
    }
    
    bindEvents() {
        // Delegación de eventos para manejar todos los clics
        document.addEventListener('click', (e) => {
            // Ver disponibilidad
            if (e.target.closest('.btn-availability') || e.target.closest('.btn-reserve')) {
                const btn = e.target.closest('.btn-availability') || e.target.closest('.btn-reserve');
                if (!btn.disabled) {
                    this.openModal(btn);
                }
                return;
            }
            
            // Navegación del calendario
            if (e.target.closest('#prevMonthNew')) {
                this.previousMonth();
                return;
            }
            
            if (e.target.closest('#nextMonthNew')) {
                this.nextMonth();
                return;
            }
            
            // Selección de días del calendario
            if (e.target.classList.contains('calendar-day-new') && 
                !e.target.classList.contains('empty') && 
                !e.target.classList.contains('disabled') && 
                !e.target.classList.contains('occupied')) {
                
                this.selectDay(e.target);
                return;
            }
            
            // Confirmar reserva
            if (e.target.closest('#confirmReservationNew')) {
                this.confirmReservation();
                return;
            }
        });
        
        // Detectar escape para cerrar modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeModal();
            }
        });
    }
    
    openModal(triggerBtn) {
        const cabinId = triggerBtn.dataset.cabinId;
        const cabinName = triggerBtn.dataset.cabinName;
        const cabinPrice = triggerBtn.dataset.cabinPrice;

        console.log('Abriendo modal para cabaña:', { cabinId, cabinName, cabinPrice });

        if (!cabinId || !cabinName) {
            console.error('Error: Datos de cabaña incompletos', { cabinId, cabinName });
            alert('Error: No se pueden cargar los datos de la cabaña');
            return;
        }

        this.currentCabin = {
            id: cabinId,
            name: cabinName,
            price: parseFloat(cabinPrice) || 0
        };

        // Actualizar el nombre en el modal
        const modalTitle = document.getElementById('modalCabinName');
        if (modalTitle) {
            modalTitle.textContent = cabinName;
        }
        
        // Resetear estado del calendario
        this.resetCalendar();
        
        // Mostrar modal
        const modal = document.getElementById('availabilityModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            console.log('Modal mostrado, cargando fechas ocupadas...');
            
            // Cargar fechas ocupadas y renderizar calendario
            this.loadOccupiedDates(cabinId);
        } else {
            console.error('Modal no encontrado en el DOM');
        }
    }
    
    closeModal() {
        const modal = document.getElementById('availabilityModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
        this.resetCalendar();
        console.log('Modal cerrado');
    }
    
    resetCalendar() {
        this.selectedStartDate = null;
        this.selectedEndDate = null;
        this.occupiedDates = [];
        this.currentDate = new Date();
        
        // Ocultar información de fechas seleccionadas
        const datesDisplay = document.getElementById('selectedDatesDisplay');
        if (datesDisplay) {
            datesDisplay.style.display = 'none';
        }
        
        // Actualizar mensaje de instrucciones
        this.updateInstructions('Selecciona la fecha de entrada haciendo clic en un día disponible');
        
        // Deshabilitar botón de confirmación
        const confirmBtn = document.getElementById('confirmReservationNew');
        if (confirmBtn) {
            confirmBtn.disabled = true;
        }
        
        // Limpiar inputs ocultos
        this.updateHiddenInputs();
        
        console.log('Calendario reseteado');
    }
    
    loadOccupiedDates(cabinId) {
        const url = `/proyecto_cabania/catalogo/getOccupiedDates?cabania_id=${cabinId}`;
        
        console.log('Cargando fechas ocupadas desde:', url);
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('Respuesta recibida:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            
            if (data.occupied_dates && Array.isArray(data.occupied_dates)) {
                this.occupiedDates = data.occupied_dates;
                console.log('✓ Fechas ocupadas cargadas:', this.occupiedDates.length, 'fechas');
            } else {
                this.occupiedDates = [];
                console.log('⚠ No se encontraron fechas ocupadas o formato incorrecto');
            }
            
            // Renderizar calendario con las fechas cargadas
            this.renderCalendar();
        })
        .catch(error => {
            console.error('❌ Error cargando fechas ocupadas:', error);
            this.occupiedDates = [];
            // Renderizar calendario sin fechas ocupadas
            this.renderCalendar();
            
            // Mostrar error al usuario
            this.updateInstructions('Error cargando disponibilidad. Intentando nuevamente...');
            
            // Reintentar después de 2 segundos
            setTimeout(() => {
                this.loadOccupiedDates(cabinId);
            }, 2000);
        });
    }
    
    renderCalendar() {
        const monthTitle = document.getElementById('monthTitleNew');
        const calendarBody = document.getElementById('calendarBodyNew');
        
        if (!monthTitle || !calendarBody) {
            console.error('❌ Elementos del calendario no encontrados en el DOM');
            return;
        }
        
        console.log('Renderizando calendario para:', this.monthNames[this.currentDate.getMonth()], this.currentDate.getFullYear());
        
        // Actualizar título del mes
        monthTitle.textContent = `${this.monthNames[this.currentDate.getMonth()]} ${this.currentDate.getFullYear()}`;
        
        // Generar días del calendario
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const firstDayOfWeek = firstDay.getDay();
        const daysInMonth = lastDay.getDate();
        
        let html = '';
        
        // Días vacíos antes del primer día del mes
        for (let i = 0; i < firstDayOfWeek; i++) {
            html += '<div class="calendar-day-new empty"></div>';
        }
        
        // Días del mes
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const dateString = this.formatDate(date);
            const classes = this.getDayClasses(date, dateString);
            
            html += `
                <div class="calendar-day-new ${classes}" data-date="${dateString}" title="${this.getDayTooltip(date, dateString)}">
                    ${day}
                </div>
            `;
        }
        
        calendarBody.innerHTML = html;
        console.log('✓ Calendario renderizado con', daysInMonth, 'días');
    }
    
    getDayClasses(date, dateString) {
        const classes = [];
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        date.setHours(0, 0, 0, 0);
        
        // Día pasado - no seleccionable
        if (date < today) {
            classes.push('disabled');
            return classes.join(' ');
        }
        
        // Día ocupado - no seleccionable
        if (this.occupiedDates.includes(dateString)) {
            classes.push('occupied');
            return classes.join(' ');
        }
        
        // Fechas seleccionadas
        if (this.selectedStartDate && dateString === this.selectedStartDate) {
            classes.push('selected-start');
        } else if (this.selectedEndDate && dateString === this.selectedEndDate) {
            classes.push('selected-end');
        } else if (this.selectedStartDate && this.selectedEndDate && 
                   this.isDateInRange(date, new Date(this.selectedStartDate), new Date(this.selectedEndDate))) {
            classes.push('in-range');
        } else {
            classes.push('available');
        }
        
        return classes.join(' ');
    }
    
    getDayTooltip(date, dateString) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        date.setHours(0, 0, 0, 0);
        
        if (date < today) {
            return 'Fecha pasada - no disponible';
        }
        
        if (this.occupiedDates.includes(dateString)) {
            return 'Fecha ocupada - no disponible';
        }
        
        if (this.selectedStartDate && dateString === this.selectedStartDate) {
            return 'Fecha de entrada seleccionada';
        }
        
        if (this.selectedEndDate && dateString === this.selectedEndDate) {
            return 'Fecha de salida seleccionada';
        }
        
        if (this.selectedStartDate && this.selectedEndDate && 
            this.isDateInRange(date, new Date(this.selectedStartDate), new Date(this.selectedEndDate))) {
            return 'Incluido en la estancia';
        }
        
        return 'Disponible - Clic para seleccionar';
    }
    
    selectDay(dayElement) {
        const dateString = dayElement.dataset.date;
        const date = new Date(dateString);
        
        console.log('Día seleccionado:', dateString);
        
        // Verificar que sea un día válido para seleccionar
        if (dayElement.classList.contains('disabled') || dayElement.classList.contains('occupied')) {
            console.log('Día no válido para selección');
            return;
        }
        
        // Lógica de selección de fechas
        if (!this.selectedStartDate) {
            // Primera selección - fecha de entrada
            this.selectedStartDate = dateString;
            this.selectedEndDate = null;
            this.updateInstructions('Fecha de entrada seleccionada. Ahora selecciona la fecha de salida.');
            console.log('Fecha de entrada establecida:', this.selectedStartDate);
            
        } else if (!this.selectedEndDate) {
            // Segunda selección - fecha de salida
            const startDate = new Date(this.selectedStartDate);
            
            if (date <= startDate) {
                // La fecha seleccionada es anterior o igual a la de entrada - reiniciar
                this.selectedStartDate = dateString;
                this.selectedEndDate = null;
                this.updateInstructions('Nueva fecha de entrada seleccionada. Ahora selecciona la fecha de salida.');
                console.log('Fecha de entrada reestablecida:', this.selectedStartDate);
            } else {
                // Fecha de salida válida
                this.selectedEndDate = dateString;
                this.showSelectedDates();
                this.updateInstructions('¡Perfecto! Fechas seleccionadas. Puedes confirmar la reserva.');
                console.log('Rango de fechas completo:', this.selectedStartDate, 'a', this.selectedEndDate);
                
                // Habilitar botón de confirmación
                const confirmBtn = document.getElementById('confirmReservationNew');
                if (confirmBtn) {
                    confirmBtn.disabled = false;
                }
            }
            
        } else {
            // Ya hay ambas fechas - reiniciar con nueva fecha de entrada
            this.selectedStartDate = dateString;
            this.selectedEndDate = null;
            this.hideSelectedDates();
            this.updateInstructions('Nueva fecha de entrada seleccionada. Ahora selecciona la fecha de salida.');
            console.log('Reinicio de selección, nueva entrada:', this.selectedStartDate);
            
            // Deshabilitar botón de confirmación
            const confirmBtn = document.getElementById('confirmReservationNew');
            if (confirmBtn) {
                confirmBtn.disabled = true;
            }
        }
        
        // Actualizar inputs ocultos para compatibilidad
        this.updateHiddenInputs();
        
        // Re-renderizar calendario para mostrar cambios visuales
        this.renderCalendar();
    }
    
    showSelectedDates() {
        const datesDisplay = document.getElementById('selectedDatesDisplay');
        const checkinDisplay = document.getElementById('displayCheckinDate');
        const checkoutDisplay = document.getElementById('displayCheckoutDate');
        const nightsSummary = document.getElementById('nightsSummary');
        
        if (datesDisplay && checkinDisplay && checkoutDisplay && nightsSummary) {
            datesDisplay.style.display = 'block';
            
            // Formatear fechas para mostrar
            checkinDisplay.textContent = this.formatDateDisplay(this.selectedStartDate);
            checkoutDisplay.textContent = this.formatDateDisplay(this.selectedEndDate);
            
            // Calcular y mostrar noches
            const nights = this.calculateNights();
            nightsSummary.textContent = `${nights} ${nights === 1 ? 'noche' : 'noches'}`;
            
            console.log('✓ Información de fechas mostrada:', nights, 'noches');
        }
    }
    
    hideSelectedDates() {
        const datesDisplay = document.getElementById('selectedDatesDisplay');
        if (datesDisplay) {
            datesDisplay.style.display = 'none';
        }
    }
    
    updateInstructions(message) {
        const instructionEl = document.getElementById('instructionMessage');
        if (instructionEl) {
            instructionEl.textContent = message;
        }
    }
    
    updateHiddenInputs() {
        const checkinInput = document.getElementById('checkinDate');
        const checkoutInput = document.getElementById('checkoutDate');
        
        if (checkinInput) {
            checkinInput.value = this.selectedStartDate || '';
        }
        
        if (checkoutInput) {
            checkoutInput.value = this.selectedEndDate || '';
        }
    }
    
    previousMonth() {
        this.currentDate.setMonth(this.currentDate.getMonth() - 1);
        this.renderCalendar();
        console.log('Navegación: Mes anterior');
    }
    
    nextMonth() {
        this.currentDate.setMonth(this.currentDate.getMonth() + 1);
        this.renderCalendar();
        console.log('Navegación: Mes siguiente');
    }
    
    formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    formatDateDisplay(dateString) {
        const date = new Date(dateString + 'T00:00:00');
        const day = date.getDate();
        const month = this.monthNames[date.getMonth()];
        const year = date.getFullYear();
        return `${day} de ${month} ${year}`;
    }
    
    isDateInRange(date, startDate, endDate) {
        return date > startDate && date < endDate;
    }
    
    calculateNights() {
        if (!this.selectedStartDate || !this.selectedEndDate) return 0;
        
        const start = new Date(this.selectedStartDate + 'T00:00:00');
        const end = new Date(this.selectedEndDate + 'T00:00:00');
        const timeDiff = end.getTime() - start.getTime();
        return Math.ceil(timeDiff / (1000 * 3600 * 24));
    }
    
    confirmReservation() {
        if (!this.selectedStartDate || !this.selectedEndDate || !this.currentCabin) {
            alert('Error: Información incompleta para la reserva');
            return;
        }
        
        const nights = this.calculateNights();
        const pricePerNight = this.currentCabin.price || 0;
        const totalPrice = nights * pricePerNight;
        
        const reservationData = {
            cabin: this.currentCabin,
            checkin: this.selectedStartDate,
            checkout: this.selectedEndDate,
            nights: nights,
            pricePerNight: pricePerNight,
            totalPrice: totalPrice
        };
        
        console.log('Datos de reserva:', reservationData);
        
        // Enviar datos directamente al formulario de reserva
        console.log('✅ Procesando reserva...');
        
        // Deshabilitar el botón para evitar múltiples envíos
        const confirmBtn = document.getElementById('confirmReservationNew');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        }
        
        // Enviar datos al formulario de reserva
        const reservationForm = document.getElementById('reservationForm');
        if (reservationForm) {
            document.getElementById('reserveCabinId').value = this.currentCabin.id;
            document.getElementById('reserveDateStart').value = this.selectedStartDate;
            document.getElementById('reserveDateEnd').value = this.selectedEndDate;
            
            console.log('📋 Enviando datos del formulario:', {
                cabania_id: this.currentCabin.id,
                fecha_inicio: this.selectedStartDate,
                fecha_fin: this.selectedEndDate
            });
            
            // Pequeña pausa para que el usuario vea el cambio en el botón
            setTimeout(() => {
                reservationForm.submit();
            }, 500);
        } else {
            console.error('❌ No se encontró el formulario de reserva');
            alert('Error: No se pudo procesar la reserva. Inténtelo de nuevo.');
            
            // Restaurar el botón si hay error
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check"></i> Confirmar Reserva';
            }
        }
        
        // Cerrar modal
        this.closeModal();
    }
}

// Función global para cerrar el modal (llamada desde el HTML)
function closeAvailabilityModal() {
    if (window.newCalendarSystem) {
        window.newCalendarSystem.closeModal();
    }
}

// Auto-inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando sistema de calendario...');
    window.newCalendarSystem = new NewCalendarSystem();
});

// También inicializar si el DOM ya está listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        if (!window.newCalendarSystem) {
            console.log('🚀 Inicializando sistema de calendario (DOM ya listo)...');
            window.newCalendarSystem = new NewCalendarSystem();
        }
    });
} else {
    console.log('🚀 Inicializando sistema de calendario (DOM completo)...');
    window.newCalendarSystem = new NewCalendarSystem();
}