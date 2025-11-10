<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/condicionessalud') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= url('/condicionessalud/' . $condicion['id_condicionsalud'] . '/edit') ?>"
                   class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Condición
                </a>
                
                <?php if ($condicion['condicionsalud_estado'] == 1): ?>
                    <!-- Condición activa: puede desactivar -->
                    <button class="btn btn-danger ml-2"
                            onclick="cambiarEstado(<?= $condicion['id_condicionsalud'] ?>, 0)">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <!-- Condición inactiva: puede activar -->
                    <button class="btn btn-success ml-2"
                            onclick="cambiarEstado(<?= $condicion['id_condicionsalud'] ?>, 1)">
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
                                <i class="fas fa-heartbeat text-muted"></i> Descripción:
                                <strong><?= htmlspecialchars($condicion['condicionsalud_descripcion']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <i class="fas fa-toggle-on text-muted"></i> Estado:
                                <?php if ($condicion['condicionsalud_estado'] == 1): ?>
                                    <span class="badge badge-success">Activa</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactiva</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <!-- Estadísticas -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($estadisticas)): ?>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="metric-box">
                                    <div class="metric-value text-primary"><?= number_format($estadisticas['total_huespedes']) ?></div>
                                    <div class="metric-label">Huéspedes</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="metric-box">
                                    <div class="metric-value text-warning"><?= number_format($estadisticas['total_reservas']) ?></div>
                                    <div class="metric-label">Reservas</div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i>
                            Aún no hay estadísticas disponibles para esta condición.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Acciones Rápidas -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= url('/condicionessalud/' . $condicion['id_condicionsalud'] . '/edit') ?>" 
                           class="btn btn-outline-warning">
                            <i class="fas fa-edit"></i> Editar Condición
                        </a>
                        
                        <button type="button" 
                                class="btn btn-outline-<?= $condicion['condicionsalud_estado'] == 1 ? 'danger' : 'success' ?>"
                                onclick="cambiarEstado(<?= $condicion['id_condicionsalud'] ?>, <?= $condicion['condicionsalud_estado'] == 1 ? '0' : '1' ?>)">
                            <i class="fas fa-<?= $condicion['condicionsalud_estado'] == 1 ? 'ban' : 'check' ?>"></i>
                            <?= $condicion['condicionsalud_estado'] == 1 ? 'Desactivar' : 'Activar' ?>
                        </button>
                        
                        <a href="<?= url('/condicionessalud') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> Ver Todas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

        </div>
    </div>
</div>

<script>
function cambiarEstado(id, nuevoEstado) {
    const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
    
    Swal.fire({
        title: '¿Confirmar acción?',
        text: `¿Está seguro de que desea ${accion} esta condición de salud?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`<?= url('/condicionessalud') ?>/${id}/estado`, {
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
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.error || 'Error al cambiar el estado',
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: 'Error de conexión',
                    icon: 'error'
                });
            });
        }
    });
}
</script>

<style>
/* Estilos personalizados siguiendo el patrón de productos */
.info-group {
    margin-bottom: 0.75rem;
}

.info-group .info-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.25rem;
    display: block;
}

.info-group .info-value {
    color: #212529;
    margin-left: 1.5rem;
}

.metric-box {
    padding: 0.5rem;
}

.metric-value {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.25rem;
}

.metric-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card-title {
    font-size: 0.875rem;
    font-weight: 600;
}

.badge-lg {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
}

.btn-group-vertical .btn {
    border-radius: 0.25rem !important;
}
</style>
