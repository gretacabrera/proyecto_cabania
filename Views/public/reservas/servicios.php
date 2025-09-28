<?php
/**
 * Vista de Selección de Servicios Adicionales - Paso 2
 * El huésped puede agregar servicios adicionales a su reserva
 */
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-concierge-bell me-2"></i>
                        Servicios Adicionales
                    </h4>
                    <small class="text-light">Paso 2 de 4 - Personaliza tu experiencia</small>
                </div>
                
                <div class="card-body p-4">
                    <!-- Progress Bar -->
                    <div class="progress mb-4" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 50%"></div>
                    </div>

                    <!-- Resumen de Reserva -->
                    <div class="card bg-light mb-4">
                        <div class="card-body py-3">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1"><?= htmlspecialchars($reserva['cabania_nombre']) ?></h6>
                                    <small class="text-muted">
                                        <?= date('d/m/Y', strtotime($reserva['fecha_ingreso'])) ?> - 
                                        <?= date('d/m/Y', strtotime($reserva['fecha_salida'])) ?>
                                        (<?= $reserva['noches'] ?> noche<?= $reserva['noches'] > 1 ? 's' : '' ?>)
                                    </small>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="fw-bold text-success">Total Base: $<?= number_format($reserva['total'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="<?= $this->url('/reservas/procesar-servicios') ?>" id="formServicios">
                        <!-- Datos de la reserva -->
                        <input type="hidden" name="cabania_id" value="<?= $reserva['cabania_id'] ?>">
                        <input type="hidden" name="fecha_ingreso" value="<?= $reserva['fecha_ingreso'] ?>">
                        <input type="hidden" name="fecha_salida" value="<?= $reserva['fecha_salida'] ?>">
                        <input type="hidden" name="cantidad_personas" value="<?= $reserva['cantidad_personas'] ?>">
                        <input type="hidden" name="id_persona" value="<?= $reserva['id_persona'] ?>">
                        <input type="hidden" name="subtotal_alojamiento" value="<?= $reserva['subtotal'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">>
                        
                        <!-- Servicios Disponibles -->
                        <div class="row g-4">
                            <?php if (!empty($servicios)): ?>
                                <div class="col-12">
                                    <h5 class="text-primary border-bottom pb-2">
                                        <i class="fas fa-concierge-bell me-2"></i>
                                        Servicios Adicionales Disponibles
                                    </h5>
                                </div>
                                
                                <?php foreach ($servicios as $servicio): ?>
                                    <div class="col-lg-6 col-xl-4">
                                        <div class="card h-100 servicio-card">
                                            <div class="card-body">
                                                <div class="form-check position-absolute top-0 end-0 m-3">
                                                    <input class="form-check-input servicio-checkbox" 
                                                           type="checkbox" 
                                                           name="servicios[]" 
                                                           value="<?= $servicio['id_servicio'] ?>"
                                                           id="servicio_<?= $servicio['id_servicio'] ?>"
                                                           data-precio="<?= $servicio['servicio_precio'] ?>"
                                                           data-nombre="<?= htmlspecialchars($servicio['servicio_nombre']) ?>">
                                                </div>
                                                
                                                <label for="servicio_<?= $servicio['id_servicio'] ?>" class="cursor-pointer w-100">
                                                    <h6 class="card-title text-dark"><?= htmlspecialchars($servicio['servicio_nombre']) ?></h6>
                                                    
                                                    <?php if (!empty($servicio['servicio_descripcion'])): ?>
                                                        <p class="card-text text-muted small mb-2">
                                                            <?= htmlspecialchars($servicio['servicio_descripcion']) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($servicio['tiposervicio_descripcion'])): ?>
                                                        <small class="text-muted d-block mb-2">
                                                            <i class="fas fa-tag me-1"></i>
                                                            <?= htmlspecialchars($servicio['tiposervicio_descripcion']) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                    
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="badge bg-success fs-6">
                                                            $<?= number_format($servicio['servicio_precio'], 0, ',', '.') ?>
                                                        </span>
                                                        
                                                        <small class="text-success">
                                                            <i class="fas fa-check-circle"></i> Disponible
                                                        </small>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="alert alert-info text-center">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No hay servicios adicionales disponibles en este momento.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Resumen de Servicios Seleccionados -->
                        <div class="card bg-info bg-opacity-10 border-info mt-4" id="resumenServicios" style="display: none;">
                            <div class="card-body">
                                <h6 class="text-info mb-3">
                                    <i class="fas fa-list-check me-1"></i>
                                    Servicios Seleccionados
                                </h6>
                                <div id="listaServicios"></div>
                                <hr>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Total Servicios:</span>
                                    <span id="totalServicios">$0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Total General -->
                        <div class="card bg-success bg-opacity-10 border-success mt-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Alojamiento (<?= $reserva['noches'] ?> noche<?= $reserva['noches'] > 1 ? 's' : '' ?>)</span>
                                            <span>$<?= number_format($reserva['total'], 0, ',', '.') ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Servicios Adicionales</span>
                                            <span id="subtotalServicios">$0</span>
                                        </div>
                                        <hr class="my-2">
                                        <div class="d-flex justify-content-between fw-bold text-success">
                                            <span>TOTAL GENERAL</span>
                                            <span id="totalGeneral">$<?= number_format($reserva['total'], 0, ',', '.') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <a href="/reservas/confirmar" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-arrow-left me-1"></i>
                                    Volver
                                </a>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" name="accion" value="omitir" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-forward me-1"></i>
                                    Omitir Servicios
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" name="accion" value="confirmar" class="btn btn-primary w-100">
                                    Confirmar Servicios
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

<!-- Estilos CSS -->
<style>
.servicio-card {
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
}

.servicio-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.servicio-card.selected {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}

.cursor-pointer {
    cursor: pointer;
}
</style>

<!-- Scripts JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.servicio-checkbox');
    const resumenServicios = document.getElementById('resumenServicios');
    const listaServicios = document.getElementById('listaServicios');
    const totalServicios = document.getElementById('totalServicios');
    const subtotalServicios = document.getElementById('subtotalServicios');
    const totalGeneral = document.getElementById('totalGeneral');
    const totalBase = <?= $reserva['total'] ?>;
    
    let serviciosSeleccionados = [];

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const card = this.closest('.servicio-card');
            const precio = parseFloat(this.dataset.precio);
            const nombre = this.dataset.nombre;
            const servicioId = this.value;

            if (this.checked) {
                card.classList.add('selected');
                serviciosSeleccionados.push({
                    id: servicioId,
                    nombre: nombre,
                    precio: precio
                });
            } else {
                card.classList.remove('selected');
                serviciosSeleccionados = serviciosSeleccionados.filter(s => s.id !== servicioId);
            }

            actualizarResumen();
        });
    });

    function actualizarResumen() {
        if (serviciosSeleccionados.length > 0) {
            resumenServicios.style.display = 'block';
            
            let html = '';
            let total = 0;
            
            serviciosSeleccionados.forEach(servicio => {
                html += `
                    <div class="d-flex justify-content-between mb-1">
                        <span>${servicio.nombre}</span>
                        <span>$${servicio.precio.toLocaleString()}</span>
                    </div>
                `;
                total += servicio.precio;
            });
            
            listaServicios.innerHTML = html;
            totalServicios.textContent = `$${total.toLocaleString()}`;
            subtotalServicios.textContent = `$${total.toLocaleString()}`;
            totalGeneral.textContent = `$${(totalBase + total).toLocaleString()}`;
        } else {
            resumenServicios.style.display = 'none';
            subtotalServicios.textContent = '$0';
            totalGeneral.textContent = `$${totalBase.toLocaleString()}`;
        }
    }

    // Manejar click en las cards para seleccionar/deseleccionar
    document.querySelectorAll('.servicio-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                const checkbox = this.querySelector('.servicio-checkbox');
                checkbox.click();
            }
        });
    });
});
</script>