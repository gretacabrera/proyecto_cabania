<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Reserva;
use App\Models\Cabania;
use App\Models\Factura;
use App\Models\Persona;

/**
 * Controlador principal para la página de inicio
 */
class HomeController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Casa de Palos - Cabañas',
            'user' => null,
            'userProfile' => null,
            'showReservaButton' => false
        ];

        if (Auth::check()) {
            $data['user'] = Auth::user();
            $data['userProfile'] = Auth::getUserProfile();
            
            // Cargar datos específicos según el perfil del usuario
            switch ($data['userProfile']) {
                case 'huesped':
                    $data = array_merge($data, $this->getHuespedData());
                    break;
                case 'administrador':
                    $data = array_merge($data, $this->getAdministradorData());
                    break;
                case 'cajero':
                    $data = array_merge($data, $this->getCajeroData());
                    break;
                case 'recepcionista':
                    $data = array_merge($data, $this->getRecepcionistaData());
                    break;
            }
        }

        return $this->render('public/home', $data, 'main');
    }
    
    /**
     * Obtener datos para el dashboard del huésped
     */
    private function getHuespedData()
    {
        $reservaModel = new Reserva();
        $personaModel = new Persona();
        
        // Buscar datos de la persona por usuario
        $persona = $personaModel->findByUsuario(Auth::user());
        
        $data = [
            'showReservaButton' => true,
            'persona' => $persona,
            'reservas_proximas' => [],
            'reservas_historial' => []
        ];
        
        if ($persona) {
            // Obtener reservas próximas del huésped
            $data['reservas_proximas'] = $this->getReservasProximasHuesped($persona['id_persona']);
            $data['reservas_historial'] = $this->getHistorialReservasHuesped($persona['id_persona']);
        }
        
        return $data;
    }
    
    /**
     * Obtener datos para el dashboard del administrador
     */
    private function getAdministradorData()
    {
        $reservaModel = new Reserva();
        $cabaniaModel = new Cabania();
        $facturaModel = new Factura();
        
        return [
            'kpis' => [
                'total_cabañas' => $this->getTotalCabanias(),
                'ocupacion_actual' => $this->getOcupacionActual(),
                'ingresos_mes' => $this->getIngresosMes(),
                'reservas_activas' => $this->getReservasActivas(),
                'huespedes_mes' => $this->getHuespedesMes()
            ],
            'estadisticas_mensuales' => $this->getEstadisticasMensuales(),
            'reservas_recientes' => $this->getReservasRecientes(5)
        ];
    }
    
    /**
     * Obtener datos para el dashboard del cajero
     */
    private function getCajeroData()
    {
        return [
            'facturacion' => [
                'facturas_hoy' => $this->getFacturasHoy(),
                'ingresos_hoy' => $this->getIngresosHoy(),
                'facturas_mes' => $this->getFacturasMes(),
                'ingresos_mes' => $this->getIngresosMes(),
                'metodos_pago' => $this->getMetodosPagoMes()
            ],
            'facturas_recientes' => $this->getFacturasRecientes(10),
            'reservas_pendientes_pago' => $this->getReservasPendientesPago()
        ];
    }
    
    /**
     * Obtener datos para el dashboard del recepcionista
     */
    private function getRecepcionistaData()
    {
        $cabaniaModel = new Cabania();
        
        return [
            'cabanias_estado' => [
                'disponibles' => $cabaniaModel->getAvailable(),
                'ocupadas' => $cabaniaModel->getOccupied(),
                'total' => $cabaniaModel->getActive()
            ],
            'reservas_hoy' => $this->getReservasHoy(),
            'checkins_hoy' => $this->getCheckinsHoy(),
            'checkouts_hoy' => $this->getCheckoutsHoy(),
            'reservas_proximas' => $this->getReservasProximas(7)
        ];
    }
    
    // Métodos auxiliares para obtener datos específicos
    
    private function getReservasProximasHuesped($personaId)
    {
        $db = \App\Core\Database::getInstance();
        $sql = "SELECT r.*, c.cabania_nombre, c.cabania_codigo, er.estadoreserva_descripcion
                FROM reserva r
                LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                WHERE h.rela_persona = ? 
                AND r.reserva_fhinicio >= CURDATE()
                AND r.rela_estadoreserva IN (1, 2)
                ORDER BY r.reserva_fhinicio ASC
                LIMIT 3";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $personaId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        return $reservas;
    }
    
    private function getHistorialReservasHuesped($personaId)
    {
        $db = \App\Core\Database::getInstance();
        $sql = "SELECT r.*, c.cabania_nombre, c.cabania_codigo, er.estadoreserva_descripcion
                FROM reserva r
                LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                WHERE h.rela_persona = ? 
                AND r.reserva_fhfin < CURDATE()
                ORDER BY r.reserva_fhinicio DESC
                LIMIT 5";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $personaId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        return $reservas;
    }
    
    private function getTotalCabanias()
    {
        $db = \App\Core\Database::getInstance();
        $result = $db->query("SELECT COUNT(*) as total FROM cabania WHERE cabania_estado IN (1, 2)");
        return $result->fetch_assoc()['total'];
    }
    
    private function getOcupacionActual()
    {
        $db = \App\Core\Database::getInstance();
        $result = $db->query("SELECT COUNT(*) as ocupadas FROM cabania WHERE cabania_estado = 2");
        $ocupadas = $result->fetch_assoc()['ocupadas'];
        $total = $this->getTotalCabanias();
        return $total > 0 ? round(($ocupadas / $total) * 100, 1) : 0;
    }
    
    private function getIngresosMes()
    {
        $db = \App\Core\Database::getInstance();
        $inicioMes = date('Y-m-01');
        $finMes = date('Y-m-t');
        $stmt = $db->prepare("SELECT COALESCE(SUM(factura_total), 0) as total 
                               FROM factura 
                               WHERE DATE(factura_fechahora) BETWEEN ? AND ?");
        $stmt->bind_param("ss", $inicioMes, $finMes);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'];
    }
    
    private function getReservasActivas()
    {
        $db = \App\Core\Database::getInstance();
        $result = $db->query("SELECT COUNT(*) as total 
                               FROM reserva 
                               WHERE rela_estadoreserva IN (1, 2, 3) 
                               AND reserva_fhfin >= CURDATE()");
        return $result->fetch_assoc()['total'];
    }
    
    private function getHuespedesMes()
    {
        $db = \App\Core\Database::getInstance();
        $inicioMes = date('Y-m-01');
        $finMes = date('Y-m-t');
        $stmt = $db->prepare("SELECT COUNT(DISTINCT h.id_huesped) as total
                               FROM huesped h
                               LEFT JOIN huesped_reserva hr ON h.id_huesped = hr.rela_huesped
                               LEFT JOIN reserva r ON hr.rela_reserva = r.id_reserva
                               WHERE DATE(r.reserva_fhinicio) BETWEEN ? AND ?");
        $stmt->bind_param("ss", $inicioMes, $finMes);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'];
    }
    
    private function getEstadisticasMensuales()
    {
        $db = \App\Core\Database::getInstance();
        $meses = [];
        for ($i = 0; $i < 6; $i++) {
            $fecha = date('Y-m-01', strtotime("-$i months"));
            $inicioMes = $fecha;
            $finMes = date('Y-m-t', strtotime($fecha));
            
            $stmt = $db->prepare("SELECT 
                                       COUNT(*) as reservas,
                                       COALESCE(SUM(f.factura_total), 0) as ingresos
                                   FROM reserva r
                                   LEFT JOIN factura f ON r.id_reserva = f.rela_reserva
                                   WHERE DATE(r.reserva_fhinicio) BETWEEN ? AND ?");
            $stmt->bind_param("ss", $inicioMes, $finMes);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $datos = $result->fetch_assoc();
            $meses[] = [
                'mes' => date('M Y', strtotime($fecha)),
                'reservas' => $datos['reservas'],
                'ingresos' => $datos['ingresos']
            ];
        }
        
        return array_reverse($meses);
    }
    
    private function getReservasRecientes($limite)
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT r.*, c.cabania_nombre, p.persona_nombre, p.persona_apellido, er.estadoreserva_descripcion
                               FROM reserva r
                               LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                               LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                               LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                               LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                               LEFT JOIN persona p ON h.rela_persona = p.id_persona
                               ORDER BY r.id_reserva DESC
                               LIMIT ?");
        $stmt->bind_param("i", $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        return $reservas;
    }
    
    private function getFacturasHoy()
    {
        $db = \App\Core\Database::getInstance();
        $hoy = date('Y-m-d');
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM factura WHERE DATE(factura_fechahora) = ?");
        $stmt->bind_param("s", $hoy);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'];
    }
    
    private function getIngresosHoy()
    {
        $db = \App\Core\Database::getInstance();
        $hoy = date('Y-m-d');
        $stmt = $db->prepare("SELECT COALESCE(SUM(factura_total), 0) as total FROM factura WHERE DATE(factura_fechahora) = ?");
        $stmt->bind_param("s", $hoy);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'];
    }
    
    private function getFacturasMes()
    {
        $db = \App\Core\Database::getInstance();
        $inicioMes = date('Y-m-01');
        $finMes = date('Y-m-t');
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM factura WHERE DATE(factura_fechahora) BETWEEN ? AND ?");
        $stmt->bind_param("ss", $inicioMes, $finMes);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'];
    }
    
    private function getMetodosPagoMes()
    {
        try {
            $db = \App\Core\Database::getInstance();
            $inicioMes = date('Y-m-01');
            $finMes = date('Y-m-t');
            $stmt = $db->prepare("SELECT mdp.metododepago_descripcion, COUNT(*) as cantidad, SUM(p.pago_total) as total
                                   FROM pago p
                                   LEFT JOIN metododepago mdp ON p.rela_metododepago = mdp.id_metododepago
                                   WHERE DATE(p.pago_fechahora) BETWEEN ? AND ?
                                   GROUP BY mdp.metododepago_descripcion");
            
            if (!$stmt) {
                return []; // Retornar array vacío si hay error en la preparación
            }
            
            $stmt->bind_param("ss", $inicioMes, $finMes);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $metodos = [];
            while ($row = $result->fetch_assoc()) {
                $metodos[] = $row;
            }
            return $metodos;
        } catch (\Exception $e) {
            error_log("Error en getMetodosPagoMes: " . $e->getMessage());
            return []; // Retornar array vacío en caso de error
        }
    }
    
    private function getFacturasRecientes($limite)
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT f.*, r.id_reserva, c.cabania_nombre, p.persona_nombre, p.persona_apellido
                               FROM factura f
                               LEFT JOIN reserva r ON f.rela_reserva = r.id_reserva
                               LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                               LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                               LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                               LEFT JOIN persona p ON h.rela_persona = p.id_persona
                               ORDER BY f.factura_fechahora DESC
                               LIMIT ?");
        $stmt->bind_param("i", $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $facturas = [];
        while ($row = $result->fetch_assoc()) {
            $facturas[] = $row;
        }
        return $facturas;
    }
    
    private function getReservasPendientesPago()
    {
        $db = \App\Core\Database::getInstance();
        $result = $db->query("SELECT r.*, c.cabania_nombre, p.persona_nombre, p.persona_apellido, c.cabania_precio
                               FROM reserva r
                               LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                               LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                               LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                               LEFT JOIN persona p ON h.rela_persona = p.id_persona
                               WHERE r.rela_estadoreserva = 1
                               ORDER BY r.reserva_fhinicio ASC");
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        return $reservas;
    }
    
    private function getReservasHoy()
    {
        $db = \App\Core\Database::getInstance();
        $hoy = date('Y-m-d');
        $stmt = $db->prepare("SELECT r.*, c.cabania_nombre, p.persona_nombre, p.persona_apellido, er.estadoreserva_descripcion
                               FROM reserva r
                               LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                               LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                               LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                               LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                               LEFT JOIN persona p ON h.rela_persona = p.id_persona
                               WHERE DATE(r.reserva_fhinicio) = ?
                               ORDER BY r.reserva_fhinicio ASC");
        $stmt->bind_param("s", $hoy);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        return $reservas;
    }
    
    private function getCheckinsHoy()
    {
        $db = \App\Core\Database::getInstance();
        $hoy = date('Y-m-d');
        $stmt = $db->prepare("SELECT r.*, c.cabania_nombre, p.persona_nombre, p.persona_apellido
                               FROM reserva r
                               LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                               LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                               LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                               LEFT JOIN persona p ON h.rela_persona = p.id_persona
                               WHERE DATE(r.reserva_fhinicio) = ? AND r.rela_estadoreserva = 2
                               ORDER BY r.reserva_fhinicio ASC");
        $stmt->bind_param("s", $hoy);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $checkins = [];
        while ($row = $result->fetch_assoc()) {
            $checkins[] = $row;
        }
        return $checkins;
    }
    
    private function getCheckoutsHoy()
    {
        $db = \App\Core\Database::getInstance();
        $hoy = date('Y-m-d');
        $stmt = $db->prepare("SELECT r.*, c.cabania_nombre, p.persona_nombre, p.persona_apellido
                               FROM reserva r
                               LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                               LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                               LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                               LEFT JOIN persona p ON h.rela_persona = p.id_persona
                               WHERE DATE(r.reserva_fhfin) = ? AND r.rela_estadoreserva IN (2, 3)
                               ORDER BY r.reserva_fhfin ASC");
        $stmt->bind_param("s", $hoy);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $checkouts = [];
        while ($row = $result->fetch_assoc()) {
            $checkouts[] = $row;
        }
        return $checkouts;
    }
    
    private function getReservasProximas($dias)
    {
        $db = \App\Core\Database::getInstance();
        $fechaLimite = date('Y-m-d', strtotime("+$dias days"));
        $stmt = $db->prepare("SELECT r.*, c.cabania_nombre, p.persona_nombre, p.persona_apellido, er.estadoreserva_descripcion
                               FROM reserva r
                               LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                               LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                               LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                               LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                               LEFT JOIN persona p ON h.rela_persona = p.id_persona
                               WHERE DATE(r.reserva_fhinicio) BETWEEN CURDATE() AND ? 
                               AND r.rela_estadoreserva IN (1, 2)
                               ORDER BY r.reserva_fhinicio ASC");
        $stmt->bind_param("s", $fechaLimite);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        return $reservas;
    }

    /**
     * Página de información sobre las cabañas
     */
    public function about()
    {
        $data = [
            'title' => 'Acerca de Nosotros - Casa de Palos',
        ];

        return $this->render('public/home/about', $data, 'main');
    }

    /**
     * Página de contacto
     */
    public function contact()
    {
        if ($this->isPost()) {
            // Procesar formulario de contacto
            $nombre = $this->post('nombre');
            $email = $this->post('email');
            $mensaje = $this->post('mensaje');

            // Aquí podrías enviar email o guardar en BD
            // Por ahora solo redirigimos con mensaje de éxito
            
            $this->redirect('/', 'Mensaje enviado correctamente. Nos pondremos en contacto contigo pronto.', 'exito');
        }

        $data = [
            'title' => 'Contacto - Casa de Palos',
        ];

        return $this->render('public/home/contact', $data, 'main');
    }
}