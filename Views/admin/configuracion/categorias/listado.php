<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><?= $title ?></h3>
                    <a href="/categorias/create" class="btn btn-success">
                        <i class="fas fa-plus"></i> Nueva Categoría
                    </a>
                </div>
                <div class="card-body">
                    <!-- Formulario de búsqueda -->
                    <form method="GET" action="/categorias" class="mb-3">
                        <div class="row">
                            <div class="col-md-8">
                                <input type="text" name="buscar" class="form-control" 
                                       placeholder="Buscar por nombre o descripción..." 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">Buscar</button>
                                <?php if (!empty($search)): ?>
                                    <a href="/categorias" class="btn btn-secondary">Limpiar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>

                    <!-- Tabla de categorías -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($categorias)): ?>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <tr>
                                            <td><?= $categoria['id_categoria'] ?></td>
                                            <td><?= htmlspecialchars($categoria['categoria_nombre']) ?></td>
                                            <td><?= htmlspecialchars($categoria['categoria_descripcion'] ?? '') ?></td>
                                            <td>
                                                <span class="badge badge-<?= $categoria['categoria_estado'] == 1 ? 'success' : 'danger' ?>">
                                                    <?= $categoria['categoria_estado'] == 1 ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="/categorias/<?= $categoria['id_categoria'] ?>/edit" 
                                                       class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($categoria['categoria_estado'] == 1): ?>
                                                        <a href="/categorias/<?= $categoria['id_categoria'] ?>/delete" 
                                                           class="btn btn-sm btn-danger"
                                                           onclick="return confirm('¿Está seguro de eliminar esta categoría?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="/categorias/<?= $categoria['id_categoria'] ?>/restore" 
                                                           class="btn btn-sm btn-success"
                                                           onclick="return confirm('¿Está seguro de restaurar esta categoría?')">
                                                            <i class="fas fa-undo"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No se encontraron categorías</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Paginación">
                            <ul class="pagination justify-content-center">
                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=1<?= !empty($search) ? '&buscar=' . urlencode($search) : '' ?>">Primero</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= !empty($search) ? '&buscar=' . urlencode($search) : '' ?>">Anterior</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&buscar=' . urlencode($search) : '' ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $currentPage + 1 ?><?= !empty($search) ? '&buscar=' . urlencode($search) : '' ?>">Siguiente</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $totalPages ?><?= !empty($search) ? '&buscar=' . urlencode($search) : '' ?>">Último</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>