<div class="container-fluid">
    <!-- Encabezado -->
    <div class="card border-0 shadow-sm">
        <div class="card-header text-dark py-3 mb-0">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0"><i class="fas fa-cash-register me-2"></i> Gestión de Caja</h4>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$caja): ?>
        <!-- Sin caja asignada -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                        <h5 class="mb-3">No tiene una caja asignada</h5>
                        <p class="text-muted">
                            Contacte con el administrador para que le asigne una caja de trabajo.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Información de la caja -->
        <div class="row mt-4">
            <!-- Columna principal -->
            <div class="col-lg-8">
                <!-- Información General de la Caja -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información de la Caja</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Descripción</label>
                                    <p class="mb-0 fw-bold"><?= htmlspecialchars($caja['caja_descripcion']) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Usuario Asignado</label>
                                    <p class="mb-0"><?= htmlspecialchars($caja['persona_denominacion'] ?? $caja['usuario_nombre']) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Estado del turno actual -->
                        <div class="alert <?= $turnoAbierto ? 'alert-success' : 'alert-warning' ?> mb-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <i class="fas <?= $turnoAbierto ? 'fa-check-circle' : 'fa-lock' ?> me-2"></i>
                                    <strong><?= $turnoAbierto ? 'Caja Abierta' : 'Caja Cerrada' ?></strong>
                                </div>
                                <?php if ($turnoAbierto): ?>
                                    <div class="col-auto">
                                        <small class="text-muted">
                                            Apertura: <?= date('d/m/Y H:i', strtotime($turnoAbierto['cajaturno_fhapertura'])) ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel lateral de Acciones Rápidas -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Acciones Rápidas</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!$turnoAbierto): ?>
                            <a href="<?= url('/aperturas/apertura') ?>" class="btn btn-success w-100 mb-3">
                                <i class="fas fa-unlock-alt me-2"></i>Abrir Caja
                            </a>
                            <div class="alert alert-info mb-0">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Importante:</strong> Debe abrir la caja antes de realizar operaciones.
                                </small>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success mb-3">
                                <small>
                                    <i class="fas fa-check-circle me-1"></i>
                                    La caja está abierta desde el <?= date('d/m/Y', strtotime($turnoAbierto['cajaturno_fhapertura'])) ?>
                                </small>
                            </div>
                            
                            <button class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-plus me-2"></i> Nuevo Movimiento
                            </button>
                            <a href="<?= url('/arqueos/formulario') ?>" class="btn btn-warning w-100 mb-2">
                                <i class="fas fa-calculator me-2"></i> Arqueo de Caja
                            </a>

                            <hr>

                            <h6 class="small text-muted mb-2">INFORMACIÓN DEL TURNO</h6>
                            <ul class="list-unstyled small mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-user text-muted me-2"></i>
                                    <strong>Operador:</strong><br>
                                    <span class="ms-4"><?= htmlspecialchars($turnoAbierto['apertura_denominacion'] ?? $turnoAbierto['apertura_nombre']) ?></span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-clock text-muted me-2"></i>
                                    <strong>Duración:</strong><br>
                                    <span class="ms-4">
                                        <?php
                                        $inicio = new DateTime($turnoAbierto['cajaturno_fhapertura']);
                                        $ahora = new DateTime();
                                        $diff = $inicio->diff($ahora);
                                        echo $diff->format('%h horas %i minutos');
                                        ?>
                                    </span>
                                </li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
