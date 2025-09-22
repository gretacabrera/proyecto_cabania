<?php
/**
 * Vista de Confirmación de Reserva Online - Paso 1
 * El huésped confirma los datos básicos de su reserva después de seleccionar cabaña y fechas
 */
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-check me-2"></i>
                        Confirmar Reserva
                    </h4>
                    <small class="text-light">Paso 1 de 4 - Confirmación de datos básicos</small>
                </div>
                
                <div class="card-body p-4">
                    <!-- Progress Bar -->
                    <div class="progress mb-4" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 25%"></div>
                    </div>

                    <!-- Datos de la Cabaña Seleccionada -->
                    <div class="row mb-4">
                        <div class="col-md-5">
                            <img src="<?= asset('imagenes/cabanias/' . $reserva['cabania_imagen']) ?>" 
                                 alt="<?= htmlspecialchars($reserva['cabania_nombre']) ?>" 
                                 class="img-fluid rounded shadow-sm"
                                 onerror="this.src='<?= asset('imagenes/cabanias/default.jpg') ?>'">
                        </div>
                        <div class="col-md-7">
                            <h5 class="text-primary"><?= htmlspecialchars($reserva['cabania_nombre']) ?></h5>
                            <p class="text-muted mb-2"><?= htmlspecialchars($reserva['cabania_descripcion']) ?></p>
                            
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Capacidad</small>
                                    <span class="badge bg-info"><?= htmlspecialchars($reserva['cabania_capacidad']) ?> personas</span>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Precio por noche</small>
                                    <span class="fw-bold text-success">$<?= number_format($reserva['cabania_precio'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles de la Reserva -->
                    <form method="POST" action="<?= $this->url('/reservas/servicios') ?>" id="formConfirmarReserva">
                        <input type="hidden" name="cabania_id" value="<?= htmlspecialchars($reserva['cabania_id']) ?>">
                        <input type="hidden" name="fecha_ingreso" value="<?= htmlspecialchars($reserva['fecha_ingreso']) ?>">
                        <input type="hidden" name="fecha_salida" value="<?= htmlspecialchars($reserva['fecha_salida']) ?>">
                        <input type="hidden" name="cantidad_personas" value="<?= htmlspecialchars($reserva['cantidad_personas']) ?>">
                        <input type="hidden" name="id_persona" value="<?= htmlspecialchars($huesped['id_persona']) ?>">
                        <input type="hidden" name="subtotal" value="<?= htmlspecialchars($reserva['subtotal']) ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        
                        <!-- Campo para observaciones que se actualizará via JavaScript -->
                        <input type="hidden" name="observaciones_hidden" id="observacionesHidden">
                        
                        <div class="row g-3">
                            <!-- Fechas -->
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            Fechas de Estadía
                                        </h6>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label small text-muted">Check-in</label>
                                                <input type="date" name="fecha_ingreso" 
                                                       class="form-control form-control-sm" 
                                                       value="<?= htmlspecialchars($reserva['fecha_ingreso']) ?>"
                                                       min="<?= date('Y-m-d') ?>" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small text-muted">Check-out</label>
                                                <input type="date" name="fecha_salida" 
                                                       class="form-control form-control-sm" 
                                                       value="<?= htmlspecialchars($reserva['fecha_salida']) ?>"
                                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                                            </div>
                                        </div>
                                        <small class="text-muted mt-1 d-block">
                                            <strong><?= $reserva['noches'] ?> noche<?= $reserva['noches'] > 1 ? 's' : '' ?></strong>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Huéspedes -->
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">
                                            <i class="fas fa-users me-1"></i>
                                            Número de Huéspedes
                                        </h6>
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <label class="form-label small text-muted">Total de personas</label>
                                                <select name="cantidad_personas" class="form-select form-select-sm" required>
                                                    <?php for($i = 1; $i <= $reserva['cabania_capacidad']; $i++): ?>
                                                        <option value="<?= $i ?>" <?= $i == ($reserva['cantidad_personas'] ?? 1) ? 'selected' : '' ?>>
                                                            <?= $i ?> persona<?= $i > 1 ? 's' : '' ?>
                                                        </option>
                                                    <?php endfor; ?>
                                                </select>
                                                <small class="text-muted">Capacidad máxima: <?= $reserva['cabania_capacidad'] ?> personas</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Datos del Huésped -->
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">
                                            <i class="fas fa-user-circle me-1"></i>
                                            Datos del Huésped Principal
                                        </h6>
                                        <input type="hidden" name="id_persona" value="<?= $huesped['id_persona'] ?>">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label small">Nombre</label>
                                                <input type="text" name="huesped_nombre" 
                                                       class="form-control" 
                                                       value="<?= htmlspecialchars($huesped['nombre'] ?? '') ?>" 
                                                       readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">Apellido</label>
                                                <input type="text" name="huesped_apellido" 
                                                       class="form-control" 
                                                       value="<?= htmlspecialchars($huesped['apellido'] ?? '') ?>" 
                                                       readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">Fecha de Nacimiento</label>
                                                <input type="date" name="huesped_fecha_nacimiento" 
                                                       class="form-control" 
                                                       value="<?= htmlspecialchars($huesped['fecha_nacimiento'] ?? '') ?>" 
                                                       readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small">Correo Electrónico</label>
                                                <input type="email" name="huesped_email" 
                                                       class="form-control" 
                                                       value="<?= htmlspecialchars($huesped['email'] ?? '') ?>" 
                                                       readonly>
                                                <?php if (empty($huesped['email'])): ?>
                                                    <small class="text-warning">No hay email registrado en contactos</small>
                                                <?php else: ?>
                                                    <small class="text-muted">Obtenido de contactos</small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small">Teléfono</label>
                                                <input type="tel" name="huesped_telefono" 
                                                       class="form-control" 
                                                       value="<?= htmlspecialchars($huesped['telefono'] ?? '') ?>" 
                                                       readonly>
                                                <?php if (empty($huesped['telefono'])): ?>
                                                    <small class="text-warning">No hay teléfono registrado en contactos</small>
                                                <?php else: ?>
                                                    <small class="text-muted">Obtenido de contactos</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i> Datos cargados desde tu perfil de usuario. 
                                                Si necesitas actualizar esta información, hazlo desde tu perfil.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div class="col-12">
                                <label class="form-label">Observaciones Especiales (Opcional)</label>
                                <textarea name="observaciones" class="form-control" rows="3" 
                                          placeholder="Menciona cualquier solicitud especial o necesidad particular..."></textarea>
                            </div>
                        </div>

                        <!-- Resumen de Costos -->
                        <div class="card bg-success bg-opacity-10 border-success mt-4">
                            <div class="card-body">
                                <h6 class="text-success mb-3">
                                    <i class="fas fa-calculator me-1"></i>
                                    Resumen de Costos
                                </h6>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span><?= $reserva['noches'] ?> noche<?= $reserva['noches'] > 1 ? 's' : '' ?> × $<?= number_format($reserva['cabania_precio'], 0, ',', '.') ?></span>
                                            <span>$<?= number_format($reserva['subtotal'], 0, ',', '.') ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between fw-bold text-success border-top pt-2">
                                            <span>Total Base</span>
                                            <span>$<?= number_format($reserva['total'], 0, ',', '.') ?></span>
                                        </div>
                                        <small class="text-muted">* Los servicios adicionales se pueden agregar en el siguiente paso</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <a href="/catalogo" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-arrow-left me-1"></i>
                                    Volver al Catálogo
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary w-100">
                                    Confirmar y Continuar
                                    <i class="fas fa-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para validación de fechas -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fechaIngreso = document.querySelector('input[name="fecha_ingreso"]');
    const fechaSalida = document.querySelector('input[name="fecha_salida"]');
    
    fechaIngreso.addEventListener('change', function() {
        const minSalida = new Date(this.value);
        minSalida.setDate(minSalida.getDate() + 1);
        fechaSalida.min = minSalida.toISOString().split('T')[0];
        
        if (fechaSalida.value <= this.value) {
            fechaSalida.value = minSalida.toISOString().split('T')[0];
        }
        actualizarCostos();
    });
    
    fechaSalida.addEventListener('change', actualizarCostos);
    
    function actualizarCostos() {
        if (fechaIngreso.value && fechaSalida.value) {
            const inicio = new Date(fechaIngreso.value);
            const fin = new Date(fechaSalida.value);
            const noches = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));
            
            if (noches > 0) {
                // Aquí podrías hacer una llamada AJAX para recalcular costos
                console.log('Noches:', noches);
            }
        }
    }
    
    // Copiar observaciones al campo oculto antes de enviar el formulario
    const form = document.getElementById('formConfirmarReserva');
    form.addEventListener('submit', function(e) {
        const observaciones = document.querySelector('textarea[name="observaciones"]');
        const observacionesHidden = document.getElementById('observacionesHidden');
        if (observaciones && observacionesHidden) {
            observacionesHidden.value = observaciones.value;
        }
    });
});
</script>