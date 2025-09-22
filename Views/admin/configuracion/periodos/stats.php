<?php
$title = 'Estadísticas de Periodos';
$currentModule = 'periodos';

require_once 'app/Views/layouts/header.php';
?>

<script>
document.body.dataset.periodosPorMes = '<?= json_encode($stats['periodos_por_mes']) ?>';
document.body.dataset.periodosActivos = '<?= $stats['periodos_activos'] ?>';
document.body.dataset.totalPeriodos = '<?= $stats['total_periodos'] ?>';
document.body.dataset.duracionPeriodos = '<?= json_encode($stats['duracion_periodos']) ?>';
</script>

<div class="container mt-4 periodos">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-chart-bar"></i> Estadísticas de Periodos</h2>
                <div>
                    <a href="/periodos" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Ver Listado
                    </a>
                    <a href="/periodos/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Periodo
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de estadísticas generales -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Periodos</h6>
                            <h3 class="mb-0"><?= $stats['total_periodos'] ?></h3>
                        </div>
                        <div class="text-primary-50">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                    </div>
                    <small class="text-primary-50">Periodos registrados en el sistema</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Periodos Activos</h6>
                            <h3 class="mb-0"><?= $stats['periodos_activos'] ?></h3>
                        </div>
                        <div class="text-success-50">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                    <small class="text-success-50">Periodos actualmente disponibles</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Periodo Actual</h6>
                            <h3 class="mb-0"><?= $stats['periodo_actual'] ? '1' : '0' ?></h3>
                        </div>
                        <div class="text-warning-50">
                            <i class="fas fa-star fa-2x"></i>
                        </div>
                    </div>
                    <small class="text-warning-50">
                        <?= $stats['periodo_actual'] ? $stats['periodo_actual']['periodo_descripcion'] : 'Ningún periodo activo actualmente' ?>
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Promedio Días</h6>
                            <h3 class="mb-0"><?= number_format($stats['duracion_promedio'], 1) ?></h3>
                        </div>
                        <div class="text-info-50">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                    <small class="text-info-50">Duración promedio por periodo</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <!-- Gráfico de periodos por mes -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Periodos por Mes
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartPeriodosPorMes" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Gráfico de estado de periodos -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie"></i> Estado de Periodos
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartEstadoPeriodos" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de duración de periodos -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Duración de Periodos (días)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartDuracionPeriodos" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de estadísticas detalladas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-table"></i> Resumen Detallado de Periodos
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($periodos_detalle)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Descripción</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Fin</th>
                                        <th>Duración</th>
                                        <th>Estado</th>
                                        <th>Clasificación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($periodos_detalle as $periodo): ?>
                                        <?php
                                        $hoy = new DateTime();
                                        $inicio = new DateTime($periodo['periodo_fechainicio']);
                                        $fin = new DateTime($periodo['periodo_fechafin']);
                                        $duracion = $inicio->diff($fin)->days + 1;
                                        
                                        $clasificacion = '';
                                        $badgeClass = '';
                                        if ($hoy >= $inicio && $hoy <= $fin) {
                                            $clasificacion = 'ACTUAL';
                                            $badgeClass = 'badge-warning';
                                        } elseif ($hoy < $inicio) {
                                            $clasificacion = 'FUTURO';
                                            $badgeClass = 'badge-info';
                                        } else {
                                            $clasificacion = 'PASADO';
                                            $badgeClass = 'badge-secondary';
                                        }
                                        ?>
                                        <tr <?= $clasificacion === 'ACTUAL' ? 'class="table-warning"' : '' ?>>
                                            <td><?= $periodo['id_periodo'] ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($periodo['periodo_descripcion']) ?></strong>
                                                <?php if ($clasificacion === 'ACTUAL'): ?>
                                                    <i class="fas fa-star text-warning ml-1" title="Periodo Actual"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($periodo['periodo_fechainicio'])) ?></td>
                                            <td><?= date('d/m/Y', strtotime($periodo['periodo_fechafin'])) ?></td>
                                            <td>
                                                <span class="badge badge-light"><?= $duracion ?> días</span>
                                            </td>
                                            <td>
                                                <?php if ($periodo['periodo_estado'] == 1): ?>
                                                    <span class="badge badge-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= $badgeClass ?>"><?= $clasificacion ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay periodos registrados en el sistema.</p>
                            <a href="/periodos/create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Crear Primer Periodo
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/Views/layouts/footer.php'; ?>