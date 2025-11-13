<?php

/**
 * Vista: Detalle de Huésped
 * Descripción: Muestra información completa de un huésped
 * Autor: Sistema MVC
 * Fecha: 2025-11-12
 */

// Validar que existe el huésped
if (!isset($huesped) || empty($huesped)) {
    echo '<div class="alert alert-danger">Huésped no encontrado.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= $this->url('/huespedes') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= $this->url('/huespedes/' . $huesped['id_huesped']) . '/edit' ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Huésped
                </a>
                
                <?php if ($huesped['huesped_estado'] == 1): ?>
                    <!-- Huésped activo: solo puede desactivar -->
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstadoHuesped(<?= $huesped['id_huesped'] ?>, 0, '<?= addslashes($huesped['persona_nombre'] . ' ' . $huesped['persona_apellido']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <!-- Huésped inactivo: solo puede activar -->
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstadoHuesped(<?= $huesped['id_huesped'] ?>, 1, '<?= addslashes($huesped['persona_nombre'] . ' ' . $huesped['persona_apellido']) ?>')">
                        <i class="fas fa-check"></i> Activar
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
                        <div class="col-md-9">
                            <div class="info-group">
                                <i class="fas fa-user text-muted"></i> Nombre Completo:
                                <strong><?= htmlspecialchars($huesped['persona_nombre'] . ' ' . $huesped['persona_apellido']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php if ($huesped['huesped_estado'] == 1): ?>
                                        <i class="fas fa-toggle-on text-success"></i> Estado: 
                                        <span class="badge bg-success badge-lg">
                                            <i class="fas fa-check"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <i class="fas fa-toggle-off text-danger"></i> Estado: 
                                        <span class="badge bg-danger badge-lg">
                                            <i class="fas fa-times"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-calendar text-muted"></i> Fecha de Nacimiento:
                                </label>
                                <div class="info-value">
                                    <?= date('d/m/Y', strtotime($huesped['persona_fechanac'])) ?>
                                    <small class="text-muted">
                                        (<?= floor((time() - strtotime($huesped['persona_fechanac'])) / (365.25 * 24 * 60 * 60)) ?> años)
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-home text-muted"></i> Dirección:
                                </label>
                                <div class="info-value">
                                    <?= htmlspecialchars($huesped['persona_direccion']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-map-marker-alt text-muted"></i> Ubicación Actual:
                                </label>
                                <div class="info-value">
                                    <?= htmlspecialchars($huesped['huesped_ubicacion'] ?? 'No especificada') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Condiciones de Salud (equivalente a foto de cabaña) -->
                <div class="card-body">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fas fa-heartbeat"></i> Condiciones de Salud
                    </h6>
                    <div class="condiciones-container">
                        <?php 
                        $tieneCondiciones = false;
                        if (!empty($todasCondiciones) && !empty($condicionesHuesped)): 
                            foreach ($todasCondiciones as $condicion): 
                                $idCondicion = $condicion['id_condicionsalud'];
                                $tieneCondicion = isset($condicionesHuesped[$idCondicion]) && $condicionesHuesped[$idCondicion] == 1;
                                if ($tieneCondicion): 
                                    $tieneCondiciones = true;
                        ?>
                                    <span class="badge text-dark" style="margin: 5px; padding: 8px 12px; font-size: 0.875rem;">
                                        <?= htmlspecialchars($condicion['condicionsalud_descripcion']) ?>
                                    </span>
                        <?php 
                                endif;
                            endforeach;
                        endif;
                        
                        if (!$tieneCondiciones): 
                        ?>
                            <p class="text-muted mb-0">
                                <i class="fas fa-info-circle"></i> No registra condiciones de salud
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Reserva Asociada -->
                <div class="card-body">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fas fa-calendar-check"></i> Reserva Asociada
                    </h6>
                    <?php if (isset($reservaAsociada) && is_array($reservaAsociada) && isset($reservaAsociada['id_reserva'])): ?>
                        <div class="mb-0">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <strong>Reserva #<?= htmlspecialchars($reservaAsociada['id_reserva']) ?></strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="d-block">
                                        <i class="fas fa-sign-in-alt text-success"></i> <strong>Inicio:</strong>
                                        <?= date('d/m/Y H:i', strtotime($reservaAsociada['reserva_fhinicio'])) ?>
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <small class="d-block">
                                        <i class="fas fa-sign-out-alt text-danger"></i> <strong>Fin:</strong>
                                        <?= date('d/m/Y H:i', strtotime($reservaAsociada['reserva_fhfin'])) ?>
                                    </small>
                                </div>
                                <div class="col-md-12 mt-2">
                                    <a href="<?= $this->url('/reservas/' . $reservaAsociada['id_reserva']) ?>" 
                                       class="btn btn-sm btn-outline-primary w-100">
                                        <i class="fas fa-eye"></i> Ver Detalles de Reserva
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle"></i> No tiene reserva asociada actualmente
                        </p>
                    <?php endif; ?>
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
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-primary"><?= number_format($estadisticas['reservas_activas'] ?? 0) ?></div>
                                <div class="metric-label">Reservas activas</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-success"><?= number_format($estadisticas['reservas_totales'] ?? 0) ?></div>
                                <div class="metric-label">Reservas totales</div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-info">$<?= number_format($estadisticas['gasto_total'] ?? 0, 0, ',', '.') ?></div>
                                <div class="metric-label">Gasto total</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-warning">
                                    <?php if (!empty($estadisticas['ultima_reserva'])): ?>
                                        <?= date('d/m', strtotime($estadisticas['ultima_reserva'])) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </div>
                                <div class="metric-label">Última reserva</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones adicionales -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-cogs"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= $this->url('/reservas/create') ?>?huesped=<?= $huesped['id_huesped'] ?>"
                            class="btn btn-outline-primary">
                            <i class="fas fa-calendar-plus"></i> Crear Reserva
                        </a>
                        <a href="<?= $this->url('/reservas') ?>?huesped=<?= $huesped['id_huesped'] ?>"
                            class="btn btn-outline-info">
                            <i class="fas fa-list"></i> Ver Reservas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para funcionalidades -->
<script>
function cambiarEstadoHuesped(id, nuevoEstado, nombre) {
    let accion, mensaje, color;
    
    switch(nuevoEstado) {
        case 1:
            accion = 'activar';
            mensaje = 'El huésped estará activo en el sistema';
            color = '#28a745';
            break;
        case 0:
            accion = 'desactivar';
            mensaje = 'El huésped quedará inactivo';
            color = '#dc3545';
            break;
        default:
            accion = 'cambiar estado';
            mensaje = '';
            color = '#6c757d';
    }
    
    console.log('Cambiando estado:', { id, nuevoEstado, nombre, accion });

    // Usar SweetAlert si está disponible, sino usar confirm simple
    const confirmar = typeof Swal !== 'undefined' ? 
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} huésped?`,
            text: `¿Está seguro que desea ${accion} a "${nombre}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then(result => result.isConfirmed) :
        Promise.resolve(confirm(`¿Está seguro que desea ${accion} a "${nombre}"?`));
    
    confirmar.then(confirmed => {
        if (confirmed) {
            const url = `<?= $this->url('/huespedes') ?>/${id}/estado`;
            console.log('URL de petición:', url);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({estado: nuevoEstado})
            })
            .then(response => {
                console.log('Respuesta recibida:', response.status, response.statusText);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                
                if (data.success) {
                    // Usar SweetAlert para éxito si está disponible
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: `Huésped ${accion}do correctamente`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        alert(`Huésped ${accion}do correctamente`);
                        location.reload();
                    }
                } else {
                    const errorMsg = 'Error al cambiar el estado: ' + (data.message || 'Error desconocido');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', errorMsg, 'error');
                    } else {
                        alert(errorMsg);
                    }
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                const errorMsg = 'Error al cambiar el estado del huésped: ' + error.message;
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', errorMsg, 'error');
                } else {
                    alert(errorMsg);
                }
            });
        }
    });
}
</script>
