<?php
/**
 * Vista: Editar asignación perfil-módulo
 */
?>

<script>
document.body.dataset.originalPerfil = '<?= $asignacion['rela_perfil'] ?>';
document.body.dataset.originalModulo = '<?= $asignacion['rela_modulo'] ?>';
document.body.dataset.originalPerfilText = '<?= htmlspecialchars($asignacion['perfil_descripcion']) ?>';
document.body.dataset.originalModuloText = '<?= htmlspecialchars($asignacion['modulo_descripcion']) ?>';
</script>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>
                        <?= htmlspecialchars($titulo) ?>
                    </h3>
                    <div class="card-tools">
                        <a href="/perfiles-modulos" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Volver al Listado
                        </a>
                    </div>
                </div>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="card-body">
                        <?php if (isset($_SESSION['flash_message'])): ?>
                            <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show">
                                <i class="fas fa-<?= $_SESSION['flash_type'] === 'error' ? 'exclamation-triangle' : 'check-circle' ?> mr-1"></i>
                                <?= htmlspecialchars($_SESSION['flash_message']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                        <?php endif; ?>

                        <!-- Información de la asignación actual -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>Asignación actual:</strong> 
                                    <?= htmlspecialchars($asignacion['perfil_descripcion']) ?>
                                    <i class="fas fa-arrow-right mx-2"></i>
                                    <?= htmlspecialchars($asignacion['modulo_descripcion']) ?>
                                    <span class="badge badge-<?= $asignacion['perfilmodulo_estado'] ? 'success' : 'danger' ?> ml-2">
                                        <?= $asignacion['perfilmodulo_estado'] ? 'Activa' : 'Inactiva' ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rela_perfil" class="form-label required">
                                        <i class="fas fa-users mr-1"></i>
                                        Perfil:
                                    </label>
                                    <select name="rela_perfil" id="rela_perfil" 
                                            class="form-control form-control-lg" required>
                                        <option value="">Seleccione el perfil...</option>
                                        <?php foreach ($perfiles as $perfil): ?>
                                            <option value="<?= $perfil['id_perfil'] ?>" 
                                                    <?= ($asignacion['rela_perfil'] == $perfil['id_perfil']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($perfil['perfil_descripcion']) ?>
                                                <?php if ($perfil['perfil_nombre']): ?>
                                                    (<?= htmlspecialchars($perfil['perfil_nombre']) ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Por favor seleccione un perfil.
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rela_modulo" class="form-label required">
                                        <i class="fas fa-puzzle-piece mr-1"></i>
                                        Módulo:
                                    </label>
                                    <select name="rela_modulo" id="rela_modulo" 
                                            class="form-control form-control-lg" required>
                                        <option value="">Seleccione el módulo...</option>
                                        <?php foreach ($modulos as $modulo): ?>
                                            <option value="<?= $modulo['id_modulo'] ?>" 
                                                    <?= ($asignacion['rela_modulo'] == $modulo['id_modulo']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($modulo['modulo_descripcion']) ?>
                                                <?php if ($modulo['modulo_nombre']): ?>
                                                    (<?= htmlspecialchars($modulo['modulo_nombre']) ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Por favor seleccione un módulo.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <strong>Atención:</strong> 
                                    Cambiar esta asignación afectará los permisos de acceso 
                                    para todos los usuarios que tengan el perfil asignado.
                                </div>
                            </div>
                        </div>

                        <!-- Estado actual y cambios -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card border-light">
                                    <div class="card-header bg-light">
                                        <i class="fas fa-exchange-alt mr-1"></i>
                                        Estado de la asignación
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong>Estado actual:</strong><br>
                                                <span class="badge badge-<?= $asignacion['perfilmodulo_estado'] ? 'success' : 'danger' ?> badge-lg">
                                                    <i class="fas fa-<?= $asignacion['perfilmodulo_estado'] ? 'check' : 'times' ?> mr-1"></i>
                                                    <?= $asignacion['perfilmodulo_estado'] ? 'Activa' : 'Inactiva' ?>
                                                </span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>ID de asignación:</strong><br>
                                                <code>#<?= $asignacion['id_perfilmodulo'] ?></code>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Última modificación:</strong><br>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y H:i:s') ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview de cambios -->
                        <div id="changes-preview" class="row changes-preview">
                            <div class="col-12">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        ¡Hay cambios pendientes!
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Perfil:</strong><br>
                                                <span id="perfil-change"></span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Módulo:</strong><br>
                                                <span id="modulo-change"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-save mr-1"></i>
                                    Guardar Cambios
                                </button>
                                <button type="reset" class="btn btn-outline-secondary btn-lg ml-2">
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