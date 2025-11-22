<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <!-- Card Contenedora Principal -->
            <div class="card shadow-lg border-0">
                <!-- Header -->
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                        <div>
                            <h4 class="mb-1 text-dark">
                                <i class="fas fa-calendar-check text-primary me-2"></i>
                                Mis Reservas
                            </h4>
                            <?php if (!empty($reservas)): ?>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Total de reservas: <strong><?= count($reservas) ?></strong>
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2 mt-2 mt-md-0">
                            <a href="<?= url('/catalogo') ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Nueva Reserva
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <?php if (empty($reservas)): ?>
                        <!-- Estado Vacío -->
                        <div class="text-center py-5 px-3">
                            <i class="fas fa-calendar-times fa-3x text-secondary mb-3"></i>
                            <h5 class="text-muted mb-2">No tienes reservas</h5>
                            <p class="text-muted small mb-3">Cuando realices una reserva aparecerá aquí.</p>
                            <a href="<?= url('/catalogo') ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-home me-1"></i>Ver Cabañas Disponibles
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Lista Responsive de Reservas -->
                        <div class="list-group list-group-flush">
                            <?php foreach ($reservas as $index => $reserva): 
                                $estadoClass = [
                                    'pendiente' => 'warning',
                                    'confirmada' => 'success',
                                    'en curso' => 'info',
                                    'finalizada' => 'secondary',
                                    'anulada' => 'danger',
                                    'cancelada' => 'danger'
                                ];
                                $estado = strtolower($reserva['estadoreserva_descripcion'] ?? 'desconocido');
                                $badgeClass = $estadoClass[$estado] ?? 'secondary';
                            ?>
                                <div class="list-group-item list-group-item-action p-0 border-0 <?= $index > 0 ? 'border-top' : '' ?>">
                                    <div class="p-4">
                                        <!-- Header de la Reserva -->
                                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-3">
                                            <div class="mb-2 mb-md-0">
                                                <h5 class="mb-1">
                                                    <i class="fas fa-home text-primary me-2"></i>
                                                    <?= htmlspecialchars($reserva['cabania_nombre']) ?>
                                                </h5>
                                                <small class="text-muted">
                                                    Estado: <strong class="text-<?= $badgeClass ?>"><?= ucfirst($reserva['estadoreserva_descripcion']) ?></strong>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <div class="mb-1">
                                                    <small class="text-muted d-block">Importe Total</small>
                                                    <h5 class="text-dark mb-0">$<?= number_format($reserva['importe_total'], 2) ?></h5>
                                                </div>
                                                <div class="mb-1">
                                                    <small class="text-muted">Abonado: <span class="text-success fw-bold">$<?= number_format($reserva['total_abonado'], 2) ?></span></small>
                                                </div>
                                                <?php if ($reserva['saldo_pendiente'] > 0): ?>
                                                    <div>
                                                        <small class="text-muted">Pendiente: <span class="text-warning fw-bold">$<?= number_format($reserva['saldo_pendiente'], 2) ?></span></small>
                                                    </div>
                                                <?php else: ?>
                                                    <div>
                                                        <small class="text-success"><i class="fas fa-check-circle me-1"></i>Pagado completo</small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Información de Fechas -->
                                        <div class="row g-3 mb-3">
                                            <div class="col-12 col-md-4">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-calendar-check text-muted me-2 mr-3"></i>
                                                    <div>
                                                        <small class="text-muted d-block">Confirmación</small>
                                                        <strong>
                                                            <?php if (!empty($reserva['fecha_confirmacion'])): ?>
                                                                <?= date('d/m/Y H:i', strtotime($reserva['fecha_confirmacion'])) ?>
                                                            <?php else: ?>
                                                                <span class="text-warning">Pendiente</span>
                                                            <?php endif; ?>
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-4">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-sign-in-alt text-success me-2 mr-3"></i>
                                                    <div>
                                                        <small class="text-muted d-block">Entrada</small>
                                                        <strong><?= date('d/m/Y', strtotime($reserva['reserva_fhinicio'])) ?></strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-4">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-sign-out-alt text-danger me-2 mr-3"></i>
                                                    <div>
                                                        <small class="text-muted d-block">Salida</small>
                                                        <strong><?= date('d/m/Y', strtotime($reserva['reserva_fhfin'])) ?></strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Acciones -->
                                        <div class="d-flex flex-wrap gap-2">
                                            <!-- Completar Pago (para reservas pendientes) -->
                                            <?php if ($reserva['rela_estadoreserva'] == 1): ?>
                                                <a href="<?= url('/reservas/' . $reserva['id_reserva'] . '/pagar') ?>" 
                                                   class="btn btn-warning btn-sm">
                                                    <i class="fas fa-credit-card me-1"></i>
                                                    <span class="d-none d-sm-inline">Completar </span>Pago
                                                </a>
                                            <?php endif; ?>
                                            
                                            <!-- Marcar Ingreso -->
                                            <?php if ($reserva['rela_estadoreserva'] == 2): ?>
                                                <button type="button"
                                                   class="btn btn-outline-success btn-sm btn-marcar-ingreso"
                                                   data-reserva-id="<?= $reserva['id_reserva'] ?>"
                                                   data-cabania="<?= htmlspecialchars($reserva['cabania_nombre']) ?>">
                                                    <i class="fas fa-sign-in-alt me-1"></i>
                                                    <span class="d-none d-sm-inline">Marcar </span>Ingreso
                                                </button>
                                            <?php endif; ?>
                                            
                                            <!-- Marcar Salida -->
                                            <?php if ($reserva['rela_estadoreserva'] == 3): ?>
                                                <button type="button"
                                                   class="btn btn-outline-warning btn-sm btn-marcar-salida"
                                                   data-reserva-id="<?= $reserva['id_reserva'] ?>"
                                                   data-cabania="<?= htmlspecialchars($reserva['cabania_nombre']) ?>">
                                                    <i class="fas fa-sign-out-alt me-1"></i>
                                                    <span class="d-none d-sm-inline">Marcar </span>Salida
                                                </button>
                                            <?php endif; ?>
                                            
                                            <!-- Registrar Consumo -->
                                            <?php if (in_array($reserva['rela_estadoreserva'], [2, 3])): ?>
                                                <a href="<?= url('/reservas/' . $reserva['id_reserva'] . '/consumos') ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-shopping-cart me-1"></i>
                                                    <span class="d-none d-sm-inline">Ver </span>Consumos
                                                </a>
                                            <?php endif; ?>
                                            
                                            <!-- Ver Huéspedes -->
                                            <a href="<?= url('/reservas/' . $reserva['id_reserva'] . '/huespedes') ?>" 
                                               class="btn btn-outline-info btn-sm">
                                                <i class="fas fa-users me-1"></i>
                                                <span class="d-none d-sm-inline">Ver </span>Huéspedes
                                            </a>
                                            
                                            <!-- Ver Comentarios -->
                                            <?php if (in_array($reserva['rela_estadoreserva'], [3, 4])): ?>
                                                <a href="<?= url('/reservas/' . $reserva['id_reserva'] . '/comentarios') ?>" 
                                                   class="btn btn-outline-secondary btn-sm">
                                                    <i class="fas fa-comments me-1"></i>
                                                    <span class="d-none d-sm-inline">Ver </span>Comentarios
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Marcar Ingreso
    document.querySelectorAll('.btn-marcar-ingreso').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const reservaId = this.dataset.reservaId;
            const cabania = this.dataset.cabania;
            
            Swal.fire({
                title: '¿Confirmar ingreso?',
                html: `¿Deseas marcar el ingreso a la cabaña <strong>${cabania}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, confirmar ingreso',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `<?= url('/reservas/') ?>${reservaId}/marcar-ingreso`;
                }
            });
        });
    });
    
    // Marcar Salida
    document.querySelectorAll('.btn-marcar-salida').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const reservaId = this.dataset.reservaId;
            const cabania = this.dataset.cabania;
            
            Swal.fire({
                title: '¿Confirmar salida?',
                html: `¿Deseas marcar la salida de la cabaña <strong>${cabania}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, confirmar salida',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `<?= url('/reservas/') ?>${reservaId}/marcar-salida`;
                }
            });
        });
    });
});
</script>
```