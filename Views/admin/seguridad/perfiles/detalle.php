<?php
$this->extend('layouts/main');
$this->section('title', $title);
$this->section('content');
?>

<div class="admin-header">
    <h1><?= $title ?></h1>
    <div class="header-actions">
        <a href="/perfiles" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
        <a href="/perfiles/<?= $perfil['id_perfil'] ?>/edit" class="btn btn-warning">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="/perfiles-modulos?perfil=<?= $perfil['id_perfil'] ?>" class="btn btn-info">
            <i class="fas fa-key"></i> Gestionar Permisos
        </a>
    </div>
</div>

<div class="detail-container">
    <div class="detail-sections">
        <!-- Información Principal -->
        <div class="detail-section primary-info">
            <h3>Información del Perfil</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>ID del Perfil:</label>
                    <span class="value">#<?= $perfil['id_perfil'] ?></span>
                </div>
                <div class="detail-item">
                    <label>Descripción:</label>
                    <span class="value profile-description">
                        <?php if ($perfil['sistema'] ?? false): ?>
                            <i class="fas fa-shield-alt text-warning" title="Perfil del sistema"></i>
                        <?php endif; ?>
                        <?php if (!empty($perfil['perfil_color'])): ?>
                            <span class="color-indicator" data-color="<?= $perfil['perfil_color'] ?>"></span>
                        <?php endif; ?>
                        <?= htmlspecialchars($perfil['perfil_descripcion']) ?>
                    </span>
                </div>
                <div class="detail-item">
                    <label>Nivel de Acceso:</label>
                    <span class="value">
                        <?php 
                        $niveles = [1 => 'Básico', 2 => 'Intermedio', 3 => 'Avanzado', 4 => 'Administrador'];
                        $nivel = $perfil['perfil_nivel'] ?? 1;
                        ?>
                        <span class="badge badge-level level-<?= $nivel ?>">
                            <?= $niveles[$nivel] ?? 'No definido' ?>
                        </span>
                    </span>
                </div>
                <div class="detail-item">
                    <label>Estado:</label>
                    <span class="value">
                        <?php if ($perfil['perfil_estado']): ?>
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
            
            <?php if (!empty($perfil['perfil_observaciones'])): ?>
            <div class="detail-item full-width">
                <label>Observaciones:</label>
                <div class="value observations-content">
                    <?= nl2br(htmlspecialchars($perfil['perfil_observaciones'])) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Configuraciones de Seguridad -->
        <div class="detail-section security-info">
            <h3>Configuraciones de Seguridad</h3>
            <div class="security-grid">
                <div class="security-item">
                    <div class="security-icon">
                        <i class="fas fa-user-plus <?= ($perfil['perfil_puede_crear_usuarios'] ?? 0) ? 'enabled' : 'disabled' ?>"></i>
                    </div>
                    <div class="security-content">
                        <label>Crear Usuarios</label>
                        <span class="status"><?= ($perfil['perfil_puede_crear_usuarios'] ?? 0) ? 'Permitido' : 'Restringido' ?></span>
                    </div>
                </div>
                
                <div class="security-item">
                    <div class="security-icon">
                        <i class="fas fa-users-cog <?= ($perfil['perfil_puede_modificar_perfiles'] ?? 0) ? 'enabled' : 'disabled' ?>"></i>
                    </div>
                    <div class="security-content">
                        <label>Modificar Perfiles</label>
                        <span class="status"><?= ($perfil['perfil_puede_modificar_perfiles'] ?? 0) ? 'Permitido' : 'Restringido' ?></span>
                    </div>
                </div>
                
                <div class="security-item">
                    <div class="security-icon">
                        <i class="fas fa-unlock <?= ($perfil['perfil_acceso_completo'] ?? 0) ? 'enabled' : 'disabled' ?>"></i>
                    </div>
                    <div class="security-content">
                        <label>Acceso Completo</label>
                        <span class="status"><?= ($perfil['perfil_acceso_completo'] ?? 0) ? 'Sí' : 'No' ?></span>
                    </div>
                </div>
                
                <div class="security-item">
                    <div class="security-icon">
                        <i class="fas fa-eye <?= ($perfil['perfil_solo_lectura'] ?? 0) ? 'enabled' : 'disabled' ?>"></i>
                    </div>
                    <div class="security-content">
                        <label>Solo Lectura</label>
                        <span class="status"><?= ($perfil['perfil_solo_lectura'] ?? 0) ? 'Sí' : 'No' ?></span>
                    </div>
                </div>
                
                <div class="security-item">
                    <div class="security-icon">
                        <i class="fas fa-star <?= ($perfil['perfil_predeterminado'] ?? 0) ? 'enabled' : 'disabled' ?>"></i>
                    </div>
                    <div class="security-content">
                        <label>Perfil Predeterminado</label>
                        <span class="status"><?= ($perfil['perfil_predeterminado'] ?? 0) ? 'Sí' : 'No' ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usuarios Asignados -->
        <div class="detail-section users-info">
            <h3>Usuarios Asignados</h3>
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $perfil['total_usuarios'] ?? 0 ?></div>
                        <div class="stat-label">Usuario<?= ($perfil['total_usuarios'] ?? 0) != 1 ? 's' : '' ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $perfil['usuarios_activos'] ?? 0 ?></div>
                        <div class="stat-label">Activos</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= ($perfil['total_usuarios'] ?? 0) - ($perfil['usuarios_activos'] ?? 0) ?></div>
                        <div class="stat-label">Inactivos</div>
                    </div>
                </div>
            </div>
            
            <?php if (($perfil['total_usuarios'] ?? 0) > 0): ?>
            <div class="users-actions">
                <a href="/usuarios?perfil=<?= $perfil['id_perfil'] ?>" class="btn btn-outline-primary">
                    <i class="fas fa-list"></i> Ver Usuarios
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Módulos y Permisos -->
        <div class="detail-section permissions-info">
            <h3>Módulos y Permisos</h3>
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-puzzle-piece"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $perfil['total_modulos'] ?? 0 ?></div>
                        <div class="stat-label">Módulos</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $perfil['total_permisos'] ?? 0 ?></div>
                        <div class="stat-label">Permisos</div>
                    </div>
                </div>
            </div>
            
            <div class="permissions-actions">
                <a href="/perfiles-modulos?perfil=<?= $perfil['id_perfil'] ?>" class="btn btn-outline-info">
                    <i class="fas fa-cog"></i> Gestionar Permisos
                </a>
                <?php if (($perfil['total_modulos'] ?? 0) == 0): ?>
                <a href="/perfiles-modulos/create?perfil=<?= $perfil['id_perfil'] ?>" class="btn btn-outline-success">
                    <i class="fas fa-plus"></i> Asignar Permisos
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Información de Auditoría -->
        <div class="detail-section audit-info">
            <h3>Información de Auditoría</h3>
            <div class="detail-grid">
                <?php if (!empty($perfil['perfil_fecha_creacion'])): ?>
                <div class="detail-item">
                    <label>Fecha de Creación:</label>
                    <span class="value">
                        <i class="fas fa-plus-circle"></i>
                        <?= date('d/m/Y H:i:s', strtotime($perfil['perfil_fecha_creacion'])) ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($perfil['perfil_fecha_modificacion'])): ?>
                <div class="detail-item">
                    <label>Última Modificación:</label>
                    <span class="value">
                        <i class="fas fa-edit"></i>
                        <?= date('d/m/Y H:i:s', strtotime($perfil['perfil_fecha_modificacion'])) ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($perfil['usuario_creacion'])): ?>
                <div class="detail-item">
                    <label>Creado por:</label>
                    <span class="value">
                        <i class="fas fa-user-shield"></i>
                        <?= htmlspecialchars($perfil['usuario_creacion']) ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($perfil['usuario_modificacion'])): ?>
                <div class="detail-item">
                    <label>Modificado por:</label>
                    <span class="value">
                        <i class="fas fa-user-edit"></i>
                        <?= htmlspecialchars($perfil['usuario_modificacion']) ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Advertencia para Perfiles del Sistema -->
        <?php if ($perfil['sistema'] ?? false): ?>
        <div class="detail-section system-warning">
            <h3>Perfil del Sistema</h3>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Atención:</strong> Este es un perfil crítico del sistema. 
                No se puede eliminar y algunas configuraciones están protegidas para mantener 
                la integridad y seguridad del sistema.
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Panel de Acciones -->
    <div class="actions-panel">
        <h4>Acciones Disponibles</h4>
        
        <?php if (!($perfil['sistema'] ?? false)): ?>
        <a href="/perfiles/<?= $perfil['id_perfil'] ?>/edit" class="action-button edit">
            <i class="fas fa-edit"></i>
            <span>Editar Perfil</span>
        </a>
        <?php endif; ?>
        
        <a href="/perfiles-modulos?perfil=<?= $perfil['id_perfil'] ?>" class="action-button permissions">
            <i class="fas fa-key"></i>
            <span>Gestionar Permisos</span>
        </a>
        
        <?php if (($perfil['total_usuarios'] ?? 0) > 0): ?>
        <a href="/usuarios?perfil=<?= $perfil['id_perfil'] ?>" class="action-button users">
            <i class="fas fa-users"></i>
            <span>Ver Usuarios (<?= $perfil['total_usuarios'] ?>)</span>
        </a>
        <?php endif; ?>
        
        <a href="/perfiles/create" class="action-button new">
            <i class="fas fa-plus"></i>
            <span>Nuevo Perfil</span>
        </a>
        
        <?php if ($perfil['perfil_estado']): ?>
            <?php if (!($perfil['sistema'] ?? false) && $this->userCan('perfiles_delete')): ?>
                <button type="button" class="action-button delete" 
                        data-action="confirmar-eliminar-perfil" 
                        data-id="<?= $perfil['id_perfil'] ?>" 
                        data-descripcion="<?= htmlspecialchars($perfil['perfil_descripcion']) ?>" 
                        data-total-usuarios="<?= $perfil['total_usuarios'] ?? 0 ?>">
                    <i class="fas fa-trash"></i>
                    <span>Eliminar</span>
                </button>
            <?php endif; ?>
        <?php else: ?>
            <?php if ($this->userCan('perfiles_restore')): ?>
                <button type="button" class="action-button restore" 
                        data-action="confirmar-restaurar-perfil" 
                        data-id="<?= $perfil['id_perfil'] ?>" 
                        data-descripcion="<?= htmlspecialchars($perfil['perfil_descripcion']) ?>">>
                    <i class="fas fa-undo"></i>
                    <span>Restaurar</span>
                </button>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>