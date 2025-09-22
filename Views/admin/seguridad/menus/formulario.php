<?php
/**
 * Vista de formulario para crear/editar menús
 */

$title = $data['title'] ?? 'Menú';
$menu = $data['menu'] ?? null;
$modulos = $data['modulos'] ?? [];
$isEdit = !empty($menu);
$action = $isEdit ? "/menus/{$menu['id_menu']}/edit" : "/menus/create";

?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><?php echo htmlspecialchars($title); ?></h1>
                    <p class="text-muted">
                        <?php echo $isEdit ? 'Modificar información del menú' : 'Crear nuevo menú en el sistema'; ?>
                    </p>
                </div>
                <div>
                    <a href="/menus" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Listado
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Formulario principal -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bars"></i>
                        <?php echo $isEdit ? 'Editar Menú' : 'Nuevo Menú'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo $action; ?>" id="menuForm">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="menu_nombre" class="form-label required">
                                        Nombre del Menú
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="menu_nombre" 
                                           name="menu_nombre" 
                                           value="<?php echo htmlspecialchars($menu['menu_nombre'] ?? ''); ?>" 
                                           required 
                                           maxlength="45"
                                           placeholder="Ej: Inicio, Productos, Reportes...">
                                    <div class="form-text">
                                        Nombre que aparecerá en el menú del sistema (máximo 45 caracteres)
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="menu_orden" class="form-label required">
                                        Orden
                                    </label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="menu_orden" 
                                           name="menu_orden" 
                                           value="<?php echo $menu['menu_orden'] ?? '1'; ?>" 
                                           required 
                                           min="1" 
                                           max="999">
                                    <div class="form-text">
                                        Orden de aparición en el menú
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($isEdit): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Estado Actual</label>
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
                                    <div class="form-text">
                                        Para cambiar el estado, use las opciones del listado
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">ID del Menú</label>
                                    <div>
                                        <code class="fs-6">#<?php echo $menu['id_menu']; ?></code>
                                    </div>
                                    <div class="form-text">
                                        Identificador único del menú
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/menus" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                <?php echo $isEdit ? 'Actualizar Menú' : 'Crear Menú'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Información adicional -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle"></i>
                        Información del Menú
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-primary">
                            <i class="fas fa-lightbulb"></i> Consejos
                        </h6>
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <i class="fas fa-check text-success"></i>
                                Use nombres descriptivos y concisos
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success"></i>
                                El orden determina la posición en el menú
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success"></i>
                                Los menús pueden asociarse con módulos
                            </li>
                        </ul>
                    </div>

                    <?php if ($isEdit && !empty($menu)): ?>
                    <div class="mb-3">
                        <h6 class="text-info">
                            <i class="fas fa-chart-line"></i> Estadísticas
                        </h6>
                        <div class="small">
                            <div class="d-flex justify-content-between mb-1">
                                <span>ID:</span>
                                <code><?php echo $menu['id_menu']; ?></code>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Orden:</span>
                                <span class="badge bg-secondary"><?php echo $menu['menu_orden']; ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Estado:</span>
                                <?php if ($menu['menu_estado'] == 1): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <h6 class="text-warning">
                            <i class="fas fa-exclamation-triangle"></i> Validaciones
                        </h6>
                        <ul class="list-unstyled small text-muted">
                            <li>• El nombre debe ser único</li>
                            <li>• El orden debe ser único</li>
                            <li>• Máximo 45 caracteres para el nombre</li>
                            <li>• El orden debe ser entre 1 y 999</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <?php if ($isEdit): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-cogs"></i>
                        Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/menus/<?php echo $menu['id_menu']; ?>" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-eye"></i> Ver Detalles Completos
                        </a>
                        <?php if ($menu['menu_estado'] == 1): ?>
                            <a href="/menus/<?php echo $menu['id_menu']; ?>/delete" 
                               class="btn btn-outline-danger btn-sm"
                               data-action="desactivar-menu" data-menu-id="<?php echo $menu['id_menu']; ?>">
                                <i class="fas fa-times"></i> Desactivar Menú
                            </a>
                        <?php else: ?>
                            <a href="/menus/<?php echo $menu['id_menu']; ?>/restore" 
                               class="btn btn-outline-success btn-sm"
                               data-action="activar-menu" data-menu-id="<?php echo $menu['id_menu']; ?>">
                                <i class="fas fa-undo"></i> Reactivar Menú
                            </a>
                        <?php endif; ?>
                        <a href="/menus/reorder" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-sort"></i> Reordenar Todos
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('menuForm');
    const nombreInput = document.getElementById('menu_nombre');
    const ordenInput = document.getElementById('menu_orden');

    // Validación en tiempo real del nombre
    nombreInput.addEventListener('input', function() {
        const valor = this.value.trim();
        if (valor.length > 45) {
            this.setCustomValidity('El nombre no puede exceder 45 caracteres');
        } else if (valor.length < 2) {
            this.setCustomValidity('El nombre debe tener al menos 2 caracteres');
        } else {
            this.setCustomValidity('');
        }
    });

    // Validación del orden
    ordenInput.addEventListener('input', function() {
        const valor = parseInt(this.value);
        if (isNaN(valor) || valor < 1) {
            this.setCustomValidity('El orden debe ser un número mayor a 0');
        } else if (valor > 999) {
            this.setCustomValidity('El orden no puede ser mayor a 999');
        } else {
            this.setCustomValidity('');
        }
    });


<?php require_once 'Views/layouts/footer.php'; ?>