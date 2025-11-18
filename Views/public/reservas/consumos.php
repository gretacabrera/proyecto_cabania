<?php
$pageTitle = 'Consumos de la Reserva';
$pageStyles = ['public.css'];
require_once __DIR__ . '/../../shared/layouts/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/reservas') ?>">Mis Reservas</a></li>
                    <li class="breadcrumb-item active">Consumos</li>
                </ol>
            </nav>
            
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-shopping-cart text-primary me-2"></i>
                    Consumos de la Reserva
                </h2>
                <a href="<?= url('/reservas') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver
                </a>
            </div>
            
            <!-- Información de la reserva -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información de la Reserva</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Cabaña:</strong> <?= htmlspecialchars($reserva['cabania_nombre'] ?? 'No disponible') ?>
                        </div>
                        <div class="col-md-3 mb-3">
                            <strong>Entrada:</strong> <?= date('d/m/Y', strtotime($reserva['reserva_fhinicio'])) ?>
                        </div>
                        <div class="col-md-3 mb-3">
                            <strong>Salida:</strong> <?= date('d/m/Y', strtotime($reserva['reserva_fhfin'])) ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Columna: Listado de consumos existentes -->
                <div class="col-lg-8">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                Consumos Registrados (<?= count($consumos) ?>)
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($consumos)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-shopping-basket fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No hay consumos registrados para esta reserva.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0 py-3">Descripción</th>
                                                <th class="border-0 py-3 text-center">Cantidad</th>
                                                <th class="border-0 py-3 text-end">Total</th>
                                                <th class="border-0 py-3 text-center">Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $totalConsumos = 0;
                                            foreach ($consumos as $consumo): 
                                                $totalConsumos += $consumo['consumo_total'];
                                                // Estado: 1=Pendiente (editable), 2=Confirmado (bloqueado), 3=Abonado (bloqueado)
                                                $esBloqueado = in_array($consumo['consumo_estado'], [2, 3]);
                                            ?>
                                                <tr>
                                                    <td class="border-0 py-3">
                                                        <?= htmlspecialchars($consumo['consumo_descripcion']) ?>
                                                    </td>
                                                    <td class="border-0 py-3 text-center">
                                                        <?= $consumo['consumo_cantidad'] ?>
                                                    </td>
                                                    <td class="border-0 py-3 text-end">
                                                        <strong class="text-success">$<?= number_format($consumo['consumo_total'], 2) ?></strong>
                                                    </td>
                                                    <td class="border-0 py-3 text-center">
                                                        <?php if ($esBloqueado): ?>
                                                            <span class="badge bg-secondary">
                                                                <i class="fas fa-lock me-1"></i>Bloqueado
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">
                                                                <i class="fas fa-clock me-1"></i>Pendiente
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr class="table-light">
                                                <td colspan="2" class="border-0 py-3 text-end"><strong>TOTAL:</strong></td>
                                                <td class="border-0 py-3 text-end">
                                                    <strong class="text-primary fs-5">$<?= number_format($totalConsumos, 2) ?></strong>
                                                </td>
                                                <td class="border-0 py-3"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Columna: Registrar nuevo consumo -->
                <div class="col-lg-4">
                    <div class="card shadow">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-plus-circle me-2"></i>
                                Registrar Nuevo Consumo
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= url('/reservas/' . $reserva['id_reserva'] . '/consumos/registrar') ?>">
                                <div class="mb-3">
                                    <label for="servicio_id" class="form-label">Servicio / Producto</label>
                                    <select name="servicio_id" id="servicio_id" class="form-select form-select-sm" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($productos_disponibles as $producto): ?>
                                            <option value="<?= $producto['id_servicio'] ?>" 
                                                    data-precio="<?= $producto['servicio_precio'] ?>">
                                                <?= htmlspecialchars($producto['servicio_nombre']) ?> 
                                                - $<?= number_format($producto['servicio_precio'], 2) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="cantidad" class="form-label">Cantidad</label>
                                    <input type="number" name="cantidad" id="cantidad" 
                                           class="form-control form-control-sm" 
                                           value="1" min="1" required>
                                </div>
                                
                                <div class="alert alert-info small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Total estimado:</strong> <span id="total-estimado">$0.00</span>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check me-2"></i>Registrar Consumo
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Nota informativa -->
                    <div class="alert alert-warning mt-3">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Importante</h6>
                        <p class="mb-0 small">
                            Solo puedes modificar o eliminar consumos que estén en estado <strong>Pendiente</strong>. 
                            Los consumos confirmados o abonados están bloqueados.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectServicio = document.getElementById('servicio_id');
    const inputCantidad = document.getElementById('cantidad');
    const spanTotal = document.getElementById('total-estimado');
    
    function actualizarTotal() {
        const opcionSeleccionada = selectServicio.options[selectServicio.selectedIndex];
        if (opcionSeleccionada && opcionSeleccionada.dataset.precio) {
            const precio = parseFloat(opcionSeleccionada.dataset.precio);
            const cantidad = parseInt(inputCantidad.value) || 1;
            const total = precio * cantidad;
            spanTotal.textContent = '$' + total.toFixed(2);
        } else {
            spanTotal.textContent = '$0.00';
        }
    }
    
    selectServicio.addEventListener('change', actualizarTotal);
    inputCantidad.addEventListener('input', actualizarTotal);
});
</script>

<?php require_once __DIR__ . '/../../shared/layouts/footer.php'; ?>
