<?php
/**
 * Vista: Formulario de Marca
 * Descripción: Formulario para crear/editar marcas
 * Autor: Sistema MVC
 * Fecha: <?php echo date('Y-m-d'); ?>
 */

$isEdit = isset($marca) && !empty($marca);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/marcas') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos de la marca' : 'Datos de la nueva marca' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formMarca" method="POST" 
                          action="<?= $isEdit ? url('/marcas/' . $marca['id_marca'] . '/edit') : url('/marcas/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_marca" value="<?= $marca['id_marca'] ?>">
                        <?php endif; ?>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="marca_descripcion" class="required">
                                <i class="fas fa-tag"></i> Descripción
                            </label>
                            <input type="text" class="form-control" id="marca_descripcion" name="marca_descripcion" 
                                   value="<?= htmlspecialchars($marca['marca_descripcion'] ?? '') ?>"
                                   required minlength="2" maxlength="45" 
                                   placeholder="Nombre de la marca">
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
                                        <?= $isEdit ? 'Actualizar Marca' : 'Crear Marca' ?>
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
                            <li>• Use nombres cortos y descriptivos</li>
                            <li>• Evite abreviaturas complejas</li>
                            <li>• Verifique que no exista duplicada</li>
                            <li>• Use mayúsculas para siglas conocidas</li>
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
                                        <div class="stat-value"><?= $statistics['productos_totales'] ?? 0 ?></div>
                                        <div class="stat-label small text-muted">Total Productos</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value">$<?= number_format($statistics['valor_inventario'] ?? 0, 0, ',', '.') ?></div>
                                        <div class="stat-label small text-muted">Valor Total</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear la marca.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Contador de caracteres
document.addEventListener('DOMContentLoaded', function() {
    const descripcionInput = document.getElementById('marca_descripcion');
    const contador = document.getElementById('contadorDescripcion');
    
    function actualizarContador() {
        if (contador && descripcionInput) {
            contador.textContent = descripcionInput.value.length;
        }
    }
    
    if (descripcionInput) {
        descripcionInput.addEventListener('input', actualizarContador);
        actualizarContador();
    }
});

// Validación del formulario
const form = document.getElementById('formMarca');
if (form) {
    form.addEventListener('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        this.classList.add('was-validated');
    });
}

// Función para limpiar formulario
function limpiarFormulario() {
    const form = document.getElementById('formMarca');
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
        
        const contador = document.getElementById('contadorDescripcion');
        if (contador) {
            contador.textContent = '0';
        }
    }
}
</script>
