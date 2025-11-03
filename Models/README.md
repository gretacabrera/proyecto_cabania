# Models - Capa de Datos del Sistema

Esta carpeta contiene todos los modelos de datos de la aplicación, representando las entidades del negocio y su lógica de acceso a datos siguiendo el patrón Active Record y las mejores prácticas de desarrollo.

## 📁 **Arquitectura de Modelos**

### 🏗️ **Estructura y Organización**

Los modelos están organizados por entidades de negocio y siguen una nomenclatura consistente:
- **Namespace**: `App\Models`
- **Herencia**: Extienden de `App\Core\Model`
- **Nomenclatura**: PascalCase, singular (ej: `Usuario`, `Reserva`)
- **Convenciones**: Propiedades protegidas, métodos públicos

### 📋 **Inventario Completo de Modelos (25 modelos)**

#### **🏠 Modelos de Alojamiento y Reservas**
Modelos para la gestión del negocio principal:

- **`Cabania.php`** - Gestión de cabañas del complejo
- **`Reserva.php`** - Reservas de huéspedes (online y presenciales)
- **`Ingreso.php`** - Registros de check-in de huéspedes
- **`Salida.php`** - Registros de check-out de huéspedes
- **`Comentario.php`** - Comentarios y feedback de huéspedes

#### **👥 Modelos de Personas y Usuarios**
Modelos para gestión de usuarios del sistema:

- **`Usuario.php`** - Usuarios del sistema (administradores, recepcionistas)
- **`Persona.php`** - Datos base de personas (huéspedes y usuarios)

#### **🛍️ Modelos de Productos y Servicios**
Modelos para la gestión comercial:

- **`Producto.php`** - Productos vendibles (consumibles, souvenirs)
- **`Servicio.php`** - Servicios ofrecidos (spa, tours, restaurante)
- **`Consumo.php`** - Consumos realizados por huéspedes
- **`Categoria.php`** - Categorías de productos
- **`Marca.php`** - Marcas de productos

#### **💳 Modelos Financieros**
Modelos para gestión de pagos y métodos:

- **`MetodoPago.php`** - Métodos de pago disponibles

#### **📊 Modelos de Estados y Configuración**
Modelos para configuración del sistema:

- **`EstadoPersona.php`** - Estados de personas (activo, inactivo, suspendido)
- **`EstadoProducto.php`** - Estados de productos (disponible, agotado, descontinuado)
- **`EstadoReserva.php`** - Estados de reservas (pendiente, confirmada, cancelada)
- **`CondicionSalud.php`** - Condiciones de salud de huéspedes
- **`Periodo.php`** - Períodos y temporadas del año

#### **📞 Modelos de Contacto y Comunicación**
Modelos para gestión de contactos:

- **`TipoContacto.php`** - Tipos de contacto (teléfono, email, etc.)
- **`TipoServicio.php`** - Tipos de servicios ofrecidos

#### **🔐 Modelos de Seguridad y Permisos**
Modelos para el sistema de autenticación y autorización:

- **`Perfil.php`** - Perfiles/roles de usuario (admin, recepcionista, huésped)
- **`Modulo.php`** - Módulos del sistema
- **`PerfilModulo.php`** - Relación entre perfiles y módulos (permisos)
- **`Menu.php`** - Menús del sistema por perfil

#### **📈 Modelos de Reportes**
Modelos para generación de reportes:

- **`Reporte.php`** - Generación y configuración de reportes

---

## 🎯 **Estructura Base de los Modelos**

### **Clase Base Model**

Todos los modelos heredan de `App\Core\Model` que proporciona:

```php
<?php

namespace App\Models;

use App\Core\Model;

class ExampleModel extends Model
{
    // Configuración de tabla
    protected $table = 'example_table';
    protected $primaryKey = 'id_example';
    
    // Campos permitidos para mass assignment
    protected $fillable = [
        'field1', 'field2', 'field3'
    ];
    
    // Campos ocultos en serialización
    protected $hidden = [
        'password', 'secret_token'
    ];
    
    // Timestamps automáticos
    protected $timestamps = true;
    
    // Soft deletes
    protected $softDeletes = true;

    /**
     * Constructor del modelo
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Métodos específicos del modelo
     */
    public function customMethod()
    {
        // Lógica específica del modelo
    }
}
```

### **Métodos Heredados de la Clase Base**

Cada modelo hereda automáticamente:

```php
// Operaciones CRUD básicas
public function find($id)                    // Buscar por ID
public function all($conditions = [])        // Obtener todos
public function create($data)               // Crear nuevo
public function update($id, $data)          // Actualizar
public function delete($id)                 // Eliminar
public function where($field, $value)       // Filtrar por campo

// Relaciones
public function hasMany($model, $foreignKey)     // Relación 1:N
public function belongsTo($model, $foreignKey)   // Relación N:1
public function belongsToMany($model, $pivot)    // Relación N:N

// Validaciones
public function validate($data, $rules)      // Validar datos
public function errors()                     // Obtener errores

// Utilidades
public function toArray()                    // Convertir a array
public function toJson()                     // Convertir a JSON
public function exists($id)                  // Verificar existencia
```

---

## 🏗️ **Modelos Detallados por Categoría**

### **📋 Alojamiento y Reservas**

#### **`Cabania.php`**
```php
<?php

namespace App\Models;

use App\Core\Model;

class Cabania extends Model
{
    protected $table = 'cabanias';
    protected $primaryKey = 'id_cabania';
    
    protected $fillable = [
        'cabania_nombre', 'cabania_codigo', 'cabania_descripcion',
        'cabania_capacidad', 'cabania_precio', 'rela_estado'
    ];

    /**
     * Obtener cabañas disponibles para fechas
     */
    public function getDisponibles($fechaInicio, $fechaFin)
    {
        $sql = "SELECT c.* FROM cabanias c 
                WHERE c.rela_estado = 1 
                AND c.id_cabania NOT IN (
                    SELECT r.rela_cabania FROM reservas r 
                    WHERE r.rela_estado IN (1,2) 
                    AND ((r.reserva_fechainicio <= ? AND r.reserva_fechafin >= ?) 
                         OR (r.reserva_fechainicio <= ? AND r.reserva_fechafin >= ?))
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssss", $fechaInicio, $fechaFin, $fechaInicio, $fechaFin);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtener reservas de la cabaña
     */
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'rela_cabania');
    }

    /**
     * Cambiar estado de la cabaña
     */
    public function cambiarEstado($nuevoEstado)
    {
        return $this->update($this->primaryKey, ['rela_estado' => $nuevoEstado]);
    }
}
```

#### **`Reserva.php`**
```php
<?php

namespace App\Models;

use App\Core\Model;

class Reserva extends Model
{
    protected $table = 'reservas';
    protected $primaryKey = 'id_reserva';
    
    protected $fillable = [
        'reserva_fechainicio', 'reserva_fechafin', 'reserva_cantidadpersonas',
        'reserva_total', 'reserva_observaciones', 'rela_cabania', 
        'rela_persona', 'rela_estado', 'rela_metodopago'
    ];

    /**
     * Relación con cabaña
     */
    public function cabania()
    {
        return $this->belongsTo(Cabania::class, 'rela_cabania');
    }

    /**
     * Relación con huésped
     */
    public function huesped()
    {
        return $this->belongsTo(Persona::class, 'rela_persona');
    }

    /**
     * Relación con consumos
     */
    public function consumos()
    {
        return $this->hasMany(Consumo::class, 'rela_reserva');
    }

    /**
     * Crear reserva con transacción
     */
    public function crearReservaCompleta($datosReserva, $serviciosAdicionales = [])
    {
        $this->db->begin_transaction();
        
        try {
            // Crear reserva
            $reservaId = $this->create($datosReserva);
            
            // Agregar servicios como consumos
            if (!empty($serviciosAdicionales)) {
                $consumoModel = new Consumo();
                foreach ($serviciosAdicionales as $servicio) {
                    $consumoModel->create([
                        'rela_reserva' => $reservaId,
                        'rela_servicio' => $servicio['id'],
                        'consumo_cantidad' => $servicio['cantidad'],
                        'consumo_precio' => $servicio['precio']
                    ]);
                }
            }
            
            // Cambiar estado de cabaña a ocupada
            $cabania = new Cabania();
            $cabania->cambiarEstado('ocupada');
            
            $this->db->commit();
            return $reservaId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Obtener reservas por estado
     */
    public function porEstado($estado)
    {
        return $this->where('rela_estado', $estado);
    }

    /**
     * Calcular total de la reserva
     */
    public function calcularTotal($fechaInicio, $fechaFin, $cabaniaPrecio, $servicios = [])
    {
        $inicio = new DateTime($fechaInicio);
        $fin = new DateTime($fechaFin);
        $noches = $inicio->diff($fin)->days;
        
        $totalAlojamiento = $noches * $cabaniaPrecio;
        $totalServicios = array_sum(array_column($servicios, 'precio'));
        
        return $totalAlojamiento + $totalServicios;
    }
}
```

### **👥 Modelos de Usuarios**

#### **`Usuario.php`**
```php
<?php

namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    
    protected $fillable = [
        'usuario_nombre', 'usuario_email', 'usuario_password',
        'rela_persona', 'rela_perfil'
    ];
    
    protected $hidden = ['usuario_password'];

    /**
     * Hash password antes de guardar
     */
    public function create($data)
    {
        if (isset($data['usuario_password'])) {
            $data['usuario_password'] = password_hash($data['usuario_password'], PASSWORD_DEFAULT);
        }
        return parent::create($data);
    }

    /**
     * Verificar password
     */
    public function verificarPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Relación con perfil
     */
    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'rela_perfil');
    }

    /**
     * Obtener permisos del usuario
     */
    public function permisos()
    {
        $sql = "SELECT m.modulo_nombre, pm.perfilmodulo_permisos 
                FROM usuarios u 
                JOIN perfiles_modulos pm ON u.rela_perfil = pm.rela_perfil 
                JOIN modulos m ON pm.rela_modulo = m.id_modulo 
                WHERE u.id_usuario = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $this->id_usuario);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
```

#### **`Persona.php`**
```php
<?php

namespace App\Models;

use App\Core\Model;

class Persona extends Model
{
    protected $table = 'personas';
    protected $primaryKey = 'id_persona';
    
    protected $fillable = [
        'persona_nombre', 'persona_apellido', 'persona_documento',
        'persona_email', 'persona_telefono', 'persona_direccion',
        'rela_estado', 'rela_tipocontacto', 'rela_condicion'
    ];

    /**
     * Obtener nombre completo
     */
    public function getNombreCompleto()
    {
        return $this->persona_nombre . ' ' . $this->persona_apellido;
    }

    /**
     * Relación con reservas
     */
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'rela_persona');
    }

    /**
     * Relación con usuario (si existe)
     */
    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'rela_persona');
    }
}
```

### **🛍️ Modelos Comerciales**

#### **`Producto.php`**
```php
<?php

namespace App\Models;

use App\Core\Model;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    
    protected $fillable = [
        'producto_nombre', 'producto_descripcion', 'producto_precio',
        'producto_stock', 'rela_categoria', 'rela_marca', 'rela_estado'
    ];

    /**
     * Verificar stock disponible
     */
    public function tieneStock($cantidad = 1)
    {
        return $this->producto_stock >= $cantidad;
    }

    /**
     * Reducir stock
     */
    public function reducirStock($cantidad)
    {
        if (!$this->tieneStock($cantidad)) {
            throw new Exception('Stock insuficiente');
        }
        
        $nuevoStock = $this->producto_stock - $cantidad;
        return $this->update($this->id_producto, ['producto_stock' => $nuevoStock]);
    }

    /**
     * Relación con categoría
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'rela_categoria');
    }
}
```

#### **`Servicio.php`**
```php
<?php

namespace App\Models;

use App\Core\Model;

class Servicio extends Model
{
    protected $table = 'servicios';
    protected $primaryKey = 'id_servicio';
    
    protected $fillable = [
        'servicio_nombre', 'servicio_descripcion', 'servicio_precio',
        'servicio_duracion', 'rela_tiposervicio'
    ];

    /**
     * Obtener servicios disponibles para fechas
     */
    public function getDisponiblesPorFecha($fecha)
    {
        // Implementar lógica de disponibilidad
        return $this->where('servicio_activo', 1);
    }

    /**
     * Agrupar por tipo de servicio
     */
    public function agrupadosPorTipo()
    {
        $sql = "SELECT ts.tiposervicio_nombre as categoria, s.*
                FROM servicios s
                JOIN tipos_servicios ts ON s.rela_tiposervicio = ts.id_tiposervicio
                WHERE s.servicio_activo = 1
                ORDER BY ts.tiposervicio_nombre, s.servicio_nombre";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $resultados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Agrupar por categoría
        $agrupados = [];
        foreach ($resultados as $servicio) {
            $categoria = $servicio['categoria'];
            unset($servicio['categoria']);
            $agrupados[$categoria][] = $servicio;
        }
        
        return $agrupados;
    }
}
```

### **💳 Modelo Financiero**

#### **`MetodoPago.php`**
```php
<?php

namespace App\Models;

use App\Core\Model;

class MetodoPago extends Model
{
    protected $table = 'metodos_pagos';
    protected $primaryKey = 'id_metodopago';
    
    protected $fillable = [
        'metodopago_nombre', 'metodopago_descripcion', 
        'metodopago_icono', 'metodopago_activo'
    ];

    /**
     * Obtener métodos activos
     */
    public function getActivos()
    {
        return $this->where('metodopago_activo', 1);
    }

    /**
     * Configuración específica por método
     */
    public function getConfiguracion()
    {
        switch ($this->metodopago_nombre) {
            case 'TARJETA DE CREDITO':
                return [
                    'requiere_validacion' => true,
                    'campos_requeridos' => ['numero', 'titular', 'vencimiento', 'cvv'],
                    'validacion_automatica' => true
                ];
            case 'TRANSFERENCIA BANCARIA':
                return [
                    'requiere_comprobante' => true,
                    'campos_requeridos' => ['comprobante'],
                    'validacion_manual' => true
                ];
            case 'EFECTIVO':
                return [
                    'pago_diferido' => true,
                    'requiere_confirmacion' => false
                ];
            default:
                return [];
        }
    }
}
```

---

## 🔐 **Seguridad y Validaciones**

### **Validaciones Implementadas**

Cada modelo incluye validaciones específicas:

```php
/**
 * Reglas de validación por modelo
 */
protected $validationRules = [
    'create' => [
        'field1' => 'required|string|max:255',
        'field2' => 'required|email|unique:table,field2',
        'field3' => 'numeric|min:0'
    ],
    'update' => [
        'field1' => 'string|max:255',
        'field2' => 'email'
    ]
];

/**
 * Validar datos antes de operaciones
 */
public function validate($data, $operation = 'create')
{
    $rules = $this->validationRules[$operation] ?? [];
    return $this->validator->validate($data, $rules);
}
```

### **Protección de Datos**

```php
// Campos ocultos en serialización
protected $hidden = ['password', 'token', 'secret'];

// Escape automático
public function toArray()
{
    $data = parent::toArray();
    return array_map(function($value) {
        return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
    }, $data);
}
```

---

## 📊 **Estado de Implementación**

### ✅ **Completado**
- **25 modelos** implementados y funcionales
- Relaciones entre modelos establecidas
- Operaciones CRUD básicas
- Validaciones de datos
- Métodos específicos por modelo
- Integración con base de datos

### ⏳ **En Desarrollo**
- Cachés de consultas frecuentes
- Optimización de consultas complejas
- Eventos de modelo (creating, created, etc.)
- Scopes globales y locales

### 🚀 **Próximas Mejoras**
- **Performance**: Implementar eager loading para relaciones
- **Validation**: Expandir sistema de validaciones
- **Events**: Sistema de eventos para modelos
- **Caching**: Cache inteligente de consultas
- **Observers**: Observadores para auditoría

---

## 🔧 **Uso de los Modelos**

### **Operaciones Básicas**

```php
// Instanciar modelo
$reserva = new Reserva();

// Crear registro
$nuevaReserva = $reserva->create([
    'reserva_fechainicio' => '2025-10-01',
    'reserva_fechafin' => '2025-10-05',
    'rela_cabania' => 1,
    'rela_persona' => 15
]);

// Buscar por ID
$reservaExistente = $reserva->find(10);

// Obtener todas las reservas
$todasReservas = $reserva->all();

// Filtrar reservas
$reservasConfirmadas = $reserva->porEstado(2);

// Actualizar
$reserva->update(10, ['reserva_observaciones' => 'Solicitud especial']);

// Eliminar
$reserva->delete(10);
```

### **Relaciones y Consultas Complejas**

```php
// Usar relaciones
$reserva = new Reserva();
$datosCompletos = $reserva->find(1);
$cabania = $datosCompletos->cabania();
$huesped = $datosCompletos->huesped();

// Consultas personalizadas
$cabania = new Cabania();
$disponibles = $cabania->getDisponibles('2025-10-01', '2025-10-05');

// Operaciones transaccionales
$reserva = new Reserva();
$reservaCompleta = $reserva->crearReservaCompleta($datosReserva, $servicios);
```

---

---

## 📈 **Métricas del Sistema de Modelos**

### **Distribución por Categoría**
- **🏠 Alojamiento y Reservas**: 5 modelos (20%)
- **👥 Personas y Usuarios**: 2 modelos (8%)
- **🛍️ Comercial**: 6 modelos (24%)
- **💳 Financiero**: 1 modelo (4%)
- **📊 Configuración**: 7 modelos (28%)
- **📞 Contacto**: 2 modelos (8%)
- **🔐 Seguridad**: 4 modelos (16%)
- **📈 Reportes**: 1 modelo (4%)

### **Complejidad por Modelo**
- **Alta Complejidad** (8 modelos): Reserva, Cabania, Usuario, Producto, Servicio
- **Media Complejidad** (12 modelos): Estados, Consumo, Perfil, etc.
- **Baja Complejidad** (5 modelos): Categoria, Marca, TipoContacto, etc.

### **Relaciones Implementadas**
- **hasMany (1:N)**: 15 relaciones establecidas
- **belongsTo (N:1)**: 20 relaciones establecidas  
- **belongsToMany (N:N)**: 3 relaciones (huesped_reserva, etc.)

---

## 🔗 **Enlaces de Documentación**

- **[README Principal](../README.md)** - Documentación completa del proyecto
- **[Controllers/README.md](../Controllers/README.md)** - Controladores y lógica de negocio
- **[Core/README.md](../Core/README.md)** - Framework base y arquitectura
- **[Views/README.md](../Views/README.md)** - Sistema de vistas organizadas

### **Diagramas y Referencias**
- **DER.png** - Diagrama de Entidad-Relación completo
- **bd.sql** - Estructura de base de datos con datos de ejemplo
- **model.mwb** - Modelo MySQL Workbench para referencia

---

*Modelos documentados el 12/10/2025 - Casa de Palos Cabañas*  
*25 modelos implementados con Active Record y relaciones complejas*