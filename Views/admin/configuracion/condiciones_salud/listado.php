<?php
$title = 'Condiciones de Salud';
$currentModule = 'condiciones_salud';

require_once 'app/Views/layouts/header.php';
?>

<div class="container mt-4">
    <!-- Título y botones de acción -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-heart-pulse"></i> Condiciones de Salud</h2>
                <div>
                    <a href="/condiciones-salud/stats" class="btn btn-info">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </a>
                    <a href="/condiciones-salud/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Condición
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes flash -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php 
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        ?>
    <?php endif; ?>

    <!-- Filtros de búsqueda -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-search"></i> Búsqueda y Filtros
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-4 mb-3">
                    <label for="condicionsalud_descripcion">Descripción:</label>
                    <input type="text" 
                           class="form-control" 
                           id="condicionsalud_descripcion" 
                           name="condicionsalud_descripcion" 
                           value="<?= htmlspecialchars($filters['condicionsalud_descripcion']) ?>"
                           placeholder="Buscar por descripción...">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="condicionsalud_estado">Estado:</label>
                    <select class="form-control" id="condicionsalud_estado" name="condicionsalud_estado">
                        <option value="">Todos los estados</option>
                        <option value="1" <?= $filters['condicionsalud_estado'] === '1' ? 'selected' : '' ?>>Activo</option>
                        <option value="0" <?= $filters['condicionsalud_estado'] === '0' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="limit">Registros por página:</label>
                    <select class="form-control" name="limit">
                        <option value="10" <?= ($pagination['per_page'] ?? 10) == 10 ? 'selected' : '' ?>>10</option>
                        <option value="25" <?= ($pagination['per_page'] ?? 10) == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= ($pagination['per_page'] ?? 10) == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= ($pagination['per_page'] ?? 10) == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
                
                <div class="col-md-2 mb-3">
                    <label>&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de resultados -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list"></i> 
                Condiciones Registradas
                <?php if ($pagination['total_records'] > 0): ?>
                    <span class="badge badge-primary"><?= $pagination['total_records'] ?></span>
                <?php endif; ?>
            </h5>
            
            <!-- Botones de exportar y acciones -->
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-download"></i> Exportar
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/condiciones-salud/export?format=csv">
                        <i class="fas fa-file-csv"></i> CSV
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <?php if (!empty($condiciones)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>
                                    <a href="?order_by=id_condicionsalud&order_dir=<?= $orderBy === 'id_condicionsalud' && $orderDir === 'ASC' ? 'DESC' : 'ASC' ?><?= http_build_query(array_merge($_GET, ['order_by' => 'id_condicionsalud', 'order_dir' => $orderBy === 'id_condicionsalud' && $orderDir === 'ASC' ? 'DESC' : 'ASC']), '', '&') ?>" 
                                       class="text-white text-decoration-none">
                                        ID
                                        <?php if ($orderBy === 'id_condicionsalud'): ?>
                                            <i class="fas fa-sort-<?= $orderDir === 'ASC' ? 'up' : 'down' ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?order_by=condicionsalud_descripcion&order_dir=<?= $orderBy === 'condicionsalud_descripcion' && $orderDir === 'ASC' ? 'DESC' : 'ASC' ?><?= http_build_query(array_merge($_GET, ['order_by' => 'condicionsalud_descripcion', 'order_dir' => $orderBy === 'condicionsalud_descripcion' && $orderDir === 'ASC' ? 'DESC' : 'ASC']), '', '&') ?>" 
                                       class="text-white text-decoration-none">
                                        Descripción
                                        <?php if ($orderBy === 'condicionsalud_descripcion'): ?>
                                            <i class="fas fa-sort-<?= $orderDir === 'ASC' ? 'up' : 'down' ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?order_by=condicionsalud_estado&order_dir=<?= $orderBy === 'condicionsalud_estado' && $orderDir === 'ASC' ? 'DESC' : 'ASC' ?><?= http_build_query(array_merge($_GET, ['order_by' => 'condicionsalud_estado', 'order_dir' => $orderBy === 'condicionsalud_estado' && $orderDir === 'ASC' ? 'DESC' : 'ASC']), '', '&') ?>" 
                                       class="text-white text-decoration-none">
                                        Estado
                                        <?php if ($orderBy === 'condicionsalud_estado'): ?>
                                            <i class="fas fa-sort-<?= $orderDir === 'ASC' ? 'up' : 'down' ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th>Tipo</th>
                                <th width="200">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($condiciones as $condicion): ?>
                                <?php
                                // Determinar si es una condición crítica
                                $descripcionLower = strtolower($condicion['condicionsalud_descripcion']);
                                $esCritica = false;
                                $palabrasCriticas = ['alergia', 'alergico', 'diabetes', 'diabetico', 'cardiaco', 'corazon', 
                                                   'epilepsia', 'epileptico', 'asma', 'asmatico', 'hipertension', 'presion'];
                                
                                foreach ($palabrasCriticas as $palabra) {
                                    if (strpos($descripcionLower, $palabra) !== false) {
                                        $esCritica = true;
                                        break;
                                    }
                                }
                                ?>
                                <tr <?= $esCritica ? 'class="table-warning"' : '' ?>>
                                    <td>
                                        <strong><?= $condicion['id_condicionsalud'] ?></strong>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($esCritica): ?>
                                                <i class="fas fa-exclamation-triangle text-warning mr-2" title="Condición Crítica"></i>
                                            <?php endif; ?>
                                            <div>
                                                <strong><?= htmlspecialchars($condicion['condicionsalud_descripcion']) ?></strong>
                                                <?php if ($esCritica): ?>
                                                    <br><small class="text-warning">⚠️ Requiere atención especial</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($condicion['condicionsalud_estado'] == 1): ?>
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
                                        <?php if ($esCritica): ?>
                                            <span class="badge badge-warning">
                                                <i class="fas fa-exclamation-triangle"></i> Crítica
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-info">
                                                <i class="fas fa-info-circle"></i> Estándar
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- Ver detalles -->
                                            <a href="/condiciones-salud/<?= $condicion['id_condicionsalud'] ?>" 
                                               class="btn btn-outline-info btn-sm" 
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <!-- Editar -->
                                            <a href="/condiciones-salud/<?= $condicion['id_condicionsalud'] ?>/edit" 
                                               class="btn btn-outline-primary btn-sm" 
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <!-- Cambiar estado -->
                                            <a href="/condiciones-salud/<?= $condicion['id_condicionsalud'] ?>/toggle-status" 
                                               class="btn btn-outline-<?= $condicion['condicionsalud_estado'] == 1 ? 'warning' : 'success' ?> btn-sm" 
                                               title="<?= $condicion['condicionsalud_estado'] == 1 ? 'Desactivar' : 'Activar' ?>"
                                               data-action="confirm-delete" 
                                               data-message="¿Está seguro de <?= $condicion['condicionsalud_estado'] == 1 ? 'desactivar' : 'activar' ?> esta condición de salud?">>
                                                <i class="fas fa-<?= $condicion['condicionsalud_estado'] == 1 ? 'eye-slash' : 'eye' ?>"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Mostrando <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?> a 
                            <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total_records']) ?> 
                            de <?= $pagination['total_records'] ?> registros
                        </div>
                        
                        <nav aria-label="Navegación de páginas">
                            <ul class="pagination pagination-sm mb-0">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?><?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1]), '', '&') ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php
                                $start_page = max(1, $pagination['current_page'] - 2);
                                $end_page = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                ?>

                                <?php if ($start_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=1<?= http_build_query($_GET, '', '&') ?>">1</a>
                                    </li>
                                    <?php if ($start_page > 2): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?><?= http_build_query(array_merge($_GET, ['page' => $i]), '', '&') ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($end_page < $pagination['total_pages']): ?>
                                    <?php if ($end_page < $pagination['total_pages'] - 1): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $pagination['total_pages'] ?><?= http_build_query(array_merge($_GET, ['page' => $pagination['total_pages']]), '', '&') ?>"><?= $pagination['total_pages'] ?></a>
                                    </li>
                                <?php endif; ?>

                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?><?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1]), '', '&') ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-heart-pulse fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No se encontraron condiciones de salud</h5>
                    <p class="text-muted">
                        <?php if (!empty(array_filter($filters))): ?>
                            No hay resultados que coincidan con los filtros aplicados.
                            <br><a href="/condiciones-salud" class="btn btn-link">Limpiar filtros</a>
                        <?php else: ?>
                            Aún no hay condiciones de salud registradas en el sistema.
                        <?php endif; ?>
                    </p>
                    <a href="/condiciones-salud/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Registrar Primera Condición
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle"></i> Información sobre Condiciones de Salud</h6>
                <ul class="mb-0 pl-3">
                    <li><strong>Condiciones Críticas:</strong> Se marcan automáticamente cuando contienen palabras contrasenia de condiciones que requieren atención especial.</li>
                    <li><strong>Estado Activo:</strong> Solo las condiciones activas están disponibles para asignar a huéspedes.</li>
                    <li><strong>Búsqueda:</strong> Puede filtrar por descripción y estado para encontrar condiciones específicas.</li>
                    <li><strong>Gestión:</strong> Use las acciones para ver detalles, editar o cambiar el estado de las condiciones.</li>
                </ul>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Accesos Rápidos</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/condiciones-salud/stats" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-chart-bar"></i> Ver Estadísticas
                        </a>
                        <a href="/condiciones-salud/create" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus"></i> Nueva Condición
                        </a>
                        <a href="/condiciones-salud/export" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-download"></i> Exportar Lista
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/Views/layouts/footer.php'; ?>