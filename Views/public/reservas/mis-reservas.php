<?php
$pageTitle = 'Mis Reservas';
$pageStyles = ['public.css'];
require_once __DIR__ . '/../../shared/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Mis Reservas
                    </h3>
                </div>
                <div class="card-body">
                    <?php if (empty($reservas)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No tienes reservas</h4>
                            <p class="text-muted">Cuando realices una reserva aparecerá aquí.</p>
                            <a href="/" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Realizar una reserva
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($reservas as $reserva): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card border-left-primary">
                                        <div class="card-body">
                                            <!-- Header con estado -->
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h5 class="card-title mb-0">
                                                    <i class="fas fa-home me-2 text-primary"></i>
                                                    <?= htmlspecialchars($reserva['cabania_nombre']) ?>
                                                </h5>
                                                <?php
                                                $estadoClass = [
                                                    'pendiente' => 'warning',
                                                    'confirmada' => 'success',
                                                    'en curso' => 'info',
                                                    'pendiente de pago' => 'warning',
                                                    'finalizada' => 'secondary',
                                                    'anulada' => 'danger',
                                                    'expirada' => 'dark',
                                                    'cancelada' => 'danger'
                                                ];
                                                $estado = $reserva['estadoreserva_descripcion'] ?? 'desconocido';
                                                $class = $estadoClass[strtolower($estado)] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $class ?> fs-6">
                                                    <?= ucfirst($estado) ?>
                                                </span>
                                            </div>
                                            
                                            <!-- Información de la reserva -->
                                            <div class="mb-3">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Check-in</small>
                                                        <strong><?= date('d/m/Y H:i', strtotime($reserva['reserva_fechainicio'])) ?></strong>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Check-out</small>
                                                        <strong><?= date('d/m/Y H:i', strtotime($reserva['reserva_fechafin'])) ?></strong>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <small class="text-muted d-block">Huéspedes</small>
                                                <strong>
                                                    <i class="fas fa-users me-1"></i>
                                                    <?= $reserva['reserva_cantidadpersonas'] ?> persona<?= $reserva['reserva_cantidadpersonas'] > 1 ? 's' : '' ?>
                                                </strong>
                                            </div>
                                            
                                            <?php if (!empty($reserva['reserva_observaciones'])): ?>
                                                <div class="mb-3">
                                                    <small class="text-muted d-block">Observaciones</small>
                                                    <p class="small"><?= nl2br(htmlspecialchars($reserva['reserva_observaciones'])) ?></p>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Expiración si aplica -->
                                            <?php if ($reserva['rela_estadoreserva'] == 1 && !empty($reserva['reserva_fhexpiracion'])): ?>
                                                <?php 
                                                $expiracion = strtotime($reserva['reserva_fhexpiracion']);
                                                $ahora = time();
                                                ?>
                                                <div class="alert alert-warning small mb-3">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <strong>Esta reserva expira:</strong><br>
                                                    <?= date('d/m/Y H:i', $expiracion) ?>
                                                    <?php if ($expiracion > $ahora): ?>
                                                        <br><small class="text-muted">
                                                            (<?= round(($expiracion - $ahora) / 60) ?> minutos restantes)
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Botones de acción -->
                                            <div class="d-flex justify-content-end">
                                                <?php 
                                                // Solo mostrar cancelación para reservas PENDIENTES o CONFIRMADAS
                                                if (in_array($reserva['rela_estadoreserva'], [1, 2])): 
                                                ?>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm btn-cancelar-reserva"
                                                            data-reserva-id="<?= $reserva['id_reserva'] ?>"
                                                            data-reserva-info="<?= htmlspecialchars($reserva['cabania_nombre']) ?>">
                                                        <i class="fas fa-times me-1"></i>
                                                        Cancelar Reserva
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($reserva['rela_estadoreserva'] == 2): // CONFIRMADA ?>
                                                    <a href="/reservas/<?= $reserva['id_reserva'] ?>/voucher" 
                                                       class="btn btn-primary btn-sm ms-2" target="_blank">
                                                        <i class="fas fa-download me-1"></i>
                                                        Descargar Voucher
                                                    </a>
                                                <?php endif; ?>
                                            </div>
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

<!-- Modal de confirmación -->
<div class="modal fade" id="confirmCancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Confirmar Cancelación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">¿Está seguro de que desea cancelar esta reserva?</p>
                <div class="alert alert-warning">
                    <strong>Cabaña:</strong> <span id="modal-reserva-info"></span><br>
                    <small class="text-muted">
                        Esta acción no se puede deshacer. La cabaña quedará nuevamente disponible.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Mantener Reserva
                </button>
                <button type="button" class="btn btn-danger" id="confirm-cancel-btn">
                    <i class="fas fa-check me-1"></i>Confirmar Cancelación
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('confirmCancelModal'));
    let reservaIdToCancel = null;
    
    // Manejar cancelación de reservas
    document.querySelectorAll('.btn-cancelar-reserva').forEach(btn => {
        btn.addEventListener('click', function() {
            reservaIdToCancel = this.dataset.reservaId;
            const reservaInfo = this.dataset.reservaInfo;
            
            document.getElementById('modal-reserva-info').textContent = reservaInfo;
            modal.show();
        });
    });
    
    // Confirmar cancelación
    document.getElementById('confirm-cancel-btn').addEventListener('click', function() {
        if (reservaIdToCancel) {
            const btn = this;
            const originalText = btn.innerHTML;
            
            // Mostrar loading
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Cancelando...';
            btn.disabled = true;
            
            fetch(`/reservas/${reservaIdToCancel}/cancelar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modal.hide();
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo cancelar la reserva'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión. Intente nuevamente.');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../shared/footer.php'; ?>