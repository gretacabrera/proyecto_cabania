<?php

namespace App\Controllers;

use App\Core\Controller;
use Pusher\Pusher;
use Exception;

/**
 * Controlador de autenticación Pusher
 * Maneja la autenticación de canales privados para notificaciones en tiempo real
 */
class PusherController extends Controller
{
    /**
     * Autenticar canal privado de Pusher
     * 
     * Este endpoint es llamado automáticamente por Pusher JS cuando un usuario
     * intenta suscribirse a un canal privado (private-user-{userId})
     */
    public function auth()
    {
        // Verificar que el usuario esté autenticado
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'No autenticado']);
            return;
        }

        // Obtener datos del POST
        $socketId = $_POST['socket_id'] ?? null;
        $channelName = $_POST['channel_name'] ?? null;

        if (!$socketId || !$channelName) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetros inválidos']);
            return;
        }

        // Verificar que el usuario solo pueda suscribirse a su propio canal
        $userId = $_SESSION['usuario_id'];
        $expectedChannel = "private-user-{$userId}";

        if ($channelName !== $expectedChannel) {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado para este canal']);
            return;
        }

        try {
            // Cargar configuración de Pusher
            $config = require __DIR__ . '/../Core/config.php';
            
            $appId = $config['pusher']['app_id'] ?? '';
            $appKey = $config['pusher']['app_key'] ?? '';
            $appSecret = $config['pusher']['app_secret'] ?? '';
            $cluster = $config['pusher']['app_cluster'] ?? 'us2';

            if (empty($appId) || empty($appKey) || empty($appSecret)) {
                throw new Exception('Configuración de Pusher incompleta');
            }

            // Crear instancia de Pusher
            $pusher = new Pusher(
                $appKey,
                $appSecret,
                $appId,
                [
                    'cluster' => $cluster,
                    'useTLS' => true
                ]
            );

            // Generar firma de autenticación
            $auth = $pusher->authorizeChannel($channelName, $socketId);
            
            // authorizeChannel() devuelve una CADENA JSON, no un array
            // No debemos hacer json_encode() de nuevo
            header('Content-Type: application/json');
            
            if (is_string($auth)) {
                // Ya es JSON, enviarlo directamente
                echo $auth;
            } else {
                // Si por alguna razón no es string, convertir a JSON
                $authJson = json_encode($auth);
                echo $authJson;
            }

        } catch (Exception $e) {
            error_log("Error autenticando canal Pusher: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error de autenticación']);
        }
    }
}
