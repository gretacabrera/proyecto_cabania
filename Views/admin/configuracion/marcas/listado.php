<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="content-header">
            <h1 class="h3 mb-2 text-gray-800"><?= $title ?></h1>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="/marcas/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Marca
                </a>
                <div>
                    <a href="/marcas/stats" class="btn btn-info">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtros de Búsqueda -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Filtros de Búsqueda</h6>
            </div>
            <div class="card-body">
                <form method="GET" class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="marca_descripcion">Descripción:</label>
                            <input type="text" 
                                   id="marca_descripcion" 
                                   name="marca_descripcion" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($filters['marca_descripcion'] ?? '') ?>"
                                   placeholder="Buscar por descripción...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="marca_estado">Estado:</label>
                            <select id="marca_estado" name="marca_estado" class="form-control">
                                <option value="">Todos los estados</option>
                                <option value="1" <?= isset($filters['marca_estado']) && $filters['marca_estado'] == '1' ? 'selected' : '' ?>>Activos</option>
                                <option value="0" <?= isset($filters['marca_estado']) && $filters['marca_estado'] == '0' ? 'selected' : '' ?>>Inactivos</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <a href="/marcas" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Marcas -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    Marcas (<?= $total ?> registros)
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($marcas)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        No se encontraron marcas con los criterios especificados.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="50%">Descripción</th>
                                    <th width="15%">Estado</th>
                                    <th width="30%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($marcas as $marca): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($marca['id_marca']) ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-tag text-info mr-2"></i>
                                                <strong><?= htmlspecialchars($marca['marca_descripcion']) ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($marca['marca_estado'] == 1): ?>
                                                <span class="badge badge-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="/marcas/show/<?= $marca['id_marca'] ?>" 
                                                   class="btn btn-sm btn-info" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <a href="/marcas/edit/<?= $marca['id_marca'] ?>" 
                                                   class="btn btn-sm btn-warning" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <?php if ($marca['marca_estado'] == 1): ?>
                                                    <a href="/marcas/toggle/<?= $marca['id_marca'] ?>" 
                                                       class="btn btn-sm btn-secondary" 
                                                       title="Desactivar"
                                                       data-action="desactivar-marca" data-marca-id="<?= $marca['id_marca'] ?>">
                                                        <i class="fas fa-eye-slash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="/marcas/toggle/<?= $marca['id_marca'] ?>" 
                                                       class="btn btn-sm btn-success" 
                                                       title="Activar"
                                                       data-action="activar-marca" data-marca-id="<?= $marca['id_marca'] ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <?php if ($pages > 1): ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                Mostrando página <?= $current_page ?> de <?= $pages ?>
                            </div>
                            <nav>
                                <ul class="pagination mb-0">
                                    <?php if ($current_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $current_page - 1 ?><?= http_build_query($filters) ? '&' . http_build_query($filters) : '' ?>">
                                                Anterior
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = max(1, $current_page - 2); $i <= min($pages, $current_page + 2); $i++): ?>
                                        <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?><?= http_build_query($filters) ? '&' . http_build_query($filters) : '' ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($current_page < $pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $current_page + 1 ?><?= http_build_query($filters) ? '&' . http_build_query($filters) : '' ?>">
                                                Siguiente
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
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

<?php require_once 'app/Views/layouts/footer.php'; ?>