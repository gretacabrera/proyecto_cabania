<div class="register-container">
    <form method="POST" action="<?= url('/auth/register') ?>" class="modern-form" id="register-form" novalidate>
        <div class="form-section">
            <h3><i class="fas fa-user-circle"></i> Credenciales de acceso</h3>
            
            <div class="form-group">
                <label for="usuario_nombre">
                    <i class="fas fa-user"></i>
                    Usuario
                </label>
                <input 
                    type="text" 
                    id="usuario_nombre" 
                    name="usuario_nombre" 
                    class="form-control"
                    placeholder="Elige un nombre de usuario"
                    required 
                    minlength="3"
                    autocomplete="username"
                >
                <small class="form-text text-muted">Mínimo 3 caracteres</small>
            </div>
            
            <div class="form-group">
                <label for="usuario_contrasenia">
                    <i class="fas fa-lock"></i>
                    Contraseña
                </label>
                <div class="password-input-group">
                    <input 
                        type="password" 
                        id="usuario_contrasenia" 
                        name="usuario_contrasenia" 
                        class="form-control"
                        placeholder="Ingresa una contraseña segura"
                        required 
                        minlength="6"
                        autocomplete="new-password"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('usuario_contrasenia')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <!-- Indicador de fortaleza de contraseña -->
            <div class="password-strength" id="passwordStrength">
                <div class="strength-bar">
                    <div class="strength-progress" id="strengthProgress"></div>
                </div>
                <small class="strength-text" id="strengthText">Ingrese una contraseña</small>
            </div>
            
            <div class="form-group">
                <label for="confirmar_contrasenia">
                    <i class="fas fa-lock"></i>
                    Confirmar contraseña
                </label>
                <div class="password-input-group">
                    <input 
                        type="password" 
                        id="confirmar_contrasenia" 
                        name="confirmar_contrasenia" 
                        class="form-control"
                        placeholder="Repite la contraseña"
                        required 
                        autocomplete="new-password"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('confirmar_contrasenia')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3><i class="fas fa-address-card"></i> Datos personales</h3>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="nombre">
                        <i class="fas fa-user"></i>
                        Nombre
                    </label>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre" 
                        class="form-control"
                        placeholder="Tu nombre"
                        required 
                        autocomplete="given-name"
                    >
                </div>
                
                <div class="form-group col-md-6">
                    <label for="apellido">
                        <i class="fas fa-user"></i>
                        Apellido
                    </label>
                    <input 
                        type="text" 
                        id="apellido" 
                        name="apellido" 
                        class="form-control"
                        placeholder="Tu apellido"
                        required 
                        autocomplete="family-name"
                    >
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="fecha_nacimiento">
                        <i class="fas fa-calendar-alt"></i>
                        Fecha de nacimiento
                    </label>
                    <input 
                        type="date" 
                        id="fecha_nacimiento" 
                        name="fecha_nacimiento" 
                        class="form-control"
                        required 
                        autocomplete="bday"
                        max="<?= date('Y-m-d', strtotime('-18 years')) ?>"
                    >
                    <small class="form-text text-muted">Debe ser mayor de 18 años</small>
                </div>
                
                <div class="form-group col-md-6">
                    <label for="direccion">
                        <i class="fas fa-map-marker-alt"></i>
                        Dirección
                    </label>
                    <input 
                        type="text" 
                        id="direccion" 
                        name="direccion" 
                        class="form-control"
                        placeholder="Tu dirección completa"
                        required 
                        autocomplete="street-address"
                    >
                </div>
            </div>
            
        </div>

        <div class="form-section">
            <h3><i class="fas fa-address-book"></i> Información de contacto</h3>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control"
                        placeholder="tu@email.com"
                        required 
                        autocomplete="email"
                    >
                </div>
                
                <div class="form-group col-md-6">
                    <label for="telefono">
                        <i class="fas fa-phone"></i>
                        Teléfono
                    </label>
                    <input 
                        type="tel" 
                        id="telefono" 
                        name="telefono" 
                        class="form-control"
                        placeholder="123456789"
                        autocomplete="tel"
                    >
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="contacto_instagram">
                        <i class="fab fa-instagram"></i>
                        Instagram
                    </label>
                    <input 
                        type="text" 
                        id="contacto_instagram" 
                        name="contacto_instagram" 
                        class="form-control"
                        placeholder="@tu_usuario"
                        autocomplete="off"
                    >
                    <small class="form-text text-muted">Opcional</small>
                </div>
                
                <div class="form-group col-md-6">
                    <label for="contacto_facebook">
                        <i class="fab fa-facebook"></i>
                        Facebook
                    </label>
                    <input 
                        type="text" 
                        id="contacto_facebook" 
                        name="contacto_facebook" 
                        class="form-control"
                        placeholder="Tu perfil de Facebook"
                        autocomplete="off"
                    >
                    <small class="form-text text-muted">Opcional</small>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="accept_all" name="accept_all" required>
                    <label class="custom-control-label" for="accept_all">
                        <strong>Acepto términos, condiciones y deseo recibir ofertas especiales y novedades por email</strong>
                    </label>
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
            <i class="fas fa-user-plus"></i>
            Crear cuenta
        </button>
        
    </form>

    <div class="auth-divider">
        <span>o</span>
    </div>

    <div class="auth-alternative">
        <p>¿Ya tienes una cuenta?</p>
        <a href="<?= url('/auth/login') ?>" class="btn btn-outline-primary">
            <i class="fas fa-sign-in-alt"></i>
            Iniciar sesión
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('register-form');
    const password = document.getElementById('usuario_contrasenia');
    const confirmPassword = document.getElementById('confirmar_contrasenia');
    const username = document.getElementById('usuario_nombre');
    const acceptAll = document.getElementById('accept_all');
    const debugInfo = document.getElementById('debug-info');
    const debugText = document.getElementById('debug-text');
    const strengthProgress = document.getElementById('strengthProgress');
    const strengthText = document.getElementById('strengthText');
    
    // Mostrar debug info
    if (debugInfo) debugInfo.style.display = 'block';

    function updateDebug(message) {
        console.log('DEBUG:', message);
        if (debugText) debugText.innerHTML = message;
    }

    updateDebug('Formulario de registro inicializado correctamente');
    
    // Validador de fuerza de contraseña
    function calculatePasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 6) score += 1;
        if (password.length >= 8) score += 1;
        if (/[a-z]/.test(password)) score += 1;
        if (/[A-Z]/.test(password)) score += 1;
        if (/[0-9]/.test(password)) score += 1;
        if (/[^A-Za-z0-9]/.test(password)) score += 1;
        
        return score;
    }
    
    function updateStrengthIndicator(score, length) {
        if (!strengthProgress || !strengthText) return;
        
        let width, className, text;
        
        if (length === 0) {
            width = 0;
            className = '';
            text = 'Ingrese una contraseña';
        } else if (score <= 2) {
            width = 30;
            className = 'strength-weak';
            text = 'Contraseña débil';
        } else if (score <= 4) {
            width = 60;
            className = 'strength-medium';
            text = 'Contraseña media';
        } else {
            width = 100;
            className = 'strength-strong';
            text = 'Contraseña fuerte';
        }
        
        strengthProgress.style.width = width + '%';
        strengthProgress.className = 'strength-progress ' + className;
        strengthText.textContent = text;
    }

    // Agregar event listener al botón directamente también
    const submitBtn = document.getElementById('submit-btn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            updateDebug('Botón clickeado directamente');
            console.log('Botón de submit clickeado');
        });
    }

    // Validar contraseñas coincidentes
    function validatePasswords() {
        if (!password || !confirmPassword) return true; // Si no existen los campos, no validar
        
        const pass1 = password.value.trim();
        const pass2 = confirmPassword.value.trim();
        
        updateDebug(`Validando contraseñas: "${pass1}" vs "${pass2}"`);
        
        if (pass2.length > 0 && pass1 !== pass2) {
            confirmPassword.setCustomValidity('Las contraseñas no coinciden');
            updateDebug('❌ Error: contraseñas no coinciden');
            return false;
        } else {
            confirmPassword.setCustomValidity('');
            if (pass1 === pass2 && pass1.length > 0) {
                updateDebug('✅ Contraseñas coinciden');
            }
            return true;
        }
    }

    // Validar edad mínima (18 años)
    function validateAge() {
        const fechaNacimiento = document.getElementById('fecha_nacimiento');
        if (fechaNacimiento && fechaNacimiento.value) {
            const today = new Date();
            const birthDate = new Date(fechaNacimiento.value);
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            if (age < 18) {
                fechaNacimiento.setCustomValidity('Debe ser mayor de 18 años');
                updateDebug('Error: debe ser mayor de 18 años');
                return false;
            } else {
                fechaNacimiento.setCustomValidity('');
                return true;
            }
        }
        return true;
    }

    // Agregar validación de edad
    const fechaNacimiento = document.getElementById('fecha_nacimiento');
    if (fechaNacimiento) {
        fechaNacimiento.addEventListener('change', validateAge);
    }

    if (password) password.addEventListener('input', function() {
        const passwordValue = this.value;
        const strength = calculatePasswordStrength(passwordValue);
        updateStrengthIndicator(strength, passwordValue.length);
        
        // Validar coincidencia de contraseñas
        if (passwordValue.length > 0 && confirmPassword.value.length > 0) {
            validatePasswords();
        }
        
        // Validación visual de fortaleza
        if (passwordValue.length > 0) {
            if (strength <= 2) {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        } else {
            this.classList.remove('is-valid', 'is-invalid');
        }
    });
    
    if (confirmPassword) confirmPassword.addEventListener('input', function() {
        // Solo validar si ambos campos tienen contenido  
        if (password.value.length > 0 && confirmPassword.value.length > 0) {
            validatePasswords();
        }
        
        // Validación visual de coincidencia
        if (this.value.length > 0) {
            if (password.value === this.value) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        } else {
            this.classList.remove('is-valid', 'is-invalid');
        }
    });

    // Validación antes del envío
    if (form) {
        form.addEventListener('submit', function(e) {
            updateDebug('🚀 Enviando formulario...');
            console.log('Formulario enviado - validando...');
            
            // Validaciones básicas
            const passwordsValid = validatePasswords();
            const ageValid = validateAge();
            
            // Verificar campos requeridos
            let missingFields = [];
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (field.type === 'checkbox' && !field.checked) {
                    missingFields.push('Términos y condiciones');
                } else if (field.type !== 'checkbox' && !field.value.trim()) {
                    missingFields.push(field.name || field.id);
                }
            });
            
            // Si hay errores, mostrarlos
            if (!passwordsValid) {
                updateDebug('❌ Error: Las contraseñas no coinciden');
                e.preventDefault();
                alert('Las contraseñas no coinciden. Por favor verifíquelas.');
                return;
            }
            
            if (!ageValid) {
                updateDebug('❌ Error: Debe ser mayor de 18 años');
                e.preventDefault();
                alert('Debe ser mayor de 18 años para registrarse.');
                return;
            }
            
            if (missingFields.length > 0) {
                updateDebug('❌ Campos faltantes: ' + missingFields.join(', '));
                e.preventDefault();
                alert('Complete los siguientes campos: ' + missingFields.join(', '));
                return;
            }
            
            // Todo está bien
            updateDebug('✅ Formulario válido - enviando al servidor...');
            console.log('✅ Formulario válido - enviando...');
            
            // No prevenir el envío - dejar que se procese
        });
    } else {
        updateDebug('❌ Error crítico: No se encontró el formulario');
    }
});

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>