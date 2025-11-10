<?php

/**
 * Vista: Detalle de Periodo
 * Descripción: Muestra información completa de un periodo
 * Autor: Sistema MVC
 * Fecha: 2025-11-10
 */

// Validar que existe el periodo
if (!isset($periodo) || empty($periodo)) {
    echo '<div class="alert alert-danger">Periodo no encontrado.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= $this->url('/periodos') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= $this->url('/periodos/' . $periodo['id_periodo']) . '/edit' ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Periodo
                </a>
                
                <?php if ($periodo['periodo_estado'] == 1): ?>
                    <!-- Periodo activo: puede desactivar -->
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstadoPeriodo(<?= $periodo['id_periodo'] ?>, 0, '<?= addslashes($periodo['periodo_descripcion']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <!-- Periodo inactivo: solo puede activar -->
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstadoPeriodo(<?= $periodo['id_periodo'] ?>, 1, '<?= addslashes($periodo['periodo_descripcion']) ?>')">
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
                        <div class="col-md-6">
                            <div class="info-group">
                                <i class="fas fa-tag text-muted"></i> Descripción:
                                <strong><?= htmlspecialchars($periodo['periodo_descripcion']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <i class="fas fa-calendar-alt text-muted"></i> Año:
                                <strong><?= $periodo['periodo_anio'] ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php if ($periodo['periodo_estado'] == 1): ?>
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
                        <div class="col-md-4">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-calendar-day text-muted"></i> Fecha de Inicio:
                                </label>
                                <div class="info-value">
                                    <?= date('d/m/Y', strtotime($periodo['periodo_fechainicio'])) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-calendar-check text-muted"></i> Fecha de Fin:
                                </label>
                                <div class="info-value">
                                    <?= date('d/m/Y', strtotime($periodo['periodo_fechafin'])) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-sort-numeric-down text-muted"></i> Orden de Visualización:
                                </label>
                                <div class="info-value">
                                    <?= $periodo['periodo_orden'] ?>
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
                                <div class="metric-value text-primary"><?= number_format($estadisticas['total_reservas']) ?></div>
                                <div class="metric-label">Total reservas</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-success">$<?= number_format($estadisticas['ingresos_generados'], 0, ',', '.') ?></div>
                                <div class="metric-label">Ingresos generados</div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-info"><?= $estadisticas['duracion_dias'] ?></div>
                                <div class="metric-label">Días de duración</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-warning"><?= number_format($estadisticas['ocupacion_promedio'], 1) ?>%</div>
                                <div class="metric-label">Ocupación promedio</div>
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
                        <a href="<?= $this->url('/reservas') ?>?periodo=<?= $periodo['id_periodo'] ?>"
                            class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> Ver Reservas del Periodo
                        </a>
                        <a href="<?= $this->url('/reportes') ?>?periodo=<?= $periodo['id_periodo'] ?>"
                            class="btn btn-outline-info">
                            <i class="fas fa-chart-line"></i> Generar Reporte
                        </a>
                        <a href="<?= $this->url('/periodos') ?>"
                            class="btn btn-outline-secondary">
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
function cambiarEstadoPeriodo(id, nuevoEstado, descripcion) {
    let accion, mensaje, color;
    
    switch(nuevoEstado) {
        case 1:
            accion = 'activar';
            mensaje = 'El periodo estará disponible para ser asignado a reservas';
            color = '#28a745';
            break;
        case 0:
            accion = 'desactivar';
            mensaje = 'El periodo no estará disponible para nuevas reservas';
            color = '#dc3545';
            break;
        default:
            accion = 'cambiar estado';
            mensaje = '';
            color = '#6c757d';
    }
    
    console.log('Cambiando estado:', { id, nuevoEstado, descripcion, accion });

    // Usar SweetAlert si está disponible, sino usar confirm simple
    const confirmar = typeof Swal !== 'undefined' ? 
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} periodo?`,
            text: `¿Está seguro que desea ${accion} el periodo "${descripcion}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then(result => result.isConfirmed) :
        Promise.resolve(confirm(`¿Está seguro que desea ${accion} el periodo "${descripcion}"?`));
    
    confirmar.then(confirmed => {
        if (confirmed) {
            const url = `<?= $this->url('/periodos') ?>/${id}/estado`;
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
                            text: `Periodo ${accion}do correctamente`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        alert(`Periodo ${accion}do correctamente`);
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
                const errorMsg = 'Error al cambiar el estado del periodo: ' + error.message;
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
