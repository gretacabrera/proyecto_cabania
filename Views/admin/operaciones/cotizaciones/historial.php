<?php
$perPage = (int) ($_GET['per_page'] ?? 10);
$start = (($pagination['current_page'] - 1) * $perPage) + 1;
$end = min($pagination['current_page'] * $perPage, $pagination['total']);

// Construir parámetros base para mantener en todas las URLs
$baseParams = [
    'producto' => $producto_id,
    'proveedor' => $proveedor_id
];
if (!empty($filters['fecha_desde'])) $baseParams['fecha_desde'] = $filters['fecha_desde'];
if (!empty($filters['fecha_hasta'])) $baseParams['fecha_hasta'] = $filters['fecha_hasta'];
if (isset($filters['monto_mayor']) && $filters['monto_mayor'] !== '') $baseParams['monto_mayor'] = $filters['monto_mayor'];
if (isset($filters['monto_menor']) && $filters['monto_menor'] !== '') $baseParams['monto_menor'] = $filters['monto_menor'];
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
            <a href="<?= url('/cotizaciones') ?>" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-2"><i class="fas fa-history"></i> Historial de Cotizaciones</h5>
                    <p class="mb-0 text-muted small">
                        <strong>Proveedor:</strong> <?= htmlspecialchars($proveedor_nombre) ?> | 
                        <strong>Producto:</strong> <?= htmlspecialchars($producto_nombre) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="collapse show" id="filtrosCollapse">
            <div class="card-body border-bottom bg-light">
                <form method="GET" action="" id="formFiltros">
                    <input type="hidden" name="producto" value="<?= $producto_id ?>">
                    <input type="hidden" name="proveedor" value="<?= $proveedor_id ?>">
                    
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
                            <label class="form-label small">Monto mayor que:</label>
                            <input type="number" class="form-control form-control-sm" name="monto_mayor" 
                                   value="<?= htmlspecialchars($filters['monto_mayor'] ?? '') ?>" 
                                   step="0.01" placeholder="0.00">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Monto menor que:</label>
                            <input type="number" class="form-control form-control-sm" name="monto_menor" 
                                   value="<?= htmlspecialchars($filters['monto_menor'] ?? '') ?>" 
                                   step="0.01" placeholder="0.00">
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-info btn-sm" onclick="limpiarFiltros()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Paginación superior -->
        <?php if (isset($pagination) && $pagination['total'] > 0): ?>
            <div class="card-header bg-light border-bottom py-2">
                <div class="d-flex justify-content-between align-items-center">
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
                        <button type="button" class="btn btn-success btn-sm" onclick="exportarHistorial()">
                            <i class="fas fa-file-excel"></i> Exportar Excel
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="exportarHistorialPdf()">
                            <i class="fas fa-file-pdf"></i> Exportar PDF
                        </button>
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
                            <th class="border-0 py-3">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historial)): ?>
                            <tr>
                                <td colspan="2" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No se encontraron cotizaciones con los filtros aplicados</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historial as $cot): ?>
                                <tr>
                                    <td class="border-0 py-3"><?= date('d/m/Y H:i', strtotime($cot['cotizacion_fechahora'])) ?></td>
                                    <td class="border-0 py-3">$<?= number_format($cot['cotizacion_monto'], 2, ',', '.') ?></td>
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
    window.location.href = '?producto=<?= $producto_id ?>&proveedor=<?= $proveedor_id ?>';
}

function cambiarRegistrosPorPagina(perPage) {
    const params = new URLSearchParams(window.location.search);
    params.set('per_page', perPage);
    params.set('page', '1');
    window.location.href = '?' + params.toString();
}

function exportarHistorial() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = '<?= url('/cotizaciones/historial/exportar') ?>?' + params.toString();
}

function exportarHistorialPdf() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = '<?= url('/cotizaciones/historial/exportar-pdf') ?>?' + params.toString();
}
</script>