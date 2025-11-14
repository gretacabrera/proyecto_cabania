<!-- Vista de solicitud de consumos - Versión Mobile con Slider Horizontal -->
<div class="container-fluid" style="height: 100vh; overflow: hidden; padding: 0;">
    <div style="height: 100%; display: flex; flex-direction: column;">
        <!-- Card unificada -->
        <div class="card" style="height: 100%; border-radius: 0;">
            <!-- Header con navegación de etapas -->
            <div class="card-header bg-white border-bottom py-2">
                <div class="row align-items-center mb-2">
                    <div class="col-2 col-md-1">
                        <button id="btnVolverHome" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                    </div>
                    <div class="col-10 col-md-11 text-center">
                        <h6 class="mb-0">Solicitar Consumos</h6>
                    </div>
                </div>
                
                <!-- Indicador de etapas -->
                <div class="d-flex justify-content-center gap-2">
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
            <div class="card-body p-0" style="height: calc(100% - 100px); overflow: hidden; position: relative;">
                <div id="slider" class="slider-container">
                    <!-- Etapa 0: Selección de Tipo -->
                    <div class="slider-etapa active" data-etapa="0">
                        <div class="etapa-content p-3 d-flex align-items-start justify-content-center" style="padding-top: 60px !important;">
                            <div class="w-100" style="max-width: 400px;">
                                <div class="d-grid gap-3">
                                    <button class="btn btn-outline-primary btn-lg py-4" onclick="seleccionarTipo('producto')">
                                        <i class="fas fa-box fa-3x d-block mb-3"></i>
                                        <h5 class="mb-1">Productos</h5>
                                        <small class="text-muted">Alimentos, bebidas y artículos</small>
                                    </button>
                                    <button class="btn btn-outline-primary btn-lg py-4" onclick="seleccionarTipo('servicio')">
                                        <i class="fas fa-concierge-bell fa-3x d-block mb-3"></i>
                                        <h5 class="mb-1">Servicios</h5>
                                        <small class="text-muted">Limpieza, mantenimiento y más</small>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Etapa 1: Categorías -->
                    <div class="slider-etapa" data-etapa="1">
                        <div class="etapa-content p-3">
                            <h6 class="mb-3">Seleccione una categoría</h6>
                            <div class="overflow-auto" style="max-height: calc(100vh - 300px);">
                                <div id="listaCategorias">
                                    <div class="text-muted text-center py-3">
                                        <small>Cargando categorías...</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="etapa-footer">
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary" onclick="irEtapa(0)">
                                    <i class="fas fa-arrow-left"></i> Atrás
                                </button>
                                <button class="btn btn-primary flex-grow-1" onclick="irEtapa(2)" id="btnSiguienteEtapa1" disabled>
                                    Siguiente <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Etapa 2: Productos/Servicios -->
                    <div class="slider-etapa" data-etapa="2">
                        <div class="etapa-content p-3 position-relative">
                            <div id="contenedorProductos" class="h-100 d-flex align-items-center justify-content-center">
                                <div class="text-center text-muted">
                                    <small>Cargando...</small>
                                </div>
                            </div>
                            
                            <!-- Flechas de navegación en los laterales del contenido -->
                            <button id="btnCarruselPrev" class="btn-carrusel-mobile" style="display: none; position: absolute; left: 5px; top: 50%; transform: translateY(-50%); z-index: 20;" onclick="moverCarrusel(-1)">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button id="btnCarruselNext" class="btn-carrusel-mobile" style="display: none; position: absolute; right: 5px; top: 50%; transform: translateY(-50%); z-index: 20;" onclick="moverCarrusel(1)">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        
                        <div class="etapa-footer">
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary" onclick="irEtapa(1)">
                                    <i class="fas fa-arrow-left"></i> Atrás
                                </button>
                                <button class="btn btn-primary flex-grow-1" onclick="irEtapa(3)">
                                    Ver Carrito <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Etapa 3: Carrito -->
                    <div class="slider-etapa" data-etapa="3">
                        <div class="etapa-content p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0" style="width: 60%;">
                                    <i class="fas fa-shopping-cart"></i> Tu Carrito
                                </h6>
                                <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2" id="btnLimpiarCarrito" onclick="limpiarCarrito()" disabled>
                                    <i class="fas fa-trash"></i> Limpiar
                                </button>
                            </div>
                            
                            <div class="overflow-auto mb-3" style="max-height: calc(100vh - 300px);">
                                <div id="listaCarrito">
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-shopping-cart fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                                        <p class="mb-0">Carrito vacío</p>
                                        <small>Agrega productos desde la etapa anterior</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="etapa-footer">
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary" onclick="irEtapa(2)">
                                    <i class="fas fa-arrow-left"></i> Atrás
                                </button>
                                <button type="button" class="btn btn-success flex-grow-1" id="btnRegistrarSolicitud" onclick="confirmarSolicitud()" disabled>
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

<!-- Modal de Confirmación -->
<form id="formSolicitud" method="POST" style="display: none;">
    <input type="hidden" name="carrito" id="inputCarrito">
</form>

<style>
/* Estilos mobile con slider horizontal */
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
    transition: transform 0.3s ease-in-out;
}

.slider-etapa {
    width: 25%;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    height: 75%;
    min-height: 75%;
}

.etapa-content {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    min-height: 0;
}

.etapa-footer {
    padding: 12px 15px;
    background: white;
    border-top: 1px solid #dee2e6;
    flex-shrink: 0;
}

/* Indicadores de etapa */
.etapa-indicador {
    flex: 1;
    text-align: center;
    padding: 8px 4px;
    border-bottom: 3px solid transparent;
    color: #6c757d;
    font-size: 0.7rem;
    transition: all 0.3s;
}

.etapa-indicador i {
    font-size: 1.2rem;
    margin-bottom: 2px;
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
    padding: 12px 15px;
    margin-bottom: 8px;
    border: 1px solid #e0e0e0;
    background: white;
    cursor: pointer;
    border-radius: 6px;
    font-size: 0.9rem;
    transition: all 0.2s;
}

.categoria-item.active {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

/* Card de producto/servicio mobile */
.producto-card-mobile {
    border: 1px solid #dee2e6;
    padding: 20px;
    text-align: center;
    background: white;
    border-radius: 8px;
    width: 100%;
    max-width: 400px;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.producto-card-mobile:active {
    transform: scale(0.98);
}

.producto-foto-mobile {
    width: 100%;
    height: 200px;
    background-size: cover;
    background-position: center;
    border-radius: 8px;
    margin-bottom: 15px;
    background-color: #f8f9fa;
}

.producto-nombre-mobile {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 10px;
    line-height: 1.4;
    color: #212529;
}

.servicio-nombre-mobile {
    font-size: 1.4rem;
    font-weight: 600;
    margin: 50px 0;
    line-height: 1.5;
    color: #333;
}

.producto-precio-mobile {
    font-size: 1.4rem;
    color: #28a745;
    font-weight: bold;
}

/* Flechas navegación producto */
.btn-carrusel-mobile {
    width: 40px;
    height: 60px;
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(0,0,0,0.15);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    z-index: 10;
}

.btn-carrusel-mobile i {
    color: #333;
    font-size: 1.3rem;
}

/* Carrito mobile */
.carrito-item-mobile {
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    margin-bottom: 10px;
    background: white;
    font-size: 0.9rem;
}

.carrito-item-mobile-nombre {
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #212529;
    margin-bottom: 4px;
}

.carrito-item-mobile-precio {
    font-size: 0.85rem;
    color: #28a745;
    font-weight: 500;
}

.carrito-controles-mobile {
    display: flex;
    align-items: center;
    gap: 6px;
    justify-content: space-between;
    margin-top: 8px;
}

.carrito-controles-mobile button {
    width: 30px;
    height: 30px;
    padding: 0;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 4px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.carrito-controles-mobile span {
    font-weight: bold;
    font-size: 1rem;
    min-width: 30px;
    text-align: center;
}

.carrito-borrar-mobile {
    color: #dc3545;
    font-size: 1.5rem;
    cursor: pointer;
    line-height: 1;
}

/* Scrollbar */
::-webkit-scrollbar {
    width: 4px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 2px;
}
</style>

<script>
// Estado de la aplicación
const app = {
    reservaId: <?= $reserva['id_reserva'] ?>,
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
    // Validación 5: Si el carrito está vacío y no se seleccionó tipo, solo permitir etapa 0
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

// Botón volver al home
document.getElementById('btnVolverHome').addEventListener('click', function() {
    if (app.carrito.length > 0) {
        Swal.fire({
            title: '¿Salir?',
            text: 'Perderás los items del carrito',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, salir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= url('/huesped/consumos') ?>';
            }
        });
    } else {
        window.location.href = '<?= url('/huesped/consumos') ?>';
    }
});

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
    
    // Requisito 2: Al seleccionar un tipo, abrir la etapa Categoría
    irEtapa(1);
}

// Cargar categorías
async function cargarCategorias() {
    try {
        const response = await fetch('<?= url('/huesped/consumos/api/categorias') ?>');
        const data = await response.json();
        
        if (!data.success || !data.data || data.data.length === 0) {
            document.getElementById('listaCategorias').innerHTML = `
                <div class="text-muted text-center py-3">
                    <small>No hay categorías</small>
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
        const response = await fetch('<?= url('/huesped/consumos/api/tipos-servicio') ?>');
        const data = await response.json();
        
        if (!data.success || !data.data || data.data.length === 0) {
            document.getElementById('listaCategorias').innerHTML = `
                <div class="text-muted text-center py-3">
                    <small>No hay tipos de servicio</small>
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
        const response = await fetch(`<?= url('/huesped/consumos/api/productos/') ?>${id}`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            renderizarProductos(data.data, 'producto');
        }
    } catch (error) {
        console.error('Error:', error);
    }
    
    // Requisito 3: Al seleccionar una categoría, abrir la etapa Selección
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
        const response = await fetch(`<?= url('/huesped/consumos/api/servicios/') ?>${id}`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            renderizarProductos(data.data, 'servicio');
        }
    } catch (error) {
        console.error('Error:', error);
    }
    
    // Requisito 3: Al seleccionar una categoría (tipo servicio), abrir la etapa Selección
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
        container.innerHTML = `<div class="text-center text-muted"><small>No hay items</small></div>`;
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
            <div class="producto-foto-mobile" style="background-image: url('<?= url('/imagenes/productos/') ?>${foto}');"></div>
            <div class="producto-nombre-mobile">${nombre}</div>
        `;
    } else {
        contenidoCard = `
            <div class="servicio-nombre-mobile">${nombre}</div>
        `;
    }
    
    container.innerHTML = `
        <div class="producto-card-mobile" onclick="agregarAlCarrito(${id}, '${app.tipoActual}', '${nombreEscapado}', ${precio})">
            ${contenidoCard}
            <div class="producto-precio-mobile">$ ${precioFormateado}</div>
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
            <div class="text-center text-muted py-4">
                <i class="fas fa-shopping-cart fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                <p class="mb-0">Carrito vacío</p>
                <small>Agrega productos desde la etapa anterior</small>
            </div>
        `;
        return;
    }
    
    container.innerHTML = '';
    
    app.carrito.forEach(item => {
        const div = document.createElement('div');
        div.className = 'carrito-item-mobile';
        
        const precioFormateado = parseFloat(item.precio).toLocaleString('es-AR');
        const subtotal = (item.precio * item.cantidad).toLocaleString('es-AR');
        
        div.innerHTML = `
            <div class="carrito-item-mobile-nombre">${item.nombre}</div>
            <div class="carrito-item-mobile-precio">$ ${precioFormateado} × ${item.cantidad} = $ ${subtotal}</div>
            <div class="carrito-controles-mobile">
                <div class="d-flex align-items-center gap-2">
                    <button onclick="modificarCantidad(${item.id}, '${item.tipo}', -1)">-</button>
                    <span>${item.cantidad}</span>
                    <button onclick="modificarCantidad(${item.id}, '${item.tipo}', 1)">+</button>
                </div>
                <span class="carrito-borrar-mobile" onclick="eliminarDelCarrito(${item.id}, '${item.tipo}')">×</span>
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
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí',
        cancelButtonText: 'No'
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
        <div style="text-align: left; font-size: 0.9rem;">
            <div style="max-height: 300px; overflow-y: auto; margin-bottom: 15px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                    <thead style="position: sticky; top: 0; background: white; border-bottom: 2px solid #dee2e6;">
                        <tr>
                            <th style="padding: 8px; text-align: left; color: #6c757d;">Item</th>
                            <th style="padding: 8px; text-align: center; color: #6c757d; width: 60px;">Cant.</th>
                            <th style="padding: 8px; text-align: right; color: #6c757d; width: 90px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
    `;
    
    app.carrito.forEach(item => {
        const subtotal = (item.precio * item.cantidad).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        resumenHTML += `
            <tr style="border-bottom: 1px solid #f1f1f1;">
                <td style="padding: 10px 8px; font-size: 0.9rem;">${item.nombre}</td>
                <td style="padding: 10px 8px; text-align: center; font-weight: 600;">× ${item.cantidad}</td>
                <td style="padding: 10px 8px; text-align: right; font-weight: 500;">$ ${subtotal}</td>
            </tr>
        `;
    });
    
    const totalFormateado = total.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    resumenHTML += `
                    </tbody>
                </table>
            </div>
            <div style="padding: 15px 8px; border-top: 2px solid #dee2e6; text-align: right;">
                <strong>TOTAL: <span style="color: #28a745; font-size: 1.3rem;">$ ${totalFormateado}</span></strong>
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
        confirmButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('formSolicitud');
            document.getElementById('inputCarrito').value = JSON.stringify(app.carrito);
            form.submit();
        }
    });
}
</script>
