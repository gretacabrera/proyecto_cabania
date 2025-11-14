# Controllers - Sistema de Gestión de Cabañas

Esta carpeta contiene todos los controladores de la aplicación, organizados siguiendo el patrón MVC y las mejores prácticas de desarrollo.

## 📁 Arquitectura de Controladores

### 🏗️ **Estructura y Organización**

Los controladores están organizados por funcionalidad y siguen una nomenclatura consistente:
- **Nombre**: PascalCase terminado en "Controller"
- **Namespace**: `App\Controllers`
- **Herencia**: Extienden de `App\Core\Controller`

### 📋 **Inventario Completo de Controladores (32 controladores activos)**

✅ **MIGRACIÓN COMPLETADA**: Todos los controladores han sido actualizados para usar las nuevas rutas de vistas organizadas.

#### **🏠 Controladores Públicos (8 controladores)**
Controladores accesibles para usuarios públicos y huéspedes:

- **`HomeController.php`** - Página principal del sitio con dashboards contextuales
- **`AuthController.php`** - Autenticación (login, logout, registro, recuperación de contraseña)
- **`EmailVerificationController.php`** - Verificación de correo electrónico
- **`CatalogoController.php`** - Catálogo público de cabañas con disponibilidad
- **`ComentariosController.php`** - Sistema de comentarios y feedback
- **`IngresosController.php`** - Proceso de check-in para huéspedes
- **`SalidasController.php`** - Proceso de check-out para huéspedes
- **`HuespedConsumosController.php`** - Módulo self-service de consumos para huéspedes autenticados
- **`TotemConsumosController.php`** - Módulo totem de pedidos sin autenticación

#### **🏢 Controladores Administrativos**

##### **Configuración Básica (13 controladores)**
Controladores para la configuración fundamental del sistema:

- **`CategoriasController.php`** - Gestión de categorías de productos
- **`CondicionesSaludController.php`** - Condiciones médicas de huéspedes
- **`CostosDanioController.php`** - Costos asociados a daños en cabañas
- **`EstadosPersonasController.php`** - Estados de huéspedes (activo, inactivo, etc.)
- **`EstadosProductosController.php`** - Estados de productos
- **`EstadosReservasController.php`** - Estados de reservas (pendiente, confirmada, etc.)
- **`InventarioController.php`** - Gestión de inventario por cabaña
- **`MarcasController.php`** - Gestión de marcas de productos
- **`MetodosPagosController.php`** - Métodos de pago disponibles
- **`NivelDanioController.php`** - Niveles de daño (leve, moderado, grave)
- **`PeriodosController.php`** - Gestión de períodos/temporadas
- **`TiposContactosController.php`** - Tipos de contacto
- **`TiposServiciosController.php`** - Tipos de servicios ofrecidos

##### **Operaciones del Negocio (7 controladores)**
Controladores para la gestión operativa diaria:

- **`CabaniasController.php`** - Gestión completa de cabañas
- **`ConsumosController.php`** - Registro administrativo de consumos de huéspedes
- **`HuespedesController.php`** - Gestión de huéspedes y datos personales
- **`ProductosController.php`** - Gestión de inventario y productos
- **`ReservasController.php`** - Gestión integral de reservas online y administrativas
- **`RevisionesController.php`** - Revisiones de inventario por reserva
- **`ServiciosController.php`** - Gestión de servicios ofrecidos

##### **Administración del Sistema (5 controladores)**
Controladores para la configuración avanzada del sistema:

- **`MenusController.php`** - Configuración de menús del sistema
- **`ModulosController.php`** - Módulos del sistema
- **`PerfilesController.php`** - Roles y perfiles de usuario
- **`PerfilesModulosController.php`** - Asignación de permisos por perfil
- **`UsuariosController.php`** - Gestión de usuarios del sistema

##### **Sistema de Reportes (1 controlador)**
- **`ReportesController.php`** - Generación de reportes y analytics ejecutivos

---

## 🎯 **Patrones y Convenciones**

### **Estructura Básica de un Controlador**

```php
<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\[ModelName];

/**
 * Descripción del controlador
 */
class ExampleController extends Controller
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new [ModelName]();
    }

    /**
     * Método index - Listado principal
     */
    public function index()
    {
        // Lógica del controlador
        $data = [
            'title' => 'Título de la página',
            'records' => $this->model->getAll()
        ];

        return $this->render('ruta/vista', $data);
    }

    /**
     * Método create - Mostrar formulario de creación
     */
    public function create()
    {
        // Implementación
    }

    /**
     * Método store - Procesar creación
     */
    public function store()
    {
        // Implementación
    }

    /**
     * Método edit - Mostrar formulario de edición
     */
    public function edit($id)
    {
        // Implementación
    }

    /**
     * Método update - Procesar actualización
     */
    public function update($id)
    {
        // Implementación
    }

    /**
     * Método delete - Eliminar registro
     */
    public function delete($id)
    {
        // Implementación
    }
}
```

### **Métodos Estándar CRUD**

Cada controlador implementa métodos estándar para operaciones CRUD:

- **`index()`** - Listar todos los registros
- **`create()`** - Mostrar formulario de creación
- **`store()`** - Procesar y guardar nuevo registro
- **`show($id)`** - Mostrar un registro específico
- **`edit($id)`** - Mostrar formulario de edición
- **`update($id)`** - Procesar actualización de registro
- **`delete($id)`** - Eliminar registro

### **Métodos Heredados de Controller Base**

Todos los controladores heredan funcionalidades de `App\Core\Controller`:

```php
// Renderizado de vistas
protected function render($template, $data = [], $layout = null)

// Respuestas JSON
protected function json($data, $status = 200)

// Redirecciones
protected function redirect($url, $status = 302)

// Validación de permisos
protected function checkPermission($module, $action = 'read')

// Manejo de errores
protected function error($message, $code = 400)
```

---

## 🛒 **Sistema de Consumos Multimodal - 3 Controladores**

### **1. ConsumosController.php (Módulo Admin)**
**Ubicación**: `Controllers/ConsumosController.php`  
**Acceso**: Administrativo (requiere autenticación)

**Métodos Implementados:**
- `index()` - Listado con filtros y paginación
- `create()` - Formulario de creación múltiple
- `store()` - Guardar múltiples consumos transaccionalmente
- `show($id)` - Detalle de consumo
- `edit($id)` - Formulario de edición
- `update($id)` - Actualizar consumo
- `delete($id)` - Eliminar consumo
- `exportar()` - Exportar a Excel
- `exportarPdf()` - Exportar a PDF

**Características:**
- ✅ Formulario dinámico con JavaScript para múltiples items
- ✅ Cálculo automático de subtotales y total
- ✅ Soporte transaccional con método `createMultiple()`
- ✅ Validación completa de datos
- ✅ Exportación con filtros aplicados

### **2. HuespedConsumosController.php (Módulo Self-Service)**
**Ubicación**: `Controllers/HuespedConsumosController.php`  
**Acceso**: Huésped autenticado

**Métodos Implementados:**
- `index()` - Listado de consumos propios del huésped
- `solicitar()` - Catálogo visual para solicitar productos/servicios (GET/POST)
- `edit($id)` - Editar cantidad de consumo propio
- `update($id)` - Actualizar consumo
- `delete($id)` - Eliminar consumo propio (AJAX)
- `show($id)` - Detalle de consumo

**Características:**
- ✅ Seguridad: Solo puede ver/editar consumos propios
- ✅ Validación de propiedad mediante cadena usuario→persona→huesped→reserva
- ✅ Catálogo visual con imágenes de productos/servicios
- ✅ Interfaz optimizada para experiencia de usuario
- ✅ Operaciones AJAX para mejor UX

### **3. TotemConsumosController.php (Módulo Sin Autenticación)**
**Ubicación**: `Controllers/TotemConsumosController.php`  
**Acceso**: Público (sin autenticación requerida)

**Métodos Implementados:**
- `index()` - Página inicial del totem
- `configurar()` - Configuración del totem con código de cabaña (GET/POST)
- `menu()` - Catálogo de productos/servicios disponibles
- `pedido()` - Procesar pedido AJAX
- `historial()` - Historial de pedidos en sesión
- `reset()` - Limpiar configuración y volver al inicio
- `getPrecioProducto()` - API para obtener precio de producto (AJAX)

**Características:**
- ✅ Sistema basado en sesión PHP (sin BD de configuración)
- ✅ Validación de cabaña mediante código único
- ✅ Validación de reserva activa para la cabaña
- ✅ Diseño fullscreen optimizado para tablets
- ✅ Layout púrpura distintivo con gradiente
- ✅ Operaciones AJAX sin recarga de página
- ✅ Almacenamiento temporal de pedidos en sesión

## 🔐 **Control de Acceso y Seguridad**

### **Niveles de Acceso por Controlador**

#### **Acceso Público** (Sin autenticación requerida)
- `HomeController` - Página principal con dashboards contextuales
- `AuthController` - Login, registro y recuperación de contraseña
- `EmailVerificationController` - Verificación de email
- `TotemConsumosController` - Totem de pedidos sin autenticación

#### **Acceso de Huésped** (Autenticación de huésped)
- `CatalogoController` - Ver cabañas disponibles y reservar
- `ComentariosController` - Dejar comentarios y feedback
- `IngresosController` - Proceso de check-in
- `SalidasController` - Proceso de check-out
- `HuespedConsumosController` - Self-service de consumos
- `ReservasController` - Gestión de reservas propias (modo online)

#### **Acceso Administrativo** (Autenticación administrativa)
- **Configuración Básica**: Todos los controladores de estados, tipos, marcas, categorías, etc.
- **Operaciones**: `CabaniasController`, `HuespedesController`, `ProductosController`, `ServiciosController`
- **Gestión de Reservas**: `ReservasController` (modo admin), `RevisionesController`, `IngresosController`, `SalidasController`
- **Consumos**: `ConsumosController` - Gestión administrativa de consumos
- **Sistema**: `MenusController`, `ModulosController`, `PerfilesController`, `UsuariosController`
- **Reportes**: `ReportesController` - Reportes ejecutivos y analytics

### **Validación de Permisos**

```php
// Ejemplo de validación en controlador
public function __construct()
{
    parent::__construct();
    
    // Verificar autenticación
    if (!Auth::check()) {
        return $this->redirect('/auth/login');
    }
    
    // Verificar permisos específicos
    if (!$this->checkPermission('cabanias', 'read')) {
        return $this->error('Acceso denegado', 403);
    }
}
```

---

## 🌐 **Integración con el Sistema de Vistas**

### **Mapeo Controlador → Vista**

Los controladores están diseñados para integrarse con la nueva estructura de vistas:

```php
// Controladores públicos
HomeController           → Views/public/home.php
AuthController           → Views/public/auth/*
EmailVerificationController → Views/public/auth/verification/*
CatalogoController       → Views/public/catalogo/*
ReservasController       → Views/public/reservas/* (flujo online completo)
ComentariosController    → Views/public/comentarios/*

// Controladores de huésped
HuespedConsumosController → Views/public/consumos/*
IngresosController       → Views/public/ingresos/*
SalidasController        → Views/public/salidas/*

// Totem (sin autenticación)
TotemConsumosController  → Views/totem/consumos/* (layout: totem)

// Controladores administrativos
CabaniasController       → Views/admin/operaciones/cabanias/*
HuespedesController      → Views/admin/operaciones/huespedes/*
ProductosController      → Views/admin/operaciones/productos/*
ServiciosController      → Views/admin/operaciones/servicios/*
ReservasController       → Views/admin/operaciones/reservas/* (modo admin)
RevisionesController     → Views/admin/operaciones/revisiones/*
ConsumosController       → Views/admin/operaciones/consumos/*
InventarioController     → Views/admin/operaciones/inventarios/*
CostosDanioController    → Views/admin/operaciones/costosdanio/*

// Sistema y seguridad
UsuariosController       → Views/admin/seguridad/usuarios/*
PerfilesController       → Views/admin/seguridad/perfiles/*
MenusController          → Views/admin/sistema/menus/*
ModulosController        → Views/admin/sistema/modulos/*
PerfilesModulosController → Views/admin/sistema/perfilesmodulos/*

// Configuración
CategoriasController     → Views/admin/configuracion/categorias/*
MarcasController         → Views/admin/configuracion/marcas/*
EstadosReservasController → Views/admin/configuracion/estadosreservas/*
// ... (otros controladores de configuración)

// Reportes
ReportesController       → Views/admin/reportes/*
```

### **Rutas Recomendadas**

```php
// Rutas públicas
GET  /                         → HomeController@index
GET  /about                    → HomeController@about
POST /contact                  → HomeController@contact
GET  /catalogo                 → CatalogoController@index
POST /catalogo/reserve         → CatalogoController@reserve

// Autenticación
GET  /auth/login               → AuthController@login
POST /auth/login               → AuthController@login
GET  /auth/register            → AuthController@register
POST /auth/register            → AuthController@register
GET  /auth/logout              → AuthController@logout
GET  /auth/verify              → EmailVerificationController@verify

// Reservas online (público/huésped)
GET  /reservas/online          → ReservasController@online
POST /reservas/servicios       → ReservasController@servicios
GET  /reservas/resumen         → ReservasController@resumen
POST /reservas/proceder-pago   → ReservasController@procederPago
GET  /reservas/exito/{id}      → ReservasController@exito

// Consumos de huésped
GET  /huesped/consumos         → HuespedConsumosController@index
GET  /huesped/consumos/solicitar → HuespedConsumosController@solicitar

// Totem (sin autenticación)
GET  /totem                    → TotemConsumosController@index
POST /totem/configurar         → TotemConsumosController@configurar
GET  /totem/menu               → TotemConsumosController@menu
POST /totem/pedido             → TotemConsumosController@pedido

// Rutas administrativas (requieren autenticación y permisos)
GET  /cabanias                 → CabaniasController@index
GET  /cabanias/create          → CabaniasController@create
GET  /cabanias/{id}            → CabaniasController@show
GET  /cabanias/{id}/edit       → CabaniasController@edit
GET  /cabanias/exportar        → CabaniasController@exportar

GET  /huespedes                → HuespedesController@index
GET  /productos                → ProductosController@index
GET  /servicios                → ServiciosController@index
GET  /reservas                 → ReservasController@index (modo admin)
GET  /consumos                 → ConsumosController@index
GET  /revisiones               → RevisionesController@index

GET  /usuarios                 → UsuariosController@index
GET  /perfiles                 → PerfilesController@index
GET  /modulos                  → ModulosController@index
GET  /reportes                 → ReportesController@index
```

---

## 📊 **Estado de Implementación**

### ✅ **Completado**
- ✅ Estructura base de todos los controladores (32 controladores activos)
- ✅ Integración completa con el sistema de autenticación
- ✅ Patrones MVC implementados consistentemente
- ✅ Controladores para todas las entidades del sistema
- ✅ Sistema de reservas online completo con flujo de pago
- ✅ Sistema multimodal de consumos (Admin, Huésped, Totem)
- ✅ Sistema de reportes con 6 reportes ejecutivos
- ✅ Gestión de perfiles y permisos
- ✅ Control de acceso por roles (Público, Huésped, Admin)
- ✅ Exportación a Excel y PDF en módulos principales

### 🎯 **En Producción**
- Sistema de verificación de email
- Dashboard contextual por perfil de usuario
- Proceso completo de check-in/check-out
- Gestión de inventario y revisiones
- Sistema de comentarios con moderación

### 🔄 **Optimizaciones Continuas**
- Optimización de consultas en listados grandes
- Implementación de caché para reportes
- Mejoras en validaciones de negocio
- Refactorización de código duplicado
- Tests unitarios para controladores críticos

---

---

## 🔗 **Enlaces Relacionados**

- **[README Principal](../README.md)** - Documentación completa del proyecto
- **[Core/README.md](../Core/README.md)** - Framework MVC personalizado  
- **[Models/README.md](../Models/README.md)** - Modelos y lógica de datos
- **[Views/README.md](../Views/README.md)** - Sistema de vistas organizadas

---

*Documentación actualizada el 14/11/2025 - Casa de Palos Cabañas*
*Sistema de Gestión Integral de Cabañas - SIRCA*