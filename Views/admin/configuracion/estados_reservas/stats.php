<?php
$title = 'Estadísticas - Estados de Reservas';
$currentModule = 'estados_reservas';

require_once 'app/Views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Estadísticas de Estados de Reservas</h2>
        <div>
            <a href="/estados-reservas" class="btn btn-secondary">
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
        <!-- Estadísticas generales -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie"></i> Estados y Reservas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Estado</th>
                                    <th>Total de Reservas</th>
                                    <th>Porcentaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalReservas = array_sum(array_column($estadosConCount, 'reservas_count'));
                                foreach ($estadosConCount as $estado): 
                                    $porcentaje = $totalReservas > 0 ? round(($estado['reservas_count'] / $totalReservas) * 100, 2) : 0;
                                ?>
                                    <tr>
                                        <td>
                                            <span class="badge <?= $estado['estadoreserva_estado'] ? 'badge-success' : 'badge-secondary' ?>">
                                                <?= htmlspecialchars($estado['estadoreserva_descripcion']) ?>
                                            </span>
                                        </td>
                                        <td><?= number_format($estado['reservas_count']) ?></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: <?= $porcentaje ?>%" 
                                                     aria-valuenow="<?= $porcentaje ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <?= $porcentaje ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-info">
                                    <th>Total</th>
                                    <th><?= number_format($totalReservas) ?></th>
                                    <th>100%</th>
                                </tr>
                            </tfoot>
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
                        <i class="fas fa-chart-donut"></i> Distribución por Estados
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de evolución mensual -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-chart-line"></i> Evolución Mensual de Reservas por Estado (<?= $year ?>)
            </h5>
        </div>
        <div class="card-body">
            <canvas id="monthlyChart" style="height: 400px;"></canvas>
        </div>
    </div>

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
                            <div class="text-white-50 small">Total Estados</div>
                            <div class="h4 mb-0"><?= count($estadosConCount) ?></div>
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
                            <div class="text-white-50 small">Estados Activos</div>
                            <div class="h4 mb-0"><?= count(array_filter($estadosConCount, function($e) { return $e['estadoreserva_estado'] == 1; })) ?></div>
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
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-white-50 small">Reservas (<?= $year ?>)</div>
                            <div class="h4 mb-0"><?= number_format($totalReservas) ?></div>
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
                            <div class="text-white-50 small">Promedio Mensual</div>
                            <div class="h4 mb-0"><?= number_format($totalReservas / 12) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Pasar datos del gráfico al JavaScript centralizado
window.estadosReservasData = <?= json_encode($estadosConCount) ?>;
window.monthlyReservasData = <?= json_encode($monthlyStats) ?>;
</script>

<?php require_once 'app/Views/layouts/footer.php'; ?>