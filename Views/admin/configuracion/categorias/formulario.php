<?php
/**
 * Vista: Formulario de Categoría
 * Descripción: Formulario para crear/editar categorías
 * Autor: Sistema MVC
 * Fecha: 2025-11-03
 */

$isEdit = isset($categoria) && !empty($categoria);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= $this->url('/categorias') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos de la categoría' : 'Datos de la nueva categoría' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formCategoria" method="POST" 
                          action="<?= $isEdit ? $this->url('/categorias/' . $categoria['id_categoria'] . '/edit') : $this->url('/categorias/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_categoria" value="<?= $categoria['id_categoria'] ?>">
                        <?php endif; ?>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="categoria_descripcion" class="required">
                                <i class="fas fa-tag"></i> Descripción de la Categoría
                            </label>
                            <input type="text" class="form-control" id="categoria_descripcion" name="categoria_descripcion" 
                                   value="<?= htmlspecialchars($categoria['categoria_descripcion'] ?? '') ?>"
                                   required maxlength="45" placeholder="Ej: Bebidas sin alcohol">
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">
                                <span id="contadorDescripcion">0</span> / 45 caracteres
                            </small>
                        </div>

                        <!-- Botones de acción -->
                        <div class="form-group mt-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save"></i> 
                                        <?= $isEdit ? 'Actualizar Categoría' : 'Crear Categoría' ?>
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
                            <li>• Use nombres descriptivos y concisos</li>
                            <li>• Evite duplicar categorías existentes</li>
                            <li>• La descripción debe ser única en el sistema</li>
                            <li>• Las categorías organizan productos del inventario</li>
                        </ul>
                    </div>

                    <hr>

                    <div class="info-section">
                        <h6><i class="fas fa-chart-line text-info"></i> Estadísticas</h6>
                        <br>
                        <?php if ($isEdit && isset($estadisticas)): ?>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value">
                                            <?= $estadisticas['total_productos'] ?? '0' ?>
                                        </div>
                                        <div class="stat-label small text-muted">Productos</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value">
                                            $<?= number_format($estadisticas['ingresos_ventas'] ?? 0, 2) ?>
                                        </div>
                                        <div class="stat-label small text-muted">Ingresos</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear la categoría.
                            </p>
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
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});

/**
 * Limpiar formulario con confirmación sutil
 */
function limpiarFormulario() {
    SwalPresets.confirm(
        '¿Limpiar formulario?',
        'Se perderán todos los datos ingresados',
        () => {
            document.getElementById('formCategoria').reset();
            document.getElementById('contadorDescripcion').textContent = '0';
            document.getElementById('formCategoria').classList.remove('was-validated');
            document.getElementById('categoria_descripcion').focus();
            
            // Toast sutil de confirmación
            SwalPresets.toast('Formulario limpiado', 'info', 2000);
        }
    );
}
</script>

<style>
.required::after {
    content: " *";
    color: #dc3545;
}

.info-section h6 {
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.stat-item .stat-value {
    font-size: 1.25rem;
    font-weight: 600;
    color: #495057;
}

.card-title {
    font-size: 0.875rem;
    font-weight: 600;
}
</style>