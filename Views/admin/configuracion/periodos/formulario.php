<?php
$title = isset($data['id_periodo']) ? 'Editar Periodo' : 'Nuevo Periodo';
$currentModule = 'periodos';

require_once 'app/Views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><?= $title ?></h4>
                        <a href="/periodos" class="btn btn-secondary">
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
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="periodo_descripcion" class="required">Descripción del Periodo:</label>
                                    <input type="text" 
                                           class="form-control <?= isset($errors) && !empty($errors) ? 'is-invalid' : '' ?>" 
                                           id="periodo_descripcion" 
                                           name="periodo_descripcion" 
                                           value="<?= htmlspecialchars($data['periodo_descripcion'] ?? '') ?>"
                                           maxlength="100"
                                           placeholder="Ej: Temporada Alta Verano 2024"
                                           required>
                                    <small class="form-text text-muted">
                                        Ingrese una descripción descriptiva del periodo (máximo 100 caracteres).
                                    </small>
                                    <div class="invalid-feedback">
                                        Por favor ingrese una descripción válida.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="periodo_fechainicio" class="required">Fecha de Inicio:</label>
                                    <input type="date" 
                                           class="form-control <?= isset($errors) && !empty($errors) ? 'is-invalid' : '' ?>" 
                                           id="periodo_fechainicio" 
                                           name="periodo_fechainicio" 
                                           value="<?= htmlspecialchars($data['periodo_fechainicio'] ?? '') ?>"
                                           required>
                                    <small class="form-text text-muted">
                                        Fecha de inicio del periodo.
                                    </small>
                                    <div class="invalid-feedback">
                                        Por favor seleccione una fecha de inicio válida.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="periodo_fechafin" class="required">Fecha de Fin:</label>
                                    <input type="date" 
                                           class="form-control <?= isset($errors) && !empty($errors) ? 'is-invalid' : '' ?>" 
                                           id="periodo_fechafin" 
                                           name="periodo_fechafin" 
                                           value="<?= htmlspecialchars($data['periodo_fechafin'] ?? '') ?>"
                                           required>
                                    <small class="form-text text-muted">
                                        Fecha de fin del periodo (debe ser posterior a la fecha de inicio).
                                    </small>
                                    <div class="invalid-feedback">
                                        Por favor seleccione una fecha de fin válida.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información de duración calculada -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info duration-info" id="duration-info">
                                    <i class="fas fa-info-circle"></i> <span id="duration-text"></span>
                                </div>
                            </div>
                        </div>

                        <?php if (isset($data['id_periodo'])): ?>
                            <div class="form-group">
                                <label>Estado Actual:</label>
                                <div>
                                    <?php if ($data['periodo_estado'] == 1): ?>
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
                                <?= isset($data['id_periodo']) ? 'Actualizar Periodo' : 'Crear Periodo' ?>
                            </button>
                            <a href="/periodos" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (isset($data['id_periodo'])): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle"></i> Información del Periodo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-3">
                                <strong>ID:</strong>
                            </div>
                            <div class="col-sm-9">
                                <?= $data['id_periodo'] ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                                <strong>Duración:</strong>
                            </div>
                            <div class="col-sm-9">
                                <?php
                                if (!empty($data['periodo_fechainicio']) && !empty($data['periodo_fechafin'])) {
                                    $inicio = new DateTime($data['periodo_fechainicio']);
                                    $fin = new DateTime($data['periodo_fechafin']);
                                    $duracion = $inicio->diff($fin)->days + 1;
                                    echo $duracion . ' días';
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                                <strong>Estado:</strong>
                            </div>
                            <div class="col-sm-9">
                                <?php if ($data['periodo_estado'] == 1): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactivo</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tips para periodos -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb"></i> Consejos para Gestión de Periodos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Buenas Prácticas:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Use descripciones claras y descriptivas</li>
                                    <li><i class="fas fa-check text-success"></i> No solape fechas entre periodos</li>
                                    <li><i class="fas fa-check text-success"></i> Defina periodos por temporadas</li>
                                    <li><i class="fas fa-check text-success"></i> Mantenga periodos activos actualizados</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Ejemplos de Periodos:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-sun text-warning"></i> Temporada Alta Verano</li>
                                    <li><i class="fas fa-snowflake text-info"></i> Temporada Media Invierno</li>
                                    <li><i class="fas fa-leaf text-success"></i> Temporada Baja Primavera</li>
                                    <li><i class="fas fa-calendar text-primary"></i> Fiestas y Eventos Especiales</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'app/Views/layouts/footer.php'; ?>