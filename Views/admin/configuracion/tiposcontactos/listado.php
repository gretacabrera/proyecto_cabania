<div class="container-fluid">
    <!-- Encabezado moderno similar al diseño de referencia -->
    <div class="card border-0 shadow-sm">
        <!-- Header oscuro -->
        <div class="card-header text-dark py-3 mb-0">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0">Gestión de Tipos de Contactos</h4>
                </div>
                <div class="col-auto">
                    <a href="<?= url('/tiposcontactos/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Nuevo Tipo de Contacto
                    </a>
                </div>
            </div>
        </div>
        <!-- Filtros compactos -->
        <div class="card-body pb-0">
            <form method="GET" action="<?= url('/tiposcontactos') ?>" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label small mb-1 text-muted">Filtros de búsqueda</label>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Descripción</label>
                        <input type="text" name="tipocontacto_descripcion" class="form-control form-control-sm" 
                               placeholder="" value="<?= htmlspecialchars($_GET['tipocontacto_descripcion'] ?? '') ?>" style="width: 200px;">
                    </div>
                    <div class="col-auto ms-auto">
                        <label class="form-label small mb-1">Estado</label>
                        <select name="tipocontacto_estado" class="form-select form-select-sm" style="width: 120px;">
                            <option value="">Todos</option>
                            <option value="1" <?= ($_GET['tipocontacto_estado'] ?? '') == '1' ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= ($_GET['tipocontacto_estado'] ?? '') == '0' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary btn-sm" title="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?= url('/tiposcontactos') ?>" class="btn btn-info btn-sm" title="Limpiar filtros">
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
                            <button type="button" onclick="exportarTiposContactos(event)" class="btn btn-success btn-sm" title="Exportar a Excel">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                            <button type="button" onclick="exportarTiposContactosPDF(event)" class="btn btn-danger btn-sm" title="Exportar a PDF">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla estilo moderno -->
        <div class="card-body p-0">
            <?php if (empty($tipos_contactos)): ?>
                <div class="empty-state py-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-address-book fa-3x text-muted opacity-50"></i>
                    </div>
                    <h6 class="text-muted">No se encontraron tipos de contactos</h6>
                    <p class="text-muted small mb-3">Intenta modificar los filtros o crea un nuevo tipo de contacto.</p>
                    <a href="<?= url('/tiposcontactos/create') ?>" class="btn btn-outline-dark btn-sm">
                        <i class="fas fa-plus fa-sm"></i> Crear tipo de contacto
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
                    <table id="tablaTiposContactos" class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0 py-3">Descripción</th>
                                <th class="border-0 py-3">Estado</th>
                                <th class="border-0 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tipos_contactos as $tipo): ?>
                                <tr>
                                    <td class="border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-tag text-primary me-2"></i>
                                            <span class="fw-medium text-dark"><?= htmlspecialchars($tipo['tipocontacto_descripcion']) ?></span>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <?php if ($tipo['tipocontacto_estado'] == 1): ?>
                                            <span class="badge bg-success text-white px-2 py-1 rounded-pill">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-white px-2 py-1 rounded-pill">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border-0 py-3 text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= url('/tiposcontactos/' . $tipo['id_tipocontacto']) ?>"
                                               class="btn btn-outline-primary btn-sm"
                                               title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= url('/tiposcontactos/' . $tipo['id_tipocontacto']) . '/edit'?>"
                                               class="btn btn-outline-warning btn-sm"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($tipo['tipocontacto_estado'] == 1): ?>
                                                <button class="btn btn-outline-danger btn-sm"
                                                        onclick="cambiarEstadoTipoContacto(<?= $tipo['id_tipocontacto'] ?>, 0, '<?= addslashes($tipo['tipocontacto_descripcion']) ?>')"
                                                        title="Desactivar">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-outline-success btn-sm"
                                                        onclick="cambiarEstadoTipoContacto(<?= $tipo['id_tipocontacto'] ?>, 1, '<?= addslashes($tipo['tipocontacto_descripcion']) ?>')"
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
function cambiarEstadoTipoContacto(id, nuevoEstado, descripcion) {
    let accion, mensaje, color;
    
    switch(nuevoEstado) {
        case 1:
            accion = 'activar';
            mensaje = 'El tipo de contacto estará disponible para su uso';
            color = '#28a745';
            break;
        case 0:
            accion = 'desactivar';
            mensaje = 'El tipo de contacto no estará disponible para su uso';
            color = '#dc3545';
            break;
        default:
            accion = 'cambiar estado';
            mensaje = '';
            color = '#6c757d';
    }
    
    const confirmar = typeof Swal !== 'undefined' ? 
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} tipo de contacto?`,
            text: `¿Está seguro que desea ${accion} el tipo de contacto "${descripcion}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then(result => result.isConfirmed) :
        Promise.resolve(confirm(`¿Está seguro que desea ${accion} el tipo de contacto "${descripcion}"?`));
    
    confirmar.then(confirmed => {
        if (confirmed) {
            const url = `<?= url('/tiposcontactos') ?>/${id}/estado`;
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({estado: nuevoEstado})
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: `Tipo de contacto ${accion}do correctamente`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        alert(`Tipo de contacto ${accion}do correctamente`);
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
                const errorMsg = 'Error al cambiar el estado del tipo de contacto: ' + error.message;
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', errorMsg, 'error');
                } else {
                    alert(errorMsg);
                }
            });
        }
    });
}

function exportarTiposContactos(event) {
    if (event) event.preventDefault();
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Generando archivo...',
            text: 'Por favor espere mientras se procesa la exportación',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
        });
    }
    
    const urlParams = new URLSearchParams(window.location.search);
    const exportUrl = '<?= url('/tiposcontactos/exportar') ?>?' + urlParams.toString();
    
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

function exportarTiposContactosPDF(event) {
    if (event) event.preventDefault();
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Generando PDF...',
            text: 'Por favor espere mientras se procesa la exportación',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
        });
    }
    
    const urlParams = new URLSearchParams(window.location.search);
    const pdfUrl = '<?= url('/tiposcontactos/exportar-pdf') ?>?' + urlParams.toString();
    
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
