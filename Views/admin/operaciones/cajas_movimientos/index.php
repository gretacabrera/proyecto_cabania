<div class="container-fluid">
    <?php if (isset($sinCaja) && $sinCaja): ?>
        <!-- Sin Caja Asignada -->
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body py-5">
                        <i class="fas fa-exclamation-triangle fa-4x text-warning mb-4"></i>
                        <h4 class="mb-3">No tiene una caja asignada</h4>
                        <p class="text-muted mb-4">
                            Para registrar movimientos de caja, primero debe tener una caja asignada por el administrador.
                        </p>
                        <a href="<?= url('/') ?>" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i> Ir al Inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif (isset($sinTurno) && $sinTurno): ?>
        <!-- Sin Turno Abierto -->
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body py-5">
                        <i class="fas fa-lock fa-4x text-danger mb-4"></i>
                        <h4 class="mb-3">No hay turno abierto</h4>
                        <p class="text-muted mb-4">
                            Para registrar y ver movimientos de caja, primero debe abrir un turno.
                        </p>
                        <div class="alert alert-info mb-4">
                            <strong><i class="fas fa-info-circle me-2"></i>Caja asignada:</strong> 
                            <?= htmlspecialchars($caja['caja_descripcion']) ?>
                        </div>
                        <a href="<?= url('/aperturas') ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-unlock me-2"></i> Abrir Caja
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
    <!-- Encabezado -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header text-dark py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0"><i class="fas fa-exchange-alt me-2"></i> Movimientos de Caja</h4>
                    <small class="text-muted">
                        Turno actual - <?= htmlspecialchars($caja['caja_descripcion']) ?>
                    </small>
                </div>
                <div class="col-auto">
                    <a href="<?= url('/movimientos/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Nuevo Movimiento
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 mr-4">
                            <i class="fas fa-list fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small text-muted"> Total Movimientos</div>
                            <div class="h5 mb-0"><?= $estadisticas['total_movimientos'] ?? 0 ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 mr-4">
                            <i class="fas fa-arrow-down fa-2x text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small text-muted"> Total Ingresos</div>
                            <div class="h5 mb-0 text-success">$ <?= number_format($estadisticas['total_ingresos'] ?? 0, 2, ',', '.') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 mr-4">
                            <i class="fas fa-arrow-up fa-2x text-danger"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small text-muted"> Total Egresos</div>
                            <div class="h5 mb-0 text-danger">$ <?= number_format($estadisticas['total_egresos'] ?? 0, 2, ',', '.') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 mr-4">
                            <i class="fas fa-balance-scale fa-2x text-info"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small text-muted"> Balance</div>
                            <div class="h5 mb-0 text-info">$ <?= number_format(($estadisticas['total_ingresos'] ?? 0) - ($estadisticas['total_egresos'] ?? 0), 2, ',', '.') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros simplificados -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?= url('/movimientos') ?>" id="formFiltros">
                <div class="row g-3 align-items-end">
                    <div class="col-md-12">
                        <label class="form-label small text-muted mb-1">Filtrar por tipo</label>
                        <select name="tipo" class="form-select form-select-sm" id="selectTipo" onchange="document.getElementById('formFiltros').submit()">
                            <option value="">Todos los movimientos</option>
                            <option value="I" <?= ($filters['tipo'] ?? '') === 'I' ? 'selected' : '' ?>>Solo Ingresos</option>
                            <option value="E" <?= ($filters['tipo'] ?? '') === 'E' ? 'selected' : '' ?>>Solo Egresos</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Listado de Movimientos -->
    <div class="card border-0 shadow-sm">
        <?php 
        $perPage = (int) ($_GET['per_page'] ?? 10);
        $start = (($pagination['current_page'] - 1) * $perPage) + 1;
        $end = min($pagination['current_page'] * $perPage, $pagination['total']);
        ?>

        <?php
        $renderPagination = function($showInfo = true) use ($pagination, $start, $end) {
        ?>
            <div class="row align-items-center">
                <?php if ($showInfo): ?>
                    <div class="col-sm-6">
                        <span class="text-muted small">
                            Mostrando <?= $start ?> a <?= $end ?> de <?= $pagination['total'] ?> registros
                        </span>
                    </div>
                <?php endif; ?>
                
                <div class="col-sm-<?= $showInfo ? '6' : '12' ?>">
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Paginación" class="d-flex justify-content-<?= $showInfo ? 'end' : 'center' ?>">
                            <ul class="pagination pagination-sm mb-0">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) ?>">Anterior</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php 
                                $startPage = max(1, $pagination['current_page'] - 2);
                                $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                
                                if ($startPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
                                    </li>
                                    <?php if ($startPage > 2): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                        <?php if ($i == $pagination['current_page']): ?>
                                            <span class="page-link bg-primary text-white border-primary"><?= $i ?></span>
                                        <?php else: ?>
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                        <?php endif; ?>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($endPage < $pagination['total_pages']): ?>
                                    <?php if ($endPage < $pagination['total_pages'] - 1): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['total_pages']])) ?>"><?= $pagination['total_pages'] ?></a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])) ?>">Siguiente</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        <?php }; ?>

        <?php if (isset($pagination) && $pagination['total'] > 0): ?>
            <div class="card-header bg-light border-bottom py-2">
                <?php $renderPagination(true); ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="border-0 py-3">Fecha/Hora</th>
                        <th class="border-0 py-3">Descripción</th>
                        <th class="border-0 py-3">Tipo</th>
                        <th class="border-0 py-3">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movimientos)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">No hay movimientos registrados</p>
                                <a href="<?= url('/movimientos/create') ?>" class="btn btn-sm btn-primary mt-3">
                                    <i class="fas fa-plus me-2"></i> Registrar Primer Movimiento
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($movimientos as $mov): ?>
                            <tr>
                                <td class="border-0 py-3">
                                    <?= date('d/m/Y H:i', strtotime($mov['cajamovimiento_fechahora'])) ?>
                                </td>
                                <td class="border-0 py-3">
                                    <?= htmlspecialchars($mov['cajamovimiento_descripcion']) ?>
                                </td>
                                <td class="border-0 py-3">
                                    <?php if ($mov['cajamovimiento_tipo'] === 'I'): ?>
                                        <span class="badge text-white bg-success"><i class="fas fa-arrow-down me-1"></i>Ingreso</span>
                                    <?php else: ?>
                                        <span class="badge text-white bg-danger"><i class="fas fa-arrow-up me-1"></i>Egreso</span>
                                    <?php endif; ?>
                                </td>
                                <td class="border-0 py-3">
                                    <span class="fw-bold <?= $mov['cajamovimiento_tipo'] === 'I' ? 'text-success' : 'text-danger' ?>">
                                        <?= $mov['cajamovimiento_tipo'] === 'I' ? '+' : '-' ?>$<?= number_format($mov['cajamovimiento_monto'], 2, ',', '.') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($pagination) && $pagination['total'] > 0): ?>
            <div class="card-footer bg-white border-top py-3">
                <?php $renderPagination(true); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
