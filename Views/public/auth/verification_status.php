<!-- Información del usuario -->
<div class="user-profile-card">
    <div class="profile-header">
        <i class="fas fa-user-circle"></i>
        <h4>Información del Usuario</h4>
    </div>
    
    <div class="profile-info">
        <div class="info-item">
            <label><i class="fas fa-user"></i> Usuario:</label>
            <span><?= htmlspecialchars($usuario['usuario_nombre']) ?></span>
        </div>
        
        <div class="info-item">
            <label><i class="fas fa-id-card"></i> Nombre:</label>
            <span><?= htmlspecialchars(trim($usuario['persona_nombre'] . ' ' . $usuario['persona_apellido'])) ?></span>
        </div>
        
        <div class="info-item">
            <label><i class="fas fa-envelope"></i> Email:</label>
            <span><?= htmlspecialchars($usuario['persona_email'] ?? 'No registrado') ?></span>
        </div>
    </div>
</div>

<div class="auth-divider">
    <span>Estado de Verificación</span>
</div>

<!-- Estado de verificación -->
<div class="verification-status-section">
    <?php if ($is_verified): ?>
        <!-- Email verificado -->
        <div class="status-verified text-center">
            <div class="status-icon">
                <div class="icon-circle success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            
            <h3 class="text-success mb-3">¡Email Verificado!</h3>
            <p class="text-muted mb-4">Tu dirección de correo electrónico ha sido verificada correctamente.</p>
            
            <div class="status-actions">
                <a href="proyecto_cabania/auth/login" class="btn btn-success btn-lg">
                    <i class="fas fa-sign-in-alt"></i>
                    Acceder al Sistema
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Email no verificado -->
        <div class="status-unverified text-center">
            <div class="status-icon">
                <div class="icon-circle warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            
            <h3 class="text-warning mb-3">Email No Verificado</h3>
            <p class="text-muted mb-4">Tu dirección de correo electrónico aún no ha sido verificada.</p>
            
            <?php if ($can_resend): ?>
                <div class="resend-section">
                    <p class="text-muted mb-3">
                        ¿No recibiste el email de verificación? Puedes solicitar un nuevo enlace.
                    </p>
                    
                    <button type="button" class="btn btn-primary btn-lg" id="resendBtn">
                        <i class="fas fa-paper-plane"></i>
                        Reenviar Email de Verificación
                    </button>
                    
                    <div id="resendResult" class="mt-3" style="display: none;"></div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<div class="auth-help">
    <div class="security-notice">
        <i class="fas fa-info-circle"></i>
        <span>La verificación de email es importante para la seguridad de tu cuenta</span>
    </div>
</div>

<!-- JavaScript para reenvío de verificación -->
<?php if (!$is_verified && $can_resend): ?>
<script>
document.getElementById('resendBtn').addEventListener('click', function() {
    const btn = this;
    const resultDiv = document.getElementById('resendResult');
    
    // Deshabilitar botón
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
    
    // Realizar petición AJAX
    fetch('/auth/verification/resend', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_id: <?= $usuario['id_usuario'] ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        // Mostrar resultado
        resultDiv.style.display = 'block';
        
        if (data.success) {
            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <i class="fas fa-check"></i> ${data.message}
                </div>
            `;
            
            // Ocultar botón después del envío exitoso
            btn.style.display = 'none';
        } else {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-times"></i> ${data.message}
                </div>
            `;
            
            // Rehabilitar botón después de 5 segundos
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Reenviar Email de Verificación';
            }, 5000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-times"></i> Error de conexión. Inténtalo nuevamente.
            </div>
        `;
        
        // Rehabilitar botón
        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Reenviar Email de Verificación';
        }, 3000);
    });
});
</script>
<?php endif; ?>

<style>
/* Estilos para verification status usando el diseño auth */
.user-profile-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
    border-left: 4px solid #007bff;
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}

.profile-header i {
    font-size: 24px;
    color: #007bff;
}

.profile-header h4 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #2c5530;
}

.profile-info {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.info-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
}

.info-item label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #495057;
    margin: 0;
    min-width: 120px;
}

.info-item label i {
    color: #007bff;
    width: 16px;
}

.info-item span {
    color: #2c5530;
    font-weight: 500;
    text-align: right;
    flex: 1;
}

/* Sección de estado de verificación */
.verification-status-section {
    padding: 20px 0;
}

.status-verified,
.status-unverified {
    padding: 20px 0;
}

.status-icon {
    margin-bottom: 30px;
}

.icon-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    position: relative;
}

.icon-circle.success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
}

.icon-circle.warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    box-shadow: 0 10px 30px rgba(255, 193, 7, 0.3);
}

.icon-circle i {
    font-size: 40px;
    color: white;
}

.icon-circle::before {
    content: '';
    position: absolute;
    top: -8px;
    left: -8px;
    right: -8px;
    bottom: -8px;
    border: 2px solid;
    border-radius: 50%;
    opacity: 0.3;
    animation: pulse 2s infinite;
}

.icon-circle.success::before {
    border-color: #28a745;
}

.icon-circle.warning::before {
    border-color: #ffc107;
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 0.3; }
    50% { transform: scale(1.1); opacity: 0.1; }
    100% { transform: scale(1); opacity: 0.3; }
}

.status-verified h3,
.status-unverified h3 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 16px;
}

.status-verified p,
.status-unverified p {
    font-size: 16px;
    line-height: 1.6;
}

/* Sección de reenvío */
.resend-section {
    margin-top: 20px;
}

.resend-section p {
    margin-bottom: 15px;
}

/* Acciones de estado */
.status-actions {
    text-align: center;
    margin: 20px 0;
}

.status-actions .btn,
.resend-section .btn {
    min-width: 250px;
    padding: 12px 24px;
    font-weight: 600;
    border-radius: 8px;
}

/* Ayuda y seguridad */
.auth-help {
    margin-top: 30px;
}

.security-notice {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #6c757d;
    font-size: 14px;
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.security-notice i {
    color: #007bff;
}

/* Estilos para resultados de reenvío */
#resendResult .alert {
    border-radius: 8px;
    margin-top: 15px;
}

/* Responsive */
@media (max-width: 768px) {
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .info-item label {
        min-width: auto;
    }
    
    .info-item span {
        text-align: left;
    }
    
    .icon-circle {
        width: 80px;
        height: 80px;
    }
    
    .icon-circle i {
        font-size: 32px;
    }
    
    .status-verified h3,
    .status-unverified h3 {
        font-size: 20px;
    }
    
    .status-actions .btn,
    .resend-section .btn {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
}
</style>