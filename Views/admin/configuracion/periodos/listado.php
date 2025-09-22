<?php
$title = 'Periodos';
$currentModule = 'periodos';

require_once 'app/Views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Periodos</h2>
        <div>
            <a href="/periodos/crear" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Periodo
            </a>
            <a href="/periodos/estadisticas" class="btn btn-info">
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
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="search">Buscar:</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" placeholder="Descripción o fechas...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="fecha_inicio">Desde:</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                   value="<?= htmlspecialchars($fecha_inicio) ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="fecha_fin">Hasta:</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                                   value="<?= htmlspecialchars($fecha_fin) ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="estado">Estado:</label>
                            <select class="form-control" id="estado" name="estado">
                                <option value="">Todos</option>
                                <option value="1" <?= $estado === '1' ? 'selected' : '' ?>>Activos</option>
                                <option value="0" <?= $estado === '0' ? 'selected' : '' ?>>Inactivos</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-block">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <a href="/periodos" class="btn btn-secondary btn-block mt-1">
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

    <!-- Tabla de periodos -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Listado de Periodos</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($result['data'])): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Descripción</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Duración</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['data'] as $periodo): ?>
                                <?php
                                // Calcular duración
                                $inicio = new DateTime($periodo['periodo_fechainicio']);
                                $fin = new DateTime($periodo['periodo_fechafin']);
                                $duracion = $inicio->diff($fin)->days + 1;
                                
                                // Determinar si es periodo actual
                                $today = new DateTime();
                                $isActual = $today >= $inicio && $today <= $fin;
                                ?>
                                <tr class="<?= $isActual && $periodo['periodo_estado'] == 1 ? 'table-warning' : '' ?>">
                                    <td><?= $periodo['id_periodo'] ?></td>
                                    <td>
                                        <?= htmlspecialchars($periodo['periodo_descripcion']) ?>
                                        <?php if ($isActual && $periodo['periodo_estado'] == 1): ?>
                                            <span class="badge badge-warning ml-2">ACTUAL</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($periodo['periodo_fechainicio'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($periodo['periodo_fechafin'])) ?></td>
                                    <td><?= $duracion ?> días</td>
                                    <td>
                                        <?php if ($periodo['periodo_estado'] == 1): ?>
                                            <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="/periodos/editar/<?= $periodo['id_periodo'] ?>" 
                                               class="btn btn-sm btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <?php if ($periodo['periodo_estado'] == 1): ?>
                                                <a href="/periodos/eliminar/<?= $periodo['id_periodo'] ?>" 
                                                   class="btn btn-sm btn-danger" title="Desactivar"
                                                   data-action="confirmar-desactivar-periodo"
                                                   data-id="<?= $periodo['id_periodo'] ?>">
                                                    <i class="fas fa-ban"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="/periodos/restaurar/<?= $periodo['id_periodo'] ?>" 
                                                   class="btn btn-sm btn-success" title="Activar"
                                                   data-action="confirmar-activar-periodo"
                                                   data-id="<?= $periodo['id_periodo'] ?>">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="/periodos/toggle/<?= $periodo['id_periodo'] ?>" 
                                               class="btn btn-sm btn-warning" title="Cambiar Estado"
                                               data-action="confirmar-cambiar-estado"
                                               data-id="<?= $periodo['id_periodo'] ?>">
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
                                <a class="page-link" href="?page=<?= $result['pagination']['current_page'] - 1 ?>&search=<?= urlencode($search) ?>&fecha_inicio=<?= urlencode($fecha_inicio) ?>&fecha_fin=<?= urlencode($fecha_fin) ?>&estado=<?= urlencode($estado) ?>">
                                    Anterior
                                </a>
                            </li>
                            
                            <?php for ($i = max(1, $result['pagination']['current_page'] - 2); $i <= min($totalPages, $result['pagination']['current_page'] + 2); $i++): ?>
                                <li class="page-item <?= $i == $result['pagination']['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&fecha_inicio=<?= urlencode($fecha_inicio) ?>&fecha_fin=<?= urlencode($fecha_fin) ?>&estado=<?= urlencode($estado) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?= $result['pagination']['current_page'] >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $result['pagination']['current_page'] + 1 ?>&search=<?= urlencode($search) ?>&fecha_inicio=<?= urlencode($fecha_inicio) ?>&fecha_fin=<?= urlencode($fecha_fin) ?>&estado=<?= urlencode($estado) ?>">
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
                    <i class="fas fa-info-circle"></i> No se encontraron periodos con los filtros especificados.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'app/Views/layouts/footer.php'; ?>