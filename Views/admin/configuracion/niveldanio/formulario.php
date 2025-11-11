<?php
/**
 * Vista: Formulario de Nivel de Daño
 * Descripción: Formulario para crear/editar niveles de daño
 */

$isEdit = isset($registro) && !empty($registro);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/niveldanio') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos del nivel de daño' : 'Datos del nuevo nivel de daño' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formNivelDanio" method="POST" 
                          action="<?= $isEdit ? url('/niveldanio/' . $registro['id_niveldanio'] . '/edit') : url('/niveldanio/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_niveldanio" value="<?= $registro['id_niveldanio'] ?>">
                        <?php endif; ?>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="niveldanio_descripcion" class="required">
                                <i class="fas fa-align-left"></i> Descripción
                            </label>
                            <input type="text" class="form-control" id="niveldanio_descripcion" name="niveldanio_descripcion" 
                                   value="<?= htmlspecialchars($registro['niveldanio_descripcion'] ?? '') ?>"
                                   required maxlength="45" 
                                   placeholder="Ej: Daño leve, Daño moderado, Daño severo">
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
                                        <?= $isEdit ? 'Actualizar Nivel de Daño' : 'Crear Nivel de Daño' ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-lg ml-2" 
                                            onclick="limpiarFormulario()">
                                        <i class="fas fa-eraser"></i> Limpiar
                                    </button>
                                </div>
                                <div>
                                    <a href="<?= url('/niveldanio') ?>" class="btn btn-outline-dark btn-lg">
                                        <i class="fas fa-times"></i> Cancelar
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
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información
                    </h6>
                </div>
                <div class="card-body">
                    <div class="info-section">
                        <h6><i class="fas fa-lightbulb text-warning"></i> Consejos</h6>
                        <ul class="list-unstyled small text-muted">
                            <li>• Use descripciones claras y concisas</li>
                            <li>• Defina niveles progresivos (leve, moderado, severo)</li>
                            <li>• Los niveles se usarán para calcular costos de reparación</li>
                            <li>• Mantenga una clasificación consistente</li>
                        </ul>
                    </div>

                    <hr>

                    <div class="info-section">
                        <h6><i class="fas fa-chart-line text-info"></i> Estadísticas</h6>
                        <br>
                        <?php if ($isEdit): ?>
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value"><?= number_format($estadisticas['danios_mes'] ?? 0) ?></div>
                                        <div class="stat-label small text-muted">Daños (mes)</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value"><?= number_format($estadisticas['danios_anio'] ?? 0) ?></div>
                                        <div class="stat-label small text-muted">Daños (año)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value">$<?= number_format($estadisticas['costos_facturados_mes'] ?? 0, 2) ?></div>
                                        <div class="stat-label small text-muted">Costos (mes)</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value">$<?= number_format($estadisticas['costos_facturados_anio'] ?? 0, 2) ?></div>
                                        <div class="stat-label small text-muted">Costos (año)</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear el nivel de daño.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para el formulario -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formNivelDanio');
    const descripcionInput = document.getElementById('niveldanio_descripcion');
    const contador = document.getElementById('contadorDescripcion');

    // Contador de caracteres
    if (descripcionInput && contador) {
        const actualizarContador = () => {
            contador.textContent = descripcionInput.value.length;
        };
        
        descripcionInput.addEventListener('input', actualizarContador);
        actualizarContador(); // Inicializar contador
    }

    // Validación del formulario
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            form.classList.add('was-validated');
        });
    }
});

function limpiarFormulario() {
    const form = document.getElementById('formNivelDanio');
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
        
        // Actualizar contador
        const contador = document.getElementById('contadorDescripcion');
        if (contador) {
            contador.textContent = '0';
        }
    }
}
</script>

<style>
.required::after {
    content: " *";
    color: red;
}

.stat-item {
    padding: 10px;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
}

.stat-label {
    color: #666;
}
</style>
