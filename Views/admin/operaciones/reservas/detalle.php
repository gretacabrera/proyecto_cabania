<?php
$title = 'Detalle de Reserva';
$currentModule = 'reservas';

require_once __DIR__ . '/../../../shared/layouts/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Detalle de Reserva #<?= $reserva['id_reserva'] ?></h4>
                        <div>
                            <a href="/reservas/<?= $reserva['id_reserva'] ?>/edit" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="/reservas" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Información principal -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5><i class="fas fa-user"></i> Información del Huésped</h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Nombre:</strong></td>
                                    <td><?= htmlspecialchars($reserva['persona_nombre'] . ' ' . $reserva['persona_apellido']) ?></td>
                                </tr>
                                <!-- Campo DNI eliminado: persona_dni no existe en BD -->
                                <!-- <tr>
                                    <td><strong>DNI:</strong></td>
                                    <td><?= $reserva['persona_dni'] ?></td>
                                </tr> -->
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td><?= htmlspecialchars($reserva['persona_email'] ?? 'No disponible') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Teléfono:</strong></td>
                                    <td><?= htmlspecialchars($reserva['persona_telefono'] ?? 'No disponible') ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-home"></i> Información de la Cabaña</h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Código:</strong></td>
                                    <td><?= htmlspecialchars($reserva['cabania_codigo']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Nombre:</strong></td>
                                    <td><?= htmlspecialchars($reserva['cabania_nombre']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Ubicación:</strong></td>
                                    <td><?= htmlspecialchars($reserva['cabania_ubicacion'] ?? 'No especificada') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Capacidad:</strong></td>
                                    <td><?= $reserva['cabania_capacidad'] ?> personas</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <!-- Información de fechas y estado -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5><i class="fas fa-calendar"></i> Fechas de la Reserva</h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Fecha inicio:</strong></td>
                                    <td><?= date('d/m/Y H:i', strtotime($reserva['reserva_fechainicio'])) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Fecha fin:</strong></td>
                                    <td><?= date('d/m/Y H:i', strtotime($reserva['reserva_fechafin'])) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Duración:</strong></td>
                                    <td>
                                        <?php
                                        $inicio = new DateTime($reserva['reserva_fechainicio']);
                                        $fin = new DateTime($reserva['reserva_fechafin']);
                                        $diff = $inicio->diff($fin);
                                        echo $diff->days . ' días';
                                        if ($diff->h > 0) {
                                            echo ' y ' . $diff->h . ' horas';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Personas:</strong></td>
                                    <td><?= $reserva['reserva_cantidadpersonas'] ?? 'No especificado' ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-info-circle"></i> Estado y Pagos</h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Estado:</strong></td>
                                    <td>
                                        <?php
                                        $estadoClass = [
                                            'pendiente' => 'warning',
                                            'confirmada' => 'success',
                                            'en curso' => 'info',
                                            'finalizada' => 'secondary',
                                            'cancelada' => 'danger',
                                            'anulada' => 'dark'
                                        ];
                                        $estado = strtolower($reserva['estadoreserva_descripcion'] ?? 'desconocido');
                                        $class = $estadoClass[$estado] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $class ?> badge-lg">
                                            <?= ucfirst($reserva['estadoreserva_descripcion']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Método de pago:</strong></td>
                                    <td>
                                        <?php
                                        $metodosPago = [
                                            '1' => 'Efectivo',
                                            '2' => 'Tarjeta de débito',
                                            '3' => 'Tarjeta de crédito',
                                            '4' => 'Transferencia'
                                        ];
                                        echo $metodosPago[$reserva['rela_metodopago']] ?? 'No especificado';
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Fecha creación:</strong></td>
                                    <td>
                                        <?= isset($reserva['reserva_fechacreacion']) ? 
                                            date('d/m/Y H:i', strtotime($reserva['reserva_fechacreacion'])) : 
                                            'No disponible' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Período:</strong></td>
                                    <td><?= htmlspecialchars($reserva['periodo_descripcion'] ?? 'No especificado') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <?php if (!empty($reserva['reserva_observaciones'])): ?>
                        <hr>
                        <h5><i class="fas fa-sticky-note"></i> Observaciones</h5>
                        <div class="alert alert-info">
                            <?= nl2br(htmlspecialchars($reserva['reserva_observaciones'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="col-md-4">
            <!-- Acciones -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cogs"></i> Acciones</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/reservas/<?= $reserva['id_reserva'] ?>/edit" class="btn btn-primary btn-block">
                            <i class="fas fa-edit"></i> Editar Reserva
                        </a>
                        
                        <?php if ($reserva['rela_estadoreserva'] == 6): // Estado anulada ?>
                            <?php if (isset($user_permissions['admin']) && $user_permissions['admin']): ?>
                                <a href="/reservas/<?= $reserva['id_reserva'] ?>/restore" 
                                   class="btn btn-success btn-block"
                                   data-confirm-action="reactivar-reserva">
                                    <i class="fas fa-check"></i> Reactivar Reserva
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="/reservas/<?= $reserva['id_reserva'] ?>/cancel" 
                               class="btn btn-danger btn-block"
                               data-confirm-action="anular-reserva">
                                <i class="fas fa-ban"></i> Anular Reserva
                            </a>
                        <?php endif; ?>
                        
                        <a href="/reservas/<?= $reserva['id_reserva'] ?>/change-status" class="btn btn-warning btn-block">
                            <i class="fas fa-exchange-alt"></i> Cambiar Estado
                        </a>
                        
                        <hr>
                        
                        <a href="/consumos?reserva=<?= $reserva['id_reserva'] ?>" class="btn btn-info btn-block">
                            <i class="fas fa-shopping-cart"></i> Ver Consumos
                        </a>
                        
                        <a href="/reservas/<?= $reserva['id_reserva'] ?>/print" class="btn btn-secondary btn-block" target="_blank">
                            <i class="fas fa-print"></i> Imprimir Reserva
                        </a>
                    </div>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Resumen</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h3 class="text-primary">ID #<?= $reserva['id_reserva'] ?></h3>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <h4 class="text-muted"><?= $diff->days ?></h4>
                                <small class="text-muted">Días</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-muted"><?= $reserva['reserva_cantidadpersonas'] ?? 'N/A' ?></h4>
                            <small class="text-muted">Personas</small>
                        </div>
                    </div>
                    
                    <?php if (!empty($consumos)): ?>
                        <hr>
                        <div class="text-center">
                            <h5 class="text-info"><?= count($consumos) ?></h5>
                            <small class="text-muted">Consumos registrados</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Consumos de la reserva -->
    <?php if (!empty($consumos)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Consumos de la Reserva</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unit.</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $totalConsumos = 0; ?>
                                    <?php foreach ($consumos as $consumo): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($consumo['producto_nombre']) ?></td>
                                            <td><?= $consumo['consumo_cantidad'] ?></td>
                                            <td>$<?= number_format($consumo['consumo_precio'], 2, ',', '.') ?></td>
                                            <td><strong>$<?= number_format($consumo['consumo_total'], 2, ',', '.') ?></strong></td>
                                            <td>
                                                <span class="badge badge-<?= $consumo['consumo_pagado'] ? 'success' : 'warning' ?>">
                                                    <?= $consumo['consumo_pagado'] ? 'Pagado' : 'Pendiente' ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php $totalConsumos += $consumo['consumo_total']; ?>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <th colspan="4" class="text-right">Total Consumos:</th>
                                        <th>$<?= number_format($totalConsumos, 2, ',', '.') ?></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="<?= asset('assets/js/main.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initReservas();
});
</script>

<?php require_once __DIR__ . '/../../../shared/layouts/footer.php'; ?>