# Sistema de Gestión de Cabañas - Casa de Palos

**Sistema integral para la gestión de cabañas, reservas online, huéspedes y servicios**

Desarrollado con PHP utilizando arquitectura MVC personalizada y paradigma de programación orientada a objetos.

**Proyecto:** SIRCA - Sistema Integral de Reservas de Cabañas y Alojamientos  
**Institución:** ISRMM - Desarrollo de Software  
**Integrantes:** Hernan Lopez, Greta Cabrera  
**Fecha:** Septiembre 2025

---

## 🎯 **Descripción del Proyecto**

Sistema web completo para la gestión integral de un complejo de cabañas que incluye:

### **Funcionalidades Principales**
- **🏠 Gestión de Cabañas**: CRUD completo con estados, disponibilidad y categorías
- **📅 Sistema de Reservas Online**: Proceso completo paso a paso para huéspedes
- **👥 Gestión de Huéspedes**: Registro, seguimiento y historial de clientes  
- **🛍️ Productos y Servicios**: Catálogo completo con inventario y consumos
- **🔐 Autenticación Multi-Perfil**: Admin, recepcionista, huésped con permisos específicos
- **📊 Sistema de Reportes**: Analytics completos y reportes ejecutivos
- **💳 Procesamiento de Pagos**: Simulación de múltiples métodos de pago
- **📧 Notificaciones**: Sistema automatizado de emails con PHPMailer

### **Características Técnicas**
- **Arquitectura MVC**: Framework personalizado con separación clara de responsabilidades
- **Base de Datos**: MySQL con diseño relacional optimizado
- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript ES6+
- **Backend**: PHP 7.4+ con POO, patrones de diseño y mejores prácticas
- **Seguridad**: Validaciones, escape de datos, consultas preparadas, CSRF protection

## 💻 **Requisitos del Sistema**

### **Servidor Web**
- **PHP**: 7.4 o superior (recomendado 8.0+)
- **MySQL**: 5.7 o superior / MariaDB 10.3+
- **Apache**: 2.4+ con mod_rewrite habilitado
- **Composer**: Para gestión de dependencias (opcional)

### **Extensiones PHP Requeridas**
```bash
php-mysqli       # Conexión MySQL
php-mbstring     # Strings multibyte
php-json         # Manejo JSON
php-session      # Sesiones
php-filter       # Validaciones
php-fileinfo     # Información de archivos
```

### **Configuración Recomendada**
```ini
memory_limit = 256M
upload_max_filesize = 32M
post_max_size = 32M
max_execution_time = 300
session.gc_maxlifetime = 3600
```

## 🏗️ **Arquitectura del Sistema**

### **Estructura del Proyecto**
```
proyecto_cabania/
├── 📁 Controllers/         # 27 Controladores MVC activos (públicos y administrativos) + 1 deprecated
│   ├── HomeController.php     # Página principal
│   ├── AuthController.php     # Autenticación
│   ├── ReservasController.php # Reservas online completas
│   ├── CabaniasController.php # Gestión de cabañas
│   └── ... (24 más)
├── 📁 Models/             # 25 Modelos de datos con relaciones
│   ├── Reserva.php           # Modelo principal de reservas
│   ├── Cabania.php           # Gestión de cabañas
│   ├── Usuario.php           # Usuarios del sistema
│   └── ... (22 más)
├── 📁 Views/              # Sistema completo de vistas organizadas
│   ├── 📁 public/            # 7 módulos públicos
│   │   ├── home.php             # Página de inicio
│   │   ├── 📁 auth/             # Login, registro
│   │   ├── 📁 catalogo/         # Catálogo público
│   │   ├── 📁 reservas/         # Sistema completo (5 vistas)
│   │   │   ├── confirmar.php       # Paso 1: Confirmación
│   │   │   ├── servicios.php       # Paso 2: Servicios adicionales
│   │   │   ├── resumen.php         # Paso 3: Vista previa
│   │   │   ├── pago.php            # Paso 4: Procesamiento
│   │   │   └── exito.php           # Paso 5: Confirmación final
│   │   └── ... (más módulos)
│   ├── 📁 admin/             # 24 módulos administrativos
│   │   ├── 📁 configuracion/    # 10 módulos básicos
│   │   ├── 📁 operaciones/      # 5 módulos de negocio
│   │   ├── 📁 seguridad/        # 5 módulos de sistema
│   │   └── 📁 reportes/         # 4 reportes especializados
│   └── 📁 shared/            # Componentes compartidos
├── 📁 Core/               # 11 clases del framework personalizado
│   ├── Application.php       # Bootstrap de la aplicación
│   ├── Router.php           # Sistema de enrutamiento
│   ├── Controller.php       # Clase base de controladores
│   ├── Model.php            # Clase base de modelos
│   ├── View.php             # Motor de renderizado
│   ├── Database.php         # Gestión de conexiones
│   ├── Auth.php             # Autenticación y autorización
│   └── ... (más componentes)
├── 📁 assets/             # Recursos frontend
│   ├── 📁 css/              # Estilos por módulo
│   ├── 📁 js/               # JavaScript por funcionalidad
│   └── 📁 images/           # Imágenes del sistema
├── 📁 imagenes/           # Archivos de usuarios
│   ├── 📁 cabanias/         # Fotos de cabañas
│   └── 📁 productos/        # Imágenes de productos
├── 📁 vendor/             # Dependencias (Composer)
├── 📄 bd.sql              # Estructura de base de datos
├── 📄 index.php           # Punto de entrada
├── 📄 .htaccess           # Configuración Apache
└── 📄 README.md           # Documentación principal
```

### **Componentes del Framework MVC Personalizado**

#### **🎯 Core Framework** (11 componentes)
- **Application**: Bootstrap y ciclo de vida
- **Router**: Enrutamiento con URLs amigables  
- **Controller**: Clase base con funcionalidades comunes
- **Model**: Active Record con operaciones CRUD
- **View**: Motor de plantillas con layouts
- **Database**: Singleton con conexiones optimizadas
- **Auth**: Autenticación multi-perfil y permisos
- **Validator**: Sistema completo de validaciones
- **Autoloader**: PSR-4 compatible
- **Config**: Configuración centralizada
- **Helpers**: Utilidades globales

#### **📊 Modelos de Datos** (25 modelos)
- **Alojamiento**: Cabania, Reserva, Ingreso, Salida
- **Usuarios**: Usuario, Persona, Perfil
- **Comercial**: Producto, Servicio, Consumo, Categoria
- **Configuración**: Estados, Métodos de pago, Períodos
- **Sistema**: Modulo, Menu, PerfilModulo

#### **🎮 Controladores** (27 controladores activos)
- **Públicos**: Home, Auth, Catalogo, Reservas (6)
- **Configuración**: Categorías, Estados, Métodos (10)
- **Operaciones**: Cabañas, Productos, Servicios (5)
- **Administración**: Usuarios, Perfiles, Módulos (5)
- **Reportes**: Analytics y reportes (1)
- ~~**Sistema**: ModuleController (eliminado)~~

#### **🖼️ Sistema de Vistas** (31+ elementos)
- **Público**: 7 módulos con sistema completo de reservas
- **Admin**: 24 módulos organizados por funcionalidad
- **Compartidas**: Layouts, componentes, errores

## 🚀 **Instalación y Configuración**

### **1. Preparación del Entorno**

```bash
# Clonar el repositorio
git clone https://github.com/gretacabrera/proyecto_cabania.git
cd proyecto_cabania

# Configurar permisos (Linux/Mac)
chmod -R 755 imagenes/
chmod -R 755 assets/
chmod 644 .htaccess
```

### **2. Configuración de Base de Datos**

```bash
# Crear base de datos
mysql -u root -p
CREATE DATABASE proyecto_cabania CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Importar estructura y datos
mysql -u root -p proyecto_cabania < bd.sql
```

### **3. Configuración de la Aplicación**

Editar `Core/config.php`:

```php
<?php
return [
    'app' => [
        'name' => 'Casa de Palos - Sistema de Cabañas',
        'url' => 'http://localhost/proyecto_cabania',  // Ajustar URL
        'debug' => true,  // false en producción
        'timezone' => 'America/Argentina/Buenos_Aires'
    ],
    
    'database' => [
        'host' => 'localhost',
        'username' => 'root',        // Ajustar credenciales
        'password' => '',            // Ajustar credenciales  
        'database' => 'proyecto_cabania',
        'charset' => 'utf8mb4'
    ],
    
    'mail' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'tu_email@gmail.com',    // Configurar email
        'password' => 'tu_app_password',       // App password de Gmail
        'encryption' => 'tls',
        'from' => [
            'address' => 'noreply@casadepalos.com',
            'name' => 'Casa de Palos Cabañas'
        ]
    ]
];
```

### **4. Configuración de Apache**

Asegurar que `mod_rewrite` esté habilitado:

```bash
# Ubuntu/Debian
sudo a2enmod rewrite
sudo systemctl restart apache2

# CentOS/RHEL  
sudo systemctl restart httpd
```

Verificar que `.htaccess` tenga:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Seguridad
<Files "*.php">
    Order Deny,Allow
    Allow from all
</Files>

# Bloquear acceso a archivos sensibles
<FilesMatch "(config\.php|\.sql|\.log)$">
    Order Deny,Allow
    Deny from all
</FilesMatch>
```

### **5. Configuración de PHP (Opcional)**

Crear `php.ini` local o configurar:

```ini
# Configuraciones recomendadas
memory_limit = 256M
upload_max_filesize = 32M  
post_max_size = 32M
max_execution_time = 300
session.gc_maxlifetime = 3600
date.timezone = America/Argentina/Buenos_Aires

# Para desarrollo
display_errors = On
error_reporting = E_ALL

# Para producción
display_errors = Off  
log_errors = On
error_log = /path/to/logs/php_errors.log
```

## 📖 **Guía de Uso del Sistema**

### **🌐 Acceso al Sistema**

#### **URLs Principales**
- **Inicio**: `http://localhost/proyecto_cabania/`
- **Login Administrativo**: `/auth/login`
- **Catálogo Público**: `/catalogo`
- **Sistema de Reservas**: `/reservas/confirmar`

#### **Usuarios de Prueba**
```sql
-- Administrador
Usuario: admin@casadepalos.com
Password: admin123

-- Recepcionista  
Usuario: recepcion@casadepalos.com
Password: recepcion123

-- Huésped de prueba
Usuario: huesped@example.com
Password: huesped123
```

### **🎯 Funcionalidades por Perfil**

#### **👑 Administrador**
- ✅ Acceso completo a todos los módulos
- ✅ Gestión de usuarios y perfiles  
- ✅ Configuración del sistema
- ✅ Reportes ejecutivos y analytics
- ✅ Gestión de cabañas y tarifas

#### **🏨 Recepcionista**
- ✅ Gestión de reservas y check-in/out
- ✅ Registro de consumos y servicios
- ✅ Gestión de huéspedes
- ✅ Reportes operativos
- ❌ Configuración de sistema

#### **🧳 Huésped**  
- ✅ Catálogo público de cabañas
- ✅ Sistema completo de reservas online
- ✅ Historial de reservas
- ✅ Comentarios y feedback
- ❌ Módulos administrativos

### **📋 Flujo de Reserva Online (Huéspedes)**

#### **Paso 1: Selección en Catálogo**
1. Navegar a `/catalogo`
2. Filtrar por fechas y capacidad
3. Seleccionar cabaña disponible
4. Hacer clic en "Reservar"

#### **Paso 2: Confirmación (`/reservas/confirmar`)**
- ✅ Validar datos de cabaña y fechas
- ✅ Configurar número de huéspedes
- ✅ Agregar observaciones especiales
- ✅ Calcular costo base por noches

#### **Paso 3: Servicios Adicionales (`/reservas/servicios`)**  
- 🎯 Seleccionar servicios extras (opcional)
- 🎯 Ver precios actualizados en tiempo real
- 🎯 Opción "Omitir" para continuar sin servicios

#### **Paso 4: Resumen (`/reservas/resumen`)**
- 📊 Vista previa completa de la reserva
- 📊 Desglose de costos detallado
- 📊 Aceptar términos y condiciones
- 📊 Botones "Modificar" o "Proceder al Pago"

#### **Paso 5: Pago (`/reservas/pago`)**
- 💳 **Tarjeta de Crédito**: Validación con ejemplo de rechazo
- 🏦 **Transferencia Bancaria**: Con datos completos
- 💵 **Efectivo**: Pago diferido al check-in
- ⚡ Procesamiento transaccional completo

#### **Paso 6: Confirmación (`/reservas/exito`)**
- 🎉 Confirmación con número de reserva
- 📧 Email automático con detalles
- 📱 Información práctica para la estadía
- 🎫 Opción de descargar comprobante

### **🔧 Panel Administrativo**

#### **Navegación Principal**
```
/admin/
├── 📊 Dashboard                    # Resumen ejecutivo
├── 🏠 Operaciones/                # Gestión diaria
│   ├── Cabañas                       # CRUD cabañas
│   ├── Reservas                      # Gestión de reservas  
│   ├── Productos                     # Inventario
│   ├── Servicios                     # Servicios ofrecidos
│   └── Consumos                      # Registro de consumos
├── ⚙️ Configuración/              # Configuración básica
│   ├── Categorías                    # Categorías de productos
│   ├── Estados                       # Estados del sistema
│   ├── Métodos de Pago              # Configuración de pagos
│   └── ... (7 más)
├── 👥 Seguridad/                  # Administración
│   ├── Usuarios                      # Gestión de usuarios
│   ├── Perfiles                      # Roles y permisos
│   └── Módulos                       # Configuración de módulos
└── 📈 Reportes/                   # Analytics
    ├── Dashboard                     # Métricas principales
    ├── Consumos                      # Reportes de ventas
    ├── Demográfico                   # Análisis de huéspedes
    └── Comentarios                   # Feedback de clientes
```

## 💻 **Desarrollo y Personalización**

### **📝 Convenciones de Código**

#### **Naming Conventions**
```php
// Clases: PascalCase
class ReservaController extends Controller

// Métodos y variables: camelCase  
public function crearReserva($datosReserva)

// Constantes: UPPER_SNAKE_CASE
const MAX_RESERVAS_POR_DIA = 50

// Archivos: PascalCase para clases, snake_case para vistas
ReservaController.php
reserva_detalle.php
```

#### **Estructura de Archivos**
```php
// Controladores
<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ModelName;

class ExampleController extends Controller {
    // Propiedades
    private $model;
    
    // Constructor
    public function __construct() { }
    
    // Métodos CRUD estándar
    public function index() { }
    public function create() { }
    public function store() { }
    public function show($id) { }  
    public function edit($id) { }
    public function update($id) { }
    public function delete($id) { }
}
```

### **🔧 Extensión del Framework**

#### **Crear Nuevo Controlador**
```bash
# 1. Crear archivo en Controllers/
touch Controllers/NuevoController.php

# 2. Implementar clase
<?php
namespace App\Controllers;
use App\Core\Controller;

class NuevoController extends Controller {
    public function index() {
        return $this->render('nuevo/index', [
            'title' => 'Nuevo Módulo'
        ]);
    }
}

# 3. Crear vistas correspondientes
mkdir Views/admin/nuevo/
touch Views/admin/nuevo/index.php
```

#### **Crear Nuevo Modelo**
```php
<?php
namespace App\Models;
use App\Core\Model;

class NuevoModel extends Model {
    protected $table = 'nueva_tabla';
    protected $primaryKey = 'id_nuevo';
    protected $fillable = ['campo1', 'campo2'];
    
    // Métodos específicos
    public function metodosPersonalizados() {
        // Implementación
    }
}
```

#### **Agregar Nuevas Rutas**
```php
// En Core/Router.php o archivo de rutas
$router->get('/nuevo-modulo', 'NuevoController@index');
$router->post('/nuevo-modulo/crear', 'NuevoController@store');
$router->get('/api/nuevo-modulo/{id}', 'NuevoController@api');
```

### **🐛 Debug y Desarrollo**

#### **Configuración de Debug**
```php
// En Core/config.php
'app' => [
    'debug' => true,        // Activar debug
    'log_level' => 'debug', // Nivel de logs
    'show_errors' => true   // Mostrar errores
]

// Helpers de debug
dd($variable);              // Dump and die
debug($variable);           // Debug sin detener
logger('mensaje', $data);   // Log personalizado
```

#### **Logs del Sistema**
```php
// Ubicación de logs
logs/error.log              # Errores PHP
logs/application.log        # Logs de aplicación  
logs/database.log          # Queries de BD
logs/auth.log              # Eventos de autenticación
```

### **🔍 Testing y Validación**

#### **Pruebas Manuales**
```bash
# URLs de prueba
http://localhost/proyecto_cabania/test/
http://localhost/proyecto_cabania/debug/database
http://localhost/proyecto_cabania/debug/auth
```

#### **Validación de Funcionalidades**
```php
// Checklist de validación
✅ Autenticación por perfil
✅ CRUD de entidades principales  
✅ Sistema de permisos
✅ Validación de formularios
✅ Seguridad (XSS, SQL Injection)
✅ Responsive design
✅ Integración con base de datos
```

## 🔐 **Seguridad y Mejores Prácticas**

### **🛡️ Medidas de Seguridad Implementadas**

#### **Autenticación y Autorización**
```php
// Multi-perfil con permisos granulares
Auth::hasPermission('cabanias', 'delete');    // Verificar permisos específicos
Auth::requireRole('administrador');           // Requerir rol específico
Auth::guest();                                // Solo usuarios no autenticados
Auth::user();                                 // Solo usuarios autenticados
```

#### **Protección contra Vulnerabilidades**
- ✅ **SQL Injection**: Consultas preparadas en todos los modelos
- ✅ **XSS**: Escape automático de datos en vistas
- ✅ **CSRF**: Tokens en formularios críticos  
- ✅ **Session Hijacking**: Regeneración de session IDs
- ✅ **Path Traversal**: Validación de rutas de archivos
- ✅ **Brute Force**: Rate limiting en login

```php
// Ejemplos de implementación
// Anti-XSS
echo $this->escape($userInput);
echo e($data);  // Helper function

// Anti-SQL Injection
$stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);

// CSRF Protection  
<input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
```

#### **Configuración Segura**
```apache
# .htaccess - Configuración de seguridad
<Files "*.php">
    Order Deny,Allow
    Allow from all
</Files>

# Bloquear archivos sensibles
<FilesMatch "(config\.php|\.sql|\.log)$">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# Headers de seguridad
Header set X-Content-Type-Options nosniff
Header set X-Frame-Options DENY  
Header set X-XSS-Protection "1; mode=block"
```

### **📊 Validaciones de Datos**

#### **Validación de Formularios**
```php
// Sistema de validación robusto
$validator = new Validator();
$rules = [
    'email' => 'required|email|unique:usuarios,usuario_email',
    'password' => 'required|min:8|confirmed',
    'fecha_reserva' => 'required|date|after:today'
];

if (!$validator->validate($data, $rules)) {
    return $this->error($validator->errors());
}
```

#### **Sanitización de Datos**
```php
// Limpieza automática de inputs
$cleanData = filter_input_array(INPUT_POST, [
    'nombre' => FILTER_SANITIZE_STRING,
    'email' => FILTER_SANITIZE_EMAIL,
    'precio' => FILTER_SANITIZE_NUMBER_FLOAT
]);
```

### **🔒 Manejo de Passwords**
```php
// Hash seguro de passwords
$hash = password_hash($password, PASSWORD_DEFAULT);

// Verificación
if (password_verify($inputPassword, $storedHash)) {
    // Login exitoso
}

// Política de passwords (recomendada)
- Mínimo 8 caracteres
- Al menos 1 mayúscula, 1 minúscula, 1 número
- - Cambio obligatorio cada 90 días (admin)
```

## 📊 **Estado del Proyecto y Roadmap**

### **✅ Completado y Funcional**

#### **Framework Core**
- ✅ Arquitectura MVC personalizada completa
- ✅ Sistema de enrutamiento con URLs amigables
- ✅ Autoloader PSR-4 compatible
- ✅ Gestión de base de datos con patrón Singleton
- ✅ Sistema de autenticación multi-perfil
- ✅ Motor de plantillas con layouts
- ✅ Validaciones y helpers globales

#### **Modelos de Datos**
- ✅ **25 modelos** implementados con relaciones
- ✅ Operaciones CRUD genéricas en clase base
- ✅ Validaciones específicas por modelo
- ✅ Métodos personalizados por entidad
- ✅ Integración completa con base de datos

#### **Controladores**
- ✅ **27 controladores** organizados por funcionalidad
- ✅ Separación público/administrativo
- ✅ Integración con sistema de permisos
- ✅ Manejo de respuestas HTTP y JSON
- ✅ Validación de acceso por perfil

#### **Sistema de Vistas**
- ✅ **31+ elementos** organizados jerárquicamente
- ✅ **Sistema completo de reservas online** (5 pasos)
- ✅ Panel administrativo con 24 módulos
- ✅ Componentes compartidos y layouts
- ✅ Diseño responsive con Bootstrap 5

#### **Funcionalidades de Negocio**
- ✅ Catálogo público de cabañas
- ✅ Sistema transaccional de reservas
- ✅ Gestión completa de huéspedes
- ✅ Inventario de productos y servicios
- ✅ Simulación de pasarela de pagos
- ✅ Sistema de reportes básico

### **⏳ En Desarrollo Activo**

#### **Sistema de Reservas Online** 
- 🔄 Integración con controladores específicos
- 🔄 Testing completo del flujo transaccional
- 🔄 Optimización de validaciones en tiempo real
- 🔄 Integración real con PHPMailer

#### **Panel Administrativo**
- 🔄 Migración completa de rutas de vistas
- 🔄 Implementación de paginación
- 🔄 Filtros avanzados en listados
- 🔄 Exportación de reportes (PDF/Excel)

#### **Performance y Seguridad**
- ✅ **Limpieza de código**: ModuleController eliminado
- 🔄 **Optimización de consultas complejas**
- 🔄 **Rate limiting para API endpoints**  
- 🔄 **Logging avanzado y monitoreo**

### **🚀 Próximas Funcionalidades (Roadmap)**

#### **Fase 1: Estabilización (Octubre 2025)**
- 🎯 **Testing Completo**: Unit tests para modelos y controladores
- 🎯 **Performance**: Sistema de caché Redis/Memcached
- 🎯 **API REST**: Endpoints para integración móvil
- 🎯 **Documentación**: API docs con Swagger

#### **Fase 2: Funcionalidades Avanzadas (Noviembre 2025)**
- 🚀 **Notificaciones Push**: WebSockets para updates en tiempo real
- 🚀 **Integración Pagos**: Mercado Pago, PayPal, Stripe
- 🚀 **Calendario Avanzado**: Gestión de disponibilidad visual
- 🚀 **CRM Básico**: Seguimiento de huéspedes y marketing

#### **Fase 3: Escalabilidad (Diciembre 2025)**
- 🌟 **Multiidioma**: Soporte i18n para inglés/portugués
- 🌟 **Multi-tenant**: Soporte para múltiples complejos
- 🌟 **App Móvil**: React Native para huéspedes
- 🌟 **BI Dashboard**: Analytics avanzados con Charts.js

### **🐛 Issues Conocidos y Prioridades**

#### **🚨 Alta Prioridad**
1. **Rutas de Vistas**: ✅ **COMPLETADO** - Controladores actualizados con nuevas rutas
2. **Email Integration**: Configurar PHPMailer para confirmaciones  
3. **Session Security**: Implementar session timeout y regeneration
4. **Error Handling**: Páginas de error personalizadas completas
5. ~~**Code Cleanup**: Remover ModuleController.php y rutas legacy~~ ✅ **COMPLETADO**

#### **⚠️ Media Prioridad**
1. **Database Optimization**: Indexar tablas para mejor performance
2. **Backup System**: Backups automáticos de BD
3. **File Upload**: Sistema robusto para imágenes de cabañas
4. **Logs Rotation**: Rotación automática de archivos de log

#### **ℹ️ Baja Prioridad**
1. **Dark Mode**: Tema oscuro para panel admin
2. **Social Login**: OAuth con Google/Facebook
3. **Chatbot**: Soporte automatizado básico
4. **PWA**: Progressive Web App para móviles

### **📈 Métricas del Proyecto**

#### **Estadísticas de Código**
- **Líneas de código**: ~15,000 LOC
- **Archivos PHP**: 64 archivos
- **Cobertura**: ~85% funcionalidades implementadas
- **Tiempo desarrollo**: 3 meses (Sep-Nov 2025)

#### **Arquitectura**
- **Framework**: MVC personalizado
- **Base de datos**: 25 tablas relacionales
- **Vistas**: 31+ elementos organizados
- **Controladores**: 27 controladores especializados
- **Modelos**: 25 modelos con relaciones

---

## 📞 **Soporte y Contribución**

### **👥 Equipo de Desarrollo**
- **Hernán López** - Backend Developer & Architecture
- **Greta Cabrera** - Frontend Developer & UX/UI

### **📧 Contacto**
- **Email**: proyecto.cabania@isrmm.edu.ar
- **Repository**: https://github.com/gretacabrera/proyecto_cabania
- **Documentation**: Ver archivos README.md en cada directorio

### **🤝 Contribuir**
1. Fork del repositorio
2. Crear branch feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push al branch (`git push origin feature/nueva-funcionalidad`)
5. Crear Pull Request

### **📝 License**
Este proyecto está bajo la licencia MIT. Ver archivo `LICENSE` para más detalles.

---

## 📚 **Documentación Adicional**

Para información detallada sobre cada componente, consultar:

- **[Controllers/README.md](Controllers/README.md)** - Documentación completa de controladores
- **[Core/README.md](Core/README.md)** - Framework y arquitectura interna
- **[Models/README.md](Models/README.md)** - Modelos de datos y relaciones  
- **[Views/README.md](Views/README.md)** - Sistema de vistas y flujos

---

*Proyecto desarrollado como parte del programa de Desarrollo de Software - ISRMM*  
*Casa de Palos Cabañas - Sistema Integral de Gestión*  
*Actualizado: 25 de Septiembre de 2025*
```
