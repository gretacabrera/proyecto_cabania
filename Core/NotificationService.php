<?php

namespace App\Core;

use Pusher\Pusher;

/**
 * Servicio de notificaciones en tiempo real con Pusher
 */
class NotificationService
{
    private $pusher;
    private $enabled;

    public function __construct()
    {
        $config = require __DIR__ . '/config.php';
        $pusherConfig = $config['pusher'];

        $this->enabled = !empty($pusherConfig['app_id']) && 
                        !empty($pusherConfig['app_key']) && 
                        !empty($pusherConfig['app_secret']);

        if ($this->enabled) {
            try {
                $this->pusher = new Pusher(
                    $pusherConfig['app_key'],
                    $pusherConfig['app_secret'],
                    $pusherConfig['app_id'],
                    [
                        'cluster' => $pusherConfig['app_cluster'],
                        'useTLS' => $pusherConfig['use_tls']
                    ]
                );
            } catch (\Exception $e) {
                error_log("ERROR inicializando Pusher: " . $e->getMessage());
                $this->enabled = false;
            }
        }
    }

    /**
     * Enviar notificación de reserva cercana al huésped
     * 
     * @param array $reserva Datos de la reserva
     * @param int $diasRestantes Días hasta el check-in
     * @param int $usuarioId ID del usuario huésped
     */
    public function notifyReservaCercana($reserva, $diasRestantes, $usuarioId = null)
    {
        if (!$this->enabled) return;

        $cabaniaNombre = $reserva['cabania_nombre'] ?? 'la cabaña';
        
        $data = [
            'type' => 'reserva_cercana',
            'title' => '¡Tu Reserva se Acerca!',
            'message' => "Tu check-in en {$cabaniaNombre} es en {$diasRestantes} día(s)",
            'icon' => 'fa-calendar-check',
            'color' => 'info',
            'data' => [
                'reserva_id' => $reserva['id_reserva'],
                'cabania' => $cabaniaNombre,
                'fecha_inicio' => $reserva['reserva_fhinicio'] ?? '',
                'fecha_fin' => $reserva['reserva_fhfin'] ?? '',
                'dias_restantes' => $diasRestantes
            ],
            'url' => '/reservas',
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Enviar al canal privado del usuario
        $channel = $usuarioId ? "private-user-{$usuarioId}" : 'guest-notifications';
        
        return $this->send($channel, 'reserva-cercana', $data);
    }

    /**
     * Enviar notificación de pago pendiente al huésped
     * 
     * @param array $reserva Datos de la reserva
     * @param float $montoPendiente Monto pendiente de pago
     * @param int $usuarioId ID del usuario huésped
     */
    public function notifyPagoPendiente($reserva, $montoPendiente, $usuarioId = null)
    {
        if (!$this->enabled) return;

        $data = [
            'type' => 'pago_pendiente',
            'title' => '⚠️ PAGO URGENTE REQUERIDO',
            'message' => "¡Completa tu pago de $" . number_format($montoPendiente, 2) . " para confirmar tu reserva!",
            'icon' => 'fa-exclamation-triangle',
            'color' => 'danger',
            'data' => [
                'reserva_id' => $reserva['id_reserva'],
                'cabania' => $reserva['cabania_nombre'] ?? 'N/A',
                'monto_pendiente' => $montoPendiente,
                'fecha_inicio' => $reserva['reserva_fhinicio']
            ],
            'url' => '/reservas',
            'timestamp' => date('Y-m-d H:i:s'),
            'priority' => 'urgent',
            'sound' => true
        ];

        // Enviar al canal privado del usuario
        $channel = $usuarioId ? "private-user-{$usuarioId}" : 'guest-notifications';
        return $this->send($channel, 'pago-pendiente', $data);
    }

    /**
     * Enviar notificación de pedido confirmado al huésped
     * 
     * @param array $consumo Datos del consumo/pedido
     * @param array $reserva Datos de la reserva
     * @param int $usuarioId ID del usuario huésped
     */
    public function notifyPedidoCabania($consumo, $reserva, $usuarioId = null)
    {
        if (!$this->enabled) return;

        // Calcular cantidad total sumando todas las cantidades de items
        $cantidadTotal = 0;
        if (isset($consumo['items']) && is_array($consumo['items'])) {
            foreach ($consumo['items'] as $item) {
                $cantidadTotal += floatval($item['consumo_cantidad'] ?? 1);
            }
        } else {
            $cantidadTotal = 1;
        }
        
        $total = $consumo['consumo_monto_total'] ?? 0;

        $data = [
            'type' => 'pedido_cabania',
            'title' => '¡Pedido Registrado!',
            'message' => "Tu pedido de {$cantidadTotal} producto(s)/servicio(s) fue registrado - $" . number_format($total, 2),
            'icon' => 'fa-receipt',
            'color' => 'info',
            'data' => [
                'consumo_id' => $consumo['id_consumo'] ?? null,
                'reserva_id' => $reserva['id_reserva'],
                'cabania' => $reserva['cabania_nombre'] ?? 'N/A',
                'items_count' => $cantidadTotal,
                'monto_total' => $total,
                'fecha_pedido' => $consumo['consumo_fecha'] ?? date('Y-m-d H:i:s')
            ],
            'url' => '/reservas/' . $reserva['id_reserva'] . '/consumos',
            'timestamp' => date('Y-m-d H:i:s'),
            'priority' => 'high',
            'sound' => true
        ];

        // Enviar al canal privado del usuario
        $channel = $usuarioId ? "private-user-{$usuarioId}" : 'guest-notifications';
        return $this->send($channel, 'pedido-cabania', $data);
    }

    /**
     * Enviar notificación de pedido confirmado (en proceso) al huésped
     * 
     * @param array $consumo Datos del consumo
     * @param array $reserva Datos de la reserva
     * @param int $usuarioId ID del usuario huésped
     */
    public function notifyPedidoConfirmado($consumo, $reserva, $usuarioId = null)
    {
        if (!$this->enabled) return;

        $productoServicio = $consumo['producto_nombre'] ?? $consumo['servicio_nombre'] ?? 'tu pedido';
        $cantidad = floatval($consumo['consumo_cantidad'] ?? 1);

        $data = [
            'type' => 'pedido_confirmado',
            'title' => '✅ Pedido Confirmado',
            'message' => "Tu pedido de {$productoServicio} está en proceso de preparación",
            'icon' => 'fa-check-circle',
            'color' => 'success',
            'data' => [
                'consumo_id' => $consumo['id_consumo'],
                'reserva_id' => $reserva['id_reserva'],
                'cabania' => $reserva['cabania_nombre'] ?? 'N/A',
                'producto_servicio' => $productoServicio,
                'cantidad' => $cantidad,
                'fecha_confirmacion' => date('Y-m-d H:i:s')
            ],
            'url' => '/reservas/' . $reserva['id_reserva'] . '/consumos',
            'timestamp' => date('Y-m-d H:i:s'),
            'priority' => 'normal',
            'sound' => true
        ];

        // Enviar al canal privado del usuario
        $channel = $usuarioId ? "private-user-{$usuarioId}" : 'guest-notifications';
        return $this->send($channel, 'pedido-confirmado', $data);
    }

    /**
     * Enviar notificación de pedido entregado al huésped
     * 
     * @param array $consumo Datos del consumo
     * @param array $reserva Datos de la reserva
     * @param int $usuarioId ID del usuario huésped
     */
    public function notifyPedidoEntregado($consumo, $reserva, $usuarioId = null)
    {
        if (!$this->enabled) return;

        $productoServicio = $consumo['producto_nombre'] ?? $consumo['servicio_nombre'] ?? 'tu pedido';
        $cantidad = floatval($consumo['consumo_cantidad'] ?? 1);

        $data = [
            'type' => 'pedido_entregado',
            'title' => '🎉 ¡Pedido Entregado!',
            'message' => "Tu pedido de {$productoServicio} ha sido entregado. ¡Disfrútalo!",
            'icon' => 'fa-check-double',
            'color' => 'success',
            'data' => [
                'consumo_id' => $consumo['id_consumo'],
                'reserva_id' => $reserva['id_reserva'],
                'cabania' => $reserva['cabania_nombre'] ?? 'N/A',
                'producto_servicio' => $productoServicio,
                'cantidad' => $cantidad,
                'fecha_entrega' => date('Y-m-d H:i:s')
            ],
            'url' => '/reservas/' . $reserva['id_reserva'] . '/consumos',
            'timestamp' => date('Y-m-d H:i:s'),
            'priority' => 'normal',
            'sound' => true
        ];

        // Enviar al canal privado del usuario
        $channel = $usuarioId ? "private-user-{$usuarioId}" : 'guest-notifications';
        return $this->send($channel, 'pedido-entregado', $data);
    }

    /**
     * Enviar notificación de inconveniente con pedido al huésped
     * 
     * @param array $consumo Datos del consumo
     * @param string $tipoInconveniente Tipo de inconveniente
     * @param string $descripcion Descripción del inconveniente
     * @param int $usuarioId ID del usuario huésped
     */
    public function notifyInconvenientePedido($consumo, $tipoInconveniente, $descripcion = '', $usuarioId = null)
    {
        if (!$this->enabled) return;

        $data = [
            'type' => 'inconveniente_pedido',
            'title' => 'Actualización de tu Pedido',
            'message' => "Hay un inconveniente con tu pedido: {$tipoInconveniente}",
            'icon' => 'fa-info-circle',
            'color' => 'warning',
            'data' => [
                'consumo_id' => $consumo['id_consumo'],
                'reserva_id' => $consumo['rela_reserva'] ?? null,
                'tipo_inconveniente' => $tipoInconveniente,
                'descripcion' => $descripcion,
                'producto_servicio' => $consumo['producto_nombre'] ?? $consumo['servicio_nombre'] ?? 'Producto/Servicio'
            ],
            'url' => '/reservas/' . ($consumo['rela_reserva'] ?? '') . '/consumos',
            'timestamp' => date('Y-m-d H:i:s'),
            'priority' => 'urgent',
            'sound' => true
        ];

        // Enviar al canal privado del usuario
        $channel = $usuarioId ? "private-user-{$usuarioId}" : 'guest-notifications';
        return $this->send($channel, 'inconveniente-pedido', $data);
    }

    /**
     * Enviar notificación genérica
     * 
     * @param string $channel Canal de Pusher
     * @param string $event Evento
     * @param array $data Datos de la notificación
     */
    private function send($channel, $event, $data)
    {
        if (!$this->enabled) {
            error_log("⚠️ NotificationService deshabilitado - No se envió notificación");
            return false;
        }

        try {
            error_log("📤 Enviando notificación Pusher - Canal: $channel, Evento: $event");
            $result = $this->pusher->trigger($channel, $event, $data);
            error_log("✅ Notificación Pusher enviada exitosamente - Resultado: " . json_encode($result));
            return $result;
        } catch (\Exception $e) {
            error_log("❌ Error enviando notificación Pusher: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Verificar si el servicio está habilitado
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
}
