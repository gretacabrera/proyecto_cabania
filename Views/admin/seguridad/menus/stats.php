<?php
/**
 * Vista de estadísticas de menús
 */

$title = $data['title'] ?? 'Estadísticas de Menús';
$stats = $data['stats'] ?? [];

?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><?php echo htmlspecialchars($title); ?></h1>
                    <p class="text-muted">Análisis y métricas del sistema de menús</p>
                </div>
                <div>
                    <a href="/menus" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Listado
                    </a>
                    <a href="/menus/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Menú
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas principales -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-text mb-1">Total de Menús</p>
                            <h2 class="mb-0"><?php echo $stats['total'] ?? 0; ?></h2>
                        </div>
                        <div>
                            <i class="fas fa-bars fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-primary bg-opacity-75">
                    <small>Menús registrados en el sistema</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-text mb-1">Menús Activos</p>
                            <h2 class="mb-0"><?php echo $stats['activos'] ?? 0; ?></h2>
                        </div>
                        <div>
                            <i class="fas fa-check-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-success bg-opacity-75">
                    <small>
                        <?php 
                        $total = $stats['total'] ?? 0;
                        $activos = $stats['activos'] ?? 0;
                        $porcentaje = $total > 0 ? round(($activos / $total) * 100, 1) : 0;
                        echo $porcentaje; ?>% del total
                    </small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-text mb-1">Menús Inactivos</p>
                            <h2 class="mb-0"><?php echo $stats['inactivos'] ?? 0; ?></h2>
                        </div>
                        <div>
                            <i class="fas fa-times-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-warning bg-opacity-75">
                    <small>
                        <?php 
                        $inactivos = $stats['inactivos'] ?? 0;
                        $porcentajeInactivos = $total > 0 ? round(($inactivos / $total) * 100, 1) : 0;
                        echo $porcentajeInactivos; ?>% del total
                    </small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-text mb-1">Promedio Orden</p>
                            <h2 class="mb-0"><?php echo number_format($stats['promedio_orden'] ?? 0, 1); ?></h2>
                        </div>
                        <div>
                            <i class="fas fa-sort-numeric-down fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-info bg-opacity-75">
                    <small>Orden promedio de posición</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos y análisis -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie"></i>
                        Distribución por Estado
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container chart-container-lg">
                        <canvas id="estadosChart" data-stats="<?php echo htmlspecialchars(json_encode($stats)); ?>"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i>
                        Resumen Numérico
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border-end">
                                <h3 class="text-primary"><?php echo $stats['total'] ?? 0; ?></h3>
                                <p class="mb-0 text-muted">Total</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <h3 class="text-success"><?php echo $stats['activos'] ?? 0; ?></h3>
                            <p class="mb-0 text-muted">Activos</p>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border-end">
                                <h3 class="text-warning"><?php echo $stats['inactivos'] ?? 0; ?></h3>
                                <p class="mb-0 text-muted">Inactivos</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <h3 class="text-info"><?php echo number_format($stats['promedio_orden'] ?? 0, 1); ?></h3>
                            <p class="mb-0 text-muted">Promedio</p>
                        </div>
                    </div>

                    <div class="progress progress-lg mb-3">
                        <?php 
                        $total = $stats['total'] ?? 0;
                        $activos = $stats['activos'] ?? 0;
                        $inactivos = $stats['inactivos'] ?? 0;
                        
                        if ($total > 0):
                            $porcentajeActivos = ($activos / $total) * 100;
                            $porcentajeInactivos = ($inactivos / $total) * 100;
                        ?>
                        <div class="progress-bar bg-success" role="progressbar" 
                             class="progress-bar bg-success"
                             data-width="<?php echo $porcentajeActivos; ?>%" 
                             title="Activos: <?php echo $activos; ?> (<?php echo round($porcentajeActivos, 1); ?>%)">
                        </div>
                        <div class="progress-bar bg-warning" role="progressbar" 
                             class="progress-bar bg-secondary"
                             data-width="<?php echo $porcentajeInactivos; ?>%" 
                             title="Inactivos: <?php echo $inactivos; ?> (<?php echo round($porcentajeInactivos, 1); ?>%)">
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="row small">
                        <div class="col-6">
                            <span class="badge bg-success me-1">■</span>
                            Activos: <?php echo round($porcentajeActivos ?? 0, 1); ?>%
                        </div>
                        <div class="col-6">
                            <span class="badge bg-warning me-1">■</span>
                            Inactivos: <?php echo round($porcentajeInactivos ?? 0, 1); ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información detallada -->
    <div class="row">
        <div class="col-lg-4 mb-3">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle"></i>
                        Información General
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between align-items-center mb-2">
                            <span>Total de menús:</span>
                            <strong><?php echo $stats['total'] ?? 0; ?></strong>
                        </li>
                        <li class="d-flex justify-content-between align-items-center mb-2">
                            <span>Menús activos:</span>
                            <span class="badge bg-success"><?php echo $stats['activos'] ?? 0; ?></span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center mb-2">
                            <span>Menús inactivos:</span>
                            <span class="badge bg-warning"><?php echo $stats['inactivos'] ?? 0; ?></span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center">
                            <span>Orden promedio:</span>
                            <span class="badge bg-info"><?php echo number_format($stats['promedio_orden'] ?? 0, 1); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line"></i>
                        Métricas de Calidad
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small">Porcentaje de Menús Activos</label>
                        <div class="progress progress-md">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 class="progress-bar bg-success"
                                 data-width="<?php echo $porcentajeActivos ?? 0; ?>%">
                                <?php echo round($porcentajeActivos ?? 0, 1); ?>%
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Distribución de Estado</label>
                        <div class="small text-muted">
                            <?php if (($porcentajeActivos ?? 0) >= 80): ?>
                                <i class="fas fa-check-circle text-success"></i> Excelente distribución
                            <?php elseif (($porcentajeActivos ?? 0) >= 60): ?>
                                <i class="fas fa-exclamation-circle text-warning"></i> Distribución aceptable
                            <?php else: ?>
                                <i class="fas fa-times-circle text-danger"></i> Revisar menús inactivos
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="text-center">
                        <small class="text-muted">
                            Estado del sistema: 
                            <?php if (($stats['total'] ?? 0) === 0): ?>
                                <span class="badge bg-secondary">Sin datos</span>
                            <?php elseif (($porcentajeActivos ?? 0) >= 80): ?>
                                <span class="badge bg-success">Óptimo</span>
                            <?php elseif (($porcentajeActivos ?? 0) >= 60): ?>
                                <span class="badge bg-warning">Regular</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Necesita atención</span>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-cogs"></i>
                        Acciones Recomendadas
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (($stats['total'] ?? 0) === 0): ?>
                        <div class="alert alert-info small py-2 mb-2">
                            <i class="fas fa-plus"></i> Crear el primer menú del sistema
                        </div>
                        <a href="/menus/create" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-plus"></i> Crear Menú
                        </a>
                    <?php elseif (($stats['inactivos'] ?? 0) > 0): ?>
                        <div class="alert alert-warning small py-2 mb-2">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Hay <?php echo $stats['inactivos']; ?> menú(s) inactivo(s)
                        </div>
                        <a href="/menus?estado=0" class="btn btn-warning btn-sm w-100 mb-2">
                            <i class="fas fa-eye"></i> Ver Menús Inactivos
                        </a>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2 mt-2">
                        <a href="/menus/reorder" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-sort"></i> Reordenar Menús
                        </a>
                        <a href="/menus/create" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus"></i> Agregar Menú
                        </a>
                        <a href="/menus" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-list"></i> Ver Todos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php require_once 'Views/layouts/footer.php'; ?>