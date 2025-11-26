<div class="container-fluid">
    <!-- Encabezado -->
    <div class="card border-0 shadow-sm">
        <div class="card-header text-dark py-3 mb-0">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0">Gestión de Cotizaciones</h4>
                </div>
                <div class="col-auto">
                    <a href="<?= url('/cotizaciones/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Nueva Cotización
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtros compactos -->
        <div class="card-body pb-0">
            <form method="GET" action="<?= url('/cotizaciones') ?>" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label small mb-1 text-muted">Filtros de búsqueda</label>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Proveedor</label>
                        <select name="rela_proveedor" class="form-select form-select-sm" style="width: 200px;">
                            <option value="">Todos</option>
                            <?php if (!empty($proveedores)): ?>
                                <?php foreach ($proveedores as $prov): ?>
                                    <option value="<?= $prov['id_proveedor'] ?>" 
                                            <?= ($_GET['rela_proveedor'] ?? '') == $prov['id_proveedor'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($prov['persona_denominacion'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Producto</label>
                        <input type="text" name="producto_nombre" class="form-control form-control-sm" 
                               placeholder="Buscar..." value="<?= htmlspecialchars($_GET['producto_nombre'] ?? '') ?>" 
                               style="width: 180px;">
                    </div>
                    <div class="col-auto ms-auto">
                        <label class="form-label small mb-1">Estado</label>
                        <select name="estado" class="form-select form-select-sm" style="width: 120px;">
                            <option value="">Todos</option>
                            <option value="1" <?= ($_GET['estado'] ?? '') == '1' ? 'selected' : '' ?>>Activa</option>
                            <option value="0" <?= ($_GET['estado'] ?? '') == '0' ? 'selected' : '' ?>>Anulada</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary btn-sm" title="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?= url('/cotizaciones') ?>" class="btn btn-info btn-sm" title="Limpiar filtros">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-auto">
                        <label class="form-label small mb-1 text-muted">Registros por página</label>
                    </div>
                    <div class="col-auto">
                        <select name="per_page" class="form-select form-select-sm" style="width: 80px;" 
                                onchange="this.form.submit()">
                            <option value="5" <?= ($_GET['per_page'] ?? '10') == '5' ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= ($_GET['per_page'] ?? '10') == '10' ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= ($_GET['per_page'] ?? '10') == '25' ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= ($_GET['per_page'] ?? '10') == '50' ? 'selected' : '' ?>>50</option>
                        </select>
                    </div>
                    <div class="col"></div>
                    <div class="col-auto">
                        <div class="btn-group" role="group">
                            <button type="button" onclick="exportarCotizaciones(event)" class="btn btn-success btn-sm" title="Exportar a Excel">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                            <button type="button" onclick="exportarCotizacionesPDF(event)" class="btn btn-danger btn-sm" title="Exportar a PDF">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla -->
        <div class="card-body p-0">
            <?php if (empty($cotizaciones)): ?>
                <div class="empty-state py-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-file-invoice-dollar fa-3x text-muted opacity-50"></i>
                    </div>
                    <h6 class="text-muted">No se encontraron cotizaciones</h6>
                    <p class="text-muted small mb-3">Intenta modificar los filtros o crea una nueva cotización.</p>
                    <a href="<?= url('/cotizaciones/create') ?>" class="btn btn-outline-dark btn-sm">
                        <i class="fas fa-plus fa-sm"></i> Crear cotización
                    </a>
                </div>
            <?php else: ?>
                <?php if (isset($pagination) && $pagination['total'] > 0): ?>
                    <?php 
                    $perPage = (int) ($_GET['per_page'] ?? 10);
                    $start = (($pagination['current_page'] - 1) * $perPage) + 1;
                    $end = min($pagination['current_page'] * $perPage, $pagination['total']);
                    
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
                    
                    <div class="card-header bg-light border-bottom py-2">
                        <?php $renderPagination(true); ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0 py-3">Proveedor</th>
                                <th class="border-0 py-3">Producto</th>
                                <th class="border-0 py-3">Monto</th>
                                <th class="border-0 py-3">Fecha/Hora</th>
                                <th class="border-0 py-3">Estado</th>
                                <th class="border-0 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cotizaciones as $cotizacion): ?>
                                <tr>
                                    <td class="border-0 py-3"><?= htmlspecialchars($cotizacion['proveedor_nombre']) ?></td>
                                    <td class="border-0 py-3"><?= htmlspecialchars($cotizacion['producto_nombre']) ?></td>
                                    <td class="border-0 py-3">$<?= number_format($cotizacion['cotizacion_monto'], 2, ',', '.') ?></td>
                                    <td class="border-0 py-3"><?= date('d/m/Y H:i', strtotime($cotizacion['cotizacion_fechahora'])) ?></td>
                                    <td class="border-0 py-3">
                                        <?php if ($cotizacion['cotizacion_estado'] == 1): ?>
                                            <span class="badge bg-success">Activa</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Anulada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border-0 py-3 text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= url('/cotizaciones/' . $cotizacion['id_cotizacion']) ?>" 
                                               class="btn btn-outline-primary" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= url('/cotizaciones/' . $cotizacion['id_cotizacion'] . '/edit') ?>" 
                                               class="btn btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($cotizacion['cotizacion_estado'] == 1): ?>
                                                <button onclick="anularCotizacion(<?= $cotizacion['id_cotizacion'] ?>)" 
                                                        class="btn btn-outline-danger" title="Anular">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                            <a href="<?= url('/cotizaciones/create?proveedor=' . $cotizacion['rela_proveedor'] . '&producto=' . $cotizacion['rela_producto']) ?>" 
                                               class="btn btn-outline-info" title="Recotizar">
                                                <i class="fas fa-redo"></i>
                                            </a>
                                            <a href="<?= url('/cotizaciones/historial?producto=' . $cotizacion['rela_producto'] . '&proveedor=' . $cotizacion['rela_proveedor']) ?>" 
                                               class="btn btn-outline-secondary" title="Ver Historial">
                                                <i class="fas fa-history"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (isset($pagination) && $pagination['total'] > 0): ?>
                    <div class="card-footer bg-white border-top py-3">
                        <?php $renderPagination(true); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function exportarCotizaciones(event) {
    event.preventDefault();
    const params = new URLSearchParams(window.location.search);
    window.location.href = '<?= url('/cotizaciones/exportar') ?>?' + params.toString();
}

function exportarCotizacionesPDF(event) {
    event.preventDefault();
    const params = new URLSearchParams(window.location.search);
    window.location.href = '<?= url('/cotizaciones/exportar-pdf') ?>?' + params.toString();
}

function anularCotizacion(id) {
    Swal.fire({
        title: '¿Anular cotización?',
        text: "Esta acción cambiará el estado de la cotización a anulada",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('/cotizaciones') ?>/' + id + '/delete';
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
