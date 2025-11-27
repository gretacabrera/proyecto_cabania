<?php

namespace App\Models;

use App\Core\Model;

class ProductoMovimiento extends Model
{
    protected $table = 'productomovimiento';
    protected $primaryKey = 'id_productomovimiento';

    /**
     * Registrar movimiento de stock
     * 
     * @param int $productoId ID del producto
     * @param string $tipo Tipo de movimiento: 'E' = Entrada, 'S' = Salida, 'A' = Ajuste, 'C' = Corrección
     * @param int $cantidad Cantidad del movimiento
     * @param string $descripcion Descripción del movimiento
     * @return bool
     */
    public function registrarMovimiento($productoId, $tipo, $cantidad, $descripcion)
    {
        // Validar tipo de movimiento
        $tiposValidos = ['E', 'S', 'A', 'C'];
        $tipoUpper = strtoupper($tipo);
        
        if (!in_array($tipoUpper, $tiposValidos)) {
            throw new \Exception("Tipo de movimiento inválido. Use 'E' (Entrada), 'S' (Salida), 'A' (Ajuste) o 'C' (Corrección)");
        }
        
        $data = [
            'rela_producto' => $productoId,
            'productomovimiento_tipo' => $tipoUpper,
            'productomovimiento_cantidad' => $cantidad,
            'productomovimiento_descripcion' => $descripcion,
            'productomovimiento_estado' => 1
        ];

        return $this->create($data);
    }
    
    /**
     * Verifica si un consumo tiene movimiento de reactivación reciente
     * Retorna el tipo de reactivación: 'error', 'reintento' o null
     */
    public function verificarReactivacion($consumoId)
    {
        $sql = "SELECT productomovimiento_tipo, productomovimiento_descripcion 
                FROM {$this->table} 
                WHERE productomovimiento_descripcion LIKE ? 
                ORDER BY productomovimiento_fechahora DESC 
                LIMIT 1";
        
        $result = $this->query($sql, ["%Consumo #{$consumoId}%"]);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $descripcion = $row['productomovimiento_descripcion'];
            
            // Detectar tipo por palabras clave en descripción
            if (strpos($descripcion, 'Corrección de error') !== false) {
                return 'error';
            } elseif (strpos($descripcion, 'Reintento - Sin descuento') !== false) {
                return 'reintento';
            }
        }
        
        return null;
    }

    /**
     * Obtener movimientos de un producto específico
     * 
     * @param int $productoId ID del producto
     * @param int $limit Número de registros a retornar
     * @return array
     */
    public function getByProducto($productoId, $limit = null)
    {
        $sql = "SELECT pm.*, p.producto_nombre
                FROM productomovimiento pm
                INNER JOIN producto p ON pm.rela_producto = p.id_producto
                WHERE pm.rela_producto = ?
                ORDER BY pm.productomovimiento_fechahora DESC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $result = $this->query($sql, [$productoId, $limit]);
        } else {
            $result = $this->query($sql, [$productoId]);
        }

        $movimientos = [];
        while ($row = $result->fetch_assoc()) {
            $movimientos[] = $row;
        }

        return $movimientos;
    }

    /**
     * Obtener últimos movimientos generales
     * 
     * @param int $limit Número de registros a retornar
     * @return array
     */
    public function getRecientes($limit = 50)
    {
        $sql = "SELECT pm.*, p.producto_nombre
                FROM productomovimiento pm
                INNER JOIN producto p ON pm.rela_producto = p.id_producto
                WHERE pm.productomovimiento_estado = 1
                ORDER BY pm.productomovimiento_fechahora DESC
                LIMIT ?";
        
        $result = $this->query($sql, [$limit]);

        $movimientos = [];
        while ($row = $result->fetch_assoc()) {
            $movimientos[] = $row;
        }

        return $movimientos;
    }

    /**
     * Obtener historial de movimientos de un producto con paginación y filtros
     */
    public function getHistorialByProducto($productoId, $page = 1, $perPage = 10, $filters = [])
    {
        $where = "pm.rela_producto = ?";
        $params = [$productoId];
        
        // Aplicar filtros
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND DATE(pm.productomovimiento_fechahora) >= ?";
            $params[] = $filters['fecha_desde'];
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND DATE(pm.productomovimiento_fechahora) <= ?";
            $params[] = $filters['fecha_hasta'];
        }
        
        if (!empty($filters['tipo'])) {
            $where .= " AND pm.productomovimiento_tipo = ?";
            $params[] = $filters['tipo'];
        }
        
        if (isset($filters['cantidad_min']) && $filters['cantidad_min'] !== '') {
            $where .= " AND ABS(pm.productomovimiento_cantidad) >= ?";
            $params[] = (int) $filters['cantidad_min'];
        }
        
        if (isset($filters['cantidad_max']) && $filters['cantidad_max'] !== '') {
            $where .= " AND ABS(pm.productomovimiento_cantidad) <= ?";
            $params[] = (int) $filters['cantidad_max'];
        }
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} pm
                     LEFT JOIN producto p ON pm.rela_producto = p.id_producto
                     WHERE {$where}";
        
        $stmtCount = $this->db->prepare($countSql);
        $types = str_repeat('s', count($params));
        $stmtCount->bind_param($types, ...$params);
        $stmtCount->execute();
        $resultCount = $stmtCount->get_result();
        $totalRecords = $resultCount->fetch_assoc()['total'];
        
        // Obtener registros paginados
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT 
                    pm.*,
                    p.producto_nombre
                FROM {$this->table} pm
                LEFT JOIN producto p ON pm.rela_producto = p.id_producto
                WHERE {$where}
                ORDER BY pm.productomovimiento_fechahora DESC
                LIMIT ? OFFSET ?";
        
        $paramsWithLimit = array_merge($params, [$perPage, $offset]);
        $typesWithLimit = $types . 'ii';
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($typesWithLimit, ...$paramsWithLimit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return [
            'data' => $data,
            'total' => $totalRecords,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalRecords / $perPage),
            'offset' => $offset,
            'limit' => $perPage
        ];
    }

    /**
     * Obtener todos los movimientos de un producto para exportación
     */
    public function getAllHistorialByProducto($productoId, $filters = [])
    {
        $where = "pm.rela_producto = ?";
        $params = [$productoId];
        
        // Aplicar filtros
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND DATE(pm.productomovimiento_fechahora) >= ?";
            $params[] = $filters['fecha_desde'];
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND DATE(pm.productomovimiento_fechahora) <= ?";
            $params[] = $filters['fecha_hasta'];
        }
        
        if (!empty($filters['tipo'])) {
            $where .= " AND pm.productomovimiento_tipo = ?";
            $params[] = $filters['tipo'];
        }
        
        if (isset($filters['cantidad_min']) && $filters['cantidad_min'] !== '') {
            $where .= " AND ABS(pm.productomovimiento_cantidad) >= ?";
            $params[] = (int) $filters['cantidad_min'];
        }
        
        if (isset($filters['cantidad_max']) && $filters['cantidad_max'] !== '') {
            $where .= " AND ABS(pm.productomovimiento_cantidad) <= ?";
            $params[] = (int) $filters['cantidad_max'];
        }
        
        $sql = "SELECT 
                    pm.*,
                    p.producto_nombre
                FROM {$this->table} pm
                LEFT JOIN producto p ON pm.rela_producto = p.id_producto
                WHERE {$where}
                ORDER BY pm.productomovimiento_fechahora DESC";
        
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return [
            'data' => $data,
            'total' => count($data)
        ];
    }
}

