# Guía de Generación de CRUDs - Proyecto Cabañas

## Introducción

Este documento define los patrones y criterios estándar para generar CRUDs completos en el proyecto de sistema de gestión de cabañas. Basado en el análisis del módulo de Cabañas, se establecen las convenciones de arquitectura, estructura de archivos, funcionalidades y patrones de código que deben seguirse.

## Estructura de Archivos por Entidad

Para cada entidad XXXX, se deben generar los siguientes archivos siguiendo la estructura del proyecto:

### 1. Controlador
**Ubicación:** `Controllers/XXXXController.php`

**Métodos obligatorios:**
- `index()` - Listado con filtros y paginación
- `create()` - Mostrar formulario de creación (GET) / Procesar creación (POST)
- `store()` - Guardar nueva entidad
- `show($id)` - Mostrar detalle de entidad específica
- `edit($id)` - Mostrar formulario de edición (GET) / Procesar actualización (POST)
- `update($id)` - Actualizar entidad existente
- `delete($id)` - Baja lógica (cambiar estado)
- `restore($id)` - Restaurar entidad eliminada
- `exportar()` - Exportar a Excel (.xlsx)
- `exportarPdf()` - Exportar a PDF

**Métodos opcionales según entidad:**
- `cambiarEstado($id)` - Cambio de estado mediante AJAX
- Métodos específicos de la entidad (ej: `checkAvailability()` para cabañas)

### 2. Modelo
**Ubicación:** `Models/XXXX.php`

**Propiedades obligatorias:**
- `protected $table = 'nombre_tabla';`
- `protected $primaryKey = 'id_nombre_tabla';`

**Métodos obligatorios:**
- `getWithDetails($page, $perPage, $filters)` - Listado paginado con filtros
- `getAllWithDetailsForExport($filters)` - Datos para exportación sin paginación

**Métodos opcionales según entidad:**
- Métodos de búsqueda específicos
- Validaciones personalizadas
- Relaciones con otras entidades

### 3. Vistas
**Ubicación:** `Views/admin/operaciones/xxxx/`

**Archivos obligatorios:**
- `listado.php` - Tabla con filtros, paginación y exportación
- `formulario.php` - Formulario para crear/editar (reutilizable)
- `detalle.php` - Vista de información completa

## Componentes de Funcionalidad Estándar

### 1. Listado (listado.php)

#### Características principales:
- **Header con título y botón "Nuevo"**
- **Filtros de búsqueda compactos** en tarjeta colapsable
- **Selector de registros por página** (5, 10, 25, 50)
- **Tabla responsiva** con datos formateados
- **Badges de estado** con colores semánticos
- **Botones de acción** (Ver, Editar, Cambiar Estado)
- **Paginación** con información de registros
- **Exportación** (Excel y PDF)
- **Estado vacío** cuando no hay registros

#### Filtros estándar:
```php
$filters = [
    'campo_nombre' => $this->get('campo_nombre'),
    'campo_codigo' => $this->get('campo_codigo'),
    'campo_estado' => $this->get('campo_estado'),
    // Campos específicos de la entidad
];
```

#### Paginación:
- Registros por página: 5, 10, 25, 50 (por defecto 10)
- Información: "Mostrando X a Y de Z entradas"
- Navegación: Anterior/Siguiente + números de página

#### Exportación:
- **Excel**: Formato .xlsx con estilos, todas las columnas, filtros aplicados
- **PDF**: Formato A4 vertical, tabla optimizada, información de filtros

### 2. Formulario (formulario.php)

#### Características principales:
- **Formulario reutilizable** para crear/editar
- **Validación HTML5** y JavaScript
- **Campos requeridos** marcados visualmente
- **Subida de archivos** (cuando aplique)
- **Vista previa** de imágenes
- **Panel lateral** con información adicional
- **Botones de acción** (Guardar, Limpiar, Cancelar)

#### Estructura estándar:
```html
<form id="formXXXX" method="POST" action="..." enctype="multipart/form-data" novalidate>
    <!-- Hidden fields para edición -->
    <!-- Campos de datos -->
    <!-- Botones de acción -->
</form>
```

#### Validaciones:
- Frontend: HTML5 + JavaScript personalizado
- Backend: En métodos store() y update()

### 3. Detalle (detalle.php)

#### Características principales:
- **Información completa** de la entidad
- **Estadísticas** relacionadas
- **Botones de acción** contextuales
- **Panel lateral** con acciones rápidas
- **Relaciones** con otras entidades (cuando aplique)

## Patrones de Código

### 1. Controlador - Método index()

```php
public function index()
{
    $this->requirePermission('nombre_modulo');

    $page = (int) $this->get('page', 1);
    $perPage = (int) $this->get('per_page', 10);
    
    // Validar perPage
    $allowedPerPage = [5, 10, 25, 50];
    if (!in_array($perPage, $allowedPerPage)) {
        $perPage = 10;
    }
    
    $filters = [
        'campo1' => $this->get('campo1'),
        'campo2' => $this->get('campo2'),
        'estado' => $this->get('estado')
    ];

    $result = $this->modelo->getWithDetails($page, $perPage, $filters);

    $data = [
        'title' => 'Gestión de XXXX',
        'registros' => $result['data'],
        'pagination' => $result,
        'filters' => $filters,
        'isAdminArea' => true
    ];

    return $this->render('admin/operaciones/xxxx/listado', $data, 'main');
}
```

### 2. Modelo - Método getWithDetails()

```php
public function getWithDetails($page = 1, $perPage = 10, $filters = [])
{
    $where = "1=1";
    $params = [];
    
    // Aplicar filtros
    if (!empty($filters['campo1'])) {
        $where .= " AND campo1 LIKE ?";
        $params[] = '%' . $filters['campo1'] . '%';
    }
    
    if (isset($filters['estado']) && $filters['estado'] !== '') {
        $where .= " AND estado = ?";
        $params[] = (int) $filters['estado'];
    }
    
    return $this->paginateWithParams($page, $perPage, $where, "campo_orden ASC", $params);
}
```

### 3. Vista - Tabla de listado

```php
<table class="table table-hover mb-0">
    <thead class="table-light">
        <tr>
            <th class="border-0 py-3">Campo 1</th>
            <th class="border-0 py-3">Campo 2</th>
            <th class="border-0 py-3">Estado</th>
            <th class="border-0 py-3 text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($registros as $registro): ?>
            <tr>
                <td class="border-0 py-3"><?= htmlspecialchars($registro['campo1']) ?></td>
                <td class="border-0 py-3"><?= htmlspecialchars($registro['campo2']) ?></td>
                <td class="border-0 py-3">
                    <?php if ($registro['estado'] == 1): ?>
                        <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td class="border-0 py-3 text-center">
                    <div class="btn-group btn-group-sm">
                        <a href="<?= url('/xxxx/' . $registro['id']) ?>" 
                           class="btn btn-outline-primary" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?= url('/xxxx/' . $registro['id'] . '/edit') ?>" 
                           class="btn btn-outline-warning" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
```

## Configuraciones por Tipo de Campo

### Campos de Texto
- Validación: `required`, `maxlength`
- HTML: `<input type="text" class="form-control" required>`

### Campos Numéricos
- Validación: `required`, `min`, `max`
- HTML: `<input type="number" class="form-control" required>`

### Campos de Precio/Moneda
- Validación: `required`, `min="0"`, `step="0.01"`
- Formato: Separador de miles, símbolo de moneda

### Campos de Estado
- Tipo: SELECT con opciones predefinidas
- Valores comunes: 0=Inactivo, 1=Activo
- Visualización: Badges con colores semánticos

### Campos de Fecha
- Tipo: `date` o `datetime-local`
- Validación: Formato y rangos válidos

### Campos de Archivo/Imagen
- Validación: Tipos MIME, tamaño máximo
- Vista previa: Mostrar imagen actual y nueva
- Gestión: Crear directorio si no existe, eliminar archivo anterior

## Estados y Workflows

### Estados Comunes
- **0**: Inactivo/Eliminado (rojo)
- **1**: Activo (verde) 
- **2**: Estado especial (amarillo/azul según contexto)

### Cambios de Estado
- AJAX con confirmación
- Mensajes contextuales según acción
- Actualización visual inmediata
- Logging de cambios (opcional)

## Exportaciones

### Excel (.xlsx)
- Biblioteca: PhpOffice/PhpSpreadsheet
- Características:
  - Encabezados con estilo
  - Formato de datos apropiado
  - Ajuste automático de columnas
  - Filtros aplicados respetados
  - Nombre de archivo con fecha

### PDF
- Biblioteca: TCPDF
- Características:
  - Orientación vertical A4
  - Tabla optimizada para papel
  - Información de filtros aplicados
  - Colores y estilos básicos
  - Conteo de registros

## Permisos y Seguridad

### Validación de Permisos
```php
$this->requirePermission('nombre_modulo');
```

### Validación de Entrada
- Sanitización de datos: `htmlspecialchars()`
- Validación de tipos: casting explícito
- Parámetros preparados en SQL
- Validación de archivos subidos

### Control de Acceso
- Verificación en cada método del controlador
- Redirección a login si no autenticado
- Error 403 si sin permisos suficientes

## JavaScript y Interactividad

### Funciones Estándar
- `cambiarEstado()`: Cambio de estado con confirmación
- `exportar()`: Descarga de archivos Excel/PDF
- Validación de formularios en tiempo real
- Vista previa de imágenes

### Bibliotecas Utilizadas
- **SweetAlert2**: Confirmaciones y alertas
- **Bootstrap**: Componentes UI
- **Font Awesome**: Iconografía

## Base de Datos

### Convenciones de Nomenclatura
- **Tablas**: Nombre singular en minúsculas
- **Primary Key**: `id_nombretabla`
- **Campos**: `nombretabla_campo`
- **Estados**: Campo `nombretabla_estado` (INT)

### Estructura de Tabla Estándar
```sql
CREATE TABLE `nombretabla` (
  `id_nombretabla` int NOT NULL AUTO_INCREMENT,
  `nombretabla_campo1` varchar(100) NOT NULL,
  `nombretabla_campo2` text,
  `nombretabla_estado` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_nombretabla`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

## Instrucción de Uso

Para generar un CRUD completo, usar la siguiente instrucción:

```
"Genera un CRUD para la entidad XXXX"
```

**Esto significa:**
1. Analizar la tabla correspondiente en `bd.sql`
2. Crear modelo `Models/XXXX.php` con tabla inferida
3. Crear controlador `Controllers/XXXXController.php` con todos los métodos
4. Crear vistas en `Views/admin/operaciones/xxxx/`:
   - `listado.php` - Con filtros específicos de la entidad
   - `formulario.php` - Con campos apropiados según la tabla
   - `detalle.php` - Con información completa y estadísticas
5. Aplicar todos los patrones y funcionalidades definidas en este documento
6. Respetar convenciones de nomenclatura y estructura
7. Incluir validaciones frontend y backend
8. Implementar exportaciones Excel y PDF
9. Agregar JavaScript para interactividad

### Campos que se Deben Inferir Automáticamente
- **Campos de texto**: Por tipo VARCHAR/TEXT
- **Campos numéricos**: Por tipo INT/FLOAT
- **Campos de estado**: Por convención `_estado`
- **Claves foráneas**: Por convención `rela_`
- **Campos de fecha**: Por tipo DATE/DATETIME
- **Campos opcionales**: Por constraint NULL

### Validaciones Automáticas
- **Requeridos**: NOT NULL en la tabla
- **Longitud máxima**: Tamaño del campo VARCHAR
- **Valores mínimos/máximos**: Según tipo de campo
- **Formatos específicos**: Email, URL, etc.

---

## Mecanismo de Razonamiento y Control de Calidad

### Principio de Contraste con Referencia de Calidad

**INSTRUCCIÓN CRÍTICA**: Antes de implementar cualquier CRUD, siempre contrastar con el módulo de **Cabañas** como **patrón de calidad objetivo**. Este módulo representa el estándar de excelencia que debe alcanzarse en todos los aspectos.

### Proceso de Validación por Contraste

#### 1. **Análisis Comparativo de Interfaces**

Antes de generar cualquier vista, realizar el siguiente razonamiento:

```
PREGUNTA DE CONTRASTE: "¿Cómo resuelve esto el módulo de Cabañas?"

ANÁLISIS OBLIGATORIO:
1. Revisar Views/admin/operaciones/cabanias/listado.php
2. Examinar Views/admin/operaciones/cabanias/formulario.php  
3. Estudiar Views/admin/operaciones/cabanias/detalle.php
4. Identificar patrones visuales, estructuras y funcionalidades
5. Adaptar esos patrones a la nueva entidad
```

#### 2. **Criterios de Calidad Específicos**

**Listado (listado.php):**
- ✅ **Header oscuro** con título y botón "Nueva [Entidad]"
- ✅ **Filtros horizontales** compactos con labels pequeños
- ✅ **Iconos contextuales** en columnas (bed, bath, users, etc.)
- ✅ **Badges con colores semánticos** (success, warning, danger)
- ✅ **Botones de acción** agrupados con tooltips descriptivos
- ✅ **Formato de precios** con separadores y moneda
- ✅ **Información secundaria** en texto pequeño y gris
- ✅ **Paginación** con información de registros

**Formulario (formulario.php):**
- ✅ **Header con breadcrumb** de navegación
- ✅ **Layout de 2 columnas** (8/4) principal/lateral
- ✅ **Card principal** para datos básicos
- ✅ **Panel lateral** para imágenes y acciones
- ✅ **Validaciones visuales** en tiempo real
- ✅ **Comentarios de ayuda** para campos complejos
- ✅ **Vista previa** de imágenes antes de guardar

**Detalle (detalle.php):**
- ✅ **Botones de acción** contextuales en header
- ✅ **Layout responsive** con información organizada
- ✅ **Estadísticas visuales** con iconos y métricas
- ✅ **Panel de acciones rápidas** en lateral
- ✅ **Información técnica** separada visualmente
- ✅ **Estados dinámicos** con cambios en vivo

#### 3. **Proceso de Contraste Sistemático**

Para cada componente generado, aplicar este checklist:

**PASO 1: VISUAL COMPARISON**
```
- ¿El header tiene el mismo estilo y estructura que Cabañas?
- ¿Los filtros siguen la misma disposición horizontal compacta?
- ¿Los iconos están alineados y son contextualmente apropiados?
- ¿Los badges de estado siguen la misma paleta de colores?
- ¿Los botones de acción tienen el mismo agrupamiento?
```

**PASO 2: FUNCTIONAL COMPARISON**
```
- ¿La paginación funciona exactamente igual?
- ¿Los filtros se comportan de la misma manera?
- ¿Las validaciones tienen la misma retroalimentación visual?
- ¿Las exportaciones mantienen el mismo formato?
- ¿Los cambios de estado siguen el mismo patrón AJAX?
```

**PASO 3: UX COMPARISON**
```
- ¿La navegación es intuitiva y consistente?
- ¿Los mensajes de error/éxito son coherentes?
- ¿La responsividad se mantiene en todos los breakpoints?
- ¿Los tooltips y ayudas contextuales están presentes?
- ¿El tiempo de carga y rendimiento es comparable?
```

#### 4. **Adaptación Inteligente**

**REGLA DE ORO**: No copiar literalmente, sino **adaptar inteligentemente**

```
EJEMPLO DE RAZONAMIENTO:
- Cabañas usa iconos "bed" y "bath" → Productos podría usar "box" y "tag"
- Cabañas muestra "Capacidad: X personas" → Productos muestra "Stock: X unidades"  
- Cabañas tiene estado "Ocupada" → Productos tiene estado "Sin Stock"
- Cabañas muestra precio "$/noche" → Productos muestra precio "c/unidad"
```

#### 5. **Checklist de Finalización**

Antes de considerar terminado un CRUD, verificar:

**CONSISTENCIA VISUAL (90% similitud con Cabañas)**
- [ ] Esquema de colores idéntico
- [ ] Tipografía y espaciado coherente  
- [ ] Iconografía contextual apropiada
- [ ] Animaciones y transiciones similares

**CONSISTENCIA FUNCIONAL (100% similitud con Cabañas)**  
- [ ] Patrones de navegación idénticos
- [ ] Flujos de trabajo equivalentes
- [ ] Mensajería de sistema coherente
- [ ] Comportamiento de filtros y paginación igual

**CONSISTENCIA DE CÓDIGO (100% similitud con Cabañas)**
- [ ] Estructura HTML equivalente
- [ ] Clases CSS reutilizadas
- [ ] Funciones JavaScript coherentes
- [ ] Patrones PHP de controlador/modelo iguales

### Implementación del Mecanismo

**ANTES de generar cualquier archivo:**

1. **Leer y analizar** el archivo correspondiente en el módulo Cabañas
2. **Identificar patrones** específicos y estructuras clave
3. **Adaptar inteligentemente** a la nueva entidad
4. **Generar el código** manteniendo consistencia
5. **Revisar diferencias** y corregir desviaciones

**NUNCA generar código sin haber consultado primero la referencia de Cabañas.**

### Ejemplo de Aplicación

```
SOLICITUD: "Genera vista de listado para Productos"

PROCESO OBLIGATORIO:
1. Leer Views/admin/operaciones/cabanias/listado.php líneas 1-100
2. Identificar: estructura de header, disposición de filtros, formato de tabla
3. Leer Views/admin/operaciones/cabanias/listado.php líneas 100-200  
4. Identificar: badges de estado, botones de acción, iconografía
5. Adaptar patrones encontrados a campos de Productos
6. Generar código manteniendo estructura y estilos idénticos
7. Revisar resultado vs. referencia de Cabañas
```

**Esta metodología garantiza que todos los CRUDs mantengan la coherencia visual, funcional y de experiencia de usuario establecida en el módulo de Cabañas.**

---

## Especificaciones de Paginación Optimizada

### Principios de Diseño de Paginación

Todos los CRUDs deben implementar un **sistema de paginación consistente y optimizado** basado en los siguientes principios:

#### 1. **Estructura de Datos Estándar**

**Modelo - Método `getWithDetails()`:**
```php
return [
    'data' => $records,              // Registros de la página actual
    'total' => $totalRecords,        // Total de registros (con filtros)
    'current_page' => $page,         // Página actual
    'total_pages' => ceil($totalRecords / $perPage), // Total de páginas
    'per_page' => $perPage,         // Registros por página
    'offset' => $offset,            // Offset para cálculos
    'limit' => $perPage             // Límite para cálculos
];
```

**Modelo - Método `getAllWithDetailsForExport()`:**
```php
return [
    'data' => $allRecords,          // Todos los registros sin paginación
    'total' => $totalRecords        // Total para estadísticas de exportación
];
```

#### 2. **Paginación Superior e Inferior Idéntica**

**Vista - Estructura obligatoria:**
```php
<!-- PAGINACIÓN SUPERIOR -->
<?php if (isset($pagination) && $pagination['total'] > 0): ?>
    <div class="card-header bg-light border-bottom py-2">
        <?php $renderPagination(true); ?>
    </div>
<?php endif; ?>

<!-- TABLA DE DATOS -->
<div class="table-responsive">
    <table><!-- contenido --></table>
</div>

<!-- PAGINACIÓN INFERIOR -->
<?php if (isset($pagination) && $pagination['total'] > 0): ?>
    <div class="card-footer bg-white border-top py-3">
        <?php $renderPagination(true); ?>
    </div>
<?php endif; ?>
```

#### 3. **Función Reutilizable de Paginación**

**Implementación obligatoria:**
```php
$renderPagination = function($showInfo = true) use ($pagination, $start, $end) {
?>
    <div class="row align-items-center">
        <?php if ($showInfo): ?>
            <!-- INFORMACIÓN DE REGISTROS (siempre visible) -->
            <div class="col-sm-6">
                <span class="text-muted small">
                    Mostrando <?= $start ?> a <?= $end ?> de <?= $pagination['total'] ?> entradas
                </span>
            </div>
        <?php endif; ?>
        
        <div class="col-sm-<?= $showInfo ? '6' : '12' ?>">
            <!-- NAVEGACIÓN (solo si hay múltiples páginas) -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <nav aria-label="Paginación" class="d-flex justify-content-<?= $showInfo ? 'end' : 'center' ?>">
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Botón Anterior -->
                        <?php if ($pagination['current_page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) ?>">Anterior</a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Navegación inteligente con elipsis -->
                        <?php 
                        $startPage = max(1, $pagination['current_page'] - 2);
                        $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                        
                        // Primera página + elipsis
                        if ($startPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
                            </li>
                            <?php if ($startPage > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Páginas del rango actual -->
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                <?php if ($i == $pagination['current_page']): ?>
                                    <!-- Página actual: destacada y no clickeable -->
                                    <span class="page-link bg-primary text-white border-primary"><?= $i ?></span>
                                <?php else: ?>
                                    <!-- Otras páginas: enlaces normales -->
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Última página + elipsis -->
                        <?php if ($endPage < $pagination['total_pages']): ?>
                            <?php if ($endPage < $pagination['total_pages'] - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['total_pages']])) ?>"><?= $pagination['total_pages'] ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Botón Siguiente -->
                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])) ?>">Siguiente</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
<?php }; ?>
```

#### 4. **Cálculo de Rangos**

**Implementación obligatoria al inicio:**
```php
<?php 
$perPage = (int) ($_GET['per_page'] ?? 10);
$start = (($pagination['current_page'] - 1) * $perPage) + 1;
$end = min($pagination['current_page'] * $perPage, $pagination['total']);
?>
```

### Comportamiento por Casos de Uso

#### **Caso 1: Una Sola Página (1-10 registros)**
- ✅ **Información visible:** "Mostrando 1 a 8 de 8 entradas"
- ❌ **Sin navegación:** No muestra botones de página
- 🎯 **UX:** Interfaz limpia sin elementos innecesarios

#### **Caso 2: Múltiples Páginas (11+ registros)**
- ✅ **Información completa:** "Mostrando 11 a 20 de 45 entradas"
- ✅ **Navegación completa:** Botones Anterior/Siguiente + números de página
- ✅ **Página actual destacada:** Fondo azul (`bg-primary text-white border-primary`)
- ✅ **Navegación inteligente:** Elipsis (...) cuando hay muchas páginas

#### **Caso 3: Sin Registros (0 entradas)**
- ✅ **Estado vacío:** Mensaje contextual con CTA para crear registro
- ❌ **Sin paginación:** No muestra información ni navegación

### Especificaciones Visuales

#### **Colores y Estilos**
- **Paginación superior:** `bg-light` (gris claro) + `border-bottom`
- **Paginación inferior:** `bg-white` (blanco) + `border-top`
- **Página actual:** `bg-primary text-white border-primary` (azul destacado)
- **Páginas inactivas:** Color estándar de Bootstrap
- **Elipsis:** `disabled` con `<span>` no clickeable

#### **Responsividad**
- **Desktop:** Layout de 2 columnas (información | navegación)
- **Móvil:** Stack vertical automático con Bootstrap responsive
- **Alineación:** Información a la izquierda, navegación a la derecha

### Validaciones y Manejo de Errores

#### **Validación de Parámetros**
```php
// En el controlador index()
$page = (int) $this->get('page', 1);
$perPage = (int) $this->get('per_page', 10);

// Validar perPage permitidos
$allowedPerPage = [5, 10, 25, 50];
if (!in_array($perPage, $allowedPerPage)) {
    $perPage = 10;
}

// Validar página mínima
if ($page < 1) {
    $page = 1;
}
```

#### **Manejo de Exportaciones**
```php
// Siempre verificar datos antes de exportar
$result = $this->modelo->getAllWithDetailsForExport($filters);
$datos = $result['data'];

if (empty($datos)) {
    $this->redirect('/entidad', 'No hay datos para exportar', 'error');
    return;
}

// Usar $result['total'] para estadísticas en archivos
```

### Checklist de Implementación

**Antes de finalizar cualquier vista de listado, verificar:**

- [ ] ✅ **Información siempre visible** - Muestra conteo incluso con 1 página
- [ ] ✅ **Paginación superior e inferior idénticas** - Misma estructura y contenido
- [ ] ✅ **Sin navegación en página única** - Solo información, sin botones
- [ ] ✅ **Página actual destacada** - Color azul distintivo y no clickeable
- [ ] ✅ **Navegación inteligente** - Elipsis cuando hay muchas páginas
- [ ] ✅ **Estructura de datos estándar** - Mismo formato en modelo
- [ ] ✅ **Filtros respetados** - Totales incluyen filtros aplicados
- [ ] ✅ **Exportaciones consistentes** - Usan estructura {'data': [], 'total': X}
- [ ] ✅ **Validación de parámetros** - perPage y page validados
- [ ] ✅ **Responsive** - Funciona en móvil y desktop

### Patrones Prohibidos

❌ **NO usar estas implementaciones:**
- Paginación solo en un lugar (arriba O abajo)
- Botones de navegación con una sola página
- Página actual como enlace clickeable
- Información de registros solo con múltiples páginas
- Estructura de datos inconsistente entre modelos
- Exportaciones que devuelven arrays simples sin total
- Navegación sin elipsis en listados largos

---

## Configuración de Enrutamiento

### Principios de Enrutamiento

**CRÍTICO**: Las rutas definidas en `Core/Application.php` DEBEN coincidir exactamente con los métodos implementados en el controlador y las URLs utilizadas en las vistas.

#### 1. **Patrón Estándar de Rutas por Entidad**

Para cada entidad XXXX, seguir el patrón establecido por el módulo de Cabañas:

```php
// Rutas de XXXX (en Core/Application.php)
$this->router->get('/xxxx', 'XXXXController@index');
$this->router->any('/xxxx/create', 'XXXXController@create');
$this->router->get('/xxxx/exportar', 'XXXXController@exportar');
$this->router->get('/xxxx/exportar-pdf', 'XXXXController@exportarPdf');
$this->router->get('/xxxx/{id}', 'XXXXController@show');
$this->router->any('/xxxx/{id}/edit', 'XXXXController@edit');
$this->router->post('/xxxx/{id}/delete', 'XXXXController@delete');
$this->router->post('/xxxx/{id}/restore', 'XXXXController@restore');
$this->router->post('/xxxx/{id}/estado', 'XXXXController@cambiarEstado');
```

#### 2. **Métodos HTTP y Funcionalidad**

| Ruta | Método HTTP | Controlador | Funcionalidad |
|------|-------------|-------------|---------------|
| `/xxxx` | `GET` | `index()` | Listado con filtros y paginación |
| `/xxxx/create` | `GET/POST` | `create()` | Formulario creación y procesamiento |
| `/xxxx/exportar` | `GET` | `exportar()` | Exportación Excel (.xlsx) |
| `/xxxx/exportar-pdf` | `GET` | `exportarPdf()` | Exportación PDF |
| `/xxxx/{id}` | `GET` | `show($id)` | Vista de detalle |
| `/xxxx/{id}/edit` | `GET/POST` | `edit($id)` | Formulario edición y procesamiento |
| `/xxxx/{id}/delete` | `POST` | `delete($id)` | Baja lógica (estado = 0) |
| `/xxxx/{id}/restore` | `POST` | `restore($id)` | Alta lógica (estado = 1) |
| `/xxxx/{id}/estado` | `POST` | `cambiarEstado($id)` | Cambio estado AJAX |

#### 3. **Implementación en Controlador**

**Patrón obligatorio para métodos que manejan GET y POST:**

```php
public function create()
{
    $this->requirePermission('entidad');

    if ($this->isPost()) {
        return $this->store(); // Procesar datos POST
    }

    // Mostrar formulario GET
    $data = [
        'title' => 'Nueva Entidad',
        'isAdminArea' => true
    ];

    return $this->render('admin/operaciones/entidad/formulario', $data, 'main');
}

public function edit($id)
{
    $this->requirePermission('entidad');

    $entidad = $this->modelo->find($id);
    if (!$entidad) {
        return $this->view->error(404);
    }

    if ($this->isPost()) {
        return $this->update($id); // Procesar datos POST
    }

    // Mostrar formulario GET
    $data = [
        'title' => 'Editar Entidad',
        'entidad' => $entidad,
        'isAdminArea' => true
    ];

    return $this->render('admin/operaciones/entidad/formulario', $data, 'main');
}
```

#### 4. **URLs en las Vistas**

**CRÍTICO**: Las URLs en JavaScript AJAX deben coincidir con las rutas definidas:

```javascript
// ✅ CORRECTO - Cambio de estado
fetch(`<?= url('/entidad') ?>/${id}/estado`, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({ estado: nuevoEstado })
})

// ❌ INCORRECTO - URL no coincide con ruta
fetch(`<?= url('/entidad') ?>/${id}/cambiar-estado`, { // Esta ruta NO existe
```

**URLs en formularios HTML:**

```html
<!-- Formulario de creación -->
<form method="POST" action="<?= url('/entidad/create') ?>">

<!-- Formulario de edición -->
<form method="POST" action="<?= url('/entidad/' . $entidad['id'] . '/edit') ?>">
```

#### 5. **Configuración en Base de Datos**

**Tabla `modulo`:** Asegurar que el campo `modulo_ruta` coincida con la ruta base:

```sql
-- ✅ CORRECTO
INSERT INTO modulo (modulo_descripcion, modulo_ruta, modulo_estado, rela_menu) 
VALUES ('Servicios', 'servicios', 1, NULL);

-- ❌ INCORRECTO 
INSERT INTO modulo (modulo_descripcion, modulo_ruta, modulo_estado, rela_menu) 
VALUES ('Servicios', '/servicios', 1, NULL); -- No incluir slash inicial
```

#### 6. **Orden de Definición de Rutas**

**IMPORTANTE**: Las rutas específicas DEBEN definirse ANTES que las rutas con parámetros:

```php
// ✅ CORRECTO - Orden adecuado
$this->router->get('/xxxx/exportar', 'XXXXController@exportar');        // Específica
$this->router->get('/xxxx/exportar-pdf', 'XXXXController@exportarPdf'); // Específica
$this->router->get('/xxxx/{id}', 'XXXXController@show');                // Con parámetro

// ❌ INCORRECTO - Orden inadecuado
$this->router->get('/xxxx/{id}', 'XXXXController@show');                // Con parámetro primero
$this->router->get('/xxxx/exportar', 'XXXXController@exportar');        // Nunca se ejecutará
```

#### 7. **Validación de Rutas**

**Checklist obligatorio antes de finalizar:**

- [ ] ✅ **Todas las rutas** definidas en `Application.php` tienen métodos correspondientes en el controlador
- [ ] ✅ **Todos los métodos** del controlador tienen rutas definidas (excepto métodos privados/helper)
- [ ] ✅ **URLs en vistas** (HTML y JavaScript) coinciden con rutas definidas
- [ ] ✅ **Parámetros de ruta** (`{id}`) se pasan correctamente a los métodos del controlador
- [ ] ✅ **Métodos HTTP** apropiados (GET para formularios/listados, POST para acciones)
- [ ] ✅ **Orden de rutas** correcto (específicas antes que paramétricas)

#### 8. **Problemas Comunes y Soluciones**

| Problema | Síntoma | Solución |
|----------|---------|----------|
| **Ruta no encontrada (404)** | "Page not found" al acceder | Verificar ruta en `Application.php` |
| **Método no existe** | Error de PHP "Method does not exist" | Implementar método en controlador |
| **AJAX no funciona** | Error 404 en peticiones AJAX | Corregir URL en JavaScript |
| **Formulario no procesa** | Formulario no guarda datos | Verificar `action` del form y método POST |
| **Parámetros no llegan** | `$id` es null en método | Verificar coincidencia `{id}` en ruta |

#### 9. **Herramientas de Diagnóstico**

**Script de prueba recomendado:**
Crear `test_rutas_[entidad].php` para verificar configuración:

```php
// Verificar rutas registradas
// Probar resolución de URLs
// Validar existencia de métodos en controlador
// Enlaces de prueba directa
```

### Patrones de URL Estándar

**Estructura consistente para todas las entidades:**
- **Listado:** `/entidad`
- **Crear:** `/entidad/create` (GET y POST)  
- **Ver:** `/entidad/{id}`
- **Editar:** `/entidad/{id}/edit` (GET y POST)
- **Exportar:** `/entidad/exportar` y `/entidad/exportar-pdf`
- **Estado:** `/entidad/{id}/estado` (POST AJAX)
- **Eliminar:** `/entidad/{id}/delete` (POST)
- **Restaurar:** `/entidad/{id}/restore` (POST)

---

*Documento generado a partir del análisis del módulo Cabañas - Proyecto Sistema de Gestión de Cabañas*
*Actualizado con Especificaciones de Paginación Optimizada y Enrutamiento - Noviembre 2025*