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

## 🔐 **Control de Acceso y Seguridad**

### **Niveles de Acceso por Controlador**

#### **Acceso Público** (Sin autenticación requerida)
- `HomeController` - Página principal
- `AuthController` - Login/registro

#### **Acceso de Huésped** (Autenticación de huésped)
- `CatalogoController` - Ver cabañas disponibles
- `ComentariosController` - Dejar comentarios
- `IngresosController` - Check-in
- `SalidasController` - Check-out

#### **Acceso Administrativo** (Autenticación administrativa)
- Todos los controladores de configuración, operaciones y administración
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

*Documentación actualizada el 25/09/2025 - Casa de Palos Cabañas*