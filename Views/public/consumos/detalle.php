<?php
$this->extend('layouts/main');
$this->section('title', $title);
$this->section('content');
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i><?= $title ?>
                        </h5>
                        <a href="<?= url('/huesped/consumos') ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Información del Producto -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-box text-primary"></i> Información del Producto/Servicio
                            </h5>
                            
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 text-center">
                                            <?php if (!empty($consumo['producto_foto'])): ?>
                                                <img src="<?= url('/imagenes/productos/' . $consumo['producto_foto']) ?>" 
                                                     alt="<?= htmlspecialchars($consumo['item_nombre']) ?>"
                                                     class="img-fluid rounded"
                                                     style="max-height: 200px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center"
                                                     style="height: 200px;">
                                                    <i class="fas fa-box fa-4x text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-8">
                                            <h4 class="mb-3"><?= htmlspecialchars($consumo['item_nombre']) ?></h4>
                                            <p class="text-muted mb-3"><?= htmlspecialchars($consumo['consumo_descripcion']) ?></p>
                                            
                                            <div class="row">
                                                <div class="col-6">
                                                    <p class="mb-2">
                                                        <strong>Precio Unitario:</strong><br>
                                                        <span class="text-success fs-5">$<?= number_format($consumo['item_precio'], 2) ?></span>
                                                    </p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="mb-2">
                                                        <strong>Cantidad:</strong><br>
                                                        <span class="badge bg-primary fs-5"><?= intval($consumo['consumo_cantidad']) ?></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de la Reserva -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-calendar-check text-success"></i> Información de la Reserva
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Cabaña:</label>
                                    <p class="mb-0 fw-bold"><?= htmlspecialchars($consumo['cabania_nombre']) ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Código de Reserva:</label>
                                    <p class="mb-0 fw-bold">Reserva #<?= htmlspecialchars($consumo['id_reserva']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen del Consumo -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-calculator text-warning"></i> Resumen del Consumo
                            </h5>
                            
                            <div class="card border-success">
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <label class="text-muted small">Precio Unitario</label>
                                            <p class="fs-5 fw-bold mb-0">$<?= number_format($consumo['item_precio'], 2) ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="text-muted small">Cantidad</label>
                                            <p class="fs-5 fw-bold mb-0"><?= intval($consumo['consumo_cantidad']) ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="text-muted small">Subtotal</label>
                                            <p class="fs-4 fw-bold text-success mb-0">$<?= number_format($consumo['consumo_total'], 2) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?= url('/huesped/consumos') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <div class="btn-group">
                            <a href="<?= url('/huesped/consumos/' . $consumo['id_consumo'] . '/edit') ?>" 
                               class="btn btn-warning">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <button type="button" 
                                    class="btn btn-danger" 
                                    onclick="eliminarConsumo(<?= $consumo['id_consumo'] ?>)">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </div>
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
                        window.location.href = '<?= url('/huesped/consumos') ?>';
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
