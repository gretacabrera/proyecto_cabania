<?php

/**
 * Vista: Detalle de Inventario
 * Descripción: Muestra información completa de un artículo de inventario
 * Autor: Sistema MVC
 * Fecha: 2025-11-11
 */

// Validar que existe el inventario
if (!isset($inventario) || empty($inventario)) {
    echo '<div class="alert alert-danger">Inventario no encontrado.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= $this->url('/inventario') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= $this->url('/inventario/' . $inventario['id_inventario']) . '/edit' ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Inventario
                </a>
                
                <?php if ($inventario['inventario_estado'] == 1): ?>
                    <!-- Inventario activo: puede desactivar -->
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstadoInventario(<?= $inventario['id_inventario'] ?>, 0, '<?= addslashes($inventario['inventario_descripcion']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <!-- Inventario inactivo: solo puede activar -->
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstadoInventario(<?= $inventario['id_inventario'] ?>, 1, '<?= addslashes($inventario['inventario_descripcion']) ?>')">
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
                                <strong><?= htmlspecialchars($inventario['inventario_descripcion']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php if ($inventario['inventario_estado'] == 1): ?>
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
                                    <i class="fas fa-boxes text-muted"></i> Stock Disponible:
                                </label>
                                <div class="info-value">
                                    <?= number_format($inventario['inventario_stock'], 0, '.', ',') ?> unidades
                                </div>
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
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-primary"><?= number_format($estadisticas['cabanias_asignadas']) ?></div>
                                <div class="metric-label">Cabañas asignadas</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-success"><?= number_format($estadisticas['usos_totales']) ?></div>
                                <div class="metric-label">Revisiones del mes</div>
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
                        <a href="<?= $this->url('/inventario/' . $inventario['id_inventario']) . '/edit' ?>"
                            class="btn btn-outline-warning">
                            <i class="fas fa-edit"></i> Editar Inventario
                        </a>
                        
                        <?php if ($inventario['inventario_stock'] <= 10): ?>
                            <button class="btn btn-outline-danger" onclick="alertaStockBajo()">
                                <i class="fas fa-exclamation-triangle"></i> Stock Bajo - Revisar
                            </button>
                        <?php endif; ?>
                        
                        <a href="<?= $this->url('/inventario') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> Ver Todos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para funcionalidades -->
<script>
function cambiarEstadoInventario(id, nuevoEstado, descripcion) {
    let accion, mensaje, color;
    
    switch(nuevoEstado) {
        case 1:
            accion = 'activar';
            mensaje = 'El inventario estará disponible para uso';
            color = '#28a745';
            break;
        case 0:
            accion = 'desactivar';
            mensaje = 'El inventario no estará disponible para uso';
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
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} inventario?`,
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
            const url = `<?= $this->url('/inventario') ?>/${id}/estado`;
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
                            text: `Inventario ${accion}do correctamente`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        alert(`Inventario ${accion}do correctamente`);
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
                const errorMsg = 'Error al cambiar el estado del inventario: ' + error.message;
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', errorMsg, 'error');
                } else {
                    alert(errorMsg);
                }
            });
        }
    });
}

function alertaStockBajo() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Atención!',
            text: 'El stock de este artículo está bajo. Se recomienda reabastecer pronto.',
            icon: 'warning',
            confirmButtonText: 'Entendido'
        });
    } else {
        alert('El stock de este artículo está bajo. Se recomienda reabastecer pronto.');
    }
}
</script>
