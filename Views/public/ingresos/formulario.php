<!-- Vista: Formulario de Ingreso -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0"><?= $titulo ?></h6>
                            <p class="text-sm mb-0">Confirme el ingreso al complejo de huéspedes con reservas activas</p>
                        </div>
                        <a href="ingresos" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Volver al Listado
                        </a>
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
                
                <div class="card-body">
                    <?php if (empty($reservas)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted mb-3"><?= $mensaje_sin_datos ?></h4>
                        <p class="text-muted mb-4">
                            Para registrar un ingreso, debe tener una reserva confirmada<br>
                            que esté dentro del período de fechas programado.
                        </p>
                        
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-lightbulb me-2"></i>¿Qué puedo hacer?
                                    </h6>
                                    <hr>
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-search me-2 text-primary"></i>
                                            <a href="ingresos/busqueda" class="text-decoration-none">
                                                Buscar en el historial de ingresos anteriores
                                            </a>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-calendar-plus me-2 text-success"></i>
                                            Verificar que su reserva esté confirmada
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-clock me-2 text-warning"></i>
                                            Esperar al período de ingreso de su reserva
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="ingresos/busqueda" class="btn btn-primary me-2">
                                <i class="fas fa-search me-2"></i>Buscar Ingresos
                            </a>
                            <a href="ingresos/stats" class="btn btn-outline-info">
                                <i class="fas fa-chart-bar me-2"></i>Ver Estadísticas
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="row">
                        <div class="col-md-8 mx-auto">
                            <div class="alert alert-success">
                                <h6 class="alert-heading">
                                    <i class="fas fa-check-circle me-2"></i>Reserva Lista para Ingreso
                                </h6>
                                <hr>
                                <p class="mb-0">
                                    Se encontró una reserva confirmada lista para el ingreso al complejo.
                                    Revise los detalles y confirme el registro.
                                </p>
                            </div>
                            
                            <?php foreach ($reservas as $reserva): ?>
                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-gradient-primary">
                                    <h6 class="text-white mb-0">
                                        <i class="fas fa-bookmark me-2"></i>Reserva #<?= $reserva['id_reserva'] ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-primary mb-3">
                                                <i class="fas fa-home me-2"></i>Información de la Cabaña
                                            </h6>
                                            <div class="info-item mb-3">
                                                <strong>Cabaña:</strong> <?= htmlspecialchars($reserva['cabania_nombre']) ?>
                                            </div>
                                            <div class="info-item mb-3">
                                                <strong>Ubicación:</strong> <?= htmlspecialchars($reserva['cabania_ubicacion']) ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-success mb-3">
                                                <i class="fas fa-user me-2"></i>Información del Huésped
                                            </h6>
                                            <div class="info-item mb-3">
                                                <strong>Huésped:</strong> 
                                                <?= htmlspecialchars($reserva['persona_nombre']) ?> 
                                                <?= htmlspecialchars($reserva['persona_apellido']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <h6 class="text-info mb-3">
                                                <i class="fas fa-calendar me-2"></i>Período de Estadía
                                            </h6>
                                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                                <div class="text-center">
                                                    <div class="text-success font-weight-bold">INICIO</div>
                                                    <div class="text-sm">
                                                        <i class="fas fa-calendar-check me-1"></i>
                                                        <?= date('d/m/Y', strtotime($reserva['reserva_fhinicio'])) ?>
                                                    </div>
                                                    <div class="text-sm text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?= date('H:i', strtotime($reserva['reserva_fhinicio'])) ?>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <i class="fas fa-arrow-right text-muted fa-2x"></i>
                                                </div>
                                                <div class="text-center">
                                                    <div class="text-warning font-weight-bold">FIN</div>
                                                    <div class="text-sm">
                                                        <i class="fas fa-calendar-times me-1"></i>
                                                        <?= date('d/m/Y', strtotime($reserva['reserva_fhfin'])) ?>
                                                    </div>
                                                    <div class="text-sm text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?= date('H:i', strtotime($reserva['reserva_fhfin'])) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 text-center">
                                        <form method="post" action="ingresos/alta" class="d-inline">
                                            <input type="hidden" name="id_reserva" value="<?= $reserva['id_reserva'] ?>">
                                            <button type="submit" class="btn btn-success btn-lg px-5" 
                                                    data-action="confirmar-ingreso-complejo" data-reserva-id="<?= $reserva['id_reserva'] ?>">
                                                <i class="fas fa-sign-in-alt me-2"></i>
                                                Confirmar Ingreso
                                            </button>
                                        </form>
                                        <a href="ingresos/detalle?id=<?= $reserva['id_reserva'] ?>" 
                                           class="btn btn-outline-info btn-lg px-4 ms-2">
                                            <i class="fas fa-eye me-2"></i>
                                            Ver Detalle
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($reservas)): ?>
    <!-- Card de Instrucciones -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Proceso de Ingreso
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="icon icon-lg icon-success shadow border-radius-md">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h6 class="mt-3">1. Verificación</h6>
                                <p class="text-sm">Confirme que los datos de la reserva sean correctos</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="icon icon-lg icon-warning shadow border-radius-md">
                                    <i class="fas fa-sign-in-alt"></i>
                                </div>
                                <h6 class="mt-3">2. Confirmación</h6>
                                <p class="text-sm">Presione "Confirmar Ingreso" para registrar el check-in</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="icon icon-lg icon-primary shadow border-radius-md">
                                    <i class="fas fa-home"></i>
                                </div>
                                <h6 class="mt-3">3. Activación</h6>
                                <p class="text-sm">La cabaña se marcará como ocupada automáticamente</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>