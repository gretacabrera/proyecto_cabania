<?php
/**
 * Vista: Formulario de Producto
 * Descripción: Formulario para crear/editar productos
 * Autor: Sistema MVC
 * Fecha: <?php echo date('Y-m-d'); ?>
 */

$isEdit = isset($producto) && !empty($producto);
?>

<div class="container-fluid">
    <!-- Acciones principales -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= url('/productos') ?>" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Formulario principal -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit"></i> 
                        <?= $isEdit ? 'Modificar datos del producto' : 'Datos del nuevo producto' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formProducto" method="POST" 
                          action="<?= $isEdit ? url('/productos/' . $producto['id_producto'] . '/edit') : url('/productos/create') ?>" 
                          enctype="multipart/form-data" novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
                            <input type="hidden" name="producto_foto_actual" value="<?= $producto['producto_foto'] ?? '' ?>">
                        <?php endif; ?>

                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="producto_nombre" class="required">
                                        <i class="fas fa-tag"></i> Nombre del Producto
                                    </label>
                                    <input type="text" class="form-control" id="producto_nombre" name="producto_nombre" 
                                           value="<?= htmlspecialchars($producto['producto_nombre'] ?? '') ?>"
                                           required maxlength="100" placeholder="Nombre completo del producto">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Stock -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="producto_stock" class="required">
                                        <i class="fas fa-boxes"></i> Stock
                                    </label>
                                    <input type="number" class="form-control" id="producto_stock" name="producto_stock" 
                                           value="<?= $producto['producto_stock'] ?? '0' ?>"
                                           required min="0" max="9999" placeholder="0">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="producto_descripcion" class="required">
                                <i class="fas fa-align-left"></i> Descripción
                            </label>
                            <textarea class="form-control" id="producto_descripcion" name="producto_descripcion" 
                                      rows="4" required maxlength="500" 
                                      placeholder="Describe las características principales del producto..."><?= htmlspecialchars($producto['producto_descripcion'] ?? '') ?></textarea>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">
                                <span id="contadorDescripcion">0</span> / 500 caracteres
                            </small>
                        </div>

                        <div class="row">
                            <!-- Precio -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="producto_precio" class="required">
                                        <i class="fas fa-dollar-sign"></i> Precio
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" class="form-control" id="producto_precio" name="producto_precio" 
                                               value="<?= $producto['producto_precio'] ?? '' ?>"
                                               required min="0.01" max="999999.99" step="0.01" placeholder="0.00">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Categoría -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="rela_categoria" class="required">
                                        <i class="fas fa-list"></i> Categoría
                                    </label>
                                    <select class="form-control" id="rela_categoria" name="rela_categoria" required>
                                        <option value="">Seleccionar categoría</option>
                                        <?php foreach ($categorias as $categoria): ?>
                                            <option value="<?= $categoria['id_categoria'] ?>" 
                                                    <?= (isset($producto) && $producto['rela_categoria'] == $categoria['id_categoria']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($categoria['categoria_descripcion']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Marca -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="rela_marca" class="required">
                                        <i class="fas fa-trademark"></i> Marca
                                    </label>
                                    <select class="form-control" id="rela_marca" name="rela_marca" required>
                                        <option value="">Seleccionar marca</option>
                                        <?php foreach ($marcas as $marca): ?>
                                            <option value="<?= $marca['id_marca'] ?>" 
                                                    <?= (isset($producto) && $producto['rela_marca'] == $marca['id_marca']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($marca['marca_descripcion']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Estado (solo para edición) -->
                        <?php if ($isEdit): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="rela_estadoproducto">
                                            <i class="fas fa-info-circle"></i> Estado
                                        </label>
                                        <select class="form-control" id="rela_estadoproducto" name="rela_estadoproducto">
                                            <?php foreach ($estadosProducto as $estado): ?>
                                                <option value="<?= $estado['id_estadoproducto'] ?>" 
                                                        <?= ($producto['rela_estadoproducto'] == $estado['id_estadoproducto']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($estado['estadoproducto_descripcion']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Imagen del producto -->
                        <div class="form-group">
                            <label for="producto_foto">
                                <i class="fas fa-camera"></i> Imagen del Producto
                            </label>
                            
                            <?php if ($isEdit && !empty($producto['producto_foto'])): ?>
                            <div class="current-image mb-3">
                                <div class="card">
                                    <div class="card-header py-2">
                                        <small class="text-muted">Imagen actual</small>
                                    </div>
                                    <div class="card-body text-center py-2">
                                        <img src="<?= url('/imagenes/productos/' . $producto['producto_foto']) ?>" 
                                             alt="<?= htmlspecialchars($producto['producto_nombre']) ?>" 
                                             class="img-fluid rounded" style="max-height: 200px;">
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="producto_foto" name="producto_foto" 
                                       accept="image/jpeg,image/jpg,image/png,image/gif">
                                <label class="custom-file-label" for="producto_foto">
                                    <?= $isEdit ? 'Cambiar imagen (opcional)' : 'Seleccionar imagen' ?>
                                </label>
                                <div class="invalid-feedback"></div>
                            </div>
                            <small class="form-text text-muted">
                                Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB
                            </small>
                            
                            <!-- Preview de nueva imagen -->
                            <div id="previewImagen" class="mt-3" style="display: none;">
                                <div class="card">
                                    <div class="card-header py-2">
                                        <small class="text-muted">Vista previa</small>
                                    </div>
                                    <div class="card-body text-center py-2">
                                        <img id="imgPreview" src="" alt="Preview" 
                                             class="img-fluid rounded" style="max-height: 200px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="form-group mt-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save"></i> 
                                        <?= $isEdit ? 'Actualizar Producto' : 'Crear Producto' ?>
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
                            <li>• Use nombres descriptivos para facilitar las búsquedas</li>
                            <li>• La descripción debe ayudar a entender el producto</li>
                            <li>• Las imágenes mejoran la presentación del catálogo</li>
                            <li>• Mantén el stock actualizado para evitar problemas</li>
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
                                        <div class="stat-value">
                                            <?= $estadisticas['consumos']['total_consumos'] ?? '0' ?>
                                        </div>
                                        <div class="stat-label small text-muted">Ventas</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value">
                                            $<?= number_format($estadisticas['consumos']['ingresos_total'] ?? 0, 2) ?>
                                        </div>
                                        <div class="stat-label small text-muted">Ingresos</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear el producto.
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
    // Manejo del archivo de imagen
    const fileInput = document.getElementById('producto_foto');
    const fileLabel = document.querySelector('.custom-file-label');
    const previewContainer = document.getElementById('previewImagen');
    const imgPreview = document.getElementById('imgPreview');
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Actualizar texto del label
            fileLabel.textContent = file.name;
            
            // Mostrar vista previa
            const reader = new FileReader();
            reader.onload = function(e) {
                imgPreview.src = e.target.result;
                previewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            fileLabel.textContent = '<?= $isEdit ? 'Cambiar imagen (opcional)' : 'Seleccionar imagen' ?>';
            previewContainer.style.display = 'none';
        }
    });

    // Contador de caracteres para descripción
    const descripcionTextarea = document.getElementById('producto_descripcion');
    const contadorDescripcion = document.getElementById('contadorDescripcion');
    
    function actualizarContador() {
        const longitud = descripcionTextarea.value.length;
        contadorDescripcion.textContent = longitud;
        contadorDescripcion.className = longitud > 450 ? 'text-warning' : 'text-muted';
    }
    
    descripcionTextarea.addEventListener('input', actualizarContador);
    actualizarContador(); // Ejecutar al cargar

    // Validación del formulario
    const form = document.getElementById('formProducto');
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});

// Función para limpiar formulario
function limpiarFormulario() {
    const form = document.getElementById('formProducto');
    form.reset();
    form.classList.remove('was-validated');
    
    // Resetear label del archivo
    const fileLabel = document.querySelector('.custom-file-label');
    fileLabel.textContent = 'Seleccionar imagen';
    
    // Ocultar vista previa
    document.getElementById('previewImagen').style.display = 'none';
    
    // Resetear contador
    document.getElementById('contadorDescripcion').textContent = '0';
}
</script>

<style>
.required::after {
    content: " *";
    color: #dc3545;
}

.info-section h6 {
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.stat-item .stat-value {
    font-size: 1.25rem;
    font-weight: 600;
    color: #495057;
}

.card-title {
    font-size: 0.875rem;
    font-weight: 600;
}
</style>