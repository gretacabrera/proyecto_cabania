<?php

/**
 * Vista: Detalle de Caja
 * Descripción: Muestra información completa de una caja
 * Autor: Sistema MVC
 * Fecha: <?= date('Y-m-d') ?>
 */

// Validar que existe la caja
if (!isset($caja) || empty($caja)) {
    echo '<div class="alert alert-danger">Caja no encontrada.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= $this->url('/cajas') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= $this->url('/cajas/' . $caja['id_caja']) . '/edit' ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Caja
                </a>
                
                <?php if ($caja['caja_estado'] == 1): ?>
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstadoCaja(<?= $caja['id_caja'] ?>, 0, '<?= addslashes($caja['caja_descripcion']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstadoCaja(<?= $caja['id_caja'] ?>, 1, '<?= addslashes($caja['caja_descripcion']) ?>')">
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
                        <div class="col-md-8">
                            <div class="info-group">
                                <i class="fas fa-tag text-muted"></i> Descripción:
                                <strong><?= htmlspecialchars($caja['caja_descripcion']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php if ($caja['caja_estado'] == 1): ?>
                                        <i class="fas fa-toggle-on text-success"></i> Estado: 
                                        <span class="badge bg-success badge-lg">
                                            <i class="fas fa-check"></i> Activa
                                        </span>
                                    <?php else: ?>
                                        <i class="fas fa-toggle-off text-danger"></i> Estado: 
                                        <span class="badge bg-danger badge-lg">
                                            <i class="fas fa-times"></i> Inactiva
                                        </span>
                                    <?php endif; ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-user text-muted"></i> Usuario Responsable:
                                </label>
                                <div class="info-value">
                                    <?= htmlspecialchars($caja['usuario_nombre'] ?? 'Sin asignar') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <!-- Estadísticas de uso -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="metric-box">
                                <div class="metric-value text-primary"><?= number_format($estadisticas['turnos_totales']) ?></div>
                                <div class="metric-label">Turnos totales</div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="metric-box">
                                <div class="metric-value text-warning"><?= number_format($estadisticas['turnos_abiertos']) ?></div>
                                <div class="metric-label">Turnos abiertos</div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="metric-box">
                                <div class="metric-value text-info"><?= number_format($estadisticas['movimientos_totales']) ?></div>
                                <div class="metric-label">Movimientos</div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="metric-box">
                                <div class="metric-value text-success">$<?= number_format($estadisticas['monto_total_movimientos'], 2) ?></div>
                                <div class="metric-label">Saldo total</div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($estadisticas['ultimo_turno'])): ?>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <h6><i class="fas fa-clock"></i> Último Turno</h6>
                                <div class="mb-2">
                                    <small class="text-muted">Apertura:</small><br>
                                    <strong><?= date('d/m/Y H:i', strtotime($estadisticas['ultimo_turno']['apertura'])) ?></strong>
                                </div>
                                <?php if ($estadisticas['ultimo_turno']['cierre']): ?>
                                    <div>
                                        <small class="text-muted">Cierre:</small><br>
                                        <strong><?= date('d/m/Y H:i', strtotime($estadisticas['ultimo_turno']['cierre'])) ?></strong>
                                    </div>
                                <?php else: ?>
                                    <div>
                                        <span class="badge bg-warning">Turno Abierto</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= $this->url('/cajas/' . $caja['id_caja']) . '/edit' ?>" 
                           class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-edit"></i> Editar Caja
                        </a>
                        
                        <?php if ($caja['caja_estado'] == 1): ?>
                            <button class="btn btn-outline-danger btn-sm"
                                    onclick="cambiarEstadoCaja(<?= $caja['id_caja'] ?>, 0, '<?= addslashes($caja['caja_descripcion']) ?>')">
                                <i class="fas fa-ban"></i> Desactivar Caja
                            </button>
                        <?php else: ?>
                            <button class="btn btn-outline-success btn-sm"
                                    onclick="cambiarEstadoCaja(<?= $caja['id_caja'] ?>, 1, '<?= addslashes($caja['caja_descripcion']) ?>')">
                                <i class="fas fa-check"></i> Activar Caja
                            </button>
                        <?php endif; ?>

                        <hr>

                        <a href="<?= $this->url('/cajas') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver al Listado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para funcionalidades -->
<script>
function cambiarEstadoCaja(id, nuevoEstado, nombre) {
    let accion, mensaje, color;
    
    switch(nuevoEstado) {
        case 1:
            accion = 'activar';
            mensaje = 'La caja estará disponible para operar';
            color = '#28a745';
            break;
        case 0:
            accion = 'desactivar';
            mensaje = 'La caja no estará disponible';
            color = '#dc3545';
            break;
        default:
            accion = 'cambiar estado';
            mensaje = '';
            color = '#6c757d';
    }
    
    const confirmar = typeof Swal !== 'undefined' ? 
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} caja?`,
            text: `¿Está seguro que desea ${accion} la caja "${nombre}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then(result => result.isConfirmed) :
        Promise.resolve(confirm(`¿Está seguro que desea ${accion} la caja "${nombre}"?`));
    
    confirmar.then(confirmed => {
        if (confirmed) {
            const url = `<?= url('/cajas') ?>/${id}/estado`;
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({estado: nuevoEstado})
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: `Caja ${accion}da correctamente`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        alert(`Caja ${accion}da correctamente`);
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
                const errorMsg = 'Error al cambiar el estado de la caja: ' + error.message;
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

<style>
.metric-box {
    padding: 15px;
    text-align: center;
}

.metric-value {
    font-size: 2rem;
    font-weight: bold;
}

.metric-label {
    color: #6c757d;
    font-size: 0.875rem;
    margin-top: 5px;
}

.info-group {
    margin-bottom: 15px;
}

.info-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 5px;
}

.info-value {
    color: #6c757d;
}

.badge-lg {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}
</style>
