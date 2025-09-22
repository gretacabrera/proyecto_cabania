<?php
$this->extend('layouts/main');
$this->section('title', $title);
$this->section('content');
?>

<div class="admin-header">
    <h1><?= $title ?></h1>
    <div class="header-actions">
        <a href="/estados-personas" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
        
        <?php if (!($estado['sistema'] ?? false) && $this->userCan('estados_personas_edit')): ?>
            <a href="/estados-personas/<?= $estado['id_estadopersona'] ?>/edit" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="detail-container">
    <!-- Información principal -->
    <div class="detail-section main-info">
        <div class="state-header">
            <div class="state-visual">
                <?php if (!empty($estado['estadopersona_color'])): ?>
                    <div class="state-color-large" 
                         style="background-color: <?= $estado['estadopersona_color'] ?>"
                         title="Color: <?= $estado['estadopersona_color'] ?>"></div>
                <?php else: ?>
                    <div class="state-color-large default-color" title="Sin color asignado">
                        <i class="fas fa-palette"></i>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="state-main-info">
                <h2 class="state-name">
                    <?php if ($estado['sistema'] ?? false): ?>
                        <i class="fas fa-lock text-warning" title="Estado del sistema"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($estado['estadopersona_descripcion']) ?>
                </h2>
                
                <div class="state-badges">
                    <?php if ($estado['estadopersona_estado']): ?>
                        <span class="badge badge-success">
                            <i class="fas fa-check-circle"></i> Activo
                        </span>
                    <?php else: ?>
                        <span class="badge badge-danger">
                            <i class="fas fa-times-circle"></i> Inactivo
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($estado['estadopersona_permite_acceso'] ?? true): ?>
                        <span class="badge badge-info">
                            <i class="fas fa-key"></i> Permite acceso
                        </span>
                    <?php else: ?>
                        <span class="badge badge-warning">
                            <i class="fas fa-ban"></i> Sin acceso al sistema
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($estado['sistema'] ?? false): ?>
                        <span class="badge badge-system">
                            <i class="fas fa-cog"></i> Estado del sistema
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($estado['estadopersona_observaciones'])): ?>
                    <div class="state-description">
                        <h4>Observaciones:</h4>
                        <p><?= nl2br(htmlspecialchars($estado['estadopersona_observaciones'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Estadísticas de uso -->
    <div class="detail-section">
        <div class="section-header">
            <h3>Estadísticas de uso</h3>
            <small class="text-muted">Información sobre el uso actual de este estado</small>
        </div>
        
        <div class="usage-grid">
            <div class="usage-card primary">
                <div class="usage-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="usage-info">
                    <div class="usage-number"><?= $estado['total_personas'] ?? 0 ?></div>
                    <div class="usage-label">Personas asignadas</div>
                </div>
                <?php if (($estado['total_personas'] ?? 0) > 0): ?>
                    <div class="usage-action">
                        <a href="/usuarios?estado_persona=<?= $estado['id_estadopersona'] ?>" 
                           class="btn btn-sm btn-outline-primary">
                            Ver personas
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="usage-card">
                <div class="usage-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="usage-info">
                    <div class="usage-number"><?= number_format($estado['porcentaje_uso'] ?? 0, 1) ?>%</div>
                    <div class="usage-label">Del total de personas</div>
                </div>
            </div>
            
            <div class="usage-card">
                <div class="usage-icon">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div class="usage-info">
                    <div class="usage-number">
                        <?= !empty($estado['estadopersona_fecha_creacion']) 
                            ? date('d/m/Y', strtotime($estado['estadopersona_fecha_creacion'])) 
                            : 'N/A' ?>
                    </div>
                    <div class="usage-label">Fecha de creación</div>
                </div>
            </div>
            
            <div class="usage-card">
                <div class="usage-icon">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="usage-info">
                    <div class="usage-number">
                        <?= !empty($estado['estadopersona_fecha_modificacion']) 
                            ? date('d/m/Y', strtotime($estado['estadopersona_fecha_modificacion'])) 
                            : 'Nunca' ?>
                    </div>
                    <div class="usage-label">Última modificación</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información técnica -->
    <div class="detail-section">
        <div class="section-header">
            <h3>Información técnica</h3>
            <small class="text-muted">Detalles del registro en la base de datos</small>
        </div>
        
        <div class="tech-info">
            <div class="tech-row">
                <div class="tech-label">ID del estado:</div>
                <div class="tech-value">
                    <code>#<?= $estado['id_estadopersona'] ?></code>
                </div>
            </div>
            
            <div class="tech-row">
                <div class="tech-label">Color hexadecimal:</div>
                <div class="tech-value">
                    <?php if (!empty($estado['estadopersona_color'])): ?>
                        <code><?= $estado['estadopersona_color'] ?></code>
                        <div class="color-sample-small" style="background-color: <?= $estado['estadopersona_color'] ?>"></div>
                    <?php else: ?>
                        <span class="text-muted">No definido</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="tech-row">
                <div class="tech-label">Estado del registro:</div>
                <div class="tech-value">
                    <?php if ($estado['estadopersona_estado']): ?>
                        <span class="status-active">Activo (1)</span>
                    <?php else: ?>
                        <span class="status-inactive">Inactivo (0)</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="tech-row">
                <div class="tech-label">Permite acceso:</div>
                <div class="tech-value">
                    <?php if ($estado['estadopersona_permite_acceso'] ?? true): ?>
                        <span class="status-active">Sí (1)</span>
                    <?php else: ?>
                        <span class="status-inactive">No (0)</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="tech-row">
                <div class="tech-label">Tipo de estado:</div>
                <div class="tech-value">
                    <?php if ($estado['sistema'] ?? false): ?>
                        <span class="system-type">Sistema (protegido)</span>
                    <?php else: ?>
                        <span class="custom-type">Personalizado</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($estado['estadopersona_fecha_creacion'])): ?>
                <div class="tech-row">
                    <div class="tech-label">Creado:</div>
                    <div class="tech-value">
                        <?= date('d/m/Y H:i:s', strtotime($estado['estadopersona_fecha_creacion'])) ?>
                        <small class="text-muted">(<?= time_ago($estado['estadopersona_fecha_creacion']) ?>)</small>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($estado['estadopersona_fecha_modificacion'])): ?>
                <div class="tech-row">
                    <div class="tech-label">Modificado:</div>
                    <div class="tech-value">
                        <?= date('d/m/Y H:i:s', strtotime($estado['estadopersona_fecha_modificacion'])) ?>
                        <small class="text-muted">(<?= time_ago($estado['estadopersona_fecha_modificacion']) ?>)</small>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (($estado['total_personas'] ?? 0) > 0): ?>
        <!-- Listado de personas con este estado -->
        <div class="detail-section">
            <div class="section-header">
                <h3>Personas con este estado</h3>
                <div class="section-actions">
                    <a href="/usuarios?estado_persona=<?= $estado['id_estadopersona'] ?>" 
                       class="btn btn-sm btn-outline-primary">
                        Ver todas (<?= $estado['total_personas'] ?>)
                    </a>
                </div>
            </div>
            
            <?php if (!empty($personas_muestra)): ?>
                <div class="people-preview">
                    <?php foreach ($personas_muestra as $persona): ?>
                        <div class="person-card">
                            <div class="person-avatar">
                                <?php if (!empty($persona['usuario_foto'])): ?>
                                    <img src="<?= $persona['usuario_foto'] ?>" alt="Avatar de <?= $persona['usuario_nombre'] ?>">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?= strtoupper(substr($persona['usuario_nombre'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="person-info">
                                <div class="person-name">
                                    <?= htmlspecialchars($persona['usuario_nombre']) ?>
                                    <?php if (!empty($persona['usuario_apellido'])): ?>
                                        <?= htmlspecialchars($persona['usuario_apellido']) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="person-email"><?= htmlspecialchars($persona['usuario_email']) ?></div>
                                <?php if (!empty($persona['usuario_fecha_ultimo_acceso'])): ?>
                                    <div class="person-last-access">
                                        Último acceso: <?= time_ago($persona['usuario_fecha_ultimo_acceso']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="person-actions">
                                <a href="/usuarios/<?= $persona['id_usuario'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    Ver perfil
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if ($estado['total_personas'] > count($personas_muestra)): ?>
                        <div class="people-more">
                            <div class="more-indicator">
                                +<?= $estado['total_personas'] - count($personas_muestra) ?> personas más
                            </div>
                            <a href="/usuarios?estado_persona=<?= $estado['id_estadopersona'] ?>" 
                               class="btn btn-primary">
                                Ver todas las personas
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Acciones adicionales -->
    <div class="detail-section actions-section">
        <div class="section-header">
            <h3>Acciones</h3>
        </div>
        
        <div class="action-grid">
            <?php if (!($estado['sistema'] ?? false) && $this->userCan('estados_personas_edit')): ?>
                <a href="/estados-personas/<?= $estado['id_estadopersona'] ?>/edit" 
                   class="action-card edit-action">
                    <div class="action-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="action-info">
                        <div class="action-title">Editar estado</div>
                        <div class="action-description">Modificar información y configuración</div>
                    </div>
                </a>
            <?php endif; ?>
            
            <?php if ($this->userCan('estados_personas_create')): ?>
                <a href="/estados-personas/create" class="action-card create-action">
                    <div class="action-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="action-info">
                        <div class="action-title">Crear nuevo estado</div>
                        <div class="action-description">Definir un estado similar</div>
                    </div>
                </a>
            <?php endif; ?>
            
            <a href="/estados-personas" class="action-card list-action">
                <div class="action-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div class="action-info">
                    <div class="action-title">Ver todos los estados</div>
                    <div class="action-description">Gestionar estados de personas</div>
                </div>
            </a>
            
            <?php if (($estado['total_personas'] ?? 0) > 0): ?>
                <a href="/usuarios?estado_persona=<?= $estado['id_estadopersona'] ?>" 
                   class="action-card users-action">
                    <div class="action-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="action-info">
                        <div class="action-title">Gestionar personas</div>
                        <div class="action-description">Ver y editar personas con este estado</div>
                    </div>
                </a>
            <?php endif; ?>
            
            <?php if (!($estado['sistema'] ?? false) && ($estado['total_personas'] ?? 0) == 0 && $this->userCan('estados_personas_delete')): ?>
                <button type="button" class="action-card delete-action" data-action="delete-estado" data-estado data-estado-descripcion="<?= htmlspecialchars($estado['estadopersona_descripcion']) ?>">
                    <div class="action-icon">
                        <i class="fas fa-trash"></i>
                    </div>
                    <div class="action-info">
                        <div class="action-title">Eliminar estado</div>
                        <div class="action-description">Eliminar permanentemente</div>
                    </div>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php
// Función auxiliar para mostrar tiempo transcurrido
function time_ago($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'hace unos segundos';
    if ($time < 3600) return 'hace ' . floor($time/60) . ' minutos';
    if ($time < 86400) return 'hace ' . floor($time/3600) . ' horas';
    if ($time < 2592000) return 'hace ' . floor($time/86400) . ' días';
    if ($time < 31536000) return 'hace ' . floor($time/2592000) . ' meses';
    return 'hace ' . floor($time/31536000) . ' años';
}
?>