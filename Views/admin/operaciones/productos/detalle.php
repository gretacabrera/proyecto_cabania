<?php 
/**
 * Vista de detalle del producto
 * Muestra información completa con estadísticas y acciones
 */
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-box mr-2"></i>
                <?= $title ?>
            </h1>
            <p class="text-muted mb-0">Información completa del producto</p>
        </div>
        <div class="btn-group">
            <a href="<?= url('/productos') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Volver al Listado
            </a>
            <a href="<?= url('/productos/' . $producto['id_producto'] . '/edit') ?>" class="btn btn-warning">
                <i class="fas fa-edit mr-1"></i>
                Editar
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información principal del producto -->
        <div class="col-lg-8">
            <!-- Datos básicos -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle mr-2"></i>
                        Información del Producto
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <?php if (!empty($producto['producto_foto'])): ?>
                                <img src="<?= url('/imagenes/productos/' . $producto['producto_foto']) ?>" 
                                     alt="<?= htmlspecialchars($producto['producto_nombre']) ?>"
                                     class="img-fluid rounded shadow"
                                     style="max-height: 250px; width: auto;">
                            <?php else: ?>
                                <div class="bg-light rounded p-4 text-muted" style="height: 200px;">
                                    <i class="fas fa-image fa-3x mb-2"></i>
                                    <p>Sin imagen</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-8">
                            <h4 class="text-primary mb-3"><?= htmlspecialchars($producto['producto_nombre']) ?></h4>
                            
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <strong>Precio:</strong>
                                    <div class="h5 text-success mt-1">$<?= number_format($producto['producto_precio'], 2) ?></div>
                                </div>
                                <div class="col-sm-6">
                                    <strong>Stock Disponible:</strong>
                                    <div class="mt-1">
                                        <span class="badge <?= getStockBadgeClass($producto['producto_stock']) ?> badge-lg">
                                            <?= $producto['producto_stock'] ?> unidades
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <strong>Categoría:</strong>
                                    <p class="mb-0"><?= htmlspecialchars($producto['categoria_descripcion'] ?? 'No especificada') ?></p>
                                </div>
                                <div class="col-sm-6">
                                    <strong>Marca:</strong>
                                    <p class="mb-0"><?= htmlspecialchars($producto['marca_descripcion'] ?? 'No especificada') ?></p>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Estado:</strong>
                                <div class="mt-1">
                                    <?php 
                                    $estadoBadgeClass = match($producto['rela_estadoproducto']) {
                                        1 => 'badge-success',  // Disponible
                                        2 => 'badge-warning',  // Stock mínimo
                                        3 => 'badge-danger',   // Sin stock
                                        4 => 'badge-secondary', // Baja
                                        default => 'badge-light'
                                    };
                                    ?>
                                    <span class="badge <?= $estadoBadgeClass ?> badge-lg">
                                        <?= htmlspecialchars($producto['estadoproducto_descripcion'] ?? 'No especificado') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div>
                        <h6 class="text-primary">Descripción</h6>
                        <p class="text-justify"><?= nl2br(htmlspecialchars($producto['producto_descripcion'])) ?></p>
                    </div>
                </div>
            </div>

            <!-- Estadísticas de consumos -->
            <?php if (isset($estadisticas['consumos'])): ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-line mr-2"></i>
                            Estadísticas de Ventas
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <i class="fas fa-shopping-cart fa-2x text-primary mb-2"></i>
                                        <h5 class="text-primary"><?= number_format($estadisticas['consumos']['total_consumos']) ?></h5>
                                        <p class="card-text">Total Ventas</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <i class="fas fa-boxes fa-2x text-info mb-2"></i>
                                        <h5 class="text-info"><?= number_format($estadisticas['consumos']['cantidad_vendida']) ?></h5>
                                        <p class="card-text">Unidades Vendidas</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card border-success">
                                    <div class="card-body">
                                        <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                                        <h5 class="text-success">$<?= number_format($estadisticas['consumos']['ingresos_total'], 2) ?></h5>
                                        <p class="card-text">Ingresos Totales</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Panel lateral de acciones -->
        <div class="col-lg-4">
            <!-- Acciones rápidas -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cogs mr-2"></i>
                        Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= url('/productos/' . $producto['id_producto'] . '/edit') ?>" 
                           class="btn btn-warning btn-block">
                            <i class="fas fa-edit mr-2"></i>
                            Editar Producto
                        </a>
                        
                        <?php if ($producto['rela_estadoproducto'] != 4): ?>
                            <button type="button" 
                                    class="btn btn-danger btn-block" 
                                    onclick="cambiarEstado(<?= $producto['id_producto'] ?>, 4, 'dar de baja')">
                                <i class="fas fa-times mr-2"></i>
                                Dar de Baja
                            </button>
                        <?php else: ?>
                            <button type="button" 
                                    class="btn btn-success btn-block" 
                                    onclick="cambiarEstado(<?= $producto['id_producto'] ?>, 1, 'reactivar')">
                                <i class="fas fa-check mr-2"></i>
                                Reactivar Producto
                            </button>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <a href="<?= url('/productos') ?>" class="btn btn-secondary btn-block">
                            <i class="fas fa-list mr-2"></i>
                            Ver Todos los Productos
                        </a>
                    </div>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info mr-2"></i>
                        Información Técnica
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td><strong>ID Producto:</strong></td>
                            <td><?= $producto['id_producto'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>Categoría ID:</strong></td>
                            <td><?= $producto['rela_categoria'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>Marca ID:</strong></td>
                            <td><?= $producto['rela_marca'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>Estado ID:</strong></td>
                            <td><?= $producto['rela_estadoproducto'] ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Alertas de inventario -->
            <?php if ($producto['producto_stock'] <= 5): ?>
                <div class="card shadow mb-4 border-warning">
                    <div class="card-header bg-warning py-3">
                        <h6 class="m-0 font-weight-bold text-white">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Alerta de Inventario
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if ($producto['producto_stock'] <= 0): ?>
                            <div class="alert alert-danger mb-0">
                                <strong>Sin Stock:</strong> Este producto no tiene unidades disponibles.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                <strong>Stock Bajo:</strong> Quedan solo <?= $producto['producto_stock'] ?> unidades disponibles.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Función para cambiar estado
function cambiarEstado(id, nuevoEstado, accion) {
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
            fetch('<?= url('/productos/') ?>' + id + '/cambiar-estado', {
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

<?php
// Función auxiliar para clases de badges de stock
function getStockBadgeClass($stock) {
    if ($stock <= 0) return 'badge-danger';
    if ($stock <= 10) return 'badge-warning';
    return 'badge-success';
}

$this->endSection();
?>