<!-- Vista de solicitud de consumos - Versión Totem con Slider Horizontal -->
<div class="container-fluid" style="height: 100vh; overflow: hidden; padding: 0;">
    <div style="height: 100%; display: flex; flex-direction: column;">
        <!-- Card unificada -->
        <div class="card" style="height: 100%; border-radius: 0;">
            <!-- Header con navegación de etapas -->
            <div class="card-header bg-white border-bottom py-3">
                <div class="row align-items-center mb-3">
                    <div class="col-2 col-md-1">
                        <a href="<?= url('/totem/menu') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                    <div class="col-8 col-md-10 text-center">
                        <h4 class="mb-0">Solicitar Consumos</h4>
                        <small class="text-muted">
                            <i class="fas fa-home"></i> <?= htmlspecialchars($cabaniaNombre) ?> | 
                            <i class="fas fa-user"></i> <?= htmlspecialchars($huespedNombre) ?>
                        </small>
                    </div>
                    <div class="col-2 col-md-1 text-end">
                        <a href="<?= url('/totem/reset') ?>" class="btn btn-outline-danger">
                            <i class="fas fa-power-off"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Indicador de etapas -->
                <div class="d-flex justify-content-center gap-3">
                    <div class="etapa-indicador active" data-etapa="0" onclick="irEtapa(0)" style="cursor: pointer;">
                        <i class="fas fa-th-large"></i>
                        <small class="d-block">Tipo</small>
                    </div>
                    <div class="etapa-indicador" data-etapa="1" onclick="irEtapa(1)" style="cursor: pointer;">
                        <i class="fas fa-list"></i>
                        <small class="d-block">Categoría</small>
                    </div>
                    <div class="etapa-indicador" data-etapa="2" onclick="irEtapa(2)" style="cursor: pointer;">
                        <i class="fas fa-shopping-bag"></i>
                        <small class="d-block">Selección</small>
                    </div>
                    <div class="etapa-indicador" data-etapa="3" onclick="irEtapa(3)" style="cursor: pointer;">
                        <i class="fas fa-shopping-cart"></i>
                        <small class="d-block">Carrito</small>
                    </div>
                </div>
            </div>
            
            <!-- Contenedor del slider -->
            <div class="card-body p-0" style="height: calc(100% - 150px); overflow: hidden; position: relative;">
                <div id="slider" class="slider-container">
                    <!-- Etapa 0: Selección de Tipo -->
                    <div class="slider-etapa active" data-etapa="0">
                        <div class="etapa-content p-4 d-flex align-items-start justify-content-center" style="padding-top: 100px !important;">
                            <div class="w-100" style="max-width: 600px;">
                                <div class="d-grid gap-4">
                                    <button class="btn btn-outline-primary btn-lg py-5" onclick="seleccionarTipo('producto')">
                                        <i class="fas fa-box fa-4x d-block mb-3"></i>
                                        <h4 class="mb-2">Productos</h4>
                                        <small class="text-muted">Alimentos, bebidas y artículos</small>
                                    </button>
                                    <button class="btn btn-outline-primary btn-lg py-5" onclick="seleccionarTipo('servicio')">
                                        <i class="fas fa-concierge-bell fa-4x d-block mb-3"></i>
                                        <h4 class="mb-2">Servicios</h4>
                                        <small class="text-muted">Limpieza, mantenimiento y más</small>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Etapa 1: Categorías -->
                    <div class="slider-etapa" data-etapa="1">
                        <div class="etapa-content p-4">
                            <h5 class="mb-4">Seleccione una categoría</h5>
                            <div class="overflow-auto" style="max-height: calc(100vh - 350px);">
                                <div id="listaCategorias">
                                    <div class="text-muted text-center py-3">
                                        <small>Cargando categorías...</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="etapa-footer">
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary btn-lg" onclick="irEtapa(0)">
                                    <i class="fas fa-arrow-left"></i> Atrás
                                </button>
                                <button class="btn btn-primary btn-lg flex-grow-1" onclick="irEtapa(2)" id="btnSiguienteEtapa1" disabled>
                                    Siguiente <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Etapa 2: Productos/Servicios -->
                    <div class="slider-etapa" data-etapa="2">
                        <div class="etapa-content p-4 position-relative">
                            <div id="contenedorProductos" class="h-100 d-flex align-items-center justify-content-center">
                                <div class="text-center text-muted">
                                    <small>Cargando...</small>
                                </div>
                            </div>
                            
                            <!-- Flechas de navegación en los laterales del contenido -->
                            <button id="btnCarruselPrev" class="btn-carrusel-totem" style="display: none; position: absolute; left: 10px; top: 50%; transform: translateY(-50%); z-index: 20;" onclick="moverCarrusel(-1)">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button id="btnCarruselNext" class="btn-carrusel-totem" style="display: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 20;" onclick="moverCarrusel(1)">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        
                        <div class="etapa-footer">
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary btn-lg" onclick="irEtapa(1)">
                                    <i class="fas fa-arrow-left"></i> Atrás
                                </button>
                                <button class="btn btn-primary btn-lg flex-grow-1" onclick="irEtapa(3)">
                                    Ver Carrito <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Etapa 3: Carrito -->
                    <div class="slider-etapa" data-etapa="3">
                        <div class="etapa-content p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0">
                                    <i class="fas fa-shopping-cart"></i> Tu Carrito
                                </h5>
                                <button type="button" class="btn btn-outline-danger" id="btnLimpiarCarrito" onclick="limpiarCarrito()" disabled>
                                    <i class="fas fa-trash"></i> Limpiar
                                </button>
                            </div>
                            
                            <div class="overflow-auto mb-3" style="max-height: calc(100vh - 350px);">
                                <div id="listaCarrito">
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-shopping-cart fa-4x mb-3 d-block" style="opacity: 0.3;"></i>
                                        <p class="mb-0">Carrito vacío</p>
                                        <small>Agrega productos desde la etapa anterior</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="etapa-footer">
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary btn-lg" onclick="irEtapa(2)">
                                    <i class="fas fa-arrow-left"></i> Atrás
                                </button>
                                <button type="button" class="btn btn-success btn-lg flex-grow-1" id="btnRegistrarSolicitud" onclick="confirmarSolicitud()" disabled>
                                    <i class="fas fa-check"></i> Confirmar Pedido
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos totem con slider horizontal */
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

/* Slider horizontal */
.slider-container {
    display: flex;
    width: 400%;
    height: 100%;
    transition: transform 0.4s ease-in-out;
}

.slider-etapa {
    width: 25%;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 100%;
}

.etapa-content {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    min-height: 0;
}

.etapa-footer {
    padding: 20px;
    background: white;
    border-top: 2px solid #dee2e6;
    flex-shrink: 0;
}

/* Indicadores de etapa */
.etapa-indicador {
    flex: 1;
    text-align: center;
    padding: 10px 8px;
    border-bottom: 4px solid transparent;
    color: #6c757d;
    font-size: 0.85rem;
    transition: all 0.3s;
}

.etapa-indicador i {
    font-size: 1.5rem;
    margin-bottom: 4px;
}

.etapa-indicador.active {
    color: #0d6efd;
    border-bottom-color: #0d6efd;
}

.etapa-indicador.completed {
    color: #28a745;
}

/* Categorías */
.categoria-item {
    padding: 20px 25px;
    margin-bottom: 12px;
    border: 2px solid #e0e0e0;
    background: white;
    cursor: pointer;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 500;
    transition: all 0.2s;
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

/* Card de producto/servicio totem */
.producto-card-totem {
    border: 2px solid #dee2e6;
    padding: 30px;
    text-align: center;
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 500px;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.producto-card-totem:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    border-color: #0d6efd;
}

.producto-card-totem:active {
    transform: scale(0.98);
}

.producto-foto-totem {
    width: 100%;
    height: 280px;
    background-size: cover;
    background-position: center;
    border-radius: 10px;
    margin-bottom: 20px;
    background-color: #f8f9fa;
}

.producto-nombre-totem {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 15px;
    line-height: 1.4;
    color: #212529;
}

.servicio-nombre-totem {
    font-size: 1.8rem;
    font-weight: 600;
    margin: 80px 0;
    line-height: 1.5;
    color: #333;
}

.producto-precio-totem {
    font-size: 2rem;
    color: #28a745;
    font-weight: bold;
}

/* Flechas navegación producto */
.btn-carrusel-totem {
    width: 60px;
    height: 80px;
    background: rgba(255, 255, 255, 0.95);
    border: 2px solid rgba(0,0,0,0.15);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 10;
    transition: all 0.2s;
}

.btn-carrusel-totem:hover {
    background: white;
    box-shadow: 0 6px 16px rgba(0,0,0,0.25);
}

.btn-carrusel-totem i {
    color: #333;
    font-size: 1.8rem;
}

/* Carrito totem */
.carrito-item-totem {
    padding: 15px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 12px;
    background: white;
    font-size: 1rem;
}

.carrito-item-totem-nombre {
    font-weight: 600;
    font-size: 1.1rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #212529;
    margin-bottom: 8px;
}

.carrito-item-totem-precio {
    font-size: 1rem;
    color: #28a745;
    font-weight: 500;
}

.carrito-controles-totem {
    display: flex;
    align-items: center;
    gap: 10px;
    justify-content: space-between;
    margin-top: 10px;
}

.carrito-controles-totem button {
    width: 40px;
    height: 40px;
    padding: 0;
    border: 2px solid #dee2e6;
    background: white;
    border-radius: 6px;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    transition: all 0.2s;
}

.carrito-controles-totem button:hover {
    background: #f8f9fa;
}

.carrito-controles-totem span {
    font-weight: bold;
    font-size: 1.3rem;
    min-width: 40px;
    text-align: center;
}

.carrito-borrar-totem {
    color: #dc3545;
    font-size: 2rem;
    cursor: pointer;
    line-height: 1;
    transition: all 0.2s;
}

.carrito-borrar-totem:hover {
    color: #bb2d3b;
    transform: scale(1.1);
}

/* Scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script>
// Estado de la aplicación
const app = {
    etapaActual: 0,
    tipoSeleccionado: null,
    categoriaSeleccionada: null,
    carrito: [],
    paginaCarrusel: 0,
    itemsPorPagina: 1,
    totalItems: 0,
    itemsActuales: [],
    tipoActual: null
};

// Navegar entre etapas
function irEtapa(numeroEtapa) {
    // Validación: Si el carrito está vacío y no se seleccionó tipo, solo permitir etapa 0
    if (app.carrito.length === 0 && !app.tipoSeleccionado && numeroEtapa !== 0) {
        Swal.fire('Atención', 'Seleccione un tipo primero', 'warning');
        return;
    }
    
    // Validación: No permitir ir a Categoría sin tipo seleccionado
    if (numeroEtapa === 1 && !app.tipoSeleccionado) {
        Swal.fire('Atención', 'Seleccione un tipo primero', 'warning');
        return;
    }
    
    // Validación: No permitir ir a Selección sin categoría seleccionada
    if (numeroEtapa === 2 && !app.categoriaSeleccionada) {
        Swal.fire('Atención', 'Seleccione una categoría primero', 'warning');
        return;
    }
    
    app.etapaActual = numeroEtapa;
    
    // Mover slider
    const slider = document.getElementById('slider');
    slider.style.transform = `translateX(-${numeroEtapa * 25}%)`;
    
    // Actualizar indicadores
    document.querySelectorAll('.etapa-indicador').forEach((ind, idx) => {
        ind.classList.remove('active', 'completed');
        if (idx === numeroEtapa) {
            ind.classList.add('active');
        } else if (idx < numeroEtapa) {
            ind.classList.add('completed');
        }
    });
    
    // Si va a carrito, actualizar vista
    if (numeroEtapa === 3) {
        actualizarUI();
    }
}

// Seleccionar tipo
function seleccionarTipo(tipo) {
    app.tipoSeleccionado = tipo;
    app.categoriaSeleccionada = null;
    
    if (tipo === 'producto') {
        cargarCategorias();
    } else {
        cargarTiposServicio();
    }
    
    document.getElementById('btnSiguienteEtapa1').disabled = true;
    
    // Al seleccionar un tipo, abrir la etapa Categoría
    irEtapa(1);
}

// Cargar categorías
async function cargarCategorias() {
    try {
        const response = await fetch('<?= url('/totem/api/categorias') ?>');
        const data = await response.json();
        
        if (!data.success || !data.data || data.data.length === 0) {
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
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudieron cargar las categorías', 'error');
    }
}

// Cargar tipos de servicio
async function cargarTiposServicio() {
    try {
        const response = await fetch('<?= url('/totem/api/tipos-servicio') ?>');
        const data = await response.json();
        
        if (!data.success || !data.data || data.data.length === 0) {
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

// Seleccionar categoría
async function seleccionarCategoria(id, nombre, element) {
    app.categoriaSeleccionada = id;
    
    document.querySelectorAll('.categoria-item').forEach(item => {
        item.classList.remove('active');
    });
    element.classList.add('active');
    
    document.getElementById('btnSiguienteEtapa1').disabled = false;
    
    try {
        const response = await fetch(`<?= url('/totem/api/productos/') ?>${id}`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            renderizarProductos(data.data, 'producto');
        }
    } catch (error) {
        console.error('Error:', error);
    }
    
    // Al seleccionar una categoría, abrir la etapa Selección
    irEtapa(2);
}

// Seleccionar tipo de servicio
async function seleccionarTipoServicio(id, nombre, element) {
    app.categoriaSeleccionada = id;
    
    document.querySelectorAll('.categoria-item').forEach(item => {
        item.classList.remove('active');
    });
    element.classList.add('active');
    
    document.getElementById('btnSiguienteEtapa1').disabled = false;
    
    try {
        const response = await fetch(`<?= url('/totem/api/servicios/') ?>${id}`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            renderizarProductos(data.data, 'servicio');
        }
    } catch (error) {
        console.error('Error:', error);
    }
    
    // Al seleccionar una categoría (tipo servicio), abrir la etapa Selección
    irEtapa(2);
}

// Renderizar productos
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
    const item = app.itemsActuales[app.paginaCarrusel];
    
    if (!item) {
        container.innerHTML = `<div class="text-center text-muted"><small>No hay items disponibles</small></div>`;
        return;
    }
    
    const id = app.tipoActual === 'producto' ? item.id_producto : item.id_servicio;
    const nombre = app.tipoActual === 'producto' ? item.producto_nombre : item.servicio_descripcion;
    const precio = app.tipoActual === 'producto' ? item.producto_precio : item.servicio_precio;
    const foto = app.tipoActual === 'producto' && item.producto_foto ? item.producto_foto : null;
    
    const nombreEscapado = nombre.replace(/'/g, "\\'");
    const precioFormateado = parseFloat(precio).toLocaleString('es-AR');
    
    let contenidoCard = '';
    if (app.tipoActual === 'producto' && foto) {
        contenidoCard = `
            <div class="producto-foto-totem" style="background-image: url('<?= url('/imagenes/productos/') ?>${foto}');"></div>
            <div class="producto-nombre-totem">${nombre}</div>
        `;
    } else {
        contenidoCard = `
            <div class="servicio-nombre-totem">${nombre}</div>
        `;
    }
    
    container.innerHTML = `
        <div class="producto-card-totem" onclick="agregarAlCarrito(${id}, '${app.tipoActual}', '${nombreEscapado}', ${precio})">
            ${contenidoCard}
            <div class="producto-precio-totem">$ ${precioFormateado}</div>
        </div>
    `;
}

// Mover carrusel
function moverCarrusel(direccion) {
    app.paginaCarrusel += direccion;
    
    if (app.paginaCarrusel < 0) app.paginaCarrusel = 0;
    if (app.paginaCarrusel >= app.totalItems) app.paginaCarrusel = app.totalItems - 1;
    
    mostrarPaginaCarrusel();
    actualizarControlesCarrusel();
}

// Actualizar controles del carrusel
function actualizarControlesCarrusel() {
    const btnPrev = document.getElementById('btnCarruselPrev');
    const btnNext = document.getElementById('btnCarruselNext');
    
    if (app.totalItems > 1) {
        btnPrev.style.display = app.paginaCarrusel > 0 ? 'flex' : 'none';
        btnNext.style.display = app.paginaCarrusel < app.totalItems - 1 ? 'flex' : 'none';
    } else {
        btnPrev.style.display = 'none';
        btnNext.style.display = 'none';
    }
}

// Agregar al carrito
function agregarAlCarrito(id, tipo, nombre, precio) {
    const itemEnCarrito = app.carrito.find(c => c.id === id && c.tipo === tipo);
    
    if (itemEnCarrito) {
        itemEnCarrito.cantidad += 1;
    } else {
        app.carrito.push({ id, tipo, nombre, precio, cantidad: 1 });
    }
    
    // Feedback
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true
    });
    
    Toast.fire({
        icon: 'success',
        title: 'Agregado al carrito'
    });
    
    actualizarUI();
}

// Modificar cantidad
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

// Actualizar UI
function actualizarUI() {
    renderizarCarrito();
    
    const carritoVacio = app.carrito.length === 0;
    document.getElementById('btnRegistrarSolicitud').disabled = carritoVacio;
    document.getElementById('btnLimpiarCarrito').disabled = carritoVacio;
}

// Renderizar carrito
function renderizarCarrito() {
    const container = document.getElementById('listaCarrito');
    
    if (app.carrito.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="fas fa-shopping-cart fa-4x mb-3 d-block" style="opacity: 0.3;"></i>
                <p class="mb-0">Carrito vacío</p>
                <small>Agrega productos desde la etapa anterior</small>
            </div>
        `;
        return;
    }
    
    container.innerHTML = '';
    
    app.carrito.forEach(item => {
        const div = document.createElement('div');
        div.className = 'carrito-item-totem';
        
        const precioFormateado = parseFloat(item.precio).toLocaleString('es-AR');
        const subtotal = (item.precio * item.cantidad).toLocaleString('es-AR');
        
        div.innerHTML = `
            <div class="carrito-item-totem-nombre">${item.nombre}</div>
            <div class="carrito-item-totem-precio">$ ${precioFormateado} × ${item.cantidad} = $ ${subtotal}</div>
            <div class="carrito-controles-totem">
                <div class="d-flex align-items-center gap-2">
                    <button onclick="modificarCantidad(${item.id}, '${item.tipo}', -1)">-</button>
                    <span>${item.cantidad}</span>
                    <button onclick="modificarCantidad(${item.id}, '${item.tipo}', 1)">+</button>
                </div>
                <span class="carrito-borrar-totem" onclick="eliminarDelCarrito(${item.id}, '${item.tipo}')">×</span>
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

// Limpiar carrito
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
            app.carrito = [];
            actualizarUI();
        }
    });
}

// Confirmar solicitud
function confirmarSolicitud() {
    if (app.carrito.length === 0) {
        Swal.fire('Advertencia', 'El carrito está vacío', 'warning');
        return;
    }
    
    const total = app.carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    
    let resumenHTML = `
        <div style="text-align: left; font-size: 1rem;">
            <div style="max-height: 350px; overflow-y: auto; margin-bottom: 20px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="position: sticky; top: 0; background: white; border-bottom: 2px solid #dee2e6;">
                        <tr>
                            <th style="padding: 12px; text-align: left; color: #6c757d;">Item</th>
                            <th style="padding: 12px; text-align: center; color: #6c757d; width: 80px;">Cant.</th>
                            <th style="padding: 12px; text-align: right; color: #6c757d; width: 120px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
    `;
    
    app.carrito.forEach(item => {
        const subtotal = (item.precio * item.cantidad).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        resumenHTML += `
            <tr style="border-bottom: 1px solid #f1f1f1;">
                <td style="padding: 12px; font-size: 1rem;">${item.nombre}</td>
                <td style="padding: 12px; text-align: center; font-weight: 600;">× ${item.cantidad}</td>
                <td style="padding: 12px; text-align: right; font-weight: 500;">$ ${subtotal}</td>
            </tr>
        `;
    });
    
    const totalFormateado = total.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    resumenHTML += `
                    </tbody>
                </table>
            </div>
            <div style="padding: 20px 12px; border-top: 2px solid #dee2e6; text-align: right;">
                <strong>TOTAL: <span style="color: #28a745; font-size: 1.6rem;">$ ${totalFormateado}</span></strong>
            </div>
        </div>
    `;
    
    Swal.fire({
        title: '¿Confirmar pedido?',
        html: resumenHTML,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            enviarPedido();
        }
    });
}

// Enviar pedido al servidor
function enviarPedido() {
    // Preparar datos
    const items = [];
    for (let item of app.carrito) {
        items.push({
            tipo: item.tipo,
            id: item.id,
            cantidad: item.cantidad
        });
    }
    
    // Mostrar loading
    Swal.fire({
        title: 'Enviando pedido...',
        text: 'Procesando su solicitud',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Enviar al servidor
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
                text: data.message || 'Su pedido ha sido registrado correctamente',
                confirmButtonColor: '#28a745',
                timer: 3000
            }).then(() => {
                // Limpiar carrito y volver al inicio
                app.carrito = [];
                app.tipoSeleccionado = null;
                app.categoriaSeleccionada = null;
                actualizarUI();
                irEtapa(0);
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'No se pudo procesar el pedido',
                confirmButtonColor: '#d33'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo conectar con el servidor. Intente nuevamente.',
            confirmButtonColor: '#d33'
        });
        });
}
</script>