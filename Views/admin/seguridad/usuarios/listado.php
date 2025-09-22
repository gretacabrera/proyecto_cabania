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
                    <a href="/usuarios/create" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Nuevo Usuario
                    </a>
                </div>
                <div class="card-body">
                    <!-- Formulario de búsqueda -->
                    <form method="GET" action="/usuarios" class="mb-3">
                        <div class="row">
                            <div class="col-md-8">
                                <input type="text" name="buscar" class="form-control" 
                                       placeholder="Buscar por nombre de usuario..." 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">Buscar</button>
                                <?php if (!empty($search)): ?>
                                    <a href="/usuarios" class="btn btn-secondary">Limpiar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>

                    <!-- Tabla de usuarios -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Persona</th>
                                    <th>Perfil</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($usuarios)): ?>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td><?= $usuario['id_usuario'] ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($usuario['usuario_nombre']) ?></strong>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars(($usuario['persona_nombre'] ?? '') . ' ' . ($usuario['persona_apellido'] ?? '')) ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= htmlspecialchars($usuario['perfil_nombre'] ?? 'Sin Perfil') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $usuario['usuario_estado'] == 1 ? 'success' : 'danger' ?>">
                                                    <?= $usuario['usuario_estado'] == 1 ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="/usuarios/<?= $usuario['id_usuario'] ?>/profile" 
                                                       class="btn btn-sm btn-info"
                                                       title="Ver Perfil">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="/usuarios/<?= $usuario['id_usuario'] ?>/edit" 
                                                       class="btn btn-sm btn-warning"
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="/usuarios/<?= $usuario['id_usuario'] ?>/toggle-status" 
                                                       class="btn btn-sm btn-<?= $usuario['usuario_estado'] == 1 ? 'secondary' : 'success' ?>"
                                                       onclick="return confirm('¿Está seguro de <?= $usuario['usuario_estado'] == 1 ? 'desactivar' : 'activar' ?> este usuario?')"
                                                       title="<?= $usuario['usuario_estado'] == 1 ? 'Desactivar' : 'Activar' ?>">
                                                        <i class="fas fa-<?= $usuario['usuario_estado'] == 1 ? 'ban' : 'check' ?>"></i>
                                                    </a>
                                                    <?php if ($usuario['usuario_estado'] == 0): ?>
                                                        <a href="/usuarios/<?= $usuario['id_usuario'] ?>/delete" 
                                                           class="btn btn-sm btn-danger"
                                                           onclick="return confirm('¿Está seguro de eliminar este usuario? Esta acción no se puede deshacer.')"
                                                           title="Eliminar Definitivamente">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No se encontraron usuarios</td>
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