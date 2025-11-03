<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la gestión de servicios
 */
class Servicio extends Model
{
    protected $table = 'servicio';
    protected $primaryKey = 'id_servicio';

    /**
     * Validar datos del servicio
     */
    public function validate($data, $id = null)
    {
        $errors = [];

        // Validar nombre del servicio
        if (empty($data['servicio_nombre'])) {
            $errors[] = 'El nombre del servicio es requerido';
        } elseif (strlen($data['servicio_nombre']) > 45) {
            $errors[] = 'El nombre no puede exceder 45 caracteres';
        } else {
            // Verificar nombre único
            $escapedName = addslashes($data['servicio_nombre']);
            $condition = "servicio_nombre = '{$escapedName}'";
            if ($id) {
                $condition .= " AND id_servicio != " . (int)$id;
            }
            
            if ($this->count($condition) > 0) {
                $errors[] = 'Ya existe un servicio con ese nombre';
            }
        }

        // Validar descripción
        if (empty($data['servicio_descripcion'])) {
            $errors[] = 'La descripción del servicio es requerida';
        } elseif (strlen($data['servicio_descripcion']) > 400) {
            $errors[] = 'La descripción no puede exceder 400 caracteres';
        }

        // Validar precio
        if (!isset($data['servicio_precio']) || $data['servicio_precio'] < 0) {
            $errors[] = 'El precio debe ser mayor o igual a 0';
        } elseif ($data['servicio_precio'] > 999999) {
            $errors[] = 'El precio no puede exceder 999,999';
        }

        // Validar tipo de servicio
        if (empty($data['rela_tiposervicio'])) {
            $errors[] = 'Debe seleccionar un tipo de servicio';
        } else {
            // Verificar que el tipo de servicio existe y está activo
            $tipoExists = $this->db->query("SELECT COUNT(*) as count FROM tiposervicio WHERE id_tiposervicio = " . (int)$data['rela_tiposervicio'] . " AND tiposervicio_estado = 1");
            $result = $tipoExists->fetch_assoc();
            
            if ($result['count'] == 0) {
                $errors[] = 'El tipo de servicio seleccionado no es válido';
            }
        }

        return empty($errors) ? true : implode(', ', $errors);
    }

    /**
     * Obtener servicios con filtros y paginación según patrón estándar
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "s.servicio_estado IS NOT NULL";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['servicio_nombre'])) {
            $where .= " AND s.servicio_nombre LIKE ?";
            $params[] = '%' . $filters['servicio_nombre'] . '%';
        }
        
        if (!empty($filters['servicio_descripcion'])) {
            $where .= " AND s.servicio_descripcion LIKE ?";
            $params[] = '%' . $filters['servicio_descripcion'] . '%';
        }
        
        if (!empty($filters['precio_min'])) {
            $where .= " AND s.servicio_precio >= ?";
            $params[] = (float) $filters['precio_min'];
        }
        
        if (!empty($filters['precio_max'])) {
            $where .= " AND s.servicio_precio <= ?";
            $params[] = (float) $filters['precio_max'];
        }
        
        if (!empty($filters['rela_tiposervicio'])) {
            $where .= " AND s.rela_tiposervicio = ?";
            $params[] = (int) $filters['rela_tiposervicio'];
        }
        
        if (isset($filters['servicio_estado']) && $filters['servicio_estado'] !== '') {
            $where .= " AND s.servicio_estado = ?";
            $params[] = (int) $filters['servicio_estado'];
        }
        
        return $this->paginateWithDetailsAndParams($page, $perPage, $where, "s.servicio_nombre ASC", $params);
    }

    /**
     * Obtener todos los servicios con filtros para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "s.servicio_estado IS NOT NULL";
        $params = [];
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['servicio_nombre'])) {
            $where .= " AND s.servicio_nombre LIKE ?";
            $params[] = '%' . $filters['servicio_nombre'] . '%';
        }
        
        if (!empty($filters['servicio_descripcion'])) {
            $where .= " AND s.servicio_descripcion LIKE ?";
            $params[] = '%' . $filters['servicio_descripcion'] . '%';
        }
        
        if (!empty($filters['precio_min'])) {
            $where .= " AND s.servicio_precio >= ?";
            $params[] = (float) $filters['precio_min'];
        }
        
        if (!empty($filters['precio_max'])) {
            $where .= " AND s.servicio_precio <= ?";
            $params[] = (float) $filters['precio_max'];
        }
        
        if (!empty($filters['rela_tiposervicio'])) {
            $where .= " AND s.rela_tiposervicio = ?";
            $params[] = (int) $filters['rela_tiposervicio'];
        }
        
        if (isset($filters['servicio_estado']) && $filters['servicio_estado'] !== '') {
            $where .= " AND s.servicio_estado = ?";
            $params[] = (int) $filters['servicio_estado'];
        }
        
        // Query para contar total (para estadísticas)
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} s 
                     LEFT JOIN tiposervicio ts ON s.rela_tiposervicio = ts.id_tiposervicio 
                     WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener TODOS los registros (sin LIMIT)
        $dataSql = "SELECT s.*, ts.tiposervicio_descripcion
                    FROM {$this->table} s 
                    LEFT JOIN tiposervicio ts ON s.rela_tiposervicio = ts.id_tiposervicio 
                    WHERE $where 
                    ORDER BY s.servicio_nombre ASC";
        $dataResult = $this->queryWithParams($dataSql, $params);
        
        $data = [];
        while ($row = $dataResult->fetch_assoc()) {
            $data[] = $row;
        }
        
        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Obtener servicios con paginación incluyendo detalles del tipo, usando parámetros preparados
     */
    private function paginateWithDetailsAndParams($page = 1, $perPage = 10, $where = "s.servicio_estado IS NOT NULL", $orderBy = null, $params = [])
    {
        $offset = ($page - 1) * $perPage;
        $limit = (int) $perPage;
        
        // Query para contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} s 
                     LEFT JOIN tiposervicio ts ON s.rela_tiposervicio = ts.id_tiposervicio 
                     WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener registros con información del tipo
        $orderClause = $orderBy ? "ORDER BY $orderBy" : '';
        $dataSql = "SELECT s.*, ts.tiposervicio_descripcion
                    FROM {$this->table} s 
                    LEFT JOIN tiposervicio ts ON s.rela_tiposervicio = ts.id_tiposervicio 
                    WHERE $where 
                    $orderClause 
                    LIMIT $limit OFFSET $offset";
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
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new \Exception("Error ejecutando consulta: " . $stmt->error);
        }
        
        return $stmt->get_result();
    }

    /**
     * Obtener estadísticas de servicios
     */
    public function getStats()
    {
        // Conteos básicos
        $totalSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $activosSql = "SELECT COUNT(*) as activos FROM {$this->table} WHERE servicio_estado = 1";
        $inactivosSql = "SELECT COUNT(*) as inactivos FROM {$this->table} WHERE servicio_estado = 0";

        $total = $this->db->query($totalSql)->fetch_assoc()['total'];
        $activos = $this->db->query($activosSql)->fetch_assoc()['activos'];
        $inactivos = $this->db->query($inactivosSql)->fetch_assoc()['inactivos'];

        // Precios
        $preciosSql = "SELECT 
                        AVG(servicio_precio) as precio_promedio,
                        MIN(servicio_precio) as precio_minimo,
                        MAX(servicio_precio) as precio_maximo
                      FROM {$this->table} WHERE servicio_estado = 1";
        
        $precios = $this->db->query($preciosSql)->fetch_assoc();

        // Servicios por tipo
        $tiposSql = "SELECT ts.tiposervicio_nombre, COUNT(*) as cantidad
                     FROM {$this->table} s
                     JOIN tiposervicio ts ON s.rela_tiposervicio = ts.id_tiposervicio
                     WHERE s.servicio_estado = 1
                     GROUP BY ts.id_tiposervicio, ts.tiposervicio_nombre
                     ORDER BY cantidad DESC";
        
        $result = $this->db->query($tiposSql);
        $serviciosPorTipo = [];
        while ($row = $result->fetch_assoc()) {
            $serviciosPorTipo[] = $row;
        }

        return [
            'total' => (int)$total,
            'activos' => (int)$activos,
            'inactivos' => (int)$inactivos,
            'precio_promedio' => (float)($precios['precio_promedio'] ?? 0),
            'precio_minimo' => (float)($precios['precio_minimo'] ?? 0),
            'precio_maximo' => (float)($precios['precio_maximo'] ?? 0),
            'servicios_por_tipo' => $serviciosPorTipo
        ];
    }

    /**
     * Obtener servicio específico con sus relaciones
     */
    public function getServiceWithDetails($id)
    {
        $sql = "SELECT s.*, ts.tiposervicio_descripcion
                FROM {$this->table} s
                LEFT JOIN tiposervicio ts ON s.rela_tiposervicio = ts.id_tiposervicio
                WHERE s.{$this->primaryKey} = " . (int)$id;

        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }

    /**
     * Obtener tipos de servicio activos
     */
    public function getTiposServicio()
    {
        $sql = "SELECT * FROM tiposervicio WHERE tiposervicio_estado = 1 ORDER BY tiposervicio_nombre ASC";
        $result = $this->db->query($sql);
        
        $tipos = [];
        while ($row = $result->fetch_assoc()) {
            $tipos[] = $row;
        }
        
        return $tipos;
    }

    /**
     * Obtener consumos asociados al servicio
     */
    public function getConsumos($servicioId)
    {
        $sql = "SELECT c.*, r.reserva_numero, p.persona_nombre, p.persona_apellido
                FROM consumo c
                JOIN reserva r ON c.rela_reserva = r.id_reserva
                JOIN huesped_reserva hr ON r.id_reserva = hr.rela_reserva
                JOIN huesped h ON hr.rela_huesped = h.id_huesped
                JOIN persona p ON h.rela_persona = p.id_persona
                WHERE c.rela_servicio = " . (int)$servicioId . "
                ORDER BY c.id_consumo DESC
                LIMIT 50";

        $result = $this->db->query($sql);
        $consumos = [];
        
        while ($row = $result->fetch_assoc()) {
            $consumos[] = $row;
        }
        
        return $consumos;
    }

    /**
     * Verificar si el servicio está siendo usado
     */
    public function isInUse($id)
    {
        $consumosSql = "SELECT COUNT(*) as count FROM consumo WHERE rela_servicio = " . (int)$id;
        $result = $this->db->query($consumosSql);
        $count = $result->fetch_assoc()['count'];
        
        return $count > 0;
    }

    /**
     * Obtener servicios activos para select
     */
    public function getForSelect()
    {
        $sql = "SELECT s.id_servicio, s.servicio_nombre, s.servicio_precio, ts.tiposervicio_descripcion
                FROM {$this->table} s
                LEFT JOIN tiposervicio ts ON s.rela_tiposervicio = ts.id_tiposervicio
                WHERE s.servicio_estado = 1
                ORDER BY ts.tiposervicio_descripcion ASC, s.servicio_nombre ASC";

        $result = $this->db->query($sql);
        $servicios = [];
        
        while ($row = $result->fetch_assoc()) {
            $servicios[] = $row;
        }
        
        return $servicios;
    }

    /**
     * Obtener servicios disponibles para reservas (tipo 3 y estado activo)
     */
    public function getServiciosParaReservas()
    {
        $sql = "SELECT s.id_servicio, s.servicio_nombre, s.servicio_descripcion, s.servicio_precio, ts.tiposervicio_descripcion
                FROM {$this->table} s
                LEFT JOIN tiposervicio ts ON s.rela_tiposervicio = ts.id_tiposervicio
                WHERE s.rela_tiposervicio = 3 AND s.servicio_estado = 1
                ORDER BY s.servicio_nombre ASC";

        $result = $this->db->query($sql);
        $servicios = [];
        
        while ($row = $result->fetch_assoc()) {
            $servicios[] = $row;
        }
        
        return $servicios;
    }

    /**
     * Obtener servicios por tipo
     */
    public function getByTipo($tipoId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE rela_tiposervicio = " . (int)$tipoId . " 
                AND servicio_estado = 1 
                ORDER BY servicio_nombre ASC";

        $result = $this->db->query($sql);
        $servicios = [];
        
        while ($row = $result->fetch_assoc()) {
            $servicios[] = $row;
        }
        
        return $servicios;
    }

    /**
     * Buscar servicios por precio
     */
    public function getByPriceRange($minPrice, $maxPrice)
    {
        $sql = "SELECT s.*, ts.tiposervicio_nombre
                FROM {$this->table} s
                LEFT JOIN tiposervicio ts ON s.rela_tiposervicio = ts.id_tiposervicio
                WHERE s.servicio_precio >= " . (float)$minPrice . "
                AND s.servicio_precio <= " . (float)$maxPrice . "
                AND s.servicio_estado = 1
                ORDER BY s.servicio_precio ASC";

        $result = $this->db->query($sql);
        $servicios = [];
        
        while ($row = $result->fetch_assoc()) {
            $servicios[] = $row;
        }
        
        return $servicios;
    }
}