<?php
/**
 * Vista para solicitar recuperación de contraseña
 * Sigue el patrón de diseño del sistema de autenticación
 */
?>

<form method="POST" action="<?= url('/auth/forgot-password') ?>" class="modern-form" id="forgotPasswordForm">
    <div class="form-group">
        <label for="email">
            <i class="fas fa-envelope"></i>
            Email
        </label>
        <input 
            type="email" 
            id="email" 
            name="email" 
            class="form-control"
            placeholder="Ingrese su email registrado"
            required 
            autocomplete="email"
        >
        <small class="form-text text-muted">
            Le enviaremos un enlace seguro para restablecer su contraseña
        </small>
    </div>

    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
        <i class="fas fa-paper-plane"></i>
        Enviar Instrucciones
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

<div class="auth-help">
    <a href="<?= url('/auth/register') ?>" class="text-muted">
        <i class="fas fa-user-plus"></i>
        ¿No tiene cuenta? Regístrese aquí
    </a>
</div>

<!-- Información adicional de seguridad -->
<div class="auth-security-info">
    <div class="security-item">
        <i class="fas fa-shield-alt text-success"></i>
        <div class="security-content">
            <h6>Enlace Seguro</h6>
            <small>El enlace expirará en 1 hora por seguridad</small>
        </div>
    </div>
    <div class="security-item">
        <i class="fas fa-clock text-info"></i>
        <div class="security-content">
            <h6>Proceso Rápido</h6>
            <small>Recibirá el email en segundos</small>
        </div>
    </div>
    <div class="security-item">
        <i class="fas fa-envelope-open text-warning"></i>
        <div class="security-content">
            <h6>Revise su Email</h6>
            <small>También verifique la carpeta de spam</small>
        </div>
    </div>
</div>

<style>
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
    const form = document.getElementById('forgotPasswordForm');
    const submitBtn = document.getElementById('submitBtn');
    const originalBtnText = submitBtn.innerHTML;
    
    form.addEventListener('submit', function(e) {
        // Validación adicional del email
        const emailInput = document.getElementById('email');
        const email = emailInput.value.trim();
        
        if (!email) {
            e.preventDefault();
            alert('Por favor ingrese su email');
            return;
        }
        
        if (!isValidEmail(email)) {
            e.preventDefault();
            alert('Por favor ingrese un email válido');
            return;
        }
        
        // Mostrar estado de carga
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        
        // Re-habilitar después de 10 segundos para evitar bloqueo permanente
        setTimeout(function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }, 10000);
    });
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});
</script>