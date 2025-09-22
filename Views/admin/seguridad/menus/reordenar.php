<?php
/**
 * Vista para reordenar menús
 */

$title = $data['title'] ?? 'Reordenar Menús';
$menus = $data['menus'] ?? [];

?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><?php echo htmlspecialchars($title); ?></h1>
                    <p class="text-muted">Reorganizar el orden de aparición de los menús</p>
                </div>
                <div>
                    <a href="/menus" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Listado
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Instrucciones -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <div class="d-flex align-items-start">
                    <i class="fas fa-info-circle fa-2x me-3 mt-1"></i>
                    <div>
                        <h5 class="alert-heading">Instrucciones para Reordenar</h5>
                        <ul class="mb-0">
                            <li>Arrastra los menús para cambiar su orden de aparición</li>
                            <li>Los menús con menor número aparecen primero en el sistema</li>
                            <li>Solo se muestran los menús activos para reordenar</li>
                            <li>Los cambios se guardan automáticamente al soltar el elemento</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Lista de menús para reordenar -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-sort"></i>
                        Menús Activos
                        <span class="badge bg-primary ms-2"><?php echo count($menus); ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($menus)): ?>
                    <form method="POST" action="/menus/reorder" id="reorderForm">
                        <div id="sortableList" class="list-group list-group-flush">
                            <?php foreach ($menus as $index => $menu): ?>
                            <div class="list-group-item d-flex align-items-center sortable-item" 
                                 data-id="<?php echo $menu['id_menu']; ?>"
                                 data-order="<?php echo $menu['menu_orden']; ?>">
                                <div class="drag-handle me-3" title="Arrastrar para reordenar">
                                    <i class="fas fa-grip-vertical text-muted"></i>
                                </div>
                                <div class="order-badge me-3">
                                    <span class="badge bg-secondary fs-6">
                                        <?php echo $menu['menu_orden']; ?>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">
                                                <?php echo htmlspecialchars($menu['menu_nombre']); ?>
                                            </h6>
                                            <small class="text-muted">
                                                ID: #<?php echo $menu['id_menu']; ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-success">Activo</span>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="orders[<?php echo $menu['id_menu']; ?>]" 
                                       value="<?php echo $menu['menu_orden']; ?>" 
                                       class="order-input">
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="p-3 bg-light border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-outline-secondary" id="resetOrder">
                                    <i class="fas fa-undo"></i> Restaurar Orden Original
                                </button>
                                <div>
                                    <span id="changeIndicator" class="text-muted me-3 change-indicator">
                                        <i class="fas fa-exclamation-circle text-warning"></i>
                                        Hay cambios sin guardar
                                    </span>
                                    <button type="submit" class="btn btn-primary" id="saveChanges">
                                        <i class="fas fa-save"></i> Guardar Cambios
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-sort fa-3x text-muted mb-3"></i>
                        <h5>No hay menús para reordenar</h5>
                        <p class="text-muted">
                            No se encontraron menús activos para reordenar.
                        </p>
                        <a href="/menus/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Crear Nuevo Menú
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Panel de ayuda -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle"></i>
                        Ayuda para Reordenar
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-primary">
                            <i class="fas fa-mouse-pointer"></i> Cómo usar
                        </h6>
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <i class="fas fa-hand-rock text-info me-2"></i>
                                Mantén presionado el icono de agarre
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-arrows-alt text-info me-2"></i>
                                Arrastra el menú a su nueva posición
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-hand-paper text-info me-2"></i>
                                Suelta para confirmar la nueva posición
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-save text-info me-2"></i>
                                Haz clic en "Guardar Cambios"
                            </li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-warning">
                            <i class="fas fa-exclamation-triangle"></i> Importante
                        </h6>
                        <ul class="list-unstyled small text-muted">
                            <li>• Los números de orden se actualizan automáticamente</li>
                            <li>• Los menús inactivos no aparecen en esta lista</li>
                            <li>• Los cambios no se guardan hasta hacer clic en "Guardar"</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Vista previa del orden -->
            <?php if (!empty($menus)): ?>
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-eye"></i>
                        Vista Previa del Orden
                    </h6>
                </div>
                <div class="card-body">
                    <div id="previewOrder">
                        <small class="text-muted">Orden actual:</small>
                        <ol class="small mt-2" id="orderPreview">
                            <?php foreach ($menus as $menu): ?>
                            <li data-id="<?php echo $menu['id_menu']; ?>">
                                <?php echo htmlspecialchars($menu['menu_nombre']); ?>
                                <small class="text-muted">(<?php echo $menu['menu_orden']; ?>)</small>
                            </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php require_once 'Views/layouts/footer.php'; ?>