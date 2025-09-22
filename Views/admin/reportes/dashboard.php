<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard de Reportes' ?> - Sistema de Cabañas</title>
    <link href="<?= asset('assets/css/main.css') ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once 'menu.php'; ?>

    <div class="dashboard-container">
        <!-- Header del Dashboard -->
        <div class="dashboard-header">
            <h1><i class="fas fa-chart-line"></i> Dashboard de Reportes</h1>
            <p>Panel de control y análisis del sistema de cabañas</p>
        </div>

        <!-- Filtros Rápidos -->
        <div class="quick-filters">
            <h3><i class="fas fa-filter"></i> Filtros Globales</h3>
            <form class="filter-group" id="globalFilters">
                <div class="filter-item">
                    <label for="periodo_global">Período:</label>
                    <select id="periodo_global" name="periodo">
                        <option value="">Todos los períodos</option>
                        <?php foreach ($filtros['periodos'] as $periodo): ?>
                            <option value="<?= $periodo['id_periodo'] ?>">
                                <?= htmlspecialchars($periodo['periodo_descripcion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-item">
                    <label for="anio_global">Año:</label>
                    <select id="anio_global" name="anio">
                        <option value="">Todos los años</option>
                        <?php foreach ($filtros['anios'] as $anio): ?>
                            <option value="<?= $anio['anio'] ?>" <?= $anio['anio'] == date('Y') ? 'selected' : '' ?>>
                                <?= $anio['anio'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-item">
                    <label for="cabana_global">Cabaña:</label>
                    <select id="cabana_global" name="cabana">
                        <option value="">Todas las cabañas</option>
                        <?php foreach ($filtros['cabanas'] as $cabana): ?>
                            <option value="<?= $cabana['id_cabania'] ?>">
                                <?= htmlspecialchars($cabana['cabania_nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-item">
                    <button type="button" class="btn btn-primary" data-action="aplicar-filtros-globales">
                        <i class="fas fa-search"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>

        <!-- Estadísticas Generales -->
        <div class="stats-grid">
            <!-- Comentarios por Puntuación -->
            <div class="stat-card">
                <h3><i class="fas fa-star"></i> Distribución de Comentarios</h3>
                <div class="chart-container">
                    <canvas id="comentariosChart"></canvas>
                </div>
            </div>

            <!-- Top Productos -->
            <div class="stat-card">
                <h3><i class="fas fa-shopping-cart"></i> Productos Más Vendidos</h3>
                <div class="chart-container">
                    <canvas id="productosChart"></canvas>
                </div>
            </div>

            <!-- Ingresos Mensuales -->
            <div class="stat-card">
                <h3><i class="fas fa-dollar-sign"></i> Ingresos Mensuales</h3>
                <div class="chart-container">
                    <canvas id="ingresosChart"></canvas>
                </div>
            </div>

            <!-- Cabañas Populares -->
            <div class="stat-card">
                <h3><i class="fas fa-home"></i> Cabañas Más Populares</h3>
                <div class="chart-container">
                    <canvas id="cabanasChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Reportes Especializados -->
        <div class="reports-grid">
            <!-- Reporte de Comentarios -->
            <div class="report-card">
                <div class="report-header">
                    <h3><i class="fas fa-comments"></i> Análisis de Comentarios</h3>
                    <p>Evaluación de satisfacción del cliente</p>
                </div>
                <div class="report-content">
                    <div class="report-stats">
                        <div class="report-stat">
                            <span class="report-stat-number" id="total-comentarios">-</span>
                            <span class="report-stat-label">Comentarios</span>
                        </div>
                        <div class="report-stat">
                            <span class="report-stat-number" id="promedio-puntuacion">-</span>
                            <span class="report-stat-label">Puntuación Promedio</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <a href="<?= url('/reportes/comentarios') ?>" class="btn btn-primary">
                            <i class="fas fa-eye"></i> Ver Reporte
                        </a>
                        <a href="<?= url('/reportes/exportar-comentarios') ?>" class="btn btn-outline">
                            <i class="fas fa-download"></i> Exportar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Reporte de Consumos -->
            <div class="report-card">
                <div class="report-header">
                    <h3><i class="fas fa-receipt"></i> Consumos por Cabaña</h3>
                    <p>Análisis financiero detallado</p>
                </div>
                <div class="report-content">
                    <div class="report-stats">
                        <div class="report-stat">
                            <span class="report-stat-number" id="total-consumos">-</span>
                            <span class="report-stat-label">Consumos</span>
                        </div>
                        <div class="report-stat">
                            <span class="report-stat-number" id="ingresos-consumos">$-</span>
                            <span class="report-stat-label">Ingresos</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <a href="<?= url('/reportes/consumos') ?>" class="btn btn-primary">
                            <i class="fas fa-eye"></i> Ver Reporte
                        </a>
                        <a href="<?= url('/reportes/exportar-consumos') ?>" class="btn btn-outline">
                            <i class="fas fa-download"></i> Exportar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Reporte de Productos -->
            <div class="report-card">
                <div class="report-header">
                    <h3><i class="fas fa-boxes"></i> Productos por Categoría</h3>
                    <p>Inventario y análisis de categorías</p>
                </div>
                <div class="report-content">
                    <div class="report-stats">
                        <div class="report-stat">
                            <span class="report-stat-number" id="total-productos">-</span>
                            <span class="report-stat-label">Productos</span>
                        </div>
                        <div class="report-stat">
                            <span class="report-stat-number" id="categorias-activas">-</span>
                            <span class="report-stat-label">Categorías</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <a href="<?= url('/reportes/productos') ?>" class="btn btn-primary">
                            <i class="fas fa-eye"></i> Ver Reporte
                        </a>
                        <a href="<?= url('/reportes/exportar-productos') ?>" class="btn btn-outline">
                            <i class="fas fa-download"></i> Exportar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Reporte de Temporadas -->
            <div class="report-card">
                <div class="report-header">
                    <h3><i class="fas fa-calendar-alt"></i> Temporadas Altas</h3>
                    <p>Análisis de períodos de mayor demanda</p>
                </div>
                <div class="report-content">
                    <div class="report-stats">
                        <div class="report-stat">
                            <span class="report-stat-number" id="temporadas-analizadas">-</span>
                            <span class="report-stat-label">Temporadas</span>
                        </div>
                        <div class="report-stat">
                            <span class="report-stat-number" id="reservas-temporada">-</span>
                            <span class="report-stat-label">Reservas</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <a href="<?= url('/reportes/temporadas') ?>" class="btn btn-primary">
                            <i class="fas fa-eye"></i> Ver Reporte
                        </a>
                        <a href="<?= url('/reportes/exportar-temporadas') ?>" class="btn btn-outline">
                            <i class="fas fa-download"></i> Exportar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Análisis Demográfico -->
            <div class="report-card">
                <div class="report-header">
                    <h3><i class="fas fa-users"></i> Análisis Demográfico</h3>
                    <p>Segmentación por grupos etarios</p>
                </div>
                <div class="report-content">
                    <div class="report-stats">
                        <div class="report-stat">
                            <span class="report-stat-number" id="grupos-etarios">-</span>
                            <span class="report-stat-label">Grupos</span>
                        </div>
                        <div class="report-stat">
                            <span class="report-stat-number" id="huespedes-analizados">-</span>
                            <span class="report-stat-label">Huéspedes</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <a href="<?= url('/reportes/demografico') ?>" class="btn btn-primary">
                            <i class="fas fa-eye"></i> Ver Reporte
                        </a>
                        <a href="<?= url('/reportes/exportar-demografico') ?>" class="btn btn-outline">
                            <i class="fas fa-download"></i> Exportar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ventas Mensuales -->
            <div class="report-card">
                <div class="report-header">
                    <h3><i class="fas fa-chart-line"></i> Ventas Mensuales</h3>
                    <p>Productos más vendidos por período</p>
                </div>
                <div class="report-content">
                    <div class="report-stats">
                        <div class="report-stat">
                            <span class="report-stat-number" id="meses-analizados">-</span>
                            <span class="report-stat-label">Meses</span>
                        </div>
                        <div class="report-stat">
                            <span class="report-stat-number" id="productos-vendidos">-</span>
                            <span class="report-stat-label">Productos</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <a href="<?= url('/reportes/ventas-mensuales') ?>" class="btn btn-primary">
                            <i class="fas fa-eye"></i> Ver Reporte
                        </a>
                        <a href="<?= url('/reportes/exportar-ventas-mensuales') ?>" class="btn btn-outline">
                            <i class="fas fa-download"></i> Exportar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="loading" id="loading">
        <i class="fas fa-spinner fa-spin"></i> Cargando datos...
    </div>


    <script>
        // Cargar datos iniciales - función centralizada en admin.js
        document.addEventListener('DOMContentLoaded', function() {
            initDashboard();
        });
    </script>
</body>
</html>