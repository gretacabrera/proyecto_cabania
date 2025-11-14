<!-- Vista de solicitud de consumos - Layout con carrito lateral -->
<div class="container-fluid" style="height: 100vh; max-height: 100vh; overflow: hidden; padding: 0; margin: 0;">
    <!-- Header integrado -->
    <div class="row g-0" style="height: 70px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        <div class="col-lg-8 d-flex align-items-center px-3">
            <div class="row w-100 align-items-center">
                <div class="col-md-4">
                    <h4 class="mb-0 text-white">
                        <i class="fas fa-home"></i> 
                        <?= htmlspecialchars($cabaniaNombre) ?>
                    </h4>
                    <small class="text-white opacity-75">Huésped: <?= htmlspecialchars($huespedNombre) ?></small>
                </div>
                <div class="col-md-4 text-center">
                    <h2 class="mb-0 text-white">Menú de Pedidos</h2>
                </div>
                <div class="col-md-4 text-end">
                    <a href="<?= url('/totem/historial') ?>" class="btn btn-light me-2">
                        <i class="fas fa-history"></i> Historial
                    </a>
                    <a href="<?= url('/totem/reset') ?>" class="btn btn-danger">
                        <i class="fas fa-power-off"></i> Salir
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-4 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.1); border-left: 1px solid rgba(255,255,255,0.2);">
            <h3 class="mb-0 text-white">
                <i class="fas fa-shopping-cart"></i> Carrito
            </h3>
        </div>
    </div>
    
    <!-- Contenido principal -->
    <div class="row g-0" style="height: calc(90vh - 70px); overflow: hidden;">
        <!-- Área principal de contenido -->
        <div class="col-lg-8 h-100" style="width: 65%;">
            <div class="h-100 d-flex flex-column" style="background: white; overflow: hidden;">
                <!-- Indicador de etapas -->
                <div class="bg-light border-bottom py-3 flex-shrink-0">
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
                    </div>
                </div>
                
                <!-- Contenedor del slider -->
                <div class="flex-grow-1" style="overflow: hidden; position: relative;">
                    <div id="sliderEtapas" class="slider-etapas-container">
                        <!-- Etapa 0: Selección de Tipo -->
                        <div class="slider-etapa" data-etapa="0">
                            <div class="h-100 d-flex flex-column justify-content-center align-items-center" style="width: 100%; height: 100%;">
                                <h5 class="mb-4 text-center">Seleccione el tipo de pedido</h5>
                                <div class="row g-4 justify-content-center" style="max-width: 700px;">
                                    <div class="col-6">
                                        <button class="btn btn-outline-primary btn-lg w-100" style="padding: 40px 20px; height: 220px; display: flex; flex-direction: column; align-items: center; justify-content: center;" onclick="seleccionarTipo('producto')">
                                            <i class="fas fa-box fa-4x d-block mb-3"></i>
                                            <h4 class="mb-2">Productos</h4>
                                            <p class="mb-0 text-muted" style="font-size: 0.9rem; line-height: 1.2;">Alimentos, bebidas y artículos</p>
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button class="btn btn-outline-primary btn-lg w-100" style="padding: 40px 20px; height: 220px; display: flex; flex-direction: column; align-items: center; justify-content: center;" onclick="seleccionarTipo('servicio')">
                                            <i class="fas fa-concierge-bell fa-4x d-block mb-3"></i>
                                            <h4 class="mb-2">Servicios</h4>
                                            <p class="mb-0 text-muted" style="font-size: 0.9rem; line-height: 1.2;">Limpieza, mantenimiento<br>y más</p>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Etapa 1: Categorías -->
                        <div class="slider-etapa" data-etapa="1">
                            <div class="h-100 d-flex flex-column" style="width: 100%; height: 100%; padding: 20px;">
                                <div class="position-relative flex-grow-1">
                                    <div id="listaCategorias" class="categorias-grid">
                                        <div class="text-muted text-center py-3">
                                            <small>Cargando categorías...</small>
                                        </div>
                                    </div>
                                    <!-- Flechas de navegación para categorías -->
                                    <button id="btnCategPrev" class="btn-carrusel-cat" style="display: none;" onclick="moverCarruselCategorias(-1)">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button id="btnCategNext" class="btn-carrusel-cat" style="display: none;" onclick="moverCarruselCategorias(1)">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Etapa 2: Productos -->
                        <div class="slider-etapa" data-etapa="2">
                            <div class="h-100 d-flex flex-column" style="width: 100%; height: 100%; padding: 20px;">
                                <div class="position-relative flex-grow-1">
                                    <div id="contenedorProductos" class="productos-grid">
                                        <div class="text-center text-muted">
                                            <small>Cargando...</small>
                                        </div>
                                    </div>
                                    <!-- Flechas de navegación para productos -->
                                    <button id="btnProdPrev" class="btn-carrusel-prod" style="display: none;" onclick="moverCarruselProductos(-1)">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button id="btnProdNext" class="btn-carrusel-prod" style="display: none;" onclick="moverCarruselProductos(1)">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Carrito lateral (siempre visible) -->
        <div class="col-lg-4 h-100" style="width: 35%; background-color: #f8f9fa;">
            <div class="h-100 d-flex flex-column">
                <div class="p-3 overflow-auto flex-grow-1">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnLimpiarCarrito" onclick="limpiarCarrito()" disabled>
                            <i class="fas fa-trash"></i> Limpiar
                        </button>
                    </div>
                    <div id="listaCarrito">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-shopping-cart fa-4x mb-3 d-block" style="opacity: 0.3;"></i>
                            <p class="mb-0">Carrito vacío</p>
                            <small>Agregue productos para comenzar</small>
                        </div>
                    </div>
                </div>
                <div class="p-3 bg-white border-top">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>TOTAL:</strong>
                            <h4 class="mb-0 text-success" id="totalCarrito">$0.00</h4>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success btn-lg w-100" id="btnRegistrarSolicitud" onclick="confirmarSolicitud()" disabled>
                        <i class="fas fa-check"></i> Confirmar Pedido
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>

html, body{
    overflow-y: hidden;
}

/* Contenedor del slider de etapas */
.slider-etapas-container {
    display: flex;
    width: 300%;
    height: 100%;
    transition: transform 0.4s ease-in-out;
}

.slider-etapa {
    width: 33.333%;
    flex-shrink: 0;
    height: 100%;
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

/* Grid de categorías (2 filas x 3 columnas) */
.categorias-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    grid-template-rows: repeat(2, 1fr);
    gap: 15px;
    height: 100%;
    width: 100%;
}

.categoria-item {
    padding: 20px;
    border: 3px solid #dee2e6;
    background: white;
    cursor: pointer;
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: 600;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    overflow: hidden;
    word-wrap: break-word;
    height: 100%;
    width: 100%;
}

.categoria-item:hover {
    background: #f8f9fa;
    border-color: #0d6efd;
    transform: translateY(-2px);
}

.categoria-item.active {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

/* Grid de productos (3 filas x 4 columnas) */
.productos-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-template-rows: repeat(3, 1fr);
    gap: 12px;
    height: 100%;
    width: 100%;
    overflow: hidden;
}

/* Card de producto/servicio totem */
.producto-card-totem {
    border: 2px solid #dee2e6;
    padding: 10px;
    text-align: center;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    width: 100%;
    overflow: hidden;
}

.producto-card-totem:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-color: #0d6efd;
}

.producto-card-totem:active {
    transform: scale(0.98);
}

.producto-foto-totem {
    width: 100%;
    height: 60%;
    max-height: 100px;
    background-size: cover;
    background-position: center;
    border-radius: 6px;
    margin-bottom: 8px;
    background-color: #f8f9fa;
    flex-shrink: 0;
}

.producto-nombre-totem {
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 6px;
    line-height: 1.2;
    color: #212529;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    flex-grow: 1;
}

.servicio-nombre-totem {
    font-size: 0.95rem;
    font-weight: 600;
    margin: 20px 0;
    line-height: 1.3;
    color: #333;
    flex-grow: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    word-wrap: break-word;
    text-align: center;
}

.producto-precio-totem {
    font-size: 1.1rem;
    color: #28a745;
    font-weight: bold;
    flex-shrink: 0;
}

/* Flechas navegación categorías */
.btn-carrusel-cat {
    width: 50px;
    height: 60px;
    background: #0d6efd;
    border: 2px solid #0d6efd;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 10;
    transition: all 0.2s;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
}

.btn-carrusel-cat:hover {
    background: #0b5ed7;
    border-color: #0b5ed7;
    box-shadow: 0 6px 16px rgba(0,0,0,0.25);
    transform: translateY(-50%) scale(1.05);
}

.btn-carrusel-cat i {
    color: white;
    font-size: 1.5rem;
}

#btnCategPrev {
    left: 10px;
}

#btnCategNext {
    right: 10px;
}

/* Flechas navegación productos */
.btn-carrusel-prod {
    width: 50px;
    height: 60px;
    background: #0d6efd;
    border: 2px solid #0d6efd;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 10;
    transition: all 0.2s;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
}

.btn-carrusel-prod:hover {
    background: #0b5ed7;
    border-color: #0b5ed7;
    box-shadow: 0 6px 16px rgba(0,0,0,0.25);
    transform: translateY(-50%) scale(1.05);
}

.btn-carrusel-prod i {
    color: white;
    font-size: 1.5rem;
}

#btnProdPrev {
    left: 10px;
}

#btnProdNext {
    right: 10px;
}

/* Carrito lateral */
.carrito-item-totem {
    padding: 12px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    margin-bottom: 10px;
    background: white;
    font-size: 0.9rem;
}

.carrito-item-totem-nombre {
    font-weight: 600;
    font-size: 0.95rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #212529;
    margin-bottom: 6px;
}

.carrito-item-totem-precio {
    font-size: 0.85rem;
    color: #28a745;
    font-weight: 500;
}

.carrito-controles-totem {
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: space-between;
    margin-top: 8px;
}

.carrito-controles-totem button {
    width: 32px;
    height: 32px;
    padding: 0;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 4px;
    font-size: 1rem;
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
    font-size: 1.1rem;
    min-width: 30px;
    text-align: center;
}

.carrito-borrar-totem {
    color: #dc3545;
    font-size: 1.5rem;
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
    
    // Para carrusel de categorías (2x3 = 6 por página)
    categoriasActuales: [],
    paginaCategoria: 0,
    categoriasPorPagina: 6,
    
    // Para carrusel de productos (3x4 = 12 por página)
    productosActuales: [],
    paginaProducto: 0,
    productosPorPagina: 12,
    
    tipoActual: null
};

// Navegar entre etapas
function irEtapa(numeroEtapa) {
    // Validaciones
    if (numeroEtapa === 1 && !app.tipoSeleccionado) {
        Swal.fire('Atención', 'Seleccione un tipo primero', 'warning');
        return;
    }
    
    if (numeroEtapa === 2 && !app.categoriaSeleccionada) {
        Swal.fire('Atención', 'Seleccione una categoría primero', 'warning');
        return;
    }
    
    app.etapaActual = numeroEtapa;
    
    // Mover slider
    const slider = document.getElementById('sliderEtapas');
    slider.style.transform = `translateX(-${numeroEtapa * 33.333}%)`;
    
    // Actualizar indicadores
    document.querySelectorAll('.etapa-indicador').forEach((ind, idx) => {
        ind.classList.remove('active', 'completed');
        if (idx === numeroEtapa) {
            ind.classList.add('active');
        } else if (idx < numeroEtapa) {
            ind.classList.add('completed');
        }
    });
    
    actualizarUI();
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
        
        app.categoriasActuales = data.data;
        app.paginaCategoria = 0;
        renderizarCategorias();
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
        
        app.categoriasActuales = data.data;
        app.paginaCategoria = 0;
        renderizarCategorias();
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudieron cargar los tipos de servicio', 'error');
    }
}

// Renderizar categorías (carrusel 2x3)
function renderizarCategorias() {
    const container = document.getElementById('listaCategorias');
    container.innerHTML = '';
    
    const inicio = app.paginaCategoria * app.categoriasPorPagina;
    const fin = inicio + app.categoriasPorPagina;
    const categoriasVisibles = app.categoriasActuales.slice(inicio, fin);
    
    categoriasVisibles.forEach(cat => {
        const div = document.createElement('div');
        div.className = 'categoria-item';
        
        if (app.tipoSeleccionado === 'producto') {
            div.textContent = cat.categoria_descripcion;
            div.onclick = () => seleccionarCategoria(cat.id_categoria, cat.categoria_descripcion);
        } else {
            div.textContent = cat.tiposervicio_descripcion;
            div.onclick = () => seleccionarTipoServicio(cat.id_tiposervicio, cat.tiposervicio_descripcion);
        }
        
        container.appendChild(div);
    });
    
    actualizarControlesCategorias();
}

// Mover carrusel de categorías
function moverCarruselCategorias(direccion) {
    const totalPaginas = Math.ceil(app.categoriasActuales.length / app.categoriasPorPagina);
    app.paginaCategoria += direccion;
    
    if (app.paginaCategoria < 0) app.paginaCategoria = 0;
    if (app.paginaCategoria >= totalPaginas) app.paginaCategoria = totalPaginas - 1;
    
    renderizarCategorias();
}

// Actualizar controles de categorías
function actualizarControlesCategorias() {
    const btnPrev = document.getElementById('btnCategPrev');
    const btnNext = document.getElementById('btnCategNext');
    const totalPaginas = Math.ceil(app.categoriasActuales.length / app.categoriasPorPagina);
    
    if (totalPaginas > 1) {
        btnPrev.style.display = app.paginaCategoria > 0 ? 'flex' : 'none';
        btnNext.style.display = app.paginaCategoria < totalPaginas - 1 ? 'flex' : 'none';
    } else {
        btnPrev.style.display = 'none';
        btnNext.style.display = 'none';
    }
}

// Seleccionar categoría
async function seleccionarCategoria(id, nombre) {
    app.categoriaSeleccionada = id;
    
    try {
        const response = await fetch(`<?= url('/totem/api/productos/') ?>${id}`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            app.productosActuales = data.data;
            app.tipoActual = 'producto';
            app.paginaProducto = 0;
            renderizarProductos();
            irEtapa(2);
        } else {
            Swal.fire('Información', 'No hay productos disponibles en esta categoría', 'info');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudieron cargar los productos', 'error');
    }
}

// Seleccionar tipo de servicio
async function seleccionarTipoServicio(id, nombre) {
    app.categoriaSeleccionada = id;
    
    try {
        const response = await fetch(`<?= url('/totem/api/servicios/') ?>${id}`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            app.productosActuales = data.data;
            app.tipoActual = 'servicio';
            app.paginaProducto = 0;
            renderizarProductos();
            irEtapa(2);
        } else {
            Swal.fire('Información', 'No hay servicios disponibles', 'info');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudieron cargar los servicios', 'error');
    }
}

// Renderizar productos (carrusel 3x4 = 12 por página)
function renderizarProductos() {
    const container = document.getElementById('contenedorProductos');
    container.innerHTML = '';
    
    const inicio = app.paginaProducto * app.productosPorPagina;
    const fin = inicio + app.productosPorPagina;
    const productosVisibles = app.productosActuales.slice(inicio, fin);
    
    productosVisibles.forEach(item => {
        const id = app.tipoActual === 'producto' ? item.id_producto : item.id_servicio;
        const nombre = app.tipoActual === 'producto' ? item.producto_nombre : item.servicio_descripcion;
        const precio = app.tipoActual === 'producto' ? item.producto_precio : item.servicio_precio;
        const foto = app.tipoActual === 'producto' && item.producto_foto ? item.producto_foto : null;
        
        const div = document.createElement('div');
        div.className = 'producto-card-totem';
        
        const nombreEscapado = nombre.replace(/'/g, "\\'");
        const precioFormateado = parseFloat(precio).toLocaleString('es-AR');
        
        if (app.tipoActual === 'producto' && foto) {
            div.innerHTML = `
                <div class="producto-foto-totem" style="background-image: url('<?= url('/imagenes/productos/') ?>${foto}');"></div>
                <div class="producto-nombre-totem">${nombre}</div>
                <div class="producto-precio-totem">$ ${precioFormateado}</div>
            `;
        } else {
            div.innerHTML = `
                <div class="servicio-nombre-totem">${nombre}</div>
                <div class="producto-precio-totem">$ ${precioFormateado}</div>
            `;
        }
        
        div.onclick = () => agregarAlCarrito(id, app.tipoActual, nombre, precio);
        container.appendChild(div);
    });
    
    actualizarControlesProductos();
}

// Mover carrusel de productos
function moverCarruselProductos(direccion) {
    const totalPaginas = Math.ceil(app.productosActuales.length / app.productosPorPagina);
    app.paginaProducto += direccion;
    
    if (app.paginaProducto < 0) app.paginaProducto = 0;
    if (app.paginaProducto >= totalPaginas) app.paginaProducto = totalPaginas - 1;
    
    renderizarProductos();
}

// Actualizar controles de productos
function actualizarControlesProductos() {
    const btnPrev = document.getElementById('btnProdPrev');
    const btnNext = document.getElementById('btnProdNext');
    const totalPaginas = Math.ceil(app.productosActuales.length / app.productosPorPagina);
    
    if (totalPaginas > 1) {
        btnPrev.style.display = app.paginaProducto > 0 ? 'flex' : 'none';
        btnNext.style.display = app.paginaProducto < totalPaginas - 1 ? 'flex' : 'none';
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
    calcularTotal();
    
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
                <small>Agregue productos para comenzar</small>
            </div>
        `;
        return;
    }
    
    container.innerHTML = '';
    
    app.carrito.forEach(item => {
        const div = document.createElement('div');
        div.className = 'carrito-item-totem';
        
        const precioFormateado = parseFloat(item.precio).toLocaleString('es-AR', { minimumFractionDigits: 2 });
        const subtotal = (item.precio * item.cantidad).toLocaleString('es-AR', { minimumFractionDigits: 2 });
        
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

// Calcular total
function calcularTotal() {
    const total = app.carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    document.getElementById('totalCarrito').textContent = `$${total.toLocaleString('es-AR', { minimumFractionDigits: 2 })}`;
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
                            <th style="padding: 12px; text-align: right; color: #6c757d; width: 120px;">Precio Unitario</th>
                            <th style="padding: 12px; text-align: center; color: #6c757d; width: 80px;">Cant.</th>
                            <th style="padding: 12px; text-align: right; color: #6c757d; width: 120px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
    `;
    
    app.carrito.forEach(item => {
        const precioUnitario = item.precio.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const subtotal = (item.precio * item.cantidad).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        resumenHTML += `
            <tr style="border-bottom: 1px solid #f1f1f1;">
                <td style="padding: 12px; font-size: 1rem;">${item.nombre}</td>
                <td style="padding: 12px; text-align: right; color: #6c757d;">$ ${precioUnitario}</td>
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
        cancelButtonColor: '#6c757d',
        width: '700px',
        customClass: {
            popup: 'swal-wide'
        }
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
