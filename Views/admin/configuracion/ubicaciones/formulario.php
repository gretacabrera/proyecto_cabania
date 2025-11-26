<?php
$isEdit = isset($ubicacion) && !empty($ubicacion);
?>

<div class="content-wrapper">
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/ubicaciones') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit"></i> 
                        <?= $isEdit ? 'Modificar datos de la ubicación' : 'Datos de la nueva ubicación' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formUbicacion" method="POST" 
                          action="<?= $isEdit ? url('/ubicaciones/' . $ubicacion['id_ubicacion'] . '/edit') : url('/ubicaciones/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_ubicacion" value="<?= $ubicacion['id_ubicacion'] ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="ubicacion_descripcion" class="required">
                                <i class="fas fa-map-marker-alt"></i> Descripción
                            </label>
                            <textarea class="form-control" id="ubicacion_descripcion" name="ubicacion_descripcion" 
                                      rows="4" required maxlength="250" 
                                      placeholder="Describe la ubicación detalladamente..."><?= htmlspecialchars($ubicacion['ubicacion_descripcion'] ?? '') ?></textarea>
                            <div class="invalid-feedback">Por favor ingrese una descripción válida.</div>
                            <small class="form-text text-muted">
                                <span id="contadorDescripcion">0</span> / 250 caracteres
                            </small>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> <?= $isEdit ? 'Guardar Cambios' : 'Crear Ubicación' ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="limpiarFormulario()">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>
                            <a href="<?= url('/ubicaciones') ?>" class="btn btn-outline-danger">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
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
                            <li>• Sea específico en la descripción de la ubicación</li>
                            <li>• Incluya puntos de referencia reconocibles</li>
                            <li>• Mencione características únicas del sector</li>
                            <li>• Considere la accesibilidad para los huéspedes</li>
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
                                        <div class="stat-value"><?= number_format($estadisticas['total_cabanias']) ?></div>
                                        <div class="stat-label small text-muted">Cabañas</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value"><?= number_format($estadisticas['total_huespedes']) ?></div>
                                        <div class="stat-label small text-muted">Huéspedes</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear la ubicación.
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
    const form = document.getElementById('formUbicacion');
    const descripcion = document.getElementById('ubicacion_descripcion');
    const contador = document.getElementById('contadorDescripcion');

    // Contador de caracteres
    if (descripcion && contador) {
        function actualizarContador() {
            contador.textContent = descripcion.value.length;
        }
        
        descripcion.addEventListener('input', actualizarContador);
        actualizarContador();
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
    const form = document.getElementById('formUbicacion');
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
