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
    </div>
</div>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?= session('error') ?>
    </div>
<?php endif; ?>

<?php if (session()->has('errors')): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <strong>Por favor corrija los siguientes errores:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach (session('errors') as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="form-container">
    <?php 
    $action = !empty($estado) ? "/estados-personas/{$estado['id_estadopersona']}" : "/estados-personas";
    $method = !empty($estado) ? 'PUT' : 'POST';
    $isEdit = !empty($estado);
    $isSystem = $isEdit && ($estado['sistema'] ?? false);
    ?>
    
    <form method="POST" action="<?= $action ?>" class="estado-form" id="estadoPersonaForm">
        <?php if ($method === 'PUT'): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>
        
        <div class="form-section">
            <div class="section-header">
                <h3>Información básica</h3>
                <?php if ($isSystem): ?>
                    <div class="system-notice">
                        <i class="fas fa-info-circle"></i>
                        <span>Este es un estado del sistema. Algunas opciones están restringidas.</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="estadopersona_descripcion" class="required">Descripción del estado</label>
                        <input type="text" 
                               class="form-control" 
                               id="estadopersona_descripcion" 
                               name="estadopersona_descripcion" 
                               value="<?= old('estadopersona_descripcion', $estado['estadopersona_descripcion'] ?? '') ?>" 
                               maxlength="100"
                               <?= $isSystem ? 'readonly' : '' ?>
                               required>
                        <div class="field-help">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Nombre descriptivo del estado de persona (ej: "Activo", "Suspendido", "Inactivo")
                            </small>
                            <span class="char-counter" id="descripcionCounter">
                                <span class="current">0</span>/<span class="max">100</span>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="estadopersona_color">Color identificativo</label>
                        <div class="color-input-group">
                            <input type="color" 
                                   class="form-control color-picker" 
                                   id="estadopersona_color" 
                                   name="estadopersona_color" 
                                   value="<?= old('estadopersona_color', $estado['estadopersona_color'] ?? '#007bff') ?>"
                                   <?= $isSystem ? 'disabled' : '' ?>>
                            <input type="text" 
                                   class="form-control color-text" 
                                   id="estadopersona_color_text" 
                                   value="<?= old('estadopersona_color', $estado['estadopersona_color'] ?? '#007bff') ?>"
                                   pattern="^#[0-9A-Fa-f]{6}$" 
                                   placeholder="#007bff"
                                   maxlength="7"
                                   <?= $isSystem ? 'readonly' : '' ?>>
                        </div>
                        <small class="text-muted">Color para identificar visualmente este estado</small>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="estadopersona_observaciones">Observaciones adicionales</label>
                <textarea class="form-control" 
                          id="estadopersona_observaciones" 
                          name="estadopersona_observaciones" 
                          rows="3" 
                          maxlength="500"
                          <?= $isSystem ? 'readonly' : '' ?>><?= old('estadopersona_observaciones', $estado['estadopersona_observaciones'] ?? '') ?></textarea>
                <div class="field-help">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Información adicional sobre cuándo usar este estado o sus implicaciones
                    </small>
                    <span class="char-counter" id="observacionesCounter">
                        <span class="current">0</span>/<span class="max">500</span>
                    </span>
                </div>
            </div>
            
            <?php if (!$isSystem): ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="estadopersona_estado" 
                                       name="estadopersona_estado" 
                                       value="1" 
                                       <?= old('estadopersona_estado', $estado['estadopersona_estado'] ?? '1') == '1' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="estadopersona_estado">
                                    <strong>Estado activo</strong>
                                    <small class="d-block text-muted">
                                        Solo los estados activos pueden ser asignados a personas
                                    </small>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="estadopersona_permite_acceso" 
                                       name="estadopersona_permite_acceso" 
                                       value="1" 
                                       <?= old('estadopersona_permite_acceso', $estado['estadopersona_permite_acceso'] ?? '1') == '1' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="estadopersona_permite_acceso">
                                    <strong>Permite acceso al sistema</strong>
                                    <small class="d-block text-muted">
                                        Las personas con este estado pueden iniciar sesión
                                    </small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Vista previa del estado -->
        <div class="form-section">
            <div class="section-header">
                <h3>Vista previa</h3>
                <small class="text-muted">Cómo se verá este estado en el sistema</small>
            </div>
            
            <div class="state-preview">
                <div class="preview-badge">
                    <span class="state-color-dot" id="previewColorDot" 
                          style="background-color: <?= old('estadopersona_color', $estado['estadopersona_color'] ?? '#007bff') ?>"></span>
                    <span class="state-name" id="previewStateName">
                        <?= old('estadopersona_descripcion', $estado['estadopersona_descripcion'] ?? 'Nuevo Estado') ?>
                    </span>
                    <span class="state-access-indicator" id="previewAccessIndicator">
                        <?php $permitAcceso = old('estadopersona_permite_acceso', $estado['estadopersona_permite_acceso'] ?? '1') == '1'; ?>
                        <?php if ($permitAcceso): ?>
                            <i class="fas fa-check-circle text-success" title="Permite acceso"></i>
                        <?php else: ?>
                            <i class="fas fa-times-circle text-danger" title="No permite acceso"></i>
                        <?php endif; ?>
                    </span>
                </div>
                
                <div class="preview-description" id="previewDescription">
                    <?php $obs = old('estadopersona_observaciones', $estado['estadopersona_observaciones'] ?? ''); ?>
                    <?= !empty($obs) ? htmlspecialchars($obs) : '<em>Sin observaciones adicionales</em>' ?>
                </div>
            </div>
        </div>

        <?php if ($isEdit): ?>
            <!-- Información de uso -->
            <div class="form-section">
                <div class="section-header">
                    <h3>Información de uso</h3>
                    <small class="text-muted">Estadísticas actuales de este estado</small>
                </div>
                
                <div class="usage-stats">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number"><?= $estado['total_personas'] ?? 0 ?></div>
                            <div class="stat-label">Personas asignadas</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number">
                                <?= !empty($estado['estadopersona_fecha_creacion']) 
                                    ? date('d/m/Y', strtotime($estado['estadopersona_fecha_creacion'])) 
                                    : '-' ?>
                            </div>
                            <div class="stat-label">Fecha creación</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number">
                                <?= !empty($estado['estadopersona_fecha_modificacion']) 
                                    ? date('d/m/Y', strtotime($estado['estadopersona_fecha_modificacion'])) 
                                    : 'Nunca' ?>
                            </div>
                            <div class="stat-label">Última modificación</div>
                        </div>
                    </div>
                </div>
                
                <?php if (($estado['total_personas'] ?? 0) > 0): ?>
                    <div class="assigned-people-preview">
                        <h4>Personas con este estado:</h4>
                        <a href="/usuarios?estado_persona=<?= $estado['id_estadopersona'] ?>" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-external-link-alt"></i>
                            Ver todas las personas con este estado
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Acciones del formulario -->
        <div class="form-actions">
            <div class="action-buttons">
                <button type="submit" class="btn btn-success" id="saveButton">
                    <i class="fas fa-save"></i>
                    <?= $isEdit ? 'Actualizar Estado' : 'Crear Estado' ?>
                </button>
                
                <a href="/estados-personas" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                
                <?php if ($isEdit && !$isSystem): ?>
                    <button type="button" class="btn btn-outline-danger" 
                            data-action="delete-estado" 
                            data-estado 
                            data-estado-descripcion="" 
                            <?= ($estado['total_personas'] ?? 0) > 0 ? 'disabled' : '' ?>>
                        <i class="fas fa-trash"></i>
                        Eliminar Estado
                    </button>
                <?php endif; ?>
            </div>
            
            <?php if ($isEdit && !$isSystem && ($estado['total_personas'] ?? 0) > 0): ?>
                <div class="deletion-notice">
                    <i class="fas fa-info-circle"></i>
                    No se puede eliminar este estado porque tiene personas asignadas. 
                    Primero reasigne las personas a otro estado.
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php $this->endSection(); ?>