<?php
$isEdit = isset($tipo_contacto);
$pageTitle = $isEdit ? 'Editar Tipo de Contacto' : 'Nuevo Tipo de Contacto';
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/tiposcontactos') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna principal: formulario (8 columnas) -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit"></i>
                        <?= $isEdit ? 'Modificar datos del tipo de contacto' : 'Datos del nuevo tipo de contacto' ?>
                    </h5>
                </div>

                <div class="card-body">
                    <form id="formTipoContacto" method="POST" 
                          action="<?= url($isEdit ? '/tiposcontactos/' . $tipo_contacto['id_tipocontacto'] . '/edit' : '/tiposcontactos/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_tipocontacto" value="<?= $tipo_contacto['id_tipocontacto'] ?>">
                        <?php endif; ?>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="tipocontacto_descripcion" class="required">
                                <i class="fas fa-tag"></i> Descripción
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="tipocontacto_descripcion" 
                                   name="tipocontacto_descripcion" 
                                   value="<?= htmlspecialchars($tipo_contacto['tipocontacto_descripcion'] ?? '') ?>"
                                   required 
                                   maxlength="45"
                                   placeholder="Ej: Celular, Email, WhatsApp">
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">Máximo 45 caracteres</small>
                        </div>

                        <!-- Estado -->
                        <div class="form-group">
                            <label for="tipocontacto_estado" class="required">
                                <i class="fas fa-toggle-on"></i> Estado
                            </label>
                            <select class="form-select" 
                                    id="tipocontacto_estado" 
                                    name="tipocontacto_estado" 
                                    required>
                                <option value="1" <?= ($tipo_contacto['tipocontacto_estado'] ?? 1) == 1 ? 'selected' : '' ?>>Activo</option>
                                <option value="0" <?= ($tipo_contacto['tipocontacto_estado'] ?? 1) == 0 ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">Solo los tipos activos estarán disponibles para su uso</small>
                        </div>

                        <!-- Botones -->
                        <div class="form-group mt-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save"></i>
                                        <?= $isEdit ? 'Actualizar Tipo de Contacto' : 'Crear Tipo de Contacto' ?>
                                    </button>
                                    <?php if (!$isEdit): ?>
                                        <button type="button" class="btn btn-outline-secondary btn-lg ml-2" 
                                                onclick="limpiarFormulario()">
                                            <i class="fas fa-eraser"></i> Limpiar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Columna lateral: información (4 columnas) -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Consejos -->
                    <div class="info-section">
                        <h6><i class="fas fa-lightbulb text-warning"></i> Consejos</h6>
                        <ul class="list-unstyled small text-muted">
                            <li>• Use nombres descriptivos y claros</li>
                            <li>• Evite abreviaturas difíciles de entender</li>
                            <li>• Considere los canales de contacto más comunes</li>
                            <li>• Mantenga consistencia en la nomenclatura</li>
                        </ul>
                    </div>

                    <hr>

                    <!-- Estadísticas (solo en edición) -->
                    <div class="info-section">
                        <h6><i class="fas fa-chart-line text-info"></i> Estadísticas</h6>
                        <br>
                        <?php if ($isEdit): ?>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="metric-box">
                                        <div class="metric-value text-primary">
                                            <?= number_format($estadisticas['total_contactos'] ?? 0) ?>
                                        </div>
                                        <div class="metric-label">Contactos Registrados</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="metric-box">
                                        <div class="metric-value text-success">
                                            <?= number_format($estadisticas['total_personas'] ?? 0) ?>
                                        </div>
                                        <div class="metric-label">Usuarios con Contactos</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear el tipo de contacto.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación del formulario
(function() {
    'use strict';
    const form = document.getElementById('formTipoContacto');
    
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    }
})();

// Función para limpiar el formulario
function limpiarFormulario() {
    const form = document.getElementById('formTipoContacto');
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
    }
}
</script>
