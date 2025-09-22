<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Cabania
 */
class Cabania extends Model
{
    protected $table = 'cabania';
    protected $primaryKey = 'id_cabania';

    /**
     * Obtener cabañas activas y ocupadas (operativas)
     */
    public function getActive()
    {
        return $this->findAll("cabania_estado IN (1, 2)", "cabania_nombre");
    }

    /**
     * Obtener solo cabañas disponibles (activas pero no ocupadas)
     */
    public function getAvailable()
    {
        return $this->findAll("cabania_estado = 1", "cabania_nombre");
    }

    /**
     * Obtener cabañas ocupadas
     */
    public function getOccupied()
    {
        return $this->findAll("cabania_estado = 2", "cabania_nombre");
    }

    /**
     * Buscar cabañas disponibles por capacidad (no incluye ocupadas)
     */
    public function findByCapacity($capacity)
    {
        return $this->findAll("cabania_capacidad >= $capacity AND cabania_estado = 1", "cabania_precio");
    }

    /**
     * Buscar cabañas disponibles por rango de precio (no incluye ocupadas)
     */
    public function findByPriceRange($minPrice, $maxPrice)
    {
        return $this->findAll("cabania_precio BETWEEN $minPrice AND $maxPrice AND cabania_estado = 1", "cabania_precio");
    }

    /**
     * Obtener cabañas con filtros y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['cabania_codigo'])) {
            $where .= " AND cabania_codigo LIKE ?";
            $params[] = '%' . $filters['cabania_codigo'] . '%';
        }
        
        if (!empty($filters['cabania_nombre'])) {
            $where .= " AND cabania_nombre LIKE ?";
            $params[] = '%' . $filters['cabania_nombre'] . '%';
        }
        
        if (!empty($filters['cabania_ubicacion'])) {
            $where .= " AND cabania_ubicacion LIKE ?";
            $params[] = '%' . $filters['cabania_ubicacion'] . '%';
        }
        
        if (!empty($filters['cabania_capacidad'])) {
            $where .= " AND cabania_capacidad >= ?";
            $params[] = (int) $filters['cabania_capacidad'];
        }
        
        if (!empty($filters['cabania_habitaciones'])) {
            $where .= " AND cabania_cantidadhabitaciones >= ?";
            $params[] = (int) $filters['cabania_habitaciones'];
        }
        
        if (!empty($filters['cabania_banios'])) {
            $where .= " AND cabania_cantidadbanios >= ?";
            $params[] = (int) $filters['cabania_banios'];
        }
        
        if (isset($filters['cabania_estado']) && $filters['cabania_estado'] !== '') {
            $where .= " AND cabania_estado = ?";
            $params[] = (int) $filters['cabania_estado'];
        }
        
        return $this->paginateWithParams($page, $perPage, $where, "cabania_nombre ASC", $params);
    }

    /**
     * Obtener cabañas con paginación usando parámetros preparados
     */
    private function paginateWithParams($page = 1, $perPage = 10, $where = "1=1", $orderBy = null, $params = [])
    {
        $offset = ($page - 1) * $perPage;
        $limit = (int) $perPage;
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener registros
        $orderClause = $orderBy ? "ORDER BY $orderBy" : '';
        $dataSql = "SELECT * FROM {$this->table} WHERE $where $orderClause LIMIT $limit OFFSET $offset";
        $dataResult = $this->queryWithParams($dataSql, $params);
        
        $data = [];
        while ($row = $dataResult->fetch_assoc()) {
            $data[] = $row;
        }
        
        $totalPages = ceil($total / $perPage);
        
        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $perPage,
            'offset' => $offset,
            'limit' => $limit
        ];
    }

    /**
     * Ejecutar query con parámetros preparados
     */
    private function queryWithParams($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new \Exception("Error preparando consulta: " . $this->db->error);
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new \Exception("Error ejecutando consulta: " . $stmt->error);
        }
        
        return $stmt->get_result();
    }

    /**
     * Verificar disponibilidad para fechas específicas (solo reservas futuras)
     */
    public function checkAvailability($cabaniaId, $fechaInicio, $fechaFin)
    {
        $fechaActual = date('Y-m-d H:i:s');
        $sql = "SELECT COUNT(*) as reservas_conflictivas 
                FROM reserva r 
                WHERE r.rela_cabania = ? 
                AND r.rela_estadoreserva IN (1, 2, 3) 
                AND r.reserva_fhfin >= ?
                AND (
                    (r.reserva_fhinicio <= ? AND r.reserva_fhfin >= ?) OR
                    (r.reserva_fhinicio <= ? AND r.reserva_fhfin >= ?) OR
                    (r.reserva_fhinicio >= ? AND r.reserva_fhfin <= ?)
                )";
        
        $result = $this->query($sql, [$cabaniaId, $fechaActual, $fechaInicio, $fechaInicio, $fechaFin, $fechaFin, $fechaInicio, $fechaFin]);
        $row = $result->fetch_assoc();
        
        return (int)$row['reservas_conflictivas'] === 0;
    }

    /**
     * Obtener reservas activas futuras de una cabaña
     */
    public function getActiveReservations($cabaniaId)
    {
        $fechaActual = date('Y-m-d H:i:s');
        $sql = "SELECT r.reserva_fhinicio, r.reserva_fhfin
                FROM reserva r
                LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                WHERE r.rela_cabania = ?
                AND er.estadoreserva_estado IN (1, 2, 3)
                AND r.reserva_fhfin >= ?
                ORDER BY r.reserva_fhinicio";
        
        $result = $this->query($sql, [$cabaniaId, $fechaActual]);
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        
        return $reservas;
    }

    /**
     * Obtener todas las fechas ocupadas de una cabaña específica
     * Solo considera reservas futuras para optimizar rendimiento
     */
    public function getOccupiedDates($cabaniaId)
    {
        // Validar que la cabaña existe y está activa
        $cabania = $this->find($cabaniaId);
        if (!$cabania || $cabania['cabania_estado'] != 1) {
            throw new \Exception('Cabaña no encontrada o inactiva');
        }

        // Consulta optimizada para obtener solo reservas futuras activas
        $fechaActual = date('Y-m-d H:i:s');
        $sql = "SELECT r.reserva_fhinicio, r.reserva_fhfin 
                FROM reserva r 
                LEFT JOIN estadoreserva er ON r.rela_estadoreserva = er.id_estadoreserva
                WHERE r.rela_cabania = ? 
                AND er.estadoreserva_estado IN (1, 2, 3)
                AND r.reserva_fhfin >= ?
                ORDER BY r.reserva_fhinicio";
        
        $result = $this->query($sql, [$cabaniaId, $fechaActual]);
        
        $fechasOcupadas = [];
        $hoy = new \DateTime();
        $hoy->setTime(0, 0, 0); // Normalizar a medianoche
        
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['reserva_fhinicio']) && !empty($row['reserva_fhfin'])) {
                try {
                    $fechaInicio = new \DateTime($row['reserva_fhinicio']);
                    $fechaFin = new \DateTime($row['reserva_fhfin']);
                    
                    // Solo considerar fechas desde hoy en adelante
                    if ($fechaInicio < $hoy) {
                        $fechaInicio = clone $hoy;
                    }
                    
                    // Solo procesar si la reserva no ha terminado completamente
                    if ($fechaFin >= $hoy) {
                        // Generar fechas ocupadas día por día incluyendo el último día
                        $periodo = new \DatePeriod(
                            $fechaInicio,
                            new \DateInterval('P1D'),
                            $fechaFin->modify('+1 day')
                        );
                        
                        foreach ($periodo as $fecha) {
                            $fechasOcupadas[] = $fecha->format('Y-m-d');
                        }
                    }
                } catch (\Exception $e) {
                    // Log error pero continúa procesando otras reservas
                    error_log("Error procesando fechas de reserva: " . $e->getMessage());
                }
            }
        }
        
        // Remover duplicados y ordenar
        $fechasOcupadas = array_unique($fechasOcupadas);
        sort($fechasOcupadas);
        
        return $fechasOcupadas;
    }

    /**
     * Obtener cabañas con paginación para el catálogo público
     */
    public function getPaginated($page = 1, $perPage = 10, $filters = [])
    {
        $whereConditions = ['cabania_estado = 1']; // Solo cabañas activas
        
        if (!empty($filters['capacidad'])) {
            $whereConditions[] = "cabania_capacidad >= " . (int)$filters['capacidad'];
        }
        
        if (!empty($filters['precio_max'])) {
            $whereConditions[] = "cabania_precio <= " . (float)$filters['precio_max'];
        }
        
        if (!empty($filters['busqueda'])) {
            $busqueda = addslashes($filters['busqueda']);
            $whereConditions[] = "(cabania_nombre LIKE '%{$busqueda}%' OR cabania_ubicacion LIKE '%{$busqueda}%' OR cabania_descripcion LIKE '%{$busqueda}%')";
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        return $this->paginate($page, $perPage, $whereClause, "cabania_nombre ASC");
    }

    /**
     * Verificar si una cabaña existe y está activa
     */
    public function isCabinActive($cabaniaId)
    {
        $cabania = $this->find($cabaniaId);
        return $cabania && $cabania['cabania_estado'] == 1;
    }

    /**
     * Obtener estadísticas de una cabaña específica
     */
    public function getStatistics($cabaniaId)
    {
        $stats = [
            'reservas_activas' => $this->getReservasActivas($cabaniaId),
            'reservas_totales' => $this->getReservasTotales($cabaniaId),
            'ocupacion_porcentaje' => $this->getOcupacionPorcentaje($cabaniaId),
            'ingresos_mes' => $this->getIngresosMes($cabaniaId)
        ];
        
        return $stats;
    }

    /**
     * Obtener número de reservas activas (confirmadas y futuras)
     */
    private function getReservasActivas($cabaniaId)
    {
        $fechaActual = date('Y-m-d');
        $sql = "SELECT COUNT(*) as total 
                FROM reserva 
                WHERE rela_cabania = ? 
                AND DATE(reserva_fhfin) >= ? 
                AND rela_estadoreserva IN (1, 2)"; // Estados: Pendiente, Confirmada
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('is', $cabaniaId, $fechaActual);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
    }

    /**
     * Obtener número total de reservas (históricas)
     */
    private function getReservasTotales($cabaniaId)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM reserva 
                WHERE rela_cabania = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $cabaniaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
    }

    /**
     * Calcular porcentaje de ocupación del mes actual
     */
    private function getOcupacionPorcentaje($cabaniaId)
    {
        $mesActual = date('Y-m');
        $inicioMes = $mesActual . '-01';
        $finMes = date('Y-m-t');
        
        // Días del mes
        $diasDelMes = date('t');
        
        // Días ocupados en el mes
        $sql = "SELECT COUNT(DISTINCT DATE(reserva_fhinicio)) as dias_ocupados
                FROM reserva 
                WHERE rela_cabania = ? 
                AND rela_estadoreserva IN (2, 3) 
                AND (
                    (reserva_fhinicio BETWEEN ? AND ?) 
                    OR (reserva_fhfin BETWEEN ? AND ?)
                    OR (reserva_fhinicio <= ? AND reserva_fhfin >= ?)
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('issssss', $cabaniaId, $inicioMes, $finMes, $inicioMes, $finMes, $inicioMes, $finMes);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $diasOcupados = (int)$row['dias_ocupados'];
        $porcentaje = ($diasDelMes > 0) ? round(($diasOcupados / $diasDelMes) * 100, 1) : 0;
        
        return $porcentaje;
    }

    /**
     * Calcular ingresos del mes actual
     */
    private function getIngresosMes($cabaniaId)
    {
        $mesActual = date('Y-m');
        $inicioMes = $mesActual . '-01';
        $finMes = date('Y-m-t');
        
        // Como no hay campo precio_total, usamos el precio base de la cabaña
        $sql = "SELECT COUNT(*) as total_reservas
                FROM reserva 
                WHERE rela_cabania = ? 
                AND rela_estadoreserva IN (2, 3) 
                AND reserva_fhinicio BETWEEN ? AND ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iss', $cabaniaId, $inicioMes, $finMes);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $totalReservas = (int)($row['total_reservas'] ?? 0);
        
        // Obtener precio de la cabaña
        $cabania = $this->find($cabaniaId);
        $precioBase = $cabania ? (float)$cabania['cabania_precio'] : 0;
        
        return $totalReservas * $precioBase;
    }
}