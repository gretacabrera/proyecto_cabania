<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="content-header">
            <h1 class="h3 mb-2 text-gray-800"><?= $title ?></h1>
            <div class="mb-4">
                <a href="/marcas" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <?= $marca ? 'Editar Marca' : 'Nueva Marca' ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <h6><i class="fas fa-exclamation-triangle"></i> Se encontraron los siguientes errores:</h6>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= $action ?>" id="marcaForm">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="marca_descripcion" class="form-label required">
                                            Descripción de la Marca:
                                        </label>
                                        <input type="text" 
                                               class="form-control <?= !empty($errors) && strpos(implode(' ', $errors), 'descripción') !== false ? 'is-invalid' : '' ?>" 
                                               id="marca_descripcion" 
                                               name="marca_descripcion" 
                                               value="<?= htmlspecialchars($marca->marca_descripcion ?? '') ?>" 
                                               maxlength="45"
                                               placeholder="Ej: Coca Cola, Nike, Samsung..."
                                               required>
                                        <small class="form-text text-muted">
                                            Máximo 45 caracteres. Este es el nombre comercial de la marca.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <?php if ($marca): ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="marca_estado" class="form-label">Estado:</label>
                                            <select class="form-control" 
                                                    id="marca_estado" 
                                                    name="marca_estado">
                                                <option value="1" <?= ($marca->marca_estado ?? 1) == 1 ? 'selected' : '' ?>>
                                                    Activo
                                                </option>
                                                <option value="0" <?= ($marca->marca_estado ?? 1) == 0 ? 'selected' : '' ?>>
                                                    Inactivo
                                                </option>
                                            </select>
                                            <small class="form-text text-muted">
                                                Solo las marcas activas estarán disponibles para nuevos productos.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="fas fa-save"></i>
                                            <?= $marca ? 'Actualizar Marca' : 'Crear Marca' ?>
                                        </button>
                                        <a href="/marcas" class="btn btn-secondary ml-2">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel de Ayuda -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-info-circle"></i> Información
                        </h6>
                    </div>
                    <div class="card-body">
                        <h6 class="text-primary">Acerca de las Marcas</h6>
                        <p class="text-muted small">
                            Las marcas identifican a los fabricantes o distribuidores de los productos 
                            en su inventario. Cada producto debe estar asociado a una marca.
                        </p>

                        <h6 class="text-primary mt-3">Consejos</h6>
                        <ul class="text-muted small">
                            <li>Use nombres comerciales reconocibles</li>
                            <li>Mantenga consistencia en la escritura</li>
                            <li>Las marcas inactivas no aparecen en productos nuevos</li>
                            <li>No se pueden eliminar marcas que tienen productos asociados</li>
                        </ul>

                        <h6 class="text-primary mt-3">Ejemplos</h6>
                        <ul class="text-muted small">
                            <li><strong>Bebidas:</strong> Coca-Cola, Pepsi, Red Bull</li>
                            <li><strong>Snacks:</strong> Pringles, Doritos, Lays</li>
                            <li><strong>Lácteos:</strong> Nestlé, La Serenísima</li>
                            <li><strong>Cerveza:</strong> Heineken, Corona, Quilmes</li>
                        </ul>
                    </div>
                </div>

                <!-- Marcas Populares -->
                <?php if ($marca): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-chart-line"></i> Información de Uso
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">
                                Esta marca está siendo utilizada en el sistema. 
                                Para ver los productos asociados, 
                                <a href="/marcas/show/<?= $marca->id_marca ?>" class="text-primary">
                                    haga clic aquí
                                </a>.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php require_once 'app/Views/layouts/footer.php'; ?>