<div class="totem-header">
    <div class="row align-items-center">
        <div class="col-md-4">
            <h3 class="mb-0">
                <i class="fas fa-home text-primary"></i> 
                <?= htmlspecialchars($cabaniaNombre) ?>
            </h3>
            <small class="text-muted">Huésped: <?= htmlspecialchars($huespedNombre) ?></small>
        </div>
        <div class="col-md-4 text-center">
            <h2 class="mb-0 text-primary">Historial de Pedidos</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= url('/totem/menu') ?>" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Volver al Menú
            </a>
        </div>
    </div>
</div>

<div class="totem-card">
    <?php if (!empty($consumos)): ?>
        <div class="mb-4">
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <span>
                    <i class="fas fa-info-circle"></i> 
                    Total de pedidos registrados: <strong><?= count($consumos) ?></strong>
                </span>
                <span class="fs-4 fw-bold">
                    Total: $<?= number_format($totalConsumos, 2) ?>
                </span>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-primary">
                    <tr>
                        <th width="60%">Producto</th>
                        <th width="15%" class="text-center">Cantidad</th>
                        <th width="25%" class="text-end">Subtotal</th>
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
                                             class="rounded me-3"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-secondary rounded me-3 d-flex align-items-center justify-content-center"
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-box text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($consumo['item_nombre']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($consumo['consumo_descripcion']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <span class="badge bg-primary fs-5"><?= intval($consumo['consumo_cantidad']) ?></span>
                            </td>
                            <td class="text-end align-middle">
                                <span class="fw-bold text-success fs-5">$<?= number_format($consumo['consumo_total'], 2) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="2" class="text-end fw-bold fs-4">TOTAL GENERAL:</td>
                        <td class="text-end fw-bold text-success fs-3">$<?= number_format($totalConsumos, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-receipt fa-5x text-muted mb-4"></i>
            <h3 class="text-muted">No hay pedidos registrados</h3>
            <p class="text-muted">Aún no se han realizado pedidos desde este tótem.</p>
            <a href="<?= url('/totem/menu') ?>" class="btn btn-primary btn-totem mt-3">
                <i class="fas fa-shopping-cart"></i> Hacer un Pedido
            </a>
        </div>
    <?php endif; ?>
</div>
