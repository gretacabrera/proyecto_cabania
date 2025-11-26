<!-- Vista de consumos del huésped -->
<div class="container-fluid px-2 px-md-4 py-3 py-md-4">
    <div class="row">
        <div class="col-12">
            <!-- Header sobrio -->
            <div class="card border-0 mb-3" style="background: #f8f9fa;">
                <div class="card-body py-2 px-3">
                    <!-- Estructura estandarizada: Botón Volver + Título -->
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <!-- Botón Volver -->
                        <div>
                            <a href="<?= url('/mis-reservas') ?>" class="btn btn-link text-secondary p-1" title="Volver a Mis Reservas">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                        
                        <!-- Título -->
                        <div class="flex-grow-1">
                            <h6 class="mb-0 text-secondary" style="font-size: 0.95rem; font-weight: 400;">
                                <span class="d-none d-sm-inline">Mis Consumos</span>
                                <span class="d-inline d-sm-none">Consumos</span>
                            </h6>
                        </div>
                    </div>
                    
                    <!-- Fila de botón -->
                    <?php 
                    // Estados permitidos: 1=Pendiente, 2=Confirmada, 3=En Curso, 4=Pendiente de Pago, 8=Pendiente de Revisión
                    // Estados NO permitidos: 5=Finalizada, 6=Anulada
                    $estadosPermitidos = [1, 2, 3, 4, 8];
                    $puedeAgregarConsumos = $reservaSeleccionada && in_array($reservaSeleccionada['rela_estadoreserva'], $estadosPermitidos);
                    
                    // Verificar si estamos en vista de reserva específica (viene por parámetro GET)
                    $esVistaReserva = !empty($_GET['reserva_id']);
                    ?>
                    <div class="d-flex gap-2">
                        <?php if ($esVistaReserva): ?>
                            <a href="<?= url('/huesped/consumos') ?>" class="btn btn-sm btn-outline-secondary" style="min-width: 100px;">
                                <i class="fas fa-list me-1"></i>Todos
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($puedeAgregarConsumos): ?>
                            <a href="<?= url('/huesped/consumos/solicitar') ?>" class="btn btn-sm btn-outline-primary" style="min-width: 120px;">
                                <i class="fas fa-plus me-1"></i>Solicitar
                            </a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-outline-secondary" disabled title="No se pueden agregar consumos en el estado actual de la reserva" style="min-width: 120px;">
                                <i class="fas fa-lock me-1"></i>Solicitar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Filtro por Reserva - Botones de selección rápida -->
                <?php if (!empty($reservas) && count($reservas) > 1): ?>
                    <div class="card-body p-3 mb-1">
                        <label class="form-label fw-bold mb-2">
                            <i class="fas fa-filter"></i> Filtrar por Reserva
                        </label>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="<?= url('/huesped/consumos') ?>" 
                               class="btn btn-sm <?= empty($_GET['reserva_id']) ? 'btn-primary' : 'btn-outline-primary' ?>">
                                Todas las reservas
                            </a>
                            <?php foreach ($reservas as $reserva): ?>
                                <a href="<?= url('/huesped/consumos?reserva_id=' . $reserva['id_reserva']) ?>" 
                                   class="btn btn-sm <?= (!empty($_GET['reserva_id']) && $_GET['reserva_id'] == $reserva['id_reserva']) ? 'btn-primary' : 'btn-outline-primary' ?>">
                                    <?= htmlspecialchars($reserva['cabania_nombre']) ?> - <?= date('d/m/Y', strtotime($reserva['reserva_fhinicio'])) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Alerta informativa según estado de la reserva -->
                <?php if ($reservaSeleccionada && !in_array($reservaSeleccionada['rela_estadoreserva'], [1, 2, 3, 4, 8])): ?>
                    <div class="card-body p-3 mb-1">
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Nota:</strong> No se pueden agregar nuevos consumos porque la reserva está en estado 
                            <strong><?= htmlspecialchars($reservaSeleccionada['estadoreserva_descripcion']) ?></strong>.
                            Solo se permiten solicitar consumos en reservas activas o en proceso de confirmación.
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($reservaSeleccionada && $reservaSeleccionada['reserva_online'] == 1 && $fechaFactura): ?>
                    <div class="card-body p-3 mb-1">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Información:</strong> Esta es una reserva online. Los consumos agregados antes del pago inicial no pueden editarse ni cancelarse. Los consumos posteriores sí pueden modificarse mientras estén en estado "Solicitud Pendiente".
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Lista de Consumos - Cards responsive -->
                <?php if (!empty($consumos)): ?>
                    <!-- Header con total en móvil -->
                    <div class="card shadow-sm mb-2 d-block d-md-none">
                        <div class="card-body p-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Total de consumos:</span>
                                <span class="fs-5 text-success fw-bold">$<?= number_format($totalConsumos, 2) ?></span>
                            </div>
                            <small class="text-muted"><?= count($consumos) ?> items</small>
                        </div>
                    </div>

                    <!-- Vista móvil: Cards -->
                    <div class="d-block d-md-none">
                        <?php foreach ($consumos as $consumo): ?>
                    <div class="card shadow-sm mb-2">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1">
                                    <div class="fw-bold"><?= htmlspecialchars($consumo['consumo_descripcion']) ?></div>
                                </div>
                                <span class="badge bg-info ms-2"><?= intval($consumo['consumo_cantidad']) ?></span>
                            </div>                                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                        <div>
                                            <small class="text-muted d-block">Precio unitario</small>
                                            <span class="fw-bold">$<?= number_format($consumo['item_precio'], 2) ?></span>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted d-block">Subtotal</small>
                                            <span class="fs-5 fw-bold text-success">$<?= number_format($consumo['consumo_total'], 2) ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2 pt-2 border-top">
                                        <div class="btn-group w-100" role="group">
                                            <a href="<?= url('/huesped/consumos/' . $consumo['id_consumo']) ?>" 
                                            class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                            <?php 
                                            // Determinar si el consumo es editable/cancelable
                                            $esEditable = false;
                                            $motivoBloqueo = '';
                                            
                                            // 1. Verificar estado de la reserva
                                            if ($reservaSeleccionada && in_array($reservaSeleccionada['rela_estadoreserva'], [1, 2, 3, 4, 8])) {
                                                // 2. Verificar estado del consumo (solo "solicitud pendiente" = 1)
                                                if ($consumo['rela_estadoconsumo'] == 1) {
                                                    // 3. Si es reserva online, verificar si ya fue facturado
                                                    if ($reservaSeleccionada['reserva_online'] == 1 && $fechaFactura) {
                                                        if ($consumo['consumo_fechahora'] > $fechaFactura) {
                                                            $esEditable = true;
                                                        } else {
                                                            $motivoBloqueo = 'Consumo ya facturado y pagado';
                                                        }
                                                    } else {
                                                        // Reserva in-situ o sin factura aún
                                                        $esEditable = true;
                                                    }
                                                } else {
                                                    $motivoBloqueo = 'Solo se pueden editar consumos en estado Solicitud Pendiente';
                                                }
                                            } else {
                                                $motivoBloqueo = 'Estado de reserva no permite modificaciones';
                                            }
                                            ?>
                                            <?php if ($esEditable): ?>
                                                <a href="<?= url('/huesped/consumos/' . $consumo['id_consumo'] . '/edit') ?>" 
                                                class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        onclick="eliminarConsumo(<?= $consumo['id_consumo'] ?>)">
                                                    <i class="fas fa-trash"></i> Cancelar
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-secondary" disabled title="<?= htmlspecialchars($motivoBloqueo) ?>">
                                                    <i class="fas fa-lock"></i> Editar
                                                </button>
                                                <button type="button" class="btn btn-sm btn-secondary" disabled title="<?= htmlspecialchars($motivoBloqueo) ?>">
                                                    <i class="fas fa-lock"></i> Cancelar
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Vista desktop: Tabla -->
                    <div class="card shadow-sm d-none d-md-block">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-list"></i> Consumos Registrados 
                                <span class="badge bg-primary"><?= count($consumos) ?> items</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Producto/Servicio</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-end">Precio Unit.</th>
                                            <th class="text-end">Subtotal</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($consumos as $consumo): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?= htmlspecialchars($consumo['consumo_descripcion']) ?></div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info"><?= intval($consumo['consumo_cantidad']) ?></span>
                                                </td>
                                                <td class="text-end">$<?= number_format($consumo['item_precio'], 2) ?></td>
                                                <td class="text-end fw-bold">$<?= number_format($consumo['consumo_total'], 2) ?></td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="<?= url('/huesped/consumos/' . $consumo['id_consumo']) ?>" 
                                                        class="btn btn-outline-primary" title="Ver detalle">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php 
                                                        // Determinar si el consumo es editable/cancelable
                                                        $esEditable = false;
                                                        $motivoBloqueo = '';
                                                        
                                                        // 1. Verificar estado de la reserva
                                                        if ($reservaSeleccionada && in_array($reservaSeleccionada['rela_estadoreserva'], [1, 2, 3, 4, 8])) {
                                                            // 2. Verificar estado del consumo (solo "solicitud pendiente" = 1)
                                                            if ($consumo['rela_estadoconsumo'] == 1) {
                                                                // 3. Si es reserva online, verificar si ya fue facturado
                                                                if ($reservaSeleccionada['reserva_online'] == 1 && $fechaFactura) {
                                                                    if ($consumo['consumo_fechahora'] > $fechaFactura) {
                                                                        $esEditable = true;
                                                                    } else {
                                                                        $motivoBloqueo = 'Consumo ya facturado y pagado';
                                                                    }
                                                                } else {
                                                                    // Reserva in-situ o sin factura aún
                                                                    $esEditable = true;
                                                                }
                                                            } else {
                                                                $motivoBloqueo = 'Solo se pueden editar consumos en estado Solicitud Pendiente';
                                                            }
                                                        } else {
                                                            $motivoBloqueo = 'Estado de reserva no permite modificaciones';
                                                        }
                                                        ?>
                                                        <?php if ($esEditable): ?>
                                                            <a href="<?= url('/huesped/consumos/' . $consumo['id_consumo'] . '/edit') ?>" 
                                                            class="btn btn-outline-warning" title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button type="button" 
                                                                    class="btn btn-outline-danger" 
                                                                    onclick="eliminarConsumo(<?= $consumo['id_consumo'] ?>)"
                                                                    title="Cancelar">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-secondary" disabled title="<?= htmlspecialchars($motivoBloqueo) ?>">
                                                                <i class="fas fa-lock"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-secondary" disabled title="<?= htmlspecialchars($motivoBloqueo) ?>">
                                                                <i class="fas fa-lock"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">TOTAL:</td>
                                            <td class="text-end fw-bold text-success fs-5">$<?= number_format($totalConsumos, 2) ?></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No hay consumos registrados</h4>
                            <p class="text-muted mb-4">Aún no has solicitado ningún producto o servicio para esta reserva.</p>
                            <a href="<?= url('/huesped/consumos/solicitar') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Solicitar Consumos
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function eliminarConsumo(id) {
    Swal.fire({
        title: '¿Eliminar consumo?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`<?= url('/huesped/consumos/') ?>${id}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: data.message,
                        timer: 2000
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            });
        }
    });
}
</script>