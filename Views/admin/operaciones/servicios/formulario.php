<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= url('/') ?>">
                    <i class="fas fa-home"></i> Inicio
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= url('/servicios') ?>">
                    <i class="fas fa-concierge-bell"></i> Servicios
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?= isset($servicio) ? 'Editar Servicio' : 'Nuevo Servicio' ?>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800">
                <i class="fas fa-<?= isset($servicio) ? 'edit' : 'plus' ?> text-primary me-2"></i>
                <?= isset($servicio) ? 'Editar Servicio' : 'Nuevo Servicio' ?>
            </h2>
            <?php if (isset($servicio)): ?>
                <small class="text-muted">ID: <?= $servicio['id_servicio'] ?></small>
            <?php endif; ?>
        </div>
        <div>
            <a href="<?= url('/servicios') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver al listado
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Formulario principal -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Datos del Servicio
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formServicio" method="POST" action="<?= isset($servicio) ? url('/servicios/' . $servicio['id_servicio']) : url('/servicios') ?>" novalidate>
                        <?php if (isset($servicio)): ?>
                            <input type="hidden" name="_method" value="PUT">
                            <input type="hidden" name="id_servicio" value="<?= $servicio['id_servicio'] ?>">
                        <?php endif; ?>

                        <!-- Nombre del servicio -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="servicio_nombre" class="form-label">
                                    Nombre del Servicio <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="servicio_nombre" 
                                       name="servicio_nombre" 
                                       value="<?= htmlspecialchars($servicio['servicio_nombre'] ?? '') ?>"
                                       required 
                                       maxlength="100"
                                       placeholder="Ej: Limpieza de cabaña, Desayuno continental, etc.">
                                <div class="invalid-feedback">
                                    Por favor ingrese el nombre del servicio.
                                </div>
                                <div class="form-text">
                                    Máximo 100 caracteres. Sea descriptivo y específico.
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="servicio_descripcion" class="form-label">
                                    Descripción <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" 
                                          id="servicio_descripcion" 
                                          name="servicio_descripcion" 
                                          rows="4"
                                          required 
                                          maxlength="500"
                                          placeholder="Describe los detalles del servicio, qué incluye, duración, etc."><?= htmlspecialchars($servicio['servicio_descripcion'] ?? '') ?></textarea>
                                <div class="invalid-feedback">
                                    Por favor ingrese una descripción del servicio.
                                </div>
                                <div class="form-text">
                                    Máximo 500 caracteres. Incluya detalles relevantes para los huéspedes.
                                </div>
                            </div>
                        </div>

                        <!-- Fila de precio y tipo -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="servicio_precio" class="form-label">
                                    Precio <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="servicio_precio" 
                                           name="servicio_precio" 
                                           value="<?= isset($servicio) ? $servicio['servicio_precio'] : '' ?>"
                                           required 
                                           min="0" 
                                           step="0.01"
                                           placeholder="0.00">
                                    <div class="invalid-feedback">
                                        Por favor ingrese un precio válido.
                                    </div>
                                </div>
                                <div class="form-text">
                                    Precio en pesos argentinos. Use punto decimal para centavos.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="rela_tiposervicio" class="form-label">
                                    Tipo de Servicio <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="rela_tiposervicio" name="rela_tiposervicio" required>
                                    <option value="">Seleccione el tipo de servicio</option>
                                    <?php if (isset($tipos_servicios) && is_array($tipos_servicios)): ?>
                                        <?php foreach ($tipos_servicios as $tipo): ?>
                                            <option value="<?= $tipo['id_tiposervicio'] ?>" 
                                                    <?= (isset($servicio) && $servicio['rela_tiposervicio'] == $tipo['id_tiposervicio']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($tipo['tiposervicio_descripcion']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor seleccione un tipo de servicio.
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    <?= isset($servicio) ? 'Actualizar Servicio' : 'Crear Servicio' ?>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="limpiarFormulario()">
                                    <i class="fas fa-eraser me-2"></i>Limpiar
                                </button>
                            </div>
                            <div>
                                <a href="<?= url('/servicios') ?>" class="btn btn-outline-danger">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <!-- Información del servicio -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Información
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (isset($servicio)): ?>
                        <!-- Información existente -->
                        <div class="mb-3">
                            <small class="text-muted d-block">Estado actual</small>
                            <?php if ($servicio['servicio_estado'] == 1): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactivo</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted d-block">Fecha de creación</small>
                            <span><?= isset($servicio['servicio_fecha_creacion']) ? date('d/m/Y H:i', strtotime($servicio['servicio_fecha_creacion'])) : 'No disponible' ?></span>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Última actualización</small>
                            <span><?= isset($servicio['servicio_fecha_actualizacion']) ? date('d/m/Y H:i', strtotime($servicio['servicio_fecha_actualizacion'])) : 'No disponible' ?></span>
                        </div>
                    <?php else: ?>
                        <!-- Ayuda para nuevo servicio -->
                        <div class="mb-3">
                            <h6 class="text-primary">
                                <i class="fas fa-lightbulb me-2"></i>Consejos
                            </h6>
                            <ul class="list-unstyled small">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Use nombres descriptivos y claros
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Incluya detalles importantes en la descripción
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Establezca precios competitivos
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Seleccione el tipo de servicio correcto
                                </li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <?php if (isset($servicio)): ?>
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>Acciones Rápidas
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?= url('/servicios/' . $servicio['id_servicio']) ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye me-2"></i>Ver Detalle
                            </a>
                            <button type="button" class="btn btn-outline-<?= $servicio['servicio_estado'] ? 'danger' : 'success' ?> btn-sm" 
                                    onclick="cambiarEstado(<?= $servicio['id_servicio'] ?>, <?= $servicio['servicio_estado'] ?>)">
                                <i class="fas fa-<?= $servicio['servicio_estado'] ? 'times' : 'check' ?> me-2"></i>
                                <?= $servicio['servicio_estado'] ? 'Desactivar' : 'Activar' ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Validación del formulario
document.getElementById('formServicio').addEventListener('submit', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    if (this.checkValidity()) {
        // Mostrar confirmación
        Swal.fire({
            title: '¿Confirmar acción?',
            text: '<?= isset($servicio) ? "¿Desea actualizar este servicio?" : "¿Desea crear este servicio?" ?>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<?= isset($servicio) ? "Sí, actualizar" : "Sí, crear" ?>',
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
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
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
<?php if (isset($servicio)): ?>
function cambiarEstado(id, estadoActual) {
    const accion = estadoActual ? 'desactivar' : 'activar';
    const mensaje = `¿Está seguro que desea ${accion} este servicio?`;
    
    Swal.fire({
        title: '¿Confirmar acción?',
        text: mensaje,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
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
    const maxLength = 500;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    // Actualizar el texto de ayuda
    const helpText = this.nextElementSibling.nextElementSibling;
    if (remaining < 50) {
        helpText.textContent = `${remaining} caracteres restantes`;
        helpText.className = remaining < 10 ? 'form-text text-danger' : 'form-text text-warning';
    } else {
        helpText.textContent = `Máximo 500 caracteres. Incluya detalles relevantes para los huéspedes.`;
        helpText.className = 'form-text';
    }
});
</script>

