<?php
/**
 * Vista: Formulario de Usuario
 * Descripción: Formulario para crear/editar usuarios
 */

$isEdit = isset($usuario) && !empty($usuario);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/usuarios') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Formulario principal -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit"></i> 
                        <?= $isEdit ? 'Modificar datos del usuario' : 'Datos del nuevo usuario' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formUsuario" method="POST" 
                          action="<?= $isEdit ? url('/usuarios/' . $usuario['id_usuario'] . '/edit') : url('/usuarios/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
                        <?php endif; ?>

                        <div class="row">
                            <!-- Nombre de Usuario -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="usuario_nombre" class="required">Nombre de Usuario</label>
                                    <input type="text" class="form-control" id="usuario_nombre" name="usuario_nombre" 
                                           value="<?= htmlspecialchars($usuario['usuario_nombre'] ?? '') ?>"
                                           required maxlength="45" placeholder="Nombre único del usuario">
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Único en el sistema, se usará para iniciar sesión</small>
                                </div>
                            </div>

                            <!-- Perfil -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rela_perfil" class="required">Perfil</label>
                                    <select class="form-select" id="rela_perfil" name="rela_perfil" required>
                                        <option value="">Seleccione un perfil...</option>
                                        <?php if (!empty($perfiles)): ?>
                                            <?php foreach ($perfiles as $perfil): ?>
                                                <option value="<?= $perfil['id_perfil'] ?>"
                                                        <?= (isset($usuario) && $usuario['rela_perfil'] == $perfil['id_perfil']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($perfil['perfil_descripcion']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Contraseña -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="usuario_contrasenia" <?= $isEdit ? '' : 'class="required"' ?>>Contraseña</label>
                                    <input type="password" class="form-control" id="usuario_contrasenia" name="usuario_contrasenia" 
                                           <?= $isEdit ? '' : 'required' ?> minlength="6" placeholder="Mínimo 6 caracteres">
                                    <div class="invalid-feedback"></div>
                                    <?php if ($isEdit): ?>
                                        <small class="form-text text-muted">Dejar vacío para mantener la contraseña actual</small>
                                    <?php else: ?>
                                        <small class="form-text text-muted">Mínimo 6 caracteres</small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Confirmar Contraseña -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="confirmar_contrasenia" <?= $isEdit ? '' : 'class="required"' ?>>Confirmar Contraseña</label>
                                    <input type="password" class="form-control" id="confirmar_contrasenia" name="confirmar_contrasenia" 
                                           <?= $isEdit ? '' : 'required' ?> placeholder="Confirme la contraseña">
                                    <div class="invalid-feedback"></div>
                                    <div id="password-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Información de la Persona -->
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-user-<?= $isEdit ? 'edit' : 'plus' ?>"></i> Información de la Persona
                        </h6>

                        <?php if ($isEdit): ?>
                            <input type="hidden" name="rela_persona" value="<?= $usuario['rela_persona'] ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="persona_nombre" class="required">Nombre</label>
                                    <input type="text" class="form-control" id="persona_nombre" 
                                           name="persona_nombre" required maxlength="45" 
                                           value="<?= htmlspecialchars($usuario['persona_nombre'] ?? '') ?>"
                                           placeholder="Nombre de la persona">
                                    <div class="invalid-feedback">El nombre es obligatorio</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="persona_apellido" class="required">Apellido</label>
                                    <input type="text" class="form-control" id="persona_apellido" 
                                           name="persona_apellido" required maxlength="45" 
                                           value="<?= htmlspecialchars($usuario['persona_apellido'] ?? '') ?>"
                                           placeholder="Apellido de la persona">
                                    <div class="invalid-feedback">El apellido es obligatorio</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="persona_fechanac" class="required">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control" id="persona_fechanac" 
                                           name="persona_fechanac" required 
                                           value="<?= htmlspecialchars($usuario['persona_fechanac'] ?? '') ?>"
                                           max="<?= date('Y-m-d') ?>">
                                    <div class="invalid-feedback">La fecha de nacimiento es obligatoria</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="persona_direccion" class="required">Dirección</label>
                                    <input type="text" class="form-control" id="persona_direccion" 
                                           name="persona_direccion" required maxlength="45" 
                                           value="<?= htmlspecialchars($usuario['persona_direccion'] ?? '') ?>"
                                           placeholder="Dirección completa">
                                    <div class="invalid-feedback">La dirección es obligatoria</div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <hr>
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Guardar Usuario
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="limpiarFormulario()">
                                    <i class="fas fa-eraser"></i> Limpiar
                                </button>
                            </div>
                            <div>
                                <a href="<?= url('/usuarios') ?>" class="btn btn-light">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Columna lateral con información -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información
                    </h6>
                </div>
                <div class="card-body">
                    <div class="info-section">
                        <h6><i class="fas fa-lightbulb text-warning"></i> Consejos</h6>
                        <ul class="list-unstyled small text-muted">
                            <li>• El nombre de usuario debe ser único en el sistema</li>
                            <li>• La contraseña debe tener al menos 6 caracteres</li>
                            <li>• El perfil determina los permisos del usuario</li>
                            <li>• Se enviará un email de verificación al crear el usuario</li>
                        </ul>
                    </div>

                    <hr>

                    <div class="info-section">
                        <h6><i class="fas fa-chart-line text-info"></i> Estadísticas</h6>
                        <br>
                        <?php if ($isEdit && isset($estadisticas)): ?>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value"><?= number_format($estadisticas['total_reservas'] ?? 0) ?></div>
                                        <div class="stat-label small text-muted">Total Reservas</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value"><?= number_format($estadisticas['reservas_activas'] ?? 0) ?></div>
                                        <div class="stat-label small text-muted">Reservas Activas</div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-12">
                                    <div class="stat-item">
                                        <div class="stat-value text-secondary">$<?= number_format($estadisticas['total_gastado'] ?? 0, 2) ?></div>
                                        <div class="stat-label small text-muted">Total Gastado</div>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($estadisticas['ultima_reserva'])): ?>
                                <hr>
                                <div class="text-center">
                                    <small class="text-muted">Última Reserva</small>
                                    <div class="small"><?= date('d/m/Y', strtotime($estadisticas['ultima_reserva']['reserva_fhinicio'])) ?></div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear el usuario.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación de contraseñas
document.getElementById('confirmar_contrasenia').addEventListener('input', function() {
    const password = document.getElementById('usuario_contrasenia').value;
    const confirmPassword = this.value;
    const feedback = document.getElementById('password-feedback');
    
    if (confirmPassword !== '') {
        if (password !== confirmPassword) {
            feedback.innerHTML = '<small class="text-danger">Las contraseñas no coinciden</small>';
            this.setCustomValidity('Las contraseñas no coinciden');
        } else {
            feedback.innerHTML = '<small class="text-success">Las contraseñas coinciden</small>';
            this.setCustomValidity('');
        }
    } else {
        feedback.innerHTML = '';
        this.setCustomValidity('');
    }
});

// Validación del formulario
document.getElementById('formUsuario').addEventListener('submit', function(e) {
    const password = document.getElementById('usuario_contrasenia').value;
    const confirmPassword = document.getElementById('confirmar_contrasenia').value;
    
    <?php if (!$isEdit): ?>
    if (password !== confirmPassword) {
        e.preventDefault();
        Swal.fire('Error', 'Las contraseñas no coinciden', 'error');
        return false;
    }
    <?php else: ?>
    if (password !== '' && password !== confirmPassword) {
        e.preventDefault();
        Swal.fire('Error', 'Las contraseñas no coinciden', 'error');
        return false;
    }
    <?php endif; ?>
    
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});

function limpiarFormulario() {
    document.getElementById('formUsuario').reset();
    document.getElementById('formUsuario').classList.remove('was-validated');
    document.getElementById('password-feedback').innerHTML = '';
}
</script>