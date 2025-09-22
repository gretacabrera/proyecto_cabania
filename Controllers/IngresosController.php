<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Ingreso;
use App\Models\Cabania;
use App\Models\EstadoReserva;
use App\Core\Database;
use Exception;

class IngresosController extends Controller
{
    private $ingresoModel;
    private $cabaniaModel;
    private $estadoReservaModel;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->ingresoModel = new Ingreso();
        $this->cabaniaModel = new Cabania();
        $this->estadoReservaModel = new EstadoReserva();
    }

    /**
     * Muestra el listado de reservas para ingreso
     */
    public function index()
    {
        try {
            $usuario_nombre = $_SESSION['usuario_nombre'] ?? '';
            
            if (empty($usuario_nombre)) {
                $this->redirect('/login');
                return;
            }

            $reservas = $this->ingresoModel->getReservasParaIngreso($usuario_nombre);
            
            $this->render('public/ingresos/listado', [
                'titulo' => 'Gestión de Ingresos',
                'reservas' => $reservas,
                'mensaje_sin_datos' => 'No se han encontrado reservas confirmadas para ingreso en este momento.'
            ]);
        } catch (Exception $e) {
            $this->render('public/ingresos/listado', [
                'titulo' => 'Gestión de Ingresos',
                'reservas' => [],
                'error' => 'Error al cargar las reservas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Muestra el formulario de ingreso para una reserva específica
     */
    public function formulario()
    {
        try {
            $usuario_nombre = $_SESSION['usuario_nombre'] ?? '';
            
            if (empty($usuario_nombre)) {
                $this->redirect('/login');
                return;
            }

            $reservas = $this->ingresoModel->getReservasParaIngreso($usuario_nombre);
            
            $this->render('public/ingresos/formulario', [
                'titulo' => 'Registrar Ingreso al Complejo',
                'reservas' => $reservas,
                'mensaje_sin_datos' => 'No se ha encontrado una reserva iniciada en este momento.'
            ]);
        } catch (Exception $e) {
            $this->render('public/ingresos/formulario', [
                'titulo' => 'Registrar Ingreso al Complejo',
                'reservas' => [],
                'error' => 'Error al cargar el formulario: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Procesa el registro de ingreso (alta)
     */
    public function alta()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('public/ingresos/formulario');
            return;
        }

        try {
            $id_reserva = $_POST['id_reserva'] ?? null;
            
            if (empty($id_reserva)) {
                throw new Exception("ID de reserva no proporcionado");
            }

            // Verificar que el usuario puede acceder a esta reserva
            $usuario_nombre = $_SESSION['usuario_nombre'] ?? '';
            if (!$this->ingresoModel->usuarioPuedeAcceder($id_reserva, $usuario_nombre)) {
                throw new Exception("No tiene permisos para acceder a esta reserva");
            }

            $resultado = $this->ingresoModel->registrarIngreso($id_reserva);

            if ($resultado['success']) {
                $_SESSION['mensaje'] = $resultado['mensaje'];
                $_SESSION['tipo_mensaje'] = 'success';
                $this->redirect('public/ingresos/formulario');
            } else {
                throw new Exception("Error al registrar el ingreso");
            }
        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error al registrar ingreso: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'error';
            $this->redirect('public/ingresos/formulario');
        }
    }

    /**
     * Muestra las estadísticas de ingresos
     */
    public function stats()
    {
        try {
            $estadisticas = $this->ingresoModel->getEstadisticasIngresos();
            
            $this->render('public/ingresos/stats', [
                'titulo' => 'Estadísticas de Ingresos',
                'estadisticas' => $estadisticas
            ]);
        } catch (Exception $e) {
            $this->render('public/ingresos/stats', [
                'titulo' => 'Estadísticas de Ingresos',
                'estadisticas' => [],
                'error' => 'Error al cargar estadísticas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Muestra el formulario de búsqueda de ingresos
     */
    public function busqueda()
    {
        try {
            $cabanias = $this->cabaniaModel->getActive();
            $estados = $this->estadoReservaModel->getActive();
            
            $resultados = [];
            
            // Si hay criterios de búsqueda, ejecutar búsqueda
            if (!empty($_GET) && (isset($_GET['fecha_desde']) || isset($_GET['fecha_hasta']) || 
                isset($_GET['cabania']) || isset($_GET['estado']) || isset($_GET['huesped']))) {
                
                $criterios = [
                    'fecha_desde' => $_GET['fecha_desde'] ?? '',
                    'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
                    'cabania' => $_GET['cabania'] ?? 'todas',
                    'estado' => $_GET['estado'] ?? 'todos',
                    'huesped' => $_GET['huesped'] ?? ''
                ];
                
                $resultados = $this->ingresoModel->buscarIngresos($criterios);
            }
            
            $this->render('public/ingresos/busqueda', [
                'titulo' => 'Búsqueda de Ingresos',
                'cabanias' => $cabanias,
                'estados' => $estados,
                'resultados' => $resultados,
                'criterios' => $_GET ?? []
            ]);
        } catch (Exception $e) {
            $this->render('public/ingresos/busqueda', [
                'titulo' => 'Búsqueda de Ingresos',
                'cabanias' => [],
                'estados' => [],
                'resultados' => [],
                'error' => 'Error en la búsqueda: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Muestra el detalle de un ingreso/reserva
     */
    public function detalle()
    {
        try {
            $id_reserva = $_GET['id'] ?? null;
            
            if (empty($id_reserva)) {
                throw new Exception("ID de reserva no proporcionado");
            }

            // Verificar permisos del usuario
            $usuario_nombre = $_SESSION['usuario_nombre'] ?? '';
            if (!empty($usuario_nombre) && !$this->ingresoModel->usuarioPuedeAcceder($id_reserva, $usuario_nombre)) {
                // Si es un usuario normal, solo puede ver sus propias reservas
                // Los administradores pueden ver todas (verificar rol si es necesario)
                throw new Exception("No tiene permisos para ver este ingreso");
            }

            $detalle = $this->ingresoModel->getDetalleIngreso($id_reserva);
            
            if (!$detalle) {
                throw new Exception("Ingreso no encontrado");
            }

            $this->render('public/ingresos/detalle', [
                'titulo' => 'Detalle de Ingreso',
                'detalle' => $detalle
            ]);
        } catch (Exception $e) {
            $this->render('public/ingresos/detalle', [
                'titulo' => 'Detalle de Ingreso',
                'detalle' => null,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * API: Obtiene las reservas disponibles para ingreso (JSON)
     */
    public function getReservasDisponibles()
    {
        header('Content-Type: application/json');
        
        try {
            $usuario_nombre = $_SESSION['usuario_nombre'] ?? '';
            
            if (empty($usuario_nombre)) {
                echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
                return;
            }

            $reservas = $this->ingresoModel->getReservasParaIngreso($usuario_nombre);
            
            echo json_encode([
                'success' => true,
                'data' => $reservas
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Registra un ingreso vía AJAX
     */
    public function registrarIngresoAjax()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $id_reserva = $input['id_reserva'] ?? $_POST['id_reserva'] ?? null;
            
            if (empty($id_reserva)) {
                throw new Exception("ID de reserva no proporcionado");
            }

            $usuario_nombre = $_SESSION['usuario_nombre'] ?? '';
            if (!$this->ingresoModel->usuarioPuedeAcceder($id_reserva, $usuario_nombre)) {
                throw new Exception("No tiene permisos para esta operación");
            }

            $resultado = $this->ingresoModel->registrarIngreso($id_reserva);

            echo json_encode([
                'success' => $resultado['success'],
                'message' => $resultado['mensaje'],
                'data' => $resultado
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
