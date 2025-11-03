<?php
/**
 * Vista: Formulario de Servicio
 * Descripción: Formulario para crear/editar servicios
 * Autor: Sistema MVC
 * Fecha: <?= date('Y-m-d') ?>
 */

$isEdit = isset($servicio) && !empty($servicio);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/servicios') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos del servicio' : 'Datos del nuevo servicio' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formServicio" method="POST" 
                          action="<?= $isEdit ? url('/servicios/' . $servicio['id_servicio'] . '/edit') : url('/servicios/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_servicio" value="<?= $servicio['id_servicio'] ?>">
                        <?php endif; ?>

                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="servicio_nombre" class="required">
                                        <i class="fas fa-concierge-bell"></i> Nombre del Servicio
                                    </label>
                                    <input type="text" class="form-control" id="servicio_nombre" name="servicio_nombre" 
                                           value="<?= htmlspecialchars($servicio['servicio_nombre'] ?? '') ?>"
                                           required maxlength="45" placeholder="Ej: Limpieza de cabaña, Desayuno continental">
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Nombre descriptivo del servicio (máximo 45 caracteres)</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Descripción -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="servicio_descripcion" class="required">
                                        <i class="fas fa-align-left"></i> Descripción
                                    </label>
                                    <textarea class="form-control" id="servicio_descripcion" name="servicio_descripcion" 
                                              rows="4" required maxlength="400"
                                              placeholder="Describe los detalles del servicio, qué incluye, duración, etc."><?= htmlspecialchars($servicio['servicio_descripcion'] ?? '') ?></textarea>
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Descripción detallada del servicio (máximo 400 caracteres)</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Precio -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="servicio_precio" class="required">
                                        <i class="fas fa-dollar-sign"></i> Precio
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" class="form-control" id="servicio_precio" name="servicio_precio" 
                                               value="<?= isset($servicio) ? $servicio['servicio_precio'] : '' ?>"
                                               required min="0" step="0.01" placeholder="0.00">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <small class="form-text text-muted">Precio del servicio en pesos argentinos</small>
                                </div>
                            </div>

                            <!-- Tipo de Servicio -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rela_tiposervicio" class="required">
                                        <i class="fas fa-tags"></i> Tipo de Servicio
                                    </label>
                                    <select class="form-control" id="rela_tiposervicio" name="rela_tiposervicio" required>
                                        <option value="">Seleccione el tipo de servicio</option>
                                        <?php if (isset($tiposServicio) && is_array($tiposServicio)): ?>
                                            <?php foreach ($tiposServicio as $tipo): ?>
                                                <option value="<?= $tipo['id_tiposervicio'] ?>" 
                                                        <?= (isset($servicio) && $servicio['rela_tiposervicio'] == $tipo['id_tiposervicio']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($tipo['tiposervicio_descripcion']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Categoría del servicio</small>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="form-group mt-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save"></i> 
                                        <?= $isEdit ? 'Actualizar Servicio' : 'Crear Servicio' ?>
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
                            <li>• Use nombres descriptivos para facilitar las búsquedas</li>
                            <li>• La descripción debe explicar qué incluye el servicio</li>
                            <li>• Asigne el tipo de servicio correcto para categorizar</li>
                            <li>• Mantenga los precios actualizados para evitar confusiones</li>
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
                                        <div class="stat-value">
                                            <?= $estadisticas['consumos']['total_consumos'] ?? '0' ?>
                                        </div>
                                        <div class="stat-label small text-muted">Consumos</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value">
                                            $<?= number_format($estadisticas['consumos']['ingresos_total'] ?? 0, 2) ?>
                                        </div>
                                        <div class="stat-label small text-muted">Ingresos</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear el servicio.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.required:after {
    content: " *";
    color: #e74c3c;
}

.content-wrapper {
    padding: 20px;
}

.page-actions {
    margin-bottom: 0;
}

.form-group {
    margin-bottom: 1.5rem;
}

.info-section {
    margin-bottom: 1.5rem;
}

.info-section h6 {
    color: #495057;
    margin-bottom: 0.75rem;
}

.stat-item {
    padding: 0.5rem 0;
}

.stat-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: #495057;
}

.stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
}

.list-group-flush .list-group-item {
    border-left: 0;
    border-right: 0;
}

.d-grid {
    display: grid;
    gap: 0.5rem;
}
</style>

<script>
// Validación del formulario
document.getElementById('formServicio').addEventListener('submit', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    if (this.checkValidity()) {
        // Mostrar confirmación
        Swal.fire({
            title: '¿Confirmar acción?',
            text: '<?= $isEdit ? "¿Desea actualizar este servicio?" : "¿Desea crear este servicio?" ?>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<?= $isEdit ? "Sí, actualizar" : "Sí, crear" ?>',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    }
    
    this.classList.add('was-validated');
});

// Limpiar formulario
function limpiarFormulario() {
    Swal.fire({
        title: '¿Limpiar formulario?',
        text: 'Se perderán todos los datos ingresados',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('formServicio').reset();
            document.getElementById('formServicio').classList.remove('was-validated');
        }
    });
}

// Cambiar estado (solo para edición)
<?php if ($isEdit): ?>
function cambiarEstado(id, estadoActual) {
    const accion = estadoActual ? 'desactivar' : 'activar';
    const mensaje = `¿Está seguro que desea ${accion} este servicio?`;
    
    Swal.fire({
        title: '¿Confirmar acción?',
        text: mensaje,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, ' + accion,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`<?= url('/servicios') ?>/${id}/estado`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ estado: estadoActual ? 0 : 1 })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('¡Éxito!', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo completar la acción', 'error');
            });
        }
    });
}
<?php endif; ?>

// Validación en tiempo real para el precio
document.getElementById('servicio_precio').addEventListener('input', function() {
    const value = parseFloat(this.value);
    if (value < 0) {
        this.setCustomValidity('El precio no puede ser negativo');
    } else if (value > 999999) {
        this.setCustomValidity('El precio es demasiado alto');
    } else {
        this.setCustomValidity('');
    }
});

// Contador de caracteres para descripción
document.getElementById('servicio_descripcion').addEventListener('input', function() {
    const maxLength = 400;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    // Buscar el elemento de ayuda
    let helpText = this.parentNode.querySelector('.form-text');
    if (helpText) {
        if (remaining < 50) {
            helpText.textContent = `${remaining} caracteres restantes`;
            helpText.className = remaining < 10 ? 'form-text text-danger' : 'form-text text-warning';
        } else {
            helpText.textContent = 'Descripción detallada del servicio (máximo 400 caracteres)';
            helpText.className = 'form-text text-muted';
        }
    }
});

// Inicializar tooltips si existe Bootstrap
if (typeof $().tooltip === 'function') {
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
}
</script>