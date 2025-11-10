<?php
/**
 * Vista: Formulario de Periodo
 * Descripción: Formulario para crear/editar periodos
 * Autor: Sistema MVC
 * Fecha: <?php echo date('Y-m-d'); ?>
 */

$isEdit = isset($periodo) && !empty($periodo);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/periodos') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos del periodo' : 'Datos del nuevo periodo' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formPeriodo" method="POST" 
                          action="<?= $isEdit ? url('/periodos/' . $periodo['id_periodo'] . '/edit') : url('/periodos/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_periodo" value="<?= $periodo['id_periodo'] ?>">
                        <?php endif; ?>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="periodo_descripcion" class="required">
                                <i class="fas fa-tag"></i> Descripción
                            </label>
                            <input type="text" class="form-control form-control-sm" id="periodo_descripcion" name="periodo_descripcion" 
                                   value="<?= htmlspecialchars($periodo['periodo_descripcion'] ?? '') ?>"
                                   required maxlength="45" placeholder="Ej: Temporada Alta Verano 2024">
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">Nombre descriptivo del periodo</small>
                        </div>

                        <div class="row">
                            <!-- Fecha de Inicio -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="periodo_fechainicio" class="required">
                                        <i class="fas fa-calendar-day"></i> Fecha de Inicio
                                    </label>
                                    <input type="date" class="form-control form-control-sm" id="periodo_fechainicio" name="periodo_fechainicio" 
                                           value="<?= $periodo['periodo_fechainicio'] ?? '' ?>"
                                           required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Fecha de Fin -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="periodo_fechafin" class="required">
                                        <i class="fas fa-calendar-check"></i> Fecha de Fin
                                    </label>
                                    <input type="date" class="form-control form-control-sm" id="periodo_fechafin" name="periodo_fechafin" 
                                           value="<?= $periodo['periodo_fechafin'] ?? '' ?>"
                                           required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Año -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="periodo_anio" class="required">
                                        <i class="fas fa-calendar-alt"></i> Año
                                    </label>
                                    <input type="number" class="form-control form-control-sm" id="periodo_anio" name="periodo_anio" 
                                           value="<?= $periodo['periodo_anio'] ?? date('Y') ?>"
                                           required min="2020" max="2100">
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Año al que corresponde el periodo</small>
                                </div>
                            </div>

                            <!-- Orden -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="periodo_orden" class="required">
                                        <i class="fas fa-sort-numeric-down"></i> Orden
                                    </label>
                                    <input type="number" class="form-control form-control-sm" id="periodo_orden" name="periodo_orden" 
                                           value="<?= $periodo['periodo_orden'] ?? '1' ?>"
                                           required min="1" max="100">
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Orden de visualización</small>
                                </div>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="form-group">
                            <label for="periodo_estado" class="required">
                                <i class="fas fa-toggle-on"></i> Estado
                            </label>
                            <select class="form-select form-select-sm" id="periodo_estado" name="periodo_estado" required>
                                <option value="1" <?= isset($periodo) && $periodo['periodo_estado'] == 1 ? 'selected' : '' ?>>Activo</option>
                                <option value="0" <?= isset($periodo) && $periodo['periodo_estado'] == 0 ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Botones -->
                        <div class="form-group mt-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                                <?php if (!$isEdit): ?>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="fas fa-eraser"></i> Limpiar
                                    </button>
                                <?php endif; ?>
                                <a href="<?= url('/periodos') ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Columna lateral -->
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
                            <li>• La descripción debe ser clara y descriptiva</li>
                            <li>• La fecha de inicio debe ser anterior a la fecha de fin</li>
                            <li>• Los periodos no deben solaparse entre sí</li>
                            <li>• El orden determina la secuencia de visualización</li>
                            <li>• Un periodo inactivo no estará disponible para uso</li>
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
                                        <div class="stat-value"><?= $estadisticas['total_reservas'] ?? 0 ?></div>
                                        <div class="stat-label small text-muted">Reservas</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value">$<?= number_format($estadisticas['ingresos_generados'] ?? 0, 0, ',', '.') ?></div>
                                        <div class="stat-label small text-muted">Ingresos</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row text-center mt-2">
                                <div class="col-12">
                                    <div class="stat-item">
                                        <div class="stat-value"><?= $estadisticas['duracion_dias'] ?? 0 ?> días</div>
                                        <div class="stat-label small text-muted">Duración</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear el periodo.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para validación -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formPeriodo');
    const fechaInicio = document.getElementById('periodo_fechainicio');
    const fechaFin = document.getElementById('periodo_fechafin');
    
    // Validar que fecha de inicio sea anterior a fecha de fin
    function validateDates() {
        if (fechaInicio.value && fechaFin.value) {
            if (fechaInicio.value >= fechaFin.value) {
                fechaFin.setCustomValidity('La fecha de fin debe ser posterior a la fecha de inicio');
                fechaFin.classList.add('is-invalid');
                return false;
            } else {
                fechaFin.setCustomValidity('');
                fechaFin.classList.remove('is-invalid');
                return true;
            }
        }
        return true;
    }
    
    fechaInicio.addEventListener('change', validateDates);
    fechaFin.addEventListener('change', validateDates);
    
    // Validación del formulario
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity() || !validateDates()) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        form.classList.add('was-validated');
    });
});
</script>