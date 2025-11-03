<?php
/**
 * Vista: Formulario de Condición de Salud
 * Descripción: Formulario para crear/editar condiciones de salud
 * Autor: Sistema MVC
 * Fecha: 2025-11-03
 */

$isEdit = isset($condicion) && !empty($condicion);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/condiciones_salud') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos de la condición' : 'Datos de la nueva condición' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formCondicionSalud" method="POST" 
                          action="<?= $isEdit ? url('/condiciones_salud/' . $condicion['id_condicionsalud'] . '/edit') : url('/condiciones_salud/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_condicionsalud" value="<?= $condicion['id_condicionsalud'] ?>">
                        <?php endif; ?>

                        <!-- Descripción de la condición -->
                        <div class="form-group">
                            <label for="condicionsalud_descripcion" class="required">
                                <i class="fas fa-heartbeat"></i> Descripción de la Condición
                            </label>
                            <textarea class="form-control" id="condicionsalud_descripcion" name="condicionsalud_descripcion" 
                                      rows="4" required maxlength="255" 
                                      placeholder="Describa la condición de salud de manera clara y específica..."><?= htmlspecialchars($condicion['condicionsalud_descripcion'] ?? '') ?></textarea>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">
                                <span id="charCount">0/255 caracteres</span>
                            </small>
                        </div>

                        <!-- Estado (solo para edición) -->
                        <?php if ($isEdit): ?>
                        <div class="form-group">
                            <label for="condicionsalud_estado">
                                <i class="fas fa-toggle-on"></i> Estado
                            </label>
                            <select class="form-control" id="condicionsalud_estado" name="condicionsalud_estado">
                                <option value="1" <?= ($condicion['condicionsalud_estado'] ?? 1) == 1 ? 'selected' : '' ?>>Activa</option>
                                <option value="0" <?= ($condicion['condicionsalud_estado'] ?? 1) == 0 ? 'selected' : '' ?>>Inactiva</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <!-- Botones de acción -->
                        <div class="form-group mt-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save"></i> 
                                        <?= $isEdit ? 'Actualizar Condición' : 'Crear Condición' ?>
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
                    <!-- Sección Consejos -->
                    <div class="mb-3">
                        <h6>Consejos</h6>
                        <ul class="list-unstyled small mb-0">
                            <li>Sea específico y use terminología médica apropiada</li>
                            <li>Mencione el tipo y severidad si es relevante</li>
                            <li>Evite abreviaciones que puedan confundir</li>
                            <li>Mantenga la información actualizada</li>
                        </ul>
                    </div>

                    <!-- Sección Estadísticas (solo en edición) -->
                    <?php if ($isEdit && isset($estadisticas)): ?>
                    <div class="mb-3">
                        <h6>Estadísticas</h6>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="stat-item">
                                    <div class="stat-value"><?= number_format($estadisticas['total_huespedes'] ?? 0) ?></div>
                                    <div class="stat-label small">Huéspedes</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-item">
                                    <div class="stat-value"><?= number_format($estadisticas['total_reservas'] ?? 0) ?></div>
                                    <div class="stat-label small">Reservas</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Sección Ejemplos (solo en creación) -->
                    <div class="mb-3">
                        <h6>Ejemplos Comunes</h6>
                        <ul class="list-unstyled small mb-0">
                            <li>Alergia severa a mariscos</li>
                            <li>Diabetes tipo 2 controlada</li>
                            <li>Hipertensión arterial</li>
                            <li>Asma bronquial leve</li>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Funciones para el formulario de condiciones de salud
document.addEventListener('DOMContentLoaded', function() {
    // Contador de caracteres para la descripción
    const descripcion = document.getElementById('condicionsalud_descripcion');
    const contador = document.getElementById('charCount');

    if (descripcion && contador) {
        function actualizarContador() {
            const longitud = descripcion.value.length;
            contador.textContent = `${longitud}/255 caracteres`;
            
            // Cambiar color si excede el límite
            if (longitud > 255) {
                contador.classList.add('text-danger');
                contador.classList.remove('text-muted');
            } else {
                contador.classList.remove('text-danger');
                contador.classList.add('text-muted');
            }
        }

        descripcion.addEventListener('input', actualizarContador);
        actualizarContador(); // Ejecutar al cargar si hay contenido
    }

    // Validación del formulario
    const form = document.getElementById('formCondicionSalud');
    if (form) {
        form.addEventListener('submit', function(e) {
            const descripcionVal = descripcion.value.trim();
            
            if (!descripcionVal) {
                e.preventDefault();
                Swal.fire('Error', 'La descripción es obligatoria', 'error');
                return false;
            }
            
            if (descripcionVal.length > 255) {
                e.preventDefault();
                Swal.fire('Error', 'La descripción no puede exceder 255 caracteres', 'error');
                return false;
            }
        });
    }
});

function limpiarFormulario() {
    Swal.fire({
        title: '¿Limpiar formulario?',
        text: 'Se perderán todos los datos ingresados',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('formCondicionSalud').reset();
            const charCount = document.getElementById('charCount');
            if (charCount) {
                charCount.textContent = '0/255 caracteres';
                charCount.classList.remove('text-danger');
                charCount.classList.add('text-muted');
            }
        }
    });
}
</script>
