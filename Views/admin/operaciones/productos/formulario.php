<?php
/**
 * Vista: Formulario de Producto
 * Descripción: Formulario para crear/editar productos
 * Siguiendo el patrón exacto del módulo de Cabañas
 */

$isEdit = isset($producto) && !empty($producto);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/productos') ?>" class="btn btn-primary">
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
                                           required maxlength="100" 
                                           placeholder="Nombre completo del producto">
                                    <div class="invalid-feedback">
                                        Por favor ingrese el nombre del producto.
                                    </div>
                                </div>
                            </div>

                            <!-- Stock -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="producto_stock" class="required">
                                        <i class="fas fa-boxes"></i> Stock Inicial
                                    </label>
                                    <input type="number" class="form-control" id="producto_stock" name="producto_stock" 
                                           value="<?= $producto['producto_stock'] ?? '0' ?>" 
                                           required min="0" max="9999" 
                                           placeholder="0">
                                    <div class="invalid-feedback">
                                        Ingrese la cantidad en stock (0 o mayor).
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Precio -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="producto_precio" class="required">
                                        <i class="fas fa-dollar-sign"></i> Precio
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="producto_precio" name="producto_precio" 
                                               value="<?= $producto['producto_precio'] ?? '' ?>" 
                                               required min="0.01" max="999999.99" step="0.01" 
                                               placeholder="0.00">
                                        <div class="invalid-feedback">
                                            Ingrese un precio válido mayor a $0.00.
                                        </div>
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
                                    <div class="invalid-feedback">
                                        Por favor seleccione una categoría.
                                    </div>
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
                                    <div class="invalid-feedback">
                                        Por favor seleccione una marca.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="producto_descripcion" class="required">
                                        <i class="fas fa-align-left"></i> Descripción
                                    </label>
                                    <textarea class="form-control" id="producto_descripcion" name="producto_descripcion" 
                                              rows="4" required maxlength="500" 
                                              placeholder="Descripción detallada del producto..."><?= htmlspecialchars($producto['producto_descripcion'] ?? '') ?></textarea>
                                    <small class="form-text text-muted">Máximo 500 caracteres.</small>
                                    <div class="invalid-feedback">
                                        Por favor ingrese una descripción del producto.
                                    </div>
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

                        <!-- Botones de acción -->
                        <div class="form-actions mt-4">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> <?= $isEdit ? 'Actualizar' : 'Guardar' ?> Producto
                                </button>
                                <button type="button" class="btn btn-info" onclick="limpiarFormulario()">
                                    <i class="fas fa-broom"></i> Limpiar
                                </button>
                                <a href="<?= url('/productos') ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <!-- Imagen del producto -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-image"></i> Imagen del Producto
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="image-preview mb-3">
                        <?php if ($isEdit && !empty($producto['producto_foto'])): ?>
                            <img id="imagePreview" src="<?= url('/imagenes/productos/' . $producto['producto_foto']) ?>" 
                                 alt="Vista previa" class="img-fluid rounded shadow" 
                                 style="max-width: 100%; max-height: 250px;">
                        <?php else: ?>
                            <div id="imagePreview" class="bg-light border border-dashed rounded p-4" style="min-height: 200px;">
                                <i class="fas fa-image fa-3x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Vista previa de imagen</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="producto_foto" class="btn btn-outline-primary btn-sm mb-0">
                            <i class="fas fa-upload"></i> Seleccionar Imagen
                        </label>
                        <input type="file" id="producto_foto" name="producto_foto" 
                               class="form-control-file d-none" 
                               accept="image/jpeg,image/jpg,image/png,image/gif">
                        <small class="form-text text-muted d-block mt-2">
                            Formatos: JPG, PNG, GIF<br>
                            Tamaño máximo: 2MB
                        </small>
                    </div>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-lightbulb"></i> Consejos
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Usa nombres descriptivos para facilitar las búsquedas
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            La descripción ayuda a los usuarios a entender el producto
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Las imágenes mejoran la presentación del catálogo
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success"></i>
                            Mantén el stock actualizado para evitar problemas
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Vista previa de imagen
    const fileInput = document.getElementById('producto_foto');
    const imagePreview = document.getElementById('imagePreview');
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.innerHTML = `<img src="${e.target.result}" alt="Vista previa" class="img-fluid rounded shadow" style="max-width: 100%; max-height: 250px;">`;
            };
            reader.readAsDataURL(file);
        }
    });

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
    
    // Limpiar vista previa de imagen
    const imagePreview = document.getElementById('imagePreview');
    imagePreview.innerHTML = `
        <div class="bg-light border border-dashed rounded p-4" style="min-height: 200px;">
            <i class="fas fa-image fa-3x text-muted mb-2"></i>
            <p class="text-muted mb-0">Vista previa de imagen</p>
        </div>
    `;
}
</script>

<style>
.required::after {
    content: " *";
    color: #dc3545;
}

.form-actions {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
}

.image-preview {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.5rem;
}

.card-title {
    font-size: 0.875rem;
    font-weight: 600;
}
</style>