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
                            Hemos enviado los detalles completos de tu reserva a: 
                            <strong><?= htmlspecialchars($reserva['huesped_email']) ?></strong>
                        </p>
                    </div>

                    <!-- Datos de la Reserva -->
                    <div class="row g-4">
                        <!-- Información de la Reserva -->
                        <div class="col-md-6">
                            <div class="card bg-light h-100">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Detalles de la Reserva
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted">Número de Reserva</small>
                                        <div class="fw-bold fs-5 text-primary">#<?= $reserva['numero_reserva'] ?></div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Estado</small>
                                        <div>
                                            <span class="badge bg-success fs-6">CONFIRMADA</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Fecha de Confirmación</small>
                                        <div class="fw-bold"><?= date('d/m/Y H:i', strtotime($reserva['fecha_confirmacion'])) ?></div>
                                    </div>
                                    
                                    <div class="mb-0">
                                        <small class="text-muted">Método de Pago</small>
                                        <div class="fw-bold"><?= htmlspecialchars($reserva['metodo_pago']) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Alojamiento -->
                        <div class="col-md-6">
                            <div class="card bg-light h-100">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-home me-2"></i>
                                        Alojamiento
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <h6 class="text-primary"><?= htmlspecialchars($reserva['cabania_nombre']) ?></h6>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">Fechas de Estadía</small>
                                        <div class="fw-bold">
                                            <i class="fas fa-calendar-check text-success me-1"></i>
                                            <?= date('d/m/Y', strtotime($reserva['fecha_ingreso'])) ?> - 
                                            <?= date('d/m/Y', strtotime($reserva['fecha_salida'])) ?>
                                        </div>
                                        <small class="text-success"><?= $reserva['noches'] ?> noche<?= $reserva['noches'] > 1 ? 's' : '' ?></small>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">Huéspedes</small>
                                        <div>
                                            <i class="fas fa-users text-info me-1"></i>
                                            <?= $reserva['adultos'] ?> adulto<?= $reserva['adultos'] > 1 ? 's' : '' ?>
                                            <?php if ($reserva['ninos'] > 0): ?>
                                                + <?= $reserva['ninos'] ?> niño<?= $reserva['ninos'] > 1 ? 's' : '' ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-0">
                                        <small class="text-muted">Total Pagado</small>
                                        <div class="fw-bold fs-5 text-success">$<?= number_format($reserva['total'], 0, ',', '.') ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Servicios Adicionales -->
                    <?php if (!empty($reserva['servicios'])): ?>
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

                    <!-- Información Importante -->
                    <div class="card mt-4 border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Información Importante para tu Estadía
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <h6 class="text-primary">
                                        <i class="fas fa-clock me-1"></i>
                                        Horarios
                                    </h6>
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>Check-in:</strong> 15:00 hs</li>
                                        <li><strong>Check-out:</strong> 11:00 hs</li>
                                        <li><strong>Recepción:</strong> 24 hs</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        Ubicación
                                    </h6>
                                    <p class="mb-2">Casa de Palos Cabañas<br>
                                    Ruta Nacional 40, Km 2054<br>
                                    El Calafate, Santa Cruz</p>
                                    <a href="https://maps.google.com" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-external-link-alt me-1"></i>
                                        Ver en Google Maps
                                    </a>
                                </div>
                                <div class="col-12">
                                    <h6 class="text-primary">
                                        <i class="fas fa-suitcase me-1"></i>
                                        Qué llevar
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success me-1"></i> Documento de identidad</li>
                                                <li><i class="fas fa-check text-success me-1"></i> Ropa de abrigo (recomendado)</li>
                                                <li><i class="fas fa-check text-success me-1"></i> Protector solar</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success me-1"></i> Cámara fotográfica</li>
                                                <li><i class="fas fa-check text-success me-1"></i> Medicamentos personales</li>
                                                <li><i class="fas fa-check text-success me-1"></i> Calzado cómodo para caminar</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contacto y Soporte -->
                    <div class="card mt-4">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-headset me-2"></i>
                                ¿Necesitas Ayuda?
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="mb-2">
                                        <i class="fas fa-phone fa-2x text-primary mb-2"></i>
                                        <h6>Teléfono</h6>
                                        <a href="tel:+542902491004" class="btn btn-outline-primary">
                                            (02902) 49-1004
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-2">
                                        <i class="fas fa-envelope fa-2x text-success mb-2"></i>
                                        <h6>Email</h6>
                                        <a href="mailto:info@casadepalos.com" class="btn btn-outline-success">
                                            info@casadepalos.com
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-2">
                                        <i class="fas fa-comments fa-2x text-warning mb-2"></i>
                                        <h6>WhatsApp</h6>
                                        <a href="https://wa.me/5492902491004" target="_blank" class="btn btn-outline-warning">
                                            (02902) 49-1004
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <a href="/reservas/comprobante/<?= $reserva['id'] ?>" target="_blank" class="btn btn-outline-primary w-100">
                                <i class="fas fa-file-pdf me-1"></i>
                                Descargar Comprobante
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="/perfil/reservas" class="btn btn-outline-info w-100">
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

                    <!-- Política de Cancelación -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Política de Cancelación
                        </h6>
                        <p class="small text-muted mb-0">
                            Puedes cancelar tu reserva sin costo hasta 48 horas antes de la fecha de check-in. 
                            Para cancelaciones, contáctanos a través de los medios indicados arriba.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Sugerencias y Promociones -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-star me-2"></i>
                        Mejora tu Experiencia
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-center">
                                <i class="fas fa-hiking fa-2x text-success mb-2"></i>
                                <h6>Tours y Excursiones</h6>
                                <p class="small text-muted">Descubre los mejores lugares de El Calafate</p>
                                <a href="/tours" class="btn btn-outline-success btn-sm">Ver Tours</a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <i class="fas fa-utensils fa-2x text-warning mb-2"></i>
                                <h6>Restaurante</h6>
                                <p class="small text-muted">Disfruta de nuestra cocina patagónica</p>
                                <a href="/restaurante" class="btn btn-outline-warning btn-sm">Ver Menú</a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <i class="fas fa-spa fa-2x text-info mb-2"></i>
                                <h6>Spa & Wellness</h6>
                                <p class="small text-muted">Relájate después de tus aventuras</p>
                                <a href="/spa" class="btn btn-outline-info btn-sm">Conocer Más</a>
                            </div>
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