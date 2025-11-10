<?php
/**
 * Vista: Gestión masiva de permisos para un perfil
 */
?>

<script>document.body.dataset.originalAssignments = '[<?= implode(',', $modulosAsignadosIds) ?>]';</script>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-cog mr-2"></i>
                        <?= htmlspecialchars($titulo) ?>
                    </h3>
                    <div class="card-tools">
                        <a href="/perfiles-modulos" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Volver al Listado
                        </a>
                    </div>
                </div>

                <form method="POST" id="permissions-form">
                    <div class="card-body">
                        <?php if (isset($_SESSION['flash_message'])): ?>
                            <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show">
                                <i class="fas fa-<?= $_SESSION['flash_type'] === 'error' ? 'exclamation-triangle' : 'check-circle' ?> mr-1"></i>
                                <?= htmlspecialchars($_SESSION['flash_message']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                        <?php endif; ?>

                        <!-- Información del perfil -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <h4 class="mb-0">
                                                    <i class="fas fa-user-circle mr-2 text-primary"></i>
                                                    <?= htmlspecialchars($perfil['perfil_descripcion']) ?>
                                                </h4>
                                                <?php if ($perfil['perfil_nombre']): ?>
                                                    <small class="text-muted"><?= htmlspecialchars($perfil['perfil_nombre']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6 text-right">
                                                <span class="badge badge-info badge-lg">
                                                    ID: <?= $perfil['id_perfil'] ?>
                                                </span>
                                                <span class="badge badge-<?= $perfil['perfil_estado'] ? 'success' : 'danger' ?> badge-lg">
                                                    <?= $perfil['perfil_estado'] ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Controles de selección masiva -->
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-success btn-sm" data-action="seleccionar-todos-perfiles-modulos">
                                        <i class="fas fa-check-double mr-1"></i>
                                        Seleccionar Todos
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" data-action="deseleccionar-todos-perfiles-modulos">
                                        <i class="fas fa-times mr-1"></i>
                                        Deseleccionar Todos
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm" data-action="invertir-seleccion-perfiles-modulos">
                                        <i class="fas fa-exchange-alt mr-1"></i>
                                        Invertir Selección
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <span class="badge badge-secondary badge-lg">
                                    <span id="contador-seleccionados">0</span> de <?= count($todosLosModulos) ?> módulos
                                </span>
                            </div>
                        </div>

                        <!-- Lista de módulos disponibles -->
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th width="5%" class="text-center">
                                                    <input type="checkbox" id="select-all" class="form-check-input">
                                                </th>
                                                <th width="35%">
                                                    <i class="fas fa-puzzle-piece mr-1"></i>
                                                    Módulo
                                                </th>
                                                <th width="40%">
                                                    <i class="fas fa-info-circle mr-1"></i>
                                                    Descripción
                                                </th>
                                                <th width="20%" class="text-center">
                                                    <i class="fas fa-toggle-on mr-1"></i>
                                                    Estado Actual
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($todosLosModulos as $modulo): ?>
                                                <?php $isAssigned = in_array($modulo['id_modulo'], $modulosAsignadosIds); ?>
                                                <tr class="<?= $isAssigned ? 'table-success' : '' ?>">
                                                    <td class="text-center">
                                                        <input type="checkbox" 
                                                               name="modulos[]" 
                                                               value="<?= $modulo['id_modulo'] ?>"
                                                               class="form-check-input module-checkbox"
                                                               <?= $isAssigned ? 'checked' : '' ?>
                                                               id="modulo_<?= $modulo['id_modulo'] ?>">
                                                    </td>
                                                    <td>
                                                        <label for="modulo_<?= $modulo['id_modulo'] ?>" class="mb-0 cursor-pointer">
                                                            <strong><?= htmlspecialchars($modulo['modulo_nombre']) ?></strong>
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <label for="modulo_<?= $modulo['id_modulo'] ?>" class="mb-0 cursor-pointer">
                                                            <?= htmlspecialchars($modulo['modulo_descripcion']) ?>
                                                        </label>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($isAssigned): ?>
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-check mr-1"></i>
                                                                Asignado
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary">
                                                                <i class="fas fa-minus mr-1"></i>
                                                                No asignado
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen de cambios -->
                        <div id="changes-summary" class="row changes-summary">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <strong>Resumen de cambios pendientes:</strong>
                                    <div class="mt-2">
                                        <span id="modules-to-add" class="badge badge-success mr-2">0 módulos a asignar</span>
                                        <span id="modules-to-remove" class="badge badge-danger">0 módulos a desasignar</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-success btn-lg" id="save-btn">
                                    <i class="fas fa-save mr-1"></i>
                                    Guardar Permisos
                                </button>
                                <button type="button" class="btn btn-outline-warning btn-lg ml-2" data-action="reset-form-perfiles-modulos">
                                    <i class="fas fa-undo mr-1"></i>
                                    Restaurar
                                </button>
                            </div>
                            <div class="col-md-6 text-right">
                                <a href="/perfiles-modulos" class="btn btn-light btn-lg">
                                    <i class="fas fa-times mr-1"></i>
                                    Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/Views/layouts/footer.php'; ?>