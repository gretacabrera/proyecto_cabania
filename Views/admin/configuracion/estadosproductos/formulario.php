<?php
$isEdit = isset($estado_producto) && !empty($estado_producto);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/estadosproductos') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar estado de producto' : 'Nuevo estado de producto' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formEstado" method="POST" novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_estadoproducto" value="<?= $estado_producto['id_estadoproducto'] ?>">
                        <?php endif; ?>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="estadoproducto_descripcion" class="required">
                                <i class="fas fa-tag"></i> Descripción
                            </label>
                            <input type="text" class="form-control" id="estadoproducto_descripcion" name="estadoproducto_descripcion" 
                                   value="<?= htmlspecialchars($estado_producto['estadoproducto_descripcion'] ?? '') ?>"
                                   required maxlength="100" placeholder="Ej: Disponible, Agotado, Descontinuado">
                            <div class="invalid-feedback">Por favor ingrese una descripción válida</div>
                            <small class="form-text text-muted">Máximo 100 caracteres</small>
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
                            <li>• Los estados deben reflejar el ciclo de vida del producto</li>
                            <li>• Evite duplicar estados similares</li>
                            <li>• Ejemplos: Disponible, Agotado, Descontinuado, En Reposición</li>
                        </ul>
                    </div>

                    <?php if ($isEdit): ?>
                        <hr>
                        <div class="info-section">
                            <h6><i class="fas fa-info-circle text-info"></i> Estado Actual</h6>
                            <div class="mt-2">
                                <?php if ($estado_producto['estadoproducto_estado'] == 1): ?>
                                    <span class="badge bg-success px-3 py-2">
                                        <i class="fas fa-check"></i> Activo
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger px-3 py-2">
                                        <i class="fas fa-ban"></i> Inactivo
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para validación -->
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