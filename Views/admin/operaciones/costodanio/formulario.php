<?php
/**
 * Vista: Formulario de Costo por Daño
 * Descripción: Formulario para crear/editar costos por daño
 */

$isEdit = isset($costo) && !empty($costo);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/costodanio') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos del costo por daño' : 'Datos del nuevo costo por daño' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formCostoDanio" method="POST" 
                          action="<?= $isEdit ? url('/costodanio/' . $costo['id_costodanio'] . '/edit') : url('/costodanio/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_costodanio" value="<?= $costo['id_costodanio'] ?>">
                        <?php endif; ?>

                        <div class="row">
                            <!-- Inventario -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rela_inventario" class="required">
                                        <i class="fas fa-box"></i> Inventario
                                    </label>
                                    <select class="form-select form-select-sm" id="rela_inventario" name="rela_inventario" required>
                                        <option value="">Seleccione un inventario</option>
                                        <?php foreach ($inventarios as $inventario): ?>
                                            <option value="<?= $inventario['id_inventario'] ?>" 
                                                    <?= ($isEdit && $costo['rela_inventario'] == $inventario['id_inventario']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($inventario['inventario_descripcion']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Seleccione el elemento del inventario afectado</small>
                                </div>
                            </div>

                            <!-- Nivel de Daño -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rela_niveldanio" class="required">
                                        <i class="fas fa-exclamation-triangle"></i> Nivel de Daño
                                    </label>
                                    <select class="form-select form-select-sm" id="rela_niveldanio" name="rela_niveldanio" required>
                                        <option value="">Seleccione un nivel de daño</option>
                                        <?php foreach ($nivelesDanio as $nivel): ?>
                                            <option value="<?= $nivel['id_niveldanio'] ?>" 
                                                    <?= ($isEdit && $costo['rela_niveldanio'] == $nivel['id_niveldanio']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($nivel['niveldanio_descripcion']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Seleccione la gravedad del daño</small>
                                </div>
                            </div>
                        </div>

                        <!-- Importe -->
                        <div class="form-group">
                            <label for="costodanio_importe" class="required">
                                <i class="fas fa-dollar-sign"></i> Importe del daño
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control" id="costodanio_importe" name="costodanio_importe" 
                                       value="<?= $costo['costodanio_importe'] ?? '' ?>"
                                       required min="0.01" step="0.01" placeholder="0.00">
                                <div class="invalid-feedback"></div>
                            </div>
                            <small class="form-text text-muted">Monto que se cobrará por este tipo de daño</small>
                        </div>

                        <!-- Botones de acción -->
                        <div class="form-group mt-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save"></i> 
                                        <?= $isEdit ? 'Actualizar Costo' : 'Crear Costo' ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-lg ml-2" 
                                            onclick="limpiarFormulario()">
                                        <i class="fas fa-eraser"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel lateral con información -->
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
                            <li>• Seleccione el inventario afectado correctamente</li>
                            <li>• Elija el nivel de daño apropiado según la gravedad</li>
                            <li>• El importe debe reflejar el costo real de reparación</li>
                            <li>• Mantenga los montos actualizados según el mercado</li>
                        </ul>
                    </div>

                    <hr>

                    <div class="info-section">
                        <h6><i class="fas fa-chart-line text-info"></i> Estadísticas</h6>
                        <br>
                        <?php if ($isEdit): ?>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value"><?= isset($estadisticas['aplicaciones_mes']) ? number_format($estadisticas['aplicaciones_mes']) : '0' ?></div>
                                        <div class="stat-label small text-muted">Aplicado mes</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value">$<?= isset($estadisticas['facturado_mes']) ? number_format($estadisticas['facturado_mes'], 0, ',', '.') : '0' ?></div>
                                        <div class="stat-label small text-muted">Facturado mes</div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value"><?= isset($estadisticas['aplicaciones_anio']) ? number_format($estadisticas['aplicaciones_anio']) : '0' ?></div>
                                        <div class="stat-label small text-muted">Aplicado año</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value">$<?= isset($estadisticas['facturado_anio']) ? number_format($estadisticas['facturado_anio'], 0, ',', '.') : '0' ?></div>
                                        <div class="stat-label small text-muted">Facturado año</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear el costo por daño.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para validación y funcionalidades -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formCostoDanio');
    
    // Validación al enviar el formulario
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    });
});

function limpiarFormulario() {
    const form = document.getElementById('formCostoDanio');
    form.reset();
    form.classList.remove('was-validated');
}
</script>

<style>
.required::after {
    content: " *";
    color: #dc3545;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #495057;
}

.stat-label {
    color: #6c757d;
    margin-top: 0.25rem;
}
</style>
