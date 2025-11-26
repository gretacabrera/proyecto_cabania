<div class="container-fluid">
    <!-- Encabezado moderno -->
    <div class="card border-0 shadow-sm">
        <!-- Header oscuro -->
        <div class="card-header text-dark py-3 mb-0">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0">Gestión de Parámetros Generales</h4>
                </div>
                <div class="col-auto">
                    <a href="<?= url('/parametrosgenerales/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Nuevo Parámetro
                    </a>
                </div>
            </div>
        </div>
        <!-- Filtros compactos -->
        <div class="card-body pb-0">
            <form method="GET" action="<?= url('/parametrosgenerales') ?>" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label small mb-1 text-muted">Filtros de búsqueda</label>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Código</label>
                        <input type="text" name="parametrogeneral_codigo" class="form-control form-control-sm" 
                               placeholder="" value="<?= htmlspecialchars($_GET['parametrogeneral_codigo'] ?? '') ?>" style="width: 150px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Descripción</label>
                        <input type="text" name="parametrogeneral_descripcion" class="form-control form-control-sm" 
                               placeholder="" value="<?= htmlspecialchars($_GET['parametrogeneral_descripcion'] ?? '') ?>" style="width: 200px;">
                    </div>
                    <div class="col-auto ms-auto">
                        <label class="form-label small mb-1">Estado</label>
                        <select name="parametrogeneral_estado" class="form-select form-select-sm" style="width: 120px;">
                            <option value="">Todos</option>
                            <option value="1" <?= ($_GET['parametrogeneral_estado'] ?? '') == '1' ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= ($_GET['parametrogeneral_estado'] ?? '') == '0' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary btn-sm" title="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?= url('/parametrosgenerales') ?>" class="btn btn-info btn-sm" title="Limpiar filtros">
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
                            <button type="button" onclick="exportarParametros(event)" class="btn btn-success btn-sm" title="Exportar a Excel">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                            <button type="button" onclick="exportarParametrosPDF(event)" class="btn btn-danger btn-sm" title="Exportar a PDF">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla estilo moderno -->
        <div class="card-body p-0">
            <?php if (empty($parametros)): ?>
                <div class="empty-state py-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-cogs fa-3x text-muted opacity-50"></i>
                    </div>
                    <h6 class="text-muted">No se encontraron parámetros</h6>
                    <p class="text-muted small mb-3">Intenta modificar los filtros o crea un nuevo parámetro.</p>
                    <a href="<?= url('/parametrosgenerales/create') ?>" class="btn btn-outline-dark btn-sm">
                        <i class="fas fa-plus fa-sm"></i> Crear parámetro
                    </a>
                </div>
            <?php else: ?>
                <!-- Información de paginación y navegación superior -->
                <?php if (isset($pagination) && $pagination['total'] > 0): ?>
                    <?php 
                    $perPage = (int) ($_GET['per_page'] ?? 10);
                    $start = (($pagination['current_page'] - 1) * $perPage) + 1;
                    $end = min($pagination['current_page'] * $perPage, $pagination['total']);
                    
                    // Función para renderizar la paginación
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
                    
                    <!-- Paginación superior -->
                    <div class="card-header bg-light border-bottom py-2">
                        <?php $renderPagination(true); ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table id="tablaParametros" class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0 py-3">Código</th>
                                <th class="border-0 py-3">Descripción</th>
                                <th class="border-0 py-3 text-end">Valor</th>
                                <th class="border-0 py-3">Estado</th>
                                <th class="border-0 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($parametros as $parametro): ?>
                                <tr>
                                    <td class="border-0 py-3">
                                        <?= htmlspecialchars($parametro['parametrogeneral_codigo']) ?>
                                    </td>
                                    <td class="border-0 py-3">
                                        <div class="fw-medium text-dark"><?= htmlspecialchars($parametro['parametrogeneral_descripcion']) ?></div>
                                    </td>
                                    <td class="border-0 py-3 text-end">
                                        <?= number_format($parametro['parametrogeneral_valor'], 2, ',', '.') ?>
                                    </td>
                                    <td class="border-0 py-3">
                                        <?php if ($parametro['parametrogeneral_estado'] == 1): ?>
                                            <span class="badge bg-success text-white px-2 py-1 rounded-pill">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-white px-2 py-1 rounded-pill">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border-0 py-3 text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= url('/parametrosgenerales/' . $parametro['id_parametrogeneral']) ?>" 
                                               class="btn btn-outline-primary" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= url('/parametrosgenerales/' . $parametro['id_parametrogeneral'] . '/edit') ?>" 
                                               class="btn btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="cambiarEstado(<?= $parametro['id_parametrogeneral'] ?>)" 
                                                    class="btn btn-outline-<?= $parametro['parametrogeneral_estado'] == 1 ? 'danger' : 'success' ?>" 
                                                    title="<?= $parametro['parametrogeneral_estado'] == 1 ? 'Desactivar' : 'Activar' ?>">
                                                <i class="fas fa-<?= $parametro['parametrogeneral_estado'] == 1 ? 'times' : 'check' ?>"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación inferior -->
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
function cambiarEstado(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Deseas cambiar el estado de este parámetro?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`<?= url('/parametrosgenerales/') ?>${id}/estado`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        '¡Actualizado!',
                        data.message,
                        'success'
                    ).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire(
                        'Error',
                        data.message,
                        'error'
                    );
                }
            })
            .catch(error => {
                Swal.fire(
                    'Error',
                    'Ocurrió un error al cambiar el estado',
                    'error'
                );
            });
        }
    });
}

function exportarParametros(event) {
    event.preventDefault();
    const params = new URLSearchParams(window.location.search);
    window.location.href = '<?= url('/parametrosgenerales/exportar') ?>?' + params.toString();
}

function exportarParametrosPDF(event) {
    event.preventDefault();
    const params = new URLSearchParams(window.location.search);
    window.location.href = '<?= url('/parametrosgenerales/exportar-pdf') ?>?' + params.toString();
}
</script>
