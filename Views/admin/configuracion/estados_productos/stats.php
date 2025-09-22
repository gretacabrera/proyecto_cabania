<?php $this->layout('layouts/main', ['title' => $title]) ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= $title ?></h2>
        <a href="/proyecto_cabania/estados-productos" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>

    <div class="row">
        <!-- Resumen general -->
        <div class="col-md-4 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Estados</h6>
                            <h4 class="mb-0"><?= count($estados_stats) ?></h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-tags fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Estados Activos</h6>
                            <h4 class="mb-0">
                                <?= count(array_filter($estados_stats, fn($e) => $e['estadoproducto_estado'] == 1)) ?>
                            </h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Productos Asignados</h6>
                            <h4 class="mb-0">
                                <?= array_sum(array_column($estados_stats, 'productos_count')) ?>
                            </h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-box fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla detallada -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Estados de Productos con Estadísticas</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($estados_stats)): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th class="text-center">Productos Asignados</th>
                                <th class="text-center">Porcentaje de Uso</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalProductos = array_sum(array_column($estados_stats, 'productos_count'));
                            foreach ($estados_stats as $estado): 
                                $porcentaje = $totalProductos > 0 ? 
                                    round(($estado['productos_count'] / $totalProductos) * 100, 1) : 0;
                            ?>
                                <tr>
                                    <td><?= $estado['id_estadoproducto'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($estado['estadoproducto_descripcion']) ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($estado['estadoproducto_estado'] == 1): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?= $estado['productos_count'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="progress" style="width: 100px;">
                                            <div class="progress-bar" 
                                                 role="progressbar" 
                                                 style="width: <?= $porcentaje ?>%"
                                                 aria-valuenow="<?= $porcentaje ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <?= $porcentaje ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="/proyecto_cabania/estados-productos/<?= $estado['id_estadoproducto'] ?>/edit" 
                                           class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No hay datos para mostrar estadísticas</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Gráfico de distribución -->
    <?php if (!empty($estados_stats) && array_sum(array_column($estados_stats, 'productos_count')) > 0): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Distribución de Productos por Estado</h5>
            </div>
            <div class="card-body">
                <canvas id="distribucionChart" height="100"></canvas>
            </div>
        </div>

        <script>
        // Pasar datos del gráfico al JavaScript centralizado
        window.estadosProductosChartData = {
            labels: [
                <?php foreach ($estados_stats as $estado): ?>
                    '<?= addslashes($estado['estadoproducto_descripcion']) ?>',
                <?php endforeach; ?>
            ],
            data: [
                <?php foreach ($estados_stats as $estado): ?>
                    <?= $estado['productos_count'] ?>,
                <?php endforeach; ?>
            ],
            backgroundColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF',
                '#4BC0C0', '#FF6384', '#36A2EB', '#FFCE56'
            ]
        };
        </script>
    <?php endif; ?>
</div>