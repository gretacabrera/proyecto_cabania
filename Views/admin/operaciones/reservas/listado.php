<?php
$pageTitle = $title ?? 'Gestión de Reservas';
$currentModule = 'reservas';
$pageStyles = ['admin.css', 'dashboard.css'];
require_once __DIR__ . '/../../../shared/layouts/header.php';
?>

<div class="container mt-4">
    <!-- Título dinámico según perfil -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>
                <?php if ($userProfile === 'administrador'): ?>
                    <i class="fas fa-crown"></i> Gestión Completa de Reservas
                <?php elseif ($userProfile === 'cajero'): ?>
                    <i class="fas fa-cash-register"></i> Facturación y Pagos
                <?php elseif ($userProfile === 'recepcionista'): ?>
                    <i class="fas fa-concierge-bell"></i> Operaciones de Recepción
                <?php else: ?>
                    Gestión de Reservas
                <?php endif; ?>
            </h2>
            <?php if ($userProfile === 'cajero'): ?>
                <p class="text-muted mb-0">Gestión de facturación y cobros</p>
            <?php elseif ($userProfile === 'recepcionista'): ?>
                <p class="text-muted mb-0">Check-ins, check-outs y operaciones del día</p>
            <?php endif; ?>
        </div>
        <div>
            <a href="/reservas/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Reserva
            </a>
            <?php if ($userProfile === 'administrador'): ?>
                <a href="/reservas/stats" class="btn btn-info">
                    <i class="fas fa-chart-bar"></i> Estadísticas
                </a>
            <?php elseif ($userProfile === 'cajero'): ?>
                <a href="/facturas" class="btn btn-success">
                    <i class="fas fa-file-invoice"></i> Facturas
                </a>
                <a href="/pagos" class="btn btn-warning">
                    <i class="fas fa-credit-card"></i> Pagos
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- KPIs específicos por perfil -->
    <?php if (isset($totalReservas) || isset($reservasPendientesPago) || isset($checkinsHoy)): ?>
    <div class="row mb-4">
        <?php if ($userProfile === 'administrador'): ?>
            <!-- KPIs del Administrador -->
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0"><?= number_format($totalReservas ?? 0) ?></h4>
                                <small>Total Reservas</small>
                            </div>
                            <div><i class="fas fa-calendar-check fa-2x opacity-75"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0"><?= number_format($reservasActivas ?? 0) ?></h4>
                                <small>Reservas Activas</small>
                            </div>
                            <div><i class="fas fa-bed fa-2x opacity-75"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0">$<?= number_format($ingresosMes ?? 0, 0) ?></h4>
                                <small>Ingresos del Mes</small>
                            </div>
                            <div><i class="fas fa-dollar-sign fa-2x opacity-75"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0"><?= $ocupacionPromedio ?? 0 ?>%</h4>
                                <small>Ocupación Promedio</small>
                            </div>
                            <div><i class="fas fa-percentage fa-2x opacity-75"></i></div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($userProfile === 'cajero'): ?>
            <!-- KPIs del Cajero -->
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0"><?= count($reservasPendientesPago ?? []) ?></h4>
                                <small>Pendientes de Pago</small>
                            </div>
                            <div><i class="fas fa-exclamation-triangle fa-2x opacity-75"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0"><?= number_format($facturasHoy ?? 0) ?></h4>
                                <small>Facturas Hoy</small>
                            </div>
                            <div><i class="fas fa-file-invoice fa-2x opacity-75"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0">$<?= number_format($ingresosHoy ?? 0, 0) ?></h4>
                                <small>Ingresos Hoy</small>
                            </div>
                            <div><i class="fas fa-dollar-sign fa-2x opacity-75"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0"><?= count($metodosPagoMes ?? []) ?></h4>
                                <small>Métodos de Pago</small>
                            </div>
                            <div><i class="fas fa-credit-card fa-2x opacity-75"></i></div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($userProfile === 'recepcionista'): ?>
            <!-- KPIs del Recepcionista -->
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0"><?= count($checkinsHoy ?? []) ?></h4>
                                <small>Check-ins Hoy</small>
                            </div>
                            <div><i class="fas fa-sign-in-alt fa-2x opacity-75"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0"><?= count($checkoutsHoy ?? []) ?></h4>
                                <small>Check-outs Hoy</small>
                            </div>
                            <div><i class="fas fa-sign-out-alt fa-2x opacity-75"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0"><?= count($reservasHoy ?? []) ?></h4>
                                <small>Reservas Hoy</small>
                            </div>
                            <div><i class="fas fa-calendar-day fa-2x opacity-75"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0"><?= $ocupacionPromedio ?? 0 ?>%</h4>
                                <small>Ocupación Actual</small>
                            </div>
                            <div><i class="fas fa-bed fa-2x opacity-75"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Alertas específicas del cajero para reservas pendientes de pago -->
    <?php if ($userProfile === 'cajero' && !empty($reservasPendientesPago)): ?>
    <div class="alert alert-warning mb-4">
        <h5><i class="fas fa-exclamation-triangle"></i> Atención: Reservas Pendientes de Pago</h5>
        <p>Hay <?= count($reservasPendientesPago) ?> reservas que requieren procesamiento de pago inmediato.</p>
        <hr>
        <?php foreach (array_slice($reservasPendientesPago, 0, 3) as $reserva): ?>
        <div class="d-flex justify-content-between align-items-center py-1">
            <span><strong>#<?= $reserva['id_reserva'] ?></strong> - <?= htmlspecialchars($reserva['persona_nombre'] . ' ' . $reserva['persona_apellido']) ?></span>
            <div>
                <span class="badge badge-warning">$<?= number_format($reserva['cabania_precio'], 0) ?></span>
                <a href="/reservas/<?= $reserva['id_reserva'] ?>/pago" class="btn btn-sm btn-success">
                    <i class="fas fa-credit-card"></i> Procesar Pago
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (count($reservasPendientesPago) > 3): ?>
        <small class="text-muted">... y <?= count($reservasPendientesPago) - 3 ?> más</small>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Filtros de búsqueda específicos por perfil -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <?php if ($userProfile === 'cajero'): ?>
                    <i class="fas fa-dollar-sign"></i> Filtros de Facturación
                <?php elseif ($userProfile === 'recepcionista'): ?>
                    <i class="fas fa-filter"></i> Filtros Operativos
                <?php else: ?>
                    <i class="fas fa-search"></i> Filtros de Búsqueda
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" id="searchForm">
                <div class="row">
                    <?php if ($userProfile === 'cajero'): ?>
                        <!-- Filtros específicos del cajero -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="estado_pago">Estado de Pago:</label>
                                <select class="form-control" id="estado_pago" name="estado_pago">
                                    <option value="">Todos</option>
                                    <option value="pendiente" <?= ($filters['estado_pago'] ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendiente de Pago</option>
                                    <option value="pagado" <?= ($filters['estado_pago'] ?? '') === 'pagado' ? 'selected' : '' ?>>Pagado</option>
                                    <option value="parcial" <?= ($filters['estado_pago'] ?? '') === 'parcial' ? 'selected' : '' ?>>Pago Parcial</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="monto_min">Monto Mínimo:</label>
                                <input type="number" step="0.01" class="form-control" id="monto_min" name="monto_min" 
                                       value="<?= htmlspecialchars($filters['monto_min'] ?? '') ?>" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fecha_vencimiento">Vencimiento hasta:</label>
                                <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" 
                                       value="<?= htmlspecialchars($filters['fecha_vencimiento'] ?? '') ?>">
                            </div>
                        </div>
                    <?php elseif ($userProfile === 'recepcionista'): ?>
                        <!-- Filtros específicos del recepcionista -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fecha_operacion">Fecha de Operación:</label>
                                <select class="form-control" id="fecha_operacion" name="fecha_operacion">
                                    <option value="">Todas las fechas</option>
                                    <option value="hoy" <?= ($filters['fecha_operacion'] ?? '') === 'hoy' ? 'selected' : '' ?>>Hoy</option>
                                    <option value="manana" <?= ($filters['fecha_operacion'] ?? '') === 'manana' ? 'selected' : '' ?>>Mañana</option>
                                    <option value="semana" <?= ($filters['fecha_operacion'] ?? '') === 'semana' ? 'selected' : '' ?>>Esta Semana</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tipo_operacion">Tipo de Operación:</label>
                                <select class="form-control" id="tipo_operacion" name="tipo_operacion">
                                    <option value="">Todas</option>
                                    <option value="checkin" <?= ($filters['tipo_operacion'] ?? '') === 'checkin' ? 'selected' : '' ?>>Check-ins</option>
                                    <option value="checkout" <?= ($filters['tipo_operacion'] ?? '') === 'checkout' ? 'selected' : '' ?>>Check-outs</option>
                                    <option value="activas" <?= ($filters['tipo_operacion'] ?? '') === 'activas' ? 'selected' : '' ?>>Activas</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cabania">Cabaña:</label>
                                <select class="form-control" id="cabania" name="cabania">
                                    <option value="">Todas las cabañas</option>
                                    <?php if (isset($cabanias)): foreach ($cabanias as $cabania): ?>
                                        <option value="<?= $cabania['id_cabania'] ?>" 
                                                <?= ($filters['cabania'] ?? '') == $cabania['id_cabania'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cabania['cabania_codigo'] . ' - ' . $cabania['cabania_nombre']) ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Filtros estándar para administrador -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_inicio">Fecha inicio:</label>
                                <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                       value="<?= htmlspecialchars($filters['fecha_inicio'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_fin">Fecha fin:</label>
                                <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin" 
                                       value="<?= htmlspecialchars($filters['fecha_fin'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cabania">Cabaña:</label>
                                <select class="form-control" id="cabania" name="cabania">
                                    <option value="">Todas las cabañas</option>
                                    <?php if (isset($cabanias)): foreach ($cabanias as $cabania): ?>
                                        <option value="<?= $cabania['id_cabania'] ?>" 
                                                <?= ($filters['cabania'] ?? '') == $cabania['id_cabania'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cabania['cabania_codigo'] . ' - ' . $cabania['cabania_nombre']) ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="estado">Estado:</label>
                                <select class="form-control" id="estado" name="estado">
                                    <option value="">Todos los estados</option>
                                    <?php 
                                    $estados = ['pendiente' => 'Pendiente', 'confirmada' => 'Confirmada', 'cancelada' => 'Cancelada', 'finalizada' => 'Finalizada'];
                                    foreach ($estados as $value => $text): ?>
                                        <option value="<?= $value ?>" 
                                                <?= ($filters['estado'] ?? '') === $value ? 'selected' : '' ?>>
                                            <?= $text ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> 
                                <?= $userProfile === 'cajero' ? 'Buscar Facturación' : 'Buscar' ?>
                            </button>
                            <button type="button" class="btn btn-secondary" data-action="limpiar-filtros">
                                <i class="fas fa-times"></i> Limpiar
                            </button>
                            <?php if ($userProfile === 'cajero'): ?>
                                <button type="button" class="btn btn-warning" onclick="mostrarSoloPendientes()">
                                    <i class="fas fa-exclamation-triangle"></i> Solo Pendientes
                                </button>
                            <?php elseif ($userProfile === 'recepcionista'): ?>
                                <button type="button" class="btn btn-info" onclick="mostrarOperacionesHoy()">
                                    <i class="fas fa-calendar-day"></i> Operaciones Hoy
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Selector de registros por página -->
    <div class="mb-3">
        <form method="GET" style="display: inline;">
            <!-- Mantener filtros existentes -->
            <?php foreach ($filters as $key => $value): ?>
                <?php if (!empty($value)): ?>
                    <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($value) ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            
            <label for="per_page">Mostrar:</label>
            <select name="per_page" id="per_page" class="form-control d-inline-block" style="width: auto;" data-auto-submit>
                <option value="10" <?= ($pagination['per_page'] ?? 10) == 10 ? 'selected' : '' ?>>10 registros</option>
                <option value="25" <?= ($pagination['per_page'] ?? 10) == 25 ? 'selected' : '' ?>>25 registros</option>
                <option value="50" <?= ($pagination['per_page'] ?? 10) == 50 ? 'selected' : '' ?>>50 registros</option>
            </select>
        </form>
    </div>

    <!-- Mensajes -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Información de registros -->
    <?php if (!empty($reservas)): ?>
        <?php 
        // Calcular start y end para la paginación
        $start = (($pagination['current_page'] - 1) * $pagination['per_page']) + 1;
        $end = min($start + $pagination['per_page'] - 1, $pagination['total']);
        ?>
        <div class="pagination-info mb-3">
            <small class="text-muted">
                Mostrando registros <?= $start ?> al <?= $end ?> 
                de <?= $pagination['total'] ?> registros encontrados
            </small>
        </div>
    <?php endif; ?>

    <!-- Tabla de reservas -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($reservas)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No se encontraron reservas</h4>
                    <p class="text-muted mb-4">No hay reservas que coincidan con los filtros aplicados.</p>
                    <a href="/reservas/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Primera Reserva
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Cabaña</th>
                                <th>Huésped</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $reserva): ?>
                                <tr>
                                    <td><?= !empty($reserva['reserva_fhinicio']) ? date('d/m/Y H:i', strtotime($reserva['reserva_fhinicio'])) : 'No disponible' ?></td>
                                    <td><?= !empty($reserva['reserva_fhfin']) ? date('d/m/Y H:i', strtotime($reserva['reserva_fhfin'])) : 'No disponible' ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($reserva['cabania_nombre']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($reserva['cabania_codigo']) ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($reserva['persona_nombre'] . ' ' . $reserva['persona_apellido']) ?>
                                        <!-- DNI campo eliminado: persona_dni no existe en BD -->
                                    </td>
                                    <td>
                                        <?php
                                        $estadoClass = [
                                            'pendiente' => 'warning',
                                            'confirmada' => 'success',
                                            'en curso' => 'info',
                                            'pendiente de pago' => 'warning',
                                            'finalizada' => 'secondary',
                                            'anulada' => 'danger',
                                            'expirada' => 'dark',
                                            'cancelada' => 'danger'
                                        ];
                                        $estado = $reserva['estadoreserva_descripcion'] ?? 'desconocido';
                                        $class = $estadoClass[strtolower($estado)] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $class ?>">
                                            <?= ucfirst($estado) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- Ver detalle - disponible para todos -->
                                            <a href="/reservas/<?= $reserva['id_reserva'] ?>" 
                                               class="btn btn-sm btn-info" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <?php if ($userProfile === 'cajero'): ?>
                                                <!-- Acciones específicas del cajero -->
                                                <?php if ($reserva['rela_estadoreserva'] == 1): // PENDIENTE ?>
                                                    <a href="/facturas/create?reserva=<?= $reserva['id_reserva'] ?>" 
                                                       class="btn btn-sm btn-success" title="Generar Factura">
                                                        <i class="fas fa-file-invoice"></i>
                                                    </a>
                                                    <a href="/pagos/procesar/<?= $reserva['id_reserva'] ?>" 
                                                       class="btn btn-sm btn-warning" title="Procesar Pago">
                                                        <i class="fas fa-credit-card"></i>
                                                    </a>
                                                <?php elseif ($reserva['rela_estadoreserva'] == 2): // CONFIRMADA ?>
                                                    <a href="/facturas/view/<?= $reserva['id_reserva'] ?>" 
                                                       class="btn btn-sm btn-secondary" title="Ver Factura">
                                                        <i class="fas fa-receipt"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                            <?php elseif ($userProfile === 'recepcionista'): ?>
                                                <!-- Acciones específicas del recepcionista -->
                                                <?php if ($reserva['rela_estadoreserva'] == 2 && !empty($reserva['reserva_fhinicio']) && date('Y-m-d') >= date('Y-m-d', strtotime($reserva['reserva_fhinicio']))): ?>
                                                    <button class="btn btn-sm btn-success btn-checkin" 
                                                            data-reserva-id="<?= $reserva['id_reserva'] ?>" title="Check-in">
                                                        <i class="fas fa-sign-in-alt"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($reserva['rela_estadoreserva'] == 3 && !empty($reserva['reserva_fhfin']) && date('Y-m-d') >= date('Y-m-d', strtotime($reserva['reserva_fhfin']))): ?>
                                                    <button class="btn btn-sm btn-warning btn-checkout" 
                                                            data-reserva-id="<?= $reserva['id_reserva'] ?>" title="Check-out">
                                                        <i class="fas fa-sign-out-alt"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <a href="/huespedes/manage/<?= $reserva['id_reserva'] ?>" 
                                                   class="btn btn-sm btn-info" title="Gestionar Huéspedes">
                                                    <i class="fas fa-users"></i>
                                                </a>
                                                
                                                <?php if (in_array($reserva['rela_estadoreserva'], [1, 2])): ?>
                                                    <a href="/reservas/<?= $reserva['id_reserva'] ?>/edit" 
                                                       class="btn btn-sm btn-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                            <?php elseif ($userProfile === 'administrador'): ?>
                                                <!-- Acciones completas del administrador -->
                                                <?php if (in_array($reserva['rela_estadoreserva'], [1, 2, 3, 4])): ?>
                                                    <a href="/reservas/<?= $reserva['id_reserva'] ?>/edit" 
                                                       class="btn btn-sm btn-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <a href="/reservas/<?= $reserva['id_reserva'] ?>/history" 
                                                   class="btn btn-sm btn-secondary" title="Historial">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                                
                                                <?php if (in_array($reserva['rela_estadoreserva'], [1, 2, 3, 4])): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger btn-anular-reserva" 
                                                            title="Anular reserva"
                                                            data-reserva-id="<?= $reserva['id_reserva'] ?>"
                                                            data-reserva-info="<?= htmlspecialchars($reserva['cabania_nombre'] . ' - ' . $reserva['persona_nombre']) ?>">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <small class="text-muted">
                                Página <?= $pagination['current_page'] ?> de <?= $pagination['total_pages'] ?>
                            </small>
                        </div>
                        <nav aria-label="Paginación de reservas">
                            <ul class="pagination pagination-sm mb-0">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="/proyecto_cabania/reservas?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] - 1])) ?>">
                                            Anterior
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                    <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="/proyecto_cabania/reservas?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="/proyecto_cabania/reservas?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] + 1])) ?>">
                                            Siguiente
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="<?= asset('assets/js/main.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initReservas();
    
    // Limpiar filtros
    document.querySelector('[data-action="limpiar-filtros"]')?.addEventListener('click', function() {
        limpiarFormularioReservas(document.getElementById('searchForm'));
    });
    
    // Auto-submit para selector de página
    document.querySelector('[data-auto-submit]')?.addEventListener('change', function() {
        autoSubmitPaginacion(this);
    });
    
    // Manejar anulación de reservas
    document.querySelectorAll('.btn-anular-reserva').forEach(btn => {
        btn.addEventListener('click', function() {
            const reservaId = this.dataset.reservaId;
            const reservaInfo = this.dataset.reservaInfo;
            
            if (confirm(`¿Está seguro de anular la reserva de ${reservaInfo}?\n\nEsta acción no se puede deshacer.`)) {
                fetch(`/reservas/${reservaId}/anular`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        location.reload();
                    } else {
                        alert('Error al anular la reserva. Intente nuevamente.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión. Intente nuevamente.');
                });
            }
        });
    });
    
    // Funciones específicas por perfil
    <?php if ($userProfile === 'cajero'): ?>
    // Funciones del cajero
    window.mostrarSoloPendientes = function() {
        document.getElementById('estado_pago').value = 'pendiente';
        document.getElementById('searchForm').submit();
    };
    
    // Procesar pago rápido
    document.querySelectorAll('[href*="/pagos/procesar/"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('¿Proceder con el procesamiento de pago para esta reserva?')) {
                e.preventDefault();
            }
        });
    });
    
    <?php elseif ($userProfile === 'recepcionista'): ?>
    // Funciones del recepcionista
    window.mostrarOperacionesHoy = function() {
        document.getElementById('fecha_operacion').value = 'hoy';
        document.getElementById('searchForm').submit();
    };
    
    // Check-in
    document.querySelectorAll('.btn-checkin').forEach(btn => {
        btn.addEventListener('click', function() {
            const reservaId = this.dataset.reservaId;
            if (confirm('¿Confirmar check-in para esta reserva?')) {
                fetch(`/reservas/${reservaId}/checkin`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        });
    });
    
    // Check-out
    document.querySelectorAll('.btn-checkout').forEach(btn => {
        btn.addEventListener('click', function() {
            const reservaId = this.dataset.reservaId;
            if (confirm('¿Confirmar check-out para esta reserva?')) {
                fetch(`/reservas/${reservaId}/checkout`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        });
    });
    
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/../../../shared/layouts/footer.php'; ?>