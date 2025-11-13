<?php
/**
 * Vista: Formulario de Inventario
 * Descripción: Formulario para crear/editar inventario
 * Autor: Sistema MVC
 * Fecha: <?php echo date('Y-m-d'); ?>
 */

$isEdit = isset($inventario) && !empty($inventario);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/inventarios') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos del inventario' : 'Datos del nuevo inventario' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formInventario" method="POST" 
                          action="<?= $isEdit ? url('/inventarios/' . $inventario['id_inventario'] . '/edit') : url('/inventarios/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_inventario" value="<?= $inventario['id_inventario'] ?>">
                        <?php endif; ?>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="inventario_descripcion" class="required">
                                <i class="fas fa-align-left"></i> Descripción
                            </label>
                            <textarea class="form-control" id="inventario_descripcion" name="inventario_descripcion" 
                                      rows="4" required maxlength="250" 
                                      placeholder="Describe el artículo de inventario..."><?= htmlspecialchars($inventario['inventario_descripcion'] ?? '') ?></textarea>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">
                                <span id="contadorDescripcion">0</span> / 250 caracteres
                            </small>
                        </div>

                        <!-- Stock -->
                        <div class="form-group">
                            <label for="inventario_stock" class="required">
                                <i class="fas fa-boxes"></i> Stock
                            </label>
                            <input type="number" class="form-control" id="inventario_stock" name="inventario_stock" 
                                   value="<?= $inventario['inventario_stock'] ?? '' ?>"
                                   required min="0" placeholder="Cantidad de unidades">
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">Cantidad actual disponible en inventario</small>
                        </div>

                        <!-- Botones de acción -->
                        <div class="form-group mt-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save"></i> 
                                        <?= $isEdit ? 'Actualizar Inventario' : 'Crear Inventario' ?>
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
                            <li>• Sea específico en la descripción del artículo</li>
                            <li>• Mantenga el stock actualizado regularmente</li>
                            <li>• Un stock de 0 indica que el artículo está agotado</li>
                            <li>• Revise periódicamente los niveles de inventario</li>
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
                                        <div class="stat-value">0</div>
                                        <div class="stat-label small text-muted">Cabañas asignadas</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value">0</div>
                                        <div class="stat-label small text-muted">Revisiones del mes</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear el inventario.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Contador de caracteres para descripción
document.addEventListener('DOMContentLoaded', function() {
    const descripcionTextarea = document.getElementById('inventario_descripcion');
    const contadorDescripcion = document.getElementById('contadorDescripcion');
    
    if (descripcionTextarea && contadorDescripcion) {
        // Mostrar contador inicial
        contadorDescripcion.textContent = descripcionTextarea.value.length;
        
        // Actualizar contador al escribir
        descripcionTextarea.addEventListener('input', function() {
            contadorDescripcion.textContent = this.value.length;
        });
    }
});

// Función para limpiar el formulario
function limpiarFormulario() {
    if (confirm('¿Está seguro que desea limpiar el formulario?')) {
        document.getElementById('formInventario').reset();
        document.getElementById('contadorDescripcion').textContent = '0';
    }
}

// Validación del formulario antes de enviar
document.getElementById('formInventario').addEventListener('submit', function(e) {
    const descripcion = document.getElementById('inventario_descripcion').value.trim();
    const stock = document.getElementById('inventario_stock').value;
    
    if (descripcion === '') {
        e.preventDefault();
        alert('Por favor, ingrese una descripción para el inventario');
        document.getElementById('inventario_descripcion').focus();
        return false;
    }
    
    if (stock === '' || parseInt(stock) < 0) {
        e.preventDefault();
        alert('Por favor, ingrese un stock válido (mayor o igual a 0)');
        document.getElementById('inventario_stock').focus();
        return false;
    }
    
    return true;
});
</script>
