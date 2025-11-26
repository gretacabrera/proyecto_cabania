# Core - Framework Base del Sistema

Este directorio contiene el núcleo del framework MVC personalizado para el Sistema de Gestión de Cabañas. Aquí se encuentran las clases fundamentales que proporcionan la base para toda la aplicación, incluyendo integración completa con **MercadoPago Checkout Pro** para pagos online.

## 🏗️ **Arquitectura del Core Framework**

### 📁 **Componentes del Framework (13 archivos)**

#### **🚀 Clases Principales del Framework**

1. **`Application.php`** - Clase principal de la aplicación
   - Bootstrap del sistema
   - Inicialización de servicios
   - Manejo del ciclo de vida de la aplicación
   - **Definición de 180+ rutas** del sistema

2. **`Router.php`** - Sistema de enrutamiento
   - Manejo de URLs amigables
   - Mapeo de rutas a controladores
   - Soporte para parámetros dinámicos
   - Métodos HTTP (GET, POST, ANY)

3. **`Controller.php`** - Clase base para controladores
   - Funcionalidades comunes para todos los controladores
   - Integración con vistas y modelos
   - Manejo de respuestas HTTP

4. **`Model.php`** - Clase base para modelos
   - Operaciones CRUD genéricas
   - Integración con base de datos
   - Validaciones y relaciones

5. **`View.php`** - Sistema de renderizado de vistas
   - Motor de plantillas
   - Manejo de layouts
   - Escape de datos para seguridad

#### **🔧 Servicios de Soporte**

6. **`Database.php`** - Gestión de base de datos
   - Patrón Singleton para conexiones
   - Conexión MySQL con PDO/MySQLi
   - Pool de conexiones y configuración

7. **`Auth.php`** - Sistema de autenticación y autorización
   - Manejo de sesiones de usuario
   - Validación de permisos por perfil
   - Control de acceso a módulos

8. **`EmailService.php`** - Servicio de envío de emails
   - Integración con PHPMailer
   - Envío de emails transaccionales
   - **Confirmación de reservas con MercadoPago**
   - Verificación de cuentas y recuperación de contraseñas
   - Templates HTML personalizables con información completa de pagos

9. **`NotificationService.php`** - Servicio de notificaciones en tiempo real
   - Integración con Pusher PHP Server SDK v7.2.7
   - **Canales privados** por usuario (`private-user-{userId}`)
   - 4 tipos de notificaciones push para huéspedes
   - Autenticación segura de suscripciones

10. **`Validator.php`** - Sistema de validación
    - Validación de formularios
    - Reglas de validación personalizables
    - Mensajes de error localizados

11. **`Autoloader.php`** - Carga automática de clases
    - Implementación PSR-4
    - Mapeo de namespaces
    - Carga bajo demanda de clases

#### **⚙️ Archivos de Configuración y Utilidades**

12. **`config.php`** - Configuración central del sistema
    - Parámetros de base de datos
    - Configuraciones de ambiente
    - Constantes del sistema
    - **Credenciales de Pusher** (app_id, app_key, app_secret, cluster)

13. **`helpers.php`** - Funciones auxiliares globales
    - Utilidades para vistas
    - Helpers para debugging
    - Funciones de conveniencia---

## 🎯 **Detalles Técnicos por Componente**

### **1. Application.php - Núcleo de la Aplicación**

```php
namespace App\Core;

class Application
{
    private static $instance = null;
    private $router;
    private $config;

    public function __construct()
    {
        $this->initializeServices();
        $this->setupErrorHandling();
        $this->loadConfiguration();
    }

    /**
     * Inicializar servicios del framework
     */
    private function initializeServices()
    {
        // Autoloader
        // Database
        // Session management
        // Error handling
    }

    /**
     * Ejecutar la aplicación
     */
    public function run()
    {
        // Process request
        // Route to controller
        // Render response
    }
}
```

**Responsabilidades:**
- ✅ Bootstrap de la aplicación
- ✅ Inicialización de servicios core
- ✅ Manejo del ciclo de vida
- ✅ Configuración de entorno

### **2. Router.php - Sistema de Enrutamiento**

```php
namespace App\Core;

class Router
{
    private $routes = [];
    private $params = [];

    /**
     * Agregar ruta GET
     */
    public function get($pattern, $handler)
    {
        $this->addRoute('GET', $pattern, $handler);
    }

    /**
     * Agregar ruta POST
     */
    public function post($pattern, $handler)
    {
        $this->addRoute('POST', $pattern, $handler);
    }

    /**
     * Resolver ruta actual
     */
    public function resolve($uri, $method)
    {
        // Match pattern
        // Extract parameters
        // Return handler
    }
}
```

**Características:**
- ✅ Soporte para métodos HTTP (GET, POST, PUT, DELETE)
- ✅ Parámetros dinámicos en URLs
- ✅ Middleware support
- ✅ URLs amigables

### **3. Controller.php - Clase Base de Controladores**

```php
namespace App\Core;

abstract class Controller
{
    protected $view;
    protected $request;

    public function __construct()
    {
        $this->view = new View();
        $this->request = $_REQUEST;
    }

    /**
     * Renderizar vista con datos
     */
    protected function render($template, $data = [], $layout = null)
    {
        return $this->view->render($template, $data, $layout);
    }

    /**
     * Respuesta JSON
     */
    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Redireccionar
     */
    protected function redirect($url, $status = 302)
    {
        http_response_code($status);
        header("Location: $url");
        exit;
    }

    /**
     * Validar permisos
     */
    protected function checkPermission($module, $action = 'read')
    {
        return Auth::hasPermission($module, $action);
    }
}
```

**Funcionalidades:**
- ✅ Integración con sistema de vistas
- ✅ Manejo de respuestas HTTP
- ✅ Control de permisos
- ✅ Utilidades para desarrollo

### **4. Model.php - Clase Base de Modelos**

```php
namespace App\Core;

abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Buscar por ID
     */
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Obtener todos los registros
     */
    public function all($conditions = [], $limit = null, $offset = 0)
    {
        // Implementation
    }

    /**
     * Crear nuevo registro
     */
    public function create($data)
    {
        // Validate fillable fields
        // Insert into database
        // Return created record
    }

    /**
     * Actualizar registro
     */
    public function update($id, $data)
    {
        // Validate and update
        // Return updated record
    }

    /**
     * Eliminar registro
     */
    public function delete($id)
    {
        // Soft delete if configured
        // Hard delete otherwise
    }
}
```

**Características:**
- ✅ Operaciones CRUD genéricas
- ✅ Relaciones entre modelos
- ✅ Validación de datos
- ✅ Campos protegidos (fillable/hidden)

### **5. View.php - Sistema de Renderizado**

```php
namespace App\Core;

class View
{
    private $viewsPath;
    private $data = [];
    private $layout = 'main';

    public function __construct()
    {
        $this->viewsPath = __DIR__ . '/../Views/';
    }

    /**
     * Renderizar vista
     */
    public function render($template, $data = [], $layout = null)
    {
        $this->data = array_merge($this->data, $data);
        
        if ($layout) {
            return $this->renderWithLayout($template, $layout);
        }
        
        return $this->renderTemplate($template);
    }

    /**
     * Renderizar con layout
     */
    private function renderWithLayout($template, $layout)
    {
        $content = $this->renderTemplate($template);
        $this->data['content'] = $content;
        return $this->renderTemplate("shared/layouts/$layout");
    }

    /**
     * Escape de datos para seguridad
     */
    public function escape($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'escape'], $data);
        }
        return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
    }
}
```

**Funcionalidades:**
- ✅ Sistema de layouts y plantillas
- ✅ Escape automático de datos
- ✅ Inclusión de partials
- ✅ Variables globales de vista

### **6. Database.php - Gestión de Base de Datos**

```php
namespace App\Core;

class Database
{
    private static $instance = null;
    private $connection;
    private $config;

    private function __construct()
    {
        $this->config = require_once 'config.php';
        $this->connect();
    }

    /**
     * Singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }

    /**
     * Establecer conexión
     */
    private function connect()
    {
        try {
            $this->connection = new mysqli(
                $this->config['database']['host'],
                $this->config['database']['username'],
                $this->config['database']['password'],
                $this->config['database']['database']
            );
            
            $this->connection->set_charset('utf8mb4');
        } catch (Exception $e) {
            throw new Exception("Error de conexión: " . $e->getMessage());
        }
    }
}
```

**Características:**
- ✅ Patrón Singleton
- ✅ Conexión segura con MySQL
- ✅ Configuración centralizada
- ✅ Manejo de errores

### **7. Auth.php - Sistema de Autenticación**

```php
namespace App\Core;

class Auth
{
    private static $user = null;
    private static $permissions = [];

    /**
     * Verificar si usuario está autenticado
     */
    public static function check()
    {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    }

    /**
     * Obtener usuario actual
     */
    public static function user()
    {
        if (self::$user === null && self::check()) {
            // Load user from database
        }
        return self::$user;
    }

    /**
     * Verificar permisos
     */
    public static function hasPermission($module, $action = 'read')
    {
        if (!self::check()) {
            return false;
        }
        
        // Check user permissions
        return in_array("{$module}.{$action}", self::$permissions);
    }

    /**
     * Login de usuario
     */
    public static function login($username, $password)
    {
        // Validate credentials
        // Create session
        // Load permissions
    }

    /**
     * Logout
     */
    public static function logout()
    {
        session_destroy();
        self::$user = null;
        self::$permissions = [];
    }
}
```

**Funcionalidades:**
- ✅ Autenticación basada en sesiones
- ✅ Sistema de permisos por módulo
- ✅ Perfiles de usuario (admin, recepcionista, huésped)
- ✅ Validación de acceso

---

## ⚙️ **Configuración y Utilidades**

### **config.php - Configuración Central**

```php
<?php

return [
    'app' => [
        'name' => 'Casa de Palos - Sistema de Cabañas',
        'url' => 'http://localhost/proyecto_cabania',
        'debug' => true,
        'timezone' => 'America/Argentina/Buenos_Aires'
    ],
    
    'database' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'proyecto_cabania',
        'charset' => 'utf8mb4'
    ],
    
    'security' => [
        'session_timeout' => 3600,
        'csrf_protection' => true,
        'password_hash' => PASSWORD_DEFAULT
    ],
    
    'mail' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => '',
        'password' => '',
        'encryption' => 'tls'
    ]
];
```

### **helpers.php - Funciones Auxiliares**

```php
<?php

/**
 * Generar URL completa
 */
function url($path = '')
{
    return rtrim(config('app.url'), '/') . '/' . ltrim($path, '/');
}

/**
 * Obtener configuración
 */
function config($key, $default = null)
{
    static $config = null;
    if ($config === null) {
        $config = require_once 'config.php';
    }
    
    return array_get($config, $key, $default);
}

/**
 * Escape HTML
 */
function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Debug helper
 */
function dd(...$vars)
{
    foreach ($vars as $var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
    exit;
}

/**
 * Obtener valor de array con notación punto
 */
function array_get($array, $key, $default = null)
{
    if (is_null($key)) return $array;
    
    foreach (explode('.', $key) as $segment) {
        if (!is_array($array) || !array_key_exists($segment, $array)) {
            return $default;
        }
        $array = $array[$segment];
    }
    
    return $array;
}
```

---

## 🔐 **Seguridad y Mejores Prácticas**

### **Medidas de Seguridad Implementadas**

1. **Escape de Datos**
   ```php
   // Automático en vistas
   echo $this->escape($data);
   
   // Manual con helper
   echo e($userInput);
   ```

2. **Consultas Preparadas**
   ```php
   // En modelos
   $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
   $stmt->bind_param("s", $email);
   ```

3. **Validación de Permisos**
   ```php
   // En controladores
   if (!$this->checkPermission('usuarios', 'delete')) {
       return $this->error('Acceso denegado', 403);
   }
   ```

4. **Protección CSRF**
   ```php
   // Token en formularios
   <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
   ```

### **Patrones de Diseño Utilizados**

- ✅ **MVC (Model-View-Controller)**: Separación de responsabilidades
- ✅ **Singleton**: Para Database y Application
- ✅ **Factory**: Para creación de modelos
- ✅ **Observer**: Para eventos del sistema
- ✅ **Strategy**: Para validaciones y procesamiento

---

## 📊 **Estado del Framework**

### ✅ **Completado y Funcional**
- ✅ Arquitectura MVC completa con 13 componentes core
- ✅ Sistema de enrutamiento con soporte para parámetros dinámicos (180+ rutas)
- ✅ Autenticación y autorización por perfiles (Admin, Cajero, Recepcionista, Huésped)
- ✅ Conexión a base de datos con patrón Singleton
- ✅ Sistema de vistas con layouts organizados (admin, public, totem)
- ✅ Validación de datos en formularios
- ✅ Manejo de errores y excepciones
- ✅ Carga automática de clases (PSR-4)
- ✅ **Integración completa con MercadoPago SDK v3.7.1**
- ✅ **Checkout Pro con Wallet Brick** para pagos online
- ✅ **Webhooks IPN** para notificaciones de pago
- ✅ **Sistema de notificaciones Pusher** en tiempo real para huéspedes
- ✅ **Canales privados por usuario** con autenticación segura
- ✅ Servicio de email con PHPMailer integrado
- ✅ Sistema de verificación de email
- ✅ **Emails de confirmación de reserva** con datos de pago completos
- ✅ Helpers globales para desarrollo
- ✅ Configuración centralizada por ambiente (.env)

### 🎯 **En Producción**
- Sistema de reservas online completo con pasarela de pago
- **Flujo de pago MercadoPago**: Catálogo → Confirmar → Servicios → Resumen → Pasarela → Pago → Éxito
- **Notificaciones push en tiempo real** para huéspedes (reserva cercana, pago pendiente, pedido en cabaña, inconvenientes)
- Dashboards contextuales por perfil de usuario
- Exportación a Excel (.xlsx) y PDF
- Sistema multimodal de consumos (Admin, Huésped, Totem)
- Gestión integral de cabañas, huéspedes y productos
- Sistema de reportes ejecutivos
- **Transacciones con integridad referencial** (Reserva → Factura → Pago)

### 🔄 **Optimizaciones Continuas**
- **Performance**: Sistema de caché para consultas frecuentes
- **Testing**: Framework de pruebas unitarias
- **CLI**: Comandos de consola para tareas administrativas
- **Events**: Sistema de eventos y listeners
- **Middleware**: Pipeline de middleware para requests
- **API REST**: Endpoints para integración con apps móviles
- **Seguridad**: Validación de transacciones duplicadas en MercadoPago

---

## 🔧 **Uso y Extensión del Framework**

### **Crear un Nuevo Controlador**

```php
<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\YourModel;

class YourController extends Controller
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new YourModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Your Page',
            'records' => $this->model->all()
        ];

        return $this->render('your/template', $data);
    }
}
```

### **Crear un Nuevo Modelo**

```php
<?php

namespace App\Models;

use App\Core\Model;

class YourModel extends Model
{
    protected $table = 'your_table';
    protected $primaryKey = 'id';
    protected $fillable = ['field1', 'field2', 'field3'];

    /**
     * Métodos específicos del modelo
     */
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
```

---

---

## 🌐 **Sistema de Enrutamiento - Rutas del Proyecto**

### **Total de Rutas Configuradas: 180+**

El sistema implementa una arquitectura de rutas completa y organizada por módulos funcionales.

### **🏠 Rutas Principales y Autenticación (9 rutas)**

```php
GET  /                           → HomeController@index (landing pública)
GET  /catalogo                   → CatalogoController@index (catálogo de cabañas)
GET  /login                      → AuthController@login (formulario login)
POST /login                      → AuthController@login (procesar login)
GET  /logout                     → AuthController@logout
GET  /registro                   → AuthController@registro
POST /registro                   → AuthController@registro
GET  /verificar-email/{token}    → EmailVerificationController@verify
POST /pusher/auth                → PusherController@auth (autenticación canales privados)
```

### **🏨 Rutas de Reservas y Pagos (18 rutas)**

#### **Flujo de Reserva Online (7 rutas)**
```php
POST /reservas/confirmar         → ReservasController@confirmar (desde catálogo)
GET  /reservas/servicios         → ReservasController@servicios (seleccionar extras)
POST /reservas/servicios         → ReservasController@servicios (guardar servicios)
GET  /reservas/resumen           → ReservasController@resumen (resumen pre-pago)
GET  /reservas/pasarela          → ReservasController@pasarela (MercadoPago Wallet Brick)
```

#### **Callbacks de MercadoPago (4 rutas)**
```php
GET  /reservas/pago-exitoso      → ReservasController@pagoExitoso (success_url)
GET  /reservas/pago-fallido      → ReservasController@pagoFallido (failure_url)
GET  /reservas/pago-pendiente    → ReservasController@pagoPendiente (pending_url)
POST /reservas/webhook           → ReservasController@webhook (IPN notifications)
```

#### **Vista de Confirmación (2 rutas)**
```php
GET  /reservas/exito             → ReservasController@exito (confirmación final)
GET  /reservas/exito/{id}        → ReservasController@exito (con ID específico)
```

#### **Gestión Administrativa (5 rutas)**
```php
GET  /reservas                   → ReservasController@index (listado por perfil)
GET  /reservas/create            → ReservasController@create
POST /reservas/create            → ReservasController@create
GET  /reservas/{id}              → ReservasController@show
GET  /reservas/{id}/edit         → ReservasController@edit
POST /reservas/{id}/edit         → ReservasController@update
```

#### **Módulo Admin (Operaciones)**
```php
GET  /consumos                    → ConsumosController@index (listado)
GET  /consumos/create             → ConsumosController@create (formulario múltiple)
POST /consumos/create             → ConsumosController@create (guardar batch)
GET  /consumos/{id}               → ConsumosController@show (detalle)
GET  /consumos/{id}/edit          → ConsumosController@edit
POST /consumos/{id}/edit          → ConsumosController@update
GET  /consumos/{id}/delete        → ConsumosController@delete
GET  /consumos/exportar           → ConsumosController@exportar (Excel)
GET  /consumos/exportar-pdf       → ConsumosController@exportarPdf
```

#### **Módulo Huésped (Self-Service)**
```php
GET  /huesped/consumos                → HuespedConsumosController@index
GET  /huesped/consumos/solicitar      → HuespedConsumosController@solicitar
POST /huesped/consumos/solicitar      → HuespedConsumosController@solicitar
GET  /huesped/consumos/{id}/edit      → HuespedConsumosController@edit
POST /huesped/consumos/{id}/edit      → HuespedConsumosController@update
POST /huesped/consumos/{id}/delete    → HuespedConsumosController@delete
GET  /huesped/consumos/{id}           → HuespedConsumosController@show
```

#### **Módulo Totem (Sin Autenticación)**
```php
GET  /totem                       → TotemConsumosController@index
POST /totem/configurar            → TotemConsumosController@configurar
GET  /totem/menu                  → TotemConsumosController@menu
GET  /totem/solicitar             → TotemConsumosController@solicitar
POST /totem/pedido                → TotemConsumosController@pedido (AJAX)
GET  /totem/historial             → TotemConsumosController@historial
GET  /totem/reset                 → TotemConsumosController@reset
GET  /totem/producto/{id}/precio  → TotemConsumosController@getPrecioProducto (API)
```

### **Características del Sistema de Rutas**
- ✅ **Separación de módulos** por prefijo de URL (/admin, /huesped, /totem, /reservas)
- ✅ **RESTful conventions** para operaciones CRUD
- ✅ **APIs AJAX** para operaciones dinámicas
- ✅ **Parámetros dinámicos** en URLs con `{id}`, `{token}`
- ✅ **Métodos HTTP** apropiados (GET/POST/ANY)
- ✅ **Callbacks externos** sin autenticación (MercadoPago webhooks)
- ✅ **Redirecciones seguras** desde ngrok a localhost para callbacks
- ✅ **Rutas públicas** para catálogo y totem (sin requireAuth)

---

## 🔔 **Sistema de Notificaciones en Tiempo Real (Pusher)**

### **Servicio de Notificaciones**

El sistema implementa notificaciones push en tiempo real para usuarios huéspedes mediante **Pusher PHP Server SDK v7.2.7**.

### **NotificationService.php - Arquitectura**

```php
namespace App\Core;

use Pusher\Pusher;

class NotificationService
{
    private $pusher;
    private $enabled = false;

    public function __construct()
    {
        $config = require_once __DIR__ . '/config.php';
        
        if ($this->isConfigured($config)) {
            $this->pusher = new Pusher(
                $config['pusher']['app_key'],
                $config['pusher']['app_secret'],
                $config['pusher']['app_id'],
                [
                    'cluster' => $config['pusher']['app_cluster'],
                    'useTLS' => true
                ]
            );
            $this->enabled = true;
        }
    }

    /**
     * Enviar notificación a canal privado de usuario
     */
    private function send($channelName, $event, $data)
    {
        if (!$this->enabled) return false;

        try {
            $this->pusher->trigger($channelName, $event, $data);
            error_log("Notificación Pusher enviada: $event");
            return true;
        } catch (Exception $e) {
            error_log("Error enviando notificación Pusher: " . $e->getMessage());
            return false;
        }
    }
}
```

### **Tipos de Notificaciones Implementadas**

#### **1. Reserva Cercana** (`reserva-cercana`)
```php
public function notifyReservaCercana($reserva, $diasRestantes, $usuarioId)
{
    $channelName = "private-user-{$usuarioId}";
    
    $data = [
        'type' => 'reserva_cercana',
        'title' => 'Tu reserva está cerca',
        'message' => "Tu estadía comienza en {$diasRestantes} días",
        'reserva_id' => $reserva['id_reserva'],
        'cabania' => $reserva['cabania_nombre'],
        'fecha_inicio' => $reserva['reserva_fechainicio'],
        'url' => "/reservas/{$reserva['id_reserva']}",
        'icon' => 'fa-calendar-check',
        'color' => 'info',
        'sound' => false,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    return $this->send($channelName, 'reserva-cercana', $data);
}
```

#### **2. Pago Pendiente** (`pago-pendiente`)
```php
public function notifyPagoPendiente($reserva, $montoPendiente, $usuarioId)
{
    $channelName = "private-user-{$usuarioId}";
    
    $data = [
        'type' => 'pago_pendiente',
        'title' => 'Pago pendiente de confirmación',
        'message' => "Tu pago de \${$montoPendiente} está siendo procesado",
        'reserva_id' => $reserva['id_reserva'],
        'monto_pendiente' => $montoPendiente,
        'monto_total' => $reserva['reserva_total'],
        'url' => "/reservas/{$reserva['id_reserva']}",
        'icon' => 'fa-credit-card',
        'color' => 'warning',
        'sound' => true,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    return $this->send($channelName, 'pago-pendiente', $data);
}
```

#### **3. Pedido en Cabaña** (`pedido-cabania`)
```php
public function notifyPedidoCabania($consumo, $reserva, $usuarioId)
{
    $channelName = "private-user-{$usuarioId}";
    
    $data = [
        'type' => 'pedido_cabania',
        'title' => 'Pedido confirmado',
        'message' => "Tu pedido ha sido registrado en {$reserva['cabania_nombre']}",
        'consumo_id' => $consumo['id_consumo'],
        'cabania' => $reserva['cabania_nombre'],
        'cantidad_items' => $consumo['cantidad'],
        'monto_total' => $consumo['total'],
        'url' => "/huesped/consumos/{$consumo['id_consumo']}",
        'icon' => 'fa-shopping-cart',
        'color' => 'success',
        'sound' => true,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    return $this->send($channelName, 'pedido-cabania', $data);
}
```

#### **4. Inconveniente con Pedido** (`inconveniente-pedido`)
```php
public function notifyInconvenientePedido($consumo, $tipo, $descripcion, $usuarioId)
{
    $channelName = "private-user-{$usuarioId}";
    
    $data = [
        'type' => 'inconveniente_pedido',
        'title' => 'Inconveniente reportado',
        'message' => "Tipo: {$tipo} - {$descripcion}",
        'consumo_id' => $consumo['id_consumo'],
        'tipo_inconveniente' => $tipo,
        'descripcion' => $descripcion,
        'url' => "/huesped/consumos/{$consumo['id_consumo']}",
        'icon' => 'fa-exclamation-triangle',
        'color' => 'danger',
        'sound' => true,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    return $this->send($channelName, 'inconveniente-pedido', $data);
}
```

### **Arquitectura de Seguridad**

#### **Canales Privados por Usuario**
- Cada huésped tiene su propio canal: `private-user-{usuarioId}`
- Autenticación obligatoria mediante endpoint `/pusher/auth`
- Validación de que el usuario solo accede a su propio canal

#### **PusherController - Autenticación de Canales**
```php
namespace App\Controllers;

use App\Core\Controller;
use Pusher\Pusher;

class PusherController extends Controller
{
    public function auth()
    {
        // Verificar autenticación
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'No autenticado']);
            return;
        }

        $socketId = $_POST['socket_id'] ?? null;
        $channelName = $_POST['channel_name'] ?? null;

        // Verificar que el usuario solo acceda a su propio canal
        $userId = $_SESSION['usuario_id'];
        $expectedChannel = "private-user-{$userId}";

        if ($channelName !== $expectedChannel) {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado para este canal']);
            return;
        }

        // Generar firma de autenticación
        $pusher = new Pusher(/*...*/);
        $auth = $pusher->authorizeChannel($channelName, $socketId);

        header('Content-Type: application/json');
        echo json_encode($auth);
    }
}
```

### **Frontend - Integración JavaScript**

#### **Inicialización de Pusher (header.php)**
```javascript
// Solo para usuarios huéspedes autenticados
NotificationService.init('<?= $pusherKey ?>', '<?= $pusherCluster ?>', <?= $userId ?>);
```

#### **Cliente JavaScript (notifications.js)**
```javascript
function initPusher(appKey, cluster, userId) {
    pusher = new Pusher(appKey, {
        cluster: cluster,
        encrypted: true,
        authEndpoint: '/pusher/auth',
        auth: {
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        }
    });

    // Suscribirse al canal privado del usuario
    const channelName = `private-user-${userId}`;
    channel = pusher.subscribe(channelName);

    // Suscribirse a eventos
    channel.bind('reserva-cercana', handleReservaCercana);
    channel.bind('pago-pendiente', handlePagoPendiente);
    channel.bind('pedido-cabania', handlePedidoCabania);
    channel.bind('inconveniente-pedido', handleInconvenientePedido);
}
```

### **Flujo de Notificaciones**

```
1. Evento ocurre (pago, pedido, etc.)
   ↓
2. Controlador obtiene usuario_id
   → Reserva::getUsuarioIdFromReserva($reservaId)
   → JOIN: reserva → huesped_reserva → huesped → persona → usuario
   ↓
3. NotificationService envía a canal private-user-{usuarioId}
   ↓
4. Pusher distribuye a clientes suscritos
   ↓
5. Frontend del huésped recibe notificación
   ↓
6. Se activan:
   → Badge de notificaciones (actualiza contador)
   → Toast visual (esquina superior derecha)
   → Sonido diferenciado (según prioridad)
   → Entrada en dropdown de notificaciones
```

### **Configuración de Pusher**

Archivo `.env`:
```env
# Pusher (Notificaciones en tiempo real)
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=us2
```

Archivo `config.php`:
```php
'pusher' => [
    'app_id' => getenv('PUSHER_APP_ID') ?: '',
    'app_key' => getenv('PUSHER_APP_KEY') ?: '',
    'app_secret' => getenv('PUSHER_APP_SECRET') ?: '',
    'app_cluster' => getenv('PUSHER_APP_CLUSTER') ?: 'us2'
]
```

### **Características del Sistema**
- ✅ **4 tipos de notificaciones** push para huéspedes
- ✅ **Canales privados** con autenticación segura
- ✅ **Persistencia visual** con badge y dropdown
- ✅ **Toasts automáticos** con auto-cierre
- ✅ **Sonidos diferenciados** según prioridad
- ✅ **Enlaces directos** a detalles del evento
- ✅ **Manejo de errores** con logging detallado
- ✅ **Límite de 10 notificaciones** en dropdown

### **Plan Gratuito de Pusher**
- 200,000 mensajes/día
- 100 conexiones concurrentes
- Canales públicos y privados
- Suficiente para la mayoría de complejos de cabañas

---

## 💳 **Integración con MercadoPago**

### **SDK Utilizado**
- **Biblioteca**: `mercadopago/dx-php` v3.7.1
- **API**: Modern API (MercadoPagoConfig, PreferenceClient, PaymentClient)
- **Tipo de integración**: Checkout Pro con Wallet Brick

### **Flujo de Pago Implementado**

```
1. Usuario selecciona cabaña (Catálogo)
   ↓
2. Confirma fechas y datos (/reservas/confirmar)
   ↓
3. Selecciona servicios opcionales (/reservas/servicios)
   ↓
4. Revisa resumen de reserva (/reservas/resumen)
   ↓
5. Procede a pasarela de pago (/reservas/pasarela)
   → Se crea Preference en MercadoPago
   → Se muestra Wallet Brick (botón de pago)
   ↓
6. Usuario redirigido a MercadoPago (checkout)
   ↓
7. Completa pago en plataforma MercadoPago
   ↓
8. MercadoPago redirige a callback según resultado:
   → Éxito: /reservas/pago-exitoso
   → Fallo: /reservas/pago-fallido
   → Pendiente: /reservas/pago-pendiente
   ↓
9. Sistema procesa transacción:
   → Valida estado de reserva
   → Genera factura
   → Registra pago
   → Actualiza estado a CONFIRMADA
   → Envía email de confirmación
   ↓
10. Redirección a vista de éxito (/reservas/exito)
    → Muestra datos de confirmación
    → Número de reserva
    → Detalles de pago
```

### **Configuración de Credenciales**

Archivo `.env`:
```env
# MercadoPago Checkout Pro (Credenciales de PRUEBA)
MERCADOPAGO_PUBLIC_KEY=APP_USR-XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX
MERCADOPAGO_ACCESS_TOKEN=APP_USR-XXXXXXXXXXXXX-XXXXXX-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX-XXXXXXXXXX
MERCADOPAGO_BASE_URL=https://tu-ngrok-url.ngrok-free.dev/proyecto_cabania/
```

### **Estructura de Datos de Pago**

**Preferencia creada:**
```php
[
    'external_reference' => $reservaId,
    'items' => [[
        'title' => "Reserva Cabaña {$cabania_nombre}",
        'quantity' => 1,
        'unit_price' => $total_amount
    ]],
    'back_urls' => [
        'success' => "{$base_url}/reservas/pago-exitoso",
        'failure' => "{$base_url}/reservas/pago-fallido",
        'pending' => "{$base_url}/reservas/pago-pendiente"
    ],
    'notification_url' => "{$base_url}/reservas/webhook"
]
```

**Datos almacenados en sesión:**
```php
$_SESSION['reserva_exitosa'] = [
    'reserva_id' => 645,
    'total_pagado' => 53100.00,
    'fecha_confirmacion' => '2025-11-18 00:48:12',
    'metodo_pago_id' => 5, // MercadoPago
    'pago_id' => 561,
    'factura_id' => 234,
    'payment_id_mp' => '134276848982'
];
```

### **Manejo de Transacciones**

El sistema utiliza **transacciones SQL** para garantizar integridad:

```php
// En Reserva::confirmPayment()
$this->db->beginTransaction();

try {
    // 1. Validar reserva en estado PENDIENTE
    // 2. Obtener datos completos con consumos
    // 3. Generar factura
    // 4. Registrar pago vinculado a factura
    // 5. Actualizar estado reserva a CONFIRMADA
    
    $this->db->commit();
} catch (Exception $e) {
    $this->db->rollback();
    throw $e;
}
```

### **Emails de Confirmación**

**Datos incluidos en el email:**
- ✅ Nombre del huésped
- ✅ Cabaña reservada
- ✅ Fechas de check-in y check-out
- ✅ Cantidad de huéspedes (adultos y menores)
- ✅ Método de pago (MercadoPago)
- ✅ Monto total abonado
- ✅ Número de reserva
- ✅ Información del complejo

**Consultas SQL optimizadas:**
```php
// Método de pago: pago → factura → metododepago
SELECT mp.metododepago_descripcion 
FROM pago p
INNER JOIN factura f ON p.rela_factura = f.id_factura
INNER JOIN metododepago mp ON p.rela_metododepago = mp.id_metododepago
WHERE f.rela_reserva = ?

// Total pagado: suma de pagos de la reserva
SELECT SUM(p.pago_total) as total
FROM pago p
INNER JOIN factura f ON p.rela_factura = f.id_factura
WHERE f.rela_reserva = ?

// Cantidad de huéspedes: conteo por edad
SELECT 
    COUNT(CASE WHEN h.huesped_edad >= 18 THEN 1 END) as adultos,
    COUNT(CASE WHEN h.huesped_edad < 18 THEN 1 END) as menores
FROM huesped_reserva hr
INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
WHERE hr.rela_reserva = ?
```

---

## 🛡️ **Manejo de Errores en Pagos**

### **Detección de Reservas Duplicadas**

```php
// Si la reserva ya está CONFIRMADA
if ($reserva['rela_estadoreserva'] == 2) {
    // Obtener datos del pago existente
    // Retornar éxito sin procesar nuevamente
    return [
        'success' => true,
        'message' => 'La reserva ya estaba confirmada previamente',
        'already_confirmed' => true
    ];
}
```

### **Logging Detallado**

```php
error_log("=== INICIO pagoExitoso ===");
error_log("Params: collection_id=$collectionId, status=$status");
error_log("DEBUG: Reserva ID a procesar: $reservaId");
error_log("DEBUG: Llamando a confirmPayment");
error_log("DEBUG: Resultado de confirmPayment: " . json_encode($resultado));
```

### **Página de Debug (Modo desarrollo)**

Cuando `APP_DEBUG=true`, los errores muestran información completa:
- Parámetros GET recibidos de MercadoPago
- Contenido de sesión
- Stack trace del error
- Archivo y línea del error

---

## 📚 **Referencias Adicionales**

### **Documentación del Proyecto**
- **[README Principal](../README.md)** - Visión general y instalación
- **[Controllers/README.md](../Controllers/README.md)** - Controladores del sistema
- **[Models/README.md](../Models/README.md)** - Modelos de datos  
- **[Views/README.md](../Views/README.md)** - Sistema de vistas

### **Arquitectura del Sistema**
- **Patrón MVC**: Separación clara de responsabilidades
- **Active Record**: Modelos con lógica de datos integrada
- **Singleton**: Database y Application instances
- **Factory Pattern**: Para creación de objetos complejos
- **Observer Pattern**: Para eventos del sistema

### **Recursos Externos**
- **PHP Documentation**: https://www.php.net/docs.php
- **MySQL Reference**: https://dev.mysql.com/doc/
- **PSR Standards**: https://www.php-fig.org/psr/
- **Composer**: https://getcomposer.org/doc/

---

*Framework Core documentado y actualizado el 18/11/2025 - Casa de Palos Cabañas*  
*Arquitectura MVC personalizada con 13 componentes core integrados*  
*Integración completa con MercadoPago SDK v3.7.1 - Checkout Pro con Wallet Brick*  
*Sistema de notificaciones en tiempo real con Pusher PHP Server SDK v7.2.7*
*Sistema de pagos online funcional con transacciones garantizadas*
*Notificaciones push seguras para hu�spedes con canales privados*