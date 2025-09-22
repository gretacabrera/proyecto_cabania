<?php
$this->extend('layouts/main');
$this->section('title', $title);
$this->section('content');
?>

<div class="admin-header">
    <h1><?= $title ?></h1>
    <div class="header-actions">
        <a href="/estados-personas/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Estado
        </a>
    </div>
</div>

<!-- Filtros de búsqueda -->
<div class="search-filters">
    <form method="GET" action="/estados-personas" class="filters-form">
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
                <a href="/estados-personas" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </div>
    </form>
</div>

<?php if (empty($estados)): ?>
    <div class="no-results">
        <i class="fas fa-user-tag"></i>
        <h3>No se encontraron estados</h3>
        <p>No hay estados de personas que coincidan con los filtros especificados.</p>
        <a href="/estados-personas/create" class="btn btn-primary">Crear Primer Estado</a>
    </div>
<?php else: ?>
    <div class="table-container">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Personas Asignadas</th>
                    <th>Color Identificativo</th>
                    <th>Estado</th>
                    <th>Fecha Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($estados as $estado): ?>
                    <tr class="<?= $estado['estadopersona_estado'] ? '' : 'table-row-disabled' ?> <?= ($estado['sistema'] ?? false) ? 'system-state' : '' ?>">
                        <td>
                            <div class="state-info">
                                <strong class="state-name">
                                    <?php if ($estado['sistema'] ?? false): ?>
                                        <i class="fas fa-lock text-warning" title="Estado del sistema"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($estado['estadopersona_descripcion']) ?>
                                </strong>
                                <?php if (!empty($estado['estadopersona_observaciones'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($estado['estadopersona_observaciones']) ?></small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="usage-info">
                                <span class="badge badge-usage">
                                    <i class="fas fa-users"></i>
                                    <?= $estado['total_personas'] ?? 0 ?> persona<?= ($estado['total_personas'] ?? 0) != 1 ? 's' : '' ?>
                                </span>
                                <?php if (($estado['total_personas'] ?? 0) > 0): ?>
                                    <a href="/usuarios?estado_persona=<?= $estado['id_estadopersona'] ?>" class="view-people-link">
                                        Ver personas
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="color-display">
                                <?php if (!empty($estado['estadopersona_color'])): ?>
                                    <div class="color-sample" style="background-color: <?= $estado['estadopersona_color'] ?>" 
                                         title="<?= $estado['estadopersona_color'] ?>"></div>
                                    <code class="color-code"><?= $estado['estadopersona_color'] ?></code>
                                <?php else: ?>
                                    <span class="text-muted">Sin color</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($estado['estadopersona_estado']): ?>
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
                                <?= !empty($estado['estadopersona_fecha_creacion']) 
                                    ? date('d/m/Y', strtotime($estado['estadopersona_fecha_creacion'])) 
                                    : '-' ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="/estados-personas/<?= $estado['id_estadopersona'] ?>" 
                                   class="btn btn-sm btn-info" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if (!($estado['sistema'] ?? false)): ?>
                                    <a href="/estados-personas/<?= $estado['id_estadopersona'] ?>/edit" 
                                       class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($estado['estadopersona_estado']): ?>
                                    <?php if (!($estado['sistema'] ?? false) && $this->userCan('estados_personas_delete')): ?>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                data-estado-delete
                                                data-estado-id="<?= $estado['id_estadopersona'] ?>"
                                                data-estado-descripcion="<?= htmlspecialchars($estado['estadopersona_descripcion']) ?>"
                                                data-estado-total-personas="<?= $estado['total_personas'] ?? 0 ?>"
                                                title="Eliminar"
                                                <?= ($estado['total_personas'] ?? 0) > 0 ? 'disabled' : '' ?>>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if ($this->userCan('estados_personas_restore')): ?>
                                        <button type="button" class="btn btn-sm btn-success" 
                                                data-estado-restore
                                                data-estado-id="<?= $estado['id_estadopersona'] ?>"
                                                data-estado-descripcion="<?= htmlspecialchars($estado['estadopersona_descripcion']) ?>"
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
        $totalEstados = count($estados);
        $totalActivos = count(array_filter($estados, function($e) { return $e['estadopersona_estado']; }));
        $totalInactivos = $totalEstados - $totalActivos;
        $totalPersonas = array_sum(array_column($estados, 'total_personas'));
        ?>
        <div class="summary-item">
            <strong>Total estados:</strong> <?= $totalEstados ?>
        </div>
        <div class="summary-item">
            <strong>Activos:</strong> <span class="text-success"><?= $totalActivos ?></span>
        </div>
        <div class="summary-item">
            <strong>Inactivos:</strong> <span class="text-danger"><?= $totalInactivos ?></span>
        </div>
        <div class="summary-item">
            <strong>Personas clasificadas:</strong> <?= $totalPersonas ?>
        </div>
    </div>

    <!-- Vista de estados más utilizados -->
    <div class="popular-states">
        <h4>Estados más utilizados</h4>
        <div class="states-chart">
            <?php 
            $estadosOrdenados = $estados;
            usort($estadosOrdenados, function($a, $b) { 
                return ($b['total_personas'] ?? 0) - ($a['total_personas'] ?? 0); 
            });
            $maxPersonas = ($estadosOrdenados[0]['total_personas'] ?? 1);
            ?>
            
            <?php foreach (array_slice($estadosOrdenados, 0, 5) as $estado): ?>
                <div class="state-bar">
                    <div class="state-label">
                        <?php if (!empty($estado['estadopersona_color'])): ?>
                            <span class="state-color" style="background-color: <?= $estado['estadopersona_color'] ?>"></span>
                        <?php endif; ?>
                        <?= htmlspecialchars($estado['estadopersona_descripcion']) ?>
                    </div>
                    <div class="state-progress">
                        <div class="progress-bar" 
                             style="width: <?= $maxPersonas > 0 ? (($estado['total_personas'] ?? 0) / $maxPersonas * 100) : 0 ?>%; 
                                    background-color: <?= $estado['estadopersona_color'] ?? '#007bff' ?>">
                        </div>
                    </div>
                    <div class="state-count"><?= $estado['total_personas'] ?? 0 ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Paginación -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination-container">
            <nav aria-label="Paginación de estados">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <?php 
                            $url = '/estados-personas?page=' . $i;
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