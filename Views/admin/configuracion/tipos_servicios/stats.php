<?php
$title = 'Estadísticas - Tipos de Servicios';
$currentModule = 'tipos_servicios';

require_once 'app/Views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Estadísticas de Tipos de Servicios</h2>
        <div>
            <a href="/tipos-servicios" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <!-- Filtro de año -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filtros</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="form-inline">
                <div class="form-group mr-3">
                    <label for="year" class="mr-2">Año:</label>
                    <select class="form-control" id="year" name="year">
                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                            <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>>
                                <?= $y ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Tipos más utilizados -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy"></i> Top 10 - Tipos Más Utilizados
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tipo de Servicio</th>
                                    <th>Total Servicios</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mostUsed as $index => $tipo): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-primary"><?= $index + 1 ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($tipo['tiposervicio_descripcion']) ?></td>
                                        <td>
                                            <strong><?= number_format($tipo['total_servicios']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge <?= $tipo['tiposervicio_estado'] ? 'badge-success' : 'badge-secondary' ?>">
                                                <?= $tipo['tiposervicio_estado'] ? 'Activo' : 'Inactivo' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de distribución -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie"></i> Distribución de Servicios por Tipo
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas generales -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-chart-bar"></i> Tipos de Servicios y Cantidad de Servicios
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Tipo de Servicio</th>
                            <th>Total de Servicios</th>
                            <th>Porcentaje</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalServicios = array_sum(array_column($tiposConCount, 'servicios_count'));
                        foreach ($tiposConCount as $tipo): 
                            $porcentaje = $totalServicios > 0 ? round(($tipo['servicios_count'] / $totalServicios) * 100, 2) : 0;
                        ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($tipo['tiposervicio_descripcion']) ?></strong>
                                </td>
                                <td><?= number_format($tipo['servicios_count']) ?></td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                             style="width: <?= $porcentaje ?>%" 
                                             aria-valuenow="<?= $porcentaje ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?= $porcentaje ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?= $tipo['tiposervicio_estado'] ? 'badge-success' : 'badge-secondary' ?>">
                                        <?= $tipo['tiposervicio_estado'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-info">
                            <th>Total</th>
                            <th><?= number_format($totalServicios) ?></th>
                            <th>100%</th>
                            <th>-</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Gráfico de evolución mensual -->
    <?php if (!empty($monthlyStats)): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-chart-line"></i> Evolución Mensual de Servicios por Tipo (<?= $year ?>)
            </h5>
        </div>
        <div class="card-body">
            <canvas id="monthlyChart" style="height: 400px;"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Resumen estadístico -->
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-list fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-white-50 small">Total Tipos</div>
                            <div class="h4 mb-0"><?= count($tiposConCount) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-white-50 small">Tipos Activos</div>
                            <div class="h4 mb-0"><?= count(array_filter($tiposConCount, function($t) { return $t['tiposervicio_estado'] == 1; })) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-cogs fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-white-50 small">Total Servicios</div>
                            <div class="h4 mb-0"><?= number_format($totalServicios) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-white-50 small">Promedio por Tipo</div>
                            <div class="h4 mb-0"><?= count($tiposConCount) > 0 ? number_format($totalServicios / count($tiposConCount)) : 0 ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('assets/js/main.js') ?>"></script>

<!-- Incluir Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Datos PHP para JavaScript
window.tiposServiciosData = {
    tipos: <?= json_encode($tiposConCount) ?>,
    monthly: <?= json_encode($monthlyStats) ?>,
    year: <?= json_encode($year) ?>
};

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar gráficos usando funciones centralizadas
    initTiposServiciosCharts(window.tiposServiciosData);
});
</script>

<?php require_once 'app/Views/layouts/footer.php'; ?>