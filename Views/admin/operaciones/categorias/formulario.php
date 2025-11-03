<?php
/**
 * Vista: Formulario de Categoría
 * Descripción: Formulario para crear/editar categorías
 * Autor: Sistema MVC
 * Fecha: <?php echo date('Y-m-d'); ?>
 */

$isEdit = isset($categoria) && !empty($categoria);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/categorias') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar Categoría' : 'Nueva Categoría' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formCategoria" method="POST" 
                          action="<?= $isEdit ? url('/categorias/' . $categoria['id_categoria'] . '/edit') : url('/categorias/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_categoria" value="<?= $categoria['id_categoria'] ?>">
                        <?php endif; ?>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="categoria_descripcion" class="required">Descripción de la Categoría</label>
                            <input type="text" class="form-control" id="categoria_descripcion" name="categoria_descripcion" 
                                   value="<?= htmlspecialchars($categoria['categoria_descripcion'] ?? '') ?>"
                                   required maxlength="45" placeholder="Ej: Bebidas sin alcohol">
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">
                                <span id="contadorDescripcion">0</span> / 45 caracteres
                            </small>
                        </div>

                        <!-- Botones de acción -->
                        <div class="form-actions mt-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <?= $isEdit ? 'Actualizar Categoría' : 'Crear Categoría' ?>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="limpiarFormulario()">
                                        Limpiar
                                    </button>
                                </div>
                                <div>
                                    <a href="<?= url('/categorias') ?>" class="btn btn-outline-secondary">
                                        Cancelar
                                    </a>
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
                    <h6 class="card-title mb-0">Información</h6>
                </div>
                <div class="card-body">
                    <?php if ($isEdit): ?>
                        <div class="alert alert-info">
                            <strong>Editando categoría</strong><br>
                            Estado actual: <?= $categoria['categoria_estado'] == 1 ? 'Activa' : 'Inactiva' ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <strong>Creando nueva categoría</strong><br>
                            La categoría se creará como activa por defecto.
                        </div>
                    <?php endif; ?>

                    <hr>

                    <h6>Consejos</h6>
                    <ul class="small text-muted">
                        <li>Use nombres descriptivos y concisos</li>
                        <li>Evite duplicar categorías existentes</li>
                        <li>La descripción debe ser única en el sistema</li>
                        <li>Las categorías son usadas para organizar productos</li>
                    </ul>

                    <hr>

                    <h6>Validaciones</h6>
                    <ul class="small text-muted">
                        <li>La descripción es obligatoria</li>
                        <li>Máximo 45 caracteres</li>
                        <li>Solo letras, números y espacios</li>
                    </ul>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Acciones Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= url('/categorias') ?>" class="btn btn-outline-primary btn-sm">
                            Ver todas las categorías
                        </a>
                        <?php if (!$isEdit): ?>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="document.getElementById('formCategoria').submit()">
                                Guardar y continuar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formCategoria');
    const descripcionInput = document.getElementById('categoria_descripcion');
    const contadorDescripcion = document.getElementById('contadorDescripcion');

    // Contador de caracteres en tiempo real
    function actualizarContador() {
        const length = descripcionInput.value.length;
        contadorDescripcion.textContent = length;
        
        if (length > 45) {
            contadorDescripcion.style.color = '#dc3545';
        } else if (length > 35) {
            contadorDescripcion.style.color = '#fd7e14';
        } else {
            contadorDescripcion.style.color = '#6c757d';
        }
    }

    descripcionInput.addEventListener('input', actualizarContador);
    
    // Inicializar contador
    actualizarContador();

    // Validación del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Limpiar errores previos
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        
        let isValid = true;
        
        // Validar descripción
        if (!descripcionInput.value.trim()) {
            mostrarError(descripcionInput, 'La descripción es obligatoria');
            isValid = false;
        } else if (descripcionInput.value.length > 45) {
            mostrarError(descripcionInput, 'La descripción no puede exceder 45 caracteres');
            isValid = false;
        } else if (descripcionInput.value.length < 3) {
            mostrarError(descripcionInput, 'La descripción debe tener al menos 3 caracteres');
            isValid = false;
        } else if (!/^[a-zA-Z0-9\sáéíóúüñÁÉÍÓÚÜÑ]+$/.test(descripcionInput.value)) {
            mostrarError(descripcionInput, 'Solo se permiten letras, números y espacios');
            isValid = false;
        }

        if (isValid) {
            // Mostrar loading
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            submitBtn.disabled = true;
            
            // Enviar formulario
            form.submit();
        }
    });

    function mostrarError(input, mensaje) {
        input.classList.add('is-invalid');
        const feedback = input.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = mensaje;
        }
        input.focus();
    }
});

/**
 * Limpiar formulario
 */
function limpiarFormulario() {
    Swal.fire({
        title: '¿Limpiar formulario?',
        text: 'Se perderán todos los datos ingresados',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('formCategoria').reset();
            // Quitar clases de error
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            // Actualizar contador
            document.getElementById('contadorDescripcion').textContent = '0';
            document.getElementById('categoria_descripcion').focus();
        }
    });
}
</script>