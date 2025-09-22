<?php
/**
 * Vista de listado de menús
 */

$title = $data['title'] ?? 'Gestión de Menús';
$menus = $data['menus'] ?? [];
$currentPage = $data['currentPage'] ?? 1;
$totalPages = $data['totalPages'] ?? 1;
$search = $data['search'] ?? '';
$orderBy = $data['orderBy'] ?? 'menu_orden';
$orderDir = $data['orderDir'] ?? 'ASC';
$stats = $data['stats'] ?? [];

?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><?php echo htmlspecialchars($title); ?></h1>
                    <p class="text-muted">Gestionar menús del sistema</p>
                </div>
                <div>
                    <a href="/menus/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Menú
                    </a>
                    <a href="/menus/reorder" class="btn btn-info">
                        <i class="fas fa-sort"></i> Reordenar
                    </a>
                    <a href="/menus/stats" class="btn btn-outline-secondary">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <?php if (!empty($stats)): ?>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="card-text">Total</p>
                            <h4><?php echo $stats['total'] ?? 0; ?></h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-bars fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="card-text">Activos</p>
                            <h4><?php echo $stats['activos'] ?? 0; ?></h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="card-text">Inactivos</p>
                            <h4><?php echo $stats['inactivos'] ?? 0; ?></h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="card-text">Promedio Orden</p>
                            <h4><?php echo number_format($stats['promedio_orden'] ?? 0, 1); ?></h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-sort-numeric-down fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filtros y búsqueda -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="buscar" class="form-label">Buscar menú</label>
                    <input type="text" class="form-control" id="buscar" name="buscar" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Nombre del menú...">
                </div>
                <div class="col-md-3">
                    <label for="orderBy" class="form-label">Ordenar por</label>
                    <select class="form-select" id="orderBy" name="orderBy">
                        <option value="menu_orden" <?php echo $orderBy === 'menu_orden' ? 'selected' : ''; ?>>Orden</option>
                        <option value="menu_nombre" <?php echo $orderBy === 'menu_nombre' ? 'selected' : ''; ?>>Nombre</option>
                        <option value="id_menu" <?php echo $orderBy === 'id_menu' ? 'selected' : ''; ?>>ID</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="orderDir" class="form-label">Dirección</label>
                    <select class="form-select" id="orderDir" name="orderDir">
                        <option value="ASC" <?php echo $orderDir === 'ASC' ? 'selected' : ''; ?>>Ascendente</option>
                        <option value="DESC" <?php echo $orderDir === 'DESC' ? 'selected' : ''; ?>>Descendente</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <a href="/menus" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de menús -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                Listado de Menús
                <?php if ($search): ?>
                    <small class="text-muted">- Resultados para: "<?php echo htmlspecialchars($search); ?>"</small>
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($menus)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Orden</th>
                            <th>Nombre</th>
                            <th>Estado</th>
                            <th>Módulos</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menus as $menu): ?>
                        <tr>
                            <td>
                                <code>#<?php echo $menu['id_menu']; ?></code>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo $menu['menu_orden']; ?>
                                </span>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($menu['menu_nombre']); ?></strong>
                            </td>
                            <td>
                                <?php if ($menu['menu_estado'] == 1): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="fas fa-cube"></i> Ver módulos asociados
                                </small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="/menus/<?php echo $menu['id_menu']; ?>" 
                                       class="btn btn-outline-info btn-sm" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/menus/<?php echo $menu['id_menu']; ?>/edit" 
                                       class="btn btn-outline-primary btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($menu['menu_estado'] == 1): ?>
                                        <a href="/menus/<?php echo $menu['id_menu']; ?>/delete" 
                                           class="btn btn-outline-danger btn-sm" 
                                           data-action="desactivar-menu" data-menu-id="<?php echo $menu['id_menu']; ?>" 
                                           title="Desactivar">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="/menus/<?php echo $menu['id_menu']; ?>/restore" 
                                           class="btn btn-outline-success btn-sm" 
                                           data-action="activar-menu" data-menu-id="<?php echo $menu['id_menu']; ?>" 
                                           title="Reactivar">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-between align-items-center p-3">
                <div>
                    <small class="text-muted">
                        Página <?php echo $currentPage; ?> de <?php echo $totalPages; ?>
                    </small>
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage - 1])); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $currentPage - 2);
                        $end = min($totalPages, $currentPage + 2);
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                            <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage + 1])); ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-bars fa-3x text-muted mb-3"></i>
                <h5>No hay menús disponibles</h5>
                <p class="text-muted">
                    <?php if ($search): ?>
                        No se encontraron menús que coincidan con su búsqueda.
                    <?php else: ?>
                        Comience creando el primer menú del sistema.
                    <?php endif; ?>
                </p>
                <?php if (!$search): ?>
                    <a href="/menus/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Primer Menú
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
.badge {
    font-size: 0.75em;
}

.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0.25rem;
    border-bottom-left-radius: 0.25rem;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0.25rem;
    border-bottom-right-radius: 0.25rem;
}

.table th {
    border-top: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.card-header h5 {
    color: #495057;
    font-weight: 600;
}

.pagination-sm .page-link {
    padding: 0.375rem 0.75rem;
}