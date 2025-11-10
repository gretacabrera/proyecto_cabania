<?php
$title = 'Estadísticas - Tipos de Contactos';
$currentModule = 'tipos_contactos';

require_once 'app/Views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Estadísticas de Tipos de Contactos</h2>
        <div>
            <a href="/tipos-contactos" class="btn btn-secondary">
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
                                    <th>Tipo de Contacto</th>
                                    <th>Total Uso</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mostUsed as $index => $tipo): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-primary"><?= $index + 1 ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($tipo['tipocontacto_descripcion']) ?></td>
                                        <td>
                                            <strong><?= number_format($tipo['total_usage']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge <?= $tipo['tipocontacto_estado'] ? 'badge-success' : 'badge-secondary' ?>">
                                                <?= $tipo['tipocontacto_estado'] ? 'Activo' : 'Inactivo' ?>
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
                        <i class="fas fa-chart-pie"></i> Distribución de Uso por Tipo
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen detallado de uso -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-chart-bar"></i> Resumen Detallado de Uso
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Tipo de Contacto</th>
                            <th>Personas</th>
                            <th>Contactos</th>
                            <th>Total</th>
                            <th>Porcentaje</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalUso = array_sum(array_column($usageSummary, 'total'));
                        foreach ($usageSummary as $resumen): 
                            $porcentaje = $totalUso > 0 ? round(($resumen['total'] / $totalUso) * 100, 2) : 0;
                        ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($resumen['tipocontacto_descripcion']) ?></strong>
                                </td>
                                <td><?= number_format($resumen['personas']) ?></td>
                                <td><?= number_format($resumen['contactos']) ?></td>
                                <td><strong><?= number_format($resumen['total']) ?></strong></td>
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
                                    <span class="badge badge-success">Activo</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-info">
                            <th>Total</th>
                            <th><?= number_format(array_sum(array_column($usageSummary, 'personas'))) ?></th>
                            <th><?= number_format(array_sum(array_column($usageSummary, 'contactos'))) ?></th>
                            <th><?= number_format($totalUso) ?></th>
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
                <i class="fas fa-chart-line"></i> Evolución Mensual de Contactos por Tipo (<?= $year ?>)
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
                            <div class="h4 mb-0"><?= count(array_filter($tiposConCount, function($t) { return $t['tipocontacto_estado'] == 1; })) ?></div>
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
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-white-50 small">Total Personas</div>
                            <div class="h4 mb-0"><?= number_format(array_sum(array_column($usageSummary, 'personas'))) ?></div>
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
                            <i class="fas fa-address-book fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-white-50 small">Total Contactos</div>
                            <div class="h4 mb-0"><?= number_format(array_sum(array_column($usageSummary, 'contactos'))) ?></div>
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
window.tiposContactosData = {
    tipos: <?= json_encode($tiposConCount) ?>,
    monthly: <?= json_encode($monthlyStats) ?>,
    usage: <?= json_encode($usageSummary) ?>,
    year: <?= json_encode($year) ?>
};

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar gráficos usando funciones centralizadas
    initTiposContactosCharts(window.tiposContactosData);
});
</script>

<?php require_once 'app/Views/layouts/footer.php'; ?>