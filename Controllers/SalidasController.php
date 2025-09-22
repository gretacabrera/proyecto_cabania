<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Salida;
use App\Models\Cabania;
use App\Models\EstadoReserva;

class SalidasController extends Controller
{
    protected $salidaModel;
    protected $cabaniaModel;
    protected $estadoReservaModel;

    public function __construct()
    {
        parent::__construct();
        $this->salidaModel = new Salida();
        $this->cabaniaModel = new Cabania();
        $this->estadoReservaModel = new EstadoReserva();
    }

    /**
     * Página principal - listado de salidas recientes
     */
    public function index()
    {
        try {
            // Verificar autenticación
            if (!$this->isAuthenticated()) {
                $this->redirect('/auth/login');
                return;
            }

            // Obtener salidas recientes (últimos 30 días)
            $fechaDesde = date('Y-m-d', strtotime('-30 days'));
            
            $salidas = $this->salidaModel->buscarSalidas([
                'fecha_desde' => $fechaDesde
            ]);

            // Obtener estadísticas básicas
            $estadisticas = $this->salidaModel->getEstadisticasSalidas();

            $this->render('public/salidas/listado', [
                'title' => 'Gestión de Salidas',
                'salidas' => $salidas,
                'estadisticas' => $estadisticas,
                'success_message' => $this->getFlashMessage('success'),
                'error_message' => $this->getFlashMessage('error')
            ]);

        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Error al cargar las salidas: ' . $e->getMessage());
            $this->render('public/salidas/listado', [
                'title' => 'Gestión de Salidas',
                'salidas' => [],
                'estadisticas' => [],
                'error_message' => $this->getFlashMessage('error')
            ]);
        }
    }

    /**
     * Formulario para registrar salida
     */
    public function formulario()
    {
        try {
            // Verificar autenticación
            if (!$this->isAuthenticated()) {
                $this->redirect('/auth/login');
                return;
            }

            $usuarioId = $_SESSION['usuario_id'] ?? null;
            
            // Obtener reservas que pueden hacer checkout
            $reservas = $this->salidaModel->getReservasParaSalida($usuarioId);

            $this->render('public/salidas/formulario', [
                'title' => 'Registrar Salida',
                'reservas' => $reservas,
                'success_message' => $this->getFlashMessage('success'),
                'error_message' => $this->getFlashMessage('error')
            ]);

        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Error al cargar el formulario: ' . $e->getMessage());
            $this->render('public/salidas/formulario', [
                'title' => 'Registrar Salida',
                'reservas' => [],
                'error_message' => $this->getFlashMessage('error')
            ]);
        }
    }

    /**
     * Procesar el registro de salida
     */
    public function registrar()
    {
        try {
            // Verificar autenticación
            if (!$this->isAuthenticated()) {
                $this->jsonResponse(['success' => false, 'message' => 'No autorizado']);
                return;
            }

            // Validar datos requeridos
            if (!isset($_POST['id_reserva']) || empty($_POST['id_reserva'])) {
                $this->setFlashMessage('error', 'ID de reserva requerido');
                $this->redirect('/public/salidas/formulario');
                return;
            }

            $idReserva = intval($_POST['id_reserva']);
            $usuarioId = $_SESSION['usuario_id'] ?? null;

            // Verificar que el usuario puede acceder a esta reserva
            if ($usuarioId && !$this->salidaModel->usuarioPuedeAcceder($idReserva, $usuarioId)) {
                $this->setFlashMessage('error', 'No tiene permisos para acceder a esta reserva');
                $this->redirect('/public/salidas/formulario');
                return;
            }

            // Registrar la salida
            $resultado = $this->salidaModel->registrarSalida($idReserva, $usuarioId);

            if ($resultado['success']) {
                $this->setFlashMessage('success', $resultado['message']);
                
                // Redirigir a comentarios si la salida fue exitosa
                $this->redirect("/comentarios/formulario?id_reserva={$idReserva}");
            } else {
                $this->setFlashMessage('error', $resultado['message']);
                $this->redirect('/public/salidas/formulario');
            }

        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Error al registrar salida: ' . $e->getMessage());
            $this->redirect('/public/salidas/formulario');
        }
    }

    /**
     * Ver detalle de una salida/reserva finalizada
     */
    public function detalle($id)
    {
        try {
            // Verificar autenticación
            if (!$this->isAuthenticated()) {
                $this->redirect('/auth/login');
                return;
            }

            $idReserva = intval($id);
            $usuarioId = $_SESSION['usuario_id'] ?? null;

            // Verificar permisos si es necesario
            if ($usuarioId && !$this->salidaModel->usuarioPuedeAcceder($idReserva, $usuarioId)) {
                $this->setFlashMessage('error', 'No tiene permisos para ver esta reserva');
                $this->redirect('/salidas');
                return;
            }

            // Obtener detalle completo
            $detalle = $this->salidaModel->getDetalleReserva($idReserva);

            if (!$detalle) {
                $this->setFlashMessage('error', 'Reserva no encontrada');
                $this->redirect('/salidas');
                return;
            }

            $this->render('public/salidas/detalle', [
                'title' => 'Detalle de Salida',
                'reserva' => $detalle,
                'success_message' => $this->getFlashMessage('success'),
                'error_message' => $this->getFlashMessage('error')
            ]);

        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Error al cargar el detalle: ' . $e->getMessage());
            $this->redirect('/salidas');
        }
    }

    /**
     * Página de búsqueda de salidas
     */
    public function busqueda()
    {
        try {
            // Verificar autenticación
            if (!$this->isAuthenticated()) {
                $this->redirect('/auth/login');
                return;
            }

            // Obtener filtros de la consulta
            $filtros = [
                'estado' => $_GET['estado'] ?? '',
                'fecha_desde' => $_GET['fecha_desde'] ?? '',
                'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
                'cabania' => $_GET['cabania'] ?? '',
                'huesped' => $_GET['huesped'] ?? ''
            ];

            $salidas = [];
            $realizarBusqueda = false;

            // Solo buscar si hay al menos un filtro
            foreach ($filtros as $filtro) {
                if (!empty($filtro)) {
                    $realizarBusqueda = true;
                    break;
                }
            }

            if ($realizarBusqueda) {
                $salidas = $this->salidaModel->buscarSalidas($filtros);
            }

            // Obtener listas para los selectores
            $cabanias = $this->cabaniaModel->getActive();
            $estados = $this->estadoReservaModel->getActive();

            $this->render('public/salidas/busqueda', [
                'title' => 'Búsqueda de Salidas',
                'salidas' => $salidas,
                'filtros' => $filtros,
                'cabanias' => $cabanias,
                'estados' => $estados,
                'realizar_busqueda' => $realizarBusqueda,
                'success_message' => $this->getFlashMessage('success'),
                'error_message' => $this->getFlashMessage('error')
            ]);

        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Error en la búsqueda: ' . $e->getMessage());
            $this->render('public/salidas/busqueda', [
                'title' => 'Búsqueda de Salidas',
                'salidas' => [],
                'filtros' => [],
                'cabanias' => [],
                'estados' => [],
                'realizar_busqueda' => false,
                'error_message' => $this->getFlashMessage('error')
            ]);
        }
    }

    /**
     * Realizar búsqueda (AJAX)
     */
    public function buscar()
    {
        try {
            // Verificar autenticación
            if (!$this->isAuthenticated()) {
                $this->jsonResponse(['success' => false, 'message' => 'No autorizado']);
                return;
            }

            $filtros = [
                'estado' => $_POST['estado'] ?? '',
                'fecha_desde' => $_POST['fecha_desde'] ?? '',
                'fecha_hasta' => $_POST['fecha_hasta'] ?? '',
                'cabania' => $_POST['cabania'] ?? '',
                'huesped' => $_POST['huesped'] ?? ''
            ];

            $salidas = $this->salidaModel->buscarSalidas($filtros);

            $this->jsonResponse([
                'success' => true,
                'data' => $salidas,
                'total' => count($salidas)
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Página de estadísticas
     */
    public function stats()
    {
        try {
            // Verificar autenticación
            if (!$this->isAuthenticated()) {
                $this->redirect('/auth/login');
                return;
            }

            // Obtener estadísticas completas
            $estadisticas = $this->salidaModel->getEstadisticasSalidas();

            // Obtener salidas recientes para la tabla
            $salidasRecientes = $this->salidaModel->buscarSalidas([
                'fecha_desde' => date('Y-m-d', strtotime('-7 days'))
            ]);

            $this->render('public/salidas/stats', [
                'title' => 'Estadísticas de Salidas',
                'estadisticas' => $estadisticas,
                'salidas_recientes' => array_slice($salidasRecientes, 0, 10), // Solo las últimas 10
                'success_message' => $this->getFlashMessage('success'),
                'error_message' => $this->getFlashMessage('error')
            ]);

        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Error al cargar estadísticas: ' . $e->getMessage());
            $this->render('public/salidas/stats', [
                'title' => 'Estadísticas de Salidas',
                'estadisticas' => [],
                'salidas_recientes' => [],
                'error_message' => $this->getFlashMessage('error')
            ]);
        }
    }

    /**
     * Obtener reservas para salida (AJAX)
     */
    public function getReservasParaSalida()
    {
        try {
            // Verificar autenticación
            if (!$this->isAuthenticated()) {
                $this->jsonResponse(['success' => false, 'message' => 'No autorizado']);
                return;
            }

            $usuarioId = $_SESSION['usuario_id'] ?? null;
            $reservas = $this->salidaModel->getReservasParaSalida($usuarioId);

            $this->jsonResponse([
                'success' => true,
                'data' => $reservas
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener reservas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Calcular estado de pagos de una reserva (AJAX)
     */
    public function calcularPagos($id)
    {
        try {
            // Verificar autenticación
            if (!$this->isAuthenticated()) {
                $this->jsonResponse(['success' => false, 'message' => 'No autorizado']);
                return;
            }

            $idReserva = intval($id);
            $estadoPagos = $this->salidaModel->calcularEstadoPagos($idReserva);

            if ($estadoPagos) {
                $this->jsonResponse([
                    'success' => true,
                    'data' => $estadoPagos
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'No se pudo calcular el estado de pagos'
                ]);
            }

        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al calcular pagos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Verificar si el usuario está autenticado
     */
    private function isAuthenticated()
    {
        return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
    }

    /**
     * Redireccionar con mensaje flash
     */
    /**
     * Establecer mensaje flash
     */
    private function setFlashMessage($type, $message)
    {
        $_SESSION["flash_{$type}"] = $message;
    }

    /**
     * Obtener mensaje flash
     */
    private function getFlashMessage($type)
    {
        if (isset($_SESSION["flash_{$type}"])) {
            $message = $_SESSION["flash_{$type}"];
            unset($_SESSION["flash_{$type}"]);
            return $message;
        }
        return null;
    }

    /**
     * Respuesta JSON
     */
    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
