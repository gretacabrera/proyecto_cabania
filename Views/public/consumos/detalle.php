<!-- Vista de detalle de consumo del huésped -->
<div class="container-fluid px-2 px-md-4 py-3 py-md-4">
    <div class="row">
        <div class="col-12">
            <!-- Card única con todo el contenido -->
            <div class="card shadow-sm">
                <!-- Header -->
                <div class="card-header bg-light">
                    <h4 class="mb-3">Detalle del Consumo</h4>
                    <hr>
                    <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                        <a href="<?= url('/huesped/consumos') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <div class="d-flex gap-2">
                            <a href="<?= url('/huesped/consumos/' . $consumo['id_consumo'] . '/edit') ?>" 
                               class="btn btn-warning btn-sm flex-fill flex-sm-grow-0">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <button type="button" 
                                    class="btn btn-danger btn-sm flex-fill flex-sm-grow-0 ml-1" 
                                    onclick="eliminarConsumo(<?= $consumo['id_consumo'] ?>)">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    <!-- Información del Producto/Servicio -->
                    <h5 class="border-bottom pb-2 mb-3">Información del Producto/Servicio</h5>
                    
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="text-muted small d-block">Descripción</label>
                            <span><?= htmlspecialchars($consumo['consumo_descripcion']) ?></span>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="text-muted small d-block">Cantidad</label>
                            <span class="fs-6"><?= intval($consumo['consumo_cantidad']) ?></span>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="text-muted small d-block">Precio Unitario</label>
                            <span class="fw-bold fs-5">$<?= number_format($consumo['item_precio'], 2) ?></span>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="text-muted small d-block">Subtotal</label>
                            <span class="fw-bold text-success fs-4">$<?= number_format($consumo['consumo_total'], 2) ?></span>
                        </div>
                    </div>

                    <br><br>

                    <!-- Información de la Reserva -->
                    <h5 class="border-bottom pb-2 mb-3">Información de la Reserva</h5>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="text-muted small d-block">Cabaña</label>
                            <p class="mb-0 fw-bold"><?= htmlspecialchars($consumo['cabania_nombre']) ?></p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="text-muted small d-block">Fecha de Reserva</label>
                            <p class="mb-0 fw-bold"><?= date('d/m/Y', strtotime($consumo['reserva_fhinicio'])) ?></p>
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
