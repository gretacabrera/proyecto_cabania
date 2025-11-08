<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para el manejo de Estados de Reservas
 */
class EstadoReserva extends Model
{
    protected $table = 'estadoreserva';
    protected $primaryKey = 'id_estadoreserva';

    // Nombres de estados de reserva
    const PENDIENTE = 'pendiente';
    const CONFIRMADA = 'confirmada';
    const EN_CURSO = 'en curso';
    const PENDIENTE_PAGO = 'pendiente de pago';
    const FINALIZADA = 'finalizada';
    const ANULADA = 'anulada';
    const EXPIRADA = 'expirada';
    const CANCELADA = 'cancelada';
    
    // Cache para IDs de estados
    private $estadosCache = null;
    private $estadosInvertidos = null;

    /**
     * Cargar estados en cache (método optimizado)
     */
    private function loadEstados()
    {
        if ($this->estadosCache === null) {
            $estados = $this->findAll("estadoreserva_estado = 1");
            
            $this->estadosCache = [];
            $this->estadosInvertidos = [];
            
            foreach ($estados as $estado) {
                $nombre = $estado['estadoreserva_descripcion'];
                $id = $estado['id_estadoreserva'];
                
                $this->estadosCache[$nombre] = $id;
                $this->estadosInvertidos[$id] = $nombre;
            }
        }
    }
    
    /**
     * Obtener ID de estado por nombre
     */
    public function getId($nombre)
    {
        $this->loadEstados();
        return isset($this->estadosCache[$nombre]) ? $this->estadosCache[$nombre] : null;
    }
    
    /**
     * Obtener nombre de estado por ID  
     */
    public function getName($id)
    {
        $this->loadEstados();
        return isset($this->estadosInvertidos[$id]) ? $this->estadosInvertidos[$id] : null;
    }
    
    /**
     * Verificar si un estado existe
     */
    public function existe($nombre)
    {
        $this->loadEstados();
        return isset($this->estadosCache[$nombre]);
    }
    
    /**
     * Obtener múltiples IDs por nombres
     */
    public function getIds($nombres)
    {
        $this->loadEstados();
        $ids = [];
        foreach ($nombres as $nombre) {
            if (isset($this->estadosCache[$nombre])) {
                $ids[] = $this->estadosCache[$nombre];
            }
        }
        return $ids;
    }

    /**
     * Obtener estados de reservas con detalles, aplicando filtros y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        
        // Aplicar filtros usando LIKE directo (sin prepared statements para compatibilidad)
        if (!empty($filters['estadoreserva_descripcion'])) {
            $descripcion = $this->db->real_escape_string($filters['estadoreserva_descripcion']);
            $where .= " AND estadoreserva_descripcion LIKE '%" . $descripcion . "%'";
        }

        if (isset($filters['estadoreserva_estado']) && $filters['estadoreserva_estado'] !== '') {
            $estado = (int) $filters['estadoreserva_estado'];
            $where .= " AND estadoreserva_estado = " . $estado;
        }

        $result = $this->paginate($page, $perPage, $where, "estadoreserva_descripcion ASC");
        
        // Agregar el conteo de reservas a cada estado
        if (isset($result['data'])) {
            foreach ($result['data'] as &$estado) {
                $estado['reservas_count'] = $this->getReservasCountByEstado($estado['id_estadoreserva']);
            }
        }
        
        return $result;
    }

    /**
     * Obtener todos los estados de reservas con detalles para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";

        // Aplicar filtros usando escape directo
        if (!empty($filters['estadoreserva_descripcion'])) {
            $descripcion = $this->db->real_escape_string($filters['estadoreserva_descripcion']);
            $where .= " AND estadoreserva_descripcion LIKE '%" . $descripcion . "%'";
        }

        if (isset($filters['estadoreserva_estado']) && $filters['estadoreserva_estado'] !== '') {
            $estado = (int) $filters['estadoreserva_estado'];
            $where .= " AND estadoreserva_estado = " . $estado;
        }

        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY estadoreserva_descripcion ASC";
        $result = $this->db->query($sql);
        
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }

        return [
            'data' => $records,
            'total' => count($records)
        ];
    }

    /**
     * Obtener el conteo de reservas por estado de reserva
     */
    public function getReservasCountByEstado($id_estadoreserva)
    {
        $id = (int) $id_estadoreserva;
        $sql = "SELECT COUNT(*) as total FROM reserva WHERE rela_estadoreserva = {$id}";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    /**
     * Obtener estadísticas generales de estados de reservas
     */
    public function getEstadisticas()
    {
        $sql = "SELECT 
                    COUNT(*) as total_estados,
                    SUM(CASE WHEN estadoreserva_estado = 1 THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN estadoreserva_estado = 0 THEN 1 ELSE 0 END) as inactivos
                FROM {$this->table}";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Validar si se puede cambiar el estado del estado de reserva
     */
    public function canChangeStatus($id_estadoreserva)
    {
        // Verificar si tiene reservas asociadas activas
        $reservasCount = $this->getReservasCountByEstado($id_estadoreserva);
        return $reservasCount === 0;
    }

    /**
     * Obtener estados activos
     */
    public function getActive()
    {
        return $this->findAll("estadoreserva_estado = 1", "estadoreserva_descripcion");
    }

    /**
     * Buscar estados de reservas
     */
    public function search($filters = [], $page = 1, $perPage = 10)
    {
        $where = ["estadoreserva_estado = 1"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "estadoreserva_descripcion LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where[] = "estadoreserva_estado = ?";
            $params[] = $filters['estado'];
        }

        $whereClause = implode(' AND ', $where);
        
        return $this->paginate($page, $perPage, $whereClause, "estadoreserva_descripcion", $params);
    }

    /**
     * Obtener total de páginas para búsqueda
     */
    public function getTotalPages($filters = [], $perPage = 10)
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "estadoreserva_descripcion LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where[] = "estadoreserva_estado = ?";
            $params[] = $filters['estado'];
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
        
        $result = $this->query($sql, $params);
        $total = $result->fetch_assoc()['total'];
        
        return ceil($total / $perPage);
    }



    /**
     * Verificar si el estado está en uso
     */
    public function isInUse($id)
    {
        $sql = "SELECT COUNT(*) as count FROM reserva WHERE rela_estadoreserva = ?";
        $result = $this->query($sql, [$id]);
        $count = $result->fetch_assoc()['count'];
        
        return $count > 0;
    }

    /**
     * Cambiar estado (baja/alta lógica)
     */
    public function toggleStatus($id)
    {
        $estadoReserva = $this->find($id);
        if (!$estadoReserva) {
            return false;
        }

        $newStatus = $estadoReserva['estadoreserva_estado'] == 1 ? 0 : 1;
        return $this->update($id, ['estadoreserva_estado' => $newStatus]);
    }

    /**
     * Obtener estados con conteo de reservas
     */
    public function getWithReservaCount()
    {
        $sql = "SELECT er.*, 
                       COUNT(r.id_reserva) as reservas_count
                FROM {$this->table} er
                LEFT JOIN reserva r ON er.{$this->primaryKey} = r.rela_estadoreserva
                GROUP BY er.{$this->primaryKey}
                ORDER BY er.estadoreserva_descripcion";
        
        $result = $this->db->query($sql);
        
        $estados = [];
        while ($row = $result->fetch_assoc()) {
            $estados[] = $row;
        }
        
        return $estados;
    }

    /**
     * Obtener estadísticas por mes
     */
    public function getMonthlyStats($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        $sql = "SELECT er.estadoreserva_descripcion,
                       MONTH(r.reserva_fechainicio) as mes,
                       COUNT(r.id_reserva) as cantidad
                FROM {$this->table} er
                LEFT JOIN reserva r ON er.{$this->primaryKey} = r.rela_estadoreserva 
                    AND YEAR(r.reserva_fechainicio) = ?
                WHERE er.estadoreserva_estado = 1
                GROUP BY er.{$this->primaryKey}, MONTH(r.reserva_fechainicio)
                ORDER BY er.estadoreserva_descripcion, mes";
        
        $result = $this->query($sql, [$year]);
        
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        
        return $stats;
    }

    // ==================================================
    // MÉTODOS DE LÓGICA DE NEGOCIO OPTIMIZADOS
    // ==================================================

    /**
     * Verificar si una reserva puede ser cancelada por el huésped
     */
    public function puedeSerCancelada($estadoActual)
    {
        $nombreEstado = $this->getName($estadoActual);
        
        $estadosCancelables = [
            self::PENDIENTE,
            self::CONFIRMADA
        ];
        
        return in_array($nombreEstado, $estadosCancelables);
    }

    /**
     * Verificar si una reserva puede ser anulada por el administrador
     */
    public function puedeSerAnulada($estadoActual)
    {
        $nombreEstado = $this->getName($estadoActual);
        
        $estadosNoAnulables = [
            self::FINALIZADA,
            self::ANULADA,
            self::CANCELADA,
            self::EXPIRADA
        ];
        
        return !in_array($nombreEstado, $estadosNoAnulables);
    }

    /**
     * Verificar si una reserva está expirada o puede expirar
     */
    public function puedeExpirar($estadoActual)
    {
        return $this->getName($estadoActual) === self::PENDIENTE;
    }

    /**
     * Verificar si una reserva ya está expirada
     */
    public function estaExpirada($estadoActual)
    {
        return $this->getName($estadoActual) === self::EXPIRADA;
    }

    /**
     * Obtener estados que bloquean disponibilidad de cabaña
     */
    public function getEstadosQueBloquean()
    {
        return $this->getIds([
            self::PENDIENTE,
            self::CONFIRMADA,
            self::EN_CURSO
        ]);
    }

    /**
     * Verificar si un estado nunca expira
     */
    public function nuncaExpira($estadoActual)
    {
        $nombreEstado = $this->getName($estadoActual);
        
        $estadosQueNuncaExpiran = [
            self::CONFIRMADA,
            self::EN_CURSO,
            self::FINALIZADA,
            self::ANULADA,
            self::CANCELADA,
            self::EXPIRADA
        ];
        
        return in_array($nombreEstado, $estadosQueNuncaExpiran);
    }

    /**
     * Obtener descripción amigable del estado
     */
    public function getDescripcion($estadoActual)
    {
        $nombreEstado = $this->getName($estadoActual);
        
        $descripciones = [
            self::PENDIENTE => 'Pendiente de confirmación',
            self::CONFIRMADA => 'Confirmada',
            self::EN_CURSO => 'En curso',
            self::PENDIENTE_PAGO => 'Pendiente de pago',
            self::FINALIZADA => 'Finalizada',
            self::ANULADA => 'Anulada por administrador',
            self::EXPIRADA => 'Expirada automáticamente',
            self::CANCELADA => 'Cancelada por huésped'
        ];
        
        return isset($descripciones[$nombreEstado]) ? $descripciones[$nombreEstado] : ucfirst($nombreEstado);
    }

    /**
     * Obtener clase CSS para el estado
     */
    public function getCssClass($estadoActual)
    {
        $nombreEstado = $this->getName($estadoActual);
        
        $clases = [
            self::PENDIENTE => 'warning',
            self::CONFIRMADA => 'success',
            self::EN_CURSO => 'info',
            self::PENDIENTE_PAGO => 'warning',
            self::FINALIZADA => 'secondary',
            self::ANULADA => 'danger',
            self::EXPIRADA => 'dark',
            self::CANCELADA => 'danger'
        ];
        
        return isset($clases[$nombreEstado]) ? $clases[$nombreEstado] : 'secondary';
    }

    /**
     * Obtener estadísticas de un estado de reserva específico
     */
    public function getStatistics($id)
    {
        $stats = [
            'total_reservas' => $this->getTotalReservasByEstado($id),
            'reservas_mes_actual' => $this->getReservasMesActualByEstado($id),
            'porcentaje_uso' => $this->getPorcentajeUsoByEstado($id)
        ];
        
        return $stats;
    }

    /**
     * Obtener total de reservas con este estado (histórico)
     */
    private function getTotalReservasByEstado($id)
    {
        $sql = "SELECT COUNT(*) as total FROM reserva WHERE rela_estadoreserva = ?";
        $result = $this->query($sql, [$id]);
        $row = $result->fetch_assoc();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Obtener reservas del mes actual con este estado
     */
    private function getReservasMesActualByEstado($id)
    {
        $mesActual = date('Y-m');
        $sql = "SELECT COUNT(*) as total 
                FROM reserva 
                WHERE rela_estadoreserva = ? 
                AND DATE_FORMAT(reserva_fhinicio, '%Y-%m') = ?";
        $result = $this->query($sql, [$id, $mesActual]);
        $row = $result->fetch_assoc();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Obtener porcentaje de uso de este estado respecto al total
     */
    private function getPorcentajeUsoByEstado($id)
    {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM reserva WHERE rela_estadoreserva = ?) as estado_count,
                    (SELECT COUNT(*) FROM reserva) as total_count";
        $result = $this->query($sql, [$id]);
        $row = $result->fetch_assoc();
        
        $estadoCount = (int)($row['estado_count'] ?? 0);
        $totalCount = (int)($row['total_count'] ?? 0);
        
        if ($totalCount == 0) {
            return 0;
        }
        
        return round(($estadoCount / $totalCount) * 100, 1);
    }
}