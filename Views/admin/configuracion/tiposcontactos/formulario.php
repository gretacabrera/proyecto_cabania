<?php
$title = isset($data['id_tipocontacto']) ? 'Editar Tipo de Contacto' : 'Nuevo Tipo de Contacto';
$currentModule = 'tipos_contactos';

require_once 'app/Views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><?= $title ?></h4>
                        <a href="/tipos-contactos" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al listado
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle"></i> Por favor corrija los siguientes errores:</h6>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <div class="form-group">
                            <label for="tipocontacto_descripcion" class="required">Descripción del Tipo de Contacto:</label>
                            <input type="text" 
                                   class="form-control <?= isset($errors) && !empty($errors) ? 'is-invalid' : '' ?>" 
                                   id="tipocontacto_descripcion" 
                                   name="tipocontacto_descripcion" 
                                   value="<?= htmlspecialchars($data['tipocontacto_descripcion'] ?? '') ?>"
                                   maxlength="100"
                                   required>
                            <small class="form-text text-muted">
                                Ingrese la descripción del tipo de contacto (máximo 100 caracteres).
                                Ejemplos: Teléfono, Email, WhatsApp, etc.
                            </small>
                            <div class="invalid-feedback">
                                Por favor ingrese una descripción válida.
                            </div>
                        </div>

                        <?php if (isset($data['id_tipocontacto'])): ?>
                            <div class="form-group">
                                <label>Estado Actual:</label>
                                <div>
                                    <?php if ($data['tipocontacto_estado'] == 1): ?>
                                        <span class="badge badge-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactivo</span>
                                    <?php endif; ?>
                                </div>
                                <small class="form-text text-muted">
                                    Para cambiar el estado, use las opciones en el listado principal.
                                </small>
                            </div>
                        <?php endif; ?>

                        <hr>
                        
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                <?= isset($data['id_tipocontacto']) ? 'Actualizar Tipo' : 'Crear Tipo' ?>
                            </button>
                            <a href="/tipos-contactos" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (isset($data['id_tipocontacto'])): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle"></i> Información Adicional
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-3">
                                <strong>ID:</strong>
                            </div>
                            <div class="col-sm-9">
                                <?= $data['id_tipocontacto'] ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                                <strong>Estado:</strong>
                            </div>
                            <div class="col-sm-9">
                                <?php if ($data['tipocontacto_estado'] == 1): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactivo</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ejemplos de uso -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb"></i> Ejemplos de Tipos de Contacto
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Tipos Comunes:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-phone text-primary"></i> Teléfono</li>
                                    <li><i class="fas fa-mobile-alt text-success"></i> Celular</li>
                                    <li><i class="fas fa-envelope text-info"></i> Email</li>
                                    <li><i class="fab fa-whatsapp text-success"></i> WhatsApp</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Tipos Adicionales:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fab fa-facebook text-primary"></i> Facebook</li>
                                    <li><i class="fab fa-instagram text-danger"></i> Instagram</li>
                                    <li><i class="fas fa-fax text-secondary"></i> Fax</li>
                                    <li><i class="fas fa-home text-warning"></i> Dirección</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>



<script src="<?= asset('assets/js/main.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initTiposContactos();
});
</script>

<?php require_once 'app/Views/layouts/footer.php'; ?>