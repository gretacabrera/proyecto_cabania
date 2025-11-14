<!-- Vista de solicitud de consumos del huésped - Componente unificado -->
<div class="container-fluid" style="height: 100vh; overflow: hidden; padding: 1rem;">
    <div style="height: 100%; display: flex; flex-direction: column;">
        <!-- Card unificada -->
        <div class="card" style="height: 700px; max-height: 90vh;">
            <!-- Header global del componente -->
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= url('/huesped/consumos') ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h5 class="mb-0">Solicitar Consumos</h5>
                    </div>
                    <div class="text-muted small">
                        <strong>Cabaña:</strong> <?= htmlspecialchars($reserva['cabania_nombre']) ?> | 
                        <strong>Estadía:</strong> 
                        <?= date('d/m/Y', strtotime($reserva['reserva_fhinicio'])) ?> - 
                        <?= date('d/m/Y', strtotime($reserva['reserva_fhfin'])) ?>
                    </div>
                </div>
            </div>
            
            <!-- Cuerpo con 3 columnas -->
            <div class="card-body p-0" style="height: calc(100% - 73px);">
                <div class="row g-0 h-100">
                    <!-- Columna izquierda: Tipos y Categorías -->
                    <div class="col-12 col-lg-2 d-flex flex-column border-end p-3 mobile-section" style="height: 100%; overflow: hidden;">
                        <!-- Selector de Tipo -->
                        <div class="mb-3" style="flex-shrink: 0;">
                            <h6 class="mb-2">Seleccionar Tipo</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="tipoSelector" id="radioProducto" value="producto" onchange="seleccionarTipo('producto')">
                                <label class="form-check-label" for="radioProducto">Producto</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipoSelector" id="radioServicio" value="servicio" onchange="seleccionarTipo('servicio')">
                                <label class="form-check-label" for="radioServicio">Servicio</label>
                            </div>
                        </div>

                        <hr class="my-2" style="flex-shrink: 0;">

                        <!-- Lista de Categorías con scroll -->
                        <h6 class="mb-2" style="flex-shrink: 0;">Seleccionar Categoría</h6>
                        <div class="flex-grow-1 overflow-auto">
                            <div id="listaCategorias">
                                <div class="text-muted text-center py-3">
                                    <small>Seleccione un tipo</small>
                                </div>
                            </div>
                        </div>
                    </div>                    <!-- Columna central: Grid de productos con carrusel -->
                    <div class="col-12 col-lg-7 d-flex flex-column border-end p-3 mobile-section" style="height: 100%; position: relative; overflow: hidden;">
                        <div id="contenedorProductos" class="flex-grow-1 overflow-hidden">
                            <div class="text-center text-muted py-5">
                                <p>Seleccione un tipo y categoría</p>
                            </div>
                        </div>
                        
                        <!-- Flechas de navegación (a media altura del contenedor de productos) -->
                        <button id="btnCarruselPrev" class="btn-carrusel position-absolute translate-middle-y" style="display: none; z-index: 10; left: -25px; top: 50%; width: 50px; height: 80px; background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; cursor: pointer;" onclick="moverCarrusel(-1)">
                            <i class="fas fa-chevron-left fa-2x" style="color: #333;"></i>
                        </button>
                        <button id="btnCarruselNext" class="btn-carrusel position-absolute translate-middle-y" style="display: none; z-index: 10; right: -25px; top: 50%; width: 50px; height: 80px; background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; cursor: pointer;" onclick="moverCarrusel(1)">
                            <i class="fas fa-chevron-right fa-2x" style="color: #333;"></i>
                        </button>
                    </div>

                    <!-- Columna derecha: Carrito -->
                    <div class="col-12 col-lg-3 d-flex flex-column p-3 mobile-section" style="height: 100%; overflow: hidden;">
                        <h6 class="mb-3">
                            <i class="fas fa-shopping-cart text-muted"></i> Carrito
                        </h6>
                        
                        <div class="flex-grow-1 overflow-auto mb-3">
                            <div id="listaCarrito">
                                <div class="text-center text-muted py-5">
                                    <p class="small mb-0">Carrito vacío</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiarCarrito" onclick="limpiarCarrito()" disabled style="flex: 0 0 auto;">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button type="button" class="btn btn-dark w-100" id="btnRegistrarSolicitud" onclick="confirmarSolicitud()" disabled>
                                <i class="fas fa-check"></i> Registrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

<!-- Modal de Confirmación -->
<form id="formSolicitud" method="POST" style="display: none;">
    <input type="hidden" name="carrito" id="inputCarrito">
</form>

<style>
/* Estilos minimalistas */
body {
    background: #1a2332;
    overflow: hidden;
}

html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    overflow: hidden;
}

/* Mobile: columnas apiladas sin scroll */
@media (max-width: 991px) {
    .mobile-section {
        height: calc(33.33vh - 30px) !important;
        min-height: 180px !important;
        max-height: 250px !important;
        border-bottom: 1px solid #dee2e6;
        border-right: none !important;
    }
    
    .mobile-section:last-child {
        border-bottom: none;
    }
}

.border {
    border: 2px solid #dee2e6 !important;
}

/* Categorías - estilo simple */
.categoria-item {
    padding: 10px 15px;
    margin-bottom: 5px;
    border: 1px solid #e0e0e0;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
    border-radius: 4px;
}

.categoria-item:hover {
    background: #f8f9fa;
    border-color: #0d6efd;
}

.categoria-item.active {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

/* Productos - estilo minimalista */
.producto-card {
    border: 1px solid #dee2e6;
    padding: 15px;
    text-align: center;
    background: white;
    border-radius: 4px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

/* Productos y Servicios - Cards grandes con fotos */
.producto-card-large {
    border: 1px solid #dee2e6;
    padding: 10px;
    text-align: center;
    background: white;
    border-radius: 4px;
    height: 85%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
}

/* Mobile: 1 item por página, altura completa */
@media (max-width: 991px) {
    .producto-card-large {
        height: 100% !important;
        max-height: 100% !important;
    }
}

.producto-foto {
    width: 100%;
    height: 65%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    border-radius: 4px;
    margin-bottom: 6px;
    background-color: #f8f9fa;
    flex-shrink: 0;
}

.producto-nombre-small {
    font-size: 0.85rem;
    margin-bottom: 6px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    text-align: center;
    line-height: 1.2;
    font-weight: 500;
    padding: 0 5px;
    flex-shrink: 1;
}

.servicio-nombre-large {
    font-size: 1.3rem;
    font-weight: 600;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    text-align: center;
    line-height: 1.3;
    padding: 20px 10px;
    color: #333;
}

.producto-nombre {
    font-size: 0.85rem;
    margin-bottom: 5px;
    min-height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1.2;
}

.producto-precio {
    font-size: 0.85rem;
    color: #28a745;
    font-weight: bold;
    margin-bottom: 0;
    text-align: center;
    flex-shrink: 0;
}

.producto-card-large:hover {
    background: #f8f9fa;
    border-color: #28a745;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Carrito - estilo compacto y optimizado */
.carrito-item {
    padding: 8px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    margin-bottom: 8px;
    background: white;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.carrito-item-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.carrito-item-nombre {
    font-size: 0.8rem;
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.carrito-item-precio-unitario {
    font-size: 0.7rem;
    color: #28a745;
}

.carrito-item-controles-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 6px;
}

.carrito-item-controles {
    display: flex;
    align-items: center;
    gap: 6px;
}

.carrito-item-controles button {
    width: 24px;
    height: 24px;
    padding: 0;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.75rem;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.carrito-item-controles button:hover {
    background: #f8f9fa;
}

.carrito-item-controles span {
    min-width: 24px;
    text-align: center;
    font-weight: bold;
    font-size: 0.8rem;
}

.carrito-item-borrar {
    color: #dc3545;
    cursor: pointer;
    font-size: 1.1rem;
    font-weight: bold;
    width: 24px;
    height: 24px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s;
}

.carrito-item-borrar:hover {
    background: #ffe5e5;
}

/* Flechas de navegación */
.btn-carrusel {
    cursor: pointer;
    transition: none;
    background: rgba(255, 255, 255, 0.9) !important;
}

/* Scrollbar personalizado */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script>
// Estado de la aplicación
const app = {
    reservaId: <?= $reserva['id_reserva'] ?>,
    tipoSeleccionado: null,
    categoriaSeleccionada: null,
    carrito: [],
    paginaCarrusel: 0,
    itemsPorPagina: window.innerWidth <= 991 ? 1 : 8, // 1 en mobile, 8 en desktop
    totalItems: 0,
    itemsActuales: [],
    tipoActual: null
};

// Actualizar itemsPorPagina al cambiar tamaño de ventana
window.addEventListener('resize', () => {
    const nuevoItemsPorPagina = window.innerWidth <= 991 ? 1 : 8;
    if (nuevoItemsPorPagina !== app.itemsPorPagina) {
        app.itemsPorPagina = nuevoItemsPorPagina;
        app.paginaCarrusel = 0;
        if (app.itemsActuales.length > 0) {
            mostrarPaginaCarrusel();
            actualizarControlesCarrusel();
        }
    }
});

// Seleccionar tipo (Producto o Servicio)
function seleccionarTipo(tipo) {
    app.tipoSeleccionado = tipo;
    app.categoriaSeleccionada = null;
    
    // Cargar categorías o tipos de servicio
    if (tipo === 'producto') {
        cargarCategorias();
    } else {
        cargarTiposServicio();
    }
    
    // Limpiar productos
    document.getElementById('contenedorProductos').innerHTML = `
        <div class="text-center text-muted py-5">
            <p>Seleccione una categoría</p>
        </div>
    `;
}

// Cargar categorías
async function cargarCategorias() {
    try {
        const response = await fetch('<?= url('/huesped/consumos/api/categorias') ?>');
        
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        
        const data = await response.json();
        
        if (!data.success) {
            Swal.fire('Error', data.message || 'No se pudieron cargar las categorías', 'error');
            return;
        }
        
        if (!data.data || data.data.length === 0) {
            document.getElementById('listaCategorias').innerHTML = `
                <div class="text-muted text-center py-3">
                    <small>No hay categorías disponibles</small>
                </div>
            `;
            return;
        }
        
        const container = document.getElementById('listaCategorias');
        container.innerHTML = '';
        
        data.data.forEach(cat => {
            const div = document.createElement('div');
            div.className = 'categoria-item';
            div.textContent = cat.categoria_descripcion;
            div.onclick = () => seleccionarCategoria(cat.id_categoria, cat.categoria_descripcion, div);
            container.appendChild(div);
        });
    } catch (error) {
        console.error('Error completo:', error);
        Swal.fire('Error', 'No se pudieron cargar las categorías: ' + error.message, 'error');
    }
}

// Cargar tipos de servicio
async function cargarTiposServicio() {
    try {
        const response = await fetch('<?= url('/huesped/consumos/api/tipos-servicio') ?>');
        const data = await response.json();
        
        if (!data.success) {
            document.getElementById('listaCategorias').innerHTML = `
                <div class="text-muted text-center py-3">
                    <small>No hay tipos de servicio disponibles</small>
                </div>
            `;
            return;
        }
        
        const container = document.getElementById('listaCategorias');
        container.innerHTML = '';
        
        data.data.forEach(tipo => {
            const div = document.createElement('div');
            div.className = 'categoria-item';
            div.textContent = tipo.tiposervicio_descripcion;
            div.onclick = () => seleccionarTipoServicio(tipo.id_tiposervicio, tipo.tiposervicio_descripcion, div);
            container.appendChild(div);
        });
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudieron cargar los tipos de servicio', 'error');
    }
}

// Seleccionar categoría de producto
async function seleccionarCategoria(id, nombre, element) {
    app.categoriaSeleccionada = id;
    
    // Actualizar estado activo
    document.querySelectorAll('.categoria-item').forEach(item => {
        item.classList.remove('active');
    });
    element.classList.add('active');
    
    try {
        const response = await fetch(`<?= url('/huesped/consumos/api/productos/') ?>${id}`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            renderizarProductos(data.data, 'producto');
        } else {
            document.getElementById('contenedorProductos').innerHTML = `
                <div class="text-center text-muted py-5">
                    <p>No hay productos disponibles</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudieron cargar los productos', 'error');
    }
}

// Seleccionar tipo de servicio
async function seleccionarTipoServicio(id, nombre, element) {
    app.categoriaSeleccionada = id;
    
    // Actualizar estado activo
    document.querySelectorAll('.categoria-item').forEach(item => {
        item.classList.remove('active');
    });
    element.classList.add('active');
    
    try {
        const response = await fetch(`<?= url('/huesped/consumos/api/servicios/') ?>${id}`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            renderizarProductos(data.data, 'servicio');
        } else {
            document.getElementById('contenedorProductos').innerHTML = `
                <div class="text-center text-muted py-5">
                    <p>No hay servicios disponibles</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudieron cargar los servicios', 'error');
    }
}

// Renderizar productos en grid con paginación
function renderizarProductos(items, tipo) {
    app.totalItems = items.length;
    app.paginaCarrusel = 0;
    app.itemsActuales = items;
    app.tipoActual = tipo;
    
    mostrarPaginaCarrusel();
    actualizarControlesCarrusel();
}

// Mostrar página actual del carrusel
function mostrarPaginaCarrusel() {
    const container = document.getElementById('contenedorProductos');
    const inicio = app.paginaCarrusel * app.itemsPorPagina;
    const fin = inicio + app.itemsPorPagina;
    const itemsPagina = app.itemsActuales.slice(inicio, fin);
    
    const isMobile = window.innerWidth <= 991;
    
    if (isMobile) {
        // MOBILE: 1 item centrado con altura completa
        container.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 p-2"></div>';
        const wrapper = container.querySelector('div');
        
        if (itemsPagina.length > 0) {
            const item = itemsPagina[0];
            const id = app.tipoActual === 'producto' ? item.id_producto : item.id_servicio;
            const nombre = app.tipoActual === 'producto' ? item.producto_nombre : item.servicio_descripcion;
            const precio = app.tipoActual === 'producto' ? item.producto_precio : item.servicio_precio;
            const foto = app.tipoActual === 'producto' && item.producto_foto ? item.producto_foto : null;
            
            const nombreEscapado = nombre.replace(/'/g, "\\'");
            const precioFormateado = parseFloat(precio).toLocaleString('es-AR');
            
            let contenidoCard = '';
            if (app.tipoActual === 'producto' && foto) {
                contenidoCard = `
                    <div class="producto-foto" style="background-image: url('<?= url('/imagenes/productos/') ?>${foto}'); height: 70%;"></div>
                    <div class="producto-nombre-small" style="font-size: 1.1rem; margin: 10px 0;">${nombre}</div>
                `;
            } else {
                contenidoCard = `
                    <div class="servicio-nombre-large" style="font-size: 1.5rem;">${nombre}</div>
                `;
            }
            
            wrapper.innerHTML = `
                <div class="producto-card-large" onclick="agregarAlCarrito(${id}, '${app.tipoActual}', '${nombreEscapado}', ${precio})" 
                     style="cursor: pointer; transition: all 0.2s; width: 100%; max-width: 400px;">
                    ${contenidoCard}
                    <div class="producto-precio" style="font-size: 1.3rem; margin-top: 10px;">$ ${precioFormateado}</div>
                </div>
            `;
        }
    } else {
        // DESKTOP: Grid con 2 filas (2 × 4 = 8 items)
        container.innerHTML = '<div class="row g-3"></div>';
        const row = container.querySelector('.row');
        
        for (let i = 0; i < 8; i++) {
            const col = document.createElement('div');
            col.className = 'col-lg-3 col-md-4 col-6';
            col.style.height = '280px';
            
            if (i < itemsPagina.length) {
                const item = itemsPagina[i];
                const id = app.tipoActual === 'producto' ? item.id_producto : item.id_servicio;
                const nombre = app.tipoActual === 'producto' ? item.producto_nombre : item.servicio_descripcion;
                const precio = app.tipoActual === 'producto' ? item.producto_precio : item.servicio_precio;
                const foto = app.tipoActual === 'producto' && item.producto_foto ? item.producto_foto : null;
                
                const nombreEscapado = nombre.replace(/'/g, "\\'");
                const precioFormateado = parseFloat(precio).toLocaleString('es-AR');
                
                let contenidoCard = '';
                if (app.tipoActual === 'producto' && foto) {
                    contenidoCard = `
                        <div class="producto-foto" style="background-image: url('<?= url('/imagenes/productos/') ?>${foto}');"></div>
                        <div class="producto-nombre-small">${nombre}</div>
                    `;
                } else {
                    contenidoCard = `
                        <div class="servicio-nombre-large">${nombre}</div>
                    `;
                }
                
                col.innerHTML = `
                    <div class="producto-card-large" onclick="agregarAlCarrito(${id}, '${app.tipoActual}', '${nombreEscapado}', ${precio})" style="cursor: pointer; transition: all 0.2s;">
                        ${contenidoCard}
                        <div class="producto-precio">$ ${precioFormateado}</div>
                    </div>
                `;
            } else {
                col.innerHTML = '<div style="height: 100%;"></div>';
            }
            
            row.appendChild(col);
        }
    }
}

// Mover carrusel
function moverCarrusel(direccion) {
    const totalPaginas = Math.ceil(app.totalItems / app.itemsPorPagina);
    app.paginaCarrusel += direccion;
    
    if (app.paginaCarrusel < 0) app.paginaCarrusel = 0;
    if (app.paginaCarrusel >= totalPaginas) app.paginaCarrusel = totalPaginas - 1;
    
    mostrarPaginaCarrusel();
    actualizarControlesCarrusel();
}

// Actualizar visibilidad de controles del carrusel
function actualizarControlesCarrusel() {
    const totalPaginas = Math.ceil(app.totalItems / app.itemsPorPagina);
    const btnPrev = document.getElementById('btnCarruselPrev');
    const btnNext = document.getElementById('btnCarruselNext');
    
    // Mostrar controles solo si hay más de una página
    if (totalPaginas > 1) {
        btnPrev.style.display = app.paginaCarrusel > 0 ? 'block' : 'none';
        btnNext.style.display = app.paginaCarrusel < totalPaginas - 1 ? 'block' : 'none';
    } else {
        btnPrev.style.display = 'none';
        btnNext.style.display = 'none';
    }
}

// Agregar al carrito desde la galería
function agregarAlCarrito(id, tipo, nombre, precio) {
    const itemEnCarrito = app.carrito.find(c => c.id === id && c.tipo === tipo);
    
    if (itemEnCarrito) {
        itemEnCarrito.cantidad += 1;
    } else {
        app.carrito.push({ id, tipo, nombre, precio, cantidad: 1 });
    }
    
    actualizarUI();
}

// Modificar cantidad en carrito (desde el carrito lateral)
function modificarCantidad(id, tipo, cambio) {
    const itemEnCarrito = app.carrito.find(c => c.id === id && c.tipo === tipo);
    
    if (itemEnCarrito) {
        itemEnCarrito.cantidad += cambio;
        if (itemEnCarrito.cantidad <= 0) {
            app.carrito = app.carrito.filter(c => !(c.id === id && c.tipo === tipo));
        }
    }
    
    actualizarUI();
}

// Actualizar interfaz
function actualizarUI() {
    // Si hay items mostrados, re-renderizar la página actual del carrusel
    if (app.itemsActuales && app.itemsActuales.length > 0) {
        mostrarPaginaCarrusel();
        actualizarControlesCarrusel();
    }
    
    // Actualizar carrito lateral
    renderizarCarrito();
    
    // Habilitar/deshabilitar botones
    const carritoVacio = app.carrito.length === 0;
    document.getElementById('btnRegistrarSolicitud').disabled = carritoVacio;
    document.getElementById('btnLimpiarCarrito').disabled = carritoVacio;
}

// Renderizar carrito lateral
function renderizarCarrito() {
    const container = document.getElementById('listaCarrito');
    
    if (app.carrito.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-5">
                <p class="small mb-0">Carrito vacío</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = '';
    
    app.carrito.forEach(item => {
        const div = document.createElement('div');
        div.className = 'carrito-item';
        
        const nombreEscapado = item.nombre.replace(/'/g, "\\'");
        const precioFormateado = parseFloat(item.precio).toLocaleString('es-AR');
        
        div.innerHTML = `
            <div class="carrito-item-info">
                <span class="carrito-item-nombre" title="${item.nombre}">${item.nombre}</span>
                <span class="carrito-item-precio-unitario">$ ${precioFormateado}</span>
            </div>
            <div class="carrito-item-controles-wrapper">
                <div class="carrito-item-controles">
                    <button onclick="modificarCantidad(${item.id}, '${item.tipo}', -1)">-</button>
                    <span>${item.cantidad}</span>
                    <button onclick="modificarCantidad(${item.id}, '${item.tipo}', 1)">+</button>
                </div>
                <span class="carrito-item-borrar" onclick="eliminarDelCarrito(${item.id}, '${item.tipo}')" title="Eliminar">×</span>
            </div>
        `;
        container.appendChild(div);
    });
}

// Eliminar del carrito
function eliminarDelCarrito(id, tipo) {
    app.carrito = app.carrito.filter(c => !(c.id === id && c.tipo === tipo));
    actualizarUI();
}

// Limpiar todo el carrito
function limpiarCarrito() {
    Swal.fire({
        title: '¿Limpiar carrito?',
        text: 'Se eliminarán todos los items del carrito',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            app.carrito = [];
            actualizarUI();
            Swal.fire({
                title: 'Carrito limpiado',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}

// Confirmar solicitud
function confirmarSolicitud() {
    if (app.carrito.length === 0) {
        Swal.fire('Advertencia', 'El carrito está vacío', 'warning');
        return;
    }
    
    // Calcular total
    const total = app.carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    
    // Generar resumen detallado con precios
    let resumenHTML = `
        <div style="text-align: left;">
            <div style="max-height: 350px; overflow-y: auto; margin-bottom: 15px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="position: sticky; top: 0; background: white; border-bottom: 2px solid #dee2e6;">
                        <tr>
                            <th style="padding: 8px; text-align: left; font-size: 0.85rem; color: #6c757d;">Item</th>
                            <th style="padding: 8px; text-align: right; font-size: 0.85rem; color: #6c757d; width: 120px;">Precio Unitario</th>
                            <th style="padding: 8px; text-align: center; font-size: 0.85rem; color: #6c757d; width: 80px;">Cantidad</th>
                            <th style="padding: 8px; text-align: right; font-size: 0.85rem; color: #6c757d; width: 130px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
    `;
    
    app.carrito.forEach(item => {
        const precioUnitario = parseFloat(item.precio).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const subtotal = (item.precio * item.cantidad).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        
        resumenHTML += `
            <tr style="border-bottom: 1px solid #f1f1f1;">
                <td style="padding: 10px 8px; font-size: 0.9rem;">${item.nombre}</td>
                <td style="padding: 10px 8px; text-align: right; font-size: 0.85rem; color: #6c757d;">$ ${precioUnitario}</td>
                <td style="padding: 10px 8px; text-align: center; font-weight: 600; font-size: 0.9rem;">× ${item.cantidad}</td>
                <td style="padding: 10px 8px; text-align: right; font-weight: 500; font-size: 0.9rem;">$ ${subtotal}</td>
            </tr>
        `;
    });
    
    const totalFormateado = total.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    resumenHTML += `
                    </tbody>
                </table>
            </div>
            <div style="padding: 15px 8px; border-top: 2px solid #dee2e6; background: white; text-align: right;">
                <div style="font-size: 1.1rem; font-weight: 600; color: #212529;">
                    TOTAL: <span style="color: #28a745; font-size: 1.3rem;">$ ${totalFormateado}</span>
                </div>
            </div>
        </div>
    `;
    
    Swal.fire({
        title: '¿Confirmar solicitud?',
        html: resumenHTML,
        icon: 'question',
        width: '600px',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            registrarSolicitud();
        }
    });
}

// Registrar solicitud
function registrarSolicitud() {
    const form = document.getElementById('formSolicitud');
    document.getElementById('inputCarrito').value = JSON.stringify(app.carrito);
    form.submit();
}
</script>

<form id="formSolicitud" method="POST" style="display: none;">
    <input type="hidden" name="carrito" id="inputCarrito">
</form>

<style>
.list-group-item.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.list-group-item-action {
    cursor: pointer;
}

.list-group-item-action:hover {
    background-color: #f8f9fa;
}
</style>
