<?php
/**
 * Vista de Resumen de Reserva - Paso 3
 * El huésped revisa todos los detalles antes del pago
 */
?>

<div class="container-fluid py-4">
    <div class="row justi                                    <!-- Total a Pagar -->
                                    <div class="d-flex justify-content-between mb-2 fs-5">
                                        <strong class="text-success">Total a Pagar</strong>
                                        <strong class="text-success">$<?= number_format($reserva['total_general'], 0, ',', '.') ?></strong>
                                    </div>ter">
        <div class="col-lg-10 col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-file-invoice me-2"></i>
                        Resumen de Reserva
                    </h4>
                    <small class="text-light">Paso 3 de 4 - Verificación final antes del pago</small>
                </div>
                
                <div class="card-body p-4">
                    <!-- Progress Bar -->
                    <div class="progress mb-4" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"></div>
                    </div>

                    <div class="row g-4">
                        <!-- Columna Izquierda: Detalles de la Reserva -->
                        <div class="col-lg-8">
                            <!-- Datos de la Cabaña -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0 text-primary">
                                        <i class="fas fa-home me-2"></i>
                                        Detalles del Alojamiento
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <?php if (!empty($cabania['cabania_foto'])): ?>
                                                <img src="<?= $this->asset('imagenes/cabanias/' . $cabania['cabania_foto']) ?>" 
                                                     alt="<?= htmlspecialchars($cabania['cabania_nombre']) ?>" 
                                                     class="img-fluid rounded">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 120px;">
                                                    <i class="fas fa-image fa-2x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-8">
                                            <h6 class="text-primary"><?= htmlspecialchars($cabania['cabania_nombre']) ?></h6>
                                            <p class="text-muted mb-3"><?= htmlspecialchars($cabania['cabania_descripcion']) ?></p>
                                            
                                            <div class="row g-2">
                                                <div class="col-sm-6">
                                                    <small class="text-muted d-block">Fechas</small>
                                                    <strong>
                                                        <?= date('d/m/Y', strtotime($reserva['fecha_ingreso'])) ?> - 
                                                        <?= date('d/m/Y', strtotime($reserva['fecha_salida'])) ?>
                                                    </strong>
                                                    <br><small class="text-success"><?= $noches ?> noche<?= $noches > 1 ? 's' : '' ?></small>
                                                </div>
                                                <div class="col-sm-6">
                                                    <small class="text-muted d-block">Huéspedes</small>
                                                    <strong><?= $reserva['cantidad_personas'] ?> persona<?= $reserva['cantidad_personas'] > 1 ? 's' : '' ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Datos del Huésped -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0 text-primary">
                                        <i class="fas fa-user-circle me-2"></i>
                                        Datos del Huésped Principal
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Nombre Completo</small>
                                            <strong><?= htmlspecialchars($persona['persona_nombre'] . ' ' . $persona['persona_apellido']) ?></strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Teléfono</small>
                                            <strong><?= htmlspecialchars($persona['persona_telefono'] ?? 'No especificado') ?></strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Email</small>
                                            <strong><?= htmlspecialchars($persona['persona_email'] ?? 'No especificado') ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Servicios Adicionales -->
                            <?php if (!empty($reserva['servicios'])): ?>
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0 text-primary">
                                            <i class="fas fa-concierge-bell me-2"></i>
                                            Servicios Adicionales
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Servicio</th>
                                                        <th class="text-center">Cantidad</th>
                                                        <th class="text-end">Precio Unit.</th>
                                                        <th class="text-end">Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($reserva['servicios'] as $servicio): ?>
                                                        <tr>
                                                            <td>
                                                                <strong><?= htmlspecialchars($servicio['nombre']) ?></strong>
                                                            </td>
                                                            <td class="text-center">1</td>
                                                            <td class="text-end">$<?= number_format($servicio['precio'], 0, ',', '.') ?></td>
                                                            <td class="text-end">$<?= number_format($servicio['precio'], 0, ',', '.') ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Columna Derecha: Resumen de Costos -->
                        <div class="col-lg-4">
                            <!-- Resumen Financiero -->
                            <div class="card sticky-top">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-calculator me-2"></i>
                                        Resumen de Pago
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <!-- Alojamiento -->
                                    <div class="d-flex justify-content-between mb-3">
                                        <div>
                                            <strong>Alojamiento</strong>
                                            <br><small class="text-muted">
                                                <?= $noches ?> noche<?= $noches > 1 ? 's' : '' ?> × 
                                                $<?= number_format($cabania['cabania_precio'], 0, ',', '.') ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <strong>$<?= number_format($reserva['subtotal_alojamiento'], 0, ',', '.') ?></strong>
                                        </div>
                                    </div>

                                    <!-- Servicios -->
                                    <?php if ($reserva['total_servicios'] > 0): ?>
                                        <div class="d-flex justify-content-between mb-3">
                                            <div>
                                                <strong>Servicios Adicionales</strong>
                                                <br><small class="text-muted"><?= count($reserva['servicios']) ?> servicio<?= count($reserva['servicios']) > 1 ? 's' : '' ?></small>
                                            </div>
                                            <div class="text-end">
                                                <strong>$<?= number_format($reserva['total_servicios'], 0, ',', '.') ?></strong>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <hr>

                                    <!-- Total -->
                                    <div class="d-flex justify-content-between mb-2 fs-5">
                                        <strong class="text-success">Total a Pagar</strong>
                                        <strong class="text-success">$<?= number_format($reserva['total_general'], 0, ',', '.') ?></strong>
                                    </div>

                                    <!-- Información Adicional -->
                                    <div class="bg-light p-3 rounded">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            <strong>Información Importante:</strong>
                                        </small>
                                        <ul class="small text-muted mb-0 mt-2">
                                            <li>Check-in: 15:00 hs</li>
                                            <li>Check-out: 11:00 hs</li>
                                            <li>Cancelación gratuita hasta 48hs antes</li>
                                            <li>Se enviará confirmación por email</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <a href="/reservas/servicios" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-arrow-left me-1"></i>
                                Modificar Servicios
                            </a>
                        </div>
                        <div class="col-md-4">
                            <form method="POST" action="/reservas/cancelar" class="d-inline w-100">
                                <input type="hidden" name="reserva_temp_id" value="<?= htmlspecialchars($reserva['temp_id']) ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <button type="submit" class="btn btn-outline-danger w-100" 
                                        onclick="return confirm('¿Está seguro que desea cancelar esta reserva?')">
                                    <i class="fas fa-times me-1"></i>
                                    Cancelar Reserva
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form method="POST" action="<?= $this->url('/reservas/pago') ?>" class="d-inline w-100">
                                <input type="hidden" name="reserva_temp_id" value="<?= htmlspecialchars($reserva['temp_id']) ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-credit-card me-1"></i>
                                    Proceder al Pago
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Términos y Condiciones -->
                    <div class="mt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terminos" required>
                            <label class="form-check-label small text-muted" for="terminos">
                                Al proceder al pago, acepto los 
                                <a href="/terminos" target="_blank">términos y condiciones</a> 
                                y la <a href="/privacidad" target="_blank">política de privacidad</a> de Casa de Palos Cabañas.
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxTerminos = document.getElementById('terminos');
    const btnPagar = document.querySelector('button[type="submit"]');
    
    checkboxTerminos.addEventListener('change', function() {
        btnPagar.disabled = !this.checked;
    });
    
    // Inicialmente deshabilitar el botón hasta que se acepten términos
    btnPagar.disabled = true;
});
</script>