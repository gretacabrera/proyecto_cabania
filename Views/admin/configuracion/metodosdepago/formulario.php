<?php
/**
 * Vista: Formulario de Método de Pago
 * Descripción: Formulario para crear/editar métodos de pago
 */

$isEdit = isset($metodo) && !empty($metodo);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/metodosdepago') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos del método de pago' : 'Datos del nuevo método de pago' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formMetodoPago" method="POST" 
                          action="<?= $isEdit ? url('/metodosdepago/' . $metodo['id_metododepago'] . '/edit') : url('/metodosdepago/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_metododepago" value="<?= $metodo['id_metododepago'] ?>">
                        <?php endif; ?>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="metododepago_descripcion" class="required">
                                <i class="fas fa-credit-card"></i> Descripción del Método de Pago
                            </label>
                            <input type="text" class="form-control" id="metododepago_descripcion" name="metododepago_descripcion" 
                                   value="<?= htmlspecialchars($metodo['metododepago_descripcion'] ?? '') ?>"
                                   required maxlength="45" placeholder="Ej: Efectivo, Tarjeta de Crédito, Transferencia">
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">
                                Nombre descriptivo del método de pago (máximo 45 caracteres)
                            </small>
                        </div>

                        <!-- Botones de acción -->
                        <div class="form-group mt-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save"></i> 
                                        <?= $isEdit ? 'Actualizar Método de Pago' : 'Crear Método de Pago' ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-lg ml-2" 
                                            onclick="limpiarFormulario()">
                                        <i class="fas fa-eraser"></i> Limpiar
                                    </button>
                                </div>
                                <div>
                                    <a href="<?= url('/metodosdepago') ?>" class="btn btn-outline-dark btn-lg">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
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
                            <li>• Use nombres descriptivos y claros</li>
                            <li>• Evite duplicar métodos existentes</li>
                            <li>• Los métodos se crean activos por defecto</li>
                            <li>• Puede desactivar métodos desde el listado</li>
                        </ul>
                    </div>

                    <?php if ($isEdit && isset($estadisticas)): ?>
                    <hr>

                    <div class="info-section">
                        <h6><i class="fas fa-chart-line text-info"></i> Estadísticas</h6>
                        <br>
                        
                        <div class="metric-box-compact mb-3">
                            <div class="d-flex align-items-center">
                                <div class="metric-icon-compact me-2">
                                    <i class="fas fa-receipt fa-lg text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="metric-value-compact text-primary"><?= number_format($estadisticas['total_pagos']) ?></div>
                                    <div class="metric-label-compact">Total Pagos</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="metric-box-compact mb-3">
                            <div class="d-flex align-items-center">
                                <div class="metric-icon-compact me-2">
                                    <i class="fas fa-dollar-sign fa-lg text-success"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="metric-value-compact text-success">$<?= number_format($estadisticas['monto_total'], 0, '.', ',') ?></div>
                                    <div class="metric-label-compact">Monto Total</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="metric-box-compact mb-3">
                            <div class="d-flex align-items-center">
                                <div class="metric-icon-compact me-2">
                                    <i class="fas fa-calendar-check fa-lg text-info"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="metric-value-compact text-info"><?= number_format($estadisticas['pagos_mes_actual']) ?></div>
                                    <div class="metric-label-compact">Este Mes</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="metric-box-compact">
                            <div class="d-flex align-items-center">
                                <div class="metric-icon-compact me-2">
                                    <i class="fas fa-clock fa-lg text-warning"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="metric-value-compact text-warning small">
                                        <?php if ($estadisticas['ultimo_uso']): ?>
                                            <?= date('d/m/Y H:i', strtotime($estadisticas['ultimo_uso'])) ?>
                                        <?php else: ?>
                                            Sin uso
                                        <?php endif; ?>
                                    </div>
                                    <div class="metric-label-compact">Último Uso</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para validación y funcionalidades -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formMetodoPago');
    
    // Validación del formulario
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
    
    // Validación en tiempo real
    const descripcionInput = document.getElementById('metododepago_descripcion');
    
    descripcionInput.addEventListener('input', function() {
        if (this.value.length < 2) {
            this.setCustomValidity('La descripción debe tener al menos 2 caracteres');
        } else if (this.value.length > 45) {
            this.setCustomValidity('La descripción no puede superar los 45 caracteres');
        } else {
            this.setCustomValidity('');
        }
    });
});

function limpiarFormulario() {
    const form = document.getElementById('formMetodoPago');
    form.reset();
    form.classList.remove('was-validated');
    
    // Remover cualquier mensaje de error personalizado
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.setCustomValidity('');
    });
}
</script>

<style>
.required::after {
    content: ' *';
    color: red;
}

.form-control.is-invalid,
.custom-file-input.is-invalid ~ .custom-file-label {
    border-color: #dc3545;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.stat-item {
    padding: 10px;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #007bff;
}

.stat-label {
    margin-top: 5px;
}

.info-section {
    margin-bottom: 15px;
}

.info-section:last-child {
    margin-bottom: 0;
}

.metric-box-compact {
    padding: 8px 0;
}

.metric-icon-compact {
    width: 40px;
    text-align: center;
}

.metric-value-compact {
    font-size: 1.25rem;
    font-weight: bold;
    line-height: 1.2;
}

.metric-label-compact {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
</style>
</style>

