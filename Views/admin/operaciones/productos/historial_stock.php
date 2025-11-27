<?php
$perPage = (int) ($_GET['per_page'] ?? 10);
$start = (($pagination['current_page'] - 1) * $perPage) + 1;
$end = min($pagination['current_page'] * $perPage, $pagination['total']);

// Construir parámetros base para mantener en todas las URLs
$baseParams = [];
if (!empty($filters['fecha_desde'])) $baseParams['fecha_desde'] = $filters['fecha_desde'];
if (!empty($filters['fecha_hasta'])) $baseParams['fecha_hasta'] = $filters['fecha_hasta'];
if (!empty($filters['tipo'])) $baseParams['tipo'] = $filters['tipo'];
if (isset($filters['cantidad_min']) && $filters['cantidad_min'] !== '') $baseParams['cantidad_min'] = $filters['cantidad_min'];
if (isset($filters['cantidad_max']) && $filters['cantidad_max'] !== '') $baseParams['cantidad_max'] = $filters['cantidad_max'];
if (isset($_GET['per_page'])) $baseParams['per_page'] = $_GET['per_page'];

// Función para renderizar paginación
$renderPagination = function($showInfo = true) use ($pagination, $start, $end, $baseParams) {
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
                                <a class="page-link" href="?<?= http_build_query(array_merge($baseParams, ['page' => $pagination['current_page'] - 1])) ?>">Anterior</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php 
                        $startPage = max(1, $pagination['current_page'] - 2);
                        $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                        
                        if ($startPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($baseParams, ['page' => 1])) ?>">1</a>
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
                                    <a class="page-link" href="?<?= http_build_query(array_merge($baseParams, ['page' => $i])) ?>"><?= $i ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($endPage < $pagination['total_pages']): ?>
                            <?php if ($endPage < $pagination['total_pages'] - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($baseParams, ['page' => $pagination['total_pages']])) ?>"><?= $pagination['total_pages'] ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($baseParams, ['page' => $pagination['current_page'] + 1])) ?>">Siguiente</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
<?php }; ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <a href="<?= url('/productos') ?>" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-2"><i class="fas fa-history"></i> Historial de Movimientos de Stock</h5>
                    <p class="mb-0 text-muted small">
                        <strong>Producto:</strong> <?= htmlspecialchars($producto['producto_nombre']) ?> | 
                        <strong>Stock actual:</strong> <?= $producto['producto_stock'] ?> unidades
                    </p>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="collapse show" id="filtrosCollapse">
            <div class="card-body border-bottom bg-light">
                <form method="GET" action="" id="formFiltros">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label small">Fecha desde:</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_desde" 
                                   value="<?= htmlspecialchars($filters['fecha_desde'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Fecha hasta:</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_hasta" 
                                   value="<?= htmlspecialchars($filters['fecha_hasta'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Tipo:</label>
                            <select class="form-select form-select-sm" name="tipo">
                                <option value="">Todos</option>
                                <option value="E" <?= ($filters['tipo'] ?? '') == 'E' ? 'selected' : '' ?>>Entrada</option>
                                <option value="S" <?= ($filters['tipo'] ?? '') == 'S' ? 'selected' : '' ?>>Salida</option>
                                <option value="A" <?= ($filters['tipo'] ?? '') == 'A' ? 'selected' : '' ?>>Ajuste</option>
                                <option value="C" <?= ($filters['tipo'] ?? '') == 'C' ? 'selected' : '' ?>>Corrección</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Cantidad mín:</label>
                            <input type="number" class="form-control form-control-sm" name="cantidad_min" 
                                   value="<?= htmlspecialchars($filters['cantidad_min'] ?? '') ?>" 
                                   step="1" placeholder="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Cantidad máx:</label>
                            <input type="number" class="form-control form-control-sm" name="cantidad_max" 
                                   value="<?= htmlspecialchars($filters['cantidad_max'] ?? '') ?>" 
                                   step="1" placeholder="0">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary btn-sm" title="Buscar">
                                    <i class="fas fa-search"></i>
                                </button>
                                <button type="button" class="btn btn-info btn-sm" onclick="limpiarFiltros()" title="Limpiar filtros">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Paginación superior -->
        <?php if (isset($pagination) && $pagination['total'] > 0): ?>
            <div class="card-header bg-light border-bottom py-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <label class="small mb-0 me-2">Mostrar:</label>
                        <select class="form-select form-select-sm d-inline-block" style="width: auto;" 
                                onchange="cambiarRegistrosPorPagina(this.value)">
                            <option value="5" <?= $perPage == 5 ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                        </select>
                    </div>
                    <div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-success btn-sm" onclick="exportarHistorial()" title="Exportar a Excel">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="exportarHistorialPDF()" title="Exportar a PDF">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
                <?php $renderPagination(true); ?>
            </div>
        <?php endif; ?>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="border-0 py-3">Fecha/Hora</th>
                            <th class="border-0 py-3">Tipo</th>
                            <th class="border-0 py-3">Cantidad</th>
                            <th class="border-0 py-3">Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historial)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No se encontraron movimientos con los filtros aplicados</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historial as $mov): ?>
                                <tr>
                                    <td class="border-0 py-3"><?= date('d/m/Y H:i', strtotime($mov['productomovimiento_fechahora'])) ?></td>
                                    <td class="border-0 py-3">
                                        <?php
                                        $tipo = $mov['productomovimiento_tipo'];
                                        $badgeClass = 'secondary';
                                        $tipoTexto = 'Desconocido';
                                        
                                        switch($tipo) {
                                            case 'E':
                                                $badgeClass = 'success';
                                                $tipoTexto = 'Entrada';
                                                break;
                                            case 'S':
                                                $badgeClass = 'danger';
                                                $tipoTexto = 'Salida';
                                                break;
                                            case 'A':
                                                $badgeClass = 'warning';
                                                $tipoTexto = 'Ajuste';
                                                break;
                                            case 'C':
                                                $badgeClass = 'info';
                                                $tipoTexto = 'Corrección';
                                                break;
                                        }
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>"><?= $tipoTexto ?></span>
                                    </td>
                                    <td class="border-0 py-3">
                                        <span class="<?= $mov['productomovimiento_cantidad'] > 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= $mov['productomovimiento_cantidad'] > 0 ? '+' : '' ?><?= $mov['productomovimiento_cantidad'] ?>
                                        </span>
                                    </td>
                                    <td class="border-0 py-3"><?= htmlspecialchars($mov['productomovimiento_descripcion']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginación inferior -->
        <?php if (isset($pagination) && $pagination['total'] > 0): ?>
            <div class="card-footer bg-white border-top py-3">
                <?php $renderPagination(true); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function limpiarFiltros() {
    window.location.href = window.location.pathname;
}

function cambiarRegistrosPorPagina(perPage) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Volver a la página 1
    window.location.href = url.toString();
}

function exportarHistorial() {
    const url = new URL(window.location.href);
    url.pathname = url.pathname + '/exportar';
    window.location.href = url.toString();
}

function exportarHistorialPDF() {
    const url = new URL(window.location.href);
    url.pathname = url.pathname + '/exportar-pdf';
    window.location.href = url.toString();
}
</script>
