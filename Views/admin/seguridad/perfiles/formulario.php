<?php
/**
 * Vista: Formulario de Perfil
 * Descripción: Formulario para crear/editar perfiles
 * Autor: Sistema MVC
 * Fecha: 2025-11-11
 */

$isEdit = isset($perfil) && !empty($perfil);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/perfiles') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Formulario principal -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit"></i> 
                        <?= $isEdit ? 'Modificar datos del perfil' : 'Datos del nuevo perfil' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formPerfil" method="POST" 
                          action="<?= $isEdit ? url('/perfiles/' . $perfil['id_perfil'] . '/edit') : url('/perfiles/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_perfil" value="<?= $perfil['id_perfil'] ?>">
                        <?php endif; ?>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="perfil_descripcion" class="required">
                                Descripción del Perfil
                            </label>
                            <input type="text" class="form-control" id="perfil_descripcion" name="perfil_descripcion" 
                                   value="<?= htmlspecialchars($perfil['perfil_descripcion'] ?? '') ?>"
                                   required maxlength="45" placeholder="Ej: Administrador, Recepcionista, Operador...">
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">
                                Nombre descriptivo del perfil de usuario (máximo 45 caracteres)
                            </small>
                        </div>

                        <!-- Estado (solo en edición) -->
                        <?php if ($isEdit): ?>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="perfil_estado" 
                                       name="perfil_estado" value="1" 
                                       <?= ($perfil['perfil_estado'] ?? 1) == 1 ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="perfil_estado">
                                    Perfil activo
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Solo los perfiles activos pueden ser asignados a usuarios
                            </small>
                        </div>
                        <?php endif; ?>

                        <!-- Botones de acción -->
                        <div class="form-group mt-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save"></i> 
                                        <?= $isEdit ? 'Actualizar Perfil' : 'Crear Perfil' ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-lg ml-2" 
                                            onclick="limpiarFormulario()">
                                        <i class="fas fa-eraser"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel lateral con información -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información
                    </h6>
                </div>
                <div class="card-body">
                    <div class="info-section">
                        <h6><i class="fas fa-lightbulb text-warning"></i> Consejos</h6>
                        <ul class="list-unstyled small text-muted">
                            <li>Use nombres descriptivos y claros</li>
                            <li>Los perfiles definen acceso a módulos</li>
                            <li>Asigne permisos en Perfiles-Módulos</li>
                            <li>No se eliminan perfiles con usuarios activos</li>
                        </ul>
                    </div>

                    <hr>

                    <div class="info-section">
                        <h6><i class="fas fa-chart-line text-info"></i> Estadísticas</h6>
                        <br>
                        <?php if ($isEdit): ?>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value"><?= $estadisticas['usuarios_totales'] ?? 0 ?></div>
                                        <div class="stat-label small text-muted">Usuarios</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value"><?= $estadisticas['modulos_asignados'] ?? 0 ?></div>
                                        <div class="stat-label small text-muted">Módulos</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear el perfil.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Limpiar formulario
function limpiarFormulario() {
    const form = document.getElementById('formPerfil');
    form.reset();
    form.classList.remove('was-validated');
}

// Validación del formulario
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formPerfil');
    
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>
