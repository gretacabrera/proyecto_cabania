<?php
/**
 * Vista de Resumen de Reserva - Paso 3
 * El huésped revisa todos los detalles antes del pago
 */
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
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
                    <!-- Mostrar mensajes de error si existen -->
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Error en el pago:</strong> <?= htmlspecialchars($_SESSION['error_message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>
                    
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
                                                <thead class="thead-light">
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
                                <input type="hidden" name="reserva_temp_id" value="<?= htmlspecialchars($reserva['temp_id'] ?? '') ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <button type="submit" class="btn btn-outline-danger w-100" 
                                        onclick="return confirm('¿Está seguro que desea cancelar esta reserva?')">
                                    <i class="fas fa-times me-1"></i>
                                    Cancelar Reserva
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form method="POST" action="<?= $this->url('/reservas/proceder-pago') ?>" class="d-inline w-100">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <button type="submit" class="btn btn-success w-100" id="btnProcederPago">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Pagar Ahora
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnPagar = document.getElementById('btnProcederPago');

    // Agregar efecto al hacer clic en "Pagar Ahora"
    btnPagar.closest('form').addEventListener('submit', function(e) {
        // Prevenir envío inicial para mostrar confirmación
        e.preventDefault();
        
        // Mostrar SweetAlert de confirmación
        Swal.fire({
            title: '¿Proceder al pago?',
            text: 'Será redirigido a la pasarela de pago segura',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Revisar datos'
        }).then((result) => {
            if (result.isConfirmed) {
                // Deshabilitar botón y mostrar loading
                btnPagar.disabled = true;
                btnPagar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Redirigiendo...';
                
                // Mostrar loading final
                Swal.fire({
                    title: 'Redirigiendo...',
                    text: 'Conectando con la pasarela de pago segura',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Enviar formulario después de un momento
                setTimeout(() => {
                    this.submit();
                }, 1500);
            }
        });
    });
});
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>