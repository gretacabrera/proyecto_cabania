<div class="container-fluid">
    <!-- Encabezado moderno similar al diseño de referencia -->
    <div class="card border-0 shadow-sm">
        <!-- Header oscuro -->
        <div class="card-header text-dark py-3 mb-0">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0">Gestión de Consumos</h4>
                </div>
                <div class="col-auto">
                    <a href="<?= url('/consumos/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Registrar Consumo
                    </a>
                </div>
            </div>
        </div>
        <!-- Filtros compactos -->
        <div class="card-body pb-0">
            <form method="GET" action="<?= url('/consumos') ?>" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label small mb-1 text-muted">Filtros de búsqueda</label>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Huésped</label>
                        <input type="text" name="huesped" class="form-control form-control-sm" 
                               placeholder="" value="<?= htmlspecialchars($_GET['huesped'] ?? '') ?>" style="width: 150px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Reserva</label>
                        <input type="number" name="reserva" class="form-control form-control-sm" 
                               placeholder="#" value="<?= htmlspecialchars($_GET['reserva'] ?? '') ?>" style="width: 100px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Producto</label>
                        <input type="text" name="producto" class="form-control form-control-sm" 
                               placeholder="" value="<?= htmlspecialchars($_GET['producto'] ?? '') ?>" style="width: 150px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Servicio</label>
                        <input type="text" name="servicio" class="form-control form-control-sm" 
                               placeholder="" value="<?= htmlspecialchars($_GET['servicio'] ?? '') ?>" style="width: 150px;">
                    </div>
                    <div class="col-auto ms-auto">
                        <label class="form-label small mb-1">Estado</label>
                        <select name="estado" class="form-select form-select-sm" style="width: 120px;">
                            <option value="">Todos</option>
                            <option value="1" <?= ($_GET['estado'] ?? '') == '1' ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= ($_GET['estado'] ?? '') == '0' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary btn-sm" title="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?= url('/consumos') ?>" class="btn btn-info btn-sm" title="Limpiar filtros">
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
                            <button type="button" onclick="exportarConsumos(event)" class="btn btn-success btn-sm" title="Exportar a Excel">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                            <button type="button" onclick="exportarConsumosPDF(event)" class="btn btn-danger btn-sm" title="Exportar a PDF">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla estilo moderno -->
        <div class="card-body p-0">
            <?php if (empty($consumos)): ?>
                <div class="empty-state py-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-shopping-cart fa-3x text-muted opacity-50"></i>
                    </div>
                    <h6 class="text-muted">No se encontraron consumos</h6>
                    <p class="text-muted small mb-3">Intenta modificar los filtros o registra un nuevo consumo.</p>
                    <a href="<?= url('/consumos/create') ?>" class="btn btn-outline-dark btn-sm">
                        <i class="fas fa-plus fa-sm"></i> Registrar consumo
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

                    <div class="card-header bg-light border-bottom py-2">
                        <?php $renderPagination(true); ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table id="tablaConsumos" class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0 py-3">Reserva</th>
                                <th class="border-0 py-3">Huésped</th>
                                <th class="border-0 py-3">Descripción</th>
                                <th class="border-0 py-3">Cantidad</th>
                                <th class="border-0 py-3">Precio Unit.</th>
                                <th class="border-0 py-3">Total</th>
                                <th class="border-0 py-3">Estado</th>
                                <th class="border-0 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($consumos as $index => $consumo): ?>
                                <tr>
                                    <td class="border-0 py-3">
                                        <div class="small text-muted">
                                            #<?= $consumo['rela_reserva'] ?>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div class="fw-medium text-dark">
                                                    <?= htmlspecialchars($consumo['huesped_nombre'] ?? 'N/A') ?>
                                                    <?php if (!empty($consumo['huesped_apellido'])): ?>
                                                        <?= htmlspecialchars($consumo['huesped_apellido']) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <div class="small text-muted">
                                            <?php if (!empty($consumo['consumo_descripcion'])): ?>
                                                <?= htmlspecialchars(substr($consumo['consumo_descripcion'], 0, 50)) ?>
                                                <?= strlen($consumo['consumo_descripcion']) > 50 ? '...' : '' ?>
                                            <?php else: ?>
                                                <span class="text-muted">Sin descripción</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-box text-primary me-2"></i>
                                            <span class="text-dark ml-2"><?= isset($consumo['consumo_cantidad']) ? number_format($consumo['consumo_cantidad'], 0) : '0' ?></span>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <span class="fw-medium text-success">
                                            $<?= isset($consumo['consumo_total']) && isset($consumo['consumo_cantidad']) && $consumo['consumo_cantidad'] > 0 
                                                ? number_format($consumo['consumo_total'] / $consumo['consumo_cantidad'], 2, '.', ',') 
                                                : '0.00' ?>
                                        </span>
                                    </td>
                                    <td class="border-0 py-3">
                                        <span class="fw-medium text-success">
                                            $<?= isset($consumo['consumo_total']) ? number_format($consumo['consumo_total'], 2, '.', ',') : '0.00' ?>
                                        </span>
                                    </td>
                                    <td class="border-0 py-3">
                                        <?php if ($consumo['consumo_estado'] == 1): ?>
                                            <span class="badge bg-success text-white px-2 py-1 rounded-pill">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-white px-2 py-1 rounded-pill">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border-0 py-3 text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= url('/consumos/' . $consumo['id_consumo']) ?>"
                                               class="btn btn-outline-primary btn-sm"
                                               title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= url('/consumos/' . $consumo['id_consumo']) . '/edit'?>"
                                               class="btn btn-outline-warning btn-sm"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($consumo['consumo_estado'] == 1): ?>
                                                <button class="btn btn-outline-danger btn-sm"
                                                        onclick="cambiarEstadoConsumo(<?= $consumo['id_consumo'] ?>, 0, '<?= addslashes($consumo['producto_nombre'] ?? '') ?>')"
                                                        title="Desactivar">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-outline-success btn-sm"
                                                        onclick="cambiarEstadoConsumo(<?= $consumo['id_consumo'] ?>, 1, '<?= addslashes($consumo['producto_nombre'] ?? '') ?>')"
                                                        title="Activar">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
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

<!-- JavaScript para funcionalidades -->
<script>
function cambiarEstadoConsumo(id, nuevoEstado, producto) {
    let accion, mensaje, color;
    
    switch(nuevoEstado) {
        case 1:
            accion = 'activar';
            mensaje = 'El consumo estará activo en el sistema';
            color = '#28a745';
            break;
        case 0:
            accion = 'desactivar';
            mensaje = 'El consumo quedará inactivo';
            color = '#dc3545';
            break;
        default:
            accion = 'cambiar estado';
            mensaje = '';
            color = '#6c757d';
    }
    
    console.log('Cambiando estado:', { id, nuevoEstado, producto, accion });
    
    // Usar SweetAlert si está disponible, sino usar confirm simple
    const confirmar = typeof Swal !== 'undefined' ? 
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} consumo?`,
            text: `¿Está seguro que desea ${accion} el consumo de "${producto}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then((result) => {
            if (result.isConfirmed) {
                ejecutarCambioEstado(id, nuevoEstado);
            }
        }) :
        window.confirm(`¿Está seguro que desea ${accion} este consumo?`);
    
    if (confirmar && typeof Swal === 'undefined') {
        ejecutarCambioEstado(id, nuevoEstado);
    }
}

function ejecutarCambioEstado(id, nuevoEstado) {
    fetch(`<?= url('/consumos/') ?>${id}/estado`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ estado: nuevoEstado })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Estado actualizado!',
                    text: data.message || 'El estado del consumo se ha actualizado correctamente',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                alert(data.message || 'Estado actualizado correctamente');
                window.location.reload();
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', data.message || 'No se pudo actualizar el estado', 'error');
            } else {
                alert('Error: ' + (data.message || 'No se pudo actualizar el estado'));
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error', 'Hubo un problema al actualizar el estado', 'error');
        } else {
            alert('Error al actualizar el estado');
        }
    });
}

function exportarConsumos(event) {
    event.preventDefault();
    const params = new URLSearchParams(window.location.search);
    window.location.href = '<?= url('/consumos/exportar') ?>?' + params.toString();
}

function exportarConsumosPDF(event) {
    event.preventDefault();
    const params = new URLSearchParams(window.location.search);
    window.location.href = '<?= url('/consumos/exportar-pdf') ?>?' + params.toString();
}
</script>