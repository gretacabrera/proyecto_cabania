<?php if ($type === 'success'): ?>
    <!-- Verificación exitosa -->
    <div class="verification-success">
        <div class="verification-icon text-center mb-4">
            <div class="icon-circle success">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        
        <div class="verification-content text-center">
            <h3 class="text-success mb-3"><?= $title ?></h3>
            <p class="text-muted mb-4"><?= htmlspecialchars($message) ?></p>
            
            <?php if (isset($usuario)): ?>
                <div class="user-info-card">
                    <h6><i class="fas fa-user"></i> Detalles de la cuenta</h6>
                    <div class="info-row">
                        <span class="label">Usuario:</span>
                        <span class="value"><?= htmlspecialchars($usuario['usuario_nombre']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Verificado el:</span>
                        <span class="value"><?= date('d/m/Y H:i:s') ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="auth-divider">
        <span></span>
    </div>

    <div class="verification-actions">
        <a href="/login" class="btn btn-success btn-lg">
            <i class="fas fa-sign-in-alt"></i>
            Iniciar Sesión
        </a>
    </div>

    <div class="auth-alternative">
        <a href="/" class="btn btn-outline-primary">
            <i class="fas fa-home"></i>
            Ir al Inicio
        </a>
    </div>

<?php else: ?>
    <!-- Error de verificación -->
    <div class="verification-error">
        <div class="verification-icon text-center mb-4">
            <div class="icon-circle error">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
        
        <div class="verification-content text-center">
            <h3 class="text-danger mb-3"><?= $title ?></h3>
            <p class="text-muted mb-4"><?= htmlspecialchars($message) ?></p>
            
            <div class="troubleshooting-card">
                <h6><i class="fas fa-question-circle"></i> Posibles causas</h6>
                <ul class="causes-list">
                    <li><i class="fas fa-clock"></i> El token ha expirado (válido por 24 horas)</li>
                    <li><i class="fas fa-check-double"></i> El token ya fue utilizado</li>
                    <li><i class="fas fa-link"></i> El enlace es incorrecto</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="auth-divider">
        <span></span>
    </div>

    <div class="verification-actions">
        <a href="/auth/verification/status" class="btn btn-primary btn-lg">
            <i class="fas fa-redo"></i>
            Solicitar Nuevo Token
        </a>
    </div>

    <div class="auth-alternative">
        <a href="/proyecto_cabania/auth/login" class="btn btn-outline-primary">
            <i class="fas fa-sign-in-alt"></i>
            Iniciar Sesión
        </a>
    </div>

<?php endif; ?>

<div class="auth-help">
    <div class="security-notice">
        <i class="fas fa-shield-alt"></i>
        <span>Esta verificación confirma la validez de tu dirección de correo electrónico</span>
    </div>
</div>

<style>
/* Estilos para verification result usando el diseño auth */
.verification-success,
.verification-error {
    padding: 20px 0;
}

.verification-icon {
    margin-bottom: 30px;
}

.icon-circle {
    width: 120px;
    height: 120px;
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

.icon-circle.error {
    background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
    box-shadow: 0 10px 30px rgba(220, 53, 69, 0.3);
}

.icon-circle i {
    font-size: 48px;
    color: white;
}

.icon-circle::before {
    content: '';
    position: absolute;
    top: -10px;
    left: -10px;
    right: -10px;
    bottom: -10px;
    border: 2px solid;
    border-radius: 50%;
    opacity: 0.3;
    animation: pulse 2s infinite;
}

.icon-circle.success::before {
    border-color: #28a745;
}

.icon-circle.error::before {
    border-color: #dc3545;
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 0.3; }
    50% { transform: scale(1.1); opacity: 0.1; }
    100% { transform: scale(1); opacity: 0.3; }
}

.verification-content h3 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 16px;
}

.verification-content p {
    font-size: 16px;
    line-height: 1.6;
}

/* Cards de información */
.user-info-card,
.troubleshooting-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
    border-left: 4px solid #28a745;
}

.troubleshooting-card {
    border-left-color: #ffc107;
}

.user-info-card h6,
.troubleshooting-card h6 {
    font-size: 16px;
    font-weight: 600;
    color: #2c5530;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row .label {
    font-weight: 600;
    color: #495057;
}

.info-row .value {
    color: #2c5530;
    font-weight: 500;
}

.causes-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.causes-list li {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
    color: #495057;
}

.causes-list i {
    color: #ffc107;
    width: 16px;
}

/* Acciones de verificación */
.verification-actions {
    text-align: center;
    margin: 20px 0;
}

.verification-actions .btn {
    min-width: 200px;
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
    color: #28a745;
}

/* Responsive */
@media (max-width: 768px) {
    .icon-circle {
        width: 80px;
        height: 80px;
    }
    
    .icon-circle i {
        font-size: 32px;
    }
    
    .verification-content h3 {
        font-size: 22px;
    }
    
    .verification-actions .btn {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
}
</style>