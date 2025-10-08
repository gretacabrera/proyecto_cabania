<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= $title ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= isset($usuario) ? '/usuarios/' . $usuario['id_usuario'] . '/edit' : '/usuarios/create' ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="usuario_nombre">Nombre de Usuario *</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="usuario_nombre" 
                                           name="usuario_nombre" 
                                           value="<?= htmlspecialchars($usuario['usuario_nombre'] ?? '') ?>"
                                           required 
                                           maxlength="50"
                                           <?= isset($usuario) ? '' : 'onchange="checkUsername(this.value)"' ?>>
                                    <small class="form-text text-muted">Único en el sistema</small>
                                    <div id="username-feedback"></div>
                                </div>

                                <div class="form-group">
                                    <label for="usuario_contrasenia">
                                        Contraseña <?= isset($usuario) ? '' : '*' ?>
                                    </label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="usuario_contrasenia" 
                                           name="usuario_contrasenia" 
                                           <?= isset($usuario) ? '' : 'required' ?>
                                           minlength="6">
                                    <small class="form-text text-muted">
                                        <?= isset($usuario) ? 'Dejar vacío para mantener contraseña actual' : 'Mínimo 6 caracteres' ?>
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="usuario_contrasenia_confirm">
                                        Confirmar Contraseña <?= isset($usuario) ? '' : '*' ?>
                                    </label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="usuario_contrasenia_confirm" 
                                           name="usuario_contrasenia_confirm"
                                           <?= isset($usuario) ? '' : 'required' ?>>
                                    <div id="password-feedback"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rela_perfil">Perfil *</label>
                                    <select class="form-control" id="rela_perfil" name="rela_perfil" required>
                                        <option value="">Seleccione un perfil...</option>
                                        <?php if (!empty($perfiles)): ?>
                                            <?php foreach ($perfiles as $perfil): ?>
                                                <option value="<?= $perfil['id_perfil'] ?>"
                                                        <?= (isset($usuario) && $usuario['rela_perfil'] == $perfil['id_perfil']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($perfil['perfil_nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="rela_persona">Persona *</label>
                                    <select class="form-control" id="rela_persona" name="rela_persona" required>
                                        <option value="">Seleccione una persona...</option>
                                        <?php if (!empty($personas)): ?>
                                            <?php foreach ($personas as $persona): ?>
                                                <option value="<?= $persona['id_persona'] ?>"
                                                        <?= (isset($usuario) && $usuario['rela_persona'] == $persona['id_persona']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($persona['persona_nombre'] . ' ' . $persona['persona_apellido']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="usuario_estado" 
                                               name="usuario_estado" 
                                               value="1" 
                                               <?= (!isset($usuario) || $usuario['usuario_estado'] == 1) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="usuario_estado">
                                            Usuario Activo
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> 
                                <?= isset($usuario) ? 'Actualizar' : 'Crear' ?> Usuario
                            </button>
                            <a href="/usuarios" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>