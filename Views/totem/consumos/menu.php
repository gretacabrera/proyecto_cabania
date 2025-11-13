<?php
$this->extend('layouts/totem');
$this->section('title', $title);
$this->section('content');
?>

<div class="totem-header">
    <div class="row align-items-center">
        <div class="col-md-4">
            <h3 class="mb-0">
                <i class="fas fa-home text-primary"></i> 
                <?= htmlspecialchars($cabaniaNombre) ?>
            </h3>
            <small class="text-muted">Huésped: <?= htmlspecialchars($huespedNombre) ?></small>
        </div>
        <div class="col-md-4 text-center">
            <h2 class="mb-0 text-primary">Menú de Pedidos</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= url('/totem/historial') ?>" class="btn btn-outline-primary me-2">
                <i class="fas fa-history"></i> Historial
            </a>
            <a href="<?= url('/totem/reset') ?>" class="btn btn-outline-danger">
                <i class="fas fa-power-off"></i> Salir
            </a>
        </div>
    </div>
</div>

<div class="totem-card">
    <div class="row">
        <!-- Catálogo de Productos -->
        <div class="col-md-8">
            <h4 class="mb-4">
                <i class="fas fa-box-open text-success"></i> Productos Disponibles
            </h4>
            
            <div class="row" id="catalogoProductos">
                <?php foreach ($productos as $producto): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="producto-totem h-100 p-3" 
                             data-id="<?= $producto['id_producto'] ?>" 
                             data-precio="<?= $producto['producto_precio'] ?>" 
                             data-nombre="<?= htmlspecialchars($producto['producto_nombre']) ?>"
                             data-stock="<?= $producto['producto_stock'] ?>">
                            
                            <?php if (!empty($producto['producto_foto'])): ?>
                                <img src="<?= url('/imagenes/productos/' . $producto['producto_foto']) ?>" 
                                     class="img-fluid rounded mb-2" 
                                     alt="<?= htmlspecialchars($producto['producto_nombre']) ?>"
                                     style="height: 120px; width: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light rounded mb-2 d-flex align-items-center justify-content-center" 
                                     style="height: 120px;">
                                    <i class="fas fa-box fa-3x text-secondary"></i>
                                </div>
                            <?php endif; ?>
                            
                            <h5 class="text-center mb-2"><?= htmlspecialchars($producto['producto_nombre']) ?></h5>
                            <p class="text-center precio-tag mb-3">$<?= number_format($producto['producto_precio'], 2) ?></p>
                            
                            <div class="cantidad-control">
                                <button type="button" class="btn btn-outline-danger btn-decrementar">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <div class="cantidad-display">0</div>
                                <button type="button" class="btn btn-outline-success btn-incrementar">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            
                            <small class="d-block text-center mt-2 text-muted">
                                Stock: <span class="stock-badge"><?= $producto['producto_stock'] ?></span>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Carrito de Pedido -->
        <div class="col-md-4">
            <div class="sticky-top" style="top: 20px;">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-cart"></i> Tu Pedido
                        </h5>
                    </div>
                    <div class="card-body" id="carritoContenido">
                        <p class="text-muted text-center">
                            <i class="fas fa-cart-plus fa-3x mb-3 d-block"></i>
                            No hay productos seleccionados
                        </p>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">TOTAL:</h5>
                            <h4 class="mb-0 text-success" id="totalCarrito">$0.00</h4>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success btn-totem" id="btnEnviarPedido" disabled>
                                <i class="fas fa-paper-plane"></i> Enviar Pedido
                            </button>
                            <button type="button" class="btn btn-outline-danger" id="btnLimpiarCarrito" disabled>
                                <i class="fas fa-trash"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let carrito = {};

document.addEventListener('DOMContentLoaded', function() {
    const productos = document.querySelectorAll('.producto-totem');
    
    productos.forEach(card => {
        const btnIncrementar = card.querySelector('.btn-incrementar');
        const btnDecrementar = card.querySelector('.btn-decrementar');
        const cantidadDisplay = card.querySelector('.cantidad-display');
        const id = card.dataset.id;
        const precio = parseFloat(card.dataset.precio);
        const nombre = card.dataset.nombre;
        const maxStock = parseInt(card.dataset.stock);
        
        btnIncrementar.addEventListener('click', () => {
            let cantidad = parseInt(cantidadDisplay.textContent);
            if (cantidad < maxStock) {
                cantidad++;
                cantidadDisplay.textContent = cantidad;
                card.classList.add('selected');
                actualizarCarrito(id, nombre, precio, cantidad);
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stock insuficiente',
                    text: `Solo hay ${maxStock} unidades disponibles`,
                    timer: 2000
                });
            }
        });
        
        btnDecrementar.addEventListener('click', () => {
            let cantidad = parseInt(cantidadDisplay.textContent);
            if (cantidad > 0) {
                cantidad--;
                cantidadDisplay.textContent = cantidad;
                if (cantidad === 0) {
                    card.classList.remove('selected');
                }
                actualizarCarrito(id, nombre, precio, cantidad);
            }
        });
    });
    
    document.getElementById('btnLimpiarCarrito').addEventListener('click', limpiarCarrito);
    document.getElementById('btnEnviarPedido').addEventListener('click', enviarPedido);
});

function actualizarCarrito(id, nombre, precio, cantidad) {
    if (cantidad > 0) {
        carrito[id] = { nombre, precio, cantidad, subtotal: precio * cantidad };
    } else {
        delete carrito[id];
    }
    renderizarCarrito();
}

function renderizarCarrito() {
    const contenido = document.getElementById('carritoContenido');
    const totalCarrito = document.getElementById('totalCarrito');
    const btnEnviar = document.getElementById('btnEnviarPedido');
    const btnLimpiar = document.getElementById('btnLimpiarCarrito');
    
    if (Object.keys(carrito).length === 0) {
        contenido.innerHTML = `
            <p class="text-muted text-center">
                <i class="fas fa-cart-plus fa-3x mb-3 d-block"></i>
                No hay productos seleccionados
            </p>
        `;
        totalCarrito.textContent = '$0.00';
        btnEnviar.disabled = true;
        btnLimpiar.disabled = true;
        return;
    }
    
    let html = '<div class="list-group list-group-flush">';
    let total = 0;
    
    for (let id in carrito) {
        const item = carrito[id];
        html += `
            <div class="list-group-item px-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1">${item.nombre}</h6>
                        <small class="text-muted">
                            ${item.cantidad} x $${item.precio.toFixed(2)}
                        </small>
                    </div>
                    <span class="fw-bold text-success">$${item.subtotal.toFixed(2)}</span>
                </div>
            </div>
        `;
        total += item.subtotal;
    }
    
    html += '</div>';
    contenido.innerHTML = html;
    totalCarrito.textContent = '$' + total.toFixed(2);
    btnEnviar.disabled = false;
    btnLimpiar.disabled = false;
}

function limpiarCarrito() {
    Swal.fire({
        title: '¿Limpiar carrito?',
        text: 'Se eliminarán todos los productos seleccionados',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            carrito = {};
            document.querySelectorAll('.cantidad-display').forEach(el => el.textContent = '0');
            document.querySelectorAll('.producto-totem').forEach(el => el.classList.remove('selected'));
            renderizarCarrito();
        }
    });
}

function enviarPedido() {
    if (Object.keys(carrito).length === 0) {
        return;
    }
    
    Swal.fire({
        title: '¿Confirmar pedido?',
        html: 'Se enviará el pedido a la cocina/administración',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Preparar datos
            const items = [];
            for (let id in carrito) {
                items.push({
                    tipo: 'producto',
                    id: id,
                    cantidad: carrito[id].cantidad
                });
            }
            
            // Enviar al servidor
            Swal.fire({
                title: 'Enviando...',
                text: 'Procesando su pedido',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('<?= url('/totem/pedido') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ items })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Pedido enviado!',
                        text: data.message,
                        confirmButtonColor: '#28a745',
                        timer: 3000
                    }).then(() => {
                        limpiarCarritoSinConfirmar();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#d33'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión. Intente nuevamente.',
                    confirmButtonColor: '#d33'
                });
            });
        }
    });
}

function limpiarCarritoSinConfirmar() {
    carrito = {};
    document.querySelectorAll('.cantidad-display').forEach(el => el.textContent = '0');
    document.querySelectorAll('.producto-totem').forEach(el => el.classList.remove('selected'));
    renderizarCarrito();
}
</script>

<?php $this->endSection(); ?>
