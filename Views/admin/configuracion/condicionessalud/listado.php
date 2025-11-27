<?php 
$perPage = (int) ($_GET['per_page'] ?? 10);
$start = (($pagination['current_page'] - 1) * $perPage) + 1;
$end = min($pagination['current_page'] * $perPage, $pagination['total']);

$renderPagination = function($showInfo = true) use ($pagination, $start, $end) {
?>
    <div class="row align-items-center">
        <?php if ($showInfo): ?>
            <!-- INFORMACIÓN DE REGISTROS (siempre visible) -->
            <div class="col-sm-6">
                <span class="text-muted small">
                    Mostrando <?= $start ?> a <?= $end ?> de <?= $pagination['total'] ?> registros
                </span>
            </div>
        <?php endif; ?>
        
        <div class="col-sm-<?= $showInfo ? '6' : '12' ?>">
            <!-- NAVEGACIÓN (solo si hay múltiples páginas) -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <nav aria-label="Paginación" class="d-flex justify-content-<?= $showInfo ? 'end' : 'center' ?>">
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Botón Anterior -->
                        <?php if ($pagination['current_page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) ?>">Anterior</a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Navegación inteligente con elipsis -->
                        <?php 
                        $startPage = max(1, $pagination['current_page'] - 2);
                        $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                        
                        // Primera página + elipsis
                        if ($startPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
                            </li>
                            <?php if ($startPage > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Páginas del rango actual -->
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                <?php if ($i == $pagination['current_page']): ?>
                                    <!-- Página actual: destacada y no clickeable -->
                                    <span class="page-link bg-primary text-white border-primary"><?= $i ?></span>
                                <?php else: ?>
                                    <!-- Otras páginas: enlaces normales -->
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Última página + elipsis -->
                        <?php if ($endPage < $pagination['total_pages']): ?>
                            <?php if ($endPage < $pagination['total_pages'] - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['total_pages']])) ?>"><?= $pagination['total_pages'] ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Botón Siguiente -->
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

<div class="container-fluid">
    <div class="card border-0 shadow-sm">
        <!-- Header oscuro -->
        <div class="card-header text-dark py-3 mb-0">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0">Gestión de Condiciones de Salud</h4>
                </div>
                <div class="col-auto">
                    <a href="<?= url('/condicionessalud/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i>Nueva Condición
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Filtros compactos -->
        <div class="card-body pb-0">
            <form method="GET" action="<?= url('/condicionessalud') ?>" class="mb-3">
                <div class="row align-items-end">
                    <div class="col-auto">
                        <label class="form-label small mb-1 text-muted">Filtros de búsqueda</label>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Descripción</label>
                        <input type="text" name="condicionsalud_descripcion" class="form-control form-control-sm" 
                               placeholder="" value="<?= htmlspecialchars($_GET['condicionsalud_descripcion'] ?? '') ?>" style="width: 200px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Estado</label>
                        <select name="condicionsalud_estado" class="form-select form-select-sm" style="width: 120px;">
                            <option value="">Todos</option>
                            <option value="1" <?= ($_GET['condicionsalud_estado'] ?? '') == '1' ? 'selected' : '' ?>>Activa</option>
                            <option value="0" <?= ($_GET['condicionsalud_estado'] ?? '') == '0' ? 'selected' : '' ?>>Inactiva</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary btn-sm" title="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?= url('/condicionessalud') ?>" class="btn btn-info btn-sm" title="Limpiar filtros">
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
                            <option value="5" <?= $perPage == 5 ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                        </select>
                    </div>
                    <div class="col"></div> <!-- Espaciador para empujar los botones a la derecha -->
                    <div class="col-auto">
                        <div class="btn-group" role="group">
                            <button type="button" onclick="exportarCondiciones(event)" class="btn btn-success btn-sm" title="Exportar a Excel">
                                <i class="fas fa-file-excel mr-1"></i> Excel
                            </button>
                            <button type="button" onclick="exportarCondicionesPDF(event)" class="btn btn-danger btn-sm" title="Exportar a PDF">
                                <i class="fas fa-file-pdf mr-1"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Paginación Superior -->
        <?php if (isset($pagination) && $pagination['total'] > 0): ?>
            <div class="card-header bg-light border-bottom py-2">
                <?php $renderPagination(true); ?>
            </div>
        <?php endif; ?>

        <!-- Tabla de datos -->
        <div class="card-body p-0">
            <?php if (!empty($condiciones)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0 py-3">Descripción</th>
                                <th class="border-0 py-3">Estado</th>
                                <th class="border-0 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($condiciones as $condicion): ?>
                                <tr>
                                    <td class="border-0 py-3">
                                        <div><?= htmlspecialchars($condicion['condicionsalud_descripcion']) ?></div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <?php if ($condicion['condicionsalud_estado'] == 1): ?>
                                            <span class="badge badge-success">Activa</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border-0 py-3 text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= url('/condicionessalud/' . $condicion['id_condicionsalud']) ?>" 
                                               class="btn btn-outline-primary" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= url('/condicionessalud/' . $condicion['id_condicionsalud'] . '/edit') ?>" 
                                               class="btn btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-<?= $condicion['condicionsalud_estado'] == 1 ? 'danger' : 'success' ?>"
                                                    onclick="cambiarEstado(<?= $condicion['id_condicionsalud'] ?>, <?= $condicion['condicionsalud_estado'] == 1 ? '0' : '1' ?>)"
                                                    title="<?= $condicion['condicionsalud_estado'] == 1 ? 'Desactivar' : 'Activar' ?>">
                                                <i class="fas fa-<?= $condicion['condicionsalud_estado'] == 1 ? 'ban' : 'check' ?>"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <!-- Estado vacío -->
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-heartbeat text-muted" style="font-size: 64px;"></i>
                    </div>
                    <h5 class="text-muted">No se encontraron condiciones de salud</h5>
                    <p class="text-muted">No hay registros que coincidan con los filtros aplicados.</p>
                    <a href="<?= url('/condicionessalud/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i>Agregar Primera Condición
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Paginación Inferior -->
        <?php if (isset($pagination) && $pagination['total'] > 0): ?>
            <div class="card-footer bg-white border-top py-3">
                <?php $renderPagination(true); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function cambiarEstado(id, nuevoEstado) {
    // Usar la utilidad CRUD estándar para cambio de estado
    CrudUtils.changeStatus(id, nuevoEstado, 'condición de salud', '<?= url('/condicionessalud') ?>');
}

// Función para exportar a Excel con alertas sutiles
function exportarCondiciones(event) {
    event.preventDefault();
    
    // Toast informativo sutil
    SwalPresets.toast('Preparando archivo Excel...', 'info', 2000);
    
    // Obtener filtros actuales
    const params = new URLSearchParams(window.location.search);
    params.delete('page'); // Remover paginación para exportar todo
    
    const url = `<?= url('/condicionessalud/exportar') ?>?${params.toString()}`;
    
    // Delay para mostrar el toast
    setTimeout(() => {
        window.location.href = url;
        
        // Confirmación de descarga
        setTimeout(() => {
            SwalPresets.toast('Descarga iniciada', 'success', 1500);
        }, 500);
    }, 800);
}

// Función para exportar a PDF con alertas sutiles
function exportarCondicionesPDF(event) {
    event.preventDefault();
    
    // Toast informativo sutil
    SwalPresets.toast('Generando archivo PDF...', 'info', 2000);
    
    // Obtener filtros actuales
    const params = new URLSearchParams(window.location.search);
    params.delete('page'); // Remover paginación para exportar todo
    
    const url = `<?= url('/condicionessalud/exportar-pdf') ?>?${params.toString()}`;
    
    // Delay para mostrar el toast
    setTimeout(() => {
        window.location.href = url;
        
        // Confirmación de descarga
        setTimeout(() => {
            SwalPresets.toast('Descarga iniciada', 'success', 1500);
        }, 500);
    }, 800);
}
</script>