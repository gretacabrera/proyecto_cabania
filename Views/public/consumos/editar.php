<!-- Vista de edición de consumo del huésped -->
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-edit me-2"></i><?= $title ?>
                        </h5>
                        <a href="<?= url('/huesped/consumos') ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST">
                        <!-- Información del Producto -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Producto/Servicio:</label>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($consumo['producto_foto'])): ?>
                                            <img src="<?= url('/imagenes/productos/' . $consumo['producto_foto']) ?>" 
                                                 alt="<?= htmlspecialchars($consumo['item_nombre']) ?>"
                                                 class="rounded me-3"
                                                 style="width: 80px; height: 80px; object-fit: cover;">
                                        <?php endif; ?>
                                        <div>
                                            <h5 class="mb-1"><?= htmlspecialchars($consumo['item_nombre']) ?></h5>
                                            <p class="mb-0 text-muted"><?= htmlspecialchars($consumo['consumo_descripcion']) ?></p>
                                            <p class="mb-0 fw-bold text-success">
                                                Precio unitario: $<?= number_format($consumo['consumo_total'] / $consumo['consumo_cantidad'], 2) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Editar Cantidad -->
                        <div class="mb-4">
                            <label for="cantidad" class="form-label fw-bold">
                                <i class="fas fa-calculator text-primary"></i> Cantidad
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <button type="button" class="btn btn-outline-secondary" id="btnDecrementar">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" 
                                       name="cantidad" 
                                       id="cantidad" 
                                       class="form-control text-center" 
                                       value="<?= intval($consumo['consumo_cantidad']) ?>" 
                                       min="1" 
                                       max="999" 
                                       required>
                                <button type="button" class="btn btn-outline-secondary" id="btnIncrementar">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">
                                Modifique la cantidad según sus necesidades
                            </small>
                        </div>

                        <!-- Cálculo del Nuevo Subtotal -->
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Nuevo Subtotal:</h5>
                                    <h4 class="mb-0 text-success" id="nuevoSubtotal">
                                        $<?= number_format($consumo['consumo_total'], 2) ?>
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= url('/huesped/consumos') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning text-white">
                                <i class="fas fa-save"></i> Actualizar Consumo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const precioUnitario = <?= $consumo['consumo_total'] / $consumo['consumo_cantidad'] ?>;
const inputCantidad = document.getElementById('cantidad');
const nuevoSubtotal = document.getElementById('nuevoSubtotal');
const btnIncrementar = document.getElementById('btnIncrementar');
const btnDecrementar = document.getElementById('btnDecrementar');

function actualizarSubtotal() {
    const cantidad = parseInt(inputCantidad.value) || 1;
    const subtotal = precioUnitario * cantidad;
    nuevoSubtotal.textContent = '$' + subtotal.toFixed(2);
}

btnIncrementar.addEventListener('click', function() {
    let valor = parseInt(inputCantidad.value) || 1;
    if (valor < 999) {
        inputCantidad.value = valor + 1;
        actualizarSubtotal();
    }
});

btnDecrementar.addEventListener('click', function() {
    let valor = parseInt(inputCantidad.value) || 1;
    if (valor > 1) {
        inputCantidad.value = valor - 1;
        actualizarSubtotal();
    }
});

inputCantidad.addEventListener('input', actualizarSubtotal);
</script>
