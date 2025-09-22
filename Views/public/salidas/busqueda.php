<?php
$pageTitle = $title ?? 'Búsqueda de Salidas';
include __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid px-4">
    <!-- Encabezado -->
    <div class="row">
        <div class="col-12">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-search text-primary"></i>
                    Búsqueda Avanzada de Salidas
                </h1>
                <div class="btn-group" role="group">
                    <a href="/salidas" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Volver al Listado
                    </a>
                    <a href="/salidas/stats" class="btn btn-outline-info">
                        <i class="fas fa-chart-bar"></i>
                        Estadísticas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes de estado -->
    <?php if (isset($success_message) && $success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message) && $error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Formulario de búsqueda -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter"></i>
                        Filtros de Búsqueda
                    </h6>
                </div>
                <div class="card-body">
                    <form method="get" action="/salidas/busqueda" id="formBusqueda">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="estado" class="form-label">
                                    <i class="fas fa-info-circle"></i>
                                    Estado de la Reserva
                                </label>
                                <select name="estado" id="estado" class="form-select">
                                    <option value="">Todos los estados</option>
                                    <option value="finalizada" <?= (($filtros['estado'] ?? '') === 'finalizada') ? 'selected' : '' ?>>
                                        Finalizada
                                    </option>
                                    <option value="pendiente de pago" <?= (($filtros['estado'] ?? '') === 'pendiente de pago') ? 'selected' : '' ?>>
                                        Pendiente de pago
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="fecha_desde" class="form-label">
                                    <i class="fas fa-calendar"></i>
                                    Fecha Desde
                                </label>
                                <input type="date" name="fecha_desde" id="fecha_desde" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($filtros['fecha_desde'] ?? '') ?>">
                                <small class="form-text text-muted">Check-out desde esta fecha</small>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="fecha_hasta" class="form-label">
                                    <i class="fas fa-calendar"></i>
                                    Fecha Hasta
                                </label>
                                <input type="date" name="fecha_hasta" id="fecha_hasta" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($filtros['fecha_hasta'] ?? '') ?>">
                                <small class="form-text text-muted">Check-out hasta esta fecha</small>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="cabania" class="form-label">
                                    <i class="fas fa-home"></i>
                                    Cabaña
                                </label>
                                <select name="cabania" id="cabania" class="form-select">
                                    <option value="">Todas las cabañas</option>
                                    <?php if (isset($cabanias) && !empty($cabanias)): ?>
                                        <?php foreach ($cabanias as $cabania): ?>
                                            <option value="<?= htmlspecialchars($cabania['id_cabania']) ?>" 
                                                    <?= (($filtros['cabania'] ?? '') == $cabania['id_cabania']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cabania['cabania_nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="huesped" class="form-label">
                                    <i class="fas fa-user"></i>
                                    Buscar por Huésped
                                </label>
                                <input type="text" name="huesped" id="huesped" 
                                       class="form-control" 
                                       placeholder="Nombre o apellido del huésped"
                                       value="<?= htmlspecialchars($filtros['huesped'] ?? '') ?>">
                                <small class="form-text text-muted">Busca por nombre o apellido</small>
                            </div>

                            <div class="col-md-6 d-flex align-items-end mb-3">
                                <div class="btn-group w-100" role="group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                        Buscar Salidas
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" data-action="limpiar-filtros">>
                                        <i class="fas fa-eraser"></i>
                                        Limpiar
                                    </button>
                                    <button type="button" class="btn btn-outline-info" data-action="buscar-hoy">>
                                        <i class="fas fa-calendar-day"></i>
                                        Hoy
                                    </button>
                                    <button type="button" class="btn btn-outline-success" data-action="buscar-semana">>
                                        <i class="fas fa-calendar-week"></i>
                                        Esta Semana
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados de la búsqueda -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i>
                        Resultados de la Búsqueda
                        <?php if (isset($salidas) && !empty($salidas)): ?>
                            <span class="badge badge-primary ml-2"><?= count($salidas) ?> encontrados</span>
                        <?php endif; ?>
                    </h6>
                    <?php if (isset($salidas) && !empty($salidas)): ?>
                        <button data-action="exportar-resultados" class="btn btn-outline-success btn-sm">>
                            <i class="fas fa-file-export"></i>
                            Exportar
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (isset($realizar_busqueda) && $realizar_busqueda): ?>
                        <?php if (isset($salidas) && !empty($salidas)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="tablaSalidas">
                                    <thead class="thead-light">
                                        <tr>
                                            <th><i class="fas fa-hashtag"></i> ID</th>
                                            <th><i class="fas fa-user"></i> Huésped</th>
                                            <th><i class="fas fa-home"></i> Cabaña</th>
                                            <th><i class="fas fa-calendar"></i> Check-in</th>
                                            <th><i class="fas fa-calendar-check"></i> Check-out</th>
                                            <th><i class="fas fa-clock"></i> Días</th>
                                            <th><i class="fas fa-info-circle"></i> Estado</th>
                                            <th><i class="fas fa-tools"></i> Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($salidas as $salida): ?>
                                            <tr>
                                                <td class="font-weight-bold text-primary">
                                                    #<?= htmlspecialchars($salida['id_reserva']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($salida['persona_nombre'] . ' ' . $salida['persona_apellido']) ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary">
                                                        <?= htmlspecialchars($salida['cabania_nombre']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($salida['fecha_inicio_formateada']) ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <small class="text-danger">
                                                        <strong><?= htmlspecialchars($salida['fecha_fin_formateada']) ?></strong>
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-light">
                                                        <?= intval($salida['dias_estadia']) ?> días
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $estadoClass = match($salida['estadoreserva_descripcion']) {
                                                        'finalizada' => 'badge-success',
                                                        'pendiente de pago' => 'badge-warning',
                                                        default => 'badge-secondary'
                                                    };
                                                    ?>
                                                    <span class="badge <?= $estadoClass ?>">
                                                        <?= ucfirst(htmlspecialchars($salida['estadoreserva_descripcion'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="/salidas/<?= htmlspecialchars($salida['id_reserva']) ?>/detalle" 
                                                           class="btn btn-outline-info btn-sm" title="Ver Detalle">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($salida['estadoreserva_descripcion'] === 'pendiente de pago'): ?>
                                                            <a href="/pagos/create?reserva=<?= htmlspecialchars($salida['id_reserva']) ?>" 
                                                               class="btn btn-outline-success btn-sm" title="Registrar Pago">
                                                                <i class="fas fa-money-bill"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Resumen de resultados -->
                            <div class="mt-3 p-3 bg-light rounded">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Total encontrados:</strong> <?= count($salidas) ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Finalizadas:</strong> 
                                        <?= count(array_filter($salidas, fn($s) => $s['estadoreserva_descripcion'] === 'finalizada')) ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Pendientes:</strong> 
                                        <?= count(array_filter($salidas, fn($s) => $s['estadoreserva_descripcion'] === 'pendiente de pago')) ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Período:</strong> 
                                        <?php if (!empty($filtros['fecha_desde']) || !empty($filtros['fecha_hasta'])): ?>
                                            <?= !empty($filtros['fecha_desde']) ? date('d/m/Y', strtotime($filtros['fecha_desde'])) : '∞' ?>
                                            -
                                            <?= !empty($filtros['fecha_hasta']) ? date('d/m/Y', strtotime($filtros['fecha_hasta'])) : 'Actual' ?>
                                        <?php else: ?>
                                            Todas las fechas
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- No se encontraron resultados -->
                            <div class="text-center py-4">
                                <i class="fas fa-search fa-3x text-gray-300 mb-3"></i>
                                <h5 class="text-gray-600">No se encontraron salidas</h5>
                                <p class="text-muted">
                                    No hay salidas que coincidan con los criterios de búsqueda especificados.
                                </p>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-outline-primary" data-action="limpiar-filtros">>
                                        <i class="fas fa-eraser"></i>
                                        Limpiar Filtros
                                    </button>
                                    <a href="/salidas" class="btn btn-primary ml-2">
                                        <i class="fas fa-list"></i>
                                        Ver Todas las Salidas
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- Estado inicial - sin búsqueda -->
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-gray-300 mb-4"></i>
                            <h5 class="text-gray-600">Búsqueda Avanzada de Salidas</h5>
                            <p class="text-muted">
                                Utilice los filtros superiores para buscar salidas específicas.<br>
                                Puede filtrar por fecha, cabaña, huésped o estado de la reserva.
                            </p>
                            
                            <div class="row justify-content-center mt-4">
                                <div class="col-md-8">
                                    <div class="alert alert-info">
                                        <h6><strong>Consejos para la búsqueda:</strong></h6>
                                        <ul class="list-unstyled mb-0">
                                            <li><i class="fas fa-lightbulb text-warning"></i> Use rangos de fechas para períodos específicos</li>
                                            <li><i class="fas fa-lightbulb text-warning"></i> Busque por nombre parcial del huésped</li>
                                            <li><i class="fas fa-lightbulb text-warning"></i> Filtre por cabaña para ver su historial</li>
                                            <li><i class="fas fa-lightbulb text-warning"></i> Use los botones rápidos para períodos comunes</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de acciones rápidas -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-bolt"></i>
                        Búsquedas Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-2">
                            <button data-action="buscar-pendientes" class="btn btn-warning btn-block">>
                                <i class="fas fa-exclamation-triangle"></i>
                                Salidas Pendientes de Pago
                            </button>
                        </div>
                        <div class="col-12 mb-2">
                            <button data-action="buscar-finalizadas" class="btn btn-success btn-block">>
                                <i class="fas fa-check-circle"></i>
                                Salidas Finalizadas Este Mes
                            </button>
                        </div>
                        <div class="col-12 mb-2">
                            <button data-action="buscar-ultima-semana" class="btn btn-info btn-block">>
                                <i class="fas fa-calendar-week"></i>
                                Salidas de la Última Semana
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle"></i>
                        Ayuda
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold">Filtros disponibles:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-info-circle text-primary"></i> <strong>Estado:</strong> Finalizada o Pendiente de pago</li>
                        <li><i class="fas fa-calendar text-primary"></i> <strong>Fechas:</strong> Rango de fechas de check-out</li>
                        <li><i class="fas fa-home text-primary"></i> <strong>Cabaña:</strong> Filtrar por cabaña específica</li>
                        <li><i class="fas fa-user text-primary"></i> <strong>Huésped:</strong> Buscar por nombre o apellido</li>
                    </ul>
                    
                    <small class="text-muted">
                        <i class="fas fa-lightbulb"></i>
                        <strong>Tip:</strong> Combine varios filtros para búsquedas más precisas.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('assets/js/main.js') ?>"></script>
<script>
// Funciones centralizadas se ejecutan automáticamente por public.js
document.addEventListener('DOMContentLoaded', function() {
    initSalidas();
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>