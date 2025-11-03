<?php
/**
 * Vista: Detalle de Servicio
 * Descripción: Muestra información completa de un servicio
 * Autor: Sistema MVC
 * Fecha: <?= date('Y-m-d') ?>
 */

// Validar que existe el servicio
if (!isset($servicio) || empty($servicio)) {
    echo '<div class="alert alert-danger">Servicio no encontrado.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/servicios') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= url('/servicios/' . $servicio['id_servicio']) . '/edit' ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Servicio
                </a>
                
                <?php if ($servicio['servicio_estado'] == 1): ?>
                    <button class="btn btn-danger ml-2"
                        onclick="cambiarEstadoServicio(<?= $servicio['id_servicio'] ?>, 0, '<?= addslashes($servicio['servicio_nombre']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <button class="btn btn-success ml-2"
                        onclick="cambiarEstadoServicio(<?= $servicio['id_servicio'] ?>, 1, '<?= addslashes($servicio['servicio_nombre']) ?>')">
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
                                <i class="fas fa-concierge-bell text-muted"></i> Nombre:
                                <strong><?= htmlspecialchars($servicio['servicio_nombre']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php if ($servicio['servicio_estado'] == 1): ?>
                                        <i class="fas fa-toggle-on text-success"></i> Estado: 
                                        <span class="badge badge-success badge-lg">
                                            <i class="fas fa-check"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <i class="fas fa-toggle-off text-danger"></i> Estado: 
                                        <span class="badge badge-danger badge-lg">
                                            <i class="fas fa-times"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <br>
                    <?php if (!empty($servicio['servicio_descripcion'])): ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-align-left text-muted"></i> Descripción:
                                </label>
                                <div class="info-value">
                                    <?= nl2br(htmlspecialchars($servicio['servicio_descripcion'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-tags text-muted"></i> Tipo de Servicio:
                                </label>
                                <div class="info-value">
                                    <?php if (!empty($servicio['tiposervicio_descripcion'])): ?>
                                        <span class="badge badge-info">
                                            <?= htmlspecialchars($servicio['tiposervicio_descripcion']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Sin categorizar</span>
                                    <?php endif; ?>
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
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-primary"><?= number_format($estadisticas['total_consumos'] ?? 0) ?></div>
                                <div class="metric-label">Consumos</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-success">$<?= number_format($estadisticas['ingresos_total'] ?? 0, 0, ',', '.') ?></div>
                                <div class="metric-label">Ingresos</div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-warning"><?= number_format($estadisticas['cantidad_total'] ?? 0) ?></div>
                                <div class="metric-label">Cantidad Total Consumida</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-warning">$<?= number_format($estadisticas['ingresos_total'] / $estadisticas['total_consumos'], 2, ',', '.') ?></div>
                                <div class="metric-label">Promedio por consumo</div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>

            <!-- Panel de acciones -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-cogs"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="<?= url('/servicios/' . $servicio['id_servicio'] . '/edit') ?>" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-edit text-warning mr-2"></i>
                            Editar servicio
                        </a>
                        <button type="button" 
                                class="list-group-item list-group-item-action" 
                                onclick="cambiarEstadoServicio(<?= $servicio['id_servicio'] ?>, <?= $servicio['servicio_estado'] ? 0 : 1 ?>, '<?= addslashes($servicio['servicio_nombre']) ?>')">
                            <i class="fas fa-<?= $servicio['servicio_estado'] ? 'times text-danger' : 'check text-success' ?> mr-2"></i>
                            <?= $servicio['servicio_estado'] ? 'Desactivar' : 'Activar' ?> servicio
                        </button>
                        <a href="<?= url('/consumos/create?servicio=' . $servicio['id_servicio']) ?>" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-plus text-info mr-2"></i>
                            Registrar consumo
                        </a>
                        <a href="<?= url('/servicios') ?>" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-list text-secondary mr-2"></i>
                            Ver todos los servicios
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.content-wrapper {
    padding: 20px;
}

.page-actions {
    margin-bottom: 0;
}

.info-group {
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.info-group i {
    width: 20px;
    margin-right: 5px;
}

.info-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.25rem;
    display: block;
}

.info-value {
    color: #212529;
    margin-left: 25px;
}

.badge-lg {
    padding: 0.4em 0.8em;
    font-size: 0.8rem;
}

.stat-card {
    text-align: center;
    padding: 0.75rem 0;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    font-weight: 500;
}

.info-item {
    margin-bottom: 0.75rem;
}

.info-item strong {
    font-size: 0.8rem;
    display: block;
    margin-bottom: 0.25rem;
}

.action-buttons .btn {
    margin-left: 0.5rem;
}

@media (max-width: 768px) {
    .action-buttons {
        margin-top: 1rem;
    }
    
    .action-buttons .btn {
        margin-left: 0;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }
}
</style>

<script>
function cambiarEstadoServicio(id, nuevoEstado, nombre) {
    const accion = nuevoEstado ? 'activar' : 'desactivar';
    const mensaje = `¿Está seguro que desea ${accion} el servicio "${nombre}"?`;
    
    Swal.fire({
        title: '¿Confirmar acción?',
        text: mensaje,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: nuevoEstado ? '#28a745' : '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`<?= url('/servicios') ?>/${id}/estado`, {
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
                    Swal.fire('¡Éxito!', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo completar la acción', 'error');
            });
        }
    });
}
</script>