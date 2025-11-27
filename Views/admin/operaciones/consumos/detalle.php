<?php
/**
 * Vista: Detalle de Consumo
 * Descripción: Muestra información completa de un consumo
 */

// Validar que existe el consumo
if (!isset($consumo) || empty($consumo)) {
    echo '<div class="alert alert-danger">Consumo no encontrado.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/consumos') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= url('/consumos/' . $consumo['id_consumo'] . '/edit') ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Consumo
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información principal -->
        <div class="col-lg-8">
            <!-- Información General -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información General
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="info-group">
                                Reserva:
                                <a href="<?= url('/reservas/' . $consumo['rela_reserva']) ?>" class="text-primary">
                                    #<?= $consumo['rela_reserva'] ?>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="info-group">
                                Huésped:
                                <strong>
                                    <?= htmlspecialchars($consumo['huesped_nombre'] ?? 'N/A') ?>
                                    <?= htmlspecialchars($consumo['huesped_apellido'] ?? '') ?>
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="info-group">
                                <p class="mb-0"><?= nl2br(htmlspecialchars($consumo['consumo_descripcion'])) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="info-group">
                                Precio Unitario: <strong>$<?= number_format(($consumo['consumo_cantidad'] > 0 ? $consumo['consumo_total'] / $consumo['consumo_cantidad'] : 0), 2) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group">
                                Cantidad: <strong><?= number_format($consumo['consumo_cantidad'], 0) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group">
                                <i class="fas fa-dollar-sign text-muted"></i> Total:
                                <strong class="text-success">$<?= number_format($consumo['consumo_total'], 2) ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="info-group">
                                Estado:
                                <?php
                                $estadoId = $consumo['rela_estadoconsumo'] ?? 0;
                                $estadoDesc = $consumo['estadoconsumo_descripcion'] ?? 'Desconocido';
                                
                                // Colores según estado
                                $badgeClass = 'bg-secondary';
                                switch($estadoId) {
                                    case 1: // Solicitud pendiente
                                        $badgeClass = 'bg-warning text-dark';
                                        break;
                                    case 2: // En proceso
                                        $badgeClass = 'bg-info';
                                        break;
                                    case 3: // Entregado
                                        $badgeClass = 'bg-success';
                                        break;
                                    case 4: // Anulado por falta de stock
                                    case 5: // Anulado por inconveniente
                                        $badgeClass = 'bg-danger';
                                        break;
                                    case 6: // Cancelado por usuario
                                        $badgeClass = 'bg-dark';
                                        break;
                                }
                                ?>
                                <span class="badge <?= $badgeClass ?> text-white px-3 py-2">
                                    <?= htmlspecialchars($estadoDesc) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <!-- Acciones Rápidas -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= url('/reservas/' . $consumo['rela_reserva']) ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-receipt"></i> Ver Reserva
                        </a>
                        
                        <?php if ($consumo['rela_producto']): ?>
                            <a href="<?= url('/productos/' . $consumo['rela_producto']) ?>" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-box"></i> Ver Producto
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($consumo['rela_servicio']): ?>
                            <a href="<?= url('/servicios/' . $consumo['rela_servicio']) ?>" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-concierge-bell"></i> Ver Servicio
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para funcionalidades -->
<script>
function cambiarEstadoConsumo(id, nuevoEstado, descripcion) {
    let accion, mensaje, color;
    
    switch(nuevoEstado) {
        case 1:
            accion = 'activar';
            mensaje = 'El consumo estará activo en el sistema';
            color = '#28a745';
            break;
        case 0:
            accion = 'desactivar';
            mensaje = 'El consumo quedará inactivo';
            color = '#dc3545';
            break;
        default:
            accion = 'cambiar estado';
            mensaje = '';
            color = '#6c757d';
    }
    
    const confirmar = typeof Swal !== 'undefined' ? 
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} consumo?`,
            text: `¿Está seguro que desea ${accion} "${descripcion}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then((result) => {
            if (result.isConfirmed) {
                ejecutarCambioEstado(id, nuevoEstado);
            }
        }) :
        window.confirm(`¿Está seguro que desea ${accion} este consumo?`);
    
    if (confirmar && typeof Swal === 'undefined') {
        ejecutarCambioEstado(id, nuevoEstado);
    }
}

function ejecutarCambioEstado(id, nuevoEstado) {
    fetch(`<?= url('/consumos/') ?>${id}/estado`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ estado: nuevoEstado })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Estado actualizado!',
                    text: data.message || 'El estado del consumo se ha actualizado correctamente',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                alert(data.message || 'Estado actualizado correctamente');
                window.location.reload();
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', data.message || 'No se pudo actualizar el estado', 'error');
            } else {
                alert('Error: ' + (data.message || 'No se pudo actualizar el estado'));
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error', 'Hubo un problema al actualizar el estado', 'error');
        } else {
            alert('Error al actualizar el estado');
        }
    });
}
</script>