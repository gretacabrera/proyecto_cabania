<?php
/**
 * Vista: Formulario de Menú
 * Descripción: Formulario para crear/editar menús
 * Autor: Sistema MVC
 * Fecha: <?php echo date('Y-m-d'); ?>
 */

$isEdit = isset($menu) && !empty($menu);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/menus') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos del menú' : 'Datos del nuevo menú' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formMenu" method="POST" 
                          action="<?= $isEdit ? url('/menus/' . $menu['id_menu'] . '/edit') : url('/menus/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_menu" value="<?= $menu['id_menu'] ?>">
                        <?php endif; ?>

                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="menu_nombre" class="required">
                                        <i class="fas fa-tag"></i> Nombre del menú
                                    </label>
                                    <input type="text" class="form-control" id="menu_nombre" name="menu_nombre" 
                                           value="<?= htmlspecialchars($menu['menu_nombre'] ?? '') ?>"
                                           required maxlength="45" placeholder="Ej: Seguridad, Operaciones, Reportes">
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Nombre descriptivo del menú en el sistema</small>
                                </div>
                            </div>

                            <!-- Orden -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="menu_orden" class="required">
                                        <i class="fas fa-sort-numeric-down"></i> Orden
                                    </label>
                                    <input type="number" class="form-control" id="menu_orden" name="menu_orden" 
                                           value="<?= $menu['menu_orden'] ?? '1' ?>"
                                           required min="1" max="100" placeholder="1">
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Posición en el sistema</small>
                                </div>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="form-group">
                            <label for="menu_estado" class="required">
                                <i class="fas fa-toggle-on"></i> Estado
                            </label>
                            <select class="form-control form-select" id="menu_estado" name="menu_estado" required>
                                <option value="1" <?= (!$isEdit || $menu['menu_estado'] == 1) ? 'selected' : '' ?>>Activo</option>
                                <option value="0" <?= ($isEdit && $menu['menu_estado'] == 0) ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">Solo los menús activos serán visibles en el sistema</small>
                        </div>

                        <!-- Botones de acción -->
                        <div class="form-group mt-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save"></i> 
                                        <?= $isEdit ? 'Actualizar Menú' : 'Crear Menú' ?>
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
                            <li>• El nombre debe ser claro y descriptivo</li>
                            <li>• El orden define la posición en la navegación</li>
                            <li>• Los menús inactivos no aparecen en el sistema</li>
                            <li>• Evite crear menús duplicados con el mismo orden</li>
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
                                        <div class="stat-value"><?= $estadisticas['total_modulos'] ?? 0 ?></div>
                                        <div class="stat-label small text-muted">Módulos</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value"><?= $estadisticas['perfiles_usando'] ?? 0 ?></div>
                                        <div class="stat-label small text-muted">Perfiles</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear el menú.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Validación de formulario -->
<script>
(function() {
    'use strict';
    
    const form = document.getElementById('formMenu');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
})();

function limpiarFormulario() {
    const form = document.getElementById('formMenu');
    form.reset();
    form.classList.remove('was-validated');
}
</script>
