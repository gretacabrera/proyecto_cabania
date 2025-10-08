<?php $this->layout('shared/layout_public', ['title' => $title]) ?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg mt-5">
                <div class="card-header text-center bg-primary text-white">
                    <h4><i class="fas fa-envelope-check"></i> Verificación de Email</h4>
                </div>
                <div class="card-body text-center p-4">
                    
                    <?php if ($type === 'success'): ?>
                        <!-- Verificación exitosa -->
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                        
                        <h5 class="text-success mb-3"><?= $title ?></h5>
                        <p class="text-muted mb-4"><?= htmlspecialchars($message) ?></p>
                        
                        <?php if (isset($usuario)): ?>
                            <div class="alert alert-success">
                                <strong>Usuario:</strong> <?= htmlspecialchars($usuario['usuario_nombre']) ?><br>
                                <strong>Verificado el:</strong> <?= date('d/m/Y H:i:s') ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <a href="/login" class="btn btn-success btn-lg me-2">
                                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                            </a>
                            <a href="/" class="btn btn-outline-primary">
                                <i class="fas fa-home"></i> Ir al Inicio
                            </a>
                        </div>
                        
                    <?php else: ?>
                        <!-- Error de verificación -->
                        <div class="mb-4">
                            <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                        </div>
                        
                        <h5 class="text-danger mb-3"><?= $title ?></h5>
                        <p class="text-muted mb-4"><?= htmlspecialchars($message) ?></p>
                        
                        <div class="alert alert-warning">
                            <h6>Posibles causas:</h6>
                            <ul class="text-start mb-0">
                                <li>El token ha expirado (válido por 24 horas)</li>
                                <li>El token ya fue utilizado</li>
                                <li>El enlace es incorrecto</li>
                            </ul>
                        </div>
                        
                        <div class="mt-4">
                            <a href="/auth/verification/status" class="btn btn-primary btn-lg me-2">
                                <i class="fas fa-redo"></i> Solicitar Nuevo Token
                            </a>
                            <a href="/login" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                            </a>
                        </div>
                        
                    <?php endif; ?>
                    
                </div>
                
                <div class="card-footer text-center text-muted">
                    <small>
                        <i class="fas fa-shield-alt"></i> 
                        Esta verificación confirma la validez de tu dirección de correo electrónico
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estilos adicionales -->
<style>
.card {
    border-radius: 15px;
    border: none;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    background: linear-gradient(135deg, #2c5530 0%, #1e3a22 100%) !important;
}

.btn {
    border-radius: 25px;
    padding: 10px 20px;
}

.alert {
    border-radius: 10px;
}

.fas {
    margin-bottom: 10px;
}
</style>