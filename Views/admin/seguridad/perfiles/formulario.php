<?php
/**
 * Vista: Formulario de Perfil
 * Descripción: Formulario para crear/editar perfiles
 * Autor: Sistema MVC
 * Fecha: 2025-11-11
 */

$isEdit = isset($perfil) && !empty($perfil);
$esSistema = $isEdit && isset($perfil['perfil_sistema']) && $perfil['perfil_sistema'] == 1;
?>

<div class="content-wrapper" data-perfil-sistema="<?= $esSistema ? 'true' : 'false' ?>">
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
                            <div class="invalid-feedback">Por favor ingrese la descripción del perfil</div>
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

                        <hr class="my-4">

                        <!-- Módulos -->
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-key"></i> Módulos Asignados
                        </h6>

                        <div class="form-group">
                            <div class="asignaciones-container">
                                <?php if (!empty($modulos)): ?>
                                    <?php foreach ($modulos as $modulo): ?>
                                        <?php
                                        $idModulo = $modulo['id_modulo'];
                                        $estaSeleccionado = false;
                                        if ($isEdit && isset($modulosPerfil[$idModulo]) && $modulosPerfil[$idModulo] == 1) {
                                            $estaSeleccionado = true;
                                        }
                                        $claseEstado = $estaSeleccionado ? 'seleccionado-modulo' : 'no-seleccionado';
                                        ?>
                                        <span class="badge asignacion-badge <?= $claseEstado ?>" 
                                              data-modulo-id="<?= $idModulo ?>" 
                                              data-tipo="modulo">
                                            <?= htmlspecialchars($modulo['modulo_descripcion']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                    <!-- Inputs hidden para enviar los módulos seleccionados -->
                                    <div id="modulos-hidden-container">
                                        <?php if ($isEdit): ?>
                                            <?php foreach ($modulos as $modulo): ?>
                                                <?php if (isset($modulosPerfil[$modulo['id_modulo']]) && $modulosPerfil[$modulo['id_modulo']] == 1): ?>
                                                    <input type="hidden" name="modulos[]" value="<?= $modulo['id_modulo'] ?>" id="hidden-modulo-<?= $modulo['id_modulo'] ?>">
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted small">No hay módulos disponibles</p>
                                <?php endif; ?>
                            </div>
                            <small class="form-text text-muted d-block mt-3">
                                <i class="fas fa-info-circle me-1"></i>
                                Haga clic en los módulos que desea asignar a este perfil
                            </small>
                        </div>

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
    
    // Resetear badges de módulos
    const badges = document.querySelectorAll('.asignacion-badge[data-tipo="modulo"]');
    badges.forEach(function(badge) {
        badge.classList.remove('seleccionado-modulo');
        badge.classList.add('no-seleccionado');
    });
    
    // Limpiar inputs hidden
    const hiddenContainer = document.getElementById('modulos-hidden-container');
    if (hiddenContainer) {
        hiddenContainer.innerHTML = '';
    }
}

// Validación del formulario
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formPerfil');
    
    // Esperar a que main.js agregue sus listeners, luego removerlos
    setTimeout(function() {
        const descripcionInput = document.getElementById('perfil_descripcion');
        if (descripcionInput) {
            const clone = descripcionInput.cloneNode(true);
            descripcionInput.parentNode.replaceChild(clone, descripcionInput);
            
            // Agregar solo validación básica al clon
            clone.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                }
            });
            
            // Marcar como válido si ya tiene valor
            if (clone.value && clone.value.trim() !== '') {
                clone.classList.add('is-valid');
            }
        }
    }, 100);
    
    // Marcar otros campos que ya tienen valor como válidos (al cargar página)
    const inputs = form.querySelectorAll('input[required]:not(#perfil_descripcion), textarea[required], select[required]');
    inputs.forEach(function(input) {
        if (input.value && input.value.trim() !== '') {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
        
        input.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
            }
        });
    });
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Limpiar clases de validación previas
        form.classList.remove('was-validated');
        
        // Verificar validez del formulario
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return false;
        }
        
        // Si todo está válido, enviar
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        }
        
        // Enviar el formulario
        form.submit();
    });

    // Manejo de badges de módulos
    const badges = document.querySelectorAll('.asignacion-badge[data-tipo="modulo"]');
    const hiddenContainer = document.getElementById('modulos-hidden-container');

    badges.forEach(function(badge) {
        badge.addEventListener('click', function(e) {
            // Prevenir cualquier comportamiento de formulario
            e.preventDefault();
            e.stopPropagation();
            
            const moduloId = this.getAttribute('data-modulo-id');
            const isSelected = this.classList.contains('seleccionado-modulo');

            if (isSelected) {
                // Deseleccionar
                this.classList.remove('seleccionado-modulo');
                this.classList.add('no-seleccionado');
                // Remover input hidden
                const hiddenInput = document.getElementById('hidden-modulo-' + moduloId);
                if (hiddenInput) {
                    hiddenInput.remove();
                }
            } else {
                // Seleccionar
                this.classList.remove('no-seleccionado');
                this.classList.add('seleccionado-modulo');
                // Agregar input hidden
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'modulos[]';
                input.value = moduloId;
                input.id = 'hidden-modulo-' + moduloId;
                hiddenContainer.appendChild(input);
            }
        });
    });
});
</script>

<style>
/* Estilos específicos de perfiles (badges centralizados en main.css) */
</style>
