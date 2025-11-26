<?php

/**
 * Vista: Detalle de Reserva
 * Descripción: Muestra información completa de una reserva
 * Autor: Sistema MVC
 * Fecha: 2025-11-23
 */

// Validar que existe la reserva
if (!isset($reserva) || empty($reserva)) {
    echo '<div class="alert alert-danger">Reserva no encontrada.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= $this->url('/reservas') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= $this->url('/reservas/' . $reserva['id_reserva']) . '/edit' ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Reserva
                </a>
                
                <?php if ($reserva['rela_estadoreserva'] != 6): ?>
                    <!-- Reserva no anulada: puede anular -->
                    <button class="btn btn-danger ms-2"
                        onclick="anularReserva(<?= $reserva['id_reserva'] ?>, '<?= addslashes($reserva['reserva_nro']) ?>')">
                        <i class="fas fa-ban"></i> Anular Reserva
                    </button>
                <?php endif; ?>
                
                <?php if ($reserva['rela_estadoreserva'] == 2): ?>
                    <!-- Reserva confirmada: puede marcar ingreso -->
                    <button class="btn btn-info ms-2"
                        onclick="cambiarEstado(<?= $reserva['id_reserva'] ?>, 3, 'En Curso')">
                        <i class="fas fa-play"></i> Marcar Ingreso
                    </button>
                <?php elseif ($reserva['rela_estadoreserva'] == 3): ?>
                    <!-- Reserva en curso: puede marcar salida -->
                    <button class="btn btn-warning ms-2"
                        onclick="cambiarEstado(<?= $reserva['id_reserva'] ?>, 8, 'Pendiente de Revisión')">
                        <i class="fas fa-stop"></i> Marcar Salida
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información principal -->
        <div class="col-lg-8">
            <!-- Datos básicos -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información General
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-group">
                                <i class="fas fa-hashtag text-muted"></i> N° Reserva:
                                <code><?= htmlspecialchars($reserva['reserva_nro']) ?></code>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="info-group">
                                <i class="fas fa-home text-muted"></i> Cabaña:
                                <strong><?= htmlspecialchars($reserva['cabania_nombre']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php
                                    $estadoBadges = [
                                        1 => ['color' => 'warning', 'icon' => 'clock'],
                                        2 => ['color' => 'success', 'icon' => 'check'],
                                        3 => ['color' => 'info', 'icon' => 'play'],
                                        4 => ['color' => 'warning', 'icon' => 'dollar-sign'],
                                        5 => ['color' => 'secondary', 'icon' => 'flag-checkered'],
                                        6 => ['color' => 'danger', 'icon' => 'ban'],
                                        8 => ['color' => 'warning', 'icon' => 'search']
                                    ];
                                    $badge = $estadoBadges[$reserva['rela_estadoreserva']] ?? ['color' => 'secondary', 'icon' => 'question'];
                                    ?>
                                    <i class="fas fa-tag text-muted"></i> Estado: 
                                    <span class="badge bg-<?= $badge['color'] ?> badge-lg">
                                        <i class="fas fa-<?= $badge['icon'] ?>"></i> <?= htmlspecialchars($reserva['estadoreserva_descripcion']) ?>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-user text-muted"></i> Huésped:
                                </label>
                                <div class="info-value">
                                    <?= htmlspecialchars($reserva['persona_nombre'] . ' ' . $reserva['persona_apellido']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-barcode text-muted"></i> Código Cabaña:
                                </label>
                                <div class="info-value">
                                    <?= htmlspecialchars($reserva['cabania_codigo']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Fechas y Duración -->
                <div class="card-body">
                    <h6 class="mb-3"><i class="fas fa-calendar-alt"></i> Fechas de Estadía</h6>
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="metric-box border rounded p-3">
                                <i class="fas fa-calendar-check fa-2x text-success mb-2"></i>
                                <div class="metric-label">Fecha Alta</div>
                                <div class="metric-value"><?= date('d/m/Y', strtotime($reserva['reserva_fechahora'])) ?></div>
                                <small class="text-muted"><?= date('H:i', strtotime($reserva['reserva_fechahora'])) ?></small>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="metric-box border rounded p-3">
                                <i class="fas fa-sign-in-alt fa-2x text-info mb-2"></i>
                                <div class="metric-label">Check-in</div>
                                <div class="metric-value"><?= date('d/m/Y', strtotime($reserva['reserva_fhinicio'])) ?></div>
                                <small class="text-muted"><?= date('H:i', strtotime($reserva['reserva_fhinicio'])) ?></small>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="metric-box border rounded p-3">
                                <i class="fas fa-sign-out-alt fa-2x text-warning mb-2"></i>
                                <div class="metric-label">Check-out</div>
                                <div class="metric-value"><?= date('d/m/Y', strtotime($reserva['reserva_fhfin'])) ?></div>
                                <small class="text-muted"><?= date('H:i', strtotime($reserva['reserva_fhfin'])) ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row text-center">
                        <div class="col-md-6">
                            <div class="metric-box">
                                <i class="fas fa-clock text-primary"></i>
                                <div class="metric-value text-primary">
                                    <?php
                                    $inicio = new DateTime($reserva['reserva_fhinicio']);
                                    $fin = new DateTime($reserva['reserva_fhfin']);
                                    $diff = $inicio->diff($fin);
                                    echo $diff->days . ' día' . ($diff->days != 1 ? 's' : '');
                                    ?>
                                </div>
                                <div class="metric-label">Duración</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="metric-box">
                                <i class="fas fa-users text-info"></i>
                                <div class="metric-value text-info">
                                    <?= htmlspecialchars($reserva['reserva_cantidadpersonas'] ?? 'N/D') ?>
                                </div>
                                <div class="metric-label">Personas</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Información de Contacto -->
                <div class="card-body">
                    <h6 class="mb-3"><i class="fas fa-address-card"></i> Información de Contacto</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-envelope text-muted"></i> Email:
                                </label>
                                <div class="info-value">
                                    <?= htmlspecialchars($reserva['persona_email'] ?? 'No disponible') ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-phone text-muted"></i> Teléfono:
                                </label>
                                <div class="info-value">
                                    <?= htmlspecialchars($reserva['persona_telefono'] ?? 'No disponible') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <!-- Estadísticas rápidas -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (isset($estadisticas)): ?>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="metric-box">
                                    <div class="metric-value text-primary"><?= number_format($estadisticas['total_pagos']) ?></div>
                                    <div class="metric-label">Pagos</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="metric-box">
                                    <div class="metric-value text-success">$<?= number_format($estadisticas['monto_pagado'], 0) ?></div>
                                    <div class="metric-label">Monto Pagado</div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row text-center">
                            <div class="col-6">
                                <div class="metric-box">
                                    <div class="metric-value text-info"><?= number_format($estadisticas['total_servicios']) ?></div>
                                    <div class="metric-label">Servicios</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="metric-box">
                                    <div class="metric-value text-warning"><?= number_format($estadisticas['total_consumos']) ?></div>
                                    <div class="metric-label">Consumos</div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">
                            <i class="fas fa-info-circle"></i><br>
                            No hay estadísticas disponibles
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= $this->url('/consumos?reserva=' . $reserva['id_reserva']) ?>" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-shopping-cart"></i> Ver Consumos
                        </a>
                        
                        <a href="<?= $this->url('/servicios?reserva=' . $reserva['id_reserva']) ?>" 
                           class="btn btn-outline-success btn-sm">
                            <i class="fas fa-concierge-bell"></i> Ver Servicios
                        </a>
                        
                        <hr class="my-2">
                        
                        <a href="<?= $this->url('/reservas/' . $reserva['id_reserva'] . '/print') ?>" 
                           class="btn btn-outline-secondary btn-sm" target="_blank">
                            <i class="fas fa-print"></i> Imprimir Reserva
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Función para anular reserva
function anularReserva(id, nro) {
    Swal.fire({
        title: '¿Anular reserva?',
        text: `¿Está seguro de anular la reserva ${nro}? Esta acción cambiará el estado a "Anulada".`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`<?= $this->url('/reservas/') ?>${id}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Anulada', 'La reserva ha sido anulada', 'success')
                        .then(() => window.location.reload());
                } else {
                    Swal.fire('Error', data.message || 'No se pudo anular la reserva', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Ocurrió un error al anular la reserva', 'error');
            });
        }
    });
}

// Función para cambiar estado
function cambiarEstado(id, nuevoEstado, nombreEstado) {
    Swal.fire({
        title: '¿Cambiar estado?',
        text: `¿Desea cambiar el estado de la reserva a "${nombreEstado}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`<?= $this->url('/reservas/') ?>${id}/estado`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ estado: nuevoEstado })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Actualizado', 'El estado ha sido actualizado', 'success')
                        .then(() => window.location.reload());
                } else {
                    Swal.fire('Error', data.message || 'No se pudo actualizar el estado', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Ocurrió un error al cambiar el estado', 'error');
            });
        }
    });
}
</script>
