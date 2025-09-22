<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="content-header">
            <h1 class="h3 mb-2 text-gray-800"><?= $title ?></h1>
            <div class="mb-4">
                <a href="/marcas" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
            </div>
        </div>

        <!-- Resumen General -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total de Marcas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= $stats['total_marcas'] ?? 0 ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tag fa-2x text-gray-300"></i>
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
                                    Marcas Activas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= $stats['marcas_activas'] ?? 0 ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                    Marcas Inactivas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= $stats['marcas_inactivas'] ?? 0 ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
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
                                    Porcentaje Activas
                                </div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                            <?php 
                                            $total = $stats['total_marcas'] ?? 0;
                                            $activas = $stats['marcas_activas'] ?? 0;
                                            $porcentaje = $total > 0 ? round(($activas / $total) * 100, 1) : 0;
                                            echo $porcentaje . '%';
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="progress progress-sm mr-2">
                                            <div class="progress-bar bg-info" 
                                                 role="progressbar" 
                                                 style="width: <?= $porcentaje ?>%" 
                                                 aria-valuenow="<?= $porcentaje ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-pie fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Marcas Más Utilizadas -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            Marcas Más Utilizadas (Top 10)
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($stats['marcas_mas_utilizadas'])): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                No hay datos de uso de marcas disponibles.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="10%">Pos.</th>
                                            <th width="50%">Marca</th>
                                            <th width="20%">Productos</th>
                                            <th width="20%">Popularidad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $maxProductos = 0;
                                        foreach ($stats['marcas_mas_utilizadas'] as $marca) {
                                            $maxProductos = max($maxProductos, $marca['productos_count']);
                                        }
                                        ?>
                                        <?php foreach ($stats['marcas_mas_utilizadas'] as $index => $marca): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge badge-<?= $index < 3 ? 'success' : 'secondary' ?>">
                                                        #<?= $index + 1 ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-tag text-primary mr-2"></i>
                                                        <strong><?= htmlspecialchars($marca['marca_descripcion']) ?></strong>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        <?= $marca['productos_count'] ?> productos
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <?php 
                                                        $porcentaje = $maxProductos > 0 ? ($marca['productos_count'] / $maxProductos) * 100 : 0;
                                                        ?>
                                                        <div class="progress-bar bg-<?= $index < 3 ? 'success' : 'primary' ?>" 
                                                             role="progressbar" 
                                                             style="width: <?= $porcentaje ?>%"
                                                             title="<?= round($porcentaje, 1) ?>%">
                                                            <?= round($porcentaje, 1) ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Panel de Acciones y Info -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-bolt"></i> Acciones Rápidas
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="/marcas/create" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <i class="fas fa-plus text-primary"></i>
                                        Nueva Marca
                                    </h6>
                                </div>
                                <p class="mb-1">Agregar una nueva marca al sistema</p>
                            </a>
                            
                            <a href="/marcas" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <i class="fas fa-list text-info"></i>
                                        Ver Todas
                                    </h6>
                                </div>
                                <p class="mb-1">Gestionar todas las marcas</p>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Lista de Marcas Activas -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            Marcas Disponibles
                        </h6>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        <?php if (empty($marcas_activas)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                No hay marcas activas configuradas.
                                <a href="/marcas/create" class="alert-link">Crear la primera</a>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($marcas_activas as $marca): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-tag text-success mr-2"></i>
                                            <?= htmlspecialchars($marca['marca_descripcion']) ?>
                                        </div>
                                        <a href="/marcas/edit/<?= $marca['id_marca'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Información del Sistema -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-info-circle"></i> Información
                        </h6>
                    </div>
                    <div class="card-body">
                        <h6 class="text-primary">Estado del Sistema</h6>
                        <p class="text-muted small">
                            <?php if (($stats['marcas_activas'] ?? 0) > 0): ?>
                                <i class="fas fa-check-circle text-success"></i>
                                El sistema tiene marcas configuradas y funcionando correctamente.
                            <?php else: ?>
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                Se recomienda configurar al menos una marca activa.
                            <?php endif; ?>
                        </p>

                        <h6 class="text-primary mt-3">Recomendaciones</h6>
                        <ul class="text-muted small">
                            <li>Configure marcas conocidas en su mercado</li>
                            <li>Use nombres comerciales oficiales</li>
                            <li>Mantenga activas solo las marcas que maneja</li>
                            <li>Revise periódicamente el uso de cada marca</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Estado (si hay datos) -->
        <?php if (($stats['total_marcas'] ?? 0) > 0): ?>
            <div class="row">
                <div class="col-xl-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                Distribución por Estado
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-pie pt-4 pb-2">
                                <canvas id="estadosPieChart" width="100%" height="50"></canvas>
                            </div>
                            <div class="mt-4 text-center small">
                                <span class="mr-2">
                                    <i class="fas fa-circle text-success"></i> Activas
                                </span>
                                <span class="mr-2">
                                    <i class="fas fa-circle text-secondary"></i> Inactivas
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (($stats['total_marcas'] ?? 0) > 0): ?>
<script>
// Pasar datos del gráfico al JavaScript centralizado
window.marcasEstadosData = {
    activas: <?= $stats['marcas_activas'] ?? 0 ?>,
    inactivas: <?= $stats['marcas_inactivas'] ?? 0 ?>
};
</script>
<?php endif; ?>

<?php require_once 'app/Views/layouts/footer.php'; ?>