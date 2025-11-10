<?php
$isEdit = isset($estado) && !empty($estado);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/estadospersonas') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos del estado de persona' : 'Datos del nuevo estado de persona' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formEstado" method="POST" 
                          action="<?= $isEdit ? url('/estadospersonas/' . $estado['id_estadopersona'] . '/edit') : url('/estadospersonas/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_estadopersona" value="<?= $estado['id_estadopersona'] ?>">
                        <?php endif; ?>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="estadopersona_descripcion" class="required">
                                <i class="fas fa-tag"></i> Descripción
                            </label>
                            <input type="text" class="form-control" id="estadopersona_descripcion" name="estadopersona_descripcion" 
                                   value="<?= htmlspecialchars($estado['estadopersona_descripcion'] ?? '') ?>"
                                   required maxlength="45" placeholder="Ej: activo, fallecido, baja">
                            <div class="invalid-feedback">Por favor ingrese una descripción válida</div>
                            <small class="form-text text-muted">Máximo 45 caracteres</small>
                        </div>

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
                            <li>• Los estados deben ser fáciles de identificar</li>
                            <li>• Evite duplicar estados similares</li>
                            <li>• Mantenga solo estados activos en uso</li>
                        </ul>
                    </div>

                    <hr>

                    <div class="info-section">
                        <h6><i class="fas fa-chart-line text-info"></i> Estadísticas</h6>
                        <br>
                        <?php if ($isEdit): ?>
                            <div class="row text-center">
                                <div class="col-12">
                                    <div class="stat-item">
                                        <div class="stat-value text-primary">
                                            <i class="fas fa-user"></i> 
                                            <?= $estadisticas['personas_asociadas'] ?? 0 ?>
                                        </div>
                                        <div class="stat-label small text-muted">Personas asociadas</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Este estado está asignado a <?= $estadisticas['personas_asociadas'] ?? 0 ?> 
                                    de <?= $estadisticas['total_personas_sistema'] ?? 0 ?> personas en el sistema.
                                </small>
                            </div>
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

<!-- JavaScript para validación y funcionalidades -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formEstado');
    
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        });
    }
});

function limpiarFormulario() {
    const form = document.getElementById('formEstado');
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
    }
}
</script>
