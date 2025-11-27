<div class="container-fluid">
    <!-- Encabezado moderno similar al diseño de referencia -->
    <div class="card border-0 shadow-sm">
        <!-- Header oscuro -->
        <div class="card-header text-dark py-3 mb-0">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0">Gestión de Consumos</h4>
                </div>
                <?php 
                $userProfile = \App\Core\Auth::getUserProfile();
                $isEncargadoBar = ($userProfile === 'encargado bar');
                ?>
                <?php if (!$isEncargadoBar): ?>
                <div class="col-auto">
                    <a href="<?= url('/consumos/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Registrar Consumo
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Filtros compactos -->
        <?php if (!$isEncargadoBar): ?>
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
                        <select name="estado" class="form-select form-select-sm" style="width: 180px;">
                            <option value="">Todos los estados</option>
                            <?php if (isset($estadosConsumo)): ?>
                                <?php foreach ($estadosConsumo as $estado): ?>
                                    <option value="<?= $estado['id_estadoconsumo'] ?>" 
                                            <?= (isset($_GET['estado']) && $_GET['estado'] == $estado['id_estadoconsumo']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($estado['estadoconsumo_descripcion']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
        <?php endif; ?>

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
                                <?php if (!$isEncargadoBar): ?>
                                <th class="border-0 py-3">Estado</th>
                                <?php endif; ?>
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
                                    <?php 
                                    // Definir estado antes de los bloques condicionales
                                    $estadoId = $consumo['rela_estadoconsumo'] ?? 0;
                                    $estadoDesc = $consumo['estadoconsumo_descripcion'] ?? 'Desconocido';
                                    ?>
                                    <?php if (!$isEncargadoBar): ?>
                                    <td class="border-0 py-3">
                                        <?php
                                        // Colores según estado
                                        $badgeClass = 'bg-secondary';
                                        $estadoStyle = '';
                                        switch($estadoId) {
                                            case 1: // Solicitud pendiente
                                                $badgeClass = 'bg-warning text-dark';
                                                break;
                                            case 2: // En proceso
                                                $badgeClass = 'bg-info';
                                                break;
                                            case 3: // Entregado
                                                $badgeClass = 'bg-success';
                                                break;
                                            case 4: // Anulado por falta de stock
                                            case 5: // Anulado por inconveniente
                                                $badgeClass = 'bg-danger';
                                                break;
                                            case 6: // Cancelado por usuario
                                                $badgeClass = 'bg-dark';
                                                break;
                                            case 7: // Anulado por pérdida
                                                $badgeClass = 'bg-secondary';
                                                $estadoStyle = 'text-decoration: line-through;';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?> text-white px-2 py-1 rounded-pill" style="<?= $estadoStyle ?>">
                                            <?= htmlspecialchars($estadoDesc) ?>
                                        </span>
                                    </td>
                                    <?php endif; ?>
                                    
                                    <!-- Columna de Acciones -->
                                    <td class="border-0 py-3 text-center">
                                        <?php if ($isEncargadoBar): ?>
                                            <!-- Botones para Encargado Bar según estado -->
                                            <div class="btn-group btn-group-sm" role="group">
                                                <?php if ($estadoId == 1): // Solicitud pendiente ?>
                                                    <button onclick="cambiarEstadoConCantidad(<?= $consumo['id_consumo'] ?>, 2, '<?= htmlspecialchars($consumo['consumo_descripcion']) ?>', <?= $consumo['consumo_cantidad'] ?>)" 
                                                            class="btn btn-success btn-sm" title="Aceptar">
                                                        <i class="fas fa-check"></i> Aceptar
                                                    </button>
                                                    <button onclick="cambiarEstadoBar(<?= $consumo['id_consumo'] ?>, 4, '<?= htmlspecialchars($consumo['consumo_descripcion']) ?>', true)" 
                                                            class="btn btn-warning btn-sm" title="Anular por stock">
                                                        <i class="fas fa-box-open"></i> Sin stock
                                                    </button>
                                                <?php elseif ($estadoId == 2): // En proceso ?>
                                                    <button onclick="confirmarEntrega(<?= $consumo['id_consumo'] ?>, '<?= htmlspecialchars($consumo['consumo_descripcion']) ?>', <?= $consumo['consumo_cantidad'] ?>)" 
                                                            class="btn btn-primary btn-sm" title="Entregar">
                                                        <i class="fas fa-check-circle"></i> Entregar
                                                    </button>
                                                    <button onclick="confirmarAnulacionStock(<?= $consumo['id_consumo'] ?>, '<?= htmlspecialchars($consumo['consumo_descripcion']) ?>')" 
                                                            class="btn btn-warning btn-sm" title="Anular por stock">
                                                        <i class="fas fa-box-open"></i> Sin stock
                                                    </button>
                                                    <button onclick="confirmarAnulacionInconveniente(<?= $consumo['id_consumo'] ?>, '<?= htmlspecialchars($consumo['consumo_descripcion']) ?>')" 
                                                            class="btn btn-danger btn-sm" title="Anular por inconveniente">
                                                        <i class="fas fa-times-circle"></i> Anular
                                                    </button>
                                                    <button onclick="confirmarPerdida(<?= $consumo['id_consumo'] ?>, '<?= htmlspecialchars($consumo['consumo_descripcion']) ?>')" 
                                                            class="btn btn-dark btn-sm" title="Anular por pérdida">
                                                        <i class="fas fa-exclamation-triangle"></i> Pérdida
                                                    </button>
                                                <?php elseif ($estadoId == 3): // Entregado ?>
                                                    <button onclick="confirmarAnulacionInconveniente(<?= $consumo['id_consumo'] ?>, '<?= htmlspecialchars($consumo['consumo_descripcion']) ?>')" 
                                                            class="btn btn-danger btn-sm" title="Anular por inconveniente">
                                                        <i class="fas fa-times-circle"></i> Anular por inconveniente
                                                    </button>
                                                    <button onclick="confirmarPerdida(<?= $consumo['id_consumo'] ?>, '<?= htmlspecialchars($consumo['consumo_descripcion']) ?>')" 
                                                            class="btn btn-dark btn-sm" title="Registrar como pérdida">
                                                        <i class="fas fa-exclamation-triangle"></i> Registrar pérdida
                                                    </button>
                                                <?php elseif ($estadoId == 7): // Anulado por pérdida ?>
                                                    <button onclick="reactivarConsumoPerdida(<?= $consumo['id_consumo'] ?>, '<?= htmlspecialchars($consumo['consumo_descripcion']) ?>', <?= $consumo['consumo_cantidad'] ?>)" 
                                                            class="btn btn-warning btn-sm" title="Reactivar consumo">
                                                        <i class="fas fa-redo"></i> Reactivar
                                                    </button>
                                                    <span class="text-muted small ms-2">Pérdida registrada</span>
                                                <?php else: ?>
                                                    <span class="text-muted small">Sin acciones</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <!-- Botones para Admin -->
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
                                            </div>
                                        <?php endif; ?>
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
// Función para cambiar estado CON edición de cantidad (Aceptar y Entregar)
function cambiarEstadoConCantidad(id, nuevoEstado, descripcion, cantidadActual) {
    let accion, mensaje, tituloEstado;
    
    switch(nuevoEstado) {
        case 2:
            accion = 'aceptar';
            tituloEstado = 'Aceptar Pedido';
            mensaje = 'Confirme la cantidad a procesar';
            break;
        case 3:
            accion = 'entregar';
            tituloEstado = 'Entregar Pedido';
            mensaje = 'Confirme la cantidad a entregar';
            break;
        default:
            accion = 'cambiar estado';
            mensaje = 'Confirme la cantidad';
    }
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: tituloEstado,
            html: `
                <p class="mb-3"><strong>${descripcion}</strong></p>
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <label class="mb-0 mr-2">${mensaje}:</label>
                    <input type="number" id="cantidadInput" class="form-control" style="width: 70px;"
                           value="${cantidadActual}" min="1" step="1">
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#3085d6',
            preConfirm: () => {
                const cantidad = document.getElementById('cantidadInput').value;
                if (!cantidad || cantidad < 1) {
                    Swal.showValidationMessage('Debe ingresar una cantidad válida');
                    return false;
                }
                return cantidad;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                ejecutarCambioEstadoConCantidad(id, nuevoEstado, result.value);
            }
        });
    } else {
        const nuevaCantidad = prompt(`${mensaje}:\n\n${descripcion}\n\nCantidad actual: ${cantidadActual}`, cantidadActual);
        if (nuevaCantidad !== null && nuevaCantidad > 0) {
            ejecutarCambioEstadoConCantidad(id, nuevoEstado, nuevaCantidad);
        }
    }
}

function ejecutarCambioEstadoConCantidad(id, nuevoEstado, cantidad) {
    fetch(`<?= url('/consumos/') ?>${id}/estado`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ 
            nuevo_estado: nuevoEstado,
            cantidad: cantidad 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Estado actualizado!',
                    text: data.message || 'El estado y cantidad del consumo se han actualizado correctamente',
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
            alert('Error: Hubo un problema al actualizar el estado');
        }
    });
}

// Función para cambiar estado SIN edición de cantidad (Anulaciones)
function cambiarEstadoBar(id, nuevoEstado, descripcion, esAnulacion = false) {
    let accion, mensaje, tituloEstado;
    
    switch(nuevoEstado) {
        case 2:
            accion = 'aceptar';
            tituloEstado = 'En Proceso';
            mensaje = 'El pedido pasará a estado "En Proceso"';
            break;
        case 3:
            accion = 'entregar';
            tituloEstado = 'Entregado';
            mensaje = 'El pedido se marcará como "Entregado"';
            break;
        case 4:
            accion = 'anular por falta de stock';
            tituloEstado = 'Anulado';
            mensaje = 'El pedido se anulará por falta de stock';
            break;
        case 5:
            accion = 'anular por inconveniente';
            tituloEstado = 'Anulado';
            mensaje = 'El pedido se anulará por inconveniente';
            break;
        case 7:
            accion = 'registrar pérdida';
            tituloEstado = 'Anulado por Pérdida';
            mensaje = 'El pedido se marcará como pérdida (NO se reintegra stock)';
            break;
        default:
            accion = 'cambiar estado';
            mensaje = '';
    }
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)}?`,
            html: `<p><strong>${descripcion}</strong></p><p>${mensaje}</p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#3085d6'
        }).then((result) => {
            if (result.isConfirmed) {
                ejecutarCambioEstadoBar(id, nuevoEstado);
            }
        });
    } else {
        if (confirm(`¿Está seguro que desea ${accion} este consumo?\n\n${descripcion}\n${mensaje}`)) {
            ejecutarCambioEstadoBar(id, nuevoEstado);
        }
    }
}

function ejecutarCambioEstadoBar(id, nuevoEstado) {
    fetch(`<?= url('/consumos/') ?>${id}/estado`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ nuevo_estado: nuevoEstado })
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
            alert('Error: Hubo un problema al actualizar el estado');
        }
    });
}

function reactivarConsumoPerdida(consumoId, descripcion, cantidad) {
    Swal.fire({
        title: 'Motivo de Reactivación',
        html: `
            <p class="mb-3">Seleccione el motivo por el cual desea reactivar este consumo:</p>
            
            <div class="list-group mb-3">
                <button type="button" class="list-group-item list-group-item-action" onclick="confirmarReactivacion(${consumoId}, 'error', '${descripcion}', ${cantidad})">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">Error Administrativo</h6>
                    </div>
                    <p class="mb-1 small">Se marcó como pérdida por equivocación. El producto NO se perdió físicamente.</p>
                    <small class="text-muted">El stock descontado será devuelto y se volverá a descontar al entregar.</small>
                </button>
                
                <button type="button" class="list-group-item list-group-item-action mt-2" onclick="confirmarReactivacion(${consumoId}, 'reintento', '${descripcion}', ${cantidad})">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">Reintento de Preparación</h6>
                    </div>
                    <p class="mb-1 small">El producto SÍ se perdió durante la preparación. Se preparará nuevamente con otro producto.</p>
                    <small class="text-muted">La pérdida quedará registrada y se descontará un nuevo producto al entregar.</small>
                </button>
            </div>
        `,
        showConfirmButton: false,
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        width: '850px'
    });
}

function confirmarReactivacion(consumoId, tipo, descripcion, cantidad) {
    const unidadTexto = cantidad === 1 ? 'unidad' : 'unidades';
    const configs = {
        'error': {
            title: 'Confirmar Corrección de Error',
            html: `
                <div class="alert alert-warning mb-3">
                    <h6 class="mb-2">Escenario: Error Administrativo</h6>
                    <p class="small mb-0">Se marcó como pérdida por equivocación. El producto nunca se perdió físicamente.</p>
                </div>
                
                <p class="mb-3"><strong>Pedido:</strong> ${descripcion} (${cantidad} ${unidadTexto})</p>
                
                <div class="card mb-3">
                    <div class="card-header bg-light py-2">
                        <strong>Flujo del Proceso</strong>
                    </div>
                    <div class="card-body">
                        <div class="text-start small">
                            <p class="mb-2"><strong>1. Registro de pérdida (incorrecto):</strong><br>
                            Se descontaron ${cantidad} ${unidadTexto} del stock por error.</p>
                            
                            <p class="mb-2"><strong>2. Reactivación (ahora):</strong><br>
                            Se devolverán ${cantidad} ${unidadTexto} al stock para corregir el error.<br>
                            Se registra la corrección en el historial de movimientos.</p>
                            
                            <p class="mb-0"><strong>3. Entrega posterior:</strong><br>
                            Al marcar como entregado, se descontarán ${cantidad} ${unidadTexto} normalmente.<br>
                            Se registra el movimiento de salida por entrega.</p>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-success mb-0">
                    <strong>Resultado Final:</strong>
                    <p class="small mb-0 mt-2">
                        Stock final: Original menos ${cantidad} ${unidadTexto}<br>
                        Descuentos netos: ${cantidad} ${unidadTexto} entregadas<br>
                        Pérdidas reales: Ninguna
                    </p>
                </div>
            `,
            icon: 'warning',
            confirmText: 'Sí, corregir error'
        },
        'reintento': {
            title: 'Confirmar Reintento de Preparación',
            html: `
                <div class="alert alert-info mb-3">
                    <h6 class="mb-2">Escenario: Reintento con Nuevo Producto</h6>
                    <p class="small mb-0">El producto se perdió realmente durante la preparación. Se preparará nuevamente.</p>
                </div>
                
                <p class="mb-3"><strong>Pedido:</strong> ${descripcion} (${cantidad} ${unidadTexto})</p>
                
                <div class="card mb-3">
                    <div class="card-header bg-light py-2">
                        <strong>Flujo del Proceso</strong>
                    </div>
                    <div class="card-body">
                        <div class="text-start small">
                            <p class="mb-2"><strong>1. Registro de pérdida (correcto):</strong><br>
                            Se descontaron ${cantidad} ${unidadTexto} del stock por pérdida del producto.<br>
                            Se registra el movimiento de salida por pérdida.</p>
                            
                            <p class="mb-2"><strong>2. Reactivación (ahora):</strong><br>
                            La pérdida ya está registrada correctamente.<br>
                            Se marca el consumo para reintento sin afectar el stock actual.<br>
                            Se registra la operación en el historial.</p>
                            
                            <p class="mb-0"><strong>3. Entrega de reintento:</strong><br>
                            Al marcar como entregado, se descontarán ${cantidad} ${unidadTexto} nuevas.<br>
                            Se registra el movimiento de salida por entrega.</p>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-danger mb-0">
                    <strong>Resultado Final:</strong>
                    <p class="small mb-0 mt-2">
                        Stock final: Original menos ${cantidad * 2} ${unidadTexto}<br>
                        Descuentos totales: ${cantidad * 2} ${unidadTexto} (${cantidad} perdidas + ${cantidad} entregadas)<br>
                        Pérdidas reales: ${cantidad} ${unidadTexto} dañadas durante preparación<br>
                        Entregas exitosas: ${cantidad} ${unidadTexto} entregadas al huésped
                    </p>
                </div>
            `,
            icon: 'question',
            confirmText: 'Sí, reintentar con nuevo producto'
        }
    };
    
    const config = configs[tipo];
    
    Swal.fire({
        title: config.title,
        html: config.html,
        icon: config.icon,
        showCancelButton: true,
        confirmButtonColor: tipo === 'error' ? '#ffc107' : '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: config.confirmText,
        cancelButtonText: 'Cancelar',
        width: '700px'
    }).then((result) => {
        if (result.isConfirmed) {
            reactivarConTipo(consumoId, tipo);
        }
    });
}

function reactivarConTipo(consumoId, tipo) {
    fetch(`<?= url('/consumos/') ?>${consumoId}/estado`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            nuevo_estado: 2,
            tipo_reactivacion: tipo
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Éxito', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error', 'Error al procesar la solicitud', 'error');
    });
}

function ejecutarCambioEstadoBarOriginal(id, nuevoEstado) {
    fetch(`<?= url('/consumos/') ?>${id}/estado`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ nuevo_estado: nuevoEstado })
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
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'No se pudo actualizar el estado'
                });
            } else {
                alert('Error: ' + (data.message || 'No se pudo actualizar el estado'));
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al procesar la solicitud'
            });
        } else {
            alert('Error al procesar la solicitud');
        }
    });
}

// Función para confirmar pérdida con modal informativo detallado
function confirmarPerdida(consumoId, descripcion) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Confirmar Pérdida de Producto',
            html: `
                <div class="alert alert-danger">
                    <strong>ATENCIÓN:</strong> Esta acción registrará una pérdida de producto.
                </div>
                <p class="mb-3"><strong>Pedido:</strong> ${descripcion}</p>
                <hr>
                <div class="text-start">
                    <p class="mb-2"><strong>Efectos de esta acción:</strong></p>
                    <p class="small mb-2">
                        El stock será descontado porque el producto se ha perdido o dañado.<br>
                        El huésped NO será cobrado por este consumo.<br>
                        Se registrará como pérdida en los movimientos de stock.
                    </p>
                    <hr>
                    <p class="mb-2 mt-3"><strong>Casos de uso:</strong></p>
                    <p class="small mb-0">
                        Producto dañado durante preparación o entrega.<br>
                        Devolución aceptada de producto no reintegrable.<br>
                        Cancelación tardía del huésped con producto ya preparado.
                    </p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, registrar pérdida',
            cancelButtonText: 'Cancelar',
            width: '600px'
        }).then((result) => {
            if (result.isConfirmed) {
                ejecutarCambioEstadoBar(consumoId, 7); // Estado 7: Anulado por pérdida
            }
        });
    } else {
        if (confirm(`¿Está seguro que desea registrar este consumo como pérdida?\n\n${descripcion}\n\nEsta acción NO reintegrará el stock.`)) {
            ejecutarCambioEstadoBar(consumoId, 7);
        }
    }
}

// Función para confirmar anulación por falta de stock con modal informativo detallado
function confirmarAnulacionStock(consumoId, descripcion) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Confirmar Anulación por Falta de Stock',
            html: `
                <div class="alert alert-warning">
                    <strong>ATENCIÓN:</strong> Esta acción anulará el pedido por falta de stock del producto solicitado.
                </div>
                <p class="mb-3"><strong>Pedido:</strong> ${descripcion}</p>
                <hr>
                <div class="text-start">
                    <p class="mb-2"><strong>Efectos de esta acción:</strong></p>
                    <p class="small mb-2">
                        El pedido se cancelará sin afectar el stock.<br>
                        El huésped NO será cobrado por este consumo.<br>
                        El pedido quedará marcado como anulado por falta de disponibilidad.
                    </p>
                    <hr>
                    <p class="mb-2 mt-3"><strong>Casos de uso:</strong></p>
                    <p class="small mb-0">
                        No hay suficiente stock disponible para completar el pedido.<br>
                        El producto se agotó antes de poder preparar el pedido.<br>
                        Error en el registro de stock que impide completar la entrega.
                    </p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, anular por stock',
            cancelButtonText: 'Cancelar',
            width: '600px'
        }).then((result) => {
            if (result.isConfirmed) {
                ejecutarCambioEstadoBar(consumoId, 4); // Estado 4: Anulado por falta de stock
            }
        });
    } else {
        if (confirm(`¿Está seguro que desea anular este consumo por falta de stock?\n\n${descripcion}\n\nEsta acción SÍ reintegrará el stock.`)) {
            ejecutarCambioEstadoBar(consumoId, 4);
        }
    }
}

// Función para confirmar anulación por inconveniente con modal informativo detallado
function confirmarAnulacionInconveniente(consumoId, descripcion) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Confirmar Anulación por Inconveniente',
            html: `
                <div class="alert alert-danger">
                    <strong>ATENCIÓN:</strong> Esta acción anulará el pedido por un problema operativo.
                </div>
                <p class="mb-3"><strong>Pedido:</strong> ${descripcion}</p>
                <hr>
                <div class="text-start">
                    <p class="mb-2"><strong>Efectos de esta acción:</strong></p>
                    <p class="small mb-2">
                        El pedido se cancelará sin afectar el stock.<br>
                        El huésped NO será cobrado por este consumo.<br>
                        El pedido quedará marcado como anulado por inconveniente operativo.
                    </p>
                    <hr>
                    <p class="mb-2 mt-3"><strong>Casos de uso:</strong></p>
                    <p class="small mb-0">
                        Error en la preparación del pedido que requiere cancelación.<br>
                        Huésped cancela el pedido después de haberlo solicitado.<br>
                        Problema operativo que impide completar la entrega correctamente.<br>
                        Devolución del producto por parte del huésped en buen estado.
                    </p>
                </div>
            `,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, anular por inconveniente',
            cancelButtonText: 'Cancelar',
            width: '700px'
        }).then((result) => {
            if (result.isConfirmed) {
                ejecutarCambioEstadoBar(consumoId, 5); // Estado 5: Anulado por inconveniente
            }
        });
    } else {
        if (confirm(`¿Está seguro que desea anular este consumo por inconveniente?\n\n${descripcion}\n\nEsta acción SÍ reintegrará el stock.`)) {
            ejecutarCambioEstadoBar(consumoId, 5);
        }
    }
}

// Función para confirmar entrega sin editar cantidad
function confirmarEntrega(consumoId, descripcion, cantidad) {
    const unidadTexto = cantidad === 1 ? 'unidad' : 'unidades';
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Confirmar Entrega',
            html: `
                <div class="alert alert-success">
                    <strong>CONFIRMACIÓN:</strong> Se va a marcar este pedido como entregado.
                </div>
                <p class="mb-3"><strong>Pedido:</strong> ${descripcion}</p>
                <p class="mb-3"><strong>Cantidad:</strong> ${cantidad} ${unidadTexto}</p>
                <hr>
                <div class="text-start">
                    <p class="mb-2"><strong>Efectos de esta acción:</strong></p>
                    <p class="small mb-0">
                        Se descontarán ${cantidad} ${unidadTexto} del stock.<br>
                        El huésped será cobrado por este consumo.<br>
                        El pedido quedará marcado como entregado exitosamente.
                    </p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, confirmar entrega',
            cancelButtonText: 'Cancelar',
            width: '600px'
        }).then((result) => {
            if (result.isConfirmed) {
                ejecutarCambioEstadoBar(consumoId, 3); // Estado 3: Entregado
            }
        });
    } else {
        if (confirm(`¿Está seguro que desea marcar este consumo como entregado?\n\n${descripcion}\n\nCantidad: ${cantidad} ${unidadTexto}`)) {
            ejecutarCambioEstadoBar(consumoId, 3);
        }
    }
}

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