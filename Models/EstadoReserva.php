<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad EstadoReserva
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
}