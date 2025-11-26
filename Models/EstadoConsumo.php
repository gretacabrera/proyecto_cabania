<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad EstadoConsumo
 * Gestiona los estados de los consumos
 */
class EstadoConsumo extends Model
{
    protected $table = 'estadoconsumo';
    protected $primaryKey = 'id_estadoconsumo';

    // Constantes de estados
    const SOLICITUD_PENDIENTE = 1;
    const EN_PROCESO = 2;
    const ENTREGADO = 3;
    const ANULADO_SIN_STOCK = 4;
    const ANULADO_INCONVENIENTE = 5;
    const CANCELADO_USUARIO = 6;

    /**
     * Obtener estados con paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        
        // Aplicar filtros
        if (!empty($filters['estadoconsumo_descripcion'])) {
            $where .= " AND estadoconsumo_descripcion LIKE '%" . $this->db->escape($filters['estadoconsumo_descripcion']) . "%'";
        }
        
        if (isset($filters['estadoconsumo_estado']) && $filters['estadoconsumo_estado'] !== '') {
            $where .= " AND estadoconsumo_estado = " . (int)$filters['estadoconsumo_estado'];
        }
        
        return $this->paginate($page, $perPage, $where, "id_estadoconsumo ASC");
    }

    /**
     * Obtener todos los estados activos
     */
    public function getActivos()
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE estadoconsumo_estado = 1 
                ORDER BY id_estadoconsumo ASC";
        
        $result = $this->db->query($sql);
        
        $estados = [];
        while ($row = $result->fetch_assoc()) {
            $estados[] = $row;
        }
        
        return $estados;
    }

    /**
     * Obtener descripción de un estado
     */
    public function getDescripcion($estadoId)
    {
        $estado = $this->find($estadoId);
        return $estado ? $estado['estadoconsumo_descripcion'] : 'Desconocido';
    }

    /**
     * Verificar si un estado existe
     */
    public function estadoExiste($estadoId)
    {
        $estado = $this->find($estadoId);
        return $estado !== null && $estado !== false;
    }

    /**
     * Obtener estados para uso en formularios
     */
    public function getForSelect()
    {
        $estados = $this->getActivos();
        
        $options = [];
        foreach ($estados as $estado) {
            $options[$estado['id_estadoconsumo']] = $estado['estadoconsumo_descripcion'];
        }
        
        return $options;
    }

    /**
     * Obtener color del badge según el estado
     */
    public function getBadgeClass($estadoId)
    {
        switch ((int)$estadoId) {
            case self::SOLICITUD_PENDIENTE:
                return 'warning'; // Amarillo
            case self::EN_PROCESO:
                return 'info'; // Azul
            case self::ENTREGADO:
                return 'success'; // Verde
            case self::ANULADO_SIN_STOCK:
                return 'danger'; // Rojo
            case self::ANULADO_INCONVENIENTE:
                return 'danger'; // Rojo
            case self::CANCELADO_USUARIO:
                return 'secondary'; // Gris
            default:
                return 'secondary';
        }
    }

    /**
     * Obtener icono según el estado
     */
    public function getIcon($estadoId)
    {
        switch ((int)$estadoId) {
            case self::SOLICITUD_PENDIENTE:
                return 'clock'; // Reloj
            case self::EN_PROCESO:
                return 'spinner'; // Procesando
            case self::ENTREGADO:
                return 'check-circle'; // Completado
            case self::ANULADO_SIN_STOCK:
                return 'times-circle'; // Error
            case self::ANULADO_INCONVENIENTE:
                return 'exclamation-triangle'; // Advertencia
            case self::CANCELADO_USUARIO:
                return 'ban'; // Cancelado
            default:
                return 'question-circle';
        }
    }

    /**
     * Validar transición de estado
     * Define qué cambios de estado son válidos
     */
    public function validarTransicion($estadoActual, $estadoNuevo)
    {
        // Matriz de transiciones permitidas
        $transicionesPermitidas = [
            self::SOLICITUD_PENDIENTE => [self::EN_PROCESO, self::CANCELADO_USUARIO],
            self::EN_PROCESO => [self::ENTREGADO, self::ANULADO_SIN_STOCK, self::ANULADO_INCONVENIENTE],
            self::ENTREGADO => [], // Estado final, no permite cambios
            self::ANULADO_SIN_STOCK => [], // Estado final
            self::ANULADO_INCONVENIENTE => [], // Estado final
            self::CANCELADO_USUARIO => [] // Estado final
        ];
        
        if (!isset($transicionesPermitidas[$estadoActual])) {
            return [
                'valido' => false,
                'mensaje' => 'Estado actual no válido'
            ];
        }
        
        if (in_array($estadoNuevo, $transicionesPermitidas[$estadoActual])) {
            return [
                'valido' => true,
                'mensaje' => 'Transición permitida'
            ];
        }
        
        return [
            'valido' => false,
            'mensaje' => 'No se puede cambiar de ' . $this->getDescripcion($estadoActual) . ' a ' . $this->getDescripcion($estadoNuevo)
        ];
    }

    /**
     * Obtener estadísticas de consumos por estado
     */
    public function getEstadisticas($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND c.consumo_fechahora >= ?";
            $params[] = $filters['fecha_desde'];
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND c.consumo_fechahora <= ?";
            $params[] = $filters['fecha_hasta'];
        }
        
        $sql = "SELECT 
                    ec.id_estadoconsumo,
                    ec.estadoconsumo_descripcion,
                    COUNT(c.id_consumo) as cantidad,
                    SUM(c.consumo_total) as total_monto,
                    AVG(c.consumo_total) as promedio_monto
                FROM estadoconsumo ec
                LEFT JOIN consumo c ON ec.id_estadoconsumo = c.rela_estadoconsumo
                WHERE {$where}
                GROUP BY ec.id_estadoconsumo, ec.estadoconsumo_descripcion
                ORDER BY ec.id_estadoconsumo ASC";
        
        $result = $this->query($sql, $params);
        
        $estadisticas = [];
        while ($row = $result->fetch_assoc()) {
            $estadisticas[] = $row;
        }
        
        return $estadisticas;
    }

    /**
     * Obtener consumos pendientes de procesar
     */
    public function getConsumosPendientes($limit = 50)
    {
        $sql = "SELECT c.*, 
                       p.producto_nombre,
                       s.servicio_descripcion,
                       r.id_reserva,
                       cab.cabania_nombre,
                       per.persona_nombre,
                       per.persona_apellido
                FROM consumo c
                LEFT JOIN producto p ON c.rela_producto = p.id_producto
                LEFT JOIN servicio s ON c.rela_servicio = s.id_servicio
                LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
                LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                LEFT JOIN persona per ON h.rela_persona = per.id_persona
                WHERE c.rela_estadoconsumo = ?
                ORDER BY c.consumo_fechahora ASC
                LIMIT ?";
        
        $result = $this->query($sql, [self::SOLICITUD_PENDIENTE, (int)$limit]);
        
        $consumos = [];
        while ($row = $result->fetch_assoc()) {
            $consumos[] = $row;
        }
        
        return $consumos;
    }

    /**
     * Obtener consumos en proceso
     */
    public function getConsumosEnProceso($limit = 50)
    {
        $sql = "SELECT c.*, 
                       p.producto_nombre,
                       s.servicio_descripcion,
                       r.id_reserva,
                       cab.cabania_nombre,
                       per.persona_nombre,
                       per.persona_apellido
                FROM consumo c
                LEFT JOIN producto p ON c.rela_producto = p.id_producto
                LEFT JOIN servicio s ON c.rela_servicio = s.id_servicio
                LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
                LEFT JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                LEFT JOIN persona per ON h.rela_persona = per.id_persona
                WHERE c.rela_estadoconsumo = ?
                ORDER BY c.consumo_fechahora ASC
                LIMIT ?";
        
        $result = $this->query($sql, [self::EN_PROCESO, (int)$limit]);
        
        $consumos = [];
        while ($row = $result->fetch_assoc()) {
            $consumos[] = $row;
        }
        
        return $consumos;
    }

    /**
     * Obtener todos los estados para exportación
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        
        if (!empty($filters['estadoconsumo_descripcion'])) {
            $where .= " AND estadoconsumo_descripcion LIKE '%" . $this->db->escape($filters['estadoconsumo_descripcion']) . "%'";
        }
        
        if (isset($filters['estadoconsumo_estado']) && $filters['estadoconsumo_estado'] !== '') {
            $where .= " AND estadoconsumo_estado = " . (int)$filters['estadoconsumo_estado'];
        }
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE {$where}
                ORDER BY id_estadoconsumo ASC";
        
        $result = $this->db->query($sql);
        
        $data = [];
        $total = 0;
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
            $total++;
        }
        
        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Obtener estadísticas de uso de un estado
     */
    public function getEstadisticasUso($estadoId)
    {
        $sql = "SELECT 
                    COUNT(c.id_consumo) as total_consumos,
                    SUM(c.consumo_total) as monto_total,
                    AVG(c.consumo_total) as monto_promedio,
                    MIN(c.consumo_fechahora) as primer_uso,
                    MAX(c.consumo_fechahora) as ultimo_uso
                FROM consumo c
                WHERE c.rela_estadoconsumo = ?";
        
        $result = $this->query($sql, [$estadoId]);
        $estadisticas = $result->fetch_assoc();
        
        // Obtener consumos recientes con este estado
        $sqlRecientes = "SELECT c.*, 
                               p.producto_nombre,
                               s.servicio_descripcion,
                               cab.cabania_nombre
                        FROM consumo c
                        LEFT JOIN producto p ON c.rela_producto = p.id_producto
                        LEFT JOIN servicio s ON c.rela_servicio = s.id_servicio
                        LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                        LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
                        WHERE c.rela_estadoconsumo = ?
                        ORDER BY c.consumo_fechahora DESC
                        LIMIT 5";
        
        $resultRecientes = $this->query($sqlRecientes, [$estadoId]);
        $consumosRecientes = [];
        while ($row = $resultRecientes->fetch_assoc()) {
            $consumosRecientes[] = $row;
        }
        
        return [
            'total_consumos' => (int)$estadisticas['total_consumos'],
            'monto_total' => $estadisticas['monto_total'] ?? 0,
            'monto_promedio' => $estadisticas['monto_promedio'] ?? 0,
            'primer_uso' => $estadisticas['primer_uso'],
            'ultimo_uso' => $estadisticas['ultimo_uso'],
            'consumos_recientes' => $consumosRecientes
        ];
    }
}
