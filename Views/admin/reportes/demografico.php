<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Análisis Demográfico' ?> - Sistema de Cabañas</title>
    <link href="<?= asset('estilos.css') ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/main.css" rel="stylesheet">
</head>
<body>
    <?php include_once 'menu.php'; ?>

    <div class="report-container">
        <div class="report-header">
            <div class="report-title">
                <h1><i class="fas fa-users"></i> <?= $title ?></h1>
            </div>
            <div class="report-actions">
                <a href="<?= url('/reportes') ?>" class="btn btn-white">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="<?= url('/reportes/exportar-demografico?' . http_build_query($filtros)) ?>" class="btn btn-outline-white">
                    <i class="fas fa-download"></i> Exportar
                </a>
            </div>
        </div>

        <div class="filters-section">
            <h3><i class="fas fa-filter"></i> Filtros de Análisis</h3>
            <form class="filters-form" method="GET" action="<?= $currentUrl ?>">
                <div class="filter-group">
                    <label for="periodo">Período:</label>
                    <select id="periodo" name="periodo">
                        <option value="">Todos los períodos</option>
                        <?php foreach ($filtrosData['periodos'] as $periodo): ?>
                            <option value="<?= $periodo['id_periodo'] ?>" <?= $filtros['periodo'] == $periodo['id_periodo'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($periodo['periodo_descripcion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-chart-bar"></i> Analizar
                    </button>
                </div>
                <div class="filter-group">
                    <a href="<?= $currentUrl ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>

        <div class="results-section">
            <?php if (!empty($resultado['data'])): ?>
                <div class="export-section">
                    <div class="export-info">
                        <i class="fas fa-info-circle"></i>
                        Análisis demográfico de <?= $resultado['total'] ?> segmentos de población
                    </div>
                    <a href="<?= url('/reportes/exportar-demografico?' . http_build_query($filtros)) ?>" class="btn btn-export">
                        <i class="fas fa-file-excel"></i> Exportar Análisis
                    </a>
                </div>

                <div class="results-header">
                    <div class="results-info">
                        Mostrando <?= count($resultado['data']) ?> de <?= $resultado['total'] ?> segmentos
                    </div>
                    <div class="pagination-info">
                        Página <?= $resultado['page'] ?> de <?= $resultado['totalPages'] ?>
                    </div>
                </div>

                <div class="table-container">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Período</th>
                                <th>Grupo Etario</th>
                                <th>Reservas</th>
                                <th>Huéspedes Únicos</th>
                                <th>Edades</th>
                                <th>Gasto Promedio</th>
                                <th>Gasto Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultado['data'] as $grupo): ?>
                                <?php 
                                $claseGrupo = '';
                                if (strpos($grupo['grupo_etario'], 'Jóvenes') !== false) $claseGrupo = 'jovenes';
                                elseif (strpos($grupo['grupo_etario'], 'Adultos (28') !== false) $claseGrupo = 'adultos';
                                elseif (strpos($grupo['grupo_etario'], 'Adultos mayores') !== false) $claseGrupo = 'adultos-mayores';
                                elseif (strpos($grupo['grupo_etario'], 'Ancianos') !== false) $claseGrupo = 'ancianos';
                                else $claseGrupo = 'menores';
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($grupo['periodo_descripcion']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="grupo-etario <?= $claseGrupo ?>">
                                            <?= htmlspecialchars($grupo['grupo_etario']) ?>
                                        </span>
                                    </td>
                                    <td class="estadistica-numerica">
                                        <div class="numero-destacado"><?= $grupo['cantidad_reservas'] ?></div>
                                        <div class="numero-secundario">reservas</div>
                                    </td>
                                    <td class="estadistica-numerica">
                                        <div class="numero-destacado"><?= $grupo['huespedes_unicos'] ?></div>
                                        <div class="numero-secundario">personas</div>
                                    </td>
                                    <td class="edad-info">
                                        <div class="edad-promedio"><?= number_format($grupo['edad_promedio'], 1) ?> años</div>
                                        <div class="rango-edad">
                                            <?= $grupo['edad_minima'] ?>-<?= $grupo['edad_maxima'] ?> años
                                        </div>
                                    </td>
                                    <td class="gasto-info">
                                        <div class="gasto-promedio">
                                            $<?= number_format($grupo['gasto_promedio'], 2) ?>
                                        </div>
                                        <div class="numero-secundario">por reserva</div>
                                    </td>
                                    <td class="gasto-info">
                                        <div class="gasto-total">
                                            $<?= number_format($grupo['gasto_total'], 2) ?>
                                        </div>
                                        <div class="numero-secundario">total</div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination-container">
                    <div class="per-page-selector">
                        <label for="per_page">Mostrar:</label>
                        <select id="per_page" name="per_page">
                            <option value="15" <?= ($resultado['perPage'] == 15) ? 'selected' : '' ?>>15</option>
                            <option value="30" <?= ($resultado['perPage'] == 30) ? 'selected' : '' ?>>30</option>
                            <option value="50" <?= ($resultado['perPage'] == 50) ? 'selected' : '' ?>>50</option>
                        </select>
                        <span>por página</span>
                    </div>

                    <div class="pagination">
                        <?php if ($resultado['page'] > 1): ?>
                            <a href="<?= $currentUrl ?>?<?= http_build_query(array_merge($filtros, ['page' => 1, 'per_page' => $resultado['perPage']])) ?>">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                            <a href="<?= $currentUrl ?>?<?= http_build_query(array_merge($filtros, ['page' => $resultado['page'] - 1, 'per_page' => $resultado['perPage']])) ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        <?php else: ?>
                            <span class="disabled"><i class="fas fa-angle-double-left"></i></span>
                            <span class="disabled"><i class="fas fa-angle-left"></i></span>
                        <?php endif; ?>

                        <?php for ($i = max(1, $resultado['page'] - 2); $i <= min($resultado['totalPages'], $resultado['page'] + 2); $i++): ?>
                            <?php if ($i == $resultado['page']): ?>
                                <span class="current"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= $currentUrl ?>?<?= http_build_query(array_merge($filtros, ['page' => $i, 'per_page' => $resultado['perPage']])) ?>">
                                    <?= $i ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($resultado['page'] < $resultado['totalPages']): ?>
                            <a href="<?= $currentUrl ?>?<?= http_build_query(array_merge($filtros, ['page' => $resultado['page'] + 1, 'per_page' => $resultado['perPage']])) ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                            <a href="<?= $currentUrl ?>?<?= http_build_query(array_merge($filtros, ['page' => $resultado['totalPages'], 'per_page' => $resultado['perPage']])) ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="disabled"><i class="fas fa-angle-right"></i></span>
                            <span class="disabled"><i class="fas fa-angle-double-right"></i></span>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>No hay datos demográficos</h3>
                    <p>No se encontraron datos demográficos para los filtros seleccionados.</p>
                    <a href="<?= $currentUrl ?>" class="btn btn-primary">
                        <i class="fas fa-refresh"></i> Ver Todos
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>