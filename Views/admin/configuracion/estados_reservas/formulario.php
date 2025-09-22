<?php
$title = isset($data['id_estadoreserva']) ? 'Editar Estado de Reserva' : 'Nuevo Estado de Reserva';
$currentModule = 'estados_reservas';

require_once 'app/Views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><?= $title ?></h4>
                        <a href="/estados-reservas" class="btn btn-secondary">
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
                            <label for="estadoreserva_descripcion" class="required">Descripción del Estado:</label>
                            <input type="text" 
                                   class="form-control <?= isset($errors) && !empty($errors) ? 'is-invalid' : '' ?>" 
                                   id="estadoreserva_descripcion" 
                                   name="estadoreserva_descripcion" 
                                   value="<?= htmlspecialchars($data['estadoreserva_descripcion'] ?? '') ?>"
                                   maxlength="100"
                                   required>
                            <small class="form-text text-muted">
                                Ingrese la descripción del estado de reserva (máximo 100 caracteres).
                            </small>
                            <div class="invalid-feedback">
                                Por favor ingrese una descripción válida.
                            </div>
                        </div>

                        <?php if (isset($data['id_estadoreserva'])): ?>
                            <div class="form-group">
                                <label>Estado Actual:</label>
                                <div>
                                    <?php if ($data['estadoreserva_estado'] == 1): ?>
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
                                <?= isset($data['id_estadoreserva']) ? 'Actualizar Estado' : 'Crear Estado' ?>
                            </button>
                            <a href="/estados-reservas" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (isset($data['id_estadoreserva'])): ?>
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
                                <?= $data['id_estadoreserva'] ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                                <strong>Estado:</strong>
                            </div>
                            <div class="col-sm-9">
                                <?php if ($data['estadoreserva_estado'] == 1): ?>
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

<?php require_once 'app/Views/layouts/footer.php'; ?>