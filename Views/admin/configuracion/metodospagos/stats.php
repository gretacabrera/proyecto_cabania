<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="content-header">
            <h1 class="h3 mb-2 text-gray-800"><?= $title ?></h1>
            <div class="mb-4">
                <a href="/metodos_pagos" class="btn btn-secondary">
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
                                    Total de Métodos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= $stats['total_metodos'] ?? 0 ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-credit-card fa-2x text-gray-300"></i>
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
                                    Métodos Activos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= $stats['metodos_activos'] ?? 0 ?>
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
                                    Métodos Inactivos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= $stats['metodos_inactivos'] ?? 0 ?>
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
                                    Porcentaje Activos
                                </div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                            <?php 
                                            $total = $stats['total_metodos'] ?? 0;
                                            $activos = $stats['metodos_activos'] ?? 0;
                                            $porcentaje = $total > 0 ? round(($activos / $total) * 100, 1) : 0;
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
            <!-- Lista de Métodos Activos -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            Métodos de Pago Disponibles
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($metodos_activos)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                No hay métodos de pago activos configurados.
                                <a href="/metodos_pagos/create" class="alert-link">Crear el primero</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="10%">ID</th>
                                            <th width="70%">Descripción</th>
                                            <th width="20%">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($metodos_activos as $metodo): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($metodo['id_metododepago']) ?></td>
                                                <td>
                                                    <i class="fas fa-credit-card text-success mr-2"></i>
                                                    <?= htmlspecialchars($metodo['metododepago_descripcion']) ?>
                                                </td>
                                                <td>
                                                    <a href="/metodos_pagos/edit/<?= $metodo['id_metododepago'] ?>" 
                                                       class="btn btn-sm btn-primary" 
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
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

            <!-- Panel de Acciones Rápidas -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-bolt"></i> Acciones Rápidas
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="/metodos_pagos/create" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <i class="fas fa-plus text-primary"></i>
                                        Nuevo Método
                                    </h6>
                                </div>
                                <p class="mb-1">Agregar un nuevo método de pago</p>
                            </a>
                            
                            <a href="/metodos_pagos" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <i class="fas fa-list text-info"></i>
                                        Ver Todos
                                    </h6>
                                </div>
                                <p class="mb-1">Gestionar todos los métodos</p>
                            </a>
                        </div>
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
                            <?php if (($stats['metodos_activos'] ?? 0) > 0): ?>
                                <i class="fas fa-check-circle text-success"></i>
                                El sistema tiene métodos de pago configurados y funcionando correctamente.
                            <?php else: ?>
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                Se recomienda configurar al menos un método de pago activo.
                            <?php endif; ?>
                        </p>

                        <h6 class="text-primary mt-3">Recomendaciones</h6>
                        <ul class="text-muted small">
                            <li>Mantenga activos solo los métodos que realmente acepta</li>
                            <li>Use nombres claros y descriptivos</li>
                            <li>Configure los métodos más comunes primero</li>
                            <li>Revise periódicamente el estado de los métodos</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Estado (si hay datos) -->
        <?php if (($stats['total_metodos'] ?? 0) > 0): ?>
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
                                <canvas id="estadosPieChart" 
                                        data-stats="<?= htmlspecialchars(json_encode($stats)) ?>" 
                                        width="100%" 
                                        height="50"></canvas>
                            </div>
                            <div class="mt-4 text-center small">
                                <span class="mr-2">
                                    <i class="fas fa-circle text-success"></i> Activos
                                </span>
                                <span class="mr-2">
                                    <i class="fas fa-circle text-secondary"></i> Inactivos
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'app/Views/layouts/footer.php'; ?>