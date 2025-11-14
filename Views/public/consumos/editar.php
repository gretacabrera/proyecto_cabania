<!-- Vista de edición de consumo del huésped - Diseño responsive -->
<div class="container py-3 py-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="row align-items-center">
                        <div class="col-2 col-md-1">
                            <a href="<?= url('/huesped/consumos') ?>" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                        <div class="col-10 col-md-11">
                            <h5 class="mb-0">
                                <i class="fas fa-edit me-2 text-muted"></i>Editar Consumo
                            </h5>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    <form method="POST">
                        <?php 
                        $itemNombre = $consumo['producto_nombre'] ?? $consumo['servicio_nombre'] ?? 'Sin nombre';
                        $productoFoto = $consumo['producto_foto'] ?? null;
                        $precioUnitario = $consumo['consumo_total'] / $consumo['consumo_cantidad'];
                        ?>

                        <!-- Información del producto/servicio -->
                        <div class="mb-4 pb-3 border-bottom">
                            <div class="d-flex align-items-start">
                                <?php if (!empty($productoFoto)): ?>
                                    <img src="<?= url('/imagenes/productos/' . $productoFoto) ?>" 
                                         alt="<?= htmlspecialchars($itemNombre) ?>"
                                         class="rounded me-3 flex-shrink-0"
                                         style="width: 70px; height: 70px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                                         style="width: 70px; height: 70px;">
                                        <i class="fas fa-box fa-2x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1"><?= htmlspecialchars($itemNombre) ?></h5>
                                    <?php if (!empty($consumo['consumo_descripcion'])): ?>
                                        <p class="mb-2 text-muted small"><?= htmlspecialchars($consumo['consumo_descripcion']) ?></p>
                                    <?php endif; ?>
                                    <p class="mb-0 fw-bold text-success">
                                        Precio unitario: $ <?= number_format($precioUnitario, 2, ',', '.') ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Control de cantidad -->
                        <div class="mb-4">
                            <label for="cantidad" class="form-label fw-bold mb-2">
                                <i class="fas fa-calculator text-primary me-1"></i>
                                Cantidad <span class="text-danger">*</span>
                            </label>
                            <div class="row g-2 align-items-center">
                                <div class="col-12 col-sm-6">
                                    <div class="input-group input-group-lg">
                                        <button type="button" class="btn btn-outline-secondary" id="btnDecrementar">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" 
                                               name="cantidad" 
                                               id="cantidad" 
                                               class="form-control text-center fw-bold" 
                                               value="<?= intval($consumo['consumo_cantidad']) ?>" 
                                               min="1" 
                                               max="999" 
                                               required>
                                        <button type="button" class="btn btn-outline-secondary" id="btnIncrementar">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="alert alert-info mb-0 py-2 small">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Use los botones +/- para ajustar
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total y botón de confirmación -->
                        <div class="border-top pt-4 mt-4">
                            <div class="row g-3 align-items-center">
                                <div class="col-12 col-md-6 mb-3 mb-md-0">
                                    <div class="d-flex align-items-baseline">
                                        <span class="text-muted me-2">TOTAL:</span>
                                        <h3 class="mb-0 text-success fw-bold" id="nuevoSubtotal">
                                            $ <?= number_format($consumo['consumo_total'], 2, ',', '.') ?>
                                        </h3>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-dark btn-lg">
                                            <i class="fas fa-check me-2"></i>Confirmar Cambios
                                        </button>
                                    </div>
                                </div>
                            </div>
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
    nuevoSubtotal.textContent = '$ ' + subtotal.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
