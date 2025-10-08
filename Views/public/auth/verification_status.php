<?php $this->layout('shared/layout_public', ['title' => $title]) ?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg mt-4">
                <div class="card-header text-center bg-info text-white">
                    <h4><i class="fas fa-envelope"></i> Estado de Verificación de Email</h4>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Información del usuario -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Usuario:</h6>
                            <p class="fw-bold"><?= htmlspecialchars($usuario['usuario_nombre']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Nombre:</h6>
                            <p class="fw-bold">
                                <?= htmlspecialchars(trim($usuario['persona_nombre'] . ' ' . $usuario['persona_apellido'])) ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted">Email:</h6>
                            <p class="fw-bold">
                                <i class="fas fa-envelope me-2"></i>
                                <?= htmlspecialchars($usuario['persona_email'] ?? 'No registrado') ?>
                            </p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Estado de verificación -->
                    <div class="text-center mb-4">
                        <?php if ($is_verified): ?>
                            <!-- Email verificado -->
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
                                <h5 class="mt-3 mb-2">¡Email Verificado!</h5>
                                <p class="mb-0">Tu dirección de correo electrónico ha sido verificada correctamente.</p>
                            </div>
                            
                            <div class="mt-4">
                                <a href="/login" class="btn btn-success btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Acceder al Sistema
                                </a>
                            </div>
                            
                        <?php else: ?>
                            <!-- Email no verificado -->
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                                <h5 class="mt-3 mb-2">Email No Verificado</h5>
                                <p class="mb-0">Tu dirección de correo electrónico aún no ha sido verificada.</p>
                            </div>
                            
                            <?php if ($can_resend): ?>
                                <div class="mt-4">
                                    <p class="text-muted mb-3">
                                        ¿No recibiste el email de verificación? Puedes solicitar un nuevo enlace.
                                    </p>
                                    
                                    <button type="button" class="btn btn-primary btn-lg" id="resendBtn">
                                        <i class="fas fa-paper-plane"></i> Reenviar Email de Verificación
                                    </button>
                                    
                                    <div id="resendResult" class="mt-3" style="display: none;"></div>
                                </div>
                            <?php endif; ?>
                            
                        <?php endif; ?>
                    </div>
                    
                </div>
                
                <div class="card-footer text-center text-muted">
                    <small>
                        <i class="fas fa-info-circle"></i> 
                        La verificación de email es importante para la seguridad de tu cuenta
                    </small>
                </div>
            </div>
        </div>
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

<!-- Estilos adicionales -->
<style>
.card {
    border-radius: 15px;
    border: none;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
}

.btn {
    border-radius: 25px;
    padding: 12px 25px;
}

.alert {
    border-radius: 10px;
}

.fw-bold {
    font-weight: 600;
}

.text-muted {
    font-size: 0.9rem;
}
</style>