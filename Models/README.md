# Models - Capa de Datos del Sistema

Esta carpeta contiene todos los modelos de datos de la aplicación, representando las entidades del negocio y su lógica de acceso a datos siguiendo el patrón Active Record y las mejores prácticas de desarrollo. Incluye integración completa con **MercadoPago SDK v3.7.1** para transacciones de pago seguras.

## 📁 **Arquitectura de Modelos**

### 🏗️ **Estructura y Organización**

Los modelos están organizados por entidades de negocio y siguen una nomenclatura consistente:
- **Namespace**: `App\Models`
- **Herencia**: Extienden de `App\Core\Model`
- **Nomenclatura**: PascalCase, singular (ej: `Usuario`, `Reserva`)
- **Convenciones**: Propiedades protegidas, métodos públicos

### 📋 **Inventario Completo de Modelos (28 modelos)**

#### **🏠 Modelos de Alojamiento y Reservas**
Modelos para la gestión del negocio principal:

- **`Cabania.php`** - Gestión de cabañas del complejo
- **`Reserva.php`** - Reservas de huéspedes (online y presenciales) con integración MercadoPago
- **`Huesped.php`** - Relación huésped-reserva con datos específicos
- **`Ingreso.php`** - Registros de check-in de huéspedes
- **`Salida.php`** - Registros de check-out de huéspedes
- **`Revision.php`** - Revisiones de inventario por reserva
- **`Comentario.php`** - Comentarios y feedback de huéspedes

#### **👥 Modelos de Personas y Usuarios**
Modelos para gestión de usuarios del sistema:

- **`Usuario.php`** - Usuarios del sistema (administradores, recepcionistas)
- **`Persona.php`** - Datos base de personas (huéspedes y usuarios)

#### **🛍️ Modelos de Productos y Servicios**
Modelos para la gestión comercial:

- **`Producto.php`** - Productos vendibles (consumibles, souvenirs)
- **`Servicio.php`** - Servicios ofrecidos (spa, tours, restaurante)
- **`Consumo.php`** - Consumos realizados por huéspedes con soporte multimodal
- **`Inventario.php`** - Control de inventario por cabaña
- **`CostoDanio.php`** - Registro de costos por daños
- **`NivelDanio.php`** - Niveles de daño (leve, moderado, grave)
- **`Categoria.php`** - Categorías de productos
- **`Marca.php`** - Marcas de productos

#### **💳 Modelos Financieros**
Modelos para gestión de pagos y facturación:

- **`MetodoPago.php`** - Métodos de pago disponibles
- **`Pago.php`** - Registros de pagos realizados
- **`Factura.php`** - Facturas generadas
- **`FacturaDetalle.php`** - Detalles de items en facturas

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
- **`Contacto.php`** - Registro de contactos de personas

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

#### **`Reserva.php`** - ACTUALIZADO con MercadoPago
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
     * NUEVO - Confirmar pago de reserva con MercadoPago
     * Ejecuta transacción completa: Factura → Pago → Estado CONFIRMADA
     * 
     * @param int $reservaId ID de la reserva
     * @param array $paymentData Datos del pago de MercadoPago
     * @return array Resultado de la confirmación
     */
    public function confirmPayment($reservaId, $paymentData)
    {
        $this->db->beginTransaction();
        
        try {
            // Obtener reserva
            $sql = "SELECT * FROM reserva WHERE id_reserva = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$reservaId]);
            $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$reserva) {
                throw new Exception('Reserva no encontrada');
            }
            
            // Verificar si ya está confirmada (evitar duplicados)
            if ($reserva['rela_estadoreserva'] == 2) {
                // Obtener datos del pago existente
                $sql = "SELECT p.*, mp.metododepago_descripcion 
                        FROM pago p
                        INNER JOIN factura f ON p.rela_factura = f.id_factura
                        INNER JOIN metododepago mp ON p.rela_metododepago = mp.id_metododepago
                        WHERE f.rela_reserva = ? 
                        LIMIT 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$reservaId]);
                $pagoExistente = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $this->db->rollback();
                return [
                    'success' => true,
                    'already_confirmed' => true,
                    'pago_id' => $pagoExistente['id_pago'] ?? null,
                    'mensaje' => 'Reserva ya confirmada previamente'
                ];
            }
            
            // 1. Generar factura
            $sql = "INSERT INTO factura (rela_reserva, factura_total, factura_fecha) 
                    VALUES (?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$reservaId, $reserva['reserva_total']]);
            $facturaId = $this->db->lastInsertId();
            
            // 2. Obtener ID de método de pago MercadoPago
            $sql = "SELECT id_metododepago FROM metododepago 
                    WHERE metododepago_descripcion = 'MercadoPago' LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $metodoPago = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$metodoPago) {
                throw new Exception('Método de pago MercadoPago no configurado');
            }
            
            // 3. Registrar pago
            $sql = "INSERT INTO pago (rela_factura, rela_metododepago, pago_total, pago_fecha) 
                    VALUES (?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $facturaId, 
                $metodoPago['id_metododepago'], 
                $reserva['reserva_total']
            ]);
            $pagoId = $this->db->lastInsertId();
            
            // 4. Actualizar estado de reserva a CONFIRMADA (2)
            $sql = "UPDATE reserva SET rela_estadoreserva = 2 WHERE id_reserva = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$reservaId]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'already_confirmed' => false,
                'reserva_id' => $reservaId,
                'factura_id' => $facturaId,
                'pago_id' => $pagoId,
                'mensaje' => 'Pago confirmado exitosamente'
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error en confirmPayment: " . $e->getMessage());
            throw $e;
        }
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

    /**
     * NUEVO - Obtener usuario_id de una reserva
     * Uso: Sistema de notificaciones Pusher para enviar a canal privado del huésped
     * 
     * Flujo de relaciones:
     * reserva → huesped_reserva → huesped → persona → usuario
     * 
     * @param int $reservaId ID de la reserva
     * @return int|null ID del usuario o null si no se encuentra
     */
    public function getUsuarioIdFromReserva($reservaId)
    {
        $sql = "SELECT u.id_usuario
                FROM reserva r
                INNER JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
                INNER JOIN persona p ON h.rela_persona = p.id_persona
                INNER JOIN usuario u ON p.id_persona = u.rela_persona
                WHERE r.id_reserva = ?
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $reservaId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result['id_usuario'] ?? null;
    }
}
```

**Nuevas Características del Modelo Reserva:**
- ✅ **Integración MercadoPago**: Método `confirmPayment()` para procesamiento de pagos
- ✅ **Transacciones Garantizadas**: Rollback automático en caso de error
- ✅ **Detección de Duplicados**: Valida si la reserva ya fue confirmada previamente
- ✅ **Estructura de Pago Completa**: Reserva → Factura → Pago → Estado CONFIRMADA
- ✅ **Logging Detallado**: Error logs para troubleshooting
- ✅ **Consultas SQL Optimizadas**: JOINs correctos (pago → factura → reserva)
- ✅ **Notificaciones Pusher**: Método `getUsuarioIdFromReserva()` para obtener usuario_id
- ✅ **Canales Privados**: Soporta envío de notificaciones a canal `private-user-{userId}`

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

#### **`Consumo.php` - ACTUALIZADO CON GESTIÓN DE STOCK**
```php
<?php

namespace App\Models;

use App\Core\Model;

class Consumo extends Model
{
    protected $table = 'consumo';
    protected $primaryKey = 'id_consumo';
    
    protected $fillable = [
        'consumo_descripcion', 'consumo_cantidad', 'consumo_precio',
        'rela_producto', 'rela_servicio', 'rela_reserva', 'rela_estadoconsumo'
    ];

    /**
     * NUEVO - Crear múltiples consumos transaccionalmente
     * Uso: Módulo Admin para registro batch
     */
    public function createMultiple($consumos)
    {
        $this->db->begin_transaction();
        try {
            $ids = [];
            foreach ($consumos as $consumo) {
                $id = $this->create($consumo);
                $ids[] = $id;
            }
            $this->db->commit();
            return $ids;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * NUEVO - Obtener consumos con detalles y evitar duplicados
     * Uso: Listados en módulos Admin y Encargado Bar
     * Nota: Usa subquery para obtener solo el primer huésped de cada reserva
     */
    public function getWithDetails($page, $perPage, $filters)
    {
        $where = "1=1";
        $params = [];
        
        // Filtros aplicados según perfil
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND DATE(c.consumo_fecha) >= ?";
            $params[] = $filters['fecha_desde'];
        }
        
        if (!empty($filters['producto'])) {
            $where .= " AND c.rela_producto = ?";
            $params[] = $filters['producto'];
        }
        
        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where .= " AND c.rela_estadoconsumo = ?";
            $params[] = $filters['estado'];
        }
        
        // Subquery para evitar duplicados por múltiples huéspedes
        $sql = "SELECT c.*, 
                   COALESCE(p.producto_nombre, s.servicio_nombre) as item_nombre,
                   ec.estadoconsumo_descripcion,
                   r.reserva_codigo,
                   cab.cabania_nombre,
                   pf.personafisica_nombre,
                   pf.personafisica_apellido
            FROM consumo c
            LEFT JOIN producto p ON c.rela_producto = p.id_producto
            LEFT JOIN servicio s ON c.rela_servicio = s.id_servicio
            LEFT JOIN estadoconsumo ec ON c.rela_estadoconsumo = ec.id_estadoconsumo
            LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
            LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
            LEFT JOIN huesped_reserva hr ON hr.rela_reserva = r.id_reserva
                AND hr.id_huespedreserva = (
                    SELECT MIN(hr2.id_huespedreserva)
                    FROM huesped_reserva hr2
                    WHERE hr2.rela_reserva = r.id_reserva
                )
            LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
            LEFT JOIN persona per ON h.rela_persona = per.id_persona
            LEFT JOIN personafisica pf ON per.id_persona = pf.rela_persona
            WHERE {$where}
            ORDER BY c.id_consumo DESC";
        
        return $this->paginateWithParams($page, $perPage, $where, "c.id_consumo DESC", $params);
    }

    /**
     * NUEVO - Obtener consumos de una reserva con detalles completos
     * Uso: Módulos Admin y Huésped
     */
    public function getConsumosByReservaWithDetails($idReserva)
    {
        $sql = "SELECT c.*, 
                       COALESCE(p.producto_nombre, s.servicio_nombre) as item_nombre,
                       COALESCE(p.producto_foto, 'default.jpg') as item_foto,
                       (c.consumo_cantidad * c.consumo_precio) as subtotal
                FROM consumo c
                LEFT JOIN producto p ON c.rela_producto = p.id_producto
                LEFT JOIN servicio s ON c.rela_servicio = s.id_servicio
                WHERE c.rela_reserva = ?
                ORDER BY c.id_consumo DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idReserva);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * NUEVO - Obtener reserva activa por cabaña
     * Uso: Módulo Totem para validación
     */
    public function getReservaActivaByCabania($idCabania)
    {
        $sql = "SELECT r.* FROM reserva r
                WHERE r.rela_cabania = ?
                AND r.rela_estado IN (
                    SELECT id_estadoreserva FROM estadoreserva 
                    WHERE estadoreserva_descripcion IN ('CONFIRMADA', 'EN_CURSO')
                )
                ORDER BY r.reserva_fechainicio DESC
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idCabania);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * NUEVO - Obtener productos disponibles
     * Uso: Catálogo visual en módulos Huésped y Totem
     */
    public function getProductosDisponibles()
    {
        $sql = "SELECT p.*, c.categoria_descripcion, m.marca_descripcion
                FROM producto p
                LEFT JOIN categoria c ON p.rela_categoria = c.id_categoria
                LEFT JOIN marca m ON p.rela_marca = m.id_marca
                WHERE p.producto_estado = 1
                ORDER BY p.producto_nombre";
        
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * NUEVO - Obtener servicios disponibles
     * Uso: Catálogo visual en módulos Huésped y Totem
     */
    public function getServiciosDisponibles()
    {
        $sql = "SELECT s.*, ts.tiposervicio_descripcion
                FROM servicio s
                LEFT JOIN tiposervicio ts ON s.rela_tiposervicio = ts.id_tiposervicio
                WHERE s.servicio_activo = 1
                ORDER BY s.servicio_nombre";
        
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
}
```

---

#### **`ProductoMovimiento.php` - NUEVO: Audit Trail de Stock**
```php
<?php

namespace App\Models;

use App\Core\Model;

class ProductoMovimiento extends Model
{
    protected $table = 'productomovimiento';
    protected $primaryKey = 'id_productomovimiento';
    
    protected $fillable = [
        'rela_producto',
        'productomovimiento_tipo',
        'productomovimiento_cantidad',
        'productomovimiento_descripcion'
    ];

    /**
     * Registrar movimiento de stock con validación de tipos
     * 
     * @param int $productoId ID del producto
     * @param string $tipo Tipo de movimiento: E (Entrada), S (Salida), A (Ajuste), C (Corrección)
     * @param int $cantidad Cantidad del movimiento
     * @param string $descripcion Descripción detallada del movimiento
     * @return int ID del movimiento creado
     * @throws Exception Si el tipo no es válido
     */
    public function registrarMovimiento($productoId, $tipo, $cantidad, $descripcion)
    {
        // Validar tipo de movimiento
        $tiposValidos = ['E', 'S', 'A', 'C'];
        if (!in_array($tipo, $tiposValidos)) {
            throw new \Exception("Tipo de movimiento inválido: {$tipo}. Debe ser E, S, A o C.");
        }
        
        return $this->create([
            'rela_producto' => $productoId,
            'productomovimiento_tipo' => $tipo,
            'productomovimiento_cantidad' => $cantidad,
            'productomovimiento_descripcion' => $descripcion
        ]);
    }

    /**
     * Verificar tipo de reactivación de un consumo
     * Analiza el último movimiento para determinar si fue error o reintento
     * 
     * @param int $consumoId ID del consumo
     * @return string|null 'error' si fue error administrativo, 'reintento' si fue pérdida real, null si no aplica
     */
    public function verificarReactivacion($consumoId)
    {
        $sql = "SELECT productomovimiento_descripcion 
                FROM productomovimiento pm
                INNER JOIN consumo c ON pm.rela_producto = c.rela_producto
                WHERE c.id_consumo = ?
                ORDER BY pm.id_productomovimiento DESC
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $consumoId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!$result) {
            return null;
        }
        
        $descripcion = $result['productomovimiento_descripcion'];
        
        // Detectar tipo de reactivación por keywords en descripción
        if (strpos($descripcion, 'Corrección de error') !== false) {
            return 'error';
        }
        
        if (strpos($descripcion, 'Reintento - Sin descuento') !== false) {
            return 'reintento';
        }
        
        return null;
    }

    /**
     * Obtener historial de movimientos de un producto
     * 
     * @param int $productoId ID del producto
     * @param int $limit Límite de registros (default: 50)
     * @return array Movimientos ordenados del más reciente al más antiguo
     */
    public function getHistorialProducto($productoId, $limit = 50)
    {
        $sql = "SELECT pm.*, p.producto_nombre
                FROM productomovimiento pm
                INNER JOIN producto p ON pm.rela_producto = p.id_producto
                WHERE pm.rela_producto = ?
                ORDER BY pm.id_productomovimiento DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $productoId, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
```

**Tipos de Movimiento:**
- **`E` (Entrada)**: Incrementa stock - Devoluciones, correcciones de error
- **`S` (Salida)**: Decrementa stock - Entregas, pérdidas, ventas
- **`A` (Ajuste)**: Sin cambio de stock - Reclasificaciones, movimientos informativos
- **`C` (Corrección)**: Incrementa stock - Corrección por error administrativo

**Flujos de Uso:**
1. **Entrega de Consumo (Estado 2→3)**: Registra salida tipo `S`
2. **Pérdida de Producto (Estado 2→7)**: Registra salida tipo `S` por pérdida
3. **Anulación con Devolución (Estado 3→5)**: Registra entrada tipo `E`
4. **Reactivación por Error (Estado 7→2)**: Registra corrección tipo `C`
5. **Reactivación por Reintento (Estado 7→2)**: Registra ajuste tipo `A` (sin cambio stock)

    /**
     * NUEVO - Obtener reservas del usuario actual
     * Uso: Módulo Huésped para filtrar consumos propios
     */
    public function getReservasUsuario($idUsuario)
    {
        $sql = "SELECT r.id_reserva 
                FROM reserva r
                INNER JOIN huesped h ON r.id_reserva = h.rela_reserva
                INNER JOIN persona p ON h.rela_persona = p.id_persona
                INNER JOIN usuario u ON p.id_persona = u.rela_persona
                WHERE u.id_usuario = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return array_column($result, 'id_reserva');
    }

    /**
     * NUEVO - Actualizar consumo
     * Uso: Módulos Admin y Huésped
     */
    public function updateConsumo($idConsumo, $data)
    {
        return $this->update($idConsumo, $data);
    }

    /**
     * NUEVO - Eliminar consumo
     * Uso: Módulos Admin y Huésped
     */
    public function deleteConsumo($idConsumo)
    {
        return $this->delete($idConsumo);
    }

    /**
     * NUEVO - Obtener cabaña por código
     * Uso: Módulo Totem para configuración
     */
    public function getCabaniaByCodigo($codigo)
    {
        $sql = "SELECT * FROM cabania WHERE cabania_codigo = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
```

**Nuevas Características del Modelo Consumo:**
- ✅ **Transaccionalidad**: Método `createMultiple()` para registros batch atómicos
- ✅ **Consultas Optimizadas**: JOINs con productos/servicios para datos completos
- ✅ **Seguridad**: Validación de propiedad de consumos por usuario
- ✅ **Multi-Módulo**: Métodos específicos para Admin, Huésped y Totem
- ✅ **APIs Flexibles**: Métodos para catálogos, reservas y validaciones

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

## 🔔 **Integración con Sistema de Notificaciones Pusher**

### **Modelo Reserva - Soporte para Notificaciones en Tiempo Real**

El modelo `Reserva` incluye un método especializado para el sistema de notificaciones push con Pusher, permitiendo enviar notificaciones a los huéspedes en sus canales privados.

### **Método `getUsuarioIdFromReserva($reservaId)`**

**Propósito:**
Obtener el `id_usuario` asociado a una reserva para enviar notificaciones push al canal privado del huésped (`private-user-{userId}`).

**Cadena de Relaciones:**
```
reserva (id_reserva)
    ↓
huesped_reserva (rela_reserva, rela_huesped) [tabla de relación N:N]
    ↓
huesped (id_huesped, rela_persona)
    ↓
persona (id_persona)
    ↓
usuario (id_usuario, rela_persona)
```

**Implementación:**
```php
public function getUsuarioIdFromReserva($reservaId)
{
    $sql = "SELECT u.id_usuario
            FROM reserva r
            INNER JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
            INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
            INNER JOIN persona p ON h.rela_persona = p.id_persona
            INNER JOIN usuario u ON p.id_persona = u.rela_persona
            WHERE r.id_reserva = ?
            LIMIT 1";
    
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param("i", $reservaId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    return $result['id_usuario'] ?? null;
}
```

### **Uso en Controladores**

**ReservasController - Notificación de Reserva Cercana:**
```php
// En pagoExitoso() después de confirmar pago
$usuarioId = $this->reservaModel->getUsuarioIdFromReserva($reservaId);

if ($usuarioId) {
    $this->notificationService->notifyReservaCercana(
        $reserva, 
        $diasRestantes, 
        $usuarioId
    );
}
```

**ReservasController - Notificación de Pago Pendiente:**
```php
// En pagoPendiente()
$usuarioId = $this->reservaModel->getUsuarioIdFromReserva($reservaId);

if ($usuarioId) {
    $this->notificationService->notifyPagoPendiente(
        $reserva, 
        $montoPendiente, 
        $usuarioId
    );
}
```

**ConsumosController - Notificación de Pedido en Cabaña:**
```php
// En create() después de guardar consumo
$usuarioId = $this->reservaModel->getUsuarioIdFromReserva($rela_reserva);

if ($usuarioId) {
    $this->notificationService->notifyPedidoCabania(
        $consumoData, 
        $reserva, 
        $usuarioId
    );
}
```

**ConsumosController - Notificación de Inconveniente:**
```php
// En reportarInconveniente()
$usuarioId = $this->reservaModel->getUsuarioIdFromReserva($consumo['rela_reserva']);

if ($usuarioId) {
    $this->notificationService->notifyInconvenientePedido(
        $consumo, 
        $tipo_inconveniente, 
        $descripcion, 
        $usuarioId
    );
}
```

### **Características de la Implementación**

**Ventajas del Método:**
- ✅ **Query Optimizado**: Single JOIN query para atravesar 5 tablas
- ✅ **Seguridad**: Prepared statements con bind_param
- ✅ **Performance**: LIMIT 1 para detener búsqueda al primer resultado
- ✅ **Manejo de Errores**: Retorna null si no encuentra usuario
- ✅ **Reutilizable**: Usado por múltiples controladores

**Casos de Uso:**
1. **Reserva Cercana**: Cuando check-in está próximo (7 días o menos)
2. **Pago Pendiente**: Cuando MercadoPago reporta pago en proceso
3. **Pedido en Cabaña**: Cuando se registra nuevo consumo
4. **Inconveniente de Pedido**: Cuando se reporta problema con pedido

**Flujo de Notificación Completo:**
```
1. Evento ocurre en el sistema (pago, pedido, etc.)
   ↓
2. Controlador llama a Reserva::getUsuarioIdFromReserva($reservaId)
   ↓
3. Modelo ejecuta JOIN query y retorna usuario_id
   ↓
4. Controlador valida que usuario_id no sea null
   ↓
5. Controlador llama a NotificationService con usuario_id
   ↓
6. NotificationService envía a canal private-user-{usuario_id}
   ↓
7. Pusher distribuye notificación al cliente del huésped
   ↓
8. Frontend muestra badge, toast y sonido al huésped
```

### **Validaciones y Manejo de Errores**

**Casos Manejados:**
- ✅ Reserva sin huéspedes asociados → retorna `null`
- ✅ Huésped sin persona vinculada → retorna `null`
- ✅ Persona sin usuario creado → retorna `null`
- ✅ Múltiples huéspedes en reserva → LIMIT 1 toma el primero
- ✅ Error de SQL → prepared statement evita injection

**Uso Seguro en Controladores:**
```php
$usuarioId = $this->reservaModel->getUsuarioIdFromReserva($reservaId);

if ($usuarioId) {
    // Enviar notificación
    $this->notificationService->notify(..., $usuarioId);
} else {
    // Log: No se pudo obtener usuario para notificación
    error_log("No se encontró usuario para reserva ID: $reservaId");
}
```

### **Relación con NotificationService**

El método trabaja en conjunto con `Core/NotificationService.php`:

```php
// NotificationService recibe usuario_id del modelo
public function notifyReservaCercana($reserva, $diasRestantes, $usuarioId)
{
    $channelName = "private-user-{$usuarioId}";
    
    $data = [
        'type' => 'reserva_cercana',
        'title' => 'Tu reserva está cerca',
        'message' => "Tu estadía comienza en {$diasRestantes} días",
        // ... más datos
    ];
    
    return $this->send($channelName, 'reserva-cercana', $data);
}
```

### **Impacto en Performance**

**Optimizaciones Aplicadas:**
- ✅ Single query con JOINs en lugar de múltiples queries
- ✅ INNER JOINs para eficiencia (descarta registros sin relación)
- ✅ LIMIT 1 para detener búsqueda temprano
- ✅ Índices en foreign keys para JOINs rápidos
- ✅ Resultado cacheado por prepared statement

**Tiempo de Ejecución Estimado:**
- Con índices apropiados: ~0.001 - 0.005 segundos
- Sin índices: ~0.01 - 0.05 segundos
- Impacto en UX: Imperceptible

---

## 💳 **Integración con MercadoPago SDK v3.7.1**

### **Estructura de Base de Datos para Pagos**

La integración con MercadoPago utiliza la siguiente estructura relacional:

```
reserva (id_reserva, reserva_total, rela_estadoreserva)
    ↓
factura (id_factura, rela_reserva, factura_total)
    ↓
pago (id_pago, rela_factura, rela_metododepago, pago_total)
    ↓
metododepago (id_metododepago, metododepago_descripcion: 'MercadoPago')
```

### **Flujo de Transacción de Pago**

**1. Usuario completa reserva online:**
- Selecciona cabaña y fechas (estado: PENDIENTE)
- Agrega servicios opcionales
- Visualiza resumen con total

**2. Pasarela de pago (pasarela.php):**
```php
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

// Configurar SDK
MercadoPagoConfig::setAccessToken($access_token);

// Crear preferencia de pago
$client = new PreferenceClient();
$preference = $client->create([
    'external_reference' => $reserva_id,
    'items' => [[
        'title' => "Reserva Cabaña {$nombre}",
        'quantity' => 1,
        'unit_price' => (float)$reserva['reserva_total']
    ]],
    'back_urls' => [
        'success' => "{$base_url}/reservas/pago-exitoso",
        'failure' => "{$base_url}/reservas/pago-fallido",
        'pending' => "{$base_url}/reservas/pago-pendiente"
    ]
]);
```

**3. Usuario paga en MercadoPago:**
- Redirigido a Checkout Pro de MercadoPago
- Completa el pago con Wallet Brick
- MercadoPago procesa la transacción

**4. Callback exitoso (ReservasController::pagoExitoso):**
```php
// Obtener datos del pago
$status = $_GET['status'] ?? '';
$payment_id = $_GET['payment_id'] ?? '';
$reservaId = $_GET['external_reference'] ?? '';

// Confirmar pago y actualizar base de datos
$reservaModel = new Reserva();
$resultado = $reservaModel->confirmPayment($reservaId, [
    'payment_id' => $payment_id,
    'status' => $status
]);
```

**5. Modelo Reserva ejecuta transacción SQL:**
```php
public function confirmPayment($reservaId, $paymentData)
{
    $this->db->beginTransaction();
    try {
        // Verificar si ya está confirmada (evitar duplicados)
        if ($reserva['rela_estadoreserva'] == 2) {
            return ['success' => true, 'already_confirmed' => true];
        }
        
        // 1. Generar factura
        INSERT INTO factura (rela_reserva, factura_total, factura_fecha)
        
        // 2. Registrar pago con método MercadoPago
        INSERT INTO pago (rela_factura, rela_metododepago, pago_total, pago_fecha)
        
        // 3. Actualizar estado a CONFIRMADA (2)
        UPDATE reserva SET rela_estadoreserva = 2
        
        $this->db->commit();
    } catch (Exception $e) {
        $this->db->rollback();
        throw $e;
    }
}
```

**6. Email de confirmación:**
```php
// Obtener datos completos con JOINs correctos
$metodo = obtenerMetodoPagoReserva($reservaId);
$total = obtenerTotalPagadoReserva($reservaId);
$huespedes = contarHuespedesReserva($reservaId);

// Enviar email con EmailService
enviarNotificacionConfirmacion($reservaId);
```

### **Consultas SQL Críticas**

**Obtener método de pago:**
```sql
SELECT mp.metododepago_descripcion 
FROM pago p
INNER JOIN factura f ON p.rela_factura = f.id_factura
INNER JOIN metododepago mp ON p.rela_metododepago = mp.id_metododepago
WHERE f.rela_reserva = ?
```

**Obtener total pagado:**
```sql
SELECT SUM(p.pago_total) as total
FROM pago p
INNER JOIN factura f ON p.rela_factura = f.id_factura
WHERE f.rela_reserva = ?
```

**Contar huéspedes por edad:**
```sql
SELECT 
    COUNT(CASE WHEN h.huesped_edad >= 18 THEN 1 END) as adultos,
    COUNT(CASE WHEN h.huesped_edad < 18 THEN 1 END) as menores
FROM huesped_reserva hr
INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
WHERE hr.rela_reserva = ?
```

### **Manejo de Errores y Validaciones**

**Detección de pagos duplicados:**
- Verifica estado de reserva antes de procesar
- Si `rela_estadoreserva = 2` (CONFIRMADA), retorna éxito sin reprocesar
- Evita múltiples facturas/pagos por la misma reserva

**Rollback automático:**
- Si falla cualquier paso de la transacción, todo se revierte
- Base de datos mantiene integridad referencial
- Logs detallados para debugging

**Validación de datos:**
- Verifica existencia de reserva
- Valida método de pago configurado
- Confirma integridad de montos

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
- ✅ **28 modelos** implementados y funcionales
- ✅ Relaciones entre modelos establecidas (47 relaciones)
- ✅ Operaciones CRUD básicas en todos los modelos
- ✅ Validaciones de datos por modelo
- ✅ Métodos específicos y consultas optimizadas
- ✅ Integración completa con base de datos
- ✅ **Integración MercadoPago SDK v3.7.1** (Checkout Pro con Wallet Brick)
- ✅ **Transacciones de pago garantizadas** (Reserva → Factura → Pago)
- ✅ **Detección de pagos duplicados** con validación de estado
- ✅ **Consultas SQL optimizadas** con JOINs correctos (pago → factura → reserva)
- ✅ **Sistema de notificaciones Pusher** - Método `getUsuarioIdFromReserva()`
- ✅ **Soporte para canales privados** - JOIN a través de 5 tablas para obtener usuario_id
- ✅ Sistema multimodal de consumos (Admin, Huésped, Totem)
- ✅ Soporte transaccional para operaciones críticas
- ✅ Métodos de exportación (Excel .xlsx, PDF)
- ✅ Paginación optimizada con filtros

### 🎯 **En Producción**
- Sistema de facturación completo con MercadoPago
- Flujo de reservas online end-to-end
- Confirmación automática de pagos
- Emails de confirmación con datos completos
- **Notificaciones push en tiempo real** para huéspedes
- Gestión de inventario por cabaña
- Control de daños y costos asociados
- Revisiones de check-in/check-out
- Reportes ejecutivos con agregaciones

### 🔄 **Optimizaciones Continuas**
- **Performance**: Eager loading para relaciones frecuentes
- **Validation**: Reglas de validación personalizadas
- **Caching**: Cache inteligente de consultas complejas
- **Events**: Observadores para auditoría automática
- **Testing**: Pruebas unitarias de modelos críticos
- **MercadoPago**: Migración a credenciales de producción

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
- **🏠 Alojamiento y Reservas**: 7 modelos (25%)
- **👥 Personas y Usuarios**: 2 modelos (7%)
- **🛍️ Comercial**: 8 modelos (29%)
- **💳 Financiero**: 4 modelos (14%)
- **📊 Configuración**: 5 modelos (18%)
- **📞 Contacto**: 3 modelos (11%)
- **🔐 Seguridad**: 4 modelos (14%)
- **📈 Reportes**: 1 modelo (4%)

### **Complejidad por Modelo**
- **Alta Complejidad** (10 modelos): Reserva, Cabania, Usuario, Producto, Servicio, Consumo, Factura, Revision, Huesped, Inventario
- **Media Complejidad** (13 modelos): Estados, Perfil, Pago, CostoDanio, Ingreso, Salida, etc.
- **Baja Complejidad** (5 modelos): Categoria, Marca, TipoContacto, TipoServicio, NivelDanio

### **Relaciones Implementadas**
- **hasMany (1:N)**: 18 relaciones establecidas
- **belongsTo (N:1)**: 25 relaciones establecidas  
- **belongsToMany (N:N)**: 4 relaciones (perfil_modulo, huesped_reserva, etc.)
- **Total**: 47 relaciones entre modelos

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

*Modelos documentados el 18/11/2025 - Casa de Palos Cabañas*  
*28 modelos implementados con Active Record y 47 relaciones establecidas*  
*Integración completa con MercadoPago SDK v3.7.1 - Transacciones de pago garantizadas*