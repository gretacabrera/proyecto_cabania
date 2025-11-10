<div class="container-fluid">
    <!-- Encabezado moderno similar al diseño de referencia -->
    <div class="card border-0 shadow-sm">
        <!-- Header oscuro -->
        <div class="card-header text-dark py-3 mb-0">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0">Gestión de Estados de Reservas</h4>
                </div>
                <div class="col-auto">
                    <a href="<?= url('/estadosreservas/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Nuevo Estado
                    </a>
                </div>
            </div>
        </div>
        <!-- Filtros compactos -->
        <div class="card-body pb-0">
            <form method="GET" action="<?= url('/estadosreservas') ?>" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label small mb-1 text-muted">Filtros de búsqueda</label>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Descripción</label>
                        <input type="text" name="estadoreserva_descripcion" class="form-control form-control-sm" 
                               placeholder="" value="<?= htmlspecialchars($_GET['estadoreserva_descripcion'] ?? '') ?>" style="width: 200px;">
                    </div>
                    <div class="col-auto ms-auto">
                        <label class="form-label small mb-1">Estado</label>
                        <select name="estadoreserva_estado" class="form-select form-select-sm" style="width: 120px;">
                            <option value="">Todos</option>
                            <option value="1" <?= ($_GET['estadoreserva_estado'] ?? '') == '1' ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= ($_GET['estadoreserva_estado'] ?? '') == '0' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary btn-sm" title="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?= url('/estadosreservas') ?>" class="btn btn-info btn-sm" title="Limpiar filtros">
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
                            <button type="button" onclick="exportarEstadosReservas(event)" class="btn btn-success btn-sm" title="Exportar a Excel">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                            <button type="button" onclick="exportarEstadosReservasPDF(event)" class="btn btn-danger btn-sm" title="Exportar a PDF">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla estilo moderno -->
        <div class="card-body p-0">
            <?php if (empty($estados)): ?>
                <div class="empty-state py-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-tasks fa-3x text-muted opacity-50"></i>
                    </div>
                    <h6 class="text-muted">No se encontraron estados de reservas</h6>
                    <p class="text-muted small mb-3">Intenta modificar los filtros o crea un nuevo estado.</p>
                    <a href="<?= url('/estadosreservas/create') ?>" class="btn btn-outline-dark btn-sm">
                        <i class="fas fa-plus fa-sm"></i> Crear estado
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
                    <table id="tablaEstadosReservas" class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0 py-3">Descripción</th>
                                <th class="border-0 py-3">Estado</th>
                                <th class="border-0 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estados as $index => $estado): ?>
                                <tr>
                                    <td class="border-0 py-3">
                                        <div class="fw-medium text-dark"><?= htmlspecialchars($estado['estadoreserva_descripcion']) ?></div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <?php if ($estado['estadoreserva_estado'] == 1): ?>
                                            <span class="badge bg-success text-white px-2 py-1 rounded-pill">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-white px-2 py-1 rounded-pill">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border-0 py-3 text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= url('/estadosreservas/' . $estado['id_estadoreserva']) ?>"
                                               class="btn btn-outline-primary btn-sm"
                                               title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= url('/estadosreservas/' . $estado['id_estadoreserva'] . '/edit') ?>"
                                               class="btn btn-outline-warning btn-sm"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($estado['estadoreserva_estado'] == 1): ?>
                                                <button class="btn btn-outline-danger btn-sm"
                                                        onclick="cambiarEstadoEstadoReserva(<?= $estado['id_estadoreserva'] ?>, 0, '<?= addslashes($estado['estadoreserva_descripcion']) ?>')"
                                                        title="Desactivar">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-outline-success btn-sm"
                                                        onclick="cambiarEstadoEstadoReserva(<?= $estado['id_estadoreserva'] ?>, 1, '<?= addslashes($estado['estadoreserva_descripcion']) ?>')"
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
function cambiarEstadoEstadoReserva(id, nuevoEstado, descripcion) {
    let accion, mensaje, color;
    
    if (nuevoEstado == 1) {
        accion = 'activar';
        mensaje = 'El estado estará disponible para usar en reservas';
        color = '#28a745';
    } else {
        accion = 'desactivar';
        mensaje = 'El estado no estará disponible para usar en reservas';
        color = '#dc3545';
    }
    
    console.log('Cambiando estado:', { id, nuevoEstado, descripcion, accion });
    
    // Usar SweetAlert si está disponible, sino usar confirm simple
    const confirmar = typeof Swal !== 'undefined' ? 
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} estado?`,
            text: `¿Está seguro que desea ${accion} el estado "${descripcion}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then(result => result.isConfirmed) :
        Promise.resolve(confirm(`¿Está seguro que desea ${accion} el estado "${descripcion}"?`));
    
    confirmar.then(confirmed => {
        if (confirmed) {
            const url = `<?= url('/estadosreservas') ?>/${id}/estado`;
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
                
                // Intentar parsear JSON incluso si hay error
                return response.json().then(data => {
                    if (!response.ok) {
                        // Si hay error, lanzar con el mensaje del servidor
                        throw { status: response.status, data: data };
                    }
                    return data;
                });
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                
                if (data.success) {
                    // Usar SweetAlert para éxito si está disponible
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: data.message || `Estado ${accion}do correctamente`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        alert(data.message || `Estado ${accion}do correctamente`);
                        location.reload();
                    }
                } else {
                    const errorMsg = data.message || 'Error desconocido';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'No se puede cambiar el estado',
                            text: errorMsg,
                            icon: 'warning',
                            confirmButtonText: 'Entendido'
                        });
                    } else {
                        alert(errorMsg);
                    }
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                
                // Si el error tiene datos del servidor, usarlos
                if (error.data && error.data.message) {
                    const errorMsg = error.data.message;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'No se puede cambiar el estado',
                            text: errorMsg,
                            icon: 'warning',
                            confirmButtonText: 'Entendido'
                        });
                    } else {
                        alert(errorMsg);
                    }
                } else {
                    const errorMsg = 'Error al cambiar el estado: ' + (error.message || 'Error de conexión');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', errorMsg, 'error');
                    } else {
                        alert(errorMsg);
                    }
                }
            });
        }
    });
}

// Función para exportar a Excel
function exportarEstadosReservas(event) {
    if (event) {
        event.preventDefault();
    }
    
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
    
    const urlParams = new URLSearchParams(window.location.search);
    const baseExportUrl = '<?= url('/estadosreservas/exportar') ?>';
    const exportUrl = baseExportUrl + '?' + urlParams.toString();
    
    const link = document.createElement('a');
    link.href = exportUrl;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
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

// Función para exportar a PDF
function exportarEstadosReservasPDF(event) {
    if (event) {
        event.preventDefault();
    }
    
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
    
    const urlParams = new URLSearchParams(window.location.search);
    const basePdfUrl = '<?= url('/estadosreservas/exportar-pdf') ?>';
    const pdfUrl = basePdfUrl + '?' + urlParams.toString();
    
    const link = document.createElement('a');
    link.href = pdfUrl;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
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
