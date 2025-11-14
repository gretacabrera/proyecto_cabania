# Controllers - Sistema de Gestión de Cabañas

Esta carpeta contiene todos los controladores de la aplicación, organizados siguiendo el patrón MVC y las mejores prácticas de desarrollo.

## 📁 Arquitectura de Controladores

### 🏗️ **Estructura y Organización**

Los controladores están organizados por funcionalidad y siguen una nomenclatura consistente:
- **Nombre**: PascalCase terminado en "Controller"
- **Namespace**: `App\Controllers`
- **Herencia**: Extienden de `App\Core\Controller`

### 📋 **Inventario Completo de Controladores (27 controladores activos)**

✅ **MIGRACIÓN COMPLETADA**: Todos los controladores han sido actualizados para usar las nuevas rutas de vistas organizadas.

#### **🏠 Controladores Públicos**
Controladores accesibles para usuarios públicos y huéspedes:

- **`HomeController.php`** - Página principal del sitio
- **`AuthController.php`** - Autenticación (login, logout, registro)
- **`CatalogoController.php`** - Catálogo público de cabañas
- **`ComentariosController.php`** - Sistema de comentarios y feedback
- **`IngresosController.php`** - Proceso de check-in para huéspedes
- **`SalidasController.php`** - Proceso de check-out para huéspedes
- **`HuespedConsumosController.php`** - **NUEVO**: Módulo self-service de consumos para huéspedes autenticados
- **`TotemConsumosController.php`** - **NUEVO**: Módulo totem de pedidos sin autenticación

#### **🏢 Controladores Administrativos**

##### **Configuración Básica** (10 controladores)
Controladores para la configuración fundamental del sistema:

- **`CategoriasController.php`** - Gestión de categorías de productos
- **`CondicionesSaludController.php`** - Condiciones médicas de huéspedes
- **`EstadosPersonasController.php`** - Estados de huéspedes (activo, inactivo, etc.)
- **`EstadosProductosController.php`** - Estados de productos
- **`EstadosReservasController.php`** - Estados de reservas (pendiente, confirmada, etc.)
- **`MarcasController.php`** - Gestión de marcas de productos
- **`MetodosPagosController.php`** - Métodos de pago disponibles
- **`PeriodosController.php`** - Gestión de períodos/temporadas
- **`TiposContactosController.php`** - Tipos de contacto
- **`TiposServiciosController.php`** - Tipos de servicios ofrecidos

##### **Operaciones del Negocio** (5 controladores)
Controladores para la gestión operativa diaria:

- **`CabaniasController.php`** - Gestión completa de cabañas
- **`ConsumosController.php`** - Registro de consumos de huéspedes
- **`ProductosController.php`** - Gestión de inventario y productos
- **`ReservasController.php`** - Gestión integral de reservas
- **`ServiciosController.php`** - Gestión de servicios ofrecidos

##### **Administración del Sistema** (5 controladores)
Controladores para la configuración avanzada del sistema:

- **`MenusController.php`** - Configuración de menús del sistema
- **`ModulosController.php`** - Módulos del sistema
- **`PerfilesController.php`** - Roles y perfiles de usuario
- **`PerfilesModulosController.php`** - Asignación de permisos por perfil
- **`UsuariosController.php`** - Gestión de usuarios del sistema

##### **Sistema de Reportes** (1 controlador)
- **`ReportesController.php`** - Generación de reportes y analytics

#### **🔧 Controladores de Sistema**
- ~~ModuleController.php~~ - **ELIMINADO** - Era un controlador legacy que ya no se necesita

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
- `HomeController` - Página principal
- `AuthController` - Login/registro
- `TotemConsumosController` - **NUEVO**: Totem de pedidos sin autenticación

#### **Acceso de Huésped** (Autenticación de huésped)
- `CatalogoController` - Ver cabañas disponibles
- `ComentariosController` - Dejar comentarios
- `IngresosController` - Check-in
- `SalidasController` - Check-out
- `HuespedConsumosController` - **NUEVO**: Self-service de consumos

#### **Acceso Administrativo** (Autenticación administrativa)
- Todos los controladores de configuración, operaciones y administración
- `ConsumosController` - Gestión administrativa de consumos
- `ReportesController` - Reportes ejecutivos

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
HomeController        → Views/public/home.php
AuthController        → Views/public/auth/*
CatalogoController    → Views/public/catalogo/*
ReservasController    → Views/public/reservas/* (nuevo sistema completo)

// Controladores administrativos
CabaniasController    → Views/admin/operaciones/cabanias/*
UsuariosController    → Views/admin/seguridad/usuarios/*
ReportesController    → Views/admin/reportes/*
```

### **Rutas Recomendadas**

```php
// Rutas públicas
GET  /                    → HomeController@index
GET  /catalogo           → CatalogoController@index
POST /reservas/confirmar → ReservasController@confirmar

// Rutas administrativas
GET  /admin/cabanias     → CabaniasController@index
GET  /admin/usuarios     → UsuariosController@index
GET  /admin/reportes     → ReportesController@index
```

---

## 📊 **Estado de Implementación**

### ✅ **Completado**
- Estructura base de todos los controladores
- Integración con el sistema de autenticación
- Patrones MVC implementados
- Controladores para todas las entidades del sistema

### ⏳ **Pendiente de Actualización**
- Migración de rutas de vistas a nueva estructura
- Implementación completa del sistema de reservas online
- Optimización de consultas y cachés
- Tests unitarios para controladores

### 🚨 **Próximas Tareas Críticas**
1. **Actualizar rutas de vistas** en controladores para nueva estructura
2. **Implementar ReservasController** completo para sistema online
3. **Validar permisos** por módulo en cada controlador
4. **Optimizar consultas** y implementar paginación

---

---

## 🔗 **Enlaces Relacionados**

- **[README Principal](../README.md)** - Documentación completa del proyecto
- **[Core/README.md](../Core/README.md)** - Framework MVC personalizado  
- **[Models/README.md](../Models/README.md)** - Modelos y lógica de datos
- **[Views/README.md](../Views/README.md)** - Sistema de vistas organizadas

---

*Documentación actualizada el 12/10/2025 - Casa de Palos Cabañas*
*Sistema de Gestión Integral de Cabañas - SIRCA*