<!-- Vista: Detalle de Ingreso -->
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0"><?= $titulo ?></h6>
                            <?php if ($detalle): ?>
                            <p class="text-sm mb-0">
                                Información completa de la reserva #<?= $detalle['id_reserva'] ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <div class="btn-group" role="group">
                            <a href="ingresos/busqueda" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Volver a Búsqueda
                            </a>
                            <a href="ingresos" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-list me-1"></i>Listado
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($error)): ?>
    <!-- Error -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-4x text-danger mb-4"></i>
                    <h4 class="text-danger mb-3">Error</h4>
                    <p class="text-muted mb-4"><?= htmlspecialchars($error) ?></p>
                    <a href="ingresos/busqueda" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Buscar Ingresos
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php elseif (!$detalle): ?>
    <!-- Sin datos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted mb-3">Ingreso no encontrado</h4>
                    <p class="text-muted mb-4">
                        No se pudo cargar la información del ingreso solicitado
                    </p>
                    <a href="ingresos/busqueda" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Buscar Otro Ingreso
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Contenido del detalle -->
    <div class="row">
        <!-- Información Principal -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-bookmark me-2"></i>Información de la Reserva
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-section mb-4">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-home me-2"></i>Cabaña
                                </h6>
                                <div class="info-item">
                                    <strong>Nombre:</strong> <?= htmlspecialchars($detalle['cabania_nombre']) ?>
                                </div>
                                <div class="info-item">
                                    <strong>Ubicación:</strong> <?= htmlspecialchars($detalle['cabania_ubicacion']) ?>
                                </div>
                                <div class="info-item">
                                    <strong>Precio por día:</strong> $<?= number_format($detalle['cabania_precio'], 2) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-section mb-4">
                                <h6 class="text-success mb-3">
                                    <i class="fas fa-user me-2"></i>Huésped Principal
                                </h6>
                                <div class="info-item">
                                    <strong>Nombre:</strong> 
                                    <?= htmlspecialchars($detalle['persona_nombre']) ?> 
                                    <?= htmlspecialchars($detalle['persona_apellido']) ?>
                                </div>
                                <div class="info-item">
                                    <strong>Documento:</strong> <?= htmlspecialchars($detalle['persona_documento'] ?? 'N/A') ?>
                                </div>
                                <div class="info-item">
                                    <strong>Teléfono:</strong> <?= htmlspecialchars($detalle['persona_telefono'] ?? 'N/A') ?>
                                </div>
                                <div class="info-item">
                                    <strong>Email:</strong> <?= htmlspecialchars($detalle['persona_email'] ?? 'N/A') ?>
                                </div>
                                <div class="info-item">
                                    <strong>Usuario:</strong> <?= htmlspecialchars($detalle['usuario_nombre'] ?? 'N/A') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="info-section">
                                <h6 class="text-info mb-3">
                                    <i class="fas fa-calendar me-2"></i>Período de Estadía
                                </h6>
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded">
                                            <div class="text-success font-weight-bold mb-1">INICIO</div>
                                            <div class="text-sm">
                                                <?= date('d/m/Y', strtotime($detalle['reserva_fhinicio'])) ?>
                                            </div>
                                            <div class="text-xs text-muted">
                                                <?= date('H:i', strtotime($detalle['reserva_fhinicio'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded">
                                            <div class="text-warning font-weight-bold mb-1">FIN</div>
                                            <div class="text-sm">
                                                <?= date('d/m/Y', strtotime($detalle['reserva_fhfin'])) ?>
                                            </div>
                                            <div class="text-xs text-muted">
                                                <?= date('H:i', strtotime($detalle['reserva_fhfin'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded">
                                            <div class="text-primary font-weight-bold mb-1">DÍAS</div>
                                            <div class="text-lg font-weight-bold text-primary">
                                                <?= $detalle['dias_estadia'] ?: 1 ?>
                                            </div>
                                            <div class="text-xs text-muted">día<?= $detalle['dias_estadia'] != 1 ? 's' : '' ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded">
                                            <?php
                                            $estadoClass = 'secondary';
                                            switch ($detalle['estadoreserva_descripcion']) {
                                                case 'confirmada': $estadoClass = 'warning'; break;
                                                case 'en curso': $estadoClass = 'success'; break;
                                                case 'finalizada': $estadoClass = 'primary'; break;
                                                case 'cancelada': $estadoClass = 'danger'; break;
                                                case 'pendiente de pago': $estadoClass = 'info'; break;
                                            }
                                            ?>
                                            <div class="text-<?= $estadoClass ?> font-weight-bold mb-1">ESTADO</div>
                                            <div class="badge badge-<?= $estadoClass ?>">
                                                <?= ucfirst($detalle['estadoreserva_descripcion']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Consumos -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>Consumos Realizados
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($detalle['consumos'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-end">Precio Unit.</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalConsumos = 0;
                                foreach ($detalle['consumos'] as $consumo): 
                                    $totalConsumos += $consumo['consumo_total'];
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($consumo['producto_nombre'] ?? 'Producto N/A') ?></td>
                                    <td class="text-end">$<?= number_format($consumo['producto_precio'] ?? 0, 2) ?></td>
                                    <td class="text-end font-weight-bold">$<?= number_format($consumo['consumo_total'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="border-top">
                                    <td colspan="2" class="text-end font-weight-bold">Total Consumos:</td>
                                    <td class="text-end font-weight-bold text-success">
                                        $<?= number_format($totalConsumos, 2) ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-shopping-cart me-2"></i>Sin consumos registrados
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pagos -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>Pagos Realizados
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($detalle['pagos'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha/Hora</th>
                                    <th>Método de Pago</th>
                                    <th class="text-end">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalPagado = 0;
                                foreach ($detalle['pagos'] as $pago): 
                                    $totalPagado += $pago['pago_total'];
                                ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($pago['pago_fechahora'])) ?></td>
                                    <td><?= htmlspecialchars($pago['metododepago_descripcion'] ?? 'N/A') ?></td>
                                    <td class="text-end font-weight-bold">$<?= number_format($pago['pago_total'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="border-top">
                                    <td colspan="2" class="text-end font-weight-bold">Total Pagado:</td>
                                    <td class="text-end font-weight-bold text-primary">
                                        $<?= number_format($totalPagado, 2) ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-credit-card me-2"></i>Sin pagos registrados
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <!-- Resumen Financiero -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>Resumen Financiero
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span>Estadía:</span>
                        <span class="font-weight-bold">$<?= number_format($detalle['importe_estadia'], 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span>Consumos:</span>
                        <span class="font-weight-bold">$<?= number_format($totalConsumos ?? 0, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-primary font-weight-bold">Total Adeudado:</span>
                        <span class="text-primary font-weight-bold">
                            $<?= number_format(($detalle['importe_estadia'] + ($totalConsumos ?? 0)), 2) ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span>Pagado:</span>
                        <span class="font-weight-bold text-success">$<?= number_format($totalPagado ?? 0, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="font-weight-bold">Saldo:</span>
                        <?php 
                        $saldo = ($detalle['importe_estadia'] + ($totalConsumos ?? 0)) - ($totalPagado ?? 0);
                        $saldoClass = $saldo > 0 ? 'danger' : ($saldo < 0 ? 'info' : 'success');
                        ?>
                        <span class="font-weight-bold text-<?= $saldoClass ?>">
                            $<?= number_format($saldo, 2) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-tools me-2"></i>Acciones
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="ingresos/busqueda" class="btn btn-outline-secondary">
                            <i class="fas fa-search me-2"></i>Nueva Búsqueda
                        </a>
                        <a href="ingresos" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>Ver Listado
                        </a>
                        <button data-action="imprimir" class="btn btn-outline-info">
                            <i class="fas fa-print me-2"></i>Imprimir Detalle
                        </button>
                        <a href="ingresos/stats" class="btn btn-outline-success">
                            <i class="fas fa-chart-bar me-2"></i>Estadísticas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
.info-item {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f1f1f1;
}

.info-item:last-child {
    border-bottom: none;
}

.info-section {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
}

.text-lg {
    font-size: 1.25rem;
}

@media print {
    .btn, .card-header {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}