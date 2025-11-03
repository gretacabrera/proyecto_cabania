<?php 
$perPage = (int) ($_GET['per_page'] ?? 10);
$start = (($pagination['current_page'] - 1) * $perPage) + 1;
$end = min($pagination['current_page'] * $perPage, $pagination['total']);
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800">
                <i class="fas fa-concierge-bell text-primary me-2"></i>
                Gestión de Servicios
            </h2>
        </div>
        <div>
            <a href="<?= url('/servicios/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nuevo Servicio
            </a>
        </div>
    </div>

    <!-- Card principal -->
    <div class="card shadow">
        <!-- Filtros de búsqueda -->
        <div class="card-header bg-light border-bottom">
            <div class="row">
                <div class="col-12">
                    <button class="btn btn-link text-dark fw-bold p-0" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosCollapse">
                        <i class="fas fa-filter me-2"></i>Filtros de búsqueda
                        <i class="fas fa-chevron-down ms-2"></i>
                    </button>
                </div>
            </div>
            
            <div class="collapse show" id="filtrosCollapse">
                <form method="GET" action="<?= url('/servicios') ?>" class="mt-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="servicio_nombre" class="form-label small text-muted">Nombre</label>
                            <input type="text" class="form-control form-control-sm" id="servicio_nombre" name="servicio_nombre" 
                                   value="<?= htmlspecialchars($filters['servicio_nombre'] ?? '') ?>" placeholder="Buscar por nombre...">
                        </div>
                        <div class="col-md-2">
                            <label for="precio_min" class="form-label small text-muted">Precio mín.</label>
                            <input type="number" class="form-control form-control-sm" id="precio_min" name="precio_min" 
                                   value="<?= htmlspecialchars($filters['precio_min'] ?? '') ?>" placeholder="0.00" step="0.01" min="0">
                        </div>
                        <div class="col-md-2">
                            <label for="precio_max" class="form-label small text-muted">Precio máx.</label>
                            <input type="number" class="form-control form-control-sm" id="precio_max" name="precio_max" 
                                   value="<?= htmlspecialchars($filters['precio_max'] ?? '') ?>" placeholder="0.00" step="0.01" min="0">
                        </div>
                        <div class="col-md-2">
                            <label for="rela_tiposervicio" class="form-label small text-muted">Tipo</label>
                            <select class="form-select form-select-sm" id="rela_tiposervicio" name="rela_tiposervicio">
                                <option value="">Todos los tipos</option>
                                <?php if (isset($tipos_servicios) && is_array($tipos_servicios)): ?>
                                    <?php foreach ($tipos_servicios as $tipo): ?>
                                        <option value="<?= $tipo['id_tiposervicio'] ?>" <?= ($filters['rela_tiposervicio'] ?? '') == $tipo['id_tiposervicio'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tipo['tiposervicio_descripcion']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="estado" class="form-label small text-muted">Estado</label>
                            <select class="form-select form-select-sm" id="estado" name="estado">
                                <option value="">Todos</option>
                                <option value="1" <?= ($filters['estado'] ?? '') === '1' ? 'selected' : '' ?>>Activo</option>
                                <option value="0" <?= ($filters['estado'] ?? '') === '0' ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small text-muted d-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="<?= url('/servicios') ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times me-1"></i>Limpiar filtros
                                    </a>
                                </div>
                                <div>
                                    <div class="btn-group">
                                        <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="5" <?= $perPage == 5 ? 'selected' : '' ?>>5 por página</option>
                                            <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10 por página</option>
                                            <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25 por página</option>
                                            <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50 por página</option>
                                        </select>
                                    </div>
                                    <div class="btn-group ms-2">
                                        <a href="<?= url('/servicios/exportar?' . http_build_query($filters)) ?>" class="btn btn-success btn-sm">
                                            <i class="fas fa-file-excel me-1"></i>Excel
                                        </a>
                                        <a href="<?= url('/servicios/exportar-pdf?' . http_build_query($filters)) ?>" class="btn btn-danger btn-sm">
                                            <i class="fas fa-file-pdf me-1"></i>PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Paginación superior -->
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

        <!-- Tabla de datos -->
        <div class="table-responsive">
            <?php if (empty($servicios)): ?>
                <!-- Estado vacío -->
                <div class="text-center py-5">
                    <i class="fas fa-concierge-bell fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No se encontraron servicios</h5>
                    <p class="text-muted">No hay servicios que coincidan con los criterios de búsqueda.</p>
                    <a href="<?= url('/servicios/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Crear primer servicio
                    </a>
                </div>
            <?php else: ?>
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 py-3">
                                <i class="fas fa-concierge-bell me-2"></i>Servicio
                            </th>
                            <th class="border-0 py-3">
                                <i class="fas fa-tag me-2"></i>Tipo
                            </th>
                            <th class="border-0 py-3">
                                <i class="fas fa-dollar-sign me-2"></i>Precio
                            </th>
                            <th class="border-0 py-3">
                                <i class="fas fa-toggle-on me-2"></i>Estado
                            </th>
                            <th class="border-0 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicios as $servicio): ?>
                            <tr>
                                <td class="border-0 py-3">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($servicio['servicio_nombre']) ?></h6>
                                        <?php if (!empty($servicio['servicio_descripcion'])): ?>
                                            <small class="text-muted"><?= htmlspecialchars(substr($servicio['servicio_descripcion'], 0, 100)) ?><?= strlen($servicio['servicio_descripcion']) > 100 ? '...' : '' ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="border-0 py-3">
                                    <?php if (!empty($servicio['tiposervicio_descripcion'])): ?>
                                        <span class="badge bg-info text-dark">
                                            <?= htmlspecialchars($servicio['tiposervicio_descripcion']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">No especificado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="border-0 py-3">
                                    <span class="fw-bold text-success">
                                        $<?= number_format($servicio['servicio_precio'], 2, ',', '.') ?>
                                    </span>
                                </td>
                                <td class="border-0 py-3">
                                    <?php if ($servicio['servicio_estado'] == 1): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="border-0 py-3 text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('/servicios/' . $servicio['id_servicio']) ?>" 
                                           class="btn btn-outline-primary" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= url('/servicios/' . $servicio['id_servicio'] . '/edit') ?>" 
                                           class="btn btn-outline-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-<?= $servicio['servicio_estado'] ? 'danger' : 'success' ?>" 
                                                onclick="cambiarEstado(<?= $servicio['id_servicio'] ?>, <?= $servicio['servicio_estado'] ?>)"
                                                title="<?= $servicio['servicio_estado'] ? 'Desactivar' : 'Activar' ?>">
                                            <i class="fas fa-<?= $servicio['servicio_estado'] ? 'times' : 'check' ?>"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
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
function cambiarEstado(id, estadoActual) {
    const accion = estadoActual ? 'desactivar' : 'activar';
    const mensaje = `¿Está seguro que desea ${accion} este servicio?`;
    
    Swal.fire({
        title: '¿Confirmar acción?',
        text: mensaje,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, ' + accion,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`<?= url('/servicios') ?>/${id}/estado`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ estado: estadoActual ? 0 : 1 })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('¡Éxito!', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo completar la acción', 'error');
            });
        }
    });
}
</script>