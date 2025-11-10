<?php
/**
 * Vista: Detalle de Método de Pago
 * Descripción: Muestra información completa de un método de pago
 */

// Validar que existe el método de pago
if (!isset($metodo) || empty($metodo)) {
    echo '<div class="alert alert-danger">Método de pago no encontrado.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/metodosdepago') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= url('/metodosdepago/' . $metodo['id_metododepago']) . '/edit' ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Método
                </a>
                
                <?php if ($metodo['metododepago_estado'] == 1): ?>
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstadoMetodo(<?= $metodo['id_metododepago'] ?>, 0, '<?= addslashes($metodo['metododepago_descripcion']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstadoMetodo(<?= $metodo['id_metododepago'] ?>, 1, '<?= addslashes($metodo['metododepago_descripcion']) ?>')">
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
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información General
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-credit-card text-muted"></i> Descripción:
                                </label>
                                <div class="info-value">
                                    <strong class="h5"><?= htmlspecialchars($metodo['metododepago_descripcion']) ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php if ($metodo['metododepago_estado'] == 1): ?>
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
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <!-- Estadísticas de uso -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Estadísticas de Uso
                    </h6>
                </div>
                <div class="card-body">
                    <div class="metric-box-compact mb-3">
                        <div class="d-flex align-items-center">
                            <div class="metric-icon-compact me-3">
                                <i class="fas fa-receipt fa-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="metric-value-compact text-primary"><?= number_format($estadisticas['total_pagos']) ?></div>
                                <div class="metric-label-compact">Total de Pagos</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="metric-box-compact mb-3">
                        <div class="d-flex align-items-center">
                            <div class="metric-icon-compact me-3">
                                <i class="fas fa-dollar-sign fa-2x text-success"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="metric-value-compact text-success">$<?= number_format($estadisticas['monto_total'], 0, '.', ',') ?></div>
                                <div class="metric-label-compact">Monto Total</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="metric-box-compact mb-3">
                        <div class="d-flex align-items-center">
                            <div class="metric-icon-compact me-3">
                                <i class="fas fa-calendar-check fa-2x text-info"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="metric-value-compact text-info"><?= number_format($estadisticas['pagos_mes_actual']) ?></div>
                                <div class="metric-label-compact">Pagos Este Mes</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="metric-box-compact">
                        <div class="d-flex align-items-center">
                            <div class="metric-icon-compact me-3">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="metric-value-compact text-warning">
                                    <?php if ($estadisticas['ultimo_uso']): ?>
                                        <?= date('d/m/Y H:i', strtotime($estadisticas['ultimo_uso'])) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </div>
                                <div class="metric-label-compact">Último Uso</div>
                            </div>
                        </div>
                    </div>
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
                        <a href="<?= url('/metodosdepago/' . $metodo['id_metododepago']) . '/edit' ?>" 
                           class="btn btn-outline-warning">
                            <i class="fas fa-edit"></i> Editar Método
                        </a>
                        
                        <?php if ($metodo['metododepago_estado'] == 1): ?>
                            <button class="btn btn-outline-danger"
                                onclick="cambiarEstadoMetodo(<?= $metodo['id_metododepago'] ?>, 0, '<?= addslashes($metodo['metododepago_descripcion']) ?>')">
                                <i class="fas fa-ban"></i> Desactivar
                            </button>
                        <?php else: ?>
                            <button class="btn btn-outline-success"
                                onclick="cambiarEstadoMetodo(<?= $metodo['id_metododepago'] ?>, 1, '<?= addslashes($metodo['metododepago_descripcion']) ?>')">
                                <i class="fas fa-check"></i> Activar
                            </button>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <a href="<?= url('/metodosdepago') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> Ver Todos los Métodos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para funcionalidades -->
<script>
function cambiarEstadoMetodo(id, nuevoEstado, descripcion) {
    const accion = nuevoEstado === 1 ? 'activar' : 'desactivar';
    const mensaje = nuevoEstado === 1 ? 
        'El método de pago estará disponible para su uso' : 
        'El método de pago no estará disponible para su uso';
    const color = nuevoEstado === 1 ? '#28a745' : '#dc3545';
    
    const confirmar = typeof Swal !== 'undefined' ? 
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} método de pago?`,
            text: `¿Está seguro que desea ${accion} "${descripcion}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then(result => result.isConfirmed) :
        Promise.resolve(confirm(`¿Está seguro que desea ${accion} el método de pago "${descripcion}"?`));
    
    confirmar.then(confirmed => {
        if (confirmed) {
            const url = `<?= url('/metodosdepago') ?>/${id}/estado`;
            
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
                            text: `Método de pago ${accion}do correctamente`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        alert(`Método de pago ${accion}do correctamente`);
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
                const errorMsg = 'Error al cambiar el estado: ' + error.message;
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
.info-group {
    margin-bottom: 20px;
}

.info-label {
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 5px;
}

.info-value {
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 5px;
}

.metric-box-compact {
    padding: 10px 0;
}

.metric-icon-compact {
    width: 50px;
    text-align: center;
}

.metric-value-compact {
    font-size: 1.5rem;
    font-weight: bold;
    line-height: 1.2;
}

.metric-label-compact {
    font-size: 0.813rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.badge-lg {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}

.action-buttons .btn {
    margin-left: 0.5rem;
}
</style>

