<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Reporte de Comentarios' ?> - Sistema de Cabañas</title>
    <link href="<?= asset('assets/css/main.css') ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once 'menu.php'; ?>

    <div class="report-container">
        <!-- Header del Reporte -->
        <div class="report-header">
            <div class="report-title">
                <h1><i class="fas fa-comments"></i> <?= $title ?></h1>
            </div>
            <div class="report-actions">
                <a href="<?= $exportUrl ?>" class="btn btn-export">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </a>
                <a href="<?= $printUrl ?>" class="btn btn-white" target="_blank">
                    <i class="fas fa-print"></i> Imprimir
                </a>
                <a href="<?= $dashboardUrl ?>" class="btn btn-outline-white">
                    <i class="fas fa-chart-bar"></i> Dashboard
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-section">
            <form method="GET" action="<?= $currentUrl ?>" class="filters-form">
                <div class="filter-group">
                    <label for="cabania">Cabaña:</label>
                    <select name="cabania" id="cabania">
                        <option value="">Todas las cabañas</option>
                        <?php foreach ($cabanias as $cabania): ?>
                            <option value="<?= $cabania['id'] ?>" <?= ($filters['cabania'] == $cabania['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cabania['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="puntuacion_min">Puntuación Mínima:</label>
                    <select name="puntuacion_min" id="puntuacion_min">
                        <option value="">Cualquier puntuación</option>
                        <option value="5" <?= ($filters['puntuacion_min'] == '5') ? 'selected' : '' ?>>5 estrellas</option>
                        <option value="4" <?= ($filters['puntuacion_min'] == '4') ? 'selected' : '' ?>>4+ estrellas</option>
                        <option value="3" <?= ($filters['puntuacion_min'] == '3') ? 'selected' : '' ?>>3+ estrellas</option>
                        <option value="2" <?= ($filters['puntuacion_min'] == '2') ? 'selected' : '' ?>>2+ estrellas</option>
                        <option value="1" <?= ($filters['puntuacion_min'] == '1') ? 'selected' : '' ?>>1+ estrellas</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="fecha_desde">Desde:</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" value="<?= htmlspecialchars($filters['fecha_desde']) ?>">
                </div>

                <div class="filter-group">
                    <label for="fecha_hasta">Hasta:</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" value="<?= htmlspecialchars($filters['fecha_hasta']) ?>">
                </div>

                <div class="filter-group">
                    <label for="con_respuesta">Con Respuesta:</label>
                    <select name="con_respuesta" id="con_respuesta">
                        <option value="">Todos</option>
                        <option value="1" <?= ($filters['con_respuesta'] == '1') ? 'selected' : '' ?>>Con respuesta</option>
                        <option value="0" <?= ($filters['con_respuesta'] == '0') ? 'selected' : '' ?>>Sin respuesta</option>
                    </select>
                </div>

                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="<?= $currentUrl ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Resultados -->
        <div class="results-section">
            <!-- Export Section -->
            <?php if (!empty($comentarios)): ?>
                <div class="export-section">
                    <div class="export-info">
                        <i class="fas fa-download"></i>
                        Opciones de exportación disponibles
                    </div>
                    <div>
                        <a href="<?= $exportUrl ?>" class="btn btn-export">
                            <i class="fas fa-file-excel"></i> Exportar a Excel
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Header de Resultados -->
            <div class="results-header">
                <div class="results-count">
                    <i class="fas fa-comments"></i>
                    Mostrando <?= $pagination['showing_from'] ?? 0 ?>-<?= $pagination['showing_to'] ?? 0 ?> 
                    de <?= $pagination['total'] ?? 0 ?> comentarios
                </div>
                <div class="per-page-selector">
                    <label for="per_page">Mostrar:</label>
                    <select name="per_page" id="per_page" data-action="change-per-page">
                        <option value="10" <?= ($perPage == 10) ? 'selected' : '' ?>>10</option>
                        <option value="25" <?= ($perPage == 25) ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= ($perPage == 50) ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= ($perPage == 100) ? 'selected' : '' ?>>100</option>
                    </select>
                    <span>por página</span>
                </div>
            </div>

            <!-- Tabla de Datos -->
            <?php if (!empty($comentarios)): ?>
                <div class="table-container">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Huésped</th>
                                <th>Cabaña</th>
                                <th>Comentario</th>
                                <th>Puntuación</th>
                                <th>Respuesta</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comentarios as $comentario): ?>
                                <tr>
                                    <td>
                                        <div class="huesped-info">
                                            <div class="huesped-nombre">
                                                <?= htmlspecialchars($comentario['huesped_nombre'] ?? '') ?>
                                            </div>
                                            <div class="huesped-email">
                                                <?= htmlspecialchars($comentario['huesped_email'] ?? '') ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="cabania-info">
                                            <div class="cabania-nombre">
                                                <?= htmlspecialchars($comentario['cabania_nombre'] ?? '') ?>
                                            </div>
                                            <div class="cabania-codigo">
                                                Código: <?= htmlspecialchars($comentario['cabania_codigo'] ?? '') ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="comentario-content">
                                            <?= nl2br(htmlspecialchars($comentario['comentario'] ?? '')) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="puntuacion">
                                            <div class="stars">
                                                <?php
                                                $puntuacion = intval($comentario['puntuacion'] ?? 0);
                                                for ($i = 1; $i <= 5; $i++) {
                                                    echo $i <= $puntuacion ? '★' : '☆';
                                                }
                                                ?>
                                            </div>
                                            <span class="categoria-puntuacion <?php
                                                if ($puntuacion >= 5) echo 'categoria-excelente';
                                                elseif ($puntuacion >= 4) echo 'categoria-bueno';
                                                elseif ($puntuacion >= 3) echo 'categoria-regular';
                                                else echo 'categoria-malo';
                                            ?>">
                                                <?php
                                                if ($puntuacion >= 5) echo 'Excelente';
                                                elseif ($puntuacion >= 4) echo 'Bueno';
                                                elseif ($puntuacion >= 3) echo 'Regular';
                                                else echo 'Malo';
                                                ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($comentario['respuesta'])): ?>
                                            <div class="respuesta">
                                                <?= nl2br(htmlspecialchars($comentario['respuesta'])) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="no-respuesta">Sin respuesta</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fecha-info">
                                            <?= date('d/m/Y', strtotime($comentario['fecha_comentario'] ?? '')) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <div class="pagination-container">
                        <div class="pagination">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <a href="<?= $baseUrl ?>&<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                                <a href="<?= $baseUrl ?>&<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) ?>">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            <?php else: ?>
                                <span class="disabled"><i class="fas fa-angle-double-left"></i></span>
                                <span class="disabled"><i class="fas fa-angle-left"></i></span>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $pagination['current_page'] - 2);
                            $end_page = min($pagination['total_pages'], $pagination['current_page'] + 2);
                            
                            if ($start_page > 1) {
                                echo '<a href="' . $baseUrl . '&' . http_build_query(array_merge($_GET, ['page' => 1])) . '">1</a>';
                                if ($start_page > 2) {
                                    echo '<span class="disabled">...</span>';
                                }
                            }

                            for ($i = $start_page; $i <= $end_page; $i++) {
                                if ($i == $pagination['current_page']) {
                                    echo '<span class="current">' . $i . '</span>';
                                } else {
                                    echo '<a href="' . $baseUrl . '&' . http_build_query(array_merge($_GET, ['page' => $i])) . '">' . $i . '</a>';
                                }
                            }

                            if ($end_page < $pagination['total_pages']) {
                                if ($end_page < $pagination['total_pages'] - 1) {
                                    echo '<span class="disabled">...</span>';
                                }
                                echo '<a href="' . $baseUrl . '&' . http_build_query(array_merge($_GET, ['page' => $pagination['total_pages']])) . '">' . $pagination['total_pages'] . '</a>';
                            }
                            ?>

                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <a href="<?= $baseUrl ?>&<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])) ?>">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                                <a href="<?= $baseUrl ?>&<?= http_build_query(array_merge($_GET, ['page' => $pagination['total_pages']])) ?>">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="disabled"><i class="fas fa-angle-right"></i></span>
                                <span class="disabled"><i class="fas fa-angle-double-right"></i></span>
                            <?php endif; ?>
                        </div>

                        <div class="pagination-info">
                            Página <?= $pagination['current_page'] ?> de <?= $pagination['total_pages'] ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h3>No se encontraron comentarios</h3>
                    <p>No hay comentarios que coincidan con los filtros aplicados.</p>
                    <a href="<?= $currentUrl ?>" class="btn btn-primary">
                        <i class="fas fa-refresh"></i> Mostrar Todos
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>


</body>
</html>