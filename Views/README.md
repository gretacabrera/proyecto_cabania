# Organización de Vistas - Casa de Palos Cabañas

Esta estructura organiza las vistas de manera lógica y escalable, siguiendo patrones de desarrollo modernos para aplicaciones web. Incluye integración completa con **MercadoPago Checkout Pro** para pagos online y **Sistema de Notificaciones en Tiempo Real con Pusher**.

## 📁 Estructura Actual (Actualizada - 18/11/2025)

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
- `reservas/` - **Sistema completo de reservas online con MercadoPago** (6 vistas):
  - `confirmar.php` - Paso 1: Confirmación de datos básicos de reserva
  - `servicios.php` - Paso 2: Selección de servicios adicionales
  - `resumen.php` - Paso 3: Vista previa de facturación y términos
  - `pasarela.php` - Paso 4: **Pasarela de pago real con MercadoPago Wallet Brick**
  - `exito.php` - Confirmación final con datos completos de pago
  - `debug_pago.php` - Vista de depuración para errores de pago (solo desarrollo)
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

## 🚀 **Sistema de Reservas Online - Flujo Completo con MercadoPago**

### 📋 **Proceso de Reserva Paso a Paso**

El sistema de reservas online implementado en `/public/reservas/` incluye un flujo completo de 6 pasos con integración real de MercadoPago:

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

#### **Paso 4: Pasarela de Pago MercadoPago (`pasarela.php`)**
- **Función**: Procesamiento real de pagos con MercadoPago Checkout Pro
- **Integración**: MercadoPago SDK v3.7.1
- **Características**:
  - **Wallet Brick**: Interfaz optimizada de MercadoPago
  - **Diseño Sobrio**: Profesional con colores corporativos (blanco, gris, bordes)
  - **Card Único**: Layout limpio con header, resumen y sección de pago
  - **Métodos de Pago**: Todos los disponibles en MercadoPago (tarjetas, efectivo, transferencias)
  - **Loading Spinner**: Indicador visual durante procesamiento
  - **Error Handling**: Manejo robusto de errores con página de debug
  - **Security Badges**: Indicadores de seguridad SSL/PCI
  - **Responsive**: Optimizado para móviles y tablets

**Flujo de procesamiento:**
```javascript
// 1. Inicializar MercadoPago SDK
const mp = new MercadoPago('PUBLIC_KEY', { locale: 'es-AR' });

// 2. Renderizar Wallet Brick
const bricksBuilder = mp.bricks();
await bricksBuilder.create('wallet', 'wallet_container', {
  initialization: {
    preferenceId: preference_id // Generado por el servidor
  }
});

// 3. Redirigir a MercadoPago para completar pago
// 4. Callback a /reservas/pago-exitoso con payment_id
```

#### **Paso 5: Callbacks de MercadoPago**
- **`pagoExitoso()`**: Procesa pago aprobado
  - Detecta redirección desde ngrok → localhost
  - Valida payment_id y external_reference
  - Ejecuta transacción SQL completa (Factura → Pago → Estado CONFIRMADA)
  - Envía email de confirmación con datos completos
  - Guarda sesión de reserva exitosa
  - Redirige a página de éxito
- **`pagoFallido()`**: Maneja pago rechazado
- **`pagoPendiente()`**: Maneja pago pendiente
- **`webhook()`**: Webhook IPN para notificaciones asíncronas

#### **Paso 6: Confirmación Exitosa (`exito.php`)**
- **Función**: Confirmación final con datos completos de pago
- **Características**:
  - Mensaje de éxito con efecto confetti
  - Número de reserva generado
  - Resumen completo de la reserva confirmada
  - **Información de pago**:
    - Método de pago: MercadoPago
    - Monto total abonado
    - Cantidad de huéspedes (adultos y menores)
  - Información práctica para la estadía
  - Email de confirmación enviado automáticamente
  - Sugerencias de servicios adicionales

#### **Vista de Debug (`debug_pago.php`)**
- **Función**: Página de depuración para errores de pago
- **Activación**: Solo cuando `APP_DEBUG=true` y ocurre un error
- **Características**:
  - Muestra parámetros GET completos
  - Información de sesión actual
  - Detalles del error con stack trace
  - Diseño claro para troubleshooting
  - Botón para volver a intentar

### 🔄 **Flujo de Datos y Estados con MercadoPago**

1. **Inicio**: Desde catálogo → seleccionar cabaña y fechas
2. **Estado "Pendiente"**: Al confirmar paso 1 (rela_estadoreserva = 1)
3. **Servicios**: Registro como consumos si se seleccionan
4. **Pasarela MercadoPago**: 
   - ✅ Crear preferencia de pago con SDK v3.7.1
   - ✅ Renderizar Wallet Brick en navegador
   - ✅ Redirigir a Checkout Pro de MercadoPago
   - ✅ Usuario completa pago en plataforma MercadoPago
5. **Callback de Pago Exitoso**: Procesamiento transaccional completo:
   - ✅ Validar payment_id y external_reference
   - ✅ Verificar si reserva ya fue confirmada (evitar duplicados)
   - ✅ Generar factura (INSERT INTO factura)
   - ✅ Registrar pago vinculado a factura (INSERT INTO pago)
   - ✅ Cambiar estado a "CONFIRMADA" (UPDATE reserva SET rela_estadoreserva = 2)
   - ✅ Enviar email con PHPMailer (datos completos: huéspedes, método, monto)
   - ❌ Rollback completo si hay errores
6. **Confirmación**: Página de éxito con todos los detalles

### 🛡️ **Validaciones y Seguridad**

- **CSRF Protection**: Tokens en todos los formularios
- **Validación de Fechas**: Prevenir reservas en el pasado
- **Capacidad**: Verificar límites de huéspedes por cabaña
- **Disponibilidad**: Validar que la cabaña esté libre
- **MercadoPago Integration**:
  - ✅ SDK oficial v3.7.1 con certificación PCI
  - ✅ Public Key y Access Token segregados
  - ✅ HTTPS obligatorio para producción
  - ✅ Webhook signature validation
  - ✅ Payment ID verification
- **Detección de Duplicados**: Verifica si reserva ya fue confirmada
- **Transaccional**: Rollback automático en errores con PDO
- **Session Management**: `session_write_close()` antes de redirects
- **Error Logging**: Logs detallados para troubleshooting

### 🎯 **Navegación y UX**

- **Barra de Progreso**: Visual en cada paso (20%, 40%, 60%, 80%, 100%)
- **Botones "Volver"**: En cada vista para retroceder
- **Validación en Tiempo Real**: JavaScript para mejor UX
- **Responsive Design**: Optimizado para móviles y tablets
- **Loading States**: 
  - Spinner durante carga de Wallet Brick
  - Indicador de procesamiento de pago
  - Animación de confirmación exitosa
- **Confirmaciones**: SweetAlert2 para acciones críticas
- **Diseño Profesional**: Colores corporativos (blanco, gris claro, bordes sutiles)
- **Error Handling**: Mensajes claros y página de debug
- **Ngrok Detection**: Redirección automática localhost para desarrollo

---

## 💳 **Integración Técnica de MercadoPago**

### **Vista: `pasarela.php`**

**Ubicación**: `Views/public/reservas/pasarela.php`

**Tecnologías utilizadas:**
- MercadoPago SDK v3.7.1 (PHP)
- MercadoPago.js (JavaScript SDK)
- Wallet Brick UI Component
- Bootstrap 5 para layout

**Estructura de la vista:**
```php
<?php
// 1. Obtener datos de reserva desde sesión
$reserva = $_SESSION['reserva_pendiente'];

// 2. Generar preferencia de pago
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

MercadoPagoConfig::setAccessToken($access_token);
$client = new PreferenceClient();
$preference = $client->create([
    'external_reference' => $reserva_id,
    'items' => [[
        'title' => "Reserva Cabaña {$nombre}",
        'quantity' => 1,
        'unit_price' => (float)$total
    ]],
    'back_urls' => [
        'success' => "{$base_url}/reservas/pago-exitoso",
        'failure' => "{$base_url}/reservas/pago-fallido",
        'pending' => "{$base_url}/reservas/pago-pendiente"
    ],
    'notification_url' => "{$base_url}/reservas/webhook"
]);
?>

<!-- 3. Renderizar interfaz -->
<div class="card">
    <div class="card-header">Completar Pago</div>
    <div class="card-body">
        <!-- Resumen de reserva -->
        <div class="resumen-reserva">
            <!-- Datos de cabaña, fechas, total -->
        </div>
        
        <!-- Wallet Brick de MercadoPago -->
        <div id="wallet_container"></div>
    </div>
</div>

<!-- 4. Inicializar SDK JavaScript -->
<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
const mp = new MercadoPago('<?= MERCADOPAGO_PUBLIC_KEY ?>', {
    locale: 'es-AR'
});

const bricksBuilder = mp.bricks();
await bricksBuilder.create('wallet', 'wallet_container', {
    initialization: {
        preferenceId: '<?= $preference->id ?>'
    },
    customization: {
        texts: {
            action: 'pay',
            valueProp: 'security_safety'
        }
    }
});
</script>
```

**Características de diseño:**
- **Color scheme**: Blanco (#FFFFFF), gris claro (#F8F9FA), bordes (#DEE2E6)
- **Card único**: Sin gradientes ni colores llamativos
- **Tipografía**: Roboto, sans-serif
- **Espaciado**: Padding consistente (1.5rem)
- **Bordes**: 0.5rem border-radius
- **Sombras**: Sutiles box-shadow para profundidad

**Flujo de usuario:**
1. Usuario ve resumen de reserva
2. Click en botón de Wallet Brick
3. Redirección a Checkout Pro de MercadoPago
4. Completa pago con método preferido
5. MercadoPago procesa transacción
6. Callback a URL de éxito/fallo/pendiente

### **Vista: `exito.php`**

**Características actualizadas:**
```php
// Obtener datos desde sesión guardada
$reserva = $_SESSION['reserva_exitosa'];

// Mostrar información completa
- Número de reserva: <?= $reserva['id_reserva'] ?>
- Método de pago: MercadoPago
- Total abonado: $<?= number_format($reserva['total'], 2) ?>
- Huéspedes: <?= $reserva['adultos'] ?> adultos, <?= $reserva['menores'] ?> menores
- Cabaña: <?= $reserva['cabania_nombre'] ?>
- Fechas: <?= $reserva['check_in'] ?> - <?= $reserva['check_out'] ?>

// Email de confirmación enviado automáticamente
✅ Email enviado a: <?= $reserva['email'] ?>
```

### **Consultas SQL Implementadas**

**En ReservasController para emails:**

```php
// Obtener método de pago
private function obtenerMetodoPagoReserva($reservaId) {
    $sql = "SELECT mp.metododepago_descripcion 
            FROM pago p
            INNER JOIN factura f ON p.rela_factura = f.id_factura
            INNER JOIN metododepago mp ON p.rela_metododepago = mp.id_metododepago
            WHERE f.rela_reserva = ?";
}

// Obtener total pagado
private function obtenerTotalPagadoReserva($reservaId) {
    $sql = "SELECT SUM(p.pago_total) as total
            FROM pago p
            INNER JOIN factura f ON p.rela_factura = f.id_factura
            WHERE f.rela_reserva = ?";
}

// Contar huéspedes por edad
private function contarHuespedesReserva($reservaId) {
    $sql = "SELECT 
            COUNT(CASE WHEN h.huesped_edad >= 18 THEN 1 END) as adultos,
            COUNT(CASE WHEN h.huesped_edad < 18 THEN 1 END) as menores
            FROM huesped_reserva hr
            INNER JOIN huesped h ON hr.rela_huesped = h.id_huesped
            WHERE hr.rela_reserva = ?";
}
```

---

## 🔔 **Sistema de Notificaciones en Tiempo Real con Pusher**

### 📋 **Visión General**

Sistema completo de notificaciones push para **huéspedes** implementado con Pusher, proporcionando actualizaciones en tiempo real sobre:
- 🗓️ **Reservas cercanas** - Recordatorios 24h antes del check-in
- 💳 **Pagos pendientes** - Alertas cuando quedan pagos sin completar
- 🛎️ **Pedidos en cabaña** - Confirmación de pedidos solicitados desde totem/menú
- ⚠️ **Inconvenientes de pedido** - Notificaciones cuando hay problemas con consumos

### 🎯 **Audiencia: Solo Huéspedes**

**IMPORTANTE**: Este sistema está diseñado específicamente para usuarios con perfil **"huésped"** (`perfil_nombre = 'huesped'`). Los administradores NO reciben notificaciones.

### 📡 **Arquitectura Frontend de Notificaciones**

#### **Componentes de Interfaz**

**1. Badge de Notificaciones en Menú (`Views/shared/components/menu.php`)**
```html
<!-- Solo visible para huéspedes -->
<?php if (isset($_SESSION['perfil_nombre']) && $_SESSION['perfil_nombre'] === 'huesped'): ?>
    <li class="nav-item dropdown" id="notifications-dropdown">
        <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown">
            <i class="fas fa-bell"></i>
            <!-- Badge con contador de notificaciones no leídas -->
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                  id="notifications-count" style="display: none;">0</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" id="notifications-list">
            <!-- Notificaciones se agregan dinámicamente -->
            <li class="dropdown-item text-center text-muted">No hay notificaciones</li>
        </ul>
    </li>
<?php endif; ?>
```

**Características del Badge:**
- **Posicionamiento**: Esquina superior derecha del icono de campana
- **Visibilidad dinámica**: `display: none` cuando no hay notificaciones
- **Contador en tiempo real**: Actualizado vía JavaScript
- **Estilo Bootstrap**: `badge rounded-pill bg-danger`

**2. Inicialización de Pusher en Header (`Views/shared/layouts/header.php`)**
```php
<!-- Solo para huéspedes autenticados -->
<?php if (isset($_SESSION['perfil_nombre']) && $_SESSION['perfil_nombre'] === 'huesped'): ?>
    <!-- Pusher JS Library -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    
    <script>
        // Configuración de Pusher
        window.pusherConfig = {
            key: '<?= getenv('PUSHER_APP_KEY') ?>',
            cluster: '<?= getenv('PUSHER_APP_CLUSTER') ?>',
            userId: '<?= $_SESSION['id_usuario'] ?>',
            authEndpoint: '<?= url('/pusher/auth') ?>'
        };
    </script>
    
    <!-- Sistema de notificaciones -->
    <script src="<?= asset('js/notifications.js') ?>"></script>
    <link rel="stylesheet" href="<?= asset('css/notifications.css') ?>">
<?php endif; ?>
```

**Variables de Configuración:**
- `pusherConfig.key`: Public Key de Pusher desde `.env`
- `pusherConfig.cluster`: Región del servidor (ej: `us2`)
- `pusherConfig.userId`: ID del usuario actual de sesión
- `pusherConfig.authEndpoint`: Endpoint para autenticar canales privados

### 🔐 **Seguridad y Canales Privados**

#### **Canal Privado por Usuario**
```javascript
// Estructura del nombre de canal
const channelName = `private-user-${userId}`;

// Suscripción con autenticación
const channel = pusher.subscribe(channelName);
```

**Características de seguridad:**
- ✅ **Privacidad**: Cada usuario solo recibe SUS notificaciones
- ✅ **Autenticación obligatoria**: Endpoint `/pusher/auth` valida identidad
- ✅ **Validación de sesión**: Solo usuarios autenticados pueden suscribirse
- ✅ **Aislamiento**: Usuario A nunca ve notificaciones de Usuario B

#### **Endpoint de Autenticación (`/pusher/auth`)**
```php
// PusherController::auth()
public function auth()
{
    // 1. Validar autenticación
    if (!Auth::check()) {
        http_response_code(403);
        echo json_encode(['error' => 'No autenticado']);
        return;
    }
    
    // 2. Validar que es huésped
    if ($_SESSION['perfil_nombre'] !== 'huesped') {
        http_response_code(403);
        echo json_encode(['error' => 'Solo huéspedes']);
        return;
    }
    
    // 3. Extraer datos de solicitud
    $socketId = $_POST['socket_id'];
    $channelName = $_POST['channel_name'];
    
    // 4. Validar formato de canal
    $expectedChannel = "private-user-{$_SESSION['id_usuario']}";
    if ($channelName !== $expectedChannel) {
        http_response_code(403);
        echo json_encode(['error' => 'Canal no autorizado']);
        return;
    }
    
    // 5. Autenticar canal con Pusher
    $pusher = new Pusher(/* config */);
    $auth = $pusher->authorizeChannel($channelName, $socketId);
    
    // 6. Retornar firma de autenticación
    echo $auth;
}
```

### 🎨 **JavaScript: Sistema de Notificaciones (`assets/js/notifications.js`)**

#### **Código Completo Implementado**
```javascript
// Sistema de Notificaciones en Tiempo Real con Pusher
// Para huéspedes del sistema de cabañas

document.addEventListener('DOMContentLoaded', function() {
    // Verificar configuración
    if (!window.pusherConfig) {
        console.warn('Pusher no configurado');
        return;
    }

    const { key, cluster, userId, authEndpoint } = window.pusherConfig;

    // Inicializar Pusher
    const pusher = new Pusher(key, {
        cluster: cluster,
        authEndpoint: authEndpoint,
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            }
        }
    });

    // Suscribirse a canal privado del usuario
    const channelName = `private-user-${userId}`;
    const channel = pusher.subscribe(channelName);

    // Manejar errores de suscripción
    channel.bind('pusher:subscription_error', function(status) {
        console.error('Error al suscribirse:', status);
    });

    // Escuchar evento genérico de notificación
    channel.bind('notification', function(data) {
        mostrarNotificacion(data);
        actualizarContador();
    });

    // Función para mostrar notificación
    function mostrarNotificacion(data) {
        const { titulo, mensaje, tipo, icono } = data;

        // Agregar a dropdown
        const lista = document.getElementById('notifications-list');
        if (lista.children[0]?.textContent.includes('No hay notificaciones')) {
            lista.innerHTML = ''; // Limpiar mensaje vacío
        }

        const item = document.createElement('li');
        item.className = 'dropdown-item notification-item';
        item.innerHTML = `
            <div class="d-flex align-items-start">
                <i class="${icono} me-2 text-${tipo === 'success' ? 'success' : tipo === 'warning' ? 'warning' : 'danger'}"></i>
                <div>
                    <strong>${titulo}</strong>
                    <p class="mb-0 small text-muted">${mensaje}</p>
                </div>
            </div>
        `;
        lista.insertBefore(item, lista.firstChild);

        // Toast notification
        mostrarToast(titulo, mensaje, tipo);
    }

    // Función para mostrar toast
    function mostrarToast(titulo, mensaje, tipo) {
        const bgClass = tipo === 'success' ? 'bg-success' : tipo === 'warning' ? 'bg-warning' : 'bg-danger';
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white ${bgClass} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${titulo}</strong><br>
                    ${mensaje}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(container);
        }

        container.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => toast.remove());
    }

    // Función para actualizar contador
    function actualizarContador() {
        const lista = document.getElementById('notifications-list');
        const contador = document.getElementById('notifications-count');
        const count = lista.querySelectorAll('.notification-item').length;

        if (count > 0) {
            contador.textContent = count;
            contador.style.display = 'inline-block';
        } else {
            contador.style.display = 'none';
        }
    }
});
```

**Funcionalidades implementadas:**
1. **Inicialización automática**: Al cargar página si hay configuración
2. **Suscripción a canal privado**: `private-user-{userId}`
3. **Listener genérico**: Evento `notification` con datos dinámicos
4. **Dropdown de notificaciones**: Actualización en tiempo real
5. **Toast visual**: Notificación emergente con auto-cierre (5 segundos)
6. **Contador de badge**: Actualización automática
7. **Manejo de errores**: Console logs para debugging

### 🎨 **Estilos CSS (`assets/css/notifications.css`)**

```css
/* Sistema de Notificaciones en Tiempo Real */
.notification-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #dee2e6;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item:last-child {
    border-bottom: none;
}

#notifications-dropdown .dropdown-menu {
    max-width: 350px;
    max-height: 400px;
    overflow-y: auto;
}

#notifications-count {
    font-size: 0.7rem;
    padding: 0.25rem 0.4rem;
}

.toast-container {
    z-index: 1050;
}
```

### 📊 **Tipos de Notificaciones Implementadas**

#### **1. Reserva Cercana (`reservaCercana()`)**
```php
// NotificationService::reservaCercana($usuarioId, $reservaData)
$data = [
    'titulo' => '¡Tu reserva es mañana!',
    'mensaje' => "Tu estadía en {$reservaData['cabania_nombre']} comienza el {$reservaData['reserva_fechainicio']}",
    'tipo' => 'success',
    'icono' => 'fas fa-calendar-check'
];
```
**Cuándo se dispara**: 24 horas antes del check-in (job programado)

#### **2. Pago Pendiente (`pagoPendiente()`)**
```php
// NotificationService::pagoPendiente($usuarioId, $montoRestante)
$data = [
    'titulo' => 'Pago pendiente',
    'mensaje' => "Tienes un saldo pendiente de $" . number_format($montoRestante, 2),
    'tipo' => 'warning',
    'icono' => 'fas fa-exclamation-triangle'
];
```
**Cuándo se dispara**: Cuando hay saldo sin pagar en una reserva

#### **3. Pedido en Cabaña (`pedidoCabania()`)**
```php
// NotificationService::pedidoCabania($usuarioId, $consumoData)
$data = [
    'titulo' => 'Pedido confirmado',
    'mensaje' => "Tu pedido de {$consumoData['cantidad']} {$consumoData['item']} fue registrado",
    'tipo' => 'success',
    'icono' => 'fas fa-shopping-cart'
];
```
**Cuándo se dispara**: Al crear consumo desde totem/menú

#### **4. Inconveniente de Pedido (`inconvenientePedido()`)**
```php
// NotificationService::inconvenientePedido($usuarioId, $motivo)
$data = [
    'titulo' => 'Problema con tu pedido',
    'mensaje' => $motivo,
    'tipo' => 'danger',
    'icono' => 'fas fa-times-circle'
];
```
**Cuándo se dispara**: Cuando hay problemas con un consumo (stock, cancelación)

### 🔄 **Flujo Completo de Notificación**

```
1. [BACKEND] Evento ocurre (ej: nuevo consumo)
   ↓
2. [CONTROLLER] Obtiene usuarioId del huésped
   - ReservasController::pagoExitoso() → getUsuarioIdFromReserva()
   - ConsumosController::create() → getUsuarioIdFromReserva()
   ↓
3. [SERVICE] NotificationService dispara notificación
   - Valida $usuarioId
   - Construye payload de datos
   - Trigger a canal privado: private-user-{$usuarioId}
   ↓
4. [PUSHER] Enruta a canal privado
   - Valida autenticación del canal
   - Envía solo al usuario correcto
   ↓
5. [FRONTEND] JavaScript recibe evento
   - notifications.js escucha evento 'notification'
   - Actualiza dropdown de notificaciones
   - Muestra toast visual
   - Actualiza badge contador
   ↓
6. [USUARIO] Ve notificación en tiempo real
```

### 🛠️ **Configuración Requerida**

#### **Variables de Entorno (`.env`)**
```env
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_public_key
PUSHER_APP_SECRET=your_secret_key
PUSHER_APP_CLUSTER=us2
```

#### **Dependencias de Composer**
```json
{
    "require": {
        "pusher/pusher-php-server": "^7.2"
    }
}
```

#### **Archivos Frontend Requeridos**
- ✅ `Views/shared/components/menu.php` - Badge de notificaciones
- ✅ `Views/shared/layouts/header.php` - Inicialización de Pusher
- ✅ `assets/js/notifications.js` - Lógica de manejo de eventos
- ✅ `assets/css/notifications.css` - Estilos visuales

### 🧪 **Testing y Debugging**

#### **Console del Navegador**
```javascript
// Verificar conexión
pusher.connection.bind('connected', () => {
    console.log('✅ Pusher conectado');
});

// Verificar suscripción
channel.bind('pusher:subscription_succeeded', () => {
    console.log('✅ Suscrito a canal privado');
});

// Log de notificaciones recibidas
channel.bind('notification', (data) => {
    console.log('📩 Notificación recibida:', data);
});
```

#### **Dashboard de Pusher**
- **Debug Console**: Ver eventos en tiempo real
- **Connection Stats**: Usuarios conectados actualmente
- **Event History**: Historial de eventos disparados

### 📈 **Estadísticas de Implementación**

**Componentes Frontend:**
- ✅ 1 Badge de notificaciones (menu.php)
- ✅ 1 Script de inicialización (header.php)
- ✅ 1 Archivo JavaScript (notifications.js - ~120 líneas)
- ✅ 1 Archivo CSS (notifications.css - ~30 líneas)
- ✅ 4 Tipos de notificaciones implementadas
- ✅ 1 Endpoint de autenticación (/pusher/auth)

**Integración Completa:**
- ✅ Backend: `NotificationService.php`, `PusherController.php`, `Reserva.php`
- ✅ Frontend: JavaScript listeners, UI components, estilos
- ✅ Seguridad: Canales privados, autenticación obligatoria
- ✅ UX: Toasts, badges, dropdowns, contadores

---

### 1. **Separación Lógica por Audiencia**
- **Público**: Módulos accesibles para huéspedes (home, auth, comentarios, check-in/out)
- **Administrativo**: Módulos internos para staff (catálogos, operaciones, configuración)
- **Reportes**: Analytics y reportes para la gestión
- **Compartido**: Componentes reutilizables en toda la aplicación

### 2. **Lógica de Acceso Diferenciada**
- **`/public/`**: Sin autenticación o con autenticación de huésped
  - `catalogo/`: Catálogo público para consultar cabañas disponibles
  - `reservas/`: **Sistema completo de reservas online con MercadoPago** con proceso paso a paso:
    1. **Confirmación** → Validar datos de cabaña, fechas y huésped
    2. **Servicios** → Seleccionar servicios adicionales (opcional)
    3. **Resumen** → Vista previa de facturación y términos
    4. **Pasarela** → Pago real con MercadoPago Wallet Brick
    5. **Callbacks** → Procesamiento de respuesta de MercadoPago
    6. **Éxito** → Confirmación final con datos completos de pago
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

### **Total**: 31 elementos + **Sistema de Reservas Online** (5 vistas especializadas) + **Sistema de Notificaciones Pusher**

#### Estado de la Implementación:
- ✅ **Estructura de directorios**: Completamente implementada
- ✅ **Separación público/admin/totem**: Funcional
- ✅ **Módulos de configuración**: 13 módulos organizados
- ✅ **Módulos de operaciones**: 9 módulos (incluye huespedes, revisiones, inventarios)
- ✅ **Sistema de reportes**: 7 reportes especializados implementados
- ✅ **Migración de controladores**: COMPLETADA - 32/32 controladores actualizados
- ✅ **Limpieza de código**: Arquitectura optimizada
- ✅ **Sistema multimodal**: 3 módulos de consumos (Admin, Huésped, Totem)
- ✅ **Notificaciones en tiempo real**: Sistema Pusher para huéspedes (4 tipos)

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
13. ✅ **Notificaciones Push en Tiempo Real**: Sistema Pusher para huéspedes (4 tipos de notificaciones)

---

---

## 🎯 **Objetivos Alcanzados**

### **✅ Funcionalidad Completada**
- **Sistema de Reservas Online con MercadoPago**: 6 pasos completamente funcionales
  - Integración real con SDK v3.7.1
  - Wallet Brick para experiencia optimizada
  - Callbacks y webhooks implementados
  - Transacciones garantizadas con rollback
  - Emails con datos completos de pago
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
- **MercadoPago SDK v3.7.1**: Procesamiento real de pagos
  - PHP SDK: MercadoPagoConfig, PreferenceClient, PaymentClient
  - JavaScript SDK: MercadoPago.js con Wallet Brick
  - Checkout Pro: Interfaz optimizada de pago
  - Webhook IPN: Notificaciones asíncronas
- **Pusher SDK v7.2.7**: Notificaciones en tiempo real para huéspedes
  - PHP Server SDK: Pusher\Pusher para triggers
  - JavaScript Client: Pusher.js v8.2.0
  - Canales privados: Seguridad por usuario
  - WebSockets: Comunicación bidireccional

---

## 📊 **Estadísticas Finales**

### **Distribución de Vistas Implementadas**
- **Públicas**: 8 módulos (home, auth, catalogo, comentarios, consumos, ingresos, reservas con 6 vistas, salidas)
- **Totem**: 1 módulo especial con 3 vistas + layout personalizado
- **Administrativas**: 37 módulos distribuidos en:
  - Configuración: 13 módulos
  - Operaciones: 9 módulos
  - Sistema: 3 módulos
  - Seguridad: 2 módulos
  - Reportes: 7 reportes con analytics
- **Compartidas**: 3 categorías (components, errors, layouts con 6 plantillas)
  - **Notificaciones**: Badge en menu.php, inicialización en header.php
- **MercadoPago**: 1 vista de pasarela + 1 vista de debug + 3 callbacks
- **Pusher**: 4 componentes frontend (notifications.js, notifications.css, menu.php, header.php)
- **Total General**: **50 módulos/vistas + 4 componentes Pusher** implementados y funcionales

### **Cobertura por Funcionalidad**
- **🌐 Experiencia Huésped**: 100% completada
- **🏢 Panel Admin**: 100% completada  
- **🔐 Sistema Seguridad**: 100% completada
- **📊 Reportes**: 100% completada
- **📱 Responsive**: 100% completada
- **🔔 Notificaciones en Tiempo Real**: 100% completada (solo huéspedes)

---

## 🔗 **Documentación Relacionada**

- **[README Principal](../README.md)** - Documentación completa del proyecto
- **[Controllers/README.md](../Controllers/README.md)** - Lógica de controladores
- **[Core/README.md](../Core/README.md)** - Framework MVC personalizado  
- **[Models/README.md](../Models/README.md)** - Modelos de datos y relaciones

---

*Estructura actualizada el 18/11/2025 - Casa de Palos Cabañas*
*✅ MIGRACIÓN COMPLETADA - 32 controladores actualizados*
*✅ ARQUITECTURA OPTIMIZADA - Sistema modular completo*
*✅ SISTEMA INTEGRAL - 50 módulos/vistas + 4 componentes Pusher implementados y funcionales*
*✅ MERCADOPAGO INTEGRADO - SDK v3.7.1 con Checkout Pro y Wallet Brick*
*✅ PUSHER INTEGRADO - SDK v7.2.7 con notificaciones en tiempo real para huéspedes*