<?php
$title = 'Estados de Reservas';
$currentModule = 'estados_reservas';

require_once 'app/Views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Estados de Reservas</h2>
        <div>
            <a href="/estados-reservas/crear" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Estado
            </a>
            <a href="/estados-reservas/estadisticas" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Estadísticas
            </a>
        </div>
    </div>

    <!-- Filtros de búsqueda -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filtros de búsqueda</h5>
        </div>
        <div class="card-body">
            <form method="GET" id="searchForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="search">Buscar por descripción:</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" placeholder="Buscar estado...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="estado">Estado:</label>
                            <select class="form-control" id="estado" name="estado">
                                <option value="">Todos</option>
                                <option value="1" <?= $estado === '1' ? 'selected' : '' ?>>Activos</option>
                                <option value="0" <?= $estado === '0' ? 'selected' : '' ?>>Inactivos</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-block">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <a href="/estados-reservas" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Mensajes -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Tabla de estados -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Listado de Estados de Reservas</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($result['data'])): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['data'] as $estado): ?>
                                <tr>
                                    <td><?= $estado['id_estadoreserva'] ?></td>
                                    <td><?= htmlspecialchars($estado['estadoreserva_descripcion']) ?></td>
                                    <td>
                                        <?php if ($estado['estadoreserva_estado'] == 1): ?>
                                            <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="/estados-reservas/editar/<?= $estado['id_estadoreserva'] ?>" 
                                               class="btn btn-sm btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <?php if ($estado['estadoreserva_estado'] == 1): ?>
                                                <a href="/estados-reservas/eliminar/<?= $estado['id_estadoreserva'] ?>" 
                                                   class="btn btn-sm btn-danger" title="Desactivar"
                                                   data-action="deactivate-estado-reserva" data-estado-id="<?= $estado['id_estadoreserva'] ?>">
                                                    <i class="fas fa-ban"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="/estados-reservas/restaurar/<?= $estado['id_estadoreserva'] ?>" 
                                                   class="btn btn-sm btn-success" title="Activar"
                                                   data-action="activate-estado-reserva" data-estado-id="<?= $estado['id_estadoreserva'] ?>">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="/estados-reservas/toggle/<?= $estado['id_estadoreserva'] ?>" 
                                               class="btn btn-sm btn-warning" title="Cambiar Estado"
                                               data-action="toggle-estado-reserva" data-estado-id="<?= $estado['id_estadoreserva'] ?>">
                                                <i class="fas fa-exchange-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Navegación de páginas">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $result['pagination']['current_page'] <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $result['pagination']['current_page'] - 1 ?>&search=<?= urlencode($search) ?>&estado=<?= urlencode($estado) ?>">
                                    Anterior
                                </a>
                            </li>
                            
                            <?php for ($i = max(1, $result['pagination']['current_page'] - 2); $i <= min($totalPages, $result['pagination']['current_page'] + 2); $i++): ?>
                                <li class="page-item <?= $i == $result['pagination']['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&estado=<?= urlencode($estado) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?= $result['pagination']['current_page'] >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $result['pagination']['current_page'] + 1 ?>&search=<?= urlencode($search) ?>&estado=<?= urlencode($estado) ?>">
                                    Siguiente
                                </a>
                            </li>
                        </ul>
                    </nav>
                    
                    <div class="text-center text-muted">
                        Página <?= $result['pagination']['current_page'] ?> de <?= $totalPages ?> 
                        (<?= $result['pagination']['total_records'] ?> registros en total)
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No se encontraron estados de reservas con los filtros especificados.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'app/Views/layouts/footer.php'; ?>