<?php
/**
 * Vista para restablecer contraseña
 * Sigue el patrón de diseño del sistema de autenticación
 */
?>

<form method="POST" action="<?= url('/auth/reset-password?token=' . urlencode($token ?? '')) ?>" class="modern-form" id="resetPasswordForm">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
    
    <!-- Información del usuario -->
    <div class="form-info">
        <div class="info-item">
            <i class="fas fa-user text-primary"></i>
            <div>
                <strong>Usuario:</strong> <?= htmlspecialchars($usuario_nombre ?? 'Usuario') ?>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="new_password">
            <i class="fas fa-lock"></i>
            Nueva Contraseña
        </label>
        <div class="password-input-group">
            <input 
                type="password" 
                id="new_password" 
                name="new_password" 
                class="form-control"
                placeholder="Ingrese su nueva contraseña"
                required 
                minlength="6"
                autocomplete="new-password"
            >
            <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        <small class="form-text text-muted">Mínimo 6 caracteres</small>
    </div>

    <div class="form-group">
        <label for="confirm_password">
            <i class="fas fa-lock"></i>
            Confirmar Contraseña
        </label>
        <div class="password-input-group">
            <input 
                type="password" 
                id="confirm_password" 
                name="confirm_password" 
                class="form-control"
                placeholder="Confirme su nueva contraseña"
                required 
                minlength="6"
                autocomplete="new-password"
            >
            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
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

    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
        <i class="fas fa-key"></i>
        Restablecer Contraseña
    </button>
</form>

<div class="auth-divider">
    <span>o</span>
</div>

<div class="auth-alternative">
    <p>¿Recordó su contraseña?</p>
    <a href="<?= url('/auth/login') ?>" class="btn btn-outline-primary">
        <i class="fas fa-sign-in-alt"></i>
        Volver al Login
    </a>
</div>

<!-- Información de seguridad -->
<div class="auth-security-info">
    <div class="security-item">
        <i class="fas fa-key text-success"></i>
        <div class="security-content">
            <h6>Contraseña Segura</h6>
            <small>Combine letras, números y símbolos</small>
        </div>
    </div>
    <div class="security-item">
        <i class="fas fa-shield-alt text-info"></i>
        <div class="security-content">
            <h6>Seguridad Total</h6>
            <small>Todas las sesiones se cerrarán automáticamente</small>
        </div>
    </div>
    <div class="security-item">
        <i class="fas fa-clock text-warning"></i>
        <div class="security-content">
            <h6>Enlace Único</h6>
            <small>Este enlace solo funciona una vez</small>
        </div>
    </div>
</div>

<style>
.form-info {
    background: rgba(0, 123, 255, 0.1);
    border: 1px solid rgba(0, 123, 255, 0.2);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--secondary-700);
}

.info-item i {
    font-size: 1.1rem;
}

.password-strength {
    margin-bottom: 1.5rem;
}

.strength-bar {
    width: 100%;
    height: 4px;
    background-color: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.strength-progress {
    height: 100%;
    width: 0%;
    border-radius: 2px;
    transition: width 0.3s ease, background-color 0.3s ease;
}

.strength-text {
    display: block;
    text-align: center;
    color: var(--secondary-600);
    font-size: 0.875rem;
}

.strength-weak {
    background-color: #dc3545;
}

.strength-medium {
    background-color: #ffc107;
}

.strength-strong {
    background-color: #28a745;
}

.auth-security-info {
    margin-top: 2rem;
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.security-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.security-item:last-child {
    margin-bottom: 0;
}

.security-item i {
    font-size: 1.2rem;
    margin-top: 0.1rem;
    opacity: 0.8;
}

.security-content h6 {
    margin: 0 0 0.25rem 0;
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.875rem;
    font-weight: 600;
}

.security-content small {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.75rem;
    line-height: 1.4;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resetPasswordForm');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submitBtn');
    const strengthProgress = document.getElementById('strengthProgress');
    const strengthText = document.getElementById('strengthText');
    
    // Validador de fuerza de contraseña
    newPasswordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = calculatePasswordStrength(password);
        updateStrengthIndicator(strength, password.length);
        validateForm();
    });
    
    confirmPasswordInput.addEventListener('input', function() {
        validateForm();
    });
    
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
    
    function validateForm() {
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        const isValid = newPassword.length >= 6 && 
                       confirmPassword.length >= 6 && 
                       newPassword === confirmPassword;
        
        submitBtn.disabled = !isValid;
        
        // Mostrar indicadores visuales de validación
        if (confirmPassword.length > 0) {
            if (newPassword === confirmPassword) {
                confirmPasswordInput.classList.remove('is-invalid');
                confirmPasswordInput.classList.add('is-valid');
            } else {
                confirmPasswordInput.classList.remove('is-valid');
                confirmPasswordInput.classList.add('is-invalid');
            }
        } else {
            confirmPasswordInput.classList.remove('is-valid', 'is-invalid');
        }
    }
    
    form.addEventListener('submit', function(e) {
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
            confirmPasswordInput.focus();
            return;
        }
        
        if (newPassword.length < 6) {
            e.preventDefault();
            alert('La contraseña debe tener al menos 6 caracteres');
            newPasswordInput.focus();
            return;
        }
        
        // Mostrar estado de carga
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Restableciendo...';
        
        // Re-habilitar después de 15 segundos para evitar bloqueo permanente
        setTimeout(function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-key"></i> Restablecer Contraseña';
        }, 15000);
    });
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