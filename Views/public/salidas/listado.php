<?php
$pageTitle = $title ?? 'Gestión de Salidas';
include __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid px-4">
    <!-- Encabezado -->
    <div class="row">
        <div class="col-12">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-sign-out-alt text-primary"></i>
                    Gestión de Salidas
                </h1>
                <div class="btn-group" role="group">
                    <a href="/salidas/formulario" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Registrar Salida
                    </a>
                    <a href="/salidas/busqueda" class="btn btn-outline-secondary">
                        <i class="fas fa-search"></i> Búsqueda Avanzada
                    </a>
                    <a href="/salidas/stats" class="btn btn-outline-info">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </a>
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

    <!-- Tarjetas de resumen -->
    <?php if (isset($estadisticas) && $estadisticas): ?>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Salidas Hoy
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= intval($estadisticas['salidas_hoy']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Salidas Esta Semana
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= intval($estadisticas['salidas_semana']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Salidas Este Mes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= intval($estadisticas['salidas_mes']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pendientes de Pago
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= intval($estadisticas['pendientes_pago']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla de salidas recientes -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i>
                        Salidas Recientes (Últimos 30 días)
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <a class="dropdown-item" href="/salidas/busqueda">
                                <i class="fas fa-search fa-sm fa-fw mr-2 text-gray-400"></i>
                                Búsqueda Avanzada
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="/salidas/stats">
                                <i class="fas fa-chart-bar fa-sm fa-fw mr-2 text-gray-400"></i>
                                Ver Estadísticas
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($salidas) && !empty($salidas)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead class="thead-light">
                                    <tr>
                                        <th><i class="fas fa-hashtag"></i> ID</th>
                                        <th><i class="fas fa-user"></i> Huésped</th>
                                        <th><i class="fas fa-home"></i> Cabaña</th>
                                        <th><i class="fas fa-calendar"></i> Check-out</th>
                                        <th><i class="fas fa-clock"></i> Días</th>
                                        <th><i class="fas fa-info-circle"></i> Estado</th>
                                        <th><i class="fas fa-tools"></i> Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($salidas as $salida): ?>
                                        <tr>
                                            <td class="font-weight-bold text-primary">
                                                #<?= htmlspecialchars($salida['id_reserva']) ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="font-weight-bold">
                                                            <?= htmlspecialchars($salida['persona_nombre'] . ' ' . $salida['persona_apellido']) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">
                                                    <?= htmlspecialchars($salida['cabania_nombre']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($salida['fecha_fin_formateada']) ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-light">
                                                    <?= intval($salida['dias_estadia']) ?> días
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                $estadoClass = match($salida['estadoreserva_descripcion']) {
                                                    'finalizada' => 'badge-success',
                                                    'pendiente de pago' => 'badge-warning',
                                                    default => 'badge-secondary'
                                                };
                                                ?>
                                                <span class="badge <?= $estadoClass ?>">
                                                    <?= ucfirst(htmlspecialchars($salida['estadoreserva_descripcion'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="/salidas/<?= htmlspecialchars($salida['id_reserva']) ?>/detalle" 
                                                       class="btn btn-outline-info btn-sm" title="Ver Detalle">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($salida['estadoreserva_descripcion'] === 'pendiente de pago'): ?>
                                                        <a href="/pagos/create?reserva=<?= htmlspecialchars($salida['id_reserva']) ?>" 
                                                           class="btn btn-outline-success btn-sm" title="Registrar Pago">
                                                            <i class="fas fa-money-bill"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="/comentarios/formulario?id_reserva=<?= htmlspecialchars($salida['id_reserva']) ?>" 
                                                       class="btn btn-outline-primary btn-sm" title="Agregar Comentario">
                                                        <i class="fas fa-comment"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación (si es necesaria) -->
                        <?php if (count($salidas) >= 20): ?>
                            <nav aria-label="Paginación de salidas">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item">
                                        <a class="page-link" href="?page=1">Anterior</a>
                                    </li>
                                    <li class="page-item active">
                                        <span class="page-link">1</span>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=2">Siguiente</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- Estado vacío -->
                        <div class="text-center py-5">
                            <i class="fas fa-sign-out-alt fa-3x text-gray-300 mb-3"></i>
                            <h5 class="text-gray-600">No hay salidas recientes</h5>
                            <p class="text-muted">
                                No se han registrado salidas en los últimos 30 días.
                            </p>
                            <div class="mt-3">
                                <a href="/salidas/formulario" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Registrar Primera Salida
                                </a>
                                <a href="/salidas/busqueda" class="btn btn-outline-secondary ml-2">
                                    <i class="fas fa-search"></i>
                                    Buscar Salidas Anteriores
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de ayuda -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-question-circle"></i>
                        Ayuda - Gestión de Salidas
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold">¿Cómo registrar una salida?</h6>
                    <ol class="mb-3">
                        <li>Haz clic en "Registrar Salida"</li>
                        <li>Selecciona la reserva que está haciendo checkout</li>
                        <li>Confirma los datos y procesa la salida</li>
                        <li>El sistema calculará automáticamente el estado de pagos</li>
                    </ol>

                    <h6 class="font-weight-bold">Estados de las salidas:</h6>
                    <ul class="list-unstyled">
                        <li><span class="badge badge-success">Finalizada</span> - Pagos completos, checkout exitoso</li>
                        <li><span class="badge badge-warning">Pendiente de pago</span> - Faltan pagos por completar</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-lightbulb"></i>
                        Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="/salidas/formulario" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">
                                    <i class="fas fa-plus text-primary"></i>
                                    Registrar Nueva Salida
                                </h6>
                            </div>
                            <p class="mb-1">Procesar el checkout de una reserva en curso.</p>
                        </a>

                        <a href="/salidas/busqueda" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">
                                    <i class="fas fa-search text-info"></i>
                                    Búsqueda Avanzada
                                </h6>
                            </div>
                            <p class="mb-1">Filtrar salidas por fechas, cabañas o huéspedes.</p>
                        </a>

                        <a href="/salidas/stats" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">
                                    <i class="fas fa-chart-bar text-success"></i>
                                    Ver Estadísticas
                                </h6>
                            </div>
                            <p class="mb-1">Análisis detallado de salidas y tendencias.</p>
                        </a>

                        <?php if (isset($estadisticas['pendientes_pago']) && $estadisticas['pendientes_pago'] > 0): ?>
                            <a href="/pagos" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <i class="fas fa-money-bill text-warning"></i>
                                        Gestionar Pagos Pendientes
                                    </h6>
                                    <span class="badge badge-warning"><?= $estadisticas['pendientes_pago'] ?></span>
                                </div>
                                <p class="mb-1">Registrar pagos para completar salidas pendientes.</p>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?php include __DIR__ . '/../layouts/footer.php'; ?>