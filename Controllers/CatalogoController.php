<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Cabania;
use App\Models\Reserva;

/**
 * Controlador para el catálogo público de cabañas
 */
class CatalogoController extends Controller
{
    /**
     * Mostrar catálogo de cabañas disponibles (vista pública)
     */
    public function index()
    {
        $cabaniaModel = new Cabania();
        
        // Obtener filtros de búsqueda
        $filters = [
            'capacidad' => $_GET['capacidad'] ?? '',
            'precio_max' => $_GET['precio_max'] ?? '',
            'busqueda' => $_GET['busqueda'] ?? ''
        ];
        
        // Obtener página actual
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 12; // Mostrar 12 cabañas por página en vista de galería
        
        // Obtener cabañas con paginación
        $result = $cabaniaModel->getPaginated($page, $perPage, $filters);
        $cabanias = $result['data'];
        $pagination = [
            'current_page' => $result['current_page'],
            'total_pages' => $result['total_pages'],
            'total' => $result['total'],
            'per_page' => $result['per_page']
        ];
        
        // Agregar imagen aleatoria a cada cabaña (simulando que tenemos fotos específicas)
        foreach ($cabanias as &$cabania) {
            $cabania['imagen'] = 'foto' . (($cabania['id_cabania'] % 8) + 1) . '.jpg';
        }
        
        // Datos para vista
        $data = [
            'title' => 'Catálogo de Cabañas - Disponibilidad',
            'description' => 'Explora nuestro catálogo de cabañas y encuentra la perfecta para tu escapada. Consulta disponibilidad y precios en tiempo real.',
            'bodyClass' => 'catalog-page',
            'cabanias' => $cabanias,
            'pagination' => $pagination,
            'filters' => $filters,
            'total_results' => $pagination['total']
        ];
        
        return $this->render('public/catalogo/index', $data, 'public');
    }
    
    /**
     * API para verificar disponibilidad de una cabaña en fechas específicas
     */
    public function checkAvailability()
    {
        // Solo responder a peticiones AJAX
        if (!$this->isAjax()) {
            $this->redirect('/catalogo');
            return;
        }
        
        $cabaniaId = (int)($_POST['cabania_id'] ?? 0);
        $fechaInicio = $_POST['fecha_inicio'] ?? '';
        $fechaFin = $_POST['fecha_fin'] ?? '';
        
        if (!$cabaniaId || !$fechaInicio || !$fechaFin) {
            $this->json(['error' => 'Datos incompletos'], 400);
            return;
        }
        
        // Validar fechas
        $inicio = \DateTime::createFromFormat('Y-m-d', $fechaInicio);
        $fin = \DateTime::createFromFormat('Y-m-d', $fechaFin);
        
        if (!$inicio || !$fin || $inicio >= $fin) {
            $this->json(['error' => 'Fechas inválidas'], 400);
            return;
        }
        
        // Verificar que las fechas sean futuras
        $hoy = new \DateTime();
        if ($inicio < $hoy) {
            $this->json(['error' => 'La fecha de inicio debe ser futura'], 400);
            return;
        }
        
        try {
            $cabaniaModel = new Cabania();
            
            // Verificar si la cabaña existe y está activa usando el modelo
            $cabania = $cabaniaModel->find($cabaniaId);
            if (!$cabania || $cabania['cabania_estado'] != 1) {
                $this->json(['error' => 'Cabaña no encontrada'], 404);
                return;
            }
            
            // Verificar disponibilidad usando el modelo
            $disponible = $cabaniaModel->checkAvailability($cabaniaId, $fechaInicio, $fechaFin);
            
            // Calcular precio total
            $dias = $inicio->diff($fin)->days;
            $precioTotal = $dias * $cabania['cabania_precio'];
            
            $this->json([
                'disponible' => $disponible,
                'cabania' => [
                    'id' => $cabania['id_cabania'],
                    'nombre' => $cabania['cabania_nombre'],
                    'precio_por_noche' => $cabania['cabania_precio'],
                    'capacidad' => $cabania['cabania_capacidad']
                ],
                'reserva' => [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'dias' => $dias,
                    'precio_total' => $precioTotal
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("Error en checkAvailability: " . $e->getMessage());
            $this->json(['error' => 'Error interno del servidor'], 500);
            return;
        }
    }
    
    /**
     * API para obtener fechas ocupadas de una cabaña
     */
    public function getOccupiedDates()
    {
        // Headers CORS para permitir peticiones cross-origin
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Content-Type: application/json');
        
        // Manejar peticiones OPTIONS (preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        try {
            $cabaniaId = (int)($_GET['cabania_id'] ?? 0);
            
            if (!$cabaniaId) {
                echo json_encode(['error' => 'ID de cabaña requerido']);
                exit;
            }
            
            // Usar el modelo para obtener las fechas ocupadas
            $cabaniaModel = new Cabania();
            $fechasOcupadas = $cabaniaModel->getOccupiedDates($cabaniaId);
            
            echo json_encode([
                'occupied_dates' => $fechasOcupadas
            ]);
            exit;
            
        } catch (\Exception $e) {
            // Log del error para debugging
            error_log("Error en getOccupiedDates: " . $e->getMessage());
            
            echo json_encode([
                'error' => 'Error obteniendo fechas ocupadas',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
    
    /**
     * Redirigir a reserva (requiere autenticación)
     */
    public function reserve()
    {
        $cabaniaId = (int)($_POST['cabania_id'] ?? 0);
        $fechaInicio = $_POST['fecha_inicio'] ?? '';
        $fechaFin = $_POST['fecha_fin'] ?? '';
        
        if (!$cabaniaId || !$fechaInicio || !$fechaFin) {
            $this->redirect('/catalogo?error=datos_incompletos');
            return;
        }
        
        // Verificar si está autenticado
        if (!Auth::check()) {
            // Guardar datos de reserva en sesión para después del login
            $_SESSION['pending_reservation'] = [
                'cabania_id' => $cabaniaId,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ];
            
            $this->redirect('/auth/login?info=necesita_login');
            return;
        }
        
        // Si está autenticado, redirigir a la vista de confirmación de reserva
        $this->redirect("/reservas/confirmar?cabania_id=$cabaniaId&fecha_inicio=$fechaInicio&fecha_fin=$fechaFin");
    }
}