<?php
$this->extend('layouts/main');
$this->section('title', $title);
$this->section('content');
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-shopping-basket text-primary"></i> Mis Consumos</h2>
                <a href="<?= url('/huesped/consumos/solicitar') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Solicitar Consumos
                </a>
            </div>

            <!-- Filtro por Reserva -->
            <?php if (!empty($reservas)): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <label for="filtroReserva" class="form-label fw-bold">
                            <i class="fas fa-filter"></i> Filtrar por Reserva
                        </label>
                        <select id="filtroReserva" class="form-select" onchange="filtrarPorReserva(this.value)">
                            <?php foreach ($reservas as $reserva): ?>
                                <option value="<?= $reserva['id_reserva'] ?>" <?= $reserva['id_reserva'] == $reservaId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($reserva['cabania_nombre']) ?> - 
                                    <?= date('d/m/Y', strtotime($reserva['reserva_fhinicio'])) ?> a 
                                    <?= date('d/m/Y', strtotime($reserva['reserva_fhfin'])) ?>
                                    (<?= htmlspecialchars($reserva['estadoreserva_descripcion']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Lista de Consumos -->
            <?php if (!empty($consumos)): ?>
                <div class="card shadow-sm">
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
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($consumo['producto_foto'])): ?>
                                                        <img src="<?= url('/imagenes/productos/' . $consumo['producto_foto']) ?>" 
                                                             alt="<?= htmlspecialchars($consumo['item_nombre']) ?>"
                                                             class="rounded me-2"
                                                             style="width: 40px; height: 40px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-secondary rounded me-2 d-flex align-items-center justify-content-center"
                                                             style="width: 40px; height: 40px;">
                                                            <i class="fas fa-box text-white"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="fw-bold"><?= htmlspecialchars($consumo['item_nombre']) ?></div>
                                                        <small class="text-muted"><?= htmlspecialchars($consumo['consumo_descripcion']) ?></small>
                                                    </div>
                                                </div>
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
                                                    <a href="<?= url('/huesped/consumos/' . $consumo['id_consumo'] . '/edit') ?>" 
                                                       class="btn btn-outline-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger" 
                                                            onclick="eliminarConsumo(<?= $consumo['id_consumo'] ?>)"
                                                            title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
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
                        <p class="text-muted">Aún no has solicitado ningún producto o servicio para esta reserva.</p>
                        <a href="<?= url('/huesped/consumos/solicitar') ?>" class="btn btn-primary mt-3">
                            <i class="fas fa-plus"></i> Solicitar Consumos
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function filtrarPorReserva(reservaId) {
    window.location.href = '<?= url('/huesped/consumos') ?>?reserva_id=' + reservaId;
}

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

<?php $this->endSection(); ?>
