/**
 * Forms JavaScript - Casa de Palos Cabañas
 * JavaScript para formularios y validación
 */

// ===========================================
// FUNCIONES LEGACY DE AUTENTICACIÓN
// ===========================================

// Función para toggle de contraseña (legacy compatibility)
function togglePassword() {
    const passwordInput = document.getElementById('usuario_clave');
    const toggleIcon = document.getElementById('password-toggle-icon');
    
    if (passwordInput && toggleIcon) {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
}

// ===========================================
// SISTEMA DE VALIDACIÓN DE FORMULARIOS
// ===========================================

class FormValidator {
    constructor() {
        this.forms = new Map();
        this.rules = {
            required: (value) => value.trim() !== '',
            email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
            minLength: (value, min) => value.length >= min,
            maxLength: (value, max) => value.length <= max,
            numeric: (value) => /^\d+$/.test(value),
            phone: (value) => /^[\d\s\-\+\(\)]+$/.test(value) && value.replace(/\D/g, '').length >= 8,
            date: (value) => !isNaN(Date.parse(value)),
            url: (value) => /^https?:\/\/.+/.test(value)
        };
        this.initializeForms();
    }

    initializeForms() {
        const forms = document.querySelectorAll('form[data-validate]');
        forms.forEach(form => {
            this.bindFormValidation(form);
        });

        // Validación en tiempo real
        document.addEventListener('blur', (e) => {
            if (e.target.matches('input[data-rules], textarea[data-rules], select[data-rules]')) {
                this.validateField(e.target);
            }
        }, true);

        document.addEventListener('input', (e) => {
            if (e.target.matches('input[data-rules], textarea[data-rules]')) {
                // Limpiar error en tiempo real si el campo es válido
                if (e.target.classList.contains('error') && this.validateField(e.target, false)) {
                    this.clearFieldError(e.target);
                }
            }
        });
    }

    bindFormValidation(form) {
        form.addEventListener('submit', (e) => {
            if (!this.validateForm(form)) {
                e.preventDefault();
                return false;
            }
        });

        this.forms.set(form, {
            isValid: false,
            fields: form.querySelectorAll('[data-rules]')
        });
    }

    validateForm(form) {
        const fields = form.querySelectorAll('[data-rules]');
        let isValid = true;
        let firstError = null;

        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
                if (!firstError) {
                    firstError = field;
                }
            }
        });

        if (firstError) {
            firstError.focus();
            this.scrollToField(firstError);
        }

        return isValid;
    }

    validateField(field, showError = true) {
        const rules = field.getAttribute('data-rules').split('|');
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';

        for (const rule of rules) {
            const [ruleName, ...params] = rule.split(':');
            
            if (!this.rules[ruleName]) continue;

            let ruleValid;
            if (params.length > 0) {
                ruleValid = this.rules[ruleName](value, params[0]);
            } else {
                ruleValid = this.rules[ruleName](value);
            }

            if (!ruleValid) {
                isValid = false;
                errorMessage = this.getErrorMessage(ruleName, field, params[0]);
                break;
            }
        }

        if (showError) {
            if (isValid) {
                this.clearFieldError(field);
            } else {
                this.showFieldError(field, errorMessage);
            }
        }

        return isValid;
    }

    showFieldError(field, message) {
        field.classList.add('error');
        
        // Remover error anterior si existe
        const existingError = field.parentNode.querySelector('.campo-error');
        if (existingError) {
            existingError.remove();
        }

        // Crear nuevo mensaje de error
        const errorDiv = document.createElement('div');
        errorDiv.className = 'campo-error';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }

    clearFieldError(field) {
        field.classList.remove('error');
        const errorDiv = field.parentNode.querySelector('.campo-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    getErrorMessage(ruleName, field, param) {
        const fieldName = field.getAttribute('data-label') || field.name || 'Este campo';
        
        const messages = {
            required: `${fieldName} es obligatorio`,
            email: `${fieldName} debe ser un email válido`,
            minLength: `${fieldName} debe tener al menos ${param} caracteres`,
            maxLength: `${fieldName} no puede tener más de ${param} caracteres`,
            numeric: `${fieldName} debe contener solo números`,
            phone: `${fieldName} debe ser un teléfono válido`,
            date: `${fieldName} debe ser una fecha válida`,
            url: `${fieldName} debe ser una URL válida`
        };

        return messages[ruleName] || `${fieldName} no es válido`;
    }

    scrollToField(field) {
        const rect = field.getBoundingClientRect();
        const top = rect.top + window.pageYOffset - 100;
        
        window.scrollTo({
            top: top,
            behavior: 'smooth'
        });
    }

    addCustomRule(name, validator, errorMessage) {
        this.rules[name] = validator;
        // Aquí podrías extender los mensajes de error personalizados
    }
}

// ===========================================
// SISTEMA DE CONTADORES DE CARACTERES
// ===========================================

class CharacterCounter {
    constructor() {
        this.initializeCounters();
    }

    initializeCounters() {
        const textareas = document.querySelectorAll('textarea[data-max-length]');
        textareas.forEach(textarea => {
            this.bindCounter(textarea);
        });

        const inputs = document.querySelectorAll('input[data-max-length]');
        inputs.forEach(input => {
            this.bindCounter(input);
        });
    }

    bindCounter(element) {
        const maxLength = parseInt(element.getAttribute('data-max-length'));
        
        // Crear contador si no existe
        let counter = element.parentNode.querySelector('.contador-caracteres');
        if (!counter) {
            counter = document.createElement('div');
            counter.className = 'contador-caracteres';
            element.parentNode.appendChild(counter);
        }

        // Actualizar contador
        const updateCounter = () => {
            const currentLength = element.value.length;
            const remaining = maxLength - currentLength;
            
            counter.textContent = `${currentLength}/${maxLength} caracteres`;
            
            // Cambiar clases según el estado
            counter.classList.remove('warning', 'danger');
            
            if (remaining <= 0) {
                counter.classList.add('danger');
            } else if (remaining <= maxLength * 0.1) { // 10% restante
                counter.classList.add('warning');
            }
        };

        element.addEventListener('input', updateCounter);
        updateCounter(); // Inicializar contador
    }
}

// ===========================================
// SISTEMA DE RATING CON ESTRELLAS
// ===========================================

class StarRating {
    constructor() {
        this.initializeRatings();
    }

    initializeRatings() {
        const ratingContainers = document.querySelectorAll('.rating-stars');
        ratingContainers.forEach(container => {
            this.bindRating(container);
        });
    }

    bindRating(container) {
        const inputs = container.querySelectorAll('input[type="radio"]');
        const labels = container.querySelectorAll('label');

        labels.forEach((label, index) => {
            label.addEventListener('mouseover', () => {
                this.highlightStars(container, inputs.length - index);
            });

            label.addEventListener('mouseout', () => {
                this.resetStars(container);
            });

            label.addEventListener('click', () => {
                const rating = inputs.length - index;
                this.setRating(container, rating);
            });
        });
    }

    highlightStars(container, rating) {
        const labels = container.querySelectorAll('label');
        labels.forEach((label, index) => {
            if (index >= labels.length - rating) {
                label.style.color = '#ffc107';
            } else {
                label.style.color = '#dee2e6';
            }
        });
    }

    resetStars(container) {
        const checkedInput = container.querySelector('input:checked');
        if (checkedInput) {
            const rating = parseInt(checkedInput.value);
            this.highlightStars(container, rating);
        } else {
            const labels = container.querySelectorAll('label');
            labels.forEach(label => {
                label.style.color = '#dee2e6';
            });
        }
    }

    setRating(container, rating) {
        this.highlightStars(container, rating);
        
        // Disparar evento personalizado
        const event = new CustomEvent('ratingChanged', {
            detail: { rating: rating, container: container }
        });
        container.dispatchEvent(event);
    }
}

// ===========================================
// AUTOCOMPLETADO Y BÚSQUEDA
// ===========================================

class SearchAutocomplete {
    constructor() {
        this.searchInputs = new Map();
        this.initializeSearchInputs();
    }

    initializeSearchInputs() {
        const inputs = document.querySelectorAll('input[data-autocomplete]');
        inputs.forEach(input => {
            this.bindAutocomplete(input);
        });
    }

    bindAutocomplete(input) {
        const url = input.getAttribute('data-autocomplete');
        const minChars = parseInt(input.getAttribute('data-min-chars')) || 2;
        
        let timeout;
        let dropdown = null;

        input.addEventListener('input', (e) => {
            clearTimeout(timeout);
            
            if (e.target.value.length < minChars) {
                this.hideDropdown(dropdown);
                return;
            }

            timeout = setTimeout(() => {
                this.searchSuggestions(input, url, e.target.value)
                    .then(suggestions => {
                        dropdown = this.showSuggestions(input, suggestions);
                    });
            }, 300);
        });

        input.addEventListener('blur', (e) => {
            // Delay para permitir click en sugerencias
            setTimeout(() => {
                this.hideDropdown(dropdown);
            }, 200);
        });
    }

    async searchSuggestions(input, url, query) {
        try {
            const response = await fetch(`${url}?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            return data.suggestions || [];
        } catch (error) {
            console.error('Error fetching suggestions:', error);
            return [];
        }
    }

    showSuggestions(input, suggestions) {
        // Remover dropdown anterior
        const existingDropdown = input.parentNode.querySelector('.autocomplete-dropdown');
        if (existingDropdown) {
            existingDropdown.remove();
        }

        if (suggestions.length === 0) return null;

        const dropdown = document.createElement('div');
        dropdown.className = 'autocomplete-dropdown';
        
        suggestions.forEach(suggestion => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.textContent = suggestion.text || suggestion;
            item.addEventListener('click', () => {
                input.value = suggestion.text || suggestion;
                if (suggestion.value) {
                    input.setAttribute('data-selected-value', suggestion.value);
                }
                this.hideDropdown(dropdown);
                
                // Disparar evento de selección
                input.dispatchEvent(new Event('autocomplete:selected'));
            });
            dropdown.appendChild(item);
        });

        input.parentNode.style.position = 'relative';
        input.parentNode.appendChild(dropdown);

        return dropdown;
    }

    hideDropdown(dropdown) {
        if (dropdown && dropdown.parentNode) {
            dropdown.parentNode.removeChild(dropdown);
        }
    }
}

// ===========================================
// FORMULARIOS DINÁMICOS
// ===========================================

class DynamicForms {
    constructor() {
        this.bindEvents();
    }

    bindEvents() {
        // Añadir elementos dinámicamente
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-dynamic-field')) {
                e.preventDefault();
                this.addDynamicField(e.target);
            }

            if (e.target.classList.contains('remove-dynamic-field')) {
                e.preventDefault();
                this.removeDynamicField(e.target);
            }
        });

        // Campos condicionales
        document.addEventListener('change', (e) => {
            if (e.target.hasAttribute('data-conditional')) {
                this.handleConditionalField(e.target);
            }
        });
    }

    addDynamicField(button) {
        const templateId = button.getAttribute('data-template');
        const container = button.getAttribute('data-container');
        
        const template = document.getElementById(templateId);
        const targetContainer = document.querySelector(container);

        if (!template || !targetContainer) return;

        const clone = template.content.cloneNode(true);
        
        // Actualizar nombres e IDs para evitar duplicados
        const inputs = clone.querySelectorAll('input, select, textarea');
        const timestamp = Date.now();
        
        inputs.forEach(input => {
            if (input.name) {
                input.name = input.name.replace('[]', `[${timestamp}]`);
            }
            if (input.id) {
                input.id = `${input.id}_${timestamp}`;
            }
        });

        targetContainer.appendChild(clone);
    }

    removeDynamicField(button) {
        const fieldContainer = button.closest('.dynamic-field');
        if (fieldContainer) {
            fieldContainer.remove();
        }
    }

    handleConditionalField(element) {
        const conditions = JSON.parse(element.getAttribute('data-conditional'));
        const currentValue = element.value;

        Object.keys(conditions).forEach(targetSelector => {
            const targetElement = document.querySelector(targetSelector);
            if (!targetElement) return;

            const condition = conditions[targetSelector];
            let shouldShow = false;

            if (condition.value) {
                shouldShow = currentValue === condition.value;
            } else if (condition.values) {
                shouldShow = condition.values.includes(currentValue);
            } else if (condition.not) {
                shouldShow = currentValue !== condition.not;
            }

            if (shouldShow) {
                targetElement.style.display = '';
                targetElement.querySelectorAll('input, select, textarea').forEach(input => {
                    input.disabled = false;
                });
            } else {
                targetElement.style.display = 'none';
                targetElement.querySelectorAll('input, select, textarea').forEach(input => {
                    input.disabled = true;
                });
            }
        });
    }
}

// ===========================================
// INICIALIZACIÓN
// ===========================================

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todos los sistemas de formularios
    window.formValidator = new FormValidator();
    window.characterCounter = new CharacterCounter();
    window.starRating = new StarRating();
    window.searchAutocomplete = new SearchAutocomplete();
    window.dynamicForms = new DynamicForms();

    // Funciones globales para compatibilidad
    window.validateForm = function(form) {
        return window.formValidator.validateForm(form);
    };

    window.showFieldError = function(field, message) {
        window.formValidator.showFieldError(field, message);
    };

    window.clearFieldError = function(field) {
        window.formValidator.clearFieldError(field);
    };

    // Funciones globales de legacy compatibility
    window.togglePassword = togglePassword;

    // Inicializar contador de caracteres para comentarios
    initComentarioCharacterCounter();

    console.log('Casa de Palos - Forms System loaded successfully');
});

// ===========================================
// FUNCIONES ESPECÍFICAS DE COMENTARIOS
// ===========================================

function initComentarioCharacterCounter() {
    const textarea = document.getElementById('comentario_texto');
    const contador = document.getElementById('contador');
    
    if (textarea && contador) {
        textarea.addEventListener('input', function() {
            const caracteres = this.value.length;
            contador.textContent = caracteres;
            
            if (caracteres > 380) {
                contador.style.color = '#e74c3c';
            } else if (caracteres > 350) {
                contador.style.color = '#f39c12';
            } else {
                contador.style.color = '#666';
            }
        });
    }
    
    // Confirmación para eliminar
    document.querySelectorAll('[data-action="confirm-delete"]').forEach(function(element) {
        element.addEventListener('click', function(e) {
            const mensaje = this.getAttribute('data-message') || '¿Está seguro de que desea eliminar este elemento?';
            if (!confirm(mensaje)) {
                e.preventDefault();
            }
        });
    });
}