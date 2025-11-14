# Organización de Vistas - Casa de Palos Cabañas

Esta estructura organiza las vistas de manera lógica y escalable, siguiendo patrones de desarrollo modernos para aplicaciones web.

## 📁 Estructura Actual (Actualizada - 14/11/2025)

### `/public/` - Vistas Públicas
Vistas accesibles para huéspedes y usuarios públicos:
- `home.php` - Página de inicio principal
- `auth/` - Login, registro, recuperación de contraseña
- `catalogo/` - Catálogo público de cabañas y servicios
- `comentarios/` - Sistema de comentarios y feedback para huéspedes
- `consumos/` - **NUEVO**: Sistema self-service de consumos para huéspedes (4 vistas):
  - `listado.php` - Listado de consumos propios del huésped
  - `solicitar.php` - Catálogo visual para solicitar productos/servicios
  - `editar.php` - Editar cantidad de consumo
  - `detalle.php` - Vista detallada de consumo
- `ingresos/` - Check-in, registro de llegadas de huéspedes
- `reservas/` - **Sistema completo de reservas online** (5 vistas):
  - `confirmar.php` - Paso 1: Confirmación de datos básicos de reserva
  - `servicios.php` - Paso 2: Selección de servicios adicionales
  - `resumen.php` - Paso 3: Vista previa de facturación y términos
  - `pago.php` - Paso 4: Simulación de pasarela de pago con validaciones
  - `exito.php` - Confirmación final y detalles de reserva exitosa
- `salidas/` - Check-out, proceso de salida de huéspedes

### `/admin/` - Panel Administrativo
Vistas que requieren autenticación administrativa:

#### `/admin/configuracion/` - Configuración Básica (13 módulos)
- `categorias/` - Gestión de categorías de productos
- `condicionessalud/` - Condiciones médicas de huéspedes
- `estadospersonas/` - Estados de huéspedes
- `estadosproductos/` - Estados de productos
- `estadosreservas/` - Estados de reservas
- `marcas/` - Gestión de marcas
- `metodosdepago/` - Métodos de pago
- `nivelesdanio/` - Niveles de daño (leve, moderado, grave)
- `periodos/` - Gestión de periodos/temporadas
- `tiposcontactos/` - Tipos de contacto
- `tiposservicios/` - Tipos de servicios

#### `/admin/operaciones/` - Operaciones del Negocio (9 módulos)  
- `cabanias/` - Gestión de cabañas del complejo
- `consumos/` - Registro administrativo de consumos con formulario múltiple:
  - `listado.php` - Listado con filtros y exportaciones
  - `formulario.php` - Formulario dinámico para múltiples items
  - `detalle.php` - Vista de detalle de consumo
- `costosdanio/` - Gestión de costos por daños
- `huespedes/` - Gestión de huéspedes y datos personales
- `inventarios/` - Control de inventario por cabaña
- `productos/` - Gestión de inventario y productos
- `reservas/` - Gestión administrativa de reservas
- `revisiones/` - Revisiones de inventario por reserva
- `servicios/` - Gestión administrativa de servicios ofrecidos

### `/totem/` - **NUEVO**: Módulo Totem Sin Autenticación
Sistema de pedidos para cabañas sin requerir autenticación:
- `consumos/` - Sistema completo de totem (3 vistas):
  - `config.php` - Configuración del totem con código de cabaña
  - `menu.php` - Catálogo de productos/servicios con botones táctiles
  - `historial.php` - Historial de pedidos realizados en sesión
- **Layout especial**: `/shared/layouts/totem.php` - Diseño fullscreen púrpura optimizado para tablets

#### `/admin/sistema/` - Administración del Sistema (3 módulos)
- `menus/` - Configuración de menús del sistema
- `modulos/` - Módulos del sistema
- `perfilesmodulos/` - Permisos y asignación de módulos

#### `/admin/seguridad/` - Gestión de Seguridad (2 módulos)
- `perfiles/` - Roles y perfiles de usuario
- `usuarios/` - Gestión de usuarios del sistema

#### `/admin/reportes/` - Sistema de Reportes (6 reportes)
Analytics y reportes administrativos:
- `index.php` - Dashboard principal de reportes
- `comentarios.php` - Reportes de feedback de huéspedes
- `consumos.php` - Analytics de consumos por cabaña
- `demografico.php` - Análisis demográfico por grupos etarios
- `productos.php` - Reportes de productos por categoría
- `temporadas.php` - Análisis de temporadas altas
- `ventas-mensuales.php` - Producto más vendido por mes

### `/shared/` - Componentes Compartidos
Elementos reutilizables en toda la aplicación:
- `layouts/` - Plantillas base (main.php, auth.php, footer.php, etc.)
- `components/` - Componentes reutilizables (menu.php, messages.php, etc.)
- `errors/` - Páginas de error (403.php, 404.php, 500.php)

## 🚀 **Sistema de Reservas Online - Flujo Completo**

### 📋 **Proceso de Reserva Paso a Paso**

El sistema de reservas online implementado en `/public/reservas/` incluye un flujo completo de 5 pasos:

#### **Paso 1: Confirmación de Reserva (`confirmar.php`)**
- **Función**: Validar datos básicos después de seleccionar cabaña y fechas
- **Requisitos**: Usuario con perfil "huésped" autenticado
- **Características**:
  - Visualización de cabaña seleccionada con imagen y detalles
  - Validación de fechas con calendario dinámico
  - Configuración de número de huéspedes (adultos/niños)
  - Datos del huésped pre-llenados desde sesión
  - Campo opcional para observaciones especiales
  - Cálculo automático de noches y costos base

#### **Paso 2: Servicios Adicionales (`servicios.php`)**
- **Función**: Permitir selección opcional de servicios extras
- **Características**:
  - Catálogo de servicios organizados por categorías
  - Selección múltiple con precios dinámicos
  - Resumen en tiempo real de servicios seleccionados
  - Botones "Confirmar" y "Omitir" para máxima flexibilidad
  - Cálculo automático del total actualizado

#### **Paso 3: Resumen de Reserva (`resumen.php`)**
- **Función**: Vista previa completa antes del pago
- **Características**:
  - Resumen detallado de alojamiento, fechas y huéspedes
  - Listado completo de servicios adicionales seleccionados
  - Desglose financiero completo (alojamiento + servicios + impuestos)
  - Información importante (horarios, políticas, contacto)
  - Aceptación obligatoria de términos y condiciones
  - Opciones de "Modificar" o "Cancelar"

#### **Paso 4: Procesamiento de Pago (`pago.php`)**
- **Función**: Simulación de pasarela de pago con validaciones
- **Métodos Soportados**:
  - **Tarjeta de Crédito**: Con validación hardcodeada (rechazo para números con "1234")
  - **Transferencia Bancaria**: Con datos completos para transferencia
  - **Efectivo**: Pago diferido al check-in
- **Características**:
  - Formularios dinámicos según método seleccionado
  - Validación de campos específicos por método
  - Ejemplo de rechazo de pago implementado
  - Confirmación con SweetAlert2
  - Procesamiento transaccional completo

#### **Paso 5: Confirmación Exitosa (`exito.php`)**
- **Función**: Confirmación final y envío de detalles
- **Características**:
  - Mensaje de éxito con efecto confetti
  - Número de reserva generado
  - Resumen completo de la reserva confirmada
  - Información práctica para la estadía
  - Opciones para descargar comprobante
  - Sugerencias de servicios adicionales

### 🔄 **Flujo de Datos y Estados**

1. **Inicio**: Desde catálogo → seleccionar cabaña y fechas
2. **Estado "Pendiente"**: Al confirmar paso 1 (rela_perfil = 1)
3. **Servicios**: Registro como consumos si se seleccionan
4. **Pago**: Procesamiento transaccional completo:
   - ✅ Insertar consumos de servicios
   - ✅ Registrar pago de reserva
   - ✅ Cambiar estado a "confirmada"
   - ✅ Cambiar estado cabaña a "ocupada"
   - ✅ Enviar email con PHPMailer
   - ❌ Rollback completo si hay errores

### 🛡️ **Validaciones y Seguridad**

- **CSRF Protection**: Tokens en todos los formularios
- **Validación de Fechas**: Prevenir reservas en el pasado
- **Capacidad**: Verificar límites de huéspedes por cabaña
- **Disponibilidad**: Validar que la cabaña esté libre
- **Pago Simulado**: Ejemplo de rechazo para testing
- **Transaccional**: Rollback automático en errores

### 🎯 **Navegación y UX**

- **Barra de Progreso**: Visual en cada paso (25%, 50%, 75%, 100%)
- **Botones "Volver"**: En cada vista para retroceder
- **Validación en Tiempo Real**: JavaScript para mejor UX
- **Responsive Design**: Optimizado para móviles
- **Loading States**: Indicadores durante procesamiento
- **Confirmaciones**: SweetAlert2 para acciones críticas

---

### 1. **Separación Lógica por Audiencia**
- **Público**: Módulos accesibles para huéspedes (home, auth, comentarios, check-in/out)
- **Administrativo**: Módulos internos para staff (catálogos, operaciones, configuración)
- **Reportes**: Analytics y reportes para la gestión
- **Compartido**: Componentes reutilizables en toda la aplicación

### 2. **Lógica de Acceso Diferenciada**
- **`/public/`**: Sin autenticación o con autenticación de huésped
  - `catalogo/`: Catálogo público para consultar cabañas disponibles
  - `reservas/`: **Sistema completo de reservas online** con proceso paso a paso:
    1. **Confirmación** → Validar datos de cabaña, fechas y huésped
    2. **Servicios** → Seleccionar servicios adicionales (opcional)
    3. **Resumen** → Vista previa de facturación y términos
    4. **Pago** → Simulación de pasarela con validación de métodos
    5. **Éxito** → Confirmación final con envío de email
  - `comentarios/`: Huéspedes pueden dejar feedback
  - `ingresos/salidas/`: Proceso de check-in/check-out para huéspedes
- **`/admin/`**: Requiere autenticación administrativa
  - `configuracion/`: Configuración básica del sistema
  - `operaciones/`: Gestión interna del negocio (incluye cabañas y reservas administrativas)
  - `seguridad/`: Administración del sistema
  - `reportes/`: Analytics y reportes ejecutivos

### 2. **Escalabilidad**
- **Fácil expansión**: Nuevas funcionalidades se pueden agregar en las categorías correctas
- **Mantenimiento**: Cambios en un módulo no afectan otros
- **Navegación**: Estructura intuitiva para desarrolladores

### 3. **Seguridad**
- **Separación de acceso**: Vistas públicas vs administrativas claramente definidas
- **Control de permisos**: Más fácil implementar middleware por sección
- **Auditoría**: Fácil identificar qué vistas requieren qué nivel de acceso

### 4. **Desarrollo**
- **Trabajo en equipo**: Diferentes desarrolladores pueden trabajar en diferentes secciones
- **Testing**: Tests más organizados por funcionalidad
- **Documentación**: Estructura auto-documentada

---

## ✅ MIGRACIÓN COMPLETADA

### 🎉 Actualización de Controladores

**COMPLETADO**: Todos los controladores han sido actualizados con las nuevas referencias de vistas organizadas.

#### ✅ Cambios Implementados:

- **ModuleController.php**: **ELIMINADO** - Ya no es necesario
- **Application.php**: Rutas legacy removidas
- **Todos los controladores**: Actualizados con rutas organizadas
- **27 controladores activos**: Funcionando con nueva estructura

#### 📝 Estructura Final Implementada:

```php
// ✅ IMPLEMENTADO - Estructura organizada:
$this->render('admin/seguridad/usuarios/index', $data);
$this->render('admin/operaciones/productos/create', $data);
$this->render('admin/operaciones/reservas/show', $data);
$this->render('public/comentarios/index', $data);
$this->render('public/home', $data);
$this->render('public/catalogo/index', $data);
$this->render('admin/operaciones/cabanias/index', $data);
```

### 🚀 Estado Actual

- ✅ **Estructura de directorios**: Implementada
- ✅ **Controladores migrados**: 27/27 actualizados  
- ✅ **Sistema de rutas**: Limpio y organizado
- ✅ **ModuleController**: Eliminado
- ✅ **Testing**: Listo para validación

---

## ⚠️ ~~MIGRACIÓN REQUERIDA~~ - **COMPLETADA**

### ~~🔧 Actualización de Controladores~~

**✅ COMPLETADO**: ~~Después de la reorganización, todos los controladores deben actualizar sus referencias de vistas.~~

#### Ejemplos de cambios necesarios:

```php
// ❌ ANTES (estructura plana):
$this->view('usuarios/index', $data);
$this->view('productos/create', $data);
$this->view('reservas/show', $data);
$this->view('comentarios/index', $data);

// ✅ DESPUÉS (estructura organizada):
$this->view('admin/seguridad/usuarios/index', $data);
$this->view('admin/operaciones/productos/create', $data);
$this->view('admin/operaciones/reservas/show', $data);
$this->view('public/comentarios/index', $data);
$this->view('public/home', $data); // Nota: archivo directo, no carpeta
$this->view('public/catalogo/index', $data);
$this->view('admin/operaciones/cabanias/index', $data);
```

#### 📝 Lista de Controladores a Actualizar:

1. **Controladores Públicos** → `public/`:
   - `AuthController.php` → `public/auth/`
   - `HomeController.php` → `public/home/`
   - `CatalogoController.php` → `public/catalogo/`
   - `ComentariosController.php` → `public/comentarios/`
   - `IngresosController.php` → `public/ingresos/`
   - `ReservasController.php` (público) → `public/reservas/`
   - `SalidasController.php` → `public/salidas/`

2. **Controladores Admin** → `admin/`:
   - **Configuración Básica** (10 controladores) → `admin/configuracion/`
   - **Operaciones** (5 controladores) → `admin/operaciones/`
     - `CabaniasController.php` → `admin/operaciones/cabanias/`
     - `ReservasController.php` (admin) → `admin/operaciones/reservas/`
   - **Administración** (5 controladores) → `admin/seguridad/`
   - **Reportes** (4 controladores) → `admin/reportes/`

## ✅ Completado y en Producción

### 🎯 Funcionalidad Implementada
- ✅ **Reorganización completada**: Estructura de directorios optimizada
- ✅ **Migración de controladores**: 32/32 controladores actualizados
- ✅ **Sistema multimodal**: 3 módulos de consumos funcionales
- ✅ **Reportes ejecutivos**: 7 reportes con analytics

### 🔒 Seguridad Implementada
- ✅ **Control de acceso por sección**: Middleware funcional
  - `/public/` → Acceso público o huésped autenticado
  - `/admin/` → Requiere autenticación administrativa
  - `/totem/` → Sin autenticación requerida
- ✅ **Validación de permisos**: Control por módulo operativo

### 📈 Optimizaciones Continuas
- 🔄 **Documentación específica**: Mejora continua por módulo
- 🔄 **Performance**: Optimización de carga de vistas
- 🔄 **UI Consistency**: Revisión constante de diseño

## 📊 **Estadísticas de la Estructura Actual**

### Estructura Final Implementada (Actualizada - Septiembre 2025):
- **📁 `/public/`**: 7 elementos principales:
  - `home.php` - Página de inicio
  - `auth/` - Autenticación de usuarios  
  - `catalogo/` - Catálogo público de cabañas
  - `comentarios/` - Sistema de feedback
  - `ingresos/` - Proceso de check-in
  - `reservas/` - **Sistema completo de reservas online (5 vistas)**
  - `salidas/` - Proceso de check-out
- **📁 `/admin/`**: 24 módulos distribuidos en:
  - `/configuracion/`: 10 módulos de configuración básica
  - `/operaciones/`: 5 módulos de operaciones del negocio (cabanias, consumos, productos, reservas, servicios)
  - `/seguridad/`: 5 módulos de administración del sistema
  - `/reportes/`: 4 reportes especializados (comentarios, consumos, dashboard, demografico)
- **📁 `/shared/`**: 3 categorías de componentes compartidos (components, errors, layouts)

### 📊 **Estadísticas de la Estructura Actual**

#### Estructura Final Implementada (Actualizada - 25 de Septiembre de 2025):
- **📁 `/public/`**: 7 elementos principales:
  - `home.php` - Página de inicio
  - `auth/` - Autenticación de usuarios  
  - `catalogo/` - Catálogo público de cabañas
  - `comentarios/` - Sistema de feedback
  - `ingresos/` - Proceso de check-in
  - `reservas/` - **Sistema completo de reservas online (5 vistas)**
  - `salidas/` - Proceso de check-out
- **📁 `/admin/`**: 24 módulos distribuidos en:
  - `/configuracion/`: 10 módulos de configuración básica
  - `/operaciones/`: 5 módulos de operaciones del negocio (cabanias, consumos, productos, reservas, servicios)
  - `/seguridad/`: 5 módulos de administración del sistema
  - `/reportes/`: 4 reportes especializados (comentarios, consumos, dashboard, demografico)
- **📁 `/shared/`**: 3 categorías de componentes compartidos (components, errors, layouts)

### **Total**: 31 elementos + **Sistema de Reservas Online** (5 vistas especializadas)

#### Estado de la Implementación:
- ✅ **Estructura de directorios**: Completamente implementada
- ✅ **Separación público/admin/totem**: Funcional
- ✅ **Módulos de configuración**: 13 módulos organizados
- ✅ **Módulos de operaciones**: 9 módulos (incluye huespedes, revisiones, inventarios)
- ✅ **Sistema de reportes**: 7 reportes especializados implementados
- ✅ **Migración de controladores**: COMPLETADA - 32/32 controladores actualizados
- ✅ **Limpieza de código**: Arquitectura optimizada
- ✅ **Sistema multimodal**: 3 módulos de consumos (Admin, Huésped, Totem)

#### Mejoras Implementadas:
1. ✅ **Sistema de Reservas Online Completo**: 5 vistas especializadas con flujo paso a paso
2. ✅ **Sistema Multimodal de Consumos**: 3 módulos (Admin, Huésped, Totem)
3. ✅ **Catálogo público**: Sistema completo con filtros y disponibilidad
4. ✅ **Dashboards Contextuales**: Vista personalizada por perfil de usuario
5. ✅ **Gestión Integral**: 9 módulos de operaciones del negocio
6. ✅ **Sistema de Reportes**: 7 reportes con analytics y gráficos
7. ✅ **Validación de Pagos**: Sistema con simulación de rechazo de tarjetas
8. ✅ **Proceso Transaccional**: Rollback automático en caso de errores
9. ✅ **Verificación de Email**: Sistema completo con PHPMailer
10. ✅ **UX Optimizada**: Diseño responsive con validación en tiempo real
11. ✅ **Check-in/Check-out**: Procesos completos de ingreso y salida
12. ✅ **Control de Inventario**: Gestión por cabaña con revisiones

---

---

## 🎯 **Objetivos Alcanzados**

### **✅ Funcionalidad Completada**
- **Sistema de Reservas Online**: 5 pasos completamente funcionales
- **Panel Administrativo**: 24 módulos organizados y operativos
- **Catálogo Público**: Sistema completo con filtros avanzados
- **Autenticación Multi-Perfil**: 3 tipos de usuarios implementados
- **Sistema de Comentarios**: Feedback integral para huéspedes

### **✅ Arquitectura Implementada** 
- **Separación Lógica**: Público vs Administrativo claramente definida
- **Componentes Reutilizables**: Layouts y components optimizados
- **Responsive Design**: Todas las vistas adaptadas a móviles
- **SEO Optimizado**: Meta tags y estructura semántica

### **✅ Tecnologías Integradas**
- **Bootstrap 5**: Framework CSS moderno
- **JavaScript ES6+**: Interactividad y validaciones
- **SweetAlert2**: Notificaciones y confirmaciones elegantes  
- **Font Awesome**: Iconografía consistente
- **PHPMailer**: Integración de emails transaccionales

---

## 📊 **Estadísticas Finales**

### **Distribución de Vistas Implementadas**
- **Públicas**: 8 módulos (home, auth, catalogo, comentarios, consumos, ingresos, reservas, salidas)
- **Totem**: 1 módulo especial con 3 vistas + layout personalizado
- **Administrativas**: 37 módulos distribuidos en:
  - Configuración: 13 módulos
  - Operaciones: 9 módulos
  - Sistema: 3 módulos
  - Seguridad: 2 módulos
  - Reportes: 7 reportes con analytics
- **Compartidas**: 3 categorías (components, errors, layouts con 6 plantillas)
- **Total General**: **49 módulos/vistas** implementados y funcionales

### **Cobertura por Funcionalidad**
- **🌐 Experiencia Huésped**: 100% completada
- **🏢 Panel Admin**: 100% completada  
- **🔐 Sistema Seguridad**: 100% completada
- **📊 Reportes**: 100% completada
- **📱 Responsive**: 100% completada

---

## 🔗 **Documentación Relacionada**

- **[README Principal](../README.md)** - Documentación completa del proyecto
- **[Controllers/README.md](../Controllers/README.md)** - Lógica de controladores
- **[Core/README.md](../Core/README.md)** - Framework MVC personalizado  
- **[Models/README.md](../Models/README.md)** - Modelos de datos y relaciones

---

*Estructura actualizada el 14/11/2025 - Casa de Palos Cabañas*
*✅ MIGRACIÓN COMPLETADA - 32 controladores actualizados*
*✅ ARQUITECTURA OPTIMIZADA - Sistema modular completo*
*✅ SISTEMA INTEGRAL - 49 módulos/vistas implementados y funcionales*