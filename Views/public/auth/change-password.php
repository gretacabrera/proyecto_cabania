<?php
/**
 * Vista para cambiar contraseña de usuario logueado
 * Sigue el patrón de diseño del sistema de autenticación
 */

// Asegurarse de que la sesión esté iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<form method="POST" action="<?= url('/auth/change-password') ?>" class="modern-form" id="changePasswordForm">
    
    <!-- Información del usuario -->
    <div class="form-info">
        <div class="info-item">
            <i class="fas fa-user text-primary"></i>
            <div>
                <strong>Usuario:</strong> <?= htmlspecialchars($usuario['usuario_nombre'] ?? 'Usuario') ?>
            </div>
        </div>
        <div class="info-item">
            <i class="fas fa-shield-alt text-success"></i>
            <div>
                <strong>Cambio de contraseña seguro</strong>
                <small class="d-block text-muted">Ingrese su contraseña actual para verificar su identidad</small>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="current_password">
            <i class="fas fa-key"></i>
            Contraseña Actual
        </label>
        <div class="password-input-group">
            <input 
                type="password" 
                id="current_password" 
                name="current_password" 
                class="form-control"
                placeholder="Ingrese su contraseña actual"
                required 
                autocomplete="current-password"
            >
            <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        <small class="form-text text-muted">Para verificar su identidad</small>
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
            Confirmar Nueva Contraseña
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
        <small class="strength-text" id="strengthText">Ingrese una nueva contraseña</small>
    </div>

    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
        <i class="fas fa-save"></i>
        Cambiar Contraseña
    </button>
</form>

<div class="auth-divider">
    <span>o</span>
</div>

<div class="auth-alternative">
    <p>¿Desea cancelar el cambio?</p>
    <a href="<?= url('/admin/dashboard') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i>
        Volver al Dashboard
    </a>
</div>

<!-- Información de seguridad -->
<div class="auth-security-info">
    <div class="security-item">
        <i class="fas fa-key text-success"></i>
        <div class="security-content">
            <h6>Contraseña Segura</h6>
            <small>Combine letras, números y símbolos para mayor seguridad</small>
        </div>
    </div>
    <div class="security-item">
        <i class="fas fa-shield-alt text-info"></i>
        <div class="security-content">
            <h6>Verificación de Identidad</h6>
            <small>Su contraseña actual es requerida para confirmar el cambio</small>
        </div>
    </div>
    <div class="security-item">
        <i class="fas fa-sign-out-alt text-warning"></i>
        <div class="security-content">
            <h6>Cierre de Sesión Automático</h6>
            <small>Su sesión actual se cerrará automáticamente después del cambio por seguridad</small>
        </div>
    </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('changePasswordForm');
    const currentPasswordInput = document.getElementById('current_password');
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
        validatePasswordMatch();
    });
    
    confirmPasswordInput.addEventListener('input', function() {
        validateForm();
        validatePasswordMatch();
    });
    
    currentPasswordInput.addEventListener('input', function() {
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
            text = 'Ingrese una nueva contraseña';
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
    
    function validatePasswordMatch() {
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
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
    
    function validateForm() {
        const currentPassword = currentPasswordInput.value;
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        const isValid = currentPassword.length > 0 &&
                       newPassword.length >= 6 && 
                       confirmPassword.length >= 6 && 
                       newPassword === confirmPassword &&
                       newPassword !== currentPassword;
        
        submitBtn.disabled = !isValid;
        
        // Validar que la nueva contraseña sea diferente
        if (newPassword.length > 0 && currentPassword.length > 0 && newPassword === currentPassword) {
            newPasswordInput.classList.add('is-invalid');
            if (!document.querySelector('.same-password-error')) {
                const errorMsg = document.createElement('small');
                errorMsg.className = 'form-text text-danger same-password-error';
                errorMsg.textContent = 'La nueva contraseña debe ser diferente a la actual';
                newPasswordInput.parentNode.parentNode.appendChild(errorMsg);
            }
        } else {
            newPasswordInput.classList.remove('is-invalid');
            const errorMsg = document.querySelector('.same-password-error');
            if (errorMsg) {
                errorMsg.remove();
            }
        }
    }
    
    form.addEventListener('submit', function(e) {
        const currentPassword = currentPasswordInput.value;
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (!currentPassword) {
            e.preventDefault();
            alert('Por favor ingrese su contraseña actual');
            currentPasswordInput.focus();
            return;
        }
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Las contraseñas nuevas no coinciden');
            confirmPasswordInput.focus();
            return;
        }
        
        if (newPassword.length < 6) {
            e.preventDefault();
            alert('La nueva contraseña debe tener al menos 6 caracteres');
            newPasswordInput.focus();
            return;
        }
        
        if (newPassword === currentPassword) {
            e.preventDefault();
            alert('La nueva contraseña debe ser diferente a la actual');
            newPasswordInput.focus();
            return;
        }
        
        // Mostrar estado de carga con advertencia de cierre de sesión
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cambiando contraseña y cerrando sesión...';
        
        // Re-habilitar después de 15 segundos para evitar bloqueo permanente
        setTimeout(function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Cambiar Contraseña';
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