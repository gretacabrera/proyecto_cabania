<?php
$this->extend('layouts/main');
$this->section('title', $title);
$this->section('content');
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-plus-circle me-2"></i><?= $title ?>
                        </h5>
                        <a href="<?= url('/huesped/consumos') ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" id="formSolicitarConsumos">
                        <!-- Selección de Reserva -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="reserva_id" class="form-label fw-bold">
                                    <i class="fas fa-calendar-check text-primary"></i> Mi Reserva
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="reserva_id" id="reserva_id" class="form-select" required>
                                    <option value="">-- Seleccione una reserva --</option>
                                    <?php foreach ($reservas as $reserva): ?>
                                        <option value="<?= $reserva['id_reserva'] ?>">
                                            <?= htmlspecialchars($reserva['cabania_nombre']) ?> - 
                                            <?= date('d/m/Y', strtotime($reserva['reserva_fhinicio'])) ?> a 
                                            <?= date('d/m/Y', strtotime($reserva['reserva_fhfin'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Catálogo de Productos -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-box-open text-success"></i> Productos Disponibles
                            </label>
                            
                            <div class="row" id="catalogoProductos">
                                <?php foreach ($productos as $producto): ?>
                                    <div class="col-md-4 col-lg-3 mb-3">
                                        <div class="card h-100 producto-card" data-id="<?= $producto['id_producto'] ?>" data-precio="<?= $producto['producto_precio'] ?>" data-nombre="<?= htmlspecialchars($producto['producto_nombre']) ?>">
                                            <?php if (!empty($producto['producto_foto'])): ?>
                                                <img src="<?= url('/imagenes/productos/' . $producto['producto_foto']) ?>" 
                                                     class="card-img-top" 
                                                     alt="<?= htmlspecialchars($producto['producto_nombre']) ?>"
                                                     style="height: 150px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                                    <i class="fas fa-box fa-3x text-secondary"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="card-body">
                                                <h6 class="card-title"><?= htmlspecialchars($producto['producto_nombre']) ?></h6>
                                                <p class="card-text">
                                                    <small class="text-muted"><?= htmlspecialchars($producto['categoria_descripcion'] ?? '') ?></small>
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="fw-bold text-success">$<?= number_format($producto['producto_precio'], 2) ?></span>
                                                    <span class="badge bg-info">Stock: <?= intval($producto['producto_stock']) ?></span>
                                                </div>
                                                <div class="mt-2">
                                                    <div class="input-group input-group-sm">
                                                        <button class="btn btn-outline-secondary btn-decrementar" type="button">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                        <input type="number" class="form-control text-center cantidad-producto" 
                                                               value="0" min="0" max="<?= intval($producto['producto_stock']) ?>" readonly>
                                                        <button class="btn btn-outline-secondary btn-incrementar" type="button">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Resumen del Pedido -->
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-receipt"></i> Resumen del Pedido
                                </h5>
                                <div id="resumenPedido" class="mt-3">
                                    <p class="text-muted">No hay productos seleccionados</p>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">TOTAL:</h5>
                                    <h4 class="mb-0 text-success" id="totalPedido">$0.00</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="<?= url('/huesped/consumos') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success" id="btnEnviarPedido">
                                <i class="fas fa-paper-plane"></i> Enviar Pedido
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productos = document.querySelectorAll('.producto-card');
    const resumenPedido = document.getElementById('resumenPedido');
    const totalPedido = document.getElementById('totalPedido');
    let pedido = {};
    
    productos.forEach(card => {
        const btnIncrementar = card.querySelector('.btn-incrementar');
        const btnDecrementar = card.querySelector('.btn-decrementar');
        const inputCantidad = card.querySelector('.cantidad-producto');
        const id = card.dataset.id;
        const precio = parseFloat(card.dataset.precio);
        const nombre = card.dataset.nombre;
        const max = parseInt(inputCantidad.max);
        
        btnIncrementar.addEventListener('click', () => {
            let cantidad = parseInt(inputCantidad.value);
            if (cantidad < max) {
                cantidad++;
                inputCantidad.value = cantidad;
                actualizarPedido(id, nombre, precio, cantidad);
            }
        });
        
        btnDecrementar.addEventListener('click', () => {
            let cantidad = parseInt(inputCantidad.value);
            if (cantidad > 0) {
                cantidad--;
                inputCantidad.value = cantidad;
                actualizarPedido(id, nombre, precio, cantidad);
            }
        });
    });
    
    function actualizarPedido(id, nombre, precio, cantidad) {
        if (cantidad > 0) {
            pedido[id] = { nombre, precio, cantidad, subtotal: precio * cantidad };
        } else {
            delete pedido[id];
        }
        renderizarResumen();
    }
    
    function renderizarResumen() {
        if (Object.keys(pedido).length === 0) {
            resumenPedido.innerHTML = '<p class="text-muted">No hay productos seleccionados</p>';
            totalPedido.textContent = '$0.00';
            return;
        }
        
        let html = '<ul class="list-unstyled mb-0">';
        let total = 0;
        
        for (let id in pedido) {
            const item = pedido[id];
            html += `
                <li class="d-flex justify-content-between align-items-center mb-2">
                    <span>${item.nombre} x ${item.cantidad}</span>
                    <span class="fw-bold">$${item.subtotal.toFixed(2)}</span>
                </li>
            `;
            total += item.subtotal;
        }
        
        html += '</ul>';
        resumenPedido.innerHTML = html;
        totalPedido.textContent = '$' + total.toFixed(2);
    }
    
    // Validación del formulario
    document.getElementById('formSolicitarConsumos').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const reservaId = document.getElementById('reserva_id').value;
        
        if (!reservaId) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe seleccionar una reserva'
            });
            return false;
        }
        
        if (Object.keys(pedido).length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe seleccionar al menos un producto'
            });
            return false;
        }
        
        // Preparar datos para enviar
        const formData = new FormData();
        formData.append('reserva_id', reservaId);
        
        let index = 0;
        for (let id in pedido) {
            formData.append(`productos[${index}]`, id);
            formData.append(`cantidades[${index}]`, pedido[id].cantidad);
            index++;
        }
        
        // Enviar
        this.submit();
    });
});
</script>

<style>
.producto-card {
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}

.producto-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.cantidad-producto {
    background-color: white !important;
}
</style>

<?php $this->endSection(); ?>
