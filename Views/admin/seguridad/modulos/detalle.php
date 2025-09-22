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
        <a href="/modulos/<?= $modulo['id_modulo'] ?>/edit" class="btn btn-warning">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php if (!empty($modulo['modulo_ruta'])): ?>
        <a href="<?= $modulo['modulo_ruta'] ?>" class="btn btn-info" target="_blank">
            <i class="fas fa-external-link-alt"></i> Probar Módulo
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="detail-container">
    <div class="detail-sections">
        <!-- Información Principal -->
        <div class="detail-section primary-info">
            <h3>Información del Módulo</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>ID del Módulo:</label>
                    <span class="value">#<?= $modulo['id_modulo'] ?></span>
                </div>
                <div class="detail-item">
                    <label>Descripción:</label>
                    <span class="value module-description">
                        <?php if (!empty($modulo['modulo_icono'])): ?>
                            <i class="<?= $modulo['modulo_icono'] ?>"></i>
                        <?php endif; ?>
                        <?= htmlspecialchars($modulo['modulo_descripcion']) ?>
                    </span>
                </div>
                <div class="detail-item">
                    <label>Ruta URL:</label>
                    <span class="value">
                        <code class="route-display"><?= htmlspecialchars($modulo['modulo_ruta']) ?></code>
                        <?php if (!empty($modulo['modulo_ruta'])): ?>
                            <a href="<?= $modulo['modulo_ruta'] ?>" target="_blank" class="test-link">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="detail-item">
                    <label>Estado:</label>
                    <span class="value">
                        <?php if ($modulo['modulo_estado']): ?>
                            <span class="badge badge-success">
                                <i class="fas fa-check-circle"></i> Activo
                            </span>
                        <?php else: ?>
                            <span class="badge badge-danger">
                                <i class="fas fa-times-circle"></i> Inactivo
                            </span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Configuración de Navegación -->
        <div class="detail-section menu-info">
            <h3>Configuración de Navegación</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Menú Padre:</label>
                    <span class="value">
                        <?php if (!empty($modulo['menu_nombre'])): ?>
                            <span class="badge badge-info">
                                <i class="fas fa-bars"></i>
                                <?= htmlspecialchars($modulo['menu_nombre']) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">
                                <i class="fas fa-minus"></i> Sin menú padre
                            </span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="detail-item">
                    <label>Ícono:</label>
                    <span class="value">
                        <?php if (!empty($modulo['modulo_icono'])): ?>
                            <div class="icon-display">
                                <i class="<?= $modulo['modulo_icono'] ?>"></i>
                                <code><?= $modulo['modulo_icono'] ?></code>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">Sin ícono definido</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="detail-item">
                    <label>Orden:</label>
                    <span class="value">
                        <?php if (isset($modulo['modulo_orden']) && $modulo['modulo_orden'] > 0): ?>
                            <span class="order-badge"><?= $modulo['modulo_orden'] ?></span>
                        <?php else: ?>
                            <span class="text-muted">Sin orden específico</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="detail-item">
                    <label>Visibilidad:</label>
                    <span class="value">
                        <?php if ($modulo['modulo_visible'] ?? true): ?>
                            <span class="badge badge-info">
                                <i class="fas fa-eye"></i> Visible en menú
                            </span>
                        <?php else: ?>
                            <span class="badge badge-secondary">
                                <i class="fas fa-eye-slash"></i> Oculto
                            </span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Vista Previa de Navegación -->
        <?php if (!empty($modulo['menu_nombre']) && $modulo['modulo_estado'] && ($modulo['modulo_visible'] ?? true)): ?>
        <div class="detail-section navigation-preview">
            <h3>Vista Previa de Navegación</h3>
            <div class="nav-simulation">
                <div class="breadcrumb-simulation">
                    <span class="breadcrumb-item">
                        <i class="fas fa-home"></i> Inicio
                    </span>
                    <i class="fas fa-chevron-right"></i>
                    <span class="breadcrumb-item">
                        <i class="fas fa-bars"></i> <?= htmlspecialchars($modulo['menu_nombre']) ?>
                    </span>
                    <i class="fas fa-chevron-right"></i>
                    <span class="breadcrumb-item active">
                        <?php if (!empty($modulo['modulo_icono'])): ?>
                            <i class="<?= $modulo['modulo_icono'] ?>"></i>
                        <?php endif; ?>
                        <?= htmlspecialchars($modulo['modulo_descripcion']) ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Configuraciones Adicionales -->
        <div class="detail-section config-info">
            <h3>Configuraciones Adicionales</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Permisos Requeridos:</label>
                    <span class="value">
                        <?php if (!empty($modulo['modulo_permisos'])): ?>
                            <div class="permissions-list">
                                <?php foreach (explode(',', $modulo['modulo_permisos']) as $permiso): ?>
                                    <span class="permission-badge"><?= trim($permiso) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">Sin permisos específicos</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="detail-item full-width">
                    <label>Observaciones:</label>
                    <span class="value">
                        <?= !empty($modulo['modulo_observaciones']) 
                            ? nl2br(htmlspecialchars($modulo['modulo_observaciones'])) 
                            : '<em class="text-muted">Sin observaciones</em>' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Información de Auditoría -->
        <div class="detail-section audit-info">
            <h3>Información de Auditoría</h3>
            <div class="detail-grid">
                <?php if (!empty($modulo['modulo_fecha_creacion'])): ?>
                <div class="detail-item">
                    <label>Fecha de Creación:</label>
                    <span class="value">
                        <i class="fas fa-plus-circle"></i>
                        <?= date('d/m/Y H:i:s', strtotime($modulo['modulo_fecha_creacion'])) ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($modulo['modulo_fecha_modificacion'])): ?>
                <div class="detail-item">
                    <label>Última Modificación:</label>
                    <span class="value">
                        <i class="fas fa-edit"></i>
                        <?= date('d/m/Y H:i:s', strtotime($modulo['modulo_fecha_modificacion'])) ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($modulo['usuario_creacion'])): ?>
                <div class="detail-item">
                    <label>Creado por:</label>
                    <span class="value">
                        <i class="fas fa-user-shield"></i>
                        <?= htmlspecialchars($modulo['usuario_creacion']) ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($modulo['usuario_modificacion'])): ?>
                <div class="detail-item">
                    <label>Modificado por:</label>
                    <span class="value">
                        <i class="fas fa-user-edit"></i>
                        <?= htmlspecialchars($modulo['usuario_modificacion']) ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Módulos Relacionados -->
        <?php if (!empty($modulos_relacionados)): ?>
        <div class="detail-section related-modules">
            <h3>Módulos del Mismo Menú</h3>
            <div class="related-grid">
                <?php foreach ($modulos_relacionados as $relacionado): ?>
                    <div class="related-item">
                        <a href="/modulos/<?= $relacionado['id_modulo'] ?>" class="related-link">
                            <?php if (!empty($relacionado['modulo_icono'])): ?>
                                <i class="<?= $relacionado['modulo_icono'] ?>"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($relacionado['modulo_descripcion']) ?>
                        </a>
                        <small><?= htmlspecialchars($relacionado['modulo_ruta']) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Panel de Acciones -->
    <div class="actions-panel">
        <h4>Acciones Disponibles</h4>
        
        <a href="/modulos/<?= $modulo['id_modulo'] ?>/edit" class="action-button edit">
            <i class="fas fa-edit"></i>
            <span>Editar Módulo</span>
        </a>
        
        <?php if (!empty($modulo['modulo_ruta'])): ?>
        <a href="<?= $modulo['modulo_ruta'] ?>" class="action-button test" target="_blank">
            <i class="fas fa-external-link-alt"></i>
            <span>Probar Módulo</span>
        </a>
        <?php endif; ?>
        
        <?php if ($modulo['modulo_estado']): ?>
            <?php if ($this->userCan('modulos_delete')): ?>
                <button type="button" class="action-button deactivate" 
                        data-action="confirmar-desactivar" 
                        data-id="<?= $modulo['id_modulo'] ?>" 
                        data-descripcion="<?= htmlspecialchars($modulo['modulo_descripcion']) ?>">
                    <i class="fas fa-ban"></i>
                    <span>Desactivar</span>
                </button>
            <?php endif; ?>
        <?php else: ?>
            <?php if ($this->userCan('modulos_restore')): ?>
                <button type="button" class="action-button activate" 
                        data-action="confirmar-activar" 
                        data-id="<?= $modulo['id_modulo'] ?>" 
                        data-descripcion="<?= htmlspecialchars($modulo['modulo_descripcion']) ?>">>
                    <i class="fas fa-check"></i>
                    <span>Activar</span>
                </button>
            <?php endif; ?>
        <?php endif; ?>
        
        <a href="/modulos/create" class="action-button new">
            <i class="fas fa-plus"></i>
            <span>Nuevo Módulo</span>
        </a>
    </div>
</div>

<?php $this->endSection(); ?>