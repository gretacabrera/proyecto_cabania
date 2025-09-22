<!-- Vista: Búsqueda de Ingresos -->
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0"><?= $titulo ?></h6>
                            <p class="text-sm mb-0">Busque ingresos por diferentes criterios</p>
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
    <?php endif; ?>

    <!-- Formulario de Búsqueda -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Criterios de Búsqueda
                    </h6>
                </div>
                <div class="card-body">
                    <form method="get" action="ingresos/busqueda">
                        <div class="row">
                            <div class="col-md-6 col-lg-3 mb-3">
                                <label class="form-label">Fecha Desde</label>
                                <input type="date" name="fecha_desde" class="form-control" 
                                       value="<?= htmlspecialchars($criterios['fecha_desde'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 col-lg-3 mb-3">
                                <label class="form-label">Fecha Hasta</label>
                                <input type="date" name="fecha_hasta" class="form-control" 
                                       value="<?= htmlspecialchars($criterios['fecha_hasta'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 col-lg-3 mb-3">
                                <label class="form-label">Cabaña</label>
                                <select name="cabania" class="form-select">
                                    <option value="todas">Todas las cabañas</option>
                                    <?php if (!empty($cabanias)): ?>
                                        <?php foreach ($cabanias as $cabania): ?>
                                        <option value="<?= $cabania['id_cabania'] ?>"
                                                <?= ($criterios['cabania'] ?? '') == $cabania['id_cabania'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cabania['cabania_nombre']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3 mb-3">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="todos">Todos los estados</option>
                                    <?php if (!empty($estados)): ?>
                                        <?php foreach ($estados as $estado): ?>
                                        <option value="<?= htmlspecialchars($estado['estadoreserva_descripcion']) ?>"
                                                <?= ($criterios['estado'] ?? '') == $estado['estadoreserva_descripcion'] ? 'selected' : '' ?>>
                                            <?= ucfirst(htmlspecialchars($estado['estadoreserva_descripcion'])) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-8 col-lg-6 mb-3">
                                <label class="form-label">Huésped (Nombre o Apellido)</label>
                                <input type="text" name="huesped" class="form-control" 
                                       placeholder="Buscar por nombre o apellido del huésped"
                                       value="<?= htmlspecialchars($criterios['huesped'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 col-lg-6 mb-3 d-flex align-items-end">
                                <div class="btn-group w-100" role="group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Buscar
                                    </button>
                                    <a href="ingresos/busqueda" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Limpiar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-list me-2"></i>Resultados de Búsqueda
                        </h6>
                        <?php if (!empty($resultados)): ?>
                        <span class="badge badge-primary">
                            <?= count($resultados) ?> resultado<?= count($resultados) != 1 ? 's' : '' ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($resultados)): ?>
                    <div class="text-center py-5">
                        <?php if (empty($criterios) || empty(array_filter($criterios))): ?>
                        <!-- Estado inicial sin búsqueda -->
                        <i class="fas fa-search fa-4x text-muted mb-4"></i>
                        <h5 class="text-muted mb-3">Ingrese criterios de búsqueda</h5>
                        <p class="text-muted">
                            Complete uno o más campos del formulario superior para buscar ingresos
                        </p>
                        <?php else: ?>
                        <!-- Sin resultados después de búsqueda -->
                        <i class="fas fa-inbox fa-4x text-muted mb-4"></i>
                        <h5 class="text-muted mb-3">No se encontraron ingresos</h5>
                        <p class="text-muted mb-4">
                            No hay ingresos que coincidan con los criterios especificados.<br>
                            Intente modificar los filtros de búsqueda.
                        </p>
                        <a href="ingresos/busqueda" class="btn btn-outline-primary">
                            <i class="fas fa-search me-2"></i>Nueva Búsqueda
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Reserva
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Cabaña
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Huésped
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Fechas
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Estado
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resultados as $ingreso): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm font-weight-bold">
                                                    #<?= $ingreso['id_reserva'] ?>
                                                </h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm font-weight-bold">
                                                <?= htmlspecialchars($ingreso['cabania_nombre']) ?>
                                            </h6>
                                            <p class="text-xs text-secondary mb-0">
                                                <?= htmlspecialchars($ingreso['cabania_ubicacion']) ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">
                                                <?= htmlspecialchars($ingreso['persona_nombre']) ?> 
                                                <?= htmlspecialchars($ingreso['persona_apellido']) ?>
                                            </h6>
                                            <p class="text-xs text-secondary mb-0">
                                                Doc: <?= htmlspecialchars($ingreso['persona_documento'] ?? 'N/A') ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <div class="d-flex flex-column">
                                            <span class="text-xs font-weight-bold">
                                                <i class="fas fa-calendar-check text-success me-1"></i>
                                                <?= date('d/m/Y', strtotime($ingreso['reserva_fhinicio'])) ?>
                                            </span>
                                            <span class="text-xs text-secondary">
                                                <i class="fas fa-calendar-times text-warning me-1"></i>
                                                <?= date('d/m/Y', strtotime($ingreso['reserva_fhfin'])) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php
                                        $estadoClass = 'secondary';
                                        switch ($ingreso['estadoreserva_descripcion']) {
                                            case 'confirmada': $estadoClass = 'warning'; break;
                                            case 'en curso': $estadoClass = 'success'; break;
                                            case 'finalizada': $estadoClass = 'primary'; break;
                                            case 'cancelada': $estadoClass = 'danger'; break;
                                            case 'pendiente de pago': $estadoClass = 'info'; break;
                                        }
                                        ?>
                                        <span class="badge badge-<?= $estadoClass ?> badge-sm">
                                            <?= ucfirst($ingreso['estadoreserva_descripcion']) ?>
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="ingresos/detalle?id=<?= $ingreso['id_reserva'] ?>" 
                                           class="btn btn-outline-info btn-sm" 
                                           title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
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
    </div>

    <?php if (!empty($resultados)): ?>
    <!-- Resumen de Búsqueda -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Resumen de Resultados
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        // Contar estados
                        $estadosCount = [];
                        foreach ($resultados as $ingreso) {
                            $estado = $ingreso['estadoreserva_descripcion'];
                            $estadosCount[$estado] = ($estadosCount[$estado] ?? 0) + 1;
                        }
                        
                        $colores = [
                            'confirmada' => 'warning',
                            'en curso' => 'success', 
                            'finalizada' => 'primary',
                            'cancelada' => 'danger',
                            'pendiente de pago' => 'info'
                        ];
                        ?>
                        <?php foreach ($estadosCount as $estado => $cantidad): ?>
                        <div class="col-md-6 col-lg-3 mb-2">
                            <div class="card border-<?= $colores[$estado] ?? 'secondary' ?> h-100">
                                <div class="card-body text-center py-3">
                                    <div class="text-<?= $colores[$estado] ?? 'secondary' ?> font-weight-bold">
                                        <?= $cantidad ?>
                                    </div>
                                    <div class="text-sm text-capitalize">
                                        <?= ucfirst($estado) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>