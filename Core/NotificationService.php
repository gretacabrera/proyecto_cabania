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
                error_log("Error inicializando Pusher: " . $e->getMessage());
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

        $data = [
            'type' => 'reserva_cercana',
            'title' => '¡Tu Reserva se Acerca!',
            'message' => "Tu check-in en {$reserva['cabania_nombre']} es en {$diasRestantes} día(s)",
            'icon' => 'fa-calendar-check',
            'color' => 'info',
            'data' => [
                'reserva_id' => $reserva['id_reserva'],
                'cabania' => $reserva['cabania_nombre'] ?? 'N/A',
                'fecha_inicio' => $reserva['reserva_fecha_inicio'],
                'fecha_fin' => $reserva['reserva_fecha_fin'] ?? '',
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
            'title' => 'Pago Pendiente de Confirmación',
            'message' => "Tu pago de $" . number_format($montoPendiente, 2) . " está siendo procesado",
            'icon' => 'fa-clock',
            'color' => 'warning',
            'data' => [
                'reserva_id' => $reserva['id_reserva'],
                'cabania' => $reserva['cabania_nombre'] ?? 'N/A',
                'monto_pendiente' => $montoPendiente,
                'monto_total' => $reserva['reserva_monto_total'] ?? 0,
                'fecha_inicio' => $reserva['reserva_fecha_inicio']
            ],
            'url' => '/reservas',
            'timestamp' => date('Y-m-d H:i:s'),
            'priority' => 'high'
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

        $items = isset($consumo['items']) ? count($consumo['items']) : 1;
        $total = $consumo['consumo_monto_total'] ?? 0;

        $data = [
            'type' => 'pedido_cabania',
            'title' => '¡Pedido Confirmado!',
            'message' => "Tu pedido de {$items} producto(s)/servicio(s) fue registrado - $" . number_format($total, 2),
            'icon' => 'fa-check-circle',
            'color' => 'success',
            'data' => [
                'consumo_id' => $consumo['id_consumo'] ?? null,
                'reserva_id' => $reserva['id_reserva'],
                'cabania' => $reserva['cabania_nombre'] ?? 'N/A',
                'items_count' => $items,
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
            error_log("Pusher deshabilitado - No se envió notificación: {$event}");
            return false;
        }

        try {
            $result = $this->pusher->trigger($channel, $event, $data);
            error_log("Notificación Pusher enviada: {$event} - " . json_encode($data));
            return $result;
        } catch (\Exception $e) {
            error_log("Error enviando notificación Pusher: " . $e->getMessage());
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
