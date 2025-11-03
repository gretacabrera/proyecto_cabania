<?php
$title = 'Estadísticas de Condiciones de Salud';
$currentModule = 'condiciones_salud';

require_once 'app/Views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-chart-bar"></i> Estadísticas de Condiciones de Salud</h2>
                <div>
                    <a href="/condiciones_salud" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Ver Listado
                    </a>
                    <a href="/condiciones_salud/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Condición
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
                            <h6 class="card-title mb-0">Total Condiciones</h6>
                            <h3 class="mb-0"><?= $stats['total_condiciones'] ?></h3>
                        </div>
                        <div class="text-primary-50">
                            <i class="fas fa-heart-pulse fa-2x"></i>
                        </div>
                    </div>
                    <small class="text-primary-50">Condiciones registradas en el sistema</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Condiciones Activas</h6>
                            <h3 class="mb-0"><?= $stats['condiciones_activas'] ?></h3>
                        </div>
                        <div class="text-success-50">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                    <small class="text-success-50">Disponibles para asignar</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Condiciones Críticas</h6>
                            <h3 class="mb-0"><?= count($condiciones_criticas) ?></h3>
                        </div>
                        <div class="text-warning-50">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                    <small class="text-warning-50">Requieren atención especial</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Más Utilizada</h6>
                            <h3 class="mb-0">
                                <?= !empty($stats['condiciones_mas_utilizadas']) ? $stats['condiciones_mas_utilizadas'][0]['uso_count'] : '0' ?>
                            </h3>
                        </div>
                        <div class="text-info-50">
                            <i class="fas fa-star fa-2x"></i>
                        </div>
                    </div>
                    <small class="text-info-50">
                        <?= !empty($stats['condiciones_mas_utilizadas']) ? 
                            substr($stats['condiciones_mas_utilizadas'][0]['condicionsalud_descripcion'], 0, 25) . '...' : 
                            'Ninguna registrada' ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <!-- Gráfico de condiciones por estado -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie"></i> Estado de Condiciones
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartEstadoCondiciones" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Gráfico de distribución por primera letra -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Distribución Alfabética
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartDistribucionAlfabetica" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Condiciones más utilizadas -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Condiciones Más Utilizadas
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="chartCondicionesMasUtilizadas" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Condiciones críticas -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle text-warning"></i> Condiciones Críticas
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($condiciones_criticas)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($condiciones_criticas, 0, 8) as $condicion): ?>
                                <div class="list-group-item px-0 py-2 border-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <i class="fas fa-exclamation-triangle text-warning mr-1"></i>
                                                <?= htmlspecialchars(substr($condicion['condicionsalud_descripcion'], 0, 30)) ?>
                                                <?= strlen($condicion['condicionsalud_descripcion']) > 30 ? '...' : '' ?>
                                            </h6>
                                        </div>
                                        <span class="badge badge-<?= $condicion['condicionsalud_estado'] == 1 ? 'success' : 'secondary' ?> badge-sm">
                                            <?= $condicion['condicionsalud_estado'] == 1 ? 'Activa' : 'Inactiva' ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (count($condiciones_criticas) > 8): ?>
                            <div class="text-center mt-2">
                                <small class="text-muted">
                                    Y <?= count($condiciones_criticas) - 8 ?> condiciones críticas más...
                                </small>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p class="text-muted mb-0">No hay condiciones críticas registradas</p>
                        </div>
                    <?php endif; ?>
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
                        <i class="fas fa-table"></i> Resumen Detallado de Condiciones
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($condiciones_detalle)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Tipo</th>
                                        <th>Uso</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($condiciones_detalle as $condicion): ?>
                                        <?php
                                        // Determinar si es crítica
                                        $descripcionLower = strtolower($condicion['condicionsalud_descripcion']);
                                        $esCritica = false;
                                        $palabrasCriticas = ['alergia', 'alergico', 'diabetes', 'diabetico', 'cardiaco', 'corazon', 
                                                           'epilepsia', 'epileptico', 'asma', 'asmatico', 'hipertension', 'presion'];
                                        
                                        foreach ($palabrasCriticas as $palabra) {
                                            if (strpos($descripcionLower, $palabra) !== false) {
                                                $esCritica = true;
                                                break;
                                            }
                                        }

                                        // Buscar uso de la condición
                                        $usoCount = 0;
                                        foreach ($stats['condiciones_mas_utilizadas'] as $utilizada) {
                                            if ($utilizada['id_condicionsalud'] == $condicion['id_condicionsalud']) {
                                                $usoCount = $utilizada['uso_count'];
                                                break;
                                            }
                                        }
                                        ?>
                                        <tr <?= $esCritica ? 'class="table-warning"' : '' ?>>
                                            <td>
                                                <strong><?= $condicion['id_condicionsalud'] ?></strong>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($esCritica): ?>
                                                        <i class="fas fa-exclamation-triangle text-warning mr-2" title="Condición Crítica"></i>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars(substr($condicion['condicionsalud_descripcion'], 0, 50)) ?></strong>
                                                        <?= strlen($condicion['condicionsalud_descripcion']) > 50 ? '...' : '' ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($condicion['condicionsalud_estado'] == 1): ?>
                                                    <span class="badge badge-success">Activa</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Inactiva</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($esCritica): ?>
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-exclamation-triangle"></i> Crítica
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-info">
                                                        <i class="fas fa-info-circle"></i> Estándar
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $usoCount > 0 ? 'primary' : 'light' ?>">
                                                    <?= $usoCount ?> huéspedes
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-heart-pulse fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay condiciones registradas en el sistema.</p>
                            <a href="/condiciones_salud/create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Crear Primera Condición
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preparar datos para los gráficos
    const estadoCondicionesData = {
        activas: <?= $stats['condiciones_activas'] ?>,
        inactivas: <?= $stats['condiciones_inactivas'] ?>
    };
    
    const distribucionAlfabeticaData = <?= json_encode($agrupadas_por_letra) ?>;
    const condicionesMasUtilizadasData = <?= json_encode(array_slice($stats['condiciones_mas_utilizadas'], 0, 10)) ?>;
    
    // Inicializar gráficos usando la función centralizada
    initCondicionesSaludStats(estadoCondicionesData, distribucionAlfabeticaData, condicionesMasUtilizadasData);
});
</script>

<?php require_once 'app/Views/layouts/footer.php'; ?>