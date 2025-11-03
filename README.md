# Sistema de Gestión de Cabañas - Casa de Palos

**Sistema integral para la gestión de cabañas, reservas online, huéspedes y servicios**

Desarrollado con PHP utilizando arquitectura MVC personalizada y paradigma de programación orientada a objetos.

**Proyecto:** SIRCA - Sistema Integral de Reservas de Cabañas y Alojamientos  
**Institución:** ISRMM - Desarrollo de Software  
**Integrantes:** Hernan Lopez, Greta Cabrera, Horacio Ortiz
**Fecha:** Octubre 2025

---

## 🎯 **Descripción del Proyecto**

**Casa de Palos** es un sistema web integral para la gestión completa de un complejo de cabañas turísticas. Desarrollado con arquitectura MVC personalizada, proporciona una solución robusta tanto para la gestión administrativa como para la experiencia del huésped.

### **🌟 Funcionalidades Principales**

#### **Para Huéspedes (Sistema Público)**
- **🌐 Catálogo Público**: Exploración de cabañas con filtros avanzados
- **📅 Sistema de Reservas Online**: Proceso completo de 5 pasos con validaciones
- **💳 Simulación de Pagos**: Tarjeta, transferencia bancaria, efectivo
- **✨ Servicios Adicionales**: Spa, restaurante, tours y actividades
- **💬 Sistema de Comentarios**: Feedback y puntuación de estadías
- **📧 Confirmaciones Automáticas**: Emails con detalles de reserva

#### **Para Administración (Panel Interno)**
- **🏠 Gestión de Cabañas**: CRUD completo con estados, fotos y disponibilidad
- **📊 Control de Reservas**: Seguimiento completo desde creación hasta finalización
- **👥 Gestión de Huéspedes**: Registro, historial y condiciones especiales
- **🛍️ Inventario Completo**: Productos, servicios, marcas y categorías
- **🔐 Multi-Perfil**: Administrador, recepcionista, huésped con permisos granulares
- **📈 Reportes Avanzados**: Dashboard, analytics, consumos, demografía
- **⚙️ Configuración**: Estados, métodos de pago, períodos, tipos de servicios
- **🧾 **Sistema de Facturación**: Numeración automática correlativa por tipo de comprobante

### **🛠️ Stack Tecnológico**
- **Backend**: PHP 8.0+ con Programación Orientada a Objetos
- **Arquitectura**: MVC personalizado con patrón Active Record
- **Base de Datos**: MySQL 8.0 con 24 tablas relacionales + numeración automática
- **Frontend**: HTML5, CSS3, Bootstrap 5.3, JavaScript ES6+
- **Dependencias**: PHPMailer para emails, SweetAlert2 para UX
- **Seguridad**: Consultas preparadas, escape de datos, CSRF protection, validaciones
- **Facturación**: Sistema automático de numeración correlativa por tipo de comprobante

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
date.timezone = America/Argentina/Buenos_Aires
```

### **Configuración de Base de Datos**
La base de datos incluye **24 tablas principales** organizadas en módulos:

#### **📊 Entidades Principales (9 tablas)**
- `cabania` - Información de cabañas del complejo
- `reserva` - Reservas de huéspedes con estados dinámicos  
- `persona` - Datos personales de huéspedes y usuarios
- `usuario` - Usuarios del sistema (admin/recepcionista)
- `producto` - Inventario de productos vendibles
- `servicio` - Servicios ofrecidos (spa, tours, etc.)
- `consumo` - Registro de consumos de huéspedes
- `comentario` - Feedback y puntuaciones
- `factura` - Facturas con numeración automática correlativa

#### **⚙️ Tablas de Configuración (10 tablas)**
- `categoria` - Categorías de productos
- `marca` - Marcas de productos
- `estadopersona` - Estados de huéspedes
- `estadoproducto` - Estados de productos  
- `estadoreserva` - Estados de reservas (8 estados dinámicos)
- `condicionsalud` - Condiciones médicas especiales
- `metododepago` - Métodos de pago disponibles
- `periodo` - Períodos y temporadas
- `tipocontacto` - Tipos de contacto
- `tiposervicio` - Tipos de servicios

#### **🔐 Sistema de Seguridad (7 tablas)**
- `perfil` - Roles del sistema (admin, recepcionista, huésped)
- `modulo` - Módulos del sistema
- `perfil_modulo` - Permisos por perfil
- `menu` - Menús por perfil
- `contacto` - Información de contacto
- `pago` - Registro de transacciones
- `tipocomprobante` - Tipos de facturas (A, B, C, Ticket) con numeración automática

## 🏗️ **Arquitectura del Sistema**

### **Estructura del Proyecto**
```
proyecto_cabania/
├── 📁 Controllers/            # 27 Controladores MVC organizados por funcionalidad
│   ├── 🌐 Públicos (6):
│   │   ├── HomeController.php        # Página principal y landing
│   │   ├── AuthController.php        # Login, registro, recuperación
│   │   ├── CatalogoController.php    # Catálogo público de cabañas
│   │   ├── ReservasController.php    # Sistema de reservas online (5 pasos)
│   │   ├── ComentariosController.php # Feedback de huéspedes
│   │   └── ... (2 más)
│   ├── ⚙️ Configuración (10):
│   │   ├── CategoriasController.php  # Categorías de productos
│   │   ├── EstadosReservasController.php # Estados dinámicos de reservas
│   │   ├── MetodosPagosController.php # Métodos de pago
│   │   └── ... (7 más)
│   ├── 🏢 Operaciones (5):
│   │   ├── CabaniasController.php    # Gestión de cabañas
│   │   ├── ProductosController.php   # Inventario y productos
│   │   ├── ServiciosController.php   # Servicios ofrecidos
│   │   └── ... (2 más)
│   ├── 🔐 Seguridad (5):
│   │   ├── UsuariosController.php    # Gestión de usuarios
│   │   ├── PerfilesController.php    # Roles y permisos
│   │   └── ... (3 más)
│   └── 📊 Reportes (1):
│       └── ReportesController.php    # Analytics y dashboard
│
├── 📁 Models/                 # 25 Modelos con Active Record y relaciones
│   ├── 🏠 Negocio Principal:
│   │   ├── Cabania.php              # Cabañas con disponibilidad
│   │   ├── Reserva.php              # Reservas transaccionales
│   │   ├── Usuario.php              # Autenticación multi-perfil
│   │   ├── Persona.php              # Datos de huéspedes
│   │   └── ... (4 más)
│   ├── 🛍️ Comercial:
│   │   ├── Producto.php             # Inventario con stock
│   │   ├── Servicio.php             # Servicios con categorías
│   │   ├── Consumo.php              # Registro de ventas
│   │   └── ... (3 más)
│   └── ⚙️ Sistema:
│       ├── EstadoReserva.php        # Estados dinámicos sin hardcode
│       ├── Perfil.php               # Sistema de roles
│       └── ... (15 más)
│
├── 📁 Views/                  # Sistema organizado en 3 secciones
│   ├── 🌐 public/                   # Experiencia del huésped (7 módulos)
│   │   ├── home.php                    # Landing page optimizada
│   │   ├── 📁 auth/                    # Autenticación de usuarios
│   │   ├── 📁 catalogo/                # Exploración de cabañas
│   │   ├── 📁 reservas/                # 🔥 Sistema de 5 pasos:
│   │   │   ├── confirmar.php              # ✅ Validación de datos
│   │   │   ├── servicios.php              # 🛍️ Servicios adicionales
│   │   │   ├── resumen.php                # 📋 Vista previa completa
│   │   │   ├── pago.php                   # 💳 Simulación de pagos
│   │   │   └── exito.php                  # 🎉 Confirmación final
│   │   ├── 📁 comentarios/             # Sistema de feedback
│   │   └── ... (3 más)
│   ├── 🏢 admin/                    # Panel administrativo (24 módulos)
│   │   ├── 📁 configuracion/           # Configuración básica (10)
│   │   ├── 📁 operaciones/             # Gestión diaria (5)
│   │   ├── 📁 seguridad/               # Administración (5)
│   │   └── 📁 reportes/                # Analytics (4)
│   └── 📁 shared/                   # Componentes reutilizables
│       ├── 📁 layouts/                 # Plantillas base
│       ├── 📁 components/              # Elementos comunes
│       └── 📁 errors/                  # Páginas de error
│
├── 📁 Core/                   # Framework MVC personalizado (13 componentes)
│   ├── Application.php              # Bootstrap y ciclo de vida
│   ├── Router.php                   # Enrutamiento con URLs amigables
│   ├── Controller.php               # Clase base con funcionalidades
│   ├── Model.php                    # Active Record con CRUD
│   ├── View.php                     # Motor de renderizado seguro
│   ├── Database.php                 # Singleton con pool de conexiones
│   ├── Auth.php                     # Autenticación multi-perfil
│   ├── Validator.php                # Sistema de validaciones
│   ├── EmailService.php             # Servicio de emails con PHPMailer
│   └── ... (4 más)
│
├── 📁 assets/                 # Recursos frontend organizados
│   ├── 📁 css/                      # Estilos por módulo (7 archivos)
│   ├── 📁 js/                       # JavaScript funcional (7 archivos)
│   └── 📁 images/                   # Recursos del sistema
├── 📁 imagenes/               # Contenido de usuarios
│   ├── 📁 cabanias/                 # Fotos de las 8 cabañas
│   └── 📁 productos/                # Imágenes de productos
├── 📁 vendor/                 # Dependencias (PHPMailer via Composer)
├── 📄 bd.sql                  # Base de datos completa (24 tablas)
├── 📄 composer.json           # Gestión de dependencias
├── 📄 index.php               # Punto de entrada con manejo de errores
├── 📄 .htaccess               # Configuración Apache con seguridad
├── 📄 DER.png                 # Diagrama de entidad-relación
└── 📄 README.md               # Documentación completa
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

### **� Sistema de Reservas Online - Experiencia Completa**

El sistema de reservas es el **corazón del proyecto**, implementando un flujo transaccional completo de 5 pasos optimizado para la conversión:

#### **🎯 Flujo de Usuario (Huésped)**

**Pre-Reserva: Exploración**
1. **Catálogo Público** (`/catalogo`) - Sin autenticación requerida
   - Filtros avanzados: fechas, capacidad, precio
   - Galería de fotos con descripciones detalladas
   - Disponibilidad en tiempo real
   - Precios dinámicos por temporada

**Reserva: Proceso Guiado (Requiere login como huésped)**

**Paso 1: Confirmación de Datos** (`/reservas/confirmar`)
- ✅ Validación de cabaña seleccionada y fechas
- 👥 Configuración de huéspedes (adultos/niños)  
- 📝 Observaciones especiales opcionales
- 💰 Cálculo automático: noches × precio base
- 🔒 Validaciones: capacidad máxima, disponibilidad

**Paso 2: Servicios Adicionales** (`/reservas/servicios`)
- 🛍️ Catálogo de servicios por categorías (Spa, Tours, Restaurante)
- ➕ Selección múltiple con cantidades
- 💵 Actualización de precios en tiempo real
- ⏭️ Opción "Omitir" para continuar sin servicios
- 📊 Preview del total actualizado

**Paso 3: Resumen Completo** (`/reservas/resumen`)
- � Vista previa detallada de toda la reserva
- � Desglose financiero completo (alojamiento + servicios + impuestos)
- ℹ️ Información práctica (horarios, políticas, contacto)
- ☑️ Aceptación obligatoria de términos y condiciones
- � Botones "Modificar Reserva" y "Cancelar"

**Paso 4: Procesamiento de Pago** (`/reservas/pago`)
- 💳 **Tarjeta de Crédito**: Con validación real (rechazo simulado para testing)
- 🏦 **Transferencia Bancaria**: Datos completos de cuenta
- 💵 **Efectivo**: Pago diferido al momento del check-in
- 🔐 Validaciones por método específico
- ⚡ Procesamiento transaccional con rollback automático

**Paso 5: Confirmación Exitosa** (`/reservas/exito`)
- 🎉 Mensaje de éxito con animación
- 🎫 Número de reserva único generado
- 📧 Email de confirmación automático (PHPMailer)
- 📱 Información práctica para la estadía
- 💾 Opción de descargar/imprimir comprobante

#### **⚙️ Estados Dinámicos (Sin Hardcode)**
El sistema maneja **8 estados** de reserva completamente dinámicos:
- 🟡 **PENDIENTE** → Creada, esperando pago
- 🟢 **CONFIRMADA** → Pago procesado exitosamente  
- 🔵 **EN_CURSO** → Check-in realizado
- ⚫ **FINALIZADA** → Check-out completado
- 🔴 **ANULADA** → Cancelada por administrador
- ⏰ **EXPIRADA** → Vencimiento automático por tiempo
- 🟠 **CANCELADA** → Cancelada por huésped
- 🟣 **PENDIENTE_PAGO** → Esperando confirmación de pago

### **⚡ Sistema de Transacciones Atómicas**

El sistema implementa **dos transacciones críticas** para garantizar la integridad de datos en el proceso de reservas online:

#### **🏠 Transacción 1: Reserva Temporal + Servicios**
**Ubicación:** `Models/Reserva.php` → `createReservationWithServices()`

```php
$this->db->transaction(function() {
    // 1. Verificar disponibilidad de cabaña
    // 2. Crear reserva en estado PENDIENTE (20 min expiración)
    // 3. Crear relación huésped-reserva
    // 4. Crear servicios como consumos
    // 5. Rollback automático si hay errores
});
```

**Características:**
- ✅ **Una sola operación atómica** para reserva + servicios seleccionados
- ✅ **Estado inicial PENDIENTE** con expiración automática de 20 minutos
- ✅ **Verificación de disponibilidad** antes de crear la reserva
- ✅ **Rollback automático** si falla cualquier paso del proceso

#### **💳 Transacción 2: Confirmación de Pago Completa**
**Ubicación:** `Models/Reserva.php` → `confirmPayment()`

```php
$this->db->transaction(function() {
    // 1. Verificar reserva en estado PENDIENTE
    // 2. Registrar pago con método seleccionado
    // 3. Cambiar estado reserva a CONFIRMADA
    // 4. Cambiar estado cabaña a OCUPADA
    // 5. Generar factura completa con detalles
    // 6. Rollback automático si hay errores
});
```

**Características:**
- ✅ **Transacción completa** que procesa pago, factura y cambios de estado
- ✅ **Generación de factura** con número automático y detalles
- ✅ **Actualización de estados** de reserva y cabaña
- ✅ **Manejo robusto de errores** con logging detallado

#### **🛡️ Beneficios de la Implementación ACID**
- **Atomicidad:** Las operaciones se completan totalmente o no se ejecutan
- **Consistencia:** Estados siempre coherentes entre todas las tablas
- **Aislamiento:** Transacciones concurrentes no interfieren entre sí
- **Durabilidad:** Una vez confirmada, la transacción es permanente

### **🧾 Sistema de Facturación Automática**

#### **Numeración Correlativa por Tipo de Comprobante**
El sistema implementa un moderno sistema de numeración automática sin hardcode:

- **FACTURA A**: `FACA-00000001`, `FACA-00000002`, etc.
- **FACTURA B**: `FACB-00000001`, `FACB-00000002`, etc.  
- **FACTURA C**: `FACC-00000001`, `FACC-00000002`, etc.
- **TICKET USUARIO FINAL**: `TICK-00000001`, `TICK-00000002`, etc.

#### **Características del Sistema**
- ✅ **Numeración Automática**: Generación transparente sin intervención manual
- ✅ **Correlativa por Tipo**: Cada tipo de comprobante maneja su propia secuencia
- ✅ **Sin Duplicados**: Índice único que previene números duplicados
- ✅ **Transaccional**: Generación segura con rollback automático
- ✅ **Formato Estándar**: Prefijo de 4 caracteres + 8 dígitos correlativos
- ✅ **Base de Datos Simplificada**: Usa estructura existente sin tablas adicionales

#### **Implementación Técnica**
```php
// Generación automática en Models/Factura.php
$numero = $factura->generateNumeroFactura($tipoComprobante);
// Resultado: "FACA-00000001" (dependiendo del tipo)
```

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

### **� Sistema de Estados de Reserva**

#### **Estados Dinámicos Sin Hardcode**
El sistema maneja 8 estados de reserva de forma completamente dinámica:
- **PENDIENTE** → Reserva creada, esperando confirmación
- **CONFIRMADA** → Pago procesado exitosamente  
- **EN CURSO** → Check-in realizado, estadía activa
- **FINALIZADA** → Check-out completado
- **ANULADA** → Cancelada por administrador
- **EXPIRADA** → Reserva pendiente que venció automáticamente
- **CANCELADA** → Cancelada por el huésped
- **PENDIENTE DE PAGO** → Esperando confirmación de pago

#### **Herramientas de Gestión**
```bash
# Sistema de Estados
php scripts/estados_console.php validate    # Validar sistema de estados
php scripts/estados_console.php report      # Generar reporte completo  
php scripts/estados_console.php migrate     # Migrar estados faltantes
php scripts/estados_console.php check       # Verificar integridad completa

# Mantenimiento del Sistema  
php scripts/cleanup.php logs               # Limpiar logs antiguos
php scripts/cleanup.php cache              # Limpiar cache
php scripts/cleanup.php temp               # Limpiar archivos temporales
php scripts/cleanup.php all                # Limpieza completa
```

#### **Componentes del Sistema**
- **EstadoReserva (Modelo)**: Lógica centralizada sin hardcode integrada
- **Métodos Estáticos**: Acceso directo desde el modelo principal
- **Migración Inteligente**: Scripts seguros de actualización
- **Consola de Gestión**: Herramientas de diagnóstico

### **�🔧 Extensión del Framework**

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
- ✅ **Sistema de facturación automática** con numeración correlativa

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
- ✅ Sistema transaccional de reservas con expiración automática
- ✅ Gestión completa de huéspedes
- ✅ Inventario de productos y servicios
- ✅ Simulación de pasarela de pagos
- ✅ Sistema de reportes básico
- ✅ **Sistema de Estados Dinámico**: Gestión sin hardcode con 8 estados
- ✅ **Sistema de Facturación**: Numeración automática correlativa por tipo (FACA, FACB, FACC, TICK)

### **⏳ En Desarrollo Activo**

#### **Sistema de Reservas Online** 
- ✅ Sistema de estados dinámico sin hardcode implementado
- ✅ Expiración automática de reservas pendientes
- ✅ Cancelación por huéspedes y anulación por admin
- 🔄 Testing completo del flujo transaccional
- 🔄 Optimización de validaciones en tiempo real
- 🔄 Integración real con PHPMailer

#### **Panel Administrativo**
- 🔄 Migración completa de rutas de vistas
- 🔄 Implementación de paginación
- 🔄 Filtros avanzados en listados
- 🔄 Exportación de reportes (PDF/Excel)

#### **Arquitectura**
- **Framework**: MVC personalizado
- **Base de datos**: 25 tablas relacionales
- **Vistas**: 31+ elementos organizados
- **Controladores**: 27 controladores especializados
- **Modelos**: 25 modelos con relaciones

---

## 📚 **Documentación Adicional**

Para información detallada sobre cada componente, consultar:

- **[Controllers/README.md](Controllers/README.md)** - Documentación completa de controladores
- **[Core/README.md](Core/README.md)** - Framework y arquitectura interna
- **[Models/README.md](Models/README.md)** - Modelos de datos y relaciones  
- **[Views/README.md](Views/README.md)** - Sistema de vistas y flujos
- **[ESTADOS_RESERVA_README.md](ESTADOS_RESERVA_README.md)** - Sistema de estados sin hardcode

---

---

## 📞 **Información del Proyecto**

### **Detalles Académicos**
- **Proyecto:** SIRCA - Sistema Integral de Reservas de Cabañas y Alojamientos
- **Institución:** ISRMM - Instituto Superior de Desarrollo de Software
- **Cátedra:** Desarrollo de Software - Programación Orientada a Objetos
- **Integrantes:** Hernan Lopez, Greta Cabrera
- **Repositorio:** [gretacabrera/proyecto_cabania](https://github.com/gretacabrera/proyecto_cabania)

### **Estado Actual del Desarrollo**
- **Versión:** 2.1 (Octubre 2025)
- **Estado:** ✅ Completamente funcional y documentado
- **Cobertura:** 100% de funcionalidades implementadas
- **Testing:** Validado en entorno de desarrollo local

### **Tecnologías Implementadas**
- **Backend:** PHP 8.0+ con MVC personalizado
- **Frontend:** HTML5, CSS3, Bootstrap 5.3, JavaScript ES6+
- **Base de Datos:** MySQL 8.0 (24 tablas relacionales)
- **Dependencias:** PHPMailer, SweetAlert2, Font Awesome
- **Servidor Web:** Apache 2.4 con mod_rewrite
- **Control de Versiones:** Git con GitHub

### **Métricas del Proyecto**
- **Líneas de Código:** ~15,000 líneas (estimado)
- **Archivos PHP:** 65+ archivos organizados
- **Controladores:** 27 controladores activos
- **Modelos:** 25 modelos con relaciones
- **Vistas:** 39+ elementos organizados
- **Base de Datos:** 24 tablas con datos de ejemplo
- **Facturación:** Sistema automático con 4 tipos de comprobantes

### **🆕 Últimas Actualizaciones**

#### **Noviembre 2025 - Sistema de Transacciones Atómicas**
- ✅ **Transacciones ACID**: Implementadas dos transacciones críticas para reservas online
- ✅ **Reserva + Servicios**: Operación atómica que incluye reserva temporal y servicios seleccionados
- ✅ **Confirmación de Pago**: Transacción completa que procesa pago, genera factura y actualiza estados
- ✅ **Rollback Automático**: Manejo robusto de errores con reversión automática de cambios
- ✅ **Logging Detallado**: Sistema de logs para monitoreo y debugging de transacciones
- ✅ **Código Limpio**: Eliminación de métodos de validación innecesarios y números de transacción redundantes

#### **Noviembre 2025 - Sistema de Facturación Automática**
- ✅ **Numeración Correlativa**: Implementado sistema automático por tipo de comprobante
- ✅ **Base de Datos**: Migración exitosa con clave única para prevenir duplicados
- ✅ **Modelo Factura**: Mejorado con método `generateNumeroFactura()` transaccional
- ✅ **Formatos Estándar**: FACA-00000001, FACB-00000001, FACC-00000001, TICK-00000001
- ✅ **Arquitectura Simplificada**: Sin tablas adicionales, usa estructura existente
- ✅ **Documentación**: Guía completa de implementación incluida

---

*Proyecto desarrollado como parte del programa de Desarrollo de Software - ISRMM*  
*Casa de Palos Cabañas - Sistema Integral de Gestión de Turismo Rural*  
*Documentación actualizada: 1 de Noviembre de 2025*
```
