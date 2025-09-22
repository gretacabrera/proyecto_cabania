<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="content-header">
            <h1 class="h3 mb-2 text-gray-800"><?= $title ?></h1>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="/marcas" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
                <div>
                    <a href="/marcas/edit/<?= $marca['id_marca'] ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar Marca
                    </a>
                </div>
            </div>
        </div>

        <!-- Información de la Marca -->
        <div class="row">
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-tag"></i> Información de la Marca
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <strong>ID:</strong>
                            </div>
                            <div class="col-sm-8">
                                #<?= htmlspecialchars($marca['id_marca']) ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <strong>Descripción:</strong>
                            </div>
                            <div class="col-sm-8">
                                <?= htmlspecialchars($marca['marca_descripcion']) ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <strong>Estado:</strong>
                            </div>
                            <div class="col-sm-8">
                                <?php if ($marca['marca_estado'] == 1): ?>
                                    <span class="badge badge-success">Activa</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactiva</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <strong>Productos:</strong>
                            </div>
                            <div class="col-sm-8">
                                <span class="badge badge-info">
                                    <?= count($productos) ?> productos asociados
                                </span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="btn-group w-100">
                                <a href="/marcas/edit/<?= $marca['id_marca'] ?>" 
                                   class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                
                                <?php if ($marca['marca_estado'] == 1): ?>
                                    <a href="/marcas/toggle/<?= $marca['id_marca'] ?>" 
                                       class="btn btn-secondary" 
                                       data-action="desactivar-marca" data-marca-id="<?= $marca['id_marca'] ?>">
                                        <i class="fas fa-eye-slash"></i> Desactivar
                                    </a>
                                <?php else: ?>
                                    <a href="/marcas/toggle/<?= $marca['id_marca'] ?>" 
                                       class="btn btn-success"
                                       data-action="activar-marca" data-marca-id="<?= $marca['id_marca'] ?>">
                                        <i class="fas fa-eye"></i> Activar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas Rápidas -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-chart-line"></i> Estadísticas
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-12 mb-2">
                                <div class="h4 mb-0 text-primary"><?= count($productos) ?></div>
                                <div class="small text-muted">Productos Asociados</div>
                            </div>
                        </div>

                        <?php if (count($productos) > 0): ?>
                            <div class="mt-3 pt-3 border-top">
                                <small class="text-muted">
                                    Esta marca está siendo utilizada en productos del sistema.
                                    <?php if ($marca['marca_estado'] == 1): ?>
                                        Para desactivarla, primero debe cambiar la marca de los productos asociados.
                                    <?php endif; ?>
                                </small>
                            </div>
                        <?php else: ?>
                            <div class="mt-3 pt-3 border-top">
                                <small class="text-success">
                                    Esta marca no tiene productos asociados y puede ser eliminada si es necesario.
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Lista de Productos Asociados -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            Productos de la Marca "<?= htmlspecialchars($marca['marca_descripcion']) ?>"
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($productos)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Esta marca no tiene productos asociados actualmente.
                            </div>
                            <div class="text-center py-4">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">
                                    No hay productos registrados para esta marca.
                                </p>
                                <a href="/productos/create" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Crear Primer Producto
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="10%">ID</th>
                                            <th width="40%">Producto</th>
                                            <th width="25%">Categoría</th>
                                            <th width="15%">Estado</th>
                                            <th width="10%">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($productos as $producto): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($producto['id_producto']) ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-box text-primary mr-2"></i>
                                                        <div>
                                                            <strong><?= htmlspecialchars($producto['producto_descripcion']) ?></strong>
                                                            <?php if (!empty($producto['producto_codigo'])): ?>
                                                                <br><small class="text-muted">
                                                                    Código: <?= htmlspecialchars($producto['producto_codigo']) ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if (!empty($producto['categoria_descripcion'])): ?>
                                                        <span class="badge badge-secondary">
                                                            <?= htmlspecialchars($producto['categoria_descripcion']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Sin categoría</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($producto['producto_estado'] == 1): ?>
                                                        <span class="badge badge-success">Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Inactivo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="/productos/show/<?= $producto['id_producto'] ?>" 
                                                       class="btn btn-sm btn-info" 
                                                       title="Ver producto">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if (count($productos) >= 20): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    Se muestran los primeros 20 productos. Para ver todos los productos de esta marca, 
                                    utilice la búsqueda avanzada en el módulo de productos.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mostrar mensajes de éxito/error -->
<?php if (isset($_SESSION['success'])): ?>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php require_once 'app/Views/layouts/footer.php'; ?>