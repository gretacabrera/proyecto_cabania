<?php
/**
 * Vista: Detalle de Estado de Producto
 * Descripción: Muestra información completa de un estado de producto
 */

// Validar que existe el estado
if (!isset($estado) || empty($estado)) {
    echo '<div class="alert alert-danger">Estado de producto no encontrado.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/estados-productos') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= url('/estados-productos/' . $estado['id_estadoproducto'] . '/edit') ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Estado
                </a>
                
                <?php if ($estado['estadoproducto_estado'] == 1): ?>
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstadoEstadoProducto(<?= $estado['id_estadoproducto'] ?>, 0, '<?= addslashes($estado['estadoproducto_descripcion']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstadoEstadoProducto(<?= $estado['id_estadoproducto'] ?>, 1, '<?= addslashes($estado['estadoproducto_descripcion']) ?>')">
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
                                <strong><?= htmlspecialchars($estado['estadoproducto_descripcion']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php if ($estado['estadoproducto_estado'] == 1): ?>
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
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="metric-box">
                                <div class="metric-value text-primary">
                                    <?= number_format($estadisticas['total_productos']) ?>
                                </div>
                                <div class="metric-label">Total de Productos</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="metric-box">
                                <div class="metric-value text-info">
                                    $<?= number_format($estadisticas['productos_mes_actual'], 2) ?>
                                </div>
                                <div class="metric-label">Valor en Inventario</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="metric-box">
                                <div class="metric-value text-warning">
                                    <?= $estadisticas['porcentaje_uso'] ?>%
                                </div>
                                <div class="metric-label">% de Uso</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <!-- Acciones adicionales -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= url('/productos') ?>?estado=<?= $estado['id_estadoproducto'] ?>"
                            class="btn btn-outline-info">
                            <i class="fas fa-list"></i> Ver Productos con este Estado
                        </a>
                        <a href="<?= url('/estados-productos/exportar') ?>?estadoproducto_estado=1"
                            class="btn btn-outline-success">
                            <i class="fas fa-file-excel"></i> Exportar Todos los Estados
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para funcionalidades -->
<script>
function cambiarEstadoEstadoProducto(id, nuevoEstado, descripcion) {
    let accion, mensaje, color;
    
    if (nuevoEstado == 1) {
        accion = 'activar';
        mensaje = 'El estado estará disponible para usar en productos';
        color = '#28a745';
    } else {
        accion = 'desactivar';
        mensaje = 'El estado no estará disponible para usar en productos';
        color = '#dc3545';
    }
    
    console.log('Cambiando estado:', { id, nuevoEstado, descripcion, accion });

    const confirmar = typeof Swal !== 'undefined' ? 
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} estado?`,
            text: `¿Está seguro que desea ${accion} el estado "${descripcion}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then(result => result.isConfirmed) :
        Promise.resolve(confirm(`¿Está seguro que desea ${accion} el estado "${descripcion}"?`));
    
    confirmar.then(confirmed => {
        if (confirmed) {
            const url = `<?= url('/estados-productos') ?>/${id}/estado`;
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
                
                return response.json().then(data => {
                    if (!response.ok) {
                        throw { status: response.status, data: data };
                    }
                    return data;
                });
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: data.message || `Estado ${accion}do correctamente`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        alert(data.message || `Estado ${accion}do correctamente`);
                        location.reload();
                    }
                } else {
                    const errorMsg = data.message || 'Error desconocido';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'No se puede cambiar el estado',
                            text: errorMsg,
                            icon: 'warning',
                            confirmButtonText: 'Entendido'
                        });
                    } else {
                        alert(errorMsg);
                    }
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                
                if (error.data && error.data.message) {
                    const errorMsg = error.data.message;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'No se puede cambiar el estado',
                            text: errorMsg,
                            icon: 'warning',
                            confirmButtonText: 'Entendido'
                        });
                    } else {
                        alert(errorMsg);
                    }
                } else {
                    const errorMsg = 'Error al cambiar el estado: ' + (error.message || 'Error de conexión');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', errorMsg, 'error');
                    } else {
                        alert(errorMsg);
                    }
                }
            });
        }
    });
}
</script>
