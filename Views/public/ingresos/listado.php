<!-- Vista: Listado de Ingresos -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0"><?= $titulo ?></h6>
                            <p class="text-sm mb-0">Gestión de ingresos y check-in de huéspedes</p>
                        </div>
                        <div class="btn-group" role="group">
                            <a href="ingresos/formulario" class="btn btn-primary btn-sm">
                                <i class="fas fa-sign-in-alt me-1"></i>Registrar Ingreso
                            </a>
                            <a href="ingresos/busqueda" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-search me-1"></i>Buscar
                            </a>
                            <a href="ingresos/stats" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-chart-bar me-1"></i>Estadísticas
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($error)): ?>
                <div class="alert alert-danger mx-4 mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-<?= $_SESSION['tipo_mensaje'] === 'error' ? 'danger' : 'success' ?> mx-4 mt-3">
                    <i class="fas fa-<?= $_SESSION['tipo_mensaje'] === 'error' ? 'exclamation-triangle' : 'check-circle' ?> me-2"></i>
                    <?= htmlspecialchars($_SESSION['mensaje']) ?>
                </div>
                <?php 
                unset($_SESSION['mensaje']);
                unset($_SESSION['tipo_mensaje']);
                endif; 
                ?>
                
                <div class="card-body px-0 pt-0 pb-2">
                    <?php if (empty($reservas)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-sign-in-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted"><?= $mensaje_sin_datos ?></h5>
                        <p class="text-sm text-muted mb-4">
                            Las reservas aparecerán aquí cuando estén confirmadas y en período de ingreso
                        </p>
                        <a href="ingresos/busqueda" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Buscar Historial de Ingresos
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
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
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservas as $reserva): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">Reserva #<?= $reserva['id_reserva'] ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm font-weight-bold">
                                                <?= htmlspecialchars($reserva['cabania_nombre']) ?>
                                            </h6>
                                            <p class="text-xs text-secondary mb-0">
                                                <?= htmlspecialchars($reserva['cabania_ubicacion']) ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">
                                                <?= htmlspecialchars($reserva['persona_nombre']) ?> 
                                                <?= htmlspecialchars($reserva['persona_apellido']) ?>
                                            </h6>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <div class="d-flex flex-column">
                                            <span class="text-xs font-weight-bold">
                                                <i class="fas fa-calendar-check text-success me-1"></i>
                                                <?= date('d/m/Y H:i', strtotime($reserva['reserva_fhinicio'])) ?>
                                            </span>
                                            <span class="text-xs text-secondary">
                                                <i class="fas fa-calendar-times text-warning me-1"></i>
                                                <?= date('d/m/Y H:i', strtotime($reserva['reserva_fhfin'])) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="ingresos/detalle?id=<?= $reserva['id_reserva'] ?>" 
                                               class="btn btn-outline-info btn-sm" 
                                               title="Ver Detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form method="post" action="ingresos/alta" style="display: inline;">
                                                <input type="hidden" name="id_reserva" value="<?= $reserva['id_reserva'] ?>">
                                                <button type="submit" class="btn btn-success btn-sm" 
                                                        title="Registrar Ingreso"
                                                        data-action="confirmar-ingreso" data-reserva-id="<?= $reserva['id_reserva'] ?>">
                                                    <i class="fas fa-sign-in-alt"></i>
                                                </button>
                                            </form>
                                        </div>
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

    <!-- Card de Ayuda -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Información sobre Ingresos
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Reservas Confirmadas:</strong> Solo aparecen reservas en estado confirmado
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-warning me-2"></i>
                            <strong>Período Activo:</strong> Dentro del rango de fechas de la reserva
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-home text-info me-2"></i>
                            <strong>Efecto del Ingreso:</strong> Cambia el estado de la reserva a "en curso" y la cabaña a "ocupada"
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="ingresos/formulario" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Registrar Nuevo Ingreso
                        </a>
                        <a href="ingresos/busqueda" class="btn btn-outline-secondary">
                            <i class="fas fa-search me-2"></i>Buscar en Historial
                        </a>
                        <a href="ingresos/stats" class="btn btn-outline-info">
                            <i class="fas fa-chart-bar me-2"></i>Ver Estadísticas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>