<?php
$pageTitle = $title ?? 'Detalle de Salida';
include __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid px-4">
    <!-- Encabezado -->
    <div class="row">
        <div class="col-12">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-file-alt text-primary"></i>
                    Detalle de Salida - Reserva #<?= htmlspecialchars($reserva['id_reserva'] ?? 'N/A') ?>
                </h1>
                <div class="btn-group" role="group">
                    <a href="/salidas" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Volver al Listado
                    </a>
                    <button data-action="print" class="btn btn-outline-primary">>
                        <i class="fas fa-print"></i>
                        Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes de estado -->
    <?php if (isset($success_message) && $success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message) && $error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($reserva) && $reserva): ?>
        <div class="row">
            <!-- Información principal -->
            <div class="col-lg-8">
                <!-- Datos de la reserva -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle"></i>
                            Información de la Reserva
                        </h6>
                        <span class="badge <?= $reserva['estadoreserva_descripcion'] === 'finalizada' ? 'badge-success' : 'badge-warning' ?> badge-lg">
                            <?= ucfirst(htmlspecialchars($reserva['estadoreserva_descripcion'])) ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-user"></i>
                                    Datos del Huésped
                                </h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Nombre:</strong></td>
                                        <td><?= htmlspecialchars($reserva['persona_nombre'] . ' ' . $reserva['persona_apellido']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Teléfono:</strong></td>
                                        <td>
                                            <?php if (!empty($reserva['persona_telefono'])): ?>
                                                <a href="tel:<?= htmlspecialchars($reserva['persona_telefono']) ?>">
                                                    <?= htmlspecialchars($reserva['persona_telefono']) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">No disponible</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td>
                                            <?php if (!empty($reserva['persona_email'])): ?>
                                                <a href="mailto:<?= htmlspecialchars($reserva['persona_email']) ?>">
                                                    <?= htmlspecialchars($reserva['persona_email']) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">No disponible</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-home"></i>
                                    Detalles del Alojamiento
                                </h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Cabaña:</strong></td>
                                        <td>
                                            <span class="badge badge-primary">
                                                <?= htmlspecialchars($reserva['cabania_nombre']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Check-in:</strong></td>
                                        <td><?= htmlspecialchars($reserva['fecha_inicio_formateada']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Check-out:</strong></td>
                                        <td>
                                            <strong class="text-danger">
                                                <?= htmlspecialchars($reserva['fecha_fin_formateada']) ?>
                                            </strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Duración:</strong></td>
                                        <td>
                                            <span class="badge badge-light">
                                                <?= intval($reserva['dias_estadia'] ?: 1) ?> días
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <?php if (!empty($reserva['reserva_observaciones'])): ?>
                            <div class="mt-3 pt-3 border-top">
                                <h6 class="text-primary">
                                    <i class="fas fa-sticky-note"></i>
                                    Observaciones
                                </h6>
                                <div class="alert alert-light">
                                    <?= nl2br(htmlspecialchars($reserva['reserva_observaciones'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Consumos -->
                <?php if (isset($reserva['consumos']) && !empty($reserva['consumos'])): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-shopping-cart"></i>
                                Consumos Durante la Estadía
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Producto</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-right">Precio Unit.</th>
                                            <th class="text-right">Subtotal</th>
                                            <th class="text-center">Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $totalConsumos = 0;
                                        foreach ($reserva['consumos'] as $consumo): 
                                            $totalConsumos += floatval($consumo['consumo_total']);
                                        ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($consumo['producto_nombre']) ?></strong>
                                                </td>
                                                <td class="text-center">
                                                    <?= intval($consumo['consumo_cantidad']) ?>
                                                </td>
                                                <td class="text-right">
                                                    $<?= number_format(floatval($consumo['producto_precio']), 2) ?>
                                                </td>
                                                <td class="text-right">
                                                    <strong>$<?= number_format(floatval($consumo['consumo_total']), 2) ?></strong>
                                                </td>
                                                <td class="text-center">
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($consumo['fecha_consumo'] ?? 'N/A') ?>
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-info">
                                            <th colspan="3" class="text-right">Total Consumos:</th>
                                            <th class="text-right">
                                                $<?= number_format($totalConsumos, 2) ?>
                                            </th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Historial de pagos -->
                <?php if (isset($reserva['pagos']) && !empty($reserva['pagos'])): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-money-bill-wave"></i>
                                Historial de Pagos
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Método de Pago</th>
                                            <th class="text-right">Monto</th>
                                            <th class="text-center">Fecha</th>
                                            <th>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $totalPagos = 0;
                                        foreach ($reserva['pagos'] as $pago): 
                                            $totalPagos += floatval($pago['pago_total']);
                                        ?>
                                            <tr>
                                                <td>
                                                    <span class="badge badge-success">
                                                        <?= htmlspecialchars($pago['metodo_nombre'] ?? 'No especificado') ?>
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    <strong class="text-success">
                                                        $<?= number_format(floatval($pago['pago_total']), 2) ?>
                                                    </strong>
                                                </td>
                                                <td class="text-center">
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($pago['fecha_pago'] ?? 'N/A') ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php if (!empty($pago['pago_observaciones'])): ?>
                                                        <small><?= htmlspecialchars($pago['pago_observaciones']) ?></small>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-success">
                                            <th class="text-right">Total Pagado:</th>
                                            <th class="text-right">
                                                $<?= number_format($totalPagos, 2) ?>
                                            </th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card shadow mb-4">
                        <div class="card-body text-center py-4">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300 mb-3"></i>
                            <h6 class="text-gray-600">No hay pagos registrados</h6>
                            <p class="text-muted">Esta reserva no tiene pagos asociados.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Panel lateral con resumen financiero -->
            <div class="col-lg-4">
                <!-- Resumen financiero -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-calculator"></i>
                            Resumen Financiero
                        </h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Costo de Estadía:</strong></td>
                                <td class="text-right">
                                    $<?= number_format(floatval($reserva['importe_estadia'] ?? 0), 2) ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Consumos:</strong></td>
                                <td class="text-right">
                                    $<?= number_format(floatval($reserva['importe_consumos'] ?? 0), 2) ?>
                                </td>
                            </tr>
                            <tr class="border-top">
                                <td><strong>Total Adeudado:</strong></td>
                                <td class="text-right">
                                    <strong>$<?= number_format(floatval($reserva['total_adeudado'] ?? 0), 2) ?></strong>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Total Pagado:</strong></td>
                                <td class="text-right text-success">
                                    <strong>$<?= number_format(floatval($reserva['total_pagado'] ?? 0), 2) ?></strong>
                                </td>
                            </tr>
                            <tr class="border-top">
                                <td><strong>Saldo:</strong></td>
                                <td class="text-right">
                                    <?php 
                                    $saldo = floatval($reserva['saldo_pendiente'] ?? 0);
                                    $saldoClass = $saldo > 0 ? 'text-danger' : ($saldo < 0 ? 'text-info' : 'text-success');
                                    ?>
                                    <strong class="<?= $saldoClass ?>">
                                        $<?= number_format(abs($saldo), 2) ?>
                                        <?= $saldo > 0 ? '(Debe)' : ($saldo < 0 ? '(A favor)' : '(Saldado)') ?>
                                    </strong>
                                </td>
                            </tr>
                        </table>

                        <!-- Estado de la salida -->
                        <?php if (isset($reserva['estado_pagos'])): ?>
                            <div class="mt-3">
                                <?php if ($reserva['estado_pagos'] === 'OK'): ?>
                                    <div class="alert alert-success py-2">
                                        <i class="fas fa-check-circle"></i>
                                        <strong>Pagos Completos</strong><br>
                                        <small>La reserva fue finalizada correctamente.</small>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning py-2">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Pagos Pendientes</strong><br>
                                        <small>Faltan $<?= number_format(abs($saldo), 2) ?> por pagar.</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Acciones disponibles -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-tools"></i>
                            Acciones
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if (isset($reserva['saldo_pendiente']) && $reserva['saldo_pendiente'] > 0): ?>
                                <a href="/pagos/create?reserva=<?= htmlspecialchars($reserva['id_reserva']) ?>" 
                                   class="btn btn-success btn-block">
                                    <i class="fas fa-money-bill"></i>
                                    Registrar Pago
                                </a>
                            <?php endif; ?>

                            <a href="/comentarios/formulario?id_reserva=<?= htmlspecialchars($reserva['id_reserva']) ?>" 
                               class="btn btn-primary btn-block">
                                <i class="fas fa-comment"></i>
                                Agregar Comentario
                            </a>

                            <a href="/consumos/reserva/<?= htmlspecialchars($reserva['id_reserva']) ?>" 
                               class="btn btn-info btn-block">
                                <i class="fas fa-shopping-cart"></i>
                                Ver Todos los Consumos
                            </a>

                            <button data-action="print" class="btn btn-outline-secondary btn-block">>
                                <i class="fas fa-print"></i>
                                Imprimir Detalle
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-info-circle"></i>
                            Información
                        </h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <i class="fas fa-calendar-alt"></i>
                            <strong>Fecha de Check-out:</strong><br>
                            <?= htmlspecialchars($reserva['fecha_fin_formateada'] ?? 'No disponible') ?>
                        </small>
                        
                        <?php if (isset($reserva['cabania_descripcion']) && !empty($reserva['cabania_descripcion'])): ?>
                            <hr>
                            <small class="text-muted">
                                <i class="fas fa-home"></i>
                                <strong>Descripción de la Cabaña:</strong><br>
                                <?= nl2br(htmlspecialchars($reserva['cabania_descripcion'])) ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Error - reserva no encontrada -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-4"></i>
                        <h4 class="text-gray-600">Reserva no encontrada</h4>
                        <p class="text-muted">
                            La reserva solicitada no existe o no tiene permisos para acceder a ella.
                        </p>
                        <div class="mt-4">
                            <a href="/salidas" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i>
                                Volver al Listado
                            </a>
                            <a href="/salidas/busqueda" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-search"></i>
                                Buscar Salidas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

    </div>
</div>

<script src="<?= asset('assets/js/main.js') ?>"></script>
<script>
// Funciones centralizadas se ejecutan automáticamente por public.js
document.addEventListener('DOMContentLoaded', function() {
    initSalidas();
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>