<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/revisiones') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div>
                <a href="<?= url('/revisiones/' . $reserva['id_reserva'] . '/edit') ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Revisión
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información General -->
        <div class="col-lg-8">
            <div class="card mb-6">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información General</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-1 mb-3">
                            <label class="text-muted small mb-1">Reserva</label>
                            <div class="fw-bold">#<?= $reserva['id_reserva'] ?></div>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="text-muted small mb-1">Cabaña</label>
                            <div class="fw-bold">
                                <?= htmlspecialchars($cabania['cabania_nombre']) ?>
                                <small class="text-muted">(<?= htmlspecialchars($cabania['cabania_codigo']) ?>)</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small mb-1">Fecha de Inicio</label>
                            <div class="fw-bold">
                                <i class="fas fa-calendar-alt text-primary"></i>
                                <?= date('d/m/Y H:i', strtotime($reserva['reserva_fhinicio'])) ?>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="text-muted small mb-1">Fecha de Fin</label>
                            <div class="fw-bold">
                                <i class="fas fa-calendar-check text-success"></i>
                                <?= date('d/m/Y H:i', strtotime($reserva['reserva_fhfin'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalle de Revisiones -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list-check"></i> Elementos Revisados</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($revisiones)): ?>
                        <div class="empty-state py-5 text-center">
                            <div class="mb-4">
                                <i class="fas fa-clipboard-check fa-3x text-muted opacity-50"></i>
                            </div>
                            <h6 class="text-muted">No hay elementos revisados</h6>
                            <p class="text-muted small mb-3">Aún no se han registrado daños para esta revisión.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="border-0 py-3">Elemento</th>
                                        <th class="border-0 py-3 text-end">Costo por Daño</th>
                                        <th class="border-0 py-3 text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($revisiones as $rev): ?>
                                        <tr>
                                            <td class="border-0 py-3">
                                                <i class="fas fa-box text-primary"></i>
                                                <?= htmlspecialchars($rev['inventario_descripcion']) ?>
                                            </td>
                                            <td class="border-0 py-3 text-end">
                                                <span class="fs-6">
                                                    $<?= number_format($rev['revision_costo'], 2) ?>
                                                </span>
                                            </td>
                                            <td class="border-0 py-3 text-center">
                                                <?php if ($rev['revision_estado'] == 1): ?>
                                                    <span>Activo</span>
                                                <?php else: ?>
                                                    <span>Anulado</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th class="border-0 py-3">Total Costos por Daños</th>
                                        <th class="border-0 py-3 text-end">
                                            <span class="badge bg-danger fs-5">
                                                $<?= number_format($totalCosto, 2) ?>
                                            </span>
                                        </th>
                                        <th class="border-0"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">

            <!-- Información Adicional -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Información Adicional</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <p><strong>Capacidad de la cabaña:</strong> <?= $cabania['cabania_capacidad'] ?> personas</p>
                        <p><strong>Habitaciones:</strong> <?= $cabania['cabania_cantidadhabitaciones'] ?></p>
                        <p><strong>Baños:</strong> <?= $cabania['cabania_cantidadbanios'] ?></p>
                        <p class="mb-0"><strong>Ubicación:</strong> <?= htmlspecialchars($cabania['cabania_ubicacion']) ?></p>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function anularRevision(idReserva) {
    Swal.fire({
        title: '¿Anular revisión?',
        text: 'Se anularán todas las revisiones de esta reserva',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('/revisiones/') ?>' + idReserva + '/delete';
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
