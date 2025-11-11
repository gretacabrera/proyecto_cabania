<?php
/**
 * Vista: Formulario de Módulo
 * Descripción: Formulario para crear/editar módulos
 */

$isEdit = isset($modulo) && !empty($modulo);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/modulos') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos del módulo' : 'Datos del nuevo módulo' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formModulo" method="POST" 
                          action="<?= $isEdit ? url('/modulos/' . $modulo['id_modulo'] . '/edit') : url('/modulos/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_modulo" value="<?= $modulo['id_modulo'] ?>">
                        <?php endif; ?>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="modulo_descripcion" class="required">
                                <i class="fas fa-align-left"></i> Descripción
                            </label>
                            <input type="text" class="form-control" id="modulo_descripcion" name="modulo_descripcion" 
                                   value="<?= htmlspecialchars($modulo['modulo_descripcion'] ?? '') ?>"
                                   required maxlength="45" placeholder="Nombre descriptivo del módulo">
                            <div class="invalid-feedback">Por favor, ingrese la descripción del módulo.</div>
                            <small class="form-text text-muted">Ejemplo: Gestión de Usuarios, Reportes de Ventas</small>
                        </div>

                        <!-- Ruta -->
                        <div class="form-group">
                            <label for="modulo_ruta" class="required">
                                <i class="fas fa-link"></i> Ruta
                            </label>
                            <input type="text" class="form-control" id="modulo_ruta" name="modulo_ruta" 
                                   value="<?= htmlspecialchars($modulo['modulo_ruta'] ?? '') ?>"
                                   required maxlength="45" placeholder="Ruta del módulo">
                            <div class="invalid-feedback">Por favor, ingrese la ruta del módulo.</div>
                            <small class="form-text text-muted">Ruta única del módulo. Ejemplo: usuarios, reportes, configuracion</small>
                        </div>

                        <!-- Menú -->
                        <div class="form-group">
                            <label for="rela_menu">
                                <i class="fas fa-bars"></i> Menú Padre
                            </label>
                            <select class="form-select form-select-sm" id="rela_menu" name="rela_menu">
                                <option value="">Sin menú padre (módulo independiente)</option>
                                <?php foreach ($menus as $menu): ?>
                                    <option value="<?= $menu['id_menu'] ?>" 
                                            <?= (isset($modulo) && $modulo['rela_menu'] == $menu['id_menu']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($menu['menu_nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Seleccione el menú al que pertenece este módulo (opcional)</small>
                        </div>

                        <!-- Estado -->
                        <div class="form-group">
                            <label for="modulo_estado">
                                <i class="fas fa-toggle-on"></i> Estado
                            </label>
                            <select class="form-select form-select-sm" id="modulo_estado" name="modulo_estado" required>
                                <option value="1" <?= (!$isEdit || $modulo['modulo_estado'] == 1) ? 'selected' : '' ?>>Activo</option>
                                <option value="0" <?= ($isEdit && $modulo['modulo_estado'] == 0) ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                            <div class="invalid-feedback">Por favor, seleccione el estado del módulo.</div>
                            <small class="form-text text-muted">Solo los módulos activos aparecen en el menú</small>
                        </div>

                        <!-- Botones de acción -->
                        <div class="form-actions mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?= $isEdit ? 'Actualizar Módulo' : 'Crear Módulo' ?>
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-eraser"></i>
                                Limpiar
                            </button>
                            <a href="<?= url('/modulos') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
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
                            <li>• La descripción debe ser clara y concisa</li>
                            <li>• La ruta debe ser única en el sistema</li>
                            <li>• Asocie el módulo a un menú para mejor organización</li>
                            <li>• Los módulos inactivos no aparecen en el sistema</li>
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
                                        <div class="stat-value"><?= number_format($estadisticas['perfiles_asignados']) ?></div>
                                        <div class="stat-label small text-muted">Perfiles</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value"><?= number_format($estadisticas['usuarios_con_permiso']) ?></div>
                                        <div class="stat-label small text-muted">Usuarios</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear el módulo.
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
    const form = document.getElementById('formModulo');
    
    // Validación del formulario
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Limpiar formulario
    form.addEventListener('reset', function() {
        form.classList.remove('was-validated');
    });
});
</script>
