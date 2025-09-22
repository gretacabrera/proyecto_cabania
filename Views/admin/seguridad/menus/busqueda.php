<?php
/**
 * Vista de búsqueda de menús
 */

$title = $data['title'] ?? 'Búsqueda de Menús';
$menus = $data['menus'] ?? [];
$currentPage = $data['currentPage'] ?? 1;
$totalPages = $data['totalPages'] ?? 1;
$totalRecords = $data['totalRecords'] ?? 0;
$search = $data['search'] ?? '';

?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><?php echo htmlspecialchars($title); ?></h1>
                    <p class="text-muted">
                        Resultados de búsqueda para: "<strong><?php echo htmlspecialchars($search); ?></strong>"
                    </p>
                </div>
                <div>
                    <a href="/menus" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Listado
                    </a>
                    <a href="/menus/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Menú
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de búsqueda -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <div class="d-flex align-items-center">
                    <i class="fas fa-search fa-2x me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Resultados de Búsqueda</h5>
                        <p class="mb-0">
                            Se encontraron <strong><?php echo $totalRecords; ?></strong> 
                            menú<?php echo $totalRecords !== 1 ? 's' : ''; ?> 
                            que coincide<?php echo $totalRecords === 1 ? '' : 'n'; ?> con 
                            "<strong><?php echo htmlspecialchars($search); ?></strong>"
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nueva búsqueda -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-search"></i>
                Realizar Nueva Búsqueda
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="/menus/search" class="row g-3">
                <div class="col-md-6">
                    <label for="q" class="form-label">Término de búsqueda</label>
                    <input type="text" 
                           class="form-control" 
                           id="q" 
                           name="q" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Buscar por nombre de menú..."
                           required>
                </div>
                <div class="col-md-3">
                    <label for="page" class="form-label">Página</label>
                    <input type="number" 
                           class="form-control" 
                           id="page" 
                           name="page" 
                           value="1" 
                           min="1"
                           max="<?php echo $totalPages; ?>">
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

    <!-- Resultados -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Resultados Encontrados</h5>
                <small class="text-muted">
                    Página <?php echo $currentPage; ?> de <?php echo $totalPages; ?>
                    (<?php echo $totalRecords; ?> resultado<?php echo $totalRecords !== 1 ? 's' : ''; ?>)
                </small>
            </div>
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
                            <th>Relevancia</th>
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
                                <strong>
                                    <?php 
                                    // Resaltar término de búsqueda
                                    $nombre = htmlspecialchars($menu['menu_nombre']);
                                    $searchEscaped = htmlspecialchars($search);
                                    $nombreResaltado = preg_replace(
                                        '/(' . preg_quote($searchEscaped, '/') . ')/i',
                                        '<mark>$1</mark>',
                                        $nombre
                                    );
                                    echo $nombreResaltado;
                                    ?>
                                </strong>
                            </td>
                            <td>
                                <?php if ($menu['menu_estado'] == 1): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                // Calcular relevancia basada en qué tan bien coincide la búsqueda
                                $nombre = strtolower($menu['menu_nombre']);
                                $searchLower = strtolower($search);
                                
                                if ($nombre === $searchLower) {
                                    $relevancia = 'Exacta';
                                    $badgeClass = 'bg-success';
                                } elseif (strpos($nombre, $searchLower) === 0) {
                                    $relevancia = 'Alta';
                                    $badgeClass = 'bg-primary';
                                } elseif (strpos($nombre, $searchLower) !== false) {
                                    $relevancia = 'Media';
                                    $badgeClass = 'bg-info';
                                } else {
                                    $relevancia = 'Baja';
                                    $badgeClass = 'bg-secondary';
                                }
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo $relevancia; ?></span>
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
                                           data-action="desactivar-menu"
                                           data-menu-id="<?php echo $menu['id_menu']; ?>"
                                           title="Desactivar">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="/menus/<?php echo $menu['id_menu']; ?>/restore" 
                                           class="btn btn-outline-success btn-sm" 
                                           data-action="activar-menu"
                                           data-menu-id="<?php echo $menu['id_menu']; ?>"
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
            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                <div>
                    <small class="text-muted">
                        Página <?php echo $currentPage; ?> de <?php echo $totalPages; ?>
                        (<?php echo $totalRecords; ?> resultado<?php echo $totalRecords !== 1 ? 's' : ''; ?>)
                    </small>
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" 
                                   href="/menus/search?q=<?php echo urlencode($search); ?>&page=<?php echo $currentPage - 1; ?>">
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
                                <a class="page-link" 
                                   href="/menus/search?q=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" 
                                   href="/menus/search?q=<?php echo urlencode($search); ?>&page=<?php echo $currentPage + 1; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <!-- Sin resultados -->
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5>No se encontraron resultados</h5>
                <p class="text-muted">
                    No se encontraron menús que coincidan con 
                    "<strong><?php echo htmlspecialchars($search); ?></strong>"
                </p>
                <div class="mt-4">
                    <h6 class="text-muted mb-3">Sugerencias:</h6>
                    <ul class="list-unstyled text-muted">
                        <li><i class="fas fa-check text-success me-2"></i>Verifique la ortografía</li>
                        <li><i class="fas fa-check text-success me-2"></i>Intente con términos más generales</li>
                        <li><i class="fas fa-check text-success me-2"></i>Use solo palabras clave</li>
                    </ul>
                </div>
                <div class="mt-4">
                    <a href="/menus" class="btn btn-outline-primary me-2">
                        <i class="fas fa-list"></i> Ver Todos los Menús
                    </a>
                    <a href="/menus/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Nuevo Menú
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($menus)): ?>
    <!-- Acciones rápidas -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt"></i>
                        Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="/menus" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-list"></i> Ver Todos
                        </a>
                        <a href="/menus/stats" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-chart-bar"></i> Estadísticas
                        </a>
                        <a href="/menus/reorder" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-sort"></i> Reordenar
                        </a>
                        <a href="/menus/create" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nuevo Menú
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>


<?php require_once 'Views/layouts/footer.php'; ?>