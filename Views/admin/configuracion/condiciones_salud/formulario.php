<?php
$title = isset($data['id_condicionsalud']) ? 'Editar Condición de Salud' : 'Nueva Condición de Salud';
$currentModule = 'condiciones_salud';

require_once 'app/Views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><?= $title ?></h4>
                        <a href="/condiciones-salud" class="btn btn-secondary">
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
                            <label for="condicionsalud_descripcion" class="required">Descripción de la Condición:</label>
                            <textarea class="form-control <?= isset($errors) && !empty($errors) ? 'is-invalid' : '' ?>" 
                                      id="condicionsalud_descripcion" 
                                      name="condicionsalud_descripcion" 
                                      rows="3"
                                      maxlength="250"
                                      placeholder="Describa la condición de salud (ej: Alergia a los frutos secos, Diabetes tipo 2, etc.)"
                                      required><?= htmlspecialchars($data['condicionsalud_descripcion'] ?? '') ?></textarea>
                            <small class="form-text text-muted">
                                Describa la condición de salud de manera clara y específica (máximo 250 caracteres).
                                <span id="char-count" class="float-right">0/250</span>
                            </small>
                            <div class="invalid-feedback">
                                Por favor ingrese una descripción válida para la condición de salud.
                            </div>
                        </div>

                        <?php if (isset($data['id_condicionsalud'])): ?>
                            <div class="form-group">
                                <label>Estado Actual:</label>
                                <div>
                                    <?php if ($data['condicionsalud_estado'] == 1): ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times-circle"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <small class="form-text text-muted">
                                    Para cambiar el estado, use las opciones en el listado principal.
                                </small>
                            </div>
                        <?php endif; ?>

                        <!-- Información sobre condiciones críticas -->
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Detección de Condiciones Críticas</h6>
                            <p class="mb-2">El sistema detecta automáticamente condiciones que requieren atención especial basándose en palabras clave:</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <small>
                                        <strong>Condiciones Detectadas:</strong><br>
                                        • Alergias (alergia, alérgico)<br>
                                        • Diabetes (diabetes, diabético)<br>
                                        • Problemas cardíacos (cardíaco, corazón)<br>
                                        • Epilepsia (epilepsia, epiléptico)
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <small>
                                        <strong>También se detectan:</strong><br>
                                        • Asma (asma, asmático)<br>
                                        • Hipertensión (hipertensión, presión)<br>
                                        • Problemas renales (renal, riñón)<br>
                                        • Problemas hepáticos (hepático, hígado)
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Previsualización de la condición -->
                        <div id="preview-card" class="card bg-light" style="display: none;">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-eye"></i> Vista Previa
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div id="preview-icon" class="mr-2"></div>
                                    <div>
                                        <strong id="preview-description">-</strong>
                                        <div id="preview-warning" style="display: none;">
                                            <small class="text-warning">⚠️ Esta condición requiere atención especial</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                <?= isset($data['id_condicionsalud']) ? 'Actualizar Condición' : 'Crear Condición' ?>
                            </button>
                            <a href="/condiciones-salud" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (isset($data['id_condicionsalud'])): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle"></i> Información de la Condición
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-3">
                                <strong>ID:</strong>
                            </div>
                            <div class="col-sm-9">
                                <?= $data['id_condicionsalud'] ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                                <strong>Estado:</strong>
                            </div>
                            <div class="col-sm-9">
                                <?php if ($data['condicionsalud_estado'] == 1): ?>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> Activo
                                    </span>
                                    <small class="text-muted ml-2">Disponible para asignar a huéspedes</small>
                                <?php else: ?>
                                    <span class="badge badge-danger">
                                        <i class="fas fa-times-circle"></i> Inactivo
                                    </span>
                                    <small class="text-muted ml-2">No disponible para nuevas asignaciones</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sugerencias para condiciones de salud -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb"></i> Buenas Prácticas para Condiciones de Salud
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Descripción Efectiva:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Sea específico y claro</li>
                                    <li><i class="fas fa-check text-success"></i> Mencione el tipo y severidad</li>
                                    <li><i class="fas fa-check text-success"></i> Use terminología médica apropiada</li>
                                    <li><i class="fas fa-check text-success"></i> Evite abreviaciones confusas</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Ejemplos de Condiciones:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-heart-pulse text-danger"></i> Alergia severa a mariscos</li>
                                    <li><i class="fas fa-heart-pulse text-warning"></i> Diabetes tipo 2 controlada</li>
                                    <li><i class="fas fa-heart-pulse text-info"></i> Hipertensión arterial</li>
                                    <li><i class="fas fa-heart-pulse text-primary"></i> Asma bronquial leve</li>
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