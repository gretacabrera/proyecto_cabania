<?php
/**
 * Vista: Detalle de Nivel de Daño
 * Descripción: Muestra información completa de un nivel de daño
 */

// Validar que existe el registro
if (!isset($registro) || empty($registro)) {
    echo '<div class="alert alert-danger">Nivel de daño no encontrado.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/niveldanio') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= url('/niveldanio/' . $registro['id_niveldanio'] . '/edit') ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Nivel de Daño
                </a>
                
                <?php if ($registro['niveldanio_estado'] == 1): ?>
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstadoNivel(<?= $registro['id_niveldanio'] ?>, 0, '<?= addslashes($registro['niveldanio_descripcion']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstadoNivel(<?= $registro['id_niveldanio'] ?>, 1, '<?= addslashes($registro['niveldanio_descripcion']) ?>')">
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
                                <i class="fas fa-align-left text-muted"></i> Descripción:
                                <strong><?= htmlspecialchars($registro['niveldanio_descripcion']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <?php if ($registro['niveldanio_estado'] == 1): ?>
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
                                <div class="metric-value text-primary"><?= number_format($estadisticas['danios_mes'] ?? 0) ?></div>
                                <div class="metric-label">Daños registrados (mes)</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-info"><?= number_format($estadisticas['danios_anio'] ?? 0) ?></div>
                                <div class="metric-label">Daños registrados (año)</div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-success">$<?= number_format($estadisticas['costos_facturados_mes'] ?? 0, 2) ?></div>
                                <div class="metric-label">Costos facturados (mes)</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-warning">$<?= number_format($estadisticas['costos_facturados_anio'] ?? 0, 2) ?></div>
                                <div class="metric-label">Costos facturados (año)</div>
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
                        <a href="<?= url('/costodanio/create') ?>?nivel=<?= $registro['id_niveldanio'] ?>"
                            class="btn btn-outline-primary">
                            <i class="fas fa-plus"></i> Registrar Costo por Daño
                        </a>
                        <a href="<?= url('/costodanio') ?>?nivel=<?= $registro['id_niveldanio'] ?>"
                            class="btn btn-outline-info">
                            <i class="fas fa-list"></i> Ver Costos de este Nivel
                        </a>
                        <a href="<?= url('/niveldanio') ?>"
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
function cambiarEstadoNivel(id, nuevoEstado, descripcion) {
    const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
    const mensaje = nuevoEstado == 1 ? 
        'El nivel de daño estará disponible para registrar incidencias' : 
        'El nivel de daño no estará disponible';
    const color = nuevoEstado == 1 ? '#28a745' : '#dc3545';
    
    const confirmar = typeof Swal !== 'undefined' ? 
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} nivel de daño?`,
            text: `¿Está seguro que desea ${accion} "${descripcion}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then(result => result.isConfirmed) :
        Promise.resolve(confirm(`¿Está seguro que desea ${accion} "${descripcion}"?`));
    
    confirmar.then(confirmed => {
        if (confirmed) {
            const url = `<?= url('/niveldanio') ?>/${id}/estado`;
            
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
                            text: `Nivel de daño ${accion}do correctamente`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        alert(`Nivel de daño ${accion}do correctamente`);
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
