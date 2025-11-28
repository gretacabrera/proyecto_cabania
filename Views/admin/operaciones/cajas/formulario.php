<?php
/**
 * Vista: Formulario de Caja
 * Descripción: Formulario para crear/editar cajas
 * Autor: Sistema MVC
 * Fecha: <?php echo date('Y-m-d'); ?>
 */

$isEdit = isset($caja) && !empty($caja);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/cajas') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos de la caja' : 'Datos de la nueva caja' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formCaja" method="POST" 
                          action="<?= $isEdit ? url('/cajas/' . $caja['id_caja'] . '/edit') : url('/cajas/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_caja" value="<?= $caja['id_caja'] ?>">
                        <?php endif; ?>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="caja_descripcion" class="required">
                                <i class="fas fa-align-left"></i> Descripción
                            </label>
                            <input type="text" class="form-control" id="caja_descripcion" name="caja_descripcion" 
                                   value="<?= htmlspecialchars($caja['caja_descripcion'] ?? '') ?>"
                                   required maxlength="45" placeholder="Descripción de la caja">
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">Nombre identificador de la caja (máx. 45 caracteres)</small>
                        </div>

                        <!-- Usuario Responsable -->
                        <div class="form-group">
                            <label for="rela_usuario" class="required">
                                <i class="fas fa-user"></i> Usuario Responsable
                            </label>
                            <select class="form-select" id="rela_usuario" name="rela_usuario" required>
                                <option value="">Seleccione un usuario</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= $usuario['id_usuario'] ?>"
                                            <?= (isset($caja['rela_usuario']) && $caja['rela_usuario'] == $usuario['id_usuario']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($usuario['usuario_nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">Usuario a cargo de la caja</small>
                        </div>

                        <!-- Botones de acción -->
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $isEdit ? 'Actualizar' : 'Guardar' ?>
                            </button>
                            <button type="reset" class="btn btn-dark">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>
                            <a href="<?= url('/cajas') ?>" class="btn btn-danger">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
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
                            <li>• Use descripciones claras y únicas para cada caja</li>
                            <li>• Asigne un usuario responsable con perfil de cajero</li>
                            <li>• Verifique que el usuario tenga los permisos necesarios</li>
                            <li>• La caja se activará automáticamente al crearla</li>
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
                                        <div class="stat-value"><?= number_format($estadisticas['turnos_totales']) ?></div>
                                        <div class="stat-label small text-muted">Turnos totales</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value"><?= number_format($estadisticas['turnos_abiertos']) ?></div>
                                        <div class="stat-label small text-muted">Turnos abiertos</div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value"><?= number_format($estadisticas['movimientos_totales']) ?></div>
                                        <div class="stat-label small text-muted">Movimientos</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value text-success">$<?= number_format($estadisticas['monto_total_movimientos'], 0) ?></div>
                                        <div class="stat-label small text-muted">Saldo total</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear la caja.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para validaciones -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formCaja');
    
    // Validación del formulario
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Limpiar validación en reset
    form.addEventListener('reset', function() {
        form.classList.remove('was-validated');
    });
});
</script>

<style>
.required::after {
    content: " *";
    color: red;
}
</style>
