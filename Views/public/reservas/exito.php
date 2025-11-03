<?php
/**
 * Vista de Confirmación de Reserva Exitosa - Paso Final
 * Confirmación de que la reserva se procesó correctamente
 */
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <!-- Mensaje de Éxito Principal -->
            <div class="card shadow-lg border-success">
                <div class="card-header bg-success text-white text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-check-circle fa-4x"></i>
                    </div>
                    <h2 class="mb-1">¡Reserva Confirmada!</h2>
                    <p class="mb-0">Tu reserva ha sido procesada exitosamente</p>
                </div>
                
                <div class="card-body p-4">
                    <!-- Información de Confirmación -->
                    <div class="alert alert-success text-center mb-4">
                        <h5 class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            Confirmación Enviada por Email
                        </h5>
                        <p class="mb-0">
                            <?php 
                            $emailMostrar = '';
                            
                            // Debug temporal - remover en producción
                            error_log("DEBUG Vista Éxito - Email en reserva: '" . ($reserva['huesped_email'] ?? 'VACÍO') . "'");
                            error_log("DEBUG Vista Éxito - Email en sesión: '" . ($_SESSION['user']['usuario_email'] ?? 'VACÍO') . "'");
                            
                            // Priorizar email del huésped de la reserva
                            if (!empty($reserva['huesped_email'])) {
                                $emailMostrar = $reserva['huesped_email'];
                            } 
                            // Fallback a datos de sesión del usuario
                            elseif (!empty($_SESSION['user']['usuario_email'])) {
                                $emailMostrar = $_SESSION['user']['usuario_email'];
                            }
                            // Último fallback genérico
                            else {
                                $emailMostrar = 'tu email registrado';
                            }
                            ?>
                            Hemos enviado los detalles completos de tu reserva a: 
                            <strong><?= htmlspecialchars($emailMostrar) ?></strong>
                        </p>
                    </div>

                    <!-- Detalles Completos de la Reserva -->
                    <div class="card bg-light">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                Detalles de la Reserva
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-0">
                                <div class="col-12">
                                    <div class="list-group list-group-flush">
                                        <!-- Fecha y hora de confirmación -->
                                        <div class="list-group-item d-flex justify-content-between align-items-start border-0 px-0 py-3">
                                            <div class="fw-medium text-dark">Fecha y hora de confirmación</div>
                                            <div class="text-end">
                                                <div class="fw-bold"><?= date('d/m/Y H:i', strtotime($reserva_exitosa['fecha_confirmacion'] ?? date('Y-m-d H:i:s'))) ?></div>
                                            </div>
                                        </div>

                                        <!-- Cabaña seleccionada -->
                                        <div class="list-group-item d-flex justify-content-between align-items-start border-0 px-0 py-3">
                                            <div class="fw-medium text-dark">Cabaña seleccionada</div>
                                            <div class="text-end">
                                                <div class="fw-bold"><?= htmlspecialchars($reserva_exitosa['cabania_nombre'] ?? $cabania['cabania_nombre'] ?? 'Cabaña') ?></div>
                                            </div>
                                        </div>

                                        <!-- Fechas de la estadía -->
                                        <div class="list-group-item d-flex justify-content-between align-items-start border-0 px-0 py-3">
                                            <div class="fw-medium text-dark">Fechas de la estadía</div>
                                            <div class="text-end">
                                                <?php 
                                                $fechaInicio = $reserva['reserva_fhinicio'] ?? $reserva['fecha_ingreso'] ?? null;
                                                $fechaFin = $reserva['reserva_fhfin'] ?? $reserva['fecha_salida'] ?? null;
                                                $noches = 0;
                                                if ($fechaInicio && $fechaFin) {
                                                    $inicio = new DateTime($fechaInicio);
                                                    $fin = new DateTime($fechaFin);
                                                    $noches = $inicio->diff($fin)->days;
                                                }
                                                ?>
                                                <div class="fw-bold">
                                                    <?= $fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : 'N/A' ?> - 
                                                    <?= $fechaFin ? date('d/m/Y', strtotime($fechaFin)) : 'N/A' ?>
                                                </div>
                                                <small class="text-muted"><?= $noches ?> noche<?= $noches > 1 ? 's' : '' ?></small>
                                            </div>
                                        </div>

                                        <!-- Cantidad de huéspedes -->
                                        <div class="list-group-item d-flex justify-content-between align-items-start border-0 px-0 py-3">
                                            <div class="fw-medium text-dark">Cantidad de huéspedes</div>
                                            <div class="text-end">
                                                <div class="fw-bold">
                                                    <?= $reserva['adultos'] ?? $reserva['cantidad_personas'] ?? 1 ?> adulto<?= ($reserva['adultos'] ?? $reserva['cantidad_personas'] ?? 1) > 1 ? 's' : '' ?>
                                                    <?php if (($reserva['ninos'] ?? 0) > 0): ?>
                                                        + <?= $reserva['ninos'] ?? 0 ?> niño<?= ($reserva['ninos'] ?? 0) > 1 ? 's' : '' ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Total abonado -->
                                        <div class="list-group-item d-flex justify-content-between align-items-start border-0 px-0 py-3">
                                            <div class="fw-medium text-dark">Total abonado</div>
                                            <div class="text-end">
                                                <div class="fw-bold fs-5">$<?= number_format($reserva_exitosa['total_pagado'] ?? $reserva['total'] ?? 0, 0, ',', '.') ?></div>
                                            </div>
                                        </div>

                                        <!-- Método de pago -->
                                        <div class="list-group-item d-flex justify-content-between align-items-start border-0 px-0 py-3">
                                            <div class="fw-medium text-dark">Método de pago</div>
                                            <div class="text-end">
                                                <div class="fw-bold"><?= htmlspecialchars($reserva_exitosa['metodo_pago'] ?? $reserva['metodo_pago'] ?? 'N/A') ?></div>
                                            </div>
                                        </div>

                                        <!-- Horarios de check-in y check-out -->
                                        <div class="list-group-item d-flex justify-content-between align-items-start border-0 px-0 py-3">
                                            <div class="fw-medium text-dark">Horarios check-in / check-out</div>
                                            <div class="text-end">
                                                <div class="fw-bold">15:00 hs / 11:00 hs</div>
                                                <small class="text-muted">Recepción 24 hs</small>
                                            </div>
                                        </div>

                                        <!-- Políticas de cancelación -->
                                        <div class="list-group-item d-flex justify-content-between align-items-start border-0 px-0 py-3">
                                            <div class="fw-medium text-dark">Políticas de cancelación</div>
                                            <div class="text-end">
                                                <div class="fw-bold">Gratuita hasta 48 hs antes</div>
                                                <small class="text-muted d-block">Sin penalidad</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Servicios Adicionales -->
                    <?php if (!empty($reserva['servicios']) && is_array($reserva['servicios'])): ?>
                        <div class="card mt-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-concierge-bell me-2"></i>
                                    Servicios Adicionales Incluidos
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($reserva['servicios'] as $servicio): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span>
                                                    <i class="fas fa-check-circle text-success me-1"></i>
                                                    <?= htmlspecialchars($servicio['nombre']) ?>
                                                </span>
                                                <span class="fw-bold">$<?= number_format($servicio['subtotal'], 0, ',', '.') ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>



                    <!-- Botones de Acción -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <a href="/reservas/comprobante/<?= $reserva_exitosa['reserva_id'] ?? $reserva['id_reserva'] ?? '' ?>" target="_blank" class="btn btn-outline-primary w-100">
                                <i class="fas fa-file-pdf me-1"></i>
                                Descargar Comprobante
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="/reservas" class="btn btn-outline-info w-100">
                                <i class="fas fa-history me-1"></i>
                                Ver Mis Reservas
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="/" class="btn btn-primary w-100">
                                <i class="fas fa-home me-1"></i>
                                Volver al Inicio
                            </a>
                        </div>
                    </div>


                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Script para confetti de celebración -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Confetti de celebración
    confetti({
        particleCount: 100,
        spread: 70,
        origin: { y: 0.6 }
    });
    
    // Segundo confetti después de 500ms
    setTimeout(() => {
        confetti({
            particleCount: 50,
            angle: 60,
            spread: 55,
            origin: { x: 0 }
        });
        confetti({
            particleCount: 50,
            angle: 120,
            spread: 55,
            origin: { x: 1 }
        });
    }, 500);
});
</script>