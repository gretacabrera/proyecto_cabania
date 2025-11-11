<?php
/**
 * Vista: Formulario de Tipo de Servicio
 * Descripción: Formulario para crear/editar tipos de servicios
 */

$isEdit = isset($tiposervicio) && !empty($tiposervicio);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/tiposservicios') ?>" class="btn btn-primary">
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
                    <h5 class="mb-0">
                        <?= $isEdit ? 'Modificar datos del tipo de servicio' : 'Datos del nuevo tipo de servicio' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formTipoServicio" method="POST" 
                          action="<?= $isEdit ? url('/tiposservicios/' . $tiposervicio['id_tiposervicio'] . '/edit') : url('/tiposservicios/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_tiposervicio" value="<?= $tiposervicio['id_tiposervicio'] ?>">
                        <?php endif; ?>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="tiposervicio_descripcion" class="required">
                                <i class="fas fa-align-left"></i> Descripción
                            </label>
                            <input type="text" class="form-control" id="tiposervicio_descripcion" name="tiposervicio_descripcion" 
                                   value="<?= htmlspecialchars($tiposervicio['tiposervicio_descripcion'] ?? '') ?>"
                                   required maxlength="250" placeholder="Ej: Restobar, Eventos recreativos, etc.">
                            <div class="invalid-feedback">Por favor ingrese una descripción</div>
                            <small class="form-text text-muted">
                                Nombre descriptivo del tipo de servicio (máximo 250 caracteres)
                            </small>
                        </div>

                        <!-- Botones de acción -->
                        <div class="form-actions mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                <?= $isEdit ? 'Actualizar' : 'Guardar' ?>
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>
                            <a href="<?= url('/tiposservicios') ?>" class="btn btn-light">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel lateral de información -->
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
                            <li>• Evite repetir tipos de servicios existentes</li>
                            <li>• Considere agrupar servicios similares bajo un mismo tipo</li>
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
                                        <div class="stat-value"><?= number_format($estadisticas['servicios_totales'] ?? 0) ?></div>
                                        <div class="stat-label small text-muted">Total</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value"><?= number_format($estadisticas['uso_mes_actual'] ?? 0) ?></div>
                                        <div class="stat-label small text-muted">Este Mes</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear el tipo de servicio.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formTipoServicio');
    
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });

        // Validación en tiempo real
        const descripcion = document.getElementById('tiposervicio_descripcion');
        if (descripcion) {
            descripcion.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    this.setCustomValidity('La descripción es obligatoria');
                } else if (this.value.length > 250) {
                    this.setCustomValidity('La descripción no puede exceder 250 caracteres');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    }
});
</script>

<style>
.required::after {
    content: " *";
    color: #dc3545;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-actions {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
}

.alert ul {
    font-size: 0.875rem;
}

.info-section {
    margin-bottom: 1rem;
}

.info-section h6 {
    font-weight: 600;
    margin-bottom: 1rem;
}

.stat-item {
    padding: 0.5rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #495057;
}

.stat-label {
    margin-top: 0.25rem;
}
</style>
