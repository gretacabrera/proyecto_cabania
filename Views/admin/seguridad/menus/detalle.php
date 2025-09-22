<?php
/**
 * Vista de detalle de menú
 */

$title = $data['title'] ?? 'Detalle del Menú';
$menu = $data['menu'] ?? null;
$modulos = $data['modulos'] ?? [];

if (!$menu) {
    header('Location: /menus');
    exit;
}

?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><?php echo htmlspecialchars($title); ?></h1>
                    <p class="text-muted">Información completa del menú</p>
                </div>
                <div>
                    <a href="/menus" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Listado
                    </a>
                    <a href="/menus/<?php echo $menu['id_menu']; ?>/edit" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Información principal -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bars"></i>
                        Información del Menú
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">ID del Menú</label>
                                <div>
                                    <code class="fs-5">#<?php echo $menu['id_menu']; ?></code>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Estado</label>
                                <div>
                                    <?php if ($menu['menu_estado'] == 1): ?>
                                        <span class="badge bg-success fs-6">
                                            <i class="fas fa-check-circle"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger fs-6">
                                            <i class="fas fa-times-circle"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label text-muted">Nombre del Menú</label>
                                <div>
                                    <h4 class="mb-0"><?php echo htmlspecialchars($menu['menu_nombre']); ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label text-muted">Orden de Aparición</label>
                                <div>
                                    <span class="badge bg-secondary fs-5">
                                        <i class="fas fa-sort-numeric-down"></i> <?php echo $menu['menu_orden']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Descripción del menú -->
                    <div class="mb-3">
                        <label class="form-label text-muted">Descripción del Sistema</label>
                        <div class="alert alert-light">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            Este menú forma parte del sistema de navegación principal. 
                            <?php if ($menu['menu_estado'] == 1): ?>
                                Actualmente está <strong>activo</strong> y visible para los usuarios con permisos correspondientes.
                            <?php else: ?>
                                Actualmente está <strong>inactivo</strong> y no es visible en la navegación.
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Módulos asociados -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cubes"></i>
                        Módulos Asociados
                        <span class="badge bg-primary ms-2"><?php echo count($modulos); ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($modulos)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre del Módulo</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modulos as $modulo): ?>
                                <tr>
                                    <td>
                                        <code>#<?php echo $modulo['id_modulo']; ?></code>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($modulo['modulo_nombre']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($modulo['modulo_estado'] == 1): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/modulos/<?php echo $modulo['id_modulo']; ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-cubes fa-3x text-muted mb-3"></i>
                        <h6>No hay módulos asociados</h6>
                        <p class="text-muted">
                            Este menú no tiene módulos asociados actualmente.
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Panel de información -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle"></i>
                        Información Técnica
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Identificador</small>
                        <div><code><?php echo $menu['id_menu']; ?></code></div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Nombre</small>
                        <div><strong><?php echo htmlspecialchars($menu['menu_nombre']); ?></strong></div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Orden</small>
                        <div><span class="badge bg-secondary"><?php echo $menu['menu_orden']; ?></span></div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Estado</small>
                        <div>
                            <?php if ($menu['menu_estado'] == 1): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactivo</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted">Módulos</small>
                        <div><span class="badge bg-primary"><?php echo count($modulos); ?></span></div>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-cogs"></i>
                        Acciones Disponibles
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/menus/<?php echo $menu['id_menu']; ?>/edit" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar Menú
                        </a>
                        <?php if ($menu['menu_estado'] == 1): ?>
                            <a href="/menus/<?php echo $menu['id_menu']; ?>/delete" 
                               class="btn btn-outline-danger"
                               data-action="desactivar-menu"
                               data-menu-id="<?php echo $menu['id_menu']; ?>">
                                <i class="fas fa-times"></i> Desactivar
                            </a>
                        <?php else: ?>
                            <a href="/menus/<?php echo $menu['id_menu']; ?>/restore" 
                               class="btn btn-outline-success"
                               data-action="activar-menu"
                               data-menu-id="<?php echo $menu['id_menu']; ?>">
                                <i class="fas fa-undo"></i> Reactivar
                            </a>
                        <?php endif; ?>
                        <hr>
                        <a href="/menus/reorder" class="btn btn-outline-info">
                            <i class="fas fa-sort"></i> Reordenar Menús
                        </a>
                        <a href="/menus/stats" class="btn btn-outline-secondary">
                            <i class="fas fa-chart-bar"></i> Ver Estadísticas
                        </a>
                    </div>
                </div>
            </div>

            <!-- Navegación -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-compass"></i>
                        Navegación Rápida
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/menus" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list"></i> Todos los Menús
                        </a>
                        <a href="/menus/create" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-plus"></i> Crear Nuevo
                        </a>
                        <a href="/menus/search" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-search"></i> Buscar Menús
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de navegación -->
    <div class="row mt-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/menus">
                            <i class="fas fa-bars"></i> Menús
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?php echo htmlspecialchars($menu['menu_nombre']); ?>
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>


<?php require_once 'Views/layouts/footer.php'; ?>