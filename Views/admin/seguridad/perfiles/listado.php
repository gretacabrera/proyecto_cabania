<?php
$this->extend('layouts/main');
$this->section('title', $title);
$this->section('content');
?>

<div class="admin-header">
    <h1><?= $title ?></h1>
    <div class="header-actions">
        <a href="/perfiles/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Perfil
        </a>
        <a href="/perfiles-modulos" class="btn btn-info">
            <i class="fas fa-key"></i> Gestionar Permisos
        </a>
    </div>
</div>

<!-- Filtros de búsqueda -->
<div class="search-filters">
    <form method="GET" action="/perfiles" class="filters-form">
        <div class="filter-row">
            <div class="filter-group">
                <label for="descripcion">Descripción:</label>
                <input type="text" name="descripcion" id="descripcion" 
                       value="<?= $filters['descripcion'] ?? '' ?>" 
                       placeholder="Buscar por descripción...">
            </div>
            
            <div class="filter-group">
                <label for="estado">Estado:</label>
                <select name="estado" id="estado">
                    <option value="">Todos los estados</option>
                    <option value="1" <?= ($filters['estado'] ?? '') === '1' ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= ($filters['estado'] ?? '') === '0' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <a href="/perfiles" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </div>
    </form>
</div>

<?php if (empty($perfiles)): ?>
    <div class="no-results">
        <i class="fas fa-users-cog"></i>
        <h3>No se encontraron perfiles</h3>
        <p>No hay perfiles que coincidan con los filtros especificados.</p>
        <a href="/perfiles/create" class="btn btn-primary">Crear Primer Perfil</a>
    </div>
<?php else: ?>
    <div class="table-container">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Usuarios Asignados</th>
                    <th>Módulos con Acceso</th>
                    <th>Estado</th>
                    <th>Fecha Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($perfiles as $perfil): ?>
                    <tr class="<?= $perfil['perfil_estado'] ? '' : 'table-row-disabled' ?> <?= $perfil['sistema'] ? 'system-profile' : '' ?>">
                        <td>
                            <div class="profile-info">
                                <strong class="profile-name">
                                    <?php if ($perfil['sistema'] ?? false): ?>
                                        <i class="fas fa-shield-alt text-warning" title="Perfil del sistema"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($perfil['perfil_descripcion']) ?>
                                </strong>
                                <?php if (!empty($perfil['perfil_observaciones'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($perfil['perfil_observaciones']) ?></small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="users-info">
                                <span class="badge badge-users">
                                    <i class="fas fa-users"></i>
                                    <?= $perfil['total_usuarios'] ?? 0 ?> usuario<?= ($perfil['total_usuarios'] ?? 0) != 1 ? 's' : '' ?>
                                </span>
                                <?php if (($perfil['total_usuarios'] ?? 0) > 0): ?>
                                    <a href="/usuarios?perfil=<?= $perfil['id_perfil'] ?>" class="view-users-link">
                                        Ver usuarios
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="modules-info">
                                <?php if (($perfil['total_modulos'] ?? 0) > 0): ?>
                                    <span class="badge badge-modules">
                                        <i class="fas fa-puzzle-piece"></i>
                                        <?= $perfil['total_modulos'] ?> módulo<?= $perfil['total_modulos'] != 1 ? 's' : '' ?>
                                    </span>
                                    <a href="/perfiles-modulos?perfil=<?= $perfil['id_perfil'] ?>" class="manage-permissions-link">
                                        Gestionar permisos
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">
                                        <i class="fas fa-ban"></i> Sin permisos
                                    </span>
                                    <a href="/perfiles-modulos/create?perfil=<?= $perfil['id_perfil'] ?>" class="assign-permissions-link">
                                        Asignar permisos
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($perfil['perfil_estado']): ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle"></i> Activo
                                </span>
                            <?php else: ?>
                                <span class="badge badge-danger">
                                    <i class="fas fa-times-circle"></i> Inactivo
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="date-cell">
                                <?= !empty($perfil['perfil_fecha_creacion']) 
                                    ? date('d/m/Y', strtotime($perfil['perfil_fecha_creacion'])) 
                                    : '-' ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="/perfiles/<?= $perfil['id_perfil'] ?>" 
                                   class="btn btn-sm btn-info" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if (!($perfil['sistema'] ?? false)): ?>
                                    <a href="/perfiles/<?= $perfil['id_perfil'] ?>/edit" 
                                       class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <a href="/perfiles-modulos?perfil=<?= $perfil['id_perfil'] ?>" 
                                   class="btn btn-sm btn-secondary" title="Gestionar permisos">
                                    <i class="fas fa-key"></i>
                                </a>
                                
                                <?php if ($perfil['perfil_estado']): ?>
                                    <?php if (!($perfil['sistema'] ?? false) && $this->userCan('perfiles_delete')): ?>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                data-action="confirmar-eliminar-perfil" 
                                                data-id="<?= $perfil['id_perfil'] ?>" 
                                                data-descripcion="<?= htmlspecialchars($perfil['perfil_descripcion']) ?>" 
                                                data-total-usuarios="<?= $perfil['total_usuarios'] ?? 0 ?>"
                                                title="Eliminar"
                                                <?= ($perfil['total_usuarios'] ?? 0) > 0 ? 'disabled' : '' ?>>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if ($this->userCan('perfiles_restore')): ?>
                                        <button type="button" class="btn btn-sm btn-success" 
                                                data-action="confirmar-restaurar-perfil" 
                                                data-id="<?= $perfil['id_perfil'] ?>" 
                                                data-descripcion="<?= htmlspecialchars($perfil['perfil_descripcion']) ?>"
                                                title="Restaurar">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Estadísticas -->
    <div class="table-summary">
        <?php 
        $totalPerfiles = count($perfiles);
        $totalActivos = count(array_filter($perfiles, function($p) { return $p['perfil_estado']; }));
        $totalInactivos = $totalPerfiles - $totalActivos;
        $totalUsuarios = array_sum(array_column($perfiles, 'total_usuarios'));
        ?>
        <div class="summary-item">
            <strong>Total perfiles:</strong> <?= $totalPerfiles ?>
        </div>
        <div class="summary-item">
            <strong>Activos:</strong> <span class="text-success"><?= $totalActivos ?></span>
        </div>
        <div class="summary-item">
            <strong>Inactivos:</strong> <span class="text-danger"><?= $totalInactivos ?></span>
        </div>
        <div class="summary-item">
            <strong>Usuarios totales:</strong> <?= $totalUsuarios ?>
        </div>
    </div>

    <!-- Paginación -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination-container">
            <nav aria-label="Paginación de perfiles">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <?php 
                            $url = '/perfiles?page=' . $i;
                            foreach ($filters as $key => $value) {
                                if (!empty($value)) {
                                    $url .= '&' . $key . '=' . urlencode($value);
                                }
                            }
                            ?>
                            <a class="page-link" href="<?= $url ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php $this->endSection(); ?>