<div class="container-fluid">
    <!-- Encabezado moderno similar al diseño de referencia -->
    <div class="card border-0 shadow-sm">
        <!-- Header oscuro -->
        <div class="card-header text-dark py-3 mb-0">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0">Gestión de Cabañas</h4>
                </div>
                <div class="col-auto">
                    <a href="<?= url('/cabanias/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Nueva Cabaña
                    </a>
                </div>
            </div>
        </div>
        <!-- Filtros compactos -->
        <div class="card-body pb-0">
            <form method="GET" action="<?= url('/cabanias') ?>" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label small mb-1 text-muted">Filtros de búsqueda</label>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Nombre</label>
                        <input type="text" name="cabania_nombre" class="form-control form-control-sm" 
                               placeholder="" value="<?= htmlspecialchars($_GET['cabania_nombre'] ?? '') ?>" style="width: 150px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Capacidad</label>
                        <input type="number" name="cabania_capacidad" class="form-control form-control-sm" 
                               placeholder="Personas" value="<?= htmlspecialchars($_GET['cabania_capacidad'] ?? '') ?>" 
                               min="1" max="20" style="width: 120px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Habitaciones</label>
                        <input type="number" name="cabania_habitaciones" class="form-control form-control-sm" 
                               placeholder="Hab." value="<?= htmlspecialchars($_GET['cabania_habitaciones'] ?? '') ?>" 
                               min="1" max="10" style="width: 100px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Baños</label>
                        <input type="number" name="cabania_banios" class="form-control form-control-sm" 
                               placeholder="Baños" value="<?= htmlspecialchars($_GET['cabania_banios'] ?? '') ?>" 
                               min="1" max="10" style="width: 90px;">
                    </div>
                    <div class="col-auto ms-auto">
                        <label class="form-label small mb-1">Estado</label>
                        <select name="cabania_estado" class="form-select form-select-sm" style="width: 120px;">
                            <option value="">Todos</option>
                            <option value="1" <?= ($_GET['cabania_estado'] ?? '') == '1' ? 'selected' : '' ?>>Activa</option>
                            <option value="2" <?= ($_GET['cabania_estado'] ?? '') == '2' ? 'selected' : '' ?>>Ocupada</option>
                            <option value="0" <?= ($_GET['cabania_estado'] ?? '') == '0' ? 'selected' : '' ?>>Inactiva</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary btn-sm" title="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?= url('/cabanias') ?>" class="btn btn-info btn-sm" title="Limpiar filtros">
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
                    <div class="col"></div> <!-- Espaciador para empujar el botón a la derecha -->
                    <div class="col-auto">
                        <div class="btn-group" role="group">
                            <button type="button" onclick="exportarCabanias(event)" class="btn btn-success btn-sm" title="Exportar a Excel">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                            <button type="button" onclick="exportarCabaniasPDF(event)" class="btn btn-danger btn-sm" title="Exportar a PDF">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla estilo moderno -->
        <div class="card-body p-0">
            <?php if (empty($cabanias)): ?>
                <div class="empty-state py-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-home fa-3x text-muted opacity-50"></i>
                    </div>
                    <h6 class="text-muted">No se encontraron cabañas</h6>
                    <p class="text-muted small mb-3">Intenta modificar los filtros o crea una nueva cabaña.</p>
                    <a href="<?= url('/admin/cabanias/formulario') ?>" class="btn btn-outline-dark btn-sm">
                        <i class="fas fa-plus fa-sm"></i> Crear cabaña
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
                    <table id="tablaCabanias" class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0 py-3">Código</th>
                                <th class="border-0 py-3">Cabaña</th>
                                <th class="border-0 py-3">Capacidad</th>
                                <th class="border-0 py-3">Habitaciones</th>
                                <th class="border-0 py-3">Baños</th>
                                <th class="border-0 py-3">Estado</th>
                                <th class="border-0 py-3">Precio</th>
                                <th class="border-0 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cabanias as $index => $cabania): ?>
                                <tr>
                                    <td class="border-0 py-3">
                                        <div class="small text-muted">
                                            <?= htmlspecialchars($cabania['cabania_codigo']) ?>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div class="fw-medium text-dark"><?= htmlspecialchars($cabania['cabania_nombre']) ?></div>
                                                <div class="small text-muted">
                                                    <?= htmlspecialchars(substr($cabania['cabania_descripcion'], 0, 40)) ?>
                                                    <?= strlen($cabania['cabania_descripcion']) > 40 ? '...' : '' ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-users text-primary me-2"></i>
                                            <span class="text-dark ml-2"><?= $cabania['cabania_capacidad'] ?></span>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-bed text-info me-2"></i>
                                            <span class="text-dark ml-2"><?= $cabania['cabania_cantidadhabitaciones'] ?></span>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-bath text-warning me-2"></i>
                                            <span class="text-dark ml-2"><?= $cabania['cabania_cantidadbanios'] ?></span>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <span class="fw-medium text-success">$<?= number_format($cabania['cabania_precio'], 0, '.', ',') ?></span>
                                        <small class="text-muted d-block">p/Noche</small>
                                    </td>                                    
                                    <td class="border-0 py-3">
                                        <?php if ($cabania['cabania_estado'] == 1): ?>
                                            <span class="badge bg-success text-white px-2 py-1 rounded-pill">Activa</span>
                                        <?php elseif ($cabania['cabania_estado'] == 2): ?>
                                            <span class="badge bg-warning text-dark px-2 py-1 rounded-pill">Ocupada</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-white px-2 py-1 rounded-pill">Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border-0 py-3 text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= url('/cabanias/' . $cabania['id_cabania']) ?>"
                                               class="btn btn-outline-primary btn-sm"
                                               title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= url('/cabanias/' . $cabania['id_cabania']) . '/edit'?>"
                                               class="btn btn-outline-warning btn-sm"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($cabania['cabania_estado'] == 1): ?>
                                                <!-- Cabaña activa: puede marcar como ocupada o desactivar -->
                                                <button class="btn btn-outline-warning btn-sm"
                                                        onclick="cambiarEstadoCabania(<?= $cabania['id_cabania'] ?>, 2, '<?= addslashes($cabania['cabania_nombre']) ?>')"
                                                        title="Marcar como ocupada">
                                                    <i class="fas fa-home"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm"
                                                        onclick="cambiarEstadoCabania(<?= $cabania['id_cabania'] ?>, 0, '<?= addslashes($cabania['cabania_nombre']) ?>')"
                                                        title="Desactivar">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php elseif ($cabania['cabania_estado'] == 2): ?>
                                                <!-- Cabaña ocupada: puede liberar (activar) o desactivar -->
                                                <button class="btn btn-outline-success btn-sm"
                                                        onclick="cambiarEstadoCabania(<?= $cabania['id_cabania'] ?>, 1, '<?= addslashes($cabania['cabania_nombre']) ?>')"
                                                        title="Liberar cabaña">
                                                    <i class="fas fa-unlock"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm"
                                                        onclick="cambiarEstadoCabania(<?= $cabania['id_cabania'] ?>, 0, '<?= addslashes($cabania['cabania_nombre']) ?>')"
                                                        title="Desactivar">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php else: ?>
                                                <!-- Cabaña inactiva: solo puede activar -->
                                                <button class="btn btn-outline-success btn-sm"
                                                        onclick="cambiarEstadoCabania(<?= $cabania['id_cabania'] ?>, 1, '<?= addslashes($cabania['cabania_nombre']) ?>')"
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

<!-- JavaScript para funcionalidades -->
<script>
function cambiarEstadoCabania(id, nuevoEstado, nombre) {
    let accion, mensaje, color;
    
    switch(nuevoEstado) {
        case 1:
            accion = 'activar';
            mensaje = 'La cabaña estará disponible para reservas';
            color = '#28a745';
            break;
        case 2:
            accion = 'marcar como ocupada';
            mensaje = 'La cabaña se marcará como ocupada por huéspedes';
            color = '#ffc107';
            break;
        case 0:
            accion = 'desactivar';
            mensaje = 'La cabaña no estará disponible para reservas';
            color = '#dc3545';
            break;
        default:
            accion = 'cambiar estado';
            mensaje = '';
            color = '#6c757d';
    }
    
    console.log('Cambiando estado:', { id, nuevoEstado, nombre, accion });
    
    // Usar SweetAlert si está disponible, sino usar confirm simple
    const confirmar = typeof Swal !== 'undefined' ? 
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} cabaña?`,
            text: `¿Está seguro que desea ${accion} la cabaña "${nombre}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then(result => result.isConfirmed) :
        Promise.resolve(confirm(`¿Está seguro que desea ${accion} la cabaña "${nombre}"?`));
    
    confirmar.then(confirmed => {
        if (confirmed) {
            const url = `<?= url('/cabanias') ?>/${id}/estado`;
            console.log('URL de petición:', url);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({estado: nuevoEstado})
            })
            .then(response => {
                console.log('Respuesta recibida:', response.status, response.statusText);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                
                if (data.success) {
                    // Usar SweetAlert para éxito si está disponible
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: `Cabaña ${accion}da correctamente`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        alert(`Cabaña ${accion}da correctamente`);
                        location.reload();
                    }
                } else {
                    const errorMsg = 'Error al cambiar el estado: ' + (data.message || 'Error desconocido');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', errorMsg, 'error');
                    } else {
                        alert(errorMsg);
                    }
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                const errorMsg = 'Error al cambiar el estado de la cabaña: ' + error.message;
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', errorMsg, 'error');
                } else {
                    alert(errorMsg);
                }
            });
        }
    });
}

// Función para exportar cabañas a Excel (.xlsx)
function exportarCabanias(event) {
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
    const baseExportUrl = '<?= url('/cabanias/exportar') ?>';
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

// Función para exportar cabañas a PDF
function exportarCabaniasPDF(event) {
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
    const basePdfUrl = '<?= url('/cabanias/exportar-pdf') ?>';
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
    }, 1000);
}

</script>
