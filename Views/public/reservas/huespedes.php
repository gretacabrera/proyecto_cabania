<?php
$pageTitle = 'Huéspedes de la Reserva';
$pageStyles = ['public.css'];
require_once __DIR__ . '/../../shared/layouts/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/reservas') ?>">Mis Reservas</a></li>
                    <li class="breadcrumb-item active">Huéspedes</li>
                </ol>
            </nav>
            
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-users text-primary me-2"></i>
                    Huéspedes de la Reserva
                </h2>
                <a href="<?= url('/reservas') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver
                </a>
            </div>
            
            <!-- Información de la reserva -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información de la Reserva</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Cabaña:</strong> <?= htmlspecialchars($reserva['cabania_nombre'] ?? 'No disponible') ?>
                        </div>
                        <div class="col-md-3 mb-3">
                            <strong>Entrada:</strong> <?= date('d/m/Y', strtotime($reserva['reserva_fhinicio'])) ?>
                        </div>
                        <div class="col-md-3 mb-3">
                            <strong>Salida:</strong> <?= date('d/m/Y', strtotime($reserva['reserva_fhfin'])) ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Lista de huéspedes -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Listado de Huéspedes (<?= count($huespedes) ?>)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($huespedes)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay huéspedes registrados para esta reserva.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 py-3">Nombre Completo</th>
                                        <th class="border-0 py-3">Fecha Nacimiento</th>
                                        <th class="border-0 py-3">Contactos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($huespedes as $huesped): ?>
                                        <tr>
                                            <td class="border-0 py-3">
                                                <i class="fas fa-user text-primary me-2"></i>
                                                <strong><?= htmlspecialchars($huesped['persona_nombre'] . ' ' . $huesped['persona_apellido']) ?></strong>
                                            </td>
                                            <td class="border-0 py-3">
                                                <?= date('d/m/Y', strtotime($huesped['persona_fechanac'])) ?>
                                            </td>
                                            <td class="border-0 py-3">
                                                <?php if (!empty($huesped['contactos'])): ?>
                                                    <small><?= $huesped['contactos'] ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin contactos</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Nota informativa -->
            <div class="alert alert-warning mt-4">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Información Importante</h6>
                <p class="mb-0 small">
                    Los datos de los huéspedes son solo de lectura. Si necesitas modificar información, 
                    contacta con la recepción del complejo.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../shared/layouts/footer.php'; ?>
