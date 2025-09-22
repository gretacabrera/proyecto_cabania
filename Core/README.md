# Core - Framework Base del Sistema

Este directorio contiene el núcleo del framework MVC personalizado para el Sistema de Gestión de Cabañas. Aquí se encuentran las clases fundamentales que proporcionan la base para toda la aplicación.

## 🏗️ **Arquitectura del Core Framework**

### 📁 **Componentes del Framework (11 archivos)**

#### **🚀 Clases Principales del Framework**

1. **`Application.php`** - Clase principal de la aplicación
   - Bootstrap del sistema
   - Inicialización de servicios
   - Manejo del ciclo de vida de la aplicación

2. **`Router.php`** - Sistema de enrutamiento
   - Manejo de URLs amigables
   - Mapeo de rutas a controladores
   - Soporte para parámetros dinámicos

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

8. **`Validator.php`** - Sistema de validación
   - Validación de formularios
   - Reglas de validación personalizables
   - Mensajes de error localizados

9. **`Autoloader.php`** - Carga automática de clases
   - Implementación PSR-4
   - Mapeo de namespaces
   - Carga bajo demanda de clases

#### **⚙️ Archivos de Configuración y Utilidades**

10. **`config.php`** - Configuración central del sistema
    - Parámetros de base de datos
    - Configuraciones de ambiente
    - Constantes del sistema

11. **`helpers.php`** - Funciones auxiliares globales
    - Utilidades para vistas
    - Helpers para debugging
    - Funciones de conveniencia

---

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
- Arquitectura MVC completa
- Sistema de enrutamiento
- Autenticación y autorización
- Conexión a base de datos
- Sistema de vistas con layouts
- Validación de datos
- Manejo de errores
- Carga automática de clases

### ⏳ **En Desarrollo**
- Cache system
- Queue management  
- Event broadcasting
- CLI commands
- Testing framework

### 🚀 **Próximas Mejoras**
- **Performance**: Implementar sistema de caché
- **Testing**: Framework de pruebas unitarias
- **CLI**: Comandos de consola para mantenimiento
- **Events**: Sistema de eventos y listeners
- **Middleware**: Pipeline de middleware para requests

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

*Framework Core documentado el 25/09/2025 - Casa de Palos Cabañas*