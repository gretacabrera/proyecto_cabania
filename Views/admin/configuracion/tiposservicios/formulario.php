<?php
$title = isset($data['id_tiposervicio']) ? 'Editar Tipo de Servicio' : 'Nuevo Tipo de Servicio';
$currentModule = 'tipos_servicios';

require_once 'app/Views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><?= $title ?></h4>
                        <a href="/tipos-servicios" class="btn btn-secondary">
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
                            <label for="tiposervicio_descripcion" class="required">Descripción del Tipo de Servicio:</label>
                            <input type="text" 
                                   class="form-control <?= isset($errors) && !empty($errors) ? 'is-invalid' : '' ?>" 
                                   id="tiposervicio_descripcion" 
                                   name="tiposervicio_descripcion" 
                                   value="<?= htmlspecialchars($data['tiposervicio_descripcion'] ?? '') ?>"
                                   maxlength="100"
                                   required>
                            <small class="form-text text-muted">
                                Ingrese la descripción del tipo de servicio (máximo 100 caracteres).
                            </small>
                            <div class="invalid-feedback">
                                Por favor ingrese una descripción válida.
                            </div>
                        </div>

                        <?php if (isset($data['id_tiposervicio'])): ?>
                            <div class="form-group">
                                <label>Estado Actual:</label>
                                <div>
                                    <?php if ($data['tiposervicio_estado'] == 1): ?>
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
                                <?= isset($data['id_tiposervicio']) ? 'Actualizar Tipo' : 'Crear Tipo' ?>
                            </button>
                            <a href="/tipos-servicios" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (isset($data['id_tiposervicio'])): ?>
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
                                <?= $data['id_tiposervicio'] ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                                <strong>Estado:</strong>
                            </div>
                            <div class="col-sm-9">
                                <?php if ($data['tiposervicio_estado'] == 1): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactivo</span>
                                <?php endif; ?>
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
    initTiposServicios();
});
</script>

<?php require_once 'app/Views/layouts/footer.php'; ?>