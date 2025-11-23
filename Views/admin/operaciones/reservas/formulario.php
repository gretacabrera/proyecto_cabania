<?php
/**
 * Vista: Formulario de Reserva
 * Descripción: Formulario para crear/editar reservas
 * Autor: Sistema MVC
 * Fecha: <?php echo date('Y-m-d'); ?>
 */

$isEdit = isset($reserva) && !empty($reserva);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/reservas') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos de la reserva' : 'Datos de la nueva reserva' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formReserva" method="POST" 
                          action="<?= $isEdit ? url('/reservas/' . $reserva['id_reserva'] . '/edit') : url('/reservas/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_reserva" value="<?= $reserva['id_reserva'] ?>">
                        <?php endif; ?>

                        <!-- Cabaña -->
                        <div class="form-group">
                            <label for="rela_cabania" class="required">
                                <i class="fas fa-home"></i> Cabaña
                            </label>
                            <select class="form-select" id="rela_cabania" name="rela_cabania" required>
                                <option value="">-- Seleccionar Cabaña --</option>
                                <?php foreach ($cabanias as $cabania): ?>
                                    <option value="<?= $cabania['id_cabania'] ?>" 
                                        <?= (isset($reserva['rela_cabania']) && $reserva['rela_cabania'] == $cabania['id_cabania']) ? 'selected' : '' ?>
                                        data-capacidad="<?= $cabania['cabania_capacidad'] ?>">
                                        <?= htmlspecialchars($cabania['cabania_codigo'] . ' - ' . $cabania['cabania_nombre']) ?> 
                                        (Cap: <?= $cabania['cabania_capacidad'] ?> personas)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">Seleccione la cabaña para la reserva</small>
                        </div>

                        <div class="row">
                            <!-- Fecha/Hora Inicio -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reserva_fhinicio" class="required">
                                        <i class="fas fa-sign-in-alt"></i> Fecha/Hora Inicio
                                    </label>
                                    <input type="datetime-local" class="form-control" id="reserva_fhinicio" name="reserva_fhinicio" 
                                           value="<?= htmlspecialchars($reserva['reserva_fhinicio'] ?? '') ?>"
                                           required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Fecha/Hora Fin -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reserva_fhfin" class="required">
                                        <i class="fas fa-sign-out-alt"></i> Fecha/Hora Fin
                                    </label>
                                    <input type="datetime-local" class="form-control" id="reserva_fhfin" name="reserva_fhfin" 
                                           value="<?= htmlspecialchars($reserva['reserva_fhfin'] ?? '') ?>"
                                           required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Estado -->
                        <?php if ($isEdit): ?>
                            <div class="form-group">
                                <label for="rela_estadoreserva">
                                    <i class="fas fa-tag"></i> Estado
                                </label>
                                <select class="form-select" id="rela_estadoreserva" name="rela_estadoreserva">
                                    <?php foreach ($estados_reserva as $estado): ?>
                                        <option value="<?= $estado['id_estadoreserva'] ?>" 
                                            <?= (isset($reserva['rela_estadoreserva']) && $reserva['rela_estadoreserva'] == $estado['id_estadoreserva']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($estado['estadoreserva_descripcion']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Estado actual de la reserva</small>
                            </div>
                        <?php endif; ?>

                        <!-- Huésped (Solo edición) -->
                        <?php if ($isEdit): ?>
                            <div class="form-group">
                                <label>
                                    <i class="fas fa-user"></i> Huésped
                                </label>
                                <input type="text" class="form-control" 
                                       value="<?= htmlspecialchars(($reserva['persona_nombre'] ?? '') . ' ' . ($reserva['persona_apellido'] ?? '')) ?>" 
                                       readonly disabled>
                                <small class="form-text text-muted">El huésped no puede modificarse</small>
                            </div>
                        <?php endif; ?>

                        <!-- Botones de acción -->
                        <div class="form-group mt-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save"></i> 
                                        <?= $isEdit ? 'Actualizar Reserva' : 'Crear Reserva' ?>
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
                            <li>• Verifique disponibilidad de la cabaña antes de confirmar</li>
                            <li>• La fecha de fin debe ser posterior a la de inicio</li>
                            <li>• La capacidad de personas no debe exceder la de la cabaña</li>
                            <li>• Los datos del huésped se cargarán automáticamente</li>
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
                                        <div class="stat-value"><?= number_format($estadisticas['total_servicios']) ?></div>
                                        <div class="stat-label small text-muted">Servicios</div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-12">
                                    <div class="stat-item">
                                        <div class="stat-value text-secondary">$<?= number_format($estadisticas['monto_pagado'], 0) ?></div>
                                        <div class="stat-label small text-muted">Monto Pagado</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear la reserva.
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
    const form = document.getElementById('formReserva');
    form.reset();
}

// Validación en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formReserva');
    const fechaInicio = document.getElementById('reserva_fhinicio');
    const fechaFin = document.getElementById('reserva_fhfin');

    // Validar fechas
    if (fechaInicio && fechaFin) {
        fechaFin.addEventListener('change', function() {
            if (fechaInicio.value && fechaFin.value) {
                if (new Date(fechaFin.value) <= new Date(fechaInicio.value)) {
                    fechaFin.setCustomValidity('La fecha de fin debe ser posterior a la de inicio');
                    fechaFin.reportValidity();
                } else {
                    fechaFin.setCustomValidity('');
                }
            }
        });
    }

    // Validación del formulario
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }
});
</script>
