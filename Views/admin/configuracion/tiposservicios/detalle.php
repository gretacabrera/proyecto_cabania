<?php
/**
 * Vista: Detalle de Tipo de Servicio
 * Descripción: Muestra información completa de un tipo de servicio
 */

// Validar que existe el tipo de servicio
if (!isset($tiposervicio) || empty($tiposervicio)) {
    echo '<div class="alert alert-danger">Tipo de servicio no encontrado.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/tiposservicios') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= url('/tiposservicios/' . $tiposervicio['id_tiposervicio']) . '/edit' ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Tipo de Servicio
                </a>
                
                <?php if ($tiposervicio['tiposervicio_estado'] == 1): ?>
                    <!-- Tipo de servicio activo: puede desactivar -->
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstadoTipoServicio(<?= $tiposervicio['id_tiposervicio'] ?>, 0, '<?= addslashes($tiposervicio['tiposervicio_descripcion']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <!-- Tipo de servicio inactivo: puede activar -->
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstadoTipoServicio(<?= $tiposervicio['id_tiposervicio'] ?>, 1, '<?= addslashes($tiposervicio['tiposervicio_descripcion']) ?>')">
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
                                <label class="info-label">
                                    <i class="fas fa-concierge-bell text-primary"></i> Descripción:
                                </label>
                                <div class="info-value">
                                    <strong><?= htmlspecialchars($tiposervicio['tiposervicio_descripcion']) ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php if ($tiposervicio['tiposervicio_estado'] == 1): ?>
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
            <!-- Estadísticas -->
            <?php if (isset($estadisticas)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-primary"><?= number_format($estadisticas['servicios_totales'] ?? 0) ?></div>
                                <div class="metric-label">Total de Servicios</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-success"><?= number_format($estadisticas['uso_mes_actual'] ?? 0) ?></div>
                                <div class="metric-label">Servicios del Mes</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Acciones Rápidas -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= url('/tiposservicios/' . $tiposervicio['id_tiposervicio']) . '/edit' ?>" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i> Editar Tipo
                        </a>
                        <a href="<?= url('/servicios?tipo=' . $tiposervicio['id_tiposervicio']) ?>" 
                           class="btn btn-outline-info">
                            <i class="fas fa-list"></i> Ver Servicios
                        </a>
                        <?php if ($tiposervicio['tiposervicio_estado'] == 1): ?>
                            <button class="btn btn-outline-danger"
                                onclick="cambiarEstadoTipoServicio(<?= $tiposervicio['id_tiposervicio'] ?>, 0, '<?= addslashes($tiposervicio['tiposervicio_descripcion']) ?>')">
                                <i class="fas fa-ban"></i> Desactivar
                            </button>
                        <?php else: ?>
                            <button class="btn btn-outline-success"
                                onclick="cambiarEstadoTipoServicio(<?= $tiposervicio['id_tiposervicio'] ?>, 1, '<?= addslashes($tiposervicio['tiposervicio_descripcion']) ?>')">
                                <i class="fas fa-check"></i> Activar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Función para cambiar el estado de un tipo de servicio
 */
function cambiarEstadoTipoServicio(id, nuevoEstado, descripcion) {
    const estadoTexto = nuevoEstado === 1 ? 'activar' : 'desactivar';
    const estadoLabel = nuevoEstado === 1 ? 'activo' : 'inactivo';
    
    Swal.fire({
        title: `¿${estadoTexto.charAt(0).toUpperCase() + estadoTexto.slice(1)} tipo de servicio?`,
        html: `¿Está seguro que desea ${estadoTexto} el tipo de servicio <strong>"${descripcion}"</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: nuevoEstado === 1 ? '#28a745' : '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Sí, ${estadoTexto}`,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Realizar petición AJAX
            fetch(`<?= url('/tiposservicios/') ?>${id}/estado`, {
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
                    Swal.fire({
                        title: '¡Éxito!',
                        text: data.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Ocurrió un error al procesar la solicitud', 'error');
                console.error('Error:', error);
            });
        }
    });
}
</script>

<style>
.info-group {
    margin-bottom: 1rem;
}

.info-label {
    font-weight: 500;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.info-value {
    font-size: 1.1rem;
}

.badge-lg {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

/* Estilos para las métricas */
.metric-box {
    padding: 1.5rem;
}

.metric-value {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
}

.metric-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.action-buttons .btn {
    margin-left: 0.5rem;
}

.d-grid.gap-2 > * {
    margin-bottom: 0.5rem;
}

.d-grid.gap-2 > *:last-child {
    margin-bottom: 0;
}
</style>
