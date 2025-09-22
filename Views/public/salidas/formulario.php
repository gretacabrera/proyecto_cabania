<?php
$pageTitle = $title ?? 'Registrar Salida';
include __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid px-4">
    <!-- Encabezado -->
    <div class="row">
        <div class="col-12">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-sign-out-alt text-primary"></i>
                    Registrar Salida del Complejo
                </h1>
                <a href="/salidas" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Volver al Listado
                </a>
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

    <!-- Contenido principal -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clipboard-check"></i>
                        Procesar Check-out de Reserva
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (isset($reservas) && !empty($reservas)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Instrucciones:</strong> 
                            Selecciona la reserva que está haciendo check-out. El sistema calculará automáticamente el estado de los pagos y liberará la cabaña.
                        </div>

                        <form method="post" action="/salidas/registrar" id="formSalida">
                            <div class="row">
                                <?php foreach ($reservas as $reserva): ?>
                                    <div class="col-12 mb-4">
                                        <div class="card border-primary">
                                            <div class="card-header bg-light">
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" 
                                                           id="reserva_<?= htmlspecialchars($reserva['id_reserva']) ?>" 
                                                           name="id_reserva" 
                                                           value="<?= htmlspecialchars($reserva['id_reserva']) ?>" 
                                                           class="custom-control-input reserva-radio"
                                                           required>
                                                    <label class="custom-control-label font-weight-bold" 
                                                           for="reserva_<?= htmlspecialchars($reserva['id_reserva']) ?>">
                                                        Reserva #<?= htmlspecialchars($reserva['id_reserva']) ?> - 
                                                        <?= htmlspecialchars($reserva['persona_nombre'] . ' ' . $reserva['persona_apellido']) ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6 class="text-primary">
                                                            <i class="fas fa-info-circle"></i>
                                                            Información de la Reserva
                                                        </h6>
                                                        <table class="table table-borderless table-sm">
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
                                                    <div class="col-md-6">
                                                        <h6 class="text-success">
                                                            <i class="fas fa-money-bill-wave"></i>
                                                            Estado Financiero
                                                        </h6>
                                                        <table class="table table-borderless table-sm">
                                                            <tr>
                                                                <td><strong>Estadía:</strong></td>
                                                                <td class="text-right">
                                                                    $<?= number_format(floatval($reserva['importe_estadia']), 2) ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Consumos:</strong></td>
                                                                <td class="text-right">
                                                                    $<?= number_format(floatval($reserva['importe_consumos']), 2) ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Total Adeudado:</strong></td>
                                                                <td class="text-right font-weight-bold">
                                                                    $<?= number_format(floatval($reserva['total_adeudado']), 2) ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Total Pagado:</strong></td>
                                                                <td class="text-right text-success">
                                                                    $<?= number_format(floatval($reserva['total_pagado']), 2) ?>
                                                                </td>
                                                            </tr>
                                                            <tr class="border-top">
                                                                <td><strong>Saldo:</strong></td>
                                                                <td class="text-right">
                                                                    <?php $saldo = floatval($reserva['saldo_pendiente']); ?>
                                                                    <strong class="<?= $saldo > 0 ? 'text-danger' : 'text-success' ?>">
                                                                        $<?= number_format(abs($saldo), 2) ?>
                                                                        <?= $saldo > 0 ? '(Debe)' : ($saldo < 0 ? '(A favor)' : '(Saldado)') ?>
                                                                    </strong>
                                                                </td>
                                                            </tr>
                                                        </table>

                                                        <!-- Estado de pagos -->
                                                        <?php if ($reserva['estado_pagos'] === 'OK'): ?>
                                                            <div class="alert alert-success py-2 mb-2">
                                                                <i class="fas fa-check-circle"></i>
                                                                <small><strong>Pagos Completos</strong> - La reserva será marcada como finalizada</small>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="alert alert-warning py-2 mb-2">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                                <small><strong>Pagos Pendientes</strong> - La reserva quedará pendiente de pago</small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <!-- Botones de acción -->
                                                <div class="mt-3 pt-3 border-top">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock"></i>
                                                            Selecciona esta reserva para procesar el check-out
                                                        </small>
                                                        <?php if ($reserva['saldo_pendiente'] > 0): ?>
                                                            <a href="/pagos/create?reserva=<?= htmlspecialchars($reserva['id_reserva']) ?>" 
                                                               class="btn btn-outline-success btn-sm" target="_blank">
                                                                <i class="fas fa-money-bill"></i>
                                                                Registrar Pago
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Botones del formulario -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <a href="/salidas" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i>
                                            Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary btn-lg" id="btnConfirmar">
                                            <i class="fas fa-check"></i>
                                            Confirmar Check-out
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    <?php else: ?>
                        <!-- No hay reservas para salida -->
                        <div class="text-center py-5">
                            <i class="fas fa-bed fa-3x text-gray-300 mb-4"></i>
                            <h4 class="text-gray-600">No hay reservas listas para check-out</h4>
                            <p class="text-muted mb-4">
                                No se han encontrado reservas en curso que puedan hacer salida en este momento.
                            </p>
                            
                            <div class="alert alert-info">
                                <h6><strong>Requisitos para registrar una salida:</strong></h6>
                                <ul class="list-unstyled mb-0">
                                    <li><i class="fas fa-check text-success"></i> La reserva debe estar en estado "en curso"</li>
                                    <li><i class="fas fa-check text-success"></i> La fecha actual debe estar dentro del período de la reserva</li>
                                    <li><i class="fas fa-check text-success"></i> El usuario debe tener permisos sobre la reserva</li>
                                </ul>
                            </div>

                            <div class="mt-4">
                                <a href="/ingresos" class="btn btn-primary mr-2">
                                    <i class="fas fa-sign-in-alt"></i>
                                    Ver Ingresos
                                </a>
                                <a href="/reservas" class="btn btn-outline-primary">
                                    <i class="fas fa-calendar-alt"></i>
                                    Gestionar Reservas
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Panel de ayuda lateral -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-question-circle"></i>
                        Ayuda - Proceso de Check-out
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold text-primary">Pasos del Check-out:</h6>
                    <ol class="mb-3">
                        <li>Revisa la información financiera de la reserva</li>
                        <li>Selecciona la reserva que desea procesar</li>
                        <li>Confirma el check-out</li>
                        <li>El sistema actualizará automáticamente el estado</li>
                        <li>Se liberará la cabaña para nuevas reservas</li>
                    </ol>

                    <h6 class="font-weight-bold text-primary">Estados resultantes:</h6>
                    <ul class="list-unstyled mb-3">
                        <li>
                            <span class="badge badge-success">Finalizada</span>
                            <small class="d-block text-muted">Todos los pagos están completos</small>
                        </li>
                        <li class="mt-2">
                            <span class="badge badge-warning">Pendiente de pago</span>
                            <small class="d-block text-muted">Faltan pagos por registrar</small>
                        </li>
                    </ul>

                    <div class="alert alert-warning">
                        <small>
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Importante:</strong> Una vez procesado el check-out, la cabaña quedará disponible para nuevas reservas.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-bolt"></i>
                        Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="/pagos" class="list-group-item list-group-item-action">
                            <i class="fas fa-money-bill text-success"></i>
                            Registrar Pagos
                        </a>
                        <a href="/consumos" class="list-group-item list-group-item-action">
                            <i class="fas fa-shopping-cart text-info"></i>
                            Gestionar Consumos
                        </a>
                        <a href="/comentarios" class="list-group-item list-group-item-action">
                            <i class="fas fa-comments text-primary"></i>
                            Comentarios de Huéspedes
                        </a>
                        <a href="/salidas/stats" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-bar text-warning"></i>
                            Ver Estadísticas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?php include __DIR__ . '/../layouts/footer.php'; ?>