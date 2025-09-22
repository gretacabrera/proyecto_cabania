<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1                        <select id="per_page" name="per_page">
                            <option value="15" <?= ($resultado['perPage'] == 15) ? 'selected' : '' ?>>15</option>
                            <option value="30" <?= ($resultado['perPage'] == 30) ? 'selected' : '' ?>>30</option>
                            <option value="50" <?= ($resultado['perPage'] == 50) ? 'selected' : '' ?>>50</option>
                        </select>
    <title><?= $title ?? 'Reporte de Consumos' ?> - Sistema de Cabañas</title>
    <link href="<?= asset('estilos.css') ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/main.css" rel="stylesheet">
</head>
<body>
    <?php include_once 'menu.php'; ?>

    <div class="report-container">
        <div class="report-header">
            <div class="report-title">
                <h1><i class="fas fa-receipt"></i> <?= $title ?></h1>
            </div>
            <div class="report-actions">
                <a href="<?= url('/reportes') ?>" class="btn btn-white">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="<?= url('/reportes/exportar-consumos?' . http_build_query($filtros)) ?>" class="btn btn-outline-white">
                    <i class="fas fa-download"></i> Exportar
                </a>
            </div>
        </div>

        <div class="filters-section">
            <h3><i class="fas fa-filter"></i> Filtros de Búsqueda</h3>
            <form class="filters-form" method="GET" action="<?= $currentUrl ?>">
                <div class="filter-group">
                    <label for="cabania">Cabaña:</label>
                    <select id="cabania" name="cabania">
                        <option value="">Todas las cabañas</option>
                        <?php foreach ($filtrosData['cabanas'] as $cabana): ?>
                            <option value="<?= $cabana['id_cabania'] ?>" <?= $filtros['cabania'] == $cabana['id_cabania'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cabana['cabania_nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="fecha_desde">Fecha Desde:</label>
                    <input type="date" id="fecha_desde" name="fecha_desde" value="<?= htmlspecialchars($filtros['fecha_desde']) ?>">
                </div>
                <div class="filter-group">
                    <label for="fecha_hasta">Fecha Hasta:</label>
                    <input type="date" id="fecha_hasta" name="fecha_hasta" value="<?= htmlspecialchars($filtros['fecha_hasta']) ?>">
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
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
                        Se encontraron <?= $resultado['total'] ?> registros con importes en pesos
                    </div>
                    <a href="<?= url('/reportes/exportar-consumos?' . http_build_query($filtros)) ?>" class="btn btn-export">
                        <i class="fas fa-file-excel"></i> Exportar a Excel
                    </a>
                </div>

                <div class="results-header">
                    <div class="results-info">
                        Mostrando <?= count($resultado['data']) ?> de <?= $resultado['total'] ?> resultados
                    </div>
                    <div class="pagination-info">
                        Página <?= $resultado['page'] ?> de <?= $resultado['totalPages'] ?>
                    </div>
                </div>

                <div class="table-container">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Cabaña</th>
                                <th>Reservas</th>
                                <th>Consumos</th>
                                <th>Importe Total</th>
                                <th>Promedio/Reserva</th>
                                <th>Período</th>
                                <th>Productos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultado['data'] as $consumo): ?>
                                <tr>
                                    <td>
                                        <div class="cabania-info">
                                            <div class="cabania-nombre"><?= htmlspecialchars($consumo['cabania_nombre']) ?></div>
                                            <div class="cabania-codigo"><?= htmlspecialchars($consumo['cabania_codigo']) ?></div>
                                        </div>
                                    </td>
                                    <td class="estadistica">
                                        <div class="numero-grande"><?= $consumo['total_reservas'] ?></div>
                                    </td>
                                    <td class="estadistica">
                                        <div class="numero-grande"><?= $consumo['total_consumos'] ?></div>
                                    </td>
                                    <td>
                                        <div class="importe-destacado">$<?= number_format($consumo['total_importe_pesos'], 2) ?></div>
                                    </td>
                                    <td>
                                        $<?= number_format($consumo['promedio_por_reserva'], 2) ?>
                                    </td>
                                    <td>
                                        <div class="fecha-info">
                                            <div><strong>Desde:</strong> <?= date('d/m/Y', strtotime($consumo['primera_fecha'])) ?></div>
                                            <div><strong>Hasta:</strong> <?= date('d/m/Y', strtotime($consumo['ultima_fecha'])) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="productos-info">
                                            <?= htmlspecialchars(substr($consumo['productos_consumidos'], 0, 100)) ?>
                                            <?php if (strlen($consumo['productos_consumidos']) > 100): ?>
                                                ...
                                            <?php endif; ?>
                                        </div>
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
                            <option value="10" <?= ($resultado['perPage'] == 10) ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= ($resultado['perPage'] == 25) ? 'selected' : '' ?>>25</option>
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
                    <i class="fas fa-receipt"></i>
                    <h3>No se encontraron consumos</h3>
                    <p>No hay consumos que coincidan con los filtros aplicados.</p>
                    <a href="<?= $currentUrl ?>" class="btn btn-primary">
                        <i class="fas fa-refresh"></i> Mostrar Todos
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>