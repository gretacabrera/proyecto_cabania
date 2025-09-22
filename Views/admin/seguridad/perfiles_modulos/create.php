<?php
/**
 * Vista: Crear nueva asignación perfil-módulo
 */
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle mr-2"></i>
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
                                                    <?= (old('rela_perfil') == $perfil['id_perfil']) ? 'selected' : '' ?>>
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
                                    <small class="form-text text-muted">
                                        Seleccione el perfil al que desea asignar el módulo.
                                    </small>
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
                                                    <?= (old('rela_modulo') == $modulo['id_modulo']) ? 'selected' : '' ?>>
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
                                    <small class="form-text text-muted">
                                        Seleccione el módulo que desea asignar al perfil.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <strong>Importante:</strong> 
                                    Esta acción otorgará permisos de acceso al módulo seleccionado 
                                    para todos los usuarios que tengan asignado el perfil seleccionado.
                                </div>
                            </div>
                        </div>

                        <!-- Preview de la asignación -->
                        <div id="preview" class="row preview-section">
                            <div class="col-12">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <i class="fas fa-eye mr-1"></i>
                                        Vista previa de la asignación
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Perfil seleccionado:</strong><br>
                                                <span id="preview-perfil" class="text-primary"></span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Módulo seleccionado:</strong><br>
                                                <span id="preview-modulo" class="text-success"></span>
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
                                    Crear Asignación
                                </button>
                                <button type="reset" class="btn btn-outline-secondary btn-lg ml-2">
                                    <i class="fas fa-undo mr-1"></i>
                                    Limpiar
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

<?php $this->endSection(); ?>