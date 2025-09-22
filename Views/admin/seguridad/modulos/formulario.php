<?php
$this->extend('layouts/main');
$this->section('title', $title);
$this->section('content');
?>

<div class="admin-header">
    <h1><?= $title ?></h1>
    <div class="header-actions">
        <a href="/modulos" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </div>
</div>

<div class="form-container">
    <form method="POST" class="form-modern" id="moduloForm">
        <div class="form-sections">
            <!-- Información Básica -->
            <div class="form-section">
                <h3>Información del Módulo</h3>
                
                <div class="form-group">
                    <label for="modulo_descripcion" class="required">Descripción:</label>
                    <input type="text" name="modulo_descripcion" id="modulo_descripcion" 
                           value="<?= $modulo['modulo_descripcion'] ?? '' ?>" 
                           required maxlength="100" class="form-control"
                           placeholder="Ej: Gestión de usuarios, Reportes de ventas...">
                    <small class="form-text text-muted">
                        Nombre descriptivo del módulo. <span id="desc_counter">0</span>/100 caracteres
                    </small>
                </div>

                <div class="form-group">
                    <label for="modulo_ruta" class="required">Ruta:</label>
                    <input type="text" name="modulo_ruta" id="modulo_ruta" 
                           value="<?= $modulo['modulo_ruta'] ?? '' ?>" 
                           required maxlength="255" class="form-control"
                           placeholder="Ej: /usuarios, /reportes/ventas...">
                    <small class="form-text text-muted">
                        Ruta URL del módulo (debe comenzar con /). <span id="ruta_counter">0</span>/255 caracteres
                    </small>
                </div>
            </div>

            <!-- Configuración del Menú -->
            <div class="form-section">
                <h3>Configuración de Navegación</h3>
                
                <div class="form-group">
                    <label for="rela_menu">Menú Padre:</label>
                    <select name="rela_menu" id="rela_menu" class="form-control">
                        <option value="">Sin menú padre (módulo independiente)</option>
                        <?php foreach ($menus as $menu): ?>
                            <option value="<?= $menu['id_menu'] ?>" 
                                    <?= (isset($modulo) && $modulo['rela_menu'] == $menu['id_menu']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($menu['menu_nombre']) ?>
                                <?php if (!$menu['menu_estado']): ?>
                                    <em>(Inactivo)</em>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">
                        Seleccione el menú al que pertenece este módulo (opcional)
                    </small>
                </div>

                <div id="menuInfo" class="menu-preview">
                    <div class="info-card">
                        <h5>Vista previa de navegación</h5>
                        <div class="nav-preview">
                            <span class="menu-item" id="previewMenu">Menú</span>
                            <i class="fas fa-chevron-right"></i>
                            <span class="module-item" id="previewModule">Módulo</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuraciones Avanzadas -->
            <div class="form-section">
                <h3>Configuraciones Adicionales</h3>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="modulo_icono">Ícono:</label>
                        <div class="icon-input">
                            <input type="text" name="modulo_icono" id="modulo_icono" 
                                   value="<?= $modulo['modulo_icono'] ?? '' ?>" 
                                   class="form-control" placeholder="fas fa-users">
                            <span class="icon-preview" id="iconPreview">
                                <i class="fas fa-question"></i>
                            </span>
                        </div>
                        <small class="form-text text-muted">
                            Clase CSS del ícono (FontAwesome). Ej: fas fa-users
                        </small>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="modulo_orden">Orden:</label>
                        <input type="number" name="modulo_orden" id="modulo_orden" 
                               value="<?= $modulo['modulo_orden'] ?? 0 ?>" 
                               min="0" max="999" class="form-control">
                        <small class="form-text text-muted">
                            Orden de aparición en el menú (0-999)
                        </small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="modulo_observaciones">Observaciones:</label>
                    <textarea name="modulo_observaciones" id="modulo_observaciones" 
                              rows="4" maxlength="500" class="form-control" 
                              placeholder="Notas adicionales sobre la funcionalidad del módulo..."><?= $modulo['modulo_observaciones'] ?? '' ?></textarea>
                    <small class="form-text text-muted">
                        Información adicional sobre el módulo. <span id="obs_counter">0</span>/500 caracteres
                    </small>
                </div>
                
                <!-- Permisos requeridos -->
                <div class="form-group">
                    <label for="modulo_permisos">Permisos Requeridos:</label>
                    <input type="text" name="modulo_permisos" id="modulo_permisos" 
                           value="<?= $modulo['modulo_permisos'] ?? '' ?>" 
                           class="form-control" placeholder="usuarios_read,usuarios_write">
                    <small class="form-text text-muted">
                        Permisos necesarios separados por comas (opcional)
                    </small>
                </div>
            </div>

            <!-- Estado y Visibilidad -->
            <?php if (isset($modulo)): ?>
            <div class="form-section">
                <h3>Estado y Visibilidad</h3>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <div class="form-check">
                            <input type="checkbox" name="modulo_estado" id="modulo_estado" 
                                   value="1" <?= ($modulo['modulo_estado'] ?? 1) ? 'checked' : '' ?> 
                                   class="form-check-input">
                            <label class="form-check-label" for="modulo_estado">
                                Módulo activo
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Solo los módulos activos aparecen en el menú
                        </small>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <div class="form-check">
                            <input type="checkbox" name="modulo_visible" id="modulo_visible" 
                                   value="1" <?= ($modulo['modulo_visible'] ?? 1) ? 'checked' : '' ?> 
                                   class="form-check-input">
                            <label class="form-check-label" for="modulo_visible">
                                Visible en menú
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Desmarcar para módulos de backend
                        </small>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Botones de acción -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                <?= isset($modulo) ? 'Actualizar Módulo' : 'Crear Módulo' ?>
            </button>
            <a href="/modulos" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Cancelar
            </a>
            <?php if (isset($modulo) && !empty($modulo['modulo_ruta'])): ?>
            <a href="<?= $modulo['modulo_ruta'] ?>" class="btn btn-info" target="_blank">
                <i class="fas fa-external-link-alt"></i>
                Probar Módulo
            </a>
            <?php endif; ?>
        </div>
    </form>
</div>



<?php $this->endSection(); ?>