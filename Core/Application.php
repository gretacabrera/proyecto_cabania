<?php

namespace App\Core;

/**
 * Clase principal de la aplicación
 */
class Application
{
    protected $router;
    protected $config;

    public function __construct()
    {
        $this->loadEnvironment();
        $this->initializeSession();
        $this->loadHelpers();
        $this->config = require __DIR__ . '/config.php';
        $this->initializeTimezone();
        $this->router = new Router();
        $this->registerRoutes();
    }

    /**
     * Cargar variables de entorno
     */
    protected function loadEnvironment()
    {
        if (file_exists(__DIR__ . '/../.env')) {
            $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                $parts = explode('=', trim($line), 2);
                if (count($parts) == 2) {
                    list($key, $value) = $parts;
                    putenv("$key=$value");
                }
            }
        }
    }

    /**
     * Inicializar sesión
     */
    protected function initializeSession()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Cargar funciones helper
     */
    protected function loadHelpers()
    {
        require_once __DIR__ . '/helpers.php';
    }

    /**
     * Inicializar zona horaria
     */
    protected function initializeTimezone()
    {
        if (isset($this->config['app']['timezone'])) {
            date_default_timezone_set($this->config['app']['timezone']);
        }
    }

    /**
     * Registrar rutas de la aplicación
     */
    protected function registerRoutes()
    {
        // Rutas de autenticación
        $this->router->get('/auth/login', 'AuthController@login');
        $this->router->post('/auth/login', 'AuthController@login');
        $this->router->get('/auth/logout', 'AuthController@logout');
        $this->router->get('/auth/register', 'AuthController@register');
        $this->router->post('/auth/register', 'AuthController@register');
        $this->router->any('/auth/change-password', 'AuthController@changePassword');
        
        // Rutas de recuperación de contraseña
        $this->router->get('/auth/forgot-password', 'AuthController@forgotPassword');
        $this->router->post('/auth/forgot-password', 'AuthController@forgotPassword');
        $this->router->get('/auth/reset-password', 'AuthController@resetPassword');
        $this->router->post('/auth/reset-password', 'AuthController@resetPassword');
        
        // Rutas de verificación de email
        $this->router->get('/auth/verify', 'EmailVerificationController@verify');
        $this->router->get('/auth/verification/status', 'EmailVerificationController@status');
        $this->router->get('/auth/verification/status/{id}', 'EmailVerificationController@status');
        $this->router->post('/auth/verification/resend', 'EmailVerificationController@resend');
        $this->router->get('/auth/verification/cleanup', 'EmailVerificationController@cleanup');

        // Rutas principales
        $this->router->get('/', 'HomeController@index');
        $this->router->get('/home', 'HomeController@index');
        $this->router->get('/about', 'HomeController@about');
        $this->router->any('/contact', 'HomeController@contact');
        
        // Rutas del catálogo público
        $this->router->get('/catalogo', 'CatalogoController@index');
        $this->router->post('/catalogo/checkAvailability', 'CatalogoController@checkAvailability');
        $this->router->get('/catalogo/getOccupiedDates', 'CatalogoController@getOccupiedDates');
        $this->router->post('/catalogo/reserve', 'CatalogoController@reserve');

        // Rutas administrativas de cabañas (requieren autenticación)
        $this->router->get('/cabanias', 'CabaniasController@index');
        $this->router->any('/cabanias/create', 'CabaniasController@create');
        $this->router->get('/cabanias/exportar', 'CabaniasController@exportar');
        $this->router->get('/cabanias/exportar-pdf', 'CabaniasController@exportarPdf');
        $this->router->get('/cabanias/{id}', 'CabaniasController@show');
        $this->router->any('/cabanias/{id}/edit', 'CabaniasController@edit');
        $this->router->post('/cabanias/{id}/delete', 'CabaniasController@delete');
        $this->router->post('/cabanias/{id}/restore', 'CabaniasController@restore');
        $this->router->post('/cabanias/{id}/estado', 'CabaniasController@cambiarEstado');
        $this->router->post('/cabanias/check-availability', 'CabaniasController@checkAvailability');

        // Rutas de reservas
        $this->router->get('/reservas', 'ReservasController@index');
        $this->router->any('/reservas/create', 'ReservasController@create');
        $this->router->any('/reservas/online', 'ReservasController@online');
        $this->router->get('/reservas/confirmar', 'ReservasController@confirmar');
        $this->router->post('/reservas/servicios', 'ReservasController@servicios');
        $this->router->post('/reservas/procesar-servicios', 'ReservasController@procesarServicios');
        $this->router->get('/reservas/resumen', 'ReservasController@resumen');
        $this->router->post('/reservas/proceder-pago', 'ReservasController@procederPago');
        $this->router->get('/reservas/pasarela', 'ReservasController@pasarela');
        $this->router->post('/reservas/pasarela', 'ReservasController@pasarela');
        $this->router->post('/reservas/procesar-pasarela', 'ReservasController@procesarPasarela');
        $this->router->post('/reservas/confirmar-pago', 'ReservasController@confirmarPago');
        $this->router->get('/reservas/exito', 'ReservasController@exito');
        $this->router->get('/reservas/exito/{id}', 'ReservasController@exito');
        $this->router->get('/reservas/limpiar-expiradas', 'ReservasController@limpiarReservasExpiradas'); // Para cron job
        $this->router->post('/reservas/{id}/cancelar', 'ReservasController@cancelarReserva'); // Cancelar por huésped
        $this->router->post('/reservas/{id}/anular', 'ReservasController@anularReserva'); // Anular por admin
        $this->router->get('/reservas/{id}', 'ReservasController@show');
        $this->router->any('/reservas/{id}/edit', 'ReservasController@edit');
        $this->router->post('/reservas/{id}/change-status', 'ReservasController@changeStatus');

        // ========== NUEVAS RUTAS DE MÓDULOS MIGRADOS ==========

        // Rutas de categorías
        $this->router->get('/categorias', 'CategoriasController@index');
        $this->router->any('/categorias/create', 'CategoriasController@create');
        $this->router->get('/categorias/exportar', 'CategoriasController@exportar');
        $this->router->get('/categorias/exportar-pdf', 'CategoriasController@exportarPdf');
        $this->router->get('/categorias/{id}', 'CategoriasController@show');
        $this->router->any('/categorias/{id}/edit', 'CategoriasController@edit');
        $this->router->post('/categorias/{id}/delete', 'CategoriasController@delete');
        $this->router->post('/categorias/{id}/restore', 'CategoriasController@restore');
        $this->router->post('/categorias/{id}/estado', 'CategoriasController@cambiarEstado');

        // Rutas de productos
        $this->router->get('/productos', 'ProductosController@index');
        $this->router->any('/productos/create', 'ProductosController@create');
        $this->router->get('/productos/exportar', 'ProductosController@exportar');
        $this->router->get('/productos/exportar-pdf', 'ProductosController@exportarPdf');
        $this->router->get('/productos/{id}', 'ProductosController@show');
        $this->router->any('/productos/{id}/edit', 'ProductosController@edit');
        $this->router->post('/productos/{id}/delete', 'ProductosController@delete');
        $this->router->post('/productos/{id}/restore', 'ProductosController@restore');
        $this->router->post('/productos/{id}/estado', 'ProductosController@cambiarEstado');

        // Rutas de usuarios
        $this->router->get('/usuarios', 'UsuariosController@index');
        $this->router->any('/usuarios/create', 'UsuariosController@create');
        $this->router->any('/usuarios/{id}/edit', 'UsuariosController@edit');
        $this->router->get('/usuarios/{id}/delete', 'UsuariosController@delete');
        $this->router->get('/usuarios/{id}/toggle-status', 'UsuariosController@toggleStatus');
        $this->router->get('/usuarios/{id}/profile', 'UsuariosController@profile');
        $this->router->get('/usuarios/profile', 'UsuariosController@profile'); // Perfil actual
        $this->router->get('/usuarios/{id}/resend-verification', 'UsuariosController@resendVerification');

        // Rutas de servicios
        $this->router->get('/servicios', 'ServiciosController@index');
        $this->router->any('/servicios/create', 'ServiciosController@create');
        $this->router->get('/servicios/exportar', 'ServiciosController@exportar');
        $this->router->get('/servicios/exportar-pdf', 'ServiciosController@exportarPdf');
        $this->router->get('/servicios/{id}', 'ServiciosController@show');
        $this->router->any('/servicios/{id}/edit', 'ServiciosController@edit');
        $this->router->post('/servicios/{id}/delete', 'ServiciosController@delete');
        $this->router->post('/servicios/{id}/restore', 'ServiciosController@restore');
        $this->router->post('/servicios/{id}/estado', 'ServiciosController@cambiarEstado');

        // Rutas de marcas
        $this->router->get('/marcas', 'MarcasController@index');
        $this->router->any('/marcas/create', 'MarcasController@create');
        $this->router->any('/marcas/{id}/edit', 'MarcasController@edit');
        $this->router->get('/marcas/{id}/delete', 'MarcasController@delete');
        $this->router->get('/marcas/{id}/restore', 'MarcasController@restore');
        $this->router->get('/marcas/search', 'MarcasController@search');
        $this->router->get('/marcas/{id}/toggle-status', 'MarcasController@toggleStatus');

        // Rutas de menús
        $this->router->get('/menus', 'MenusController@index');
        $this->router->any('/menus/create', 'MenusController@create');
        $this->router->any('/menus/{id}/edit', 'MenusController@edit');
        $this->router->get('/menus/{id}/delete', 'MenusController@delete');
        $this->router->get('/menus/{id}/restore', 'MenusController@restore');
        $this->router->get('/menus/search', 'MenusController@search');
        $this->router->get('/menus/hierarchy', 'MenusController@hierarchy');
        $this->router->get('/menus/{id}/toggle-status', 'MenusController@toggleStatus');
        $this->router->post('/menus/update-order', 'MenusController@updateOrder');

        // Rutas de métodos de pago
        $this->router->get('/metodos-pagos', 'MetodosPagosController@index');
        $this->router->any('/metodos-pagos/create', 'MetodosPagosController@create');
        $this->router->any('/metodos-pagos/{id}/edit', 'MetodosPagosController@edit');
        $this->router->get('/metodos-pagos/{id}/delete', 'MetodosPagosController@delete');
        $this->router->get('/metodos-pagos/{id}/restore', 'MetodosPagosController@restore');
        $this->router->get('/metodos-pagos/search', 'MetodosPagosController@search');
        $this->router->get('/metodos-pagos/{id}/toggle-status', 'MetodosPagosController@toggleStatus');
        $this->router->get('/metodos-pagos/stats', 'MetodosPagosController@stats');

        // Rutas de módulos del sistema
        $this->router->get('/modulos', 'ModulosController@index');
        $this->router->any('/modulos/create', 'ModulosController@create');
        $this->router->any('/modulos/{id}/edit', 'ModulosController@edit');
        $this->router->get('/modulos/{id}/delete', 'ModulosController@delete');
        $this->router->get('/modulos/{id}/restore', 'ModulosController@restore');
        $this->router->get('/modulos/search', 'ModulosController@search');
        $this->router->get('/modulos/hierarchy', 'ModulosController@hierarchy');
        $this->router->get('/modulos/{id}/toggle-status', 'ModulosController@toggleStatus');
        $this->router->post('/modulos/update-order', 'ModulosController@updateOrder');
        $this->router->get('/modulos/{id}/permissions', 'ModulosController@permissions');

        // Rutas de perfiles
        $this->router->get('/perfiles', 'PerfilesController@index');
        $this->router->any('/perfiles/create', 'PerfilesController@create');
        $this->router->any('/perfiles/{id}/edit', 'PerfilesController@edit');
        $this->router->get('/perfiles/{id}/delete', 'PerfilesController@delete');
        $this->router->get('/perfiles/{id}/restore', 'PerfilesController@restore');
        $this->router->get('/perfiles/search', 'PerfilesController@search');
        $this->router->get('/perfiles/{id}/modules', 'PerfilesController@modules');
        $this->router->get('/perfiles/{id}/users', 'PerfilesController@users');
        $this->router->get('/perfiles/{id}/toggle-status', 'PerfilesController@toggleStatus');
        $this->router->any('/perfiles/{id}/clone', 'PerfilesController@clone');

        // Rutas de estados de personas
        $this->router->get('/estados-personas', 'EstadosPersonasController@index');
        $this->router->any('/estados-personas/create', 'EstadosPersonasController@create');
        $this->router->any('/estados-personas/{id}/edit', 'EstadosPersonasController@edit');
        $this->router->get('/estados-personas/{id}/delete', 'EstadosPersonasController@delete');
        $this->router->get('/estados-personas/{id}/restore', 'EstadosPersonasController@restore');
        $this->router->get('/estados-personas/{id}/toggle-status', 'EstadosPersonasController@toggleStatus');

        // Rutas de estados de productos
        $this->router->get('/estados-productos', 'EstadosProductosController@index');
        $this->router->any('/estados-productos/create', 'EstadosProductosController@create');
        $this->router->any('/estados-productos/{id}/edit', 'EstadosProductosController@edit');
        $this->router->get('/estados-productos/{id}/delete', 'EstadosProductosController@delete');
        $this->router->get('/estados-productos/{id}/restore', 'EstadosProductosController@restore');
        $this->router->get('/estados-productos/{id}/toggle-status', 'EstadosProductosController@toggleStatus');
        $this->router->get('/estados-productos/search', 'EstadosProductosController@search');
        $this->router->get('/estados-productos/stats', 'EstadosProductosController@stats');

        // Rutas de estados de reservas
        $this->router->get('/estados-reservas', 'EstadosReservasController@index');
        $this->router->any('/estados-reservas/create', 'EstadosReservasController@create');
        $this->router->get('/estados-reservas/exportar', 'EstadosReservasController@exportar');
        $this->router->get('/estados-reservas/exportar-pdf', 'EstadosReservasController@exportarPdf');
        $this->router->get('/estados-reservas/{id}', 'EstadosReservasController@show');
        $this->router->any('/estados-reservas/{id}/edit', 'EstadosReservasController@edit');
        $this->router->post('/estados-reservas/{id}/delete', 'EstadosReservasController@delete');
        $this->router->post('/estados-reservas/{id}/restore', 'EstadosReservasController@restore');
        $this->router->post('/estados-reservas/{id}/estado', 'EstadosReservasController@cambiarEstado');

        // Rutas de tipos de servicios
        $this->router->get('/tipos-servicios', 'TiposServiciosController@index');
        $this->router->any('/tipos-servicios/create', 'TiposServiciosController@create');
        $this->router->any('/tipos-servicios/{id}/edit', 'TiposServiciosController@edit');
        $this->router->get('/tipos-servicios/{id}/delete', 'TiposServiciosController@delete');
        $this->router->get('/tipos-servicios/{id}/restore', 'TiposServiciosController@restore');
        $this->router->get('/tipos-servicios/{id}/toggle-status', 'TiposServiciosController@toggleStatus');
        $this->router->get('/tipos-servicios/search', 'TiposServiciosController@search');
        $this->router->get('/tipos-servicios/stats', 'TiposServiciosController@stats');

        // Rutas de tipos de contactos
        $this->router->get('/tipos-contactos', 'TiposContactosController@index');
        $this->router->any('/tipos-contactos/create', 'TiposContactosController@create');
        $this->router->any('/tipos-contactos/{id}/edit', 'TiposContactosController@edit');
        $this->router->get('/tipos-contactos/{id}/delete', 'TiposContactosController@delete');
        $this->router->get('/tipos-contactos/{id}/restore', 'TiposContactosController@restore');
        $this->router->get('/tipos-contactos/{id}/toggle-status', 'TiposContactosController@toggleStatus');
        $this->router->get('/tipos-contactos/search', 'TiposContactosController@search');
        $this->router->get('/tipos-contactos/stats', 'TiposContactosController@stats');

        // Rutas de periodos
        $this->router->get('/periodos', 'PeriodosController@index');
        $this->router->any('/periodos/create', 'PeriodosController@create');
        $this->router->any('/periodos/{id}/edit', 'PeriodosController@edit');
        $this->router->get('/periodos/{id}/delete', 'PeriodosController@delete');
        $this->router->get('/periodos/{id}/restore', 'PeriodosController@restore');
        $this->router->get('/periodos/{id}/toggle-status', 'PeriodosController@toggleStatus');
        $this->router->get('/periodos/search', 'PeriodosController@search');
        $this->router->get('/periodos/stats', 'PeriodosController@stats');

        // Rutas de condiciones de salud
        $this->router->get('/condiciones_salud', 'CondicionesSaludController@index');
        $this->router->any('/condiciones_salud/create', 'CondicionesSaludController@create');
        $this->router->get('/condiciones_salud/exportar', 'CondicionesSaludController@exportar');
        $this->router->get('/condiciones_salud/exportar-pdf', 'CondicionesSaludController@exportarPdf');
        $this->router->get('/condiciones_salud/{id}', 'CondicionesSaludController@show');
        $this->router->any('/condiciones_salud/{id}/edit', 'CondicionesSaludController@edit');
        $this->router->post('/condiciones_salud/{id}/delete', 'CondicionesSaludController@delete');
        $this->router->post('/condiciones_salud/{id}/restore', 'CondicionesSaludController@restore');
        $this->router->post('/condiciones_salud/{id}/estado', 'CondicionesSaludController@cambiarEstado');

        // Rutas de comentarios
        $this->router->get('/comentarios', 'ComentariosController@index');
        $this->router->get('/comentarios/public', 'ComentariosController@public'); // Vista pública
        $this->router->any('/comentarios/create', 'ComentariosController@create');
        $this->router->get('/comentarios/{id}', 'ComentariosController@show');
        $this->router->any('/comentarios/{id}/edit', 'ComentariosController@edit');
        $this->router->post('/comentarios/{id}/moderate', 'ComentariosController@moderate');
        $this->router->get('/comentarios/{id}/delete', 'ComentariosController@delete');
        $this->router->get('/comentarios/{id}/restore', 'ComentariosController@restore');
        $this->router->get('/comentarios/search', 'ComentariosController@search');
        $this->router->post('/comentarios/{id}/report', 'ComentariosController@report');

        // Rutas de consumos
        $this->router->get('/consumos', 'ConsumosController@index');
        $this->router->any('/consumos/create', 'ConsumosController@create');
        $this->router->get('/consumos/{id}', 'ConsumosController@show');
        $this->router->any('/consumos/{id}/edit', 'ConsumosController@edit');
        $this->router->get('/consumos/{id}/delete', 'ConsumosController@delete');
        $this->router->get('/consumos/{id}/restore', 'ConsumosController@restore');
        $this->router->get('/consumos/reserva/{id}', 'ConsumosController@byReserva');
        $this->router->any('/consumos/facturar/{id}', 'ConsumosController@facturar');
        $this->router->get('/consumos/producto/{id}/precio', 'ConsumosController@getPrecioProducto');
        $this->router->any('/consumos/reporte', 'ConsumosController@reporte');

        // Rutas de ingresos
        $this->router->get('/ingresos', 'IngresosController@index');
        $this->router->get('/ingresos/formulario', 'IngresosController@formulario');
        $this->router->post('/ingresos/registrar', 'IngresosController@alta');
        $this->router->get('/ingresos/stats', 'IngresosController@stats');
        $this->router->get('/ingresos/busqueda', 'IngresosController@busqueda');
        $this->router->post('/ingresos/buscar', 'IngresosController@buscar');
        $this->router->get('/ingresos/{id}/detalle', 'IngresosController@detalle');
        $this->router->get('/ingresos/ajax/reservas-para-ingreso', 'IngresosController@getReservasParaIngreso');

        // Rutas de salidas
        $this->router->get('/salidas', 'SalidasController@index');
        $this->router->get('/salidas/formulario', 'SalidasController@formulario');
        $this->router->post('/salidas/registrar', 'SalidasController@registrar');
        $this->router->get('/salidas/stats', 'SalidasController@stats');
        $this->router->get('/salidas/busqueda', 'SalidasController@busqueda');
        $this->router->post('/salidas/buscar', 'SalidasController@buscar');
        $this->router->get('/salidas/{id}/detalle', 'SalidasController@detalle');
        $this->router->get('/salidas/ajax/reservas-para-salida', 'SalidasController@getReservasParaSalida');
        $this->router->get('/salidas/{id}/calcular-pagos', 'SalidasController@calcularPagos');

        // ========== RUTAS DEL SISTEMA DE REPORTES ==========
        
        // Dashboard principal de reportes
        $this->router->get('/reportes', 'ReportesController@index');
        
        // Reporte de comentarios
        $this->router->get('/reportes/comentarios', 'ReportesController@comentarios');
        $this->router->get('/reportes/exportar-comentarios', 'ReportesController@exportarComentarios');
        
        // Reporte de consumos por cabaña
        $this->router->get('/reportes/consumos', 'ReportesController@consumos');
        $this->router->get('/reportes/exportar-consumos', 'ReportesController@exportarConsumos');
        
        // Reporte de productos por categoría
        $this->router->get('/reportes/productos', 'ReportesController@productos');
        $this->router->get('/reportes/exportar-productos', 'ReportesController@exportarProductos');
        
        // Reporte de temporadas altas
        $this->router->get('/reportes/temporadas', 'ReportesController@temporadas');
        $this->router->get('/reportes/exportar-temporadas', 'ReportesController@exportarTemporadas');
        
        // Análisis demográfico (grupos etarios)
        $this->router->get('/reportes/demografico', 'ReportesController@demografico');
        $this->router->get('/reportes/exportar-demografico', 'ReportesController@exportarDemografico');
        
        // Ventas mensuales (producto más vendido por mes)
        $this->router->get('/reportes/ventas-mensuales', 'ReportesController@ventasMensuales');
        $this->router->get('/reportes/exportar-ventas-mensuales', 'ReportesController@exportarVentasMensuales');
        
        // API para gráficos del dashboard
        $this->router->get('/reportes/api-graficos', 'ReportesController@apiGraficos');

        // ========== RUTAS DE PERFILES-MÓDULOS (TABLA PIVOT) ==========
        
        // Dashboard y listado principal
        $this->router->get('/perfiles-modulos', 'PerfilesModulosController@index');
        $this->router->get('/perfiles-modulos/stats', 'PerfilesModulosController@stats');
        
        // Crear nueva asignación
        $this->router->get('/perfiles-modulos/create', 'PerfilesModulosController@create');
        $this->router->post('/perfiles-modulos/create', 'PerfilesModulosController@create');
        
        // Editar asignación existente
        $this->router->get('/perfiles-modulos/{id}/edit', 'PerfilesModulosController@edit');
        $this->router->post('/perfiles-modulos/{id}/edit', 'PerfilesModulosController@edit');
        
        // Gestión de estado (baja/alta lógica)
        $this->router->get('/perfiles-modulos/{id}/delete', 'PerfilesModulosController@delete');
        $this->router->get('/perfiles-modulos/{id}/restore', 'PerfilesModulosController@restore');
        
        // Vistas especiales de relaciones
        $this->router->get('/perfiles-modulos/perfil/{id}/modulos', 'PerfilesModulosController@modulesByProfile');
        $this->router->get('/perfiles-modulos/modulo/{id}/perfiles', 'PerfilesModulosController@profilesByModule');
        
        // Gestión masiva de permisos
        $this->router->get('/perfiles-modulos/perfil/{id}/permisos', 'PerfilesModulosController@managePermissions');
        $this->router->post('/perfiles-modulos/perfil/{id}/permisos', 'PerfilesModulosController@managePermissions');
        
        // API y búsquedas
        $this->router->get('/perfiles-modulos/search', 'PerfilesModulosController@search');

    }

    /**
     * Ejecutar la aplicación
     */
    public function run()
    {
        try {
            $uri = $this->router->getCurrentUri();
            $method = $_SERVER['REQUEST_METHOD'];
            
            $result = $this->router->resolve($uri, $method);
            
            // Si el controlador devuelve contenido, mostrarlo
            if ($result !== null) {
                echo $result;
            }
            
        } catch (\Exception $e) {
            // Log del error
            error_log("Error en aplicación: " . $e->getMessage());
            
            // Mostrar página de error
            http_response_code(500);
            $view = new View();
            $view->error(500);
        }
    }

    /**
     * Obtener configuración
     */
    public function getConfig($key = null)
    {
        if ($key === null) {
            return $this->config;
        }

        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }

        return $value;
    }
}