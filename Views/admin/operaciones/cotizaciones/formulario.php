<?php
$isEdit = isset($cotizacion) && !empty($cotizacion);
$proveedorSeleccionado = $proveedorSeleccionado ?? ($cotizacion['rela_proveedor'] ?? '');
$productoSeleccionado = $productoSeleccionado ?? ($cotizacion['rela_producto'] ?? '');
?>

<div class="content-wrapper">
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/cotizaciones') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos de la cotización' : 'Datos de la nueva cotización' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formCotizacion" method="POST" 
                          action="<?= $isEdit ? url('/cotizaciones/' . $cotizacion['id_cotizacion'] . '/edit') : url('/cotizaciones/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_cotizacion" value="<?= $cotizacion['id_cotizacion'] ?>">
                        <?php endif; ?>

                        <div class="row">
                            <!-- Proveedor -->
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="rela_proveedor" class="required">
                                        <i class="fas fa-truck text-muted"></i> Proveedor
                                    </label>
                                    <select class="form-select" id="rela_proveedor" name="rela_proveedor" required>
                                        <option value="">Seleccione un proveedor</option>
                                        <?php foreach ($proveedores as $prov): ?>
                                            <option value="<?= $prov['id_proveedor'] ?>" 
                                                    <?= $proveedorSeleccionado == $prov['id_proveedor'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($prov['persona_denominacion'] ?? '') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Por favor seleccione un proveedor</div>
                                </div>
                            </div>

                            <!-- Producto -->
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="rela_producto" class="required">
                                        <i class="fas fa-box text-muted"></i> Producto
                                    </label>
                                    <select class="form-select" id="rela_producto" name="rela_producto" required>
                                        <option value="">Seleccione un producto</option>
                                        <?php foreach ($productos as $prod): ?>
                                            <option value="<?= $prod['id_producto'] ?>" 
                                                    <?= $productoSeleccionado == $prod['id_producto'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($prod['producto_nombre']) ?>
                                                <?php if (!empty($prod['marca_descripcion'])): ?>
                                                    (<?= htmlspecialchars($prod['marca_descripcion']) ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Por favor seleccione un producto</div>
                                </div>
                            </div>
                        </div>

                        <!-- Monto -->
                        <div class="form-group mb-3">
                            <label for="cotizacion_monto" class="required">
                                <i class="fas fa-dollar-sign text-muted"></i> Monto de Cotización
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="cotizacion_monto" name="cotizacion_monto" 
                                       value="<?= $cotizacion['cotizacion_monto'] ?? '' ?>"
                                       required min="0.01" step="0.01" placeholder="0.00">
                                <div class="invalid-feedback">El monto debe ser mayor a 0</div>
                            </div>
                            <small class="form-text text-muted">Ingrese el monto cotizado por el proveedor</small>
                        </div>

                        <div class="form-actions mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cotización
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>
                            <a href="<?= url('/cotizaciones') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

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
                            <li>• Seleccione el proveedor y producto para el cual desea registrar la cotización</li>
                            <li>• Ingrese el monto cotizado por el proveedor</li>
                            <li>• El sistema guardará automáticamente la fecha y hora de la cotización</li>
                        </ul>
                    </div>

                    <?php if ($isEdit && isset($estadisticas) && !empty($estadisticas)): ?>
                    <hr>

                    <div class="info-section">
                        <h6><i class="fas fa-chart-line text-info"></i> Estadísticas</h6>
                        <br>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="stat-item">
                                    <div class="stat-value"><?= $estadisticas['total_cotizaciones'] ?? 0 ?></div>
                                    <div class="stat-label small text-muted">Cotizaciones</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-item">
                                    <div class="stat-value">$<?= number_format($estadisticas['precio_promedio'] ?? 0, 0) ?></div>
                                    <div class="stat-label small text-muted">Promedio</div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="stat-item">
                                    <div class="stat-value text-success">$<?= number_format($estadisticas['precio_minimo'] ?? 0, 0) ?></div>
                                    <div class="stat-label small text-muted">Mínimo</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-item">
                                    <div class="stat-value text-danger">$<?= number_format($estadisticas['precio_maximo'] ?? 0, 0) ?></div>
                                    <div class="stat-label small text-muted">Máximo</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-item {
    margin-bottom: 1rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.75rem;
}
</style>

<script>
document.getElementById('formCotizacion').addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});
</script>
