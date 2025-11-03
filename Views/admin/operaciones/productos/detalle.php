<?php
/**
 * Vista: Detalle de Producto
 * Descripción: Muestra información completa de un producto
 * Autor: Sistema MVC
 * Fecha: 2025-11-03
 */

// Validar que existe el producto
if (!isset($producto) || empty($producto)) {
    echo '<div class="alert alert-danger">Producto no encontrado.</div>';
    return;
}

// Función auxiliar para clases de badges de stock
function getStockBadgeClass($stock) {
    if ($stock <= 0) return 'badge-danger';
    if ($stock <= 10) return 'badge-warning';
    return 'badge-success';
}
?>

<div class="container-fluid">
    <!-- Acciones principales -->
    <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/productos') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= url('/productos/' . $producto['id_producto'] . '/edit') ?>"
                   class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Producto
                </a>
                
                <?php if ($producto['rela_estadoproducto'] != 4): ?>
                    <!-- Producto activo: puede dar de baja -->
                    <button class="btn btn-danger ml-2"
                            onclick="cambiarEstadoProducto(<?= $producto['id_producto'] ?>, 4, '<?= addslashes($producto['producto_nombre']) ?>')">
                        <i class="fas fa-ban"></i> Dar de Baja
                    </button>
                <?php else: ?>
                    <!-- Producto dado de baja: puede reactivar -->
                    <button class="btn btn-success ml-2"
                            onclick="cambiarEstadoProducto(<?= $producto['id_producto'] ?>, 1, '<?= addslashes($producto['producto_nombre']) ?>')">
                        <i class="fas fa-check"></i> Reactivar
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
                        <div class="col-md-3">
                            <div class="info-group">
                                <i class="fas fa-barcode text-muted"></i> ID:
                                <code><?= htmlspecialchars($producto['id_producto']) ?></code>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <i class="fas fa-tag text-muted"></i> Nombre:
                                <strong><?= htmlspecialchars($producto['producto_nombre']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php 
                                    $estadoBadgeClass = 'badge-light';
                                    $estadoIcono = 'fas fa-question';
                                    $estadoIconoColor = 'text-muted';
                                    switch($producto['rela_estadoproducto']) {
                                        case 1: 
                                            $estadoBadgeClass = 'badge-success';
                                            $estadoIcono = 'fas fa-toggle-on';
                                            $estadoIconoColor = 'text-success';
                                            break;
                                        case 2: 
                                            $estadoBadgeClass = 'badge-warning';
                                            $estadoIcono = 'fas fa-exclamation-triangle';
                                            $estadoIconoColor = 'text-warning';
                                            break;
                                        case 3: 
                                            $estadoBadgeClass = 'badge-danger';
                                            $estadoIcono = 'fas fa-times-circle';
                                            $estadoIconoColor = 'text-danger';
                                            break;
                                        case 4: 
                                            $estadoBadgeClass = 'badge-secondary';
                                            $estadoIcono = 'fas fa-toggle-off';
                                            $estadoIconoColor = 'text-danger';
                                            break;
                                    }
                                    ?>
                                    <i class="<?= $estadoIcono ?> <?= $estadoIconoColor ?>"></i> Estado: 
                                    <span class="badge <?= $estadoBadgeClass ?> badge-lg">
                                        <i class="<?= str_replace('toggle-', '', str_replace('times-circle', 'times', str_replace('exclamation-triangle', 'exclamation-triangle', $estadoIcono))) ?>"></i> 
                                        <?= htmlspecialchars($producto['estadoproducto_descripcion'] ?? 'No especificado') ?>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-align-left text-muted"></i> Descripción:
                                </label>
                                <div class="info-value">
                                    <?= nl2br(htmlspecialchars($producto['producto_descripcion'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-list text-muted"></i> Categoría:
                                </label>
                                <div class="info-value">
                                    <?= htmlspecialchars($producto['categoria_descripcion'] ?? 'No especificada') ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-trademark text-muted"></i> Marca:
                                </label>
                                <div class="info-value">
                                    <?= htmlspecialchars($producto['marca_descripcion'] ?? 'No especificada') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Características -->
                <div class="card-body">
                    <div class="row">
                        <div class="text-center">
                            <i class="fas fa-boxes fa-2x text-primary"></i>
                            <?= $producto['producto_stock'] ?> En Stock
                        </div>
                        <div class="ml-4 text-center">
                            <i class="fas fa-dollar-sign fa-2x text-success"></i>
                            $<?= number_format($producto['producto_precio'], 2) ?> c/Unidad
                        </div>
                    </div>
                </div>
                <!-- Foto -->
                <?php if (!empty($producto['producto_foto']) && $producto['producto_foto'] !== 'default.jpg'): ?>
                    <div class="card-body text-center">
                        <img src="<?= url('/imagenes/productos/' . $producto['producto_foto']) ?>"
                            alt="Foto de <?= htmlspecialchars($producto['producto_nombre']) ?>"
                            class="img-fluid rounded shadow-sm" style="max-height: 400px;">
                    </div>
                <?php endif; ?>
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
                                <div class="metric-value text-primary"><?= number_format($estadisticas['consumos']['total_consumos'] ?? 0) ?></div>
                                <div class="metric-label">Ventas totales</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-success"><?= number_format($estadisticas['consumos']['cantidad_vendida'] ?? 0) ?></div>
                                <div class="metric-label">Unidades vendidas</div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-12">
                            <div class="metric-box">
                                <div class="metric-value text-warning">$<?= number_format($estadisticas['consumos']['ingresos_total'] ?? 0, 2) ?></div>
                                <div class="metric-label">Ingresos totales</div>
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
                    <div class="btn-group-vertical w-100">
                        <a href="<?= url('/productos/' . $producto['id_producto'] . '/edit') ?>"
                           class="btn btn-outline-warning mb-2">
                            <i class="fas fa-edit"></i> Editar Producto
                        </a>
                        <?php if (!empty($producto['producto_foto']) && $producto['producto_foto'] !== 'default.jpg'): ?>
                            <a href="<?= url('/imagenes/productos/' . $producto['producto_foto']) ?>"
                                target="_blank" class="btn btn-outline-secondary mb-2">
                                <i class="fas fa-eye"></i> Ver Imagen Completa
                            </a>
                        <?php endif; ?>
                        <a href="<?= url('/productos') ?>" class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> Ver Todos los Productos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Función para cambiar estado
function cambiarEstadoProducto(id, nuevoEstado, accion) {
    Swal.fire({
        title: '¿Está seguro?',
        text: `¿Desea ${accion} este producto?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?= url('/productos/') ?>' + id + '/estado', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'estado=' + nuevoEstado
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
                Swal.fire('Error', 'Ocurrió un error inesperado', 'error');
            });
        }
    });
}
</script>

<!-- JavaScript para funcionalidades -->
<script>
function cambiarEstadoProducto(id, nuevoEstado, nombre) {
    let accion, mensaje, color;
    
    switch(nuevoEstado) {
        case 1:
            accion = 'activar';
            mensaje = 'El producto estará disponible para la venta';
            color = '#28a745';
            break;
        case 4:
            accion = 'dar de baja';
            mensaje = 'El producto ya no estará disponible para la venta';
            color = '#dc3545';
            break;
        default:
            accion = 'cambiar estado de';
            mensaje = 'Se cambiará el estado del producto';
            color = '#007bff';
    }

    Swal.fire({
        title: '¿Está seguro?',
        html: `¿Desea <strong>${accion}</strong> el producto "<em>${nombre}</em>"?<br><small class="text-muted">${mensaje}</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: color,
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        focusConfirm: false,
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Procesando...',
                html: 'Actualizando estado del producto',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Realizar petición AJAX
            fetch(`<?= url('/productos/') ?>${id}/estado`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `estado=${nuevoEstado}`
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
                        // Recargar la página para mostrar los cambios
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'No se pudo actualizar el estado del producto',
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor. Verifique su conexión a internet.',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
            });
        }
    });
}
</script>

<style>
/* Estilos personalizados siguiendo el patrón de cabañas */
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