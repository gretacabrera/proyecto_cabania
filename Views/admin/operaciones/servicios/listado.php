<div class="container-fluid">
    <!-- Encabezado moderno similar al diseño de referencia -->
    <div class="card border-0 shadow-sm">
        <!-- Header oscuro -->
        <div class="card-header text-dark py-3 mb-0">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0">Gestión de Servicios</h4>
                </div>
                <div class="col-auto">
                    <a href="<?= url('/servicios/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i>Nuevo Servicio
                    </a>
                </div>
            </div>
        </div>
        <!-- Filtros compactos -->
        <div class="card-body pb-0">
            <form method="GET" action="<?= url('/servicios') ?>" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label small mb-1 text-muted">Filtros de búsqueda</label>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Nombre</label>
                        <input type="text" name="servicio_nombre" class="form-control form-control-sm" 
                               placeholder="" value="<?= htmlspecialchars($filters['servicio_nombre'] ?? '') ?>" style="width: 150px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Precio Min.</label>
                        <input type="number" name="precio_min" class="form-control form-control-sm" 
                               placeholder="0.00" value="<?= htmlspecialchars($filters['precio_min'] ?? '') ?>" 
                               step="0.01" min="0" style="width: 120px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Precio Max.</label>
                        <input type="number" name="precio_max" class="form-control form-control-sm" 
                               placeholder="0.00" value="<?= htmlspecialchars($filters['precio_max'] ?? '') ?>" 
                               step="0.01" min="0" style="width: 120px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Tipo</label>
                        <select name="rela_tiposervicio" class="form-select form-select-sm" style="width: 160px;">
                            <option value="">Todos</option>
                            <?php if (isset($tiposServicio) && is_array($tiposServicio)): ?>
                                <?php foreach ($tiposServicio as $tipo): ?>
                                    <option value="<?= $tipo['id_tiposervicio'] ?>" <?= ($filters['rela_tiposervicio'] ?? '') == $tipo['id_tiposervicio'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tipo['tiposervicio_descripcion']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Estado</label>
                        <select name="servicio_estado" class="form-select form-select-sm" style="width: 120px;">
                            <option value="">Todos</option>
                            <option value="1" <?= ($filters['servicio_estado'] ?? '') === '1' ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= ($filters['servicio_estado'] ?? '') === '0' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary btn-sm" title="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?= url('/servicios') ?>" class="btn btn-info btn-sm" title="Limpiar filtros">
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
                            <?php $perPage = (int) ($_GET['per_page'] ?? 10); ?>
                            <option value="5" <?= $perPage == 5 ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                        </select>
                    </div>
                    <div class="col"></div> <!-- Espaciador para empujar los botones a la derecha -->
                    <div class="col-auto">
                        <div class="btn-group" role="group">
                            <button type="button" onclick="exportarServicios(event)" class="btn btn-success btn-sm" title="Exportar a Excel">
                                <i class="fas fa-file-excel mr-1"></i> Excel
                            </button>
                            <button type="button" onclick="exportarServiciosPDF(event)" class="btn btn-danger btn-sm" title="Exportar a PDF">
                                <i class="fas fa-file-pdf mr-1"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Contenido de la tabla -->
        <div class="card-body pt-0">
            <?php if (empty($servicios)): ?>
                <!-- Estado vacío -->
                <div class="text-center py-5">
                    <i class="fas fa-concierge-bell fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay servicios registrados</h5>
                    <p class="text-muted mb-4">Comienza creando tu primer servicio</p>
                    <a href="<?= url('/servicios/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>Crear primer servicio
                    </a>
                </div>
            <?php else: ?>
                <!-- Información de paginación -->
                <?php 
                $start = (($pagination['current_page'] - 1) * $pagination['per_page']) + 1;
                $end = min($pagination['current_page'] * $pagination['per_page'], $pagination['total']);
                ?>
                
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <small class="text-muted">
                            Mostrando <?= $start ?> a <?= $end ?> de <?= $pagination['total'] ?> servicios
                        </small>
                    </div>
                    <div class="col-sm-6 text-sm-right">
                        <?php if ($pagination['total_pages'] > 1): ?>
                            <nav aria-label="Paginación">
                                <ul class="pagination pagination-sm mb-0 justify-content-end">
                                    <?php if ($pagination['current_page'] > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) ?>">‹</a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    $startPage = max(1, $pagination['current_page'] - 2);
                                    $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                    
                                    for ($i = $startPage; $i <= $endPage; $i++): ?>
                                        <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])) ?>">›</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tabla de servicios -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-top-0">Servicio</th>
                                <th class="border-top-0">Tipo</th>
                                <th class="border-top-0">Precio</th>
                                <th class="border-top-0">Estado</th>
                                <th class="border-top-0 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($servicios as $servicio): ?>
                                <tr>
                                    <td class="align-middle">
                                        <div class="media">
                                            <div class="media-body">
                                                <h6 class="mb-1"><?= htmlspecialchars($servicio['servicio_nombre']) ?></h6>
                                                <?php if (!empty($servicio['servicio_descripcion'])): ?>
                                                    <small class="text-muted d-block">
                                                        <?= htmlspecialchars(substr($servicio['servicio_descripcion'], 0, 80)) ?>
                                                        <?= strlen($servicio['servicio_descripcion']) > 80 ? '...' : '' ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <?php if (!empty($servicio['tiposervicio_descripcion'])): ?>
                                            <span class="badge badge-info">
                                                <?= htmlspecialchars($servicio['tiposervicio_descripcion']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small">Sin tipo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-dollar-sign text-success mr-2"></i>
                                            <span class="font-weight-bold text-success">
                                                <?= number_format($servicio['servicio_precio'], 2, ',', '.') ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ($servicio['servicio_estado'] == 1): ?>
                                            <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= url('/servicios/' . $servicio['id_servicio']) ?>" 
                                               class="btn btn-outline-info" title="Ver detalle" data-toggle="tooltip">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= url('/servicios/' . $servicio['id_servicio'] . '/edit') ?>" 
                                               class="btn btn-outline-warning" title="Editar" data-toggle="tooltip">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-<?= $servicio['servicio_estado'] ? 'danger' : 'success' ?>" 
                                                    onclick="cambiarEstado(<?= $servicio['id_servicio'] ?>, <?= $servicio['servicio_estado'] ?>)"
                                                    title="<?= $servicio['servicio_estado'] ? 'Desactivar' : 'Activar' ?>" 
                                                    data-toggle="tooltip">
                                                <i class="fas fa-<?= $servicio['servicio_estado'] ? 'times' : 'check' ?>"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación inferior -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="row mt-3">
                        <div class="col-sm-6">
                            <small class="text-muted">
                                Página <?= $pagination['current_page'] ?> de <?= $pagination['total_pages'] ?>
                            </small>
                        </div>
                        <div class="col-sm-6">
                            <nav aria-label="Paginación">
                                <ul class="pagination pagination-sm mb-0 justify-content-end">
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
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
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
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
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

// Función para exportar servicios a Excel (.xlsx)
function exportarServicios(event) {
    // Prevenir comportamiento por defecto del botón
    if (event) {
        event.preventDefault();
    }
    
    // Mostrar mensaje de procesamiento
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Generando archivo...',
            text: 'Por favor espere mientras se procesa la exportación',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
    
    // Obtener los parámetros actuales de la URL (filtros y paginación)
    const urlParams = new URLSearchParams(window.location.search);
    
    // Crear la URL de exportación manteniendo todos los filtros
    const baseExportUrl = '<?= url('/servicios/exportar') ?>';
    const exportUrl = baseExportUrl + '?' + urlParams.toString();
    
    // Crear un enlace temporal para la descarga
    const link = document.createElement('a');
    link.href = exportUrl;
    link.style.display = 'none';
    document.body.appendChild(link);
    
    // Simular clic para iniciar descarga
    link.click();
    
    // Limpiar el enlace temporal
    document.body.removeChild(link);
    
    // Cerrar mensaje de carga después de un momento
    setTimeout(() => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¡Exportación iniciada!',
                text: 'El archivo se descargará automáticamente',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            alert('Exportación iniciada - El archivo se descargará automáticamente');
        }
    }, 1000);
}

// Función para exportar servicios a PDF
function exportarServiciosPDF(event) {
    // Prevenir comportamiento por defecto del botón
    if (event) {
        event.preventDefault();
    }
    
    // Mostrar mensaje de procesamiento
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Generando PDF...',
            text: 'Por favor espere mientras se procesa la exportación',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
    
    // Obtener los parámetros actuales de la URL (filtros y paginación)
    const urlParams = new URLSearchParams(window.location.search);
    
    // Crear la URL de exportación PDF manteniendo todos los filtros
    const basePdfUrl = '<?= url('/servicios/exportar-pdf') ?>';
    const pdfUrl = basePdfUrl + '?' + urlParams.toString();
    
    // Crear un enlace temporal para la descarga
    const link = document.createElement('a');
    link.href = pdfUrl;
    link.style.display = 'none';
    document.body.appendChild(link);
    
    // Simular clic para iniciar descarga
    link.click();
    
    // Limpiar el enlace temporal
    document.body.removeChild(link);
    
    // Cerrar mensaje de carga después de un momento
    setTimeout(() => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¡PDF generado!',
                text: 'El archivo PDF se descargará automáticamente',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            alert('PDF generado - El archivo se descargará automáticamente');
        }
    }, 1500);
}

// Inicializar tooltips
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>