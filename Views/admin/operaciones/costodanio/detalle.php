<?php
/**
 * Vista: Detalle de Costo por Daño
 * Descripción: Muestra información completa de un costo por daño
 */

// Validar que existe el costo
if (!isset($costo) || empty($costo)) {
    echo '<div class="alert alert-danger">Costo por daño no encontrado.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/costodanio') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= url('/costodanio/' . $costo['id_costodanio']) . '/edit' ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Costo
                </a>
                
                <?php if ($costo['costodanio_estado'] == 1): ?>
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstadoCosto(<?= $costo['id_costodanio'] ?>, 0, '<?= addslashes($costo['inventario_descripcion'] . ' - ' . $costo['niveldanio_descripcion']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstadoCosto(<?= $costo['id_costodanio'] ?>, 1, '<?= addslashes($costo['inventario_descripcion'] . ' - ' . $costo['niveldanio_descripcion']) ?>')">
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
                        <div class="col-md-5">
                            <div class="info-group">
                                <i class="fas fa-box text-muted"></i> Inventario:
                                <strong><?= htmlspecialchars($costo['inventario_descripcion']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group">
                                <i class="fas fa-exclamation-triangle text-muted"></i> Nivel de Daño:
                                <strong><?= htmlspecialchars($costo['niveldanio_descripcion']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php if ($costo['costodanio_estado'] == 1): ?>
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
                        <div class="col-md-12">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-dollar-sign text-muted"></i> Importe del Daño:
                                </label>
                                <div class="info-value">
                                    <h4 class="text-success mb-0">$<?= number_format($costo['costodanio_importe'], 2, '.', ',') ?></h4>
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
                                <div class="metric-value text-primary"><?= number_format($estadisticas['aplicaciones_mes'] ?? 0) ?></div>
                                <div class="metric-label">Aplicado mes</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-success">$<?= number_format($estadisticas['facturado_mes'] ?? 0, 0, ',', '.') ?></div>
                                <div class="metric-label">Facturado mes</div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-info"><?= number_format($estadisticas['aplicaciones_anio'] ?? 0) ?></div>
                                <div class="metric-label">Aplicado año</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-warning">$<?= number_format($estadisticas['facturado_anio'] ?? 0, 0, ',', '.') ?></div>
                                <div class="metric-label">Facturado año</div>
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
                        <a href="<?= url('/inventario/' . $costo['rela_inventario']) ?>"
                            class="btn btn-outline-primary">
                            <i class="fas fa-box"></i> Ver Inventario
                        </a>
                        <a href="<?= url('/costodanio') ?>?inventario=<?= urlencode($costo['inventario_descripcion']) ?>"
                            class="btn btn-outline-info">
                            <i class="fas fa-list"></i> Costos del Inventario
                        </a>
                        <a href="<?= url('/costodanio') ?>?niveldanio=<?= urlencode($costo['niveldanio_descripcion']) ?>"
                            class="btn btn-outline-secondary">
                            <i class="fas fa-filter"></i> Costos por Nivel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para funcionalidades -->
<script>
function cambiarEstadoCosto(id, nuevoEstado, descripcion) {
    let accion, mensaje, color;
    
    switch(nuevoEstado) {
        case 1:
            accion = 'activar';
            mensaje = 'El costo por daño estará disponible para aplicarse';
            color = '#28a745';
            break;
        case 0:
            accion = 'desactivar';
            mensaje = 'El costo por daño no estará disponible';
            color = '#dc3545';
            break;
        default:
            accion = 'cambiar estado';
            mensaje = '';
            color = '#6c757d';
    }

    // Usar SweetAlert si está disponible, sino usar confirm simple
    const confirmar = typeof Swal !== 'undefined' ? 
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} costo?`,
            text: `¿Está seguro que desea ${accion} el costo por daño "${descripcion}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then(result => result.isConfirmed) :
        Promise.resolve(confirm(`¿Está seguro que desea ${accion} el costo por daño "${descripcion}"?`));
    
    confirmar.then(confirmed => {
        if (confirmed) {
            const url = `<?= url('/costodanio') ?>/${id}/estado`;
            
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
                            text: `Costo por daño ${accion}do correctamente`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        alert(`Costo por daño ${accion}do correctamente`);
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
                const errorMsg = 'Error al cambiar el estado del costo por daño: ' + error.message;
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
