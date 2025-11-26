<?php
/**
 * Vista: Formulario de Estado de Consumo
 * Descripción: Formulario para crear/editar estados de consumo
 */

$isEdit = isset($estado) && !empty($estado);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/estadosconsumo') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos del estado' : 'Datos del nuevo estado' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formEstado" method="POST" 
                          action="<?= $isEdit ? url('/estadosconsumo/' . $estado['id_estadoconsumo'] . '/edit') : url('/estadosconsumo/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_estadoconsumo" value="<?= $estado['id_estadoconsumo'] ?>">
                        <?php endif; ?>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="estadoconsumo_descripcion" class="required">
                                <i class="fas fa-tag"></i> Descripción
                            </label>
                            <input type="text" class="form-control" id="estadoconsumo_descripcion" name="estadoconsumo_descripcion" 
                                   value="<?= htmlspecialchars($estado['estadoconsumo_descripcion'] ?? '') ?>"
                                   required maxlength="100" placeholder="Ej: En proceso, Entregado, Cancelado">
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">Nombre descriptivo del estado de consumo</small>
                        </div>

                        <hr class="my-4">

                        <!-- Botones de acción -->
                        <div class="form-group mt-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save"></i> 
                                        <?= $isEdit ? 'Actualizar Estado' : 'Crear Estado' ?>
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
                            <li>• Use descripciones claras y concisas</li>
                            <li>• Los estados deben reflejar el ciclo de vida del consumo</li>
                            <li>• Evite crear estados duplicados o ambiguos</li>
                            <li>• Piense en el flujo de trabajo antes de crear</li>
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
                                        <div class="stat-value"><?= number_format($estadisticas['total_consumos']) ?></div>
                                        <div class="stat-label small text-muted">Consumos</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value">$<?= number_format($estadisticas['monto_total'], 0) ?></div>
                                        <div class="stat-label small text-muted">Monto Total</div>
                                    </div>
                                </div>
                            </div>
                            <?php if ($estadisticas['total_consumos'] > 0): ?>
                                <hr>
                                <div class="row text-center">
                                    <div class="col-12">
                                        <div class="stat-item">
                                            <div class="stat-value text-secondary">$<?= number_format($estadisticas['monto_promedio'], 0) ?></div>
                                            <div class="stat-label small text-muted">Promedio por Consumo</div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear el estado.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Limpiar formulario
function limpiarFormulario() {
    const form = document.getElementById('formEstado');
    form.reset();
}

// Validación en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const descripcion = document.getElementById('estadoconsumo_descripcion');
    
    if (descripcion) {
        descripcion.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    }
});
</script>
