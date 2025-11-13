<?php
/**
 * Vista: Formulario de Cabaña
 * Descripción: Formulario para crear/editar cabañas
 * Autor: Sistema MVC
 * Fecha: <?php echo date('Y-m-d'); ?>
 */

$isEdit = isset($cabania) && !empty($cabania);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/cabanias') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos de la cabaña' : 'Datos de la nueva cabaña' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formCabania" method="POST" 
                          action="<?= $isEdit ? url('/cabanias/' . $cabania['id_cabania'] . '/edit') : url('/cabanias/create') ?>" 
                          enctype="multipart/form-data" novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_cabania" value="<?= $cabania['id_cabania'] ?>">
                            <input type="hidden" name="cabania_foto_actual" value="<?= $cabania['cabania_foto'] ?? '' ?>">
                        <?php endif; ?>

                        <div class="row">
                            <!-- Código -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cabania_codigo" class="required">
                                        <i class="fas fa-barcode"></i> Código
                                    </label>
                                    <input type="text" class="form-control" id="cabania_codigo" name="cabania_codigo" 
                                           value="<?= htmlspecialchars($cabania['cabania_codigo'] ?? '') ?>"
                                           required maxlength="20" placeholder="Ej: CAB-001">
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Código único identificador de la cabaña</small>
                                </div>
                            </div>

                            <!-- Nombre -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cabania_nombre" class="required">
                                        <i class="fas fa-tag"></i> Nombre
                                    </label>
                                    <input type="text" class="form-control" id="cabania_nombre" name="cabania_nombre" 
                                           value="<?= htmlspecialchars($cabania['cabania_nombre'] ?? '') ?>"
                                           required maxlength="100" placeholder="Nombre de la cabaña">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="cabania_descripcion" class="required">
                                <i class="fas fa-align-left"></i> Descripción
                            </label>
                            <textarea class="form-control" id="cabania_descripcion" name="cabania_descripcion" 
                                      rows="4" required maxlength="500" 
                                      placeholder="Describe las características principales de la cabaña..."><?= htmlspecialchars($cabania['cabania_descripcion'] ?? '') ?></textarea>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">
                                <span id="contadorDescripcion">0</span> / 500 caracteres
                            </small>
                        </div>

                        <div class="row">
                            <!-- Capacidad -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cabania_capacidad" class="required">
                                        <i class="fas fa-users"></i> Capacidad
                                    </label>
                                    <input type="number" class="form-control" id="cabania_capacidad" name="cabania_capacidad" 
                                           value="<?= $cabania['cabania_capacidad'] ?? '' ?>"
                                           required min="1" max="20" placeholder="Personas">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Precio -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cabania_precio" class="required">
                                        <i class="fas fa-dollar-sign"></i> Precio por noche
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" class="form-control" id="cabania_precio" name="cabania_precio" 
                                               value="<?= $cabania['cabania_precio'] ?? '' ?>"
                                               required min="0" step="0.01" placeholder="0.00">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Baños -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cabania_cantidadbanios" class="required">
                                        <i class="fas fa-bath"></i> Baños
                                    </label>
                                    <input type="number" class="form-control" id="cabania_cantidadbanios" name="cabania_cantidadbanios" 
                                           value="<?php echo $cabania['cabania_cantidadbanios'] ?? ''; ?>"
                                           required min="1" max="10" placeholder="Cantidad">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Habitaciones -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cabania_cantidadhabitaciones" class="required">
                                        <i class="fas fa-bed"></i> Habitaciones
                                    </label>
                                    <input type="number" class="form-control" id="cabania_cantidadhabitaciones" name="cabania_cantidadhabitaciones" 
                                           value="<?php echo $cabania['cabania_cantidadhabitaciones'] ?? ''; ?>"
                                           required min="1" max="10" placeholder="Cantidad">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Ubicación -->
                        <div class="form-group">
                            <label for="cabania_ubicacion" class="required">
                                <i class="fas fa-map-marker-alt"></i> Ubicación
                            </label>
                            <input type="text" class="form-control" id="cabania_ubicacion" name="cabania_ubicacion" 
                                   value="<?php echo htmlspecialchars($cabania['cabania_ubicacion'] ?? ''); ?>"
                                   required maxlength="200" 
                                   placeholder="Ej: Sector A - Junto al lago">
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Foto -->
                        <div class="form-group">
                            <label for="cabania_foto">
                                <i class="fas fa-camera"></i> Foto
                            </label>
                            <?php if ($isEdit && !empty($cabania['cabania_foto'])): ?>
                                <div class="current-photo mb-2">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header py-2">
                                                    <small class="text-muted">Foto actual</small>
                                                </div>
                                                <div class="card-body text-center py-2">
                                                    <img src="<?= asset('imagenes/cabanias/' . $cabania['cabania_foto']) ?>" 
                                                         alt="Foto actual de <?= htmlspecialchars($cabania['cabania_nombre']) ?>"
                                                         class="img-fluid rounded" style="max-height: 200px;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="cabania_foto" name="cabania_foto" 
                                       accept="image/jpeg,image/jpg,image/png,image/gif">
                                <label class="custom-file-label" for="cabania_foto">
                                    <?= $isEdit ? 'Cambiar foto (opcional)' : 'Seleccionar foto' ?>
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

                        <!-- Inventario asignado -->
                        <hr class="my-4">

                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-box"></i> Inventario Asignado
                        </h6>

                        <div class="form-group">
                            <div class="asignaciones-container">
                                <?php if (isset($inventarios) && !empty($inventarios)): ?>
                                    <?php foreach ($inventarios as $inventario): ?>
                                        <?php
                                        $idInventario = $inventario['id_inventario'];
                                        $estaSeleccionado = false;
                                        if ($isEdit && isset($inventarioCabania[$idInventario]) && $inventarioCabania[$idInventario] == 1) {
                                            $estaSeleccionado = true;
                                        }
                                        $claseEstado = $estaSeleccionado ? 'seleccionado-inventario' : 'no-seleccionado';
                                        ?>
                                        <span class="badge asignacion-badge <?= $claseEstado ?>" 
                                              data-inventario-id="<?= $idInventario ?>" 
                                              data-tipo="inventario">
                                            <?= htmlspecialchars($inventario['inventario_descripcion']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                    <!-- Inputs hidden para enviar los inventarios seleccionados -->
                                    <div id="inventarios-hidden-container">
                                        <?php if ($isEdit): ?>
                                            <?php foreach ($inventarios as $inventario): ?>
                                                <?php if (isset($inventarioCabania[$inventario['id_inventario']]) && $inventarioCabania[$inventario['id_inventario']] == 1): ?>
                                                    <input type="hidden" name="inventarios[]" value="<?= $inventario['id_inventario'] ?>" id="hidden-inventario-<?= $inventario['id_inventario'] ?>">
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted small">No hay inventario disponible para asignar</p>
                                <?php endif; ?>
                            </div>
                            <small class="form-text text-muted d-block mt-3">
                                <i class="fas fa-info-circle me-1"></i>
                                Haga clic en los elementos de inventario que desea asignar a esta cabaña
                            </small>
                        </div>

                        <hr class="my-4">

                        <!-- Botones de acción -->
                        <div class="form-group mt-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save"></i> 
                                        <?= $isEdit ? 'Actualizar Cabaña' : 'Crear Cabaña' ?>
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
                            <li>• Use códigos únicos e informativos</li>
                            <li>• La descripción debe ser atractiva y precisa</li>
                            <li>• Las fotos mejoran la experiencia del cliente</li>
                            <li>• Mantenga los precios actualizados</li>
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
                                        <div class="stat-value"><?= number_format($estadisticas['reservas_activas']) ?></div>
                                        <div class="stat-label small text-muted">Reservas</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value">$<?= number_format($estadisticas['ingresos_mes'], 0) ?></div>
                                        <div class="stat-label small text-muted">Ingresos</div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-12">
                                    <div class="stat-item">
                                        <div class="stat-value text-secondary"><?= number_format($estadisticas['items_inventario']) ?></div>
                                        <div class="stat-label small text-muted">Items de Inventario</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear la cabaña.
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
    const form = document.getElementById('formCabania');
    form.reset();
    
    // Resetear badges de inventario
    const badges = document.querySelectorAll('.asignacion-badge[data-tipo="inventario"]');
    badges.forEach(function(badge) {
        badge.classList.remove('seleccionado-inventario');
        badge.classList.add('no-seleccionado');
    });
    
    // Limpiar inputs hidden
    const hiddenContainer = document.getElementById('inventarios-hidden-container');
    if (hiddenContainer) {
        hiddenContainer.innerHTML = '';
    }
    
    // Limpiar preview de imagen
    const previewDiv = document.getElementById('previewImagen');
    if (previewDiv) {
        previewDiv.style.display = 'none';
    }
}

// Contador de caracteres para descripción
document.addEventListener('DOMContentLoaded', function() {
    const descripcion = document.getElementById('cabania_descripcion');
    const contador = document.getElementById('contadorDescripcion');
    
    if (descripcion && contador) {
        // Actualizar contador inicial
        contador.textContent = descripcion.value.length;
        
        // Actualizar en tiempo real
        descripcion.addEventListener('input', function() {
            contador.textContent = this.value.length;
        });
    }
    
    // Preview de imagen
    const fotoInput = document.getElementById('cabania_foto');
    if (fotoInput) {
        fotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imgPreview');
                    const previewDiv = document.getElementById('previewImagen');
                    if (preview && previewDiv) {
                        preview.src = e.target.result;
                        previewDiv.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
                
                // Actualizar label del custom-file
                const label = fotoInput.nextElementSibling;
                if (label) {
                    label.textContent = file.name;
                }
            }
        });
    }

    // Manejo de badges de inventario
    const badges = document.querySelectorAll('.asignacion-badge[data-tipo="inventario"]');
    const hiddenContainer = document.getElementById('inventarios-hidden-container');

    if (badges.length > 0 && hiddenContainer) {
        badges.forEach(function(badge) {
            badge.addEventListener('click', function(e) {
                // Prevenir cualquier comportamiento de formulario
                e.preventDefault();
                e.stopPropagation();
                
                const inventarioId = this.getAttribute('data-inventario-id');
                const isSelected = this.classList.contains('seleccionado-inventario');

                if (isSelected) {
                    // Deseleccionar
                    this.classList.remove('seleccionado-inventario');
                    this.classList.add('no-seleccionado');
                    // Remover input hidden
                    const hiddenInput = document.getElementById('hidden-inventario-' + inventarioId);
                    if (hiddenInput) {
                        hiddenInput.remove();
                    }
                } else {
                    // Seleccionar
                    this.classList.remove('no-seleccionado');
                    this.classList.add('seleccionado-inventario');
                    // Agregar input hidden
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'inventarios[]';
                    input.value = inventarioId;
                    input.id = 'hidden-inventario-' + inventarioId;
                    hiddenContainer.appendChild(input);
                }
            });
        });
    }
});
</script>