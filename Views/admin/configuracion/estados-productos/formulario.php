<?php
/**
 * Vista: Formulario de Estado de Producto
 * Descripción: Formulario para crear/editar estados de productos
 */

$isEdit = isset($estado) && !empty($estado);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/estados-productos') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos del estado de producto' : 'Datos del nuevo estado de producto' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formEstadoProducto" method="POST" 
                          action="<?= $isEdit ? url('/estados-productos/' . $estado['id_estadoproducto'] . '/edit') : url('/estados-productos/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_estadoproducto" value="<?= $estado['id_estadoproducto'] ?>">
                        <?php endif; ?>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="estadoproducto_descripcion" class="required">
                                <i class="fas fa-tag"></i> Descripción
                            </label>
                            <input type="text" class="form-control" id="estadoproducto_descripcion" name="estadoproducto_descripcion" 
                                   value="<?= htmlspecialchars($estado['estadoproducto_descripcion'] ?? '') ?>"
                                   required maxlength="45" placeholder="Ej: Disponible, En Stock Mínimo, Sin Stock">
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">
                                Nombre descriptivo del estado de producto (máximo 45 caracteres)
                            </small>
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
                            <li>• Use nombres claros y descriptivos</li>
                            <li>• Los estados ayudan a gestionar el inventario de productos</li>
                            <li>• Ejemplos: "Disponible", "Stock Mínimo", "Sin Stock"</li>
                            <li>• Evite duplicar estados existentes</li>
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
                                        <div class="stat-value">
                                            <?= isset($productos_count) ? $productos_count : 0 ?>
                                        </div>
                                        <div class="stat-label small text-muted">Productos con este estado</div>
                                    </div>
                                </div>
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

<script>
function limpiarFormulario() {
    document.getElementById('formEstadoProducto').reset();
}
</script>
