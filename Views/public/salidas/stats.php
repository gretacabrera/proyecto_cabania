<?php
$pageTitle = $title ?? 'Estadísticas de Salidas';
include __DIR__ . '/../../shared/layouts/header.php';
?>

<div class="container-fluid px-4">
    <!-- Encabezado -->
    <div class="row">
        <div class="col-12">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-chart-bar text-primary"></i>
                    Estadísticas de Salidas
                </h1>
                <div class="btn-group" role="group">
                    <a href="/salidas" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Volver al Listado
                    </a>
                    <a href="/salidas/busqueda" class="btn btn-outline-info">
                        <i class="fas fa-search"></i>
                        Búsqueda Avanzada
                    </a>
                    <button data-action="print" class="btn btn-outline-primary">
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

    <?php if (isset($estadisticas) && $estadisticas): ?>
        <!-- Tarjetas de estadísticas principales -->
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

        <!-- Gráficos y análisis -->
        <div class="row">
            <!-- Distribución por estado -->
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-pie"></i>
                            Distribución por Estado (Este Mes)
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($estadisticas['distribucion_estados'])): ?>
                            <div class="chart-pie pt-4 pb-2">
                                <canvas id="pieChart"></canvas>
                            </div>
                            
                            <div class="mt-4 text-center small">
                                <?php foreach ($estadisticas['distribucion_estados'] as $estado): ?>
                                    <span class="mr-2">
                                        <i class="fas fa-circle <?= $estado['estadoreserva_descripcion'] === 'finalizada' ? 'text-success' : 'text-warning' ?>"></i>
                                        <?= ucfirst(htmlspecialchars($estado['estadoreserva_descripcion'])) ?>: 
                                        <strong><?= intval($estado['cantidad']) ?></strong>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-chart-pie fa-2x text-gray-300 mb-3"></i>
                                <p class="text-muted">No hay datos suficientes para mostrar la distribución</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Cabaña más popular -->
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-trophy"></i>
                            Cabaña con Más Salidas (Este Mes)
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (isset($estadisticas['cabania_popular']) && $estadisticas['cabania_popular']): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-home fa-3x text-success mb-3"></i>
                                <h4 class="font-weight-bold text-primary">
                                    <?= htmlspecialchars($estadisticas['cabania_popular']['cabania_nombre']) ?>
                                </h4>
                                <p class="text-muted mb-2">Cabaña más utilizada</p>
                                <div class="progress progress-lg mb-3">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: 100%;">
                                        <?= intval($estadisticas['cabania_popular']['total_salidas']) ?> salidas
                                    </div>
                                </div>
                                <div class="row text-center">
                                    <div class="col">
                                        <span class="text-success">
                                            <i class="fas fa-check-circle"></i>
                                            <strong>Liderazgo</strong>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-home fa-2x text-gray-300 mb-3"></i>
                                <p class="text-muted">No hay datos suficientes para determinar la cabaña más popular</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Salidas recientes y análisis -->
        <div class="row">
            <!-- Lista de salidas recientes -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-clock"></i>
                            Salidas Recientes (Última Semana)
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (isset($salidas_recientes) && !empty($salidas_recientes)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th><i class="fas fa-hashtag"></i> ID</th>
                                            <th><i class="fas fa-user"></i> Huésped</th>
                                            <th><i class="fas fa-home"></i> Cabaña</th>
                                            <th><i class="fas fa-calendar-check"></i> Check-out</th>
                                            <th><i class="fas fa-info-circle"></i> Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($salidas_recientes, 0, 10) as $salida): ?>
                                            <tr>
                                                <td class="font-weight-bold text-primary">
                                                    <a href="/salidas/<?= htmlspecialchars($salida['id_reserva']) ?>/detalle">
                                                        #<?= htmlspecialchars($salida['id_reserva']) ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <small><?= htmlspecialchars($salida['persona_nombre'] . ' ' . $salida['persona_apellido']) ?></small>
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
                                                <td>
                                                    <?php 
                                                    // Determinar clase CSS según el estado de la reserva
                                                    switch($salida['estadoreserva_descripcion']) {
                                                        case 'finalizada':
                                                            $estadoClass = 'badge-success';
                                                            break;
                                                        case 'pendiente de pago':
                                                            $estadoClass = 'badge-warning';
                                                            break;
                                                        default:
                                                            $estadoClass = 'badge-secondary';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge <?= $estadoClass ?> badge-sm">
                                                        <?= ucfirst(htmlspecialchars($salida['estadoreserva_descripcion'])) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if (count($salidas_recientes) > 10): ?>
                                <div class="text-center mt-3">
                                    <a href="/salidas/busqueda" class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                        Ver Todas las Salidas Recientes
                                    </a>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-clock fa-2x text-gray-300 mb-3"></i>
                                <p class="text-muted">No hay salidas recientes para mostrar</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Panel de análisis rápido -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-chart-line"></i>
                            Análisis Rápido
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="font-weight-bold text-primary">
                                <i class="fas fa-percentage"></i>
                                Tasa de Finalización
                            </h6>
                            <?php 
                            $totalSalidasMes = intval($estadisticas['salidas_mes']);
                            $salidasFinalizadas = 0;
                            if (!empty($estadisticas['distribucion_estados'])) {
                                foreach ($estadisticas['distribucion_estados'] as $estado) {
                                    if ($estado['estadoreserva_descripcion'] === 'finalizada') {
                                        $salidasFinalizadas = intval($estado['cantidad']);
                                        break;
                                    }
                                }
                            }
                            $tasaFinalizacion = $totalSalidasMes > 0 ? ($salidasFinalizadas / $totalSalidasMes * 100) : 0;
                            ?>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?= $tasaFinalizacion ?>%;"
                                     aria-valuenow="<?= $tasaFinalizacion ?>" 
                                     aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted">
                                <?= number_format($tasaFinalizacion, 1) ?>% de las salidas están completamente pagadas
                            </small>
                        </div>

                        <div class="mb-4">
                            <h6 class="font-weight-bold text-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Atención Requerida
                            </h6>
                            <div class="alert alert-<?= $estadisticas['pendientes_pago'] > 0 ? 'warning' : 'success' ?> py-2">
                                <?php if ($estadisticas['pendientes_pago'] > 0): ?>
                                    <strong><?= $estadisticas['pendientes_pago'] ?></strong> reserva<?= $estadisticas['pendientes_pago'] > 1 ? 's' : '' ?> 
                                    pendiente<?= $estadisticas['pendientes_pago'] > 1 ? 's' : '' ?> de pago
                                    <br>
                                    <a href="/salidas/busqueda?estado=pendiente+de+pago" class="btn btn-warning btn-sm mt-2">
                                        <i class="fas fa-eye"></i> Ver Pendientes
                                    </a>
                                <?php else: ?>
                                    <i class="fas fa-check-circle"></i>
                                    Todas las salidas están al día con los pagos
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="font-weight-bold text-success">
                                <i class="fas fa-trending-up"></i>
                                Tendencia Semanal
                            </h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Esta semana:</span>
                                <strong class="text-success">
                                    <?= intval($estadisticas['salidas_semana']) ?> salidas
                                </strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Promedio diario:</span>
                                <strong class="text-info">
                                    <?= number_format($estadisticas['salidas_semana'] / 7, 1) ?> salidas/día
                                </strong>
                            </div>
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
                        <div class="d-grid gap-2">
                            <?php if ($estadisticas['pendientes_pago'] > 0): ?>
                                <a href="/salidas/busqueda?estado=pendiente+de+pago" 
                                   class="btn btn-warning btn-block">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Ver Pagos Pendientes
                                </a>
                            <?php endif; ?>
                            
                            <a href="/salidas/formulario" class="btn btn-primary btn-block">
                                <i class="fas fa-plus"></i>
                                Registrar Nueva Salida
                            </a>
                            
                            <a href="/salidas/busqueda" class="btn btn-info btn-block">
                                <i class="fas fa-search"></i>
                                Búsqueda Avanzada
                            </a>
                            
                            <a href="/reportes/salidas" class="btn btn-secondary btn-block">
                                <i class="fas fa-file-alt"></i>
                                Generar Reporte
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Error al cargar estadísticas -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-chart-bar fa-3x text-gray-300 mb-4"></i>
                        <h4 class="text-gray-600">Error al cargar estadísticas</h4>
                        <p class="text-muted">
                            No se pudieron cargar las estadísticas de salidas en este momento.
                        </p>
                        <div class="mt-4">
                            <a href="/salidas" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i>
                                Volver al Listado
                            </a>
                            <button data-action="reload" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-redo"></i>
                                Intentar Nuevamente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Los datos de estadísticas se cargan automáticamente por public.js
document.addEventListener('DOMContentLoaded', function() {
    if (typeof initSalidasStats === 'function') {
        const chartData = {};
        
        <?php if (!empty($estadisticas['distribucion_estados'])): ?>
        chartData.distribucionEstados = {
            labels: [
                <?php 
                $estados = $estadisticas['distribucion_estados'];
                $total = count($estados);
                foreach ($estados as $index => $estado): 
                ?>
                '<?= ucfirst(htmlspecialchars($estado['estadoreserva_descripcion'])) ?>'<?= ($index < $total - 1) ? ',' : '' ?>
                <?php endforeach; ?>
            ],
            data: [
                <?php 
                foreach ($estados as $index => $estado): 
                ?>
                <?= intval($estado['cantidad']) ?><?= ($index < $total - 1) ? ',' : '' ?>
                <?php endforeach; ?>
            ]
        };
        <?php endif; ?>
        
        initSalidasStats(chartData);
    }
});
</script>

<?php include __DIR__ . '/../../shared/layouts/footer.php'; ?>