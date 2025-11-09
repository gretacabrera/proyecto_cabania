<!-- Vista: Estadísticas de Ingresos -->
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0"><?= $titulo ?></h6>
                            <p class="text-sm mb-0">Análisis y métricas de ingresos al complejo</p>
                        </div>
                        <div class="btn-group" role="group">
                            <a href="ingresos" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Volver
                            </a>
                            <a href="ingresos/formulario" class="btn btn-primary btn-sm">
                                <i class="fas fa-sign-in-alt me-1"></i>Nuevo Ingreso
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($error)): ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
        </div>
    </div>
    <?php elseif (empty($estadisticas)): ?>
    <div class="row">
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-chart-bar fa-4x text-muted mb-4"></i>
                <h4 class="text-muted">No hay datos estadísticos disponibles</h4>
                <p class="text-muted">Las estadísticas aparecerán cuando haya ingresos registrados</p>
            </div>
        </div>
    </div>
    <?php else: ?>

    <!-- Estados de Reservas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>Estados de Reservas
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($estadisticas['estados-reservas'])): ?>
                    <div class="row">
                        <?php 
                        $totalReservas = array_sum(array_column($estadisticas['estados-reservas'], 'cantidad'));
                        $colores = [
                            'confirmada' => 'warning',
                            'en curso' => 'success', 
                            'finalizada' => 'primary',
                            'cancelada' => 'danger',
                            'pendiente de pago' => 'info'
                        ];
                        ?>
                        <?php foreach ($estadisticas['estados-reservas'] as $estado): ?>
                        <?php 
                        $porcentaje = $totalReservas > 0 ? round(($estado['cantidad'] / $totalReservas) * 100, 1) : 0;
                        $colorClass = $colores[$estado['estado']] ?? 'secondary';
                        ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-<?= $colorClass ?>">
                                <div class="card-body text-center">
                                    <div class="icon icon-lg icon-<?= $colorClass ?> shadow border-radius-md mb-3">
                                        <i class="fas fa-<?= $estado['estado'] === 'en curso' ? 'clock' : ($estado['estado'] === 'finalizada' ? 'check-circle' : 'bookmark') ?>"></i>
                                    </div>
                                    <h5 class="text-<?= $colorClass ?> mb-1"><?= $estado['cantidad'] ?></h5>
                                    <p class="text-sm mb-1 text-capitalize font-weight-bold">
                                        <?= ucfirst($estado['estado']) ?>
                                    </p>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-<?= $colorClass ?>" 
                                             role="progressbar" 
                                             style="width: <?= $porcentaje ?>%">
                                        </div>
                                    </div>
                                    <small class="text-muted"><?= $porcentaje ?>% del total</small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-inbox me-2"></i>No hay datos de estados disponibles
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Ingresos por Mes -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Ingresos por Mes (Último Año)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($estadisticas['ingresos_por_mes'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mes</th>
                                    <th>Ingresos</th>
                                    <th>Gráfico</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $maxIngresos = max(array_column($estadisticas['ingresos_por_mes'], 'ingresos'));
                                $meses = [
                                    '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
                                    '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
                                    '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
                                ];
                                ?>
                                <?php foreach ($estadisticas['ingresos_por_mes'] as $mes): ?>
                                <?php 
                                $mesNombre = $meses[substr($mes['mes'], -2)] . ' ' . substr($mes['mes'], 0, 4);
                                $porcentaje = $maxIngresos > 0 ? ($mes['ingresos'] / $maxIngresos) * 100 : 0;
                                ?>
                                <tr>
                                    <td class="font-weight-bold"><?= $mesNombre ?></td>
                                    <td>
                                        <span class="badge badge-primary"><?= $mes['ingresos'] ?></span>
                                    </td>
                                    <td>
                                        <div class="progress progress-sm" style="width: 200px;">
                                            <div class="progress-bar bg-gradient-success" 
                                                 role="progressbar" 
                                                 style="width: <?= $porcentaje ?>%">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-calendar-times me-2"></i>No hay datos mensuales disponibles
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Cabañas Más Populares -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>Cabañas Más Utilizadas
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($estadisticas['cabanias_populares'])): ?>
                    <div class="row">
                        <?php 
                        $maxUsos = max(array_column($estadisticas['cabanias_populares'], 'usos'));
                        $medalColors = ['warning', 'secondary', 'dark', 'info', 'success'];
                        ?>
                        <?php foreach ($estadisticas['cabanias_populares'] as $index => $cabania): ?>
                        <?php 
                        $colorClass = $medalColors[$index] ?? 'primary';
                        $porcentaje = $maxUsos > 0 ? ($cabania['usos'] / $maxUsos) * 100 : 0;
                        ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="position-relative mb-3">
                                        <?php if ($index < 3): ?>
                                        <div class="badge badge-<?= $colorClass ?> position-absolute top-0 start-0">
                                            #<?= $index + 1 ?>
                                        </div>
                                        <?php endif; ?>
                                        <div class="icon icon-lg icon-<?= $colorClass ?> shadow border-radius-md mx-auto">
                                            <i class="fas fa-home"></i>
                                        </div>
                                    </div>
                                    <h6 class="font-weight-bold mb-1">
                                        <?= htmlspecialchars($cabania['cabania_nombre']) ?>
                                    </h6>
                                    <p class="text-sm text-muted mb-2">
                                        <?= htmlspecialchars($cabania['cabania_ubicacion']) ?>
                                    </p>
                                    <div class="d-flex justify-content-center align-items-center mb-2">
                                        <i class="fas fa-chart-bar text-<?= $colorClass ?> me-2"></i>
                                        <span class="font-weight-bold text-<?= $colorClass ?>">
                                            <?= $cabania['usos'] ?> uso<?= $cabania['usos'] != 1 ? 's' : '' ?>
                                        </span>
                                    </div>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-<?= $colorClass ?>" 
                                             role="progressbar" 
                                             style="width: <?= $porcentaje ?>%">
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?= round($porcentaje, 1) ?>% de uso relativo
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-home me-2"></i>No hay datos de cabañas disponibles
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php endif; ?>

    <!-- Acciones Rápidas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-tools me-2"></i>Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="ingresos/formulario" class="btn btn-primary w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>Nuevo Ingreso
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="ingresos/busqueda" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-search me-2"></i>Buscar Ingresos
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="ingresos" class="btn btn-outline-info w-100">
                                <i class="fas fa-list me-2"></i>Ver Listado
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button data-action="actualizar" class="btn btn-outline-success w-100">
                                <i class="fas fa-sync-alt me-2"></i>Actualizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
.progress-sm {
    height: 0.4rem;
}

.badge {
    font-size: 0.75em;
}

.icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.75rem;
    font-size: 1.2rem;
}

.icon-lg {
    width: 56px;
    height: 56px;
    font-size: 1.5rem;
}