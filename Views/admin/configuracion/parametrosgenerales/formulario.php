<?php
/**
 * Vista: Formulario de Parámetro General
 * Descripción: Formulario para crear/editar parámetros de configuración del sistema
 */

$isEdit = isset($parametro) && !empty($parametro);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/parametrosgenerales') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos del parámetro' : 'Datos del nuevo parámetro' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formParametro" method="POST" 
                          action="<?= $isEdit ? url('/parametrosgenerales/' . $parametro['id_parametrogeneral'] . '/edit') : url('/parametrosgenerales/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_parametrogeneral" value="<?= $parametro['id_parametrogeneral'] ?>">
                        <?php endif; ?>

                        <div class="row">
                            <!-- Código -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="parametrogeneral_codigo" class="required">
                                        <i class="fas fa-barcode"></i> Código
                                    </label>
                                    <input type="text" class="form-control" id="parametrogeneral_codigo" name="parametrogeneral_codigo" 
                                           value="<?= htmlspecialchars($parametro['parametrogeneral_codigo'] ?? '') ?>"
                                           required maxlength="5" placeholder="Ej: PIIVA" style="text-transform: uppercase;">
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Código único de 5 caracteres (se convierte a mayúsculas)</small>
                                </div>
                            </div>

                            <!-- Estado (solo en edición) -->
                            <?php if ($isEdit): ?>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="parametrogeneral_estado">
                                        <i class="fas fa-toggle-on"></i> Estado
                                    </label>
                                    <select class="form-select" id="parametrogeneral_estado" name="parametrogeneral_estado">
                                        <option value="1" <?= ($parametro['parametrogeneral_estado'] ?? 1) == 1 ? 'selected' : '' ?>>Activo</option>
                                        <option value="0" <?= ($parametro['parametrogeneral_estado'] ?? 1) == 0 ? 'selected' : '' ?>>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Descripción / Valor -->
                        <div class="form-group mb-3">
                            <label for="parametrogeneral_descripcion" class="required">
                                <i class="fas fa-align-left"></i> Descripción / Valor
                            </label>
                            <textarea class="form-control" id="parametrogeneral_descripcion" name="parametrogeneral_descripcion" 
                                      rows="3" required maxlength="250" 
                                      placeholder="Ingrese la descripción o valor del parámetro..."><?= htmlspecialchars($parametro['parametrogeneral_descripcion'] ?? '') ?></textarea>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">
                                <span id="contadorDescripcion">0</span> / 250 caracteres
                            </small>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $isEdit ? 'Actualizar' : 'Guardar' ?>
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>
                            <a href="<?= url('/parametrosgenerales') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
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
                            <li>• El código debe ser único y de 5 caracteres máximo</li>
                            <li>• Use códigos descriptivos que identifiquen el parámetro</li>
                            <li>• Los códigos se guardan siempre en mayúsculas</li>
                            <li>• La descripción puede contener el valor numérico o texto</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formParametro');
    const codigoInput = document.getElementById('parametrogeneral_codigo');
    const descripcionTextarea = document.getElementById('parametrogeneral_descripcion');
    const contadorDescripcion = document.getElementById('contadorDescripcion');

    // Contador de caracteres para descripción
    if (descripcionTextarea && contadorDescripcion) {
        function actualizarContador() {
            const caracteresActuales = descripcionTextarea.value.length;
            contadorDescripcion.textContent = caracteresActuales;
            
            if (caracteresActuales > 200) {
                contadorDescripcion.classList.add('text-danger');
            } else {
                contadorDescripcion.classList.remove('text-danger');
            }
        }
        
        actualizarContador();
        descripcionTextarea.addEventListener('input', actualizarContador);
    }

    // Convertir código a mayúsculas automáticamente
    if (codigoInput) {
        codigoInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

    // Validación del formulario
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Limpiar validación al resetear
    form.addEventListener('reset', function() {
        form.classList.remove('was-validated');
        if (contadorDescripcion) {
            contadorDescripcion.textContent = '0';
        }
    });
});
</script>

<style>
.required::after {
    content: " *";
    color: red;
}

.form-group {
    margin-bottom: 1rem;
}

.d-flex.gap-2 > * {
    margin-right: 0.5rem;
}

.d-flex.gap-2 > *:last-child {
    margin-right: 0;
}
</style>
