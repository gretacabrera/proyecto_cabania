<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad DevolucionDetalle
 * Gestiona líneas de detalle de devoluciones (productos devueltos)
 */
class DevolucionDetalle extends Model
{
    protected $table = 'devoluciondetalle';
    protected $primaryKey = 'id_devoluciondetalle';

    /**
     * Obtener detalles de devolución con información de productos
     */
    public function getByDevolucion($devolucionId)
    {
        $sql = "SELECT dd.*,
                       p.producto_nombre,
                       p.producto_foto,
                       p.producto_codigo,
                       c.consumo_cantidad as cantidad_original,
                       c.consumo_preciounitario as precio_original,
                       c.consumo_total as total_original,
                       c.id_consumo
                FROM devoluciondetalle dd
                INNER JOIN consumo c ON dd.rela_consumo = c.id_consumo
                INNER JOIN producto p ON c.rela_producto = p.id_producto
                WHERE dd.rela_devolucion = ?
                ORDER BY dd.id_devoluciondetalle";
        
        $result = $this->query($sql, [$devolucionId]);
        
        $detalles = [];
        while ($row = $result->fetch_assoc()) {
            $detalles[] = $row;
        }
        
        return $detalles;
    }

    /**
     * Obtener detalles con paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Filtro por devolución
        if (!empty($filters['rela_devolucion'])) {
            $where .= " AND dd.rela_devolucion = ?";
            $params[] = (int) $filters['rela_devolucion'];
        }
        
        // Filtro por estado
        if (isset($filters['devoluciondetalle_estado']) && $filters['devoluciondetalle_estado'] !== '') {
            $where .= " AND dd.devoluciondetalle_estado = ?";
            $params[] = (int) $filters['devoluciondetalle_estado'];
        }
        
        // Filtro por producto
        if (!empty($filters['producto_nombre'])) {
            $where .= " AND p.producto_nombre LIKE ?";
            $params[] = '%' . $filters['producto_nombre'] . '%';
        }
        
        $sql = "SELECT dd.*,
                       p.producto_nombre,
                       p.producto_foto,
                       c.consumo_cantidad as cantidad_original,
                       d.devolucion_fechahora,
                       d.devolucion_estado
                FROM devoluciondetalle dd
                INNER JOIN devolucion d ON dd.rela_devolucion = d.id_devolucion
                INNER JOIN consumo c ON dd.rela_consumo = c.id_consumo
                INNER JOIN producto p ON c.rela_producto = p.id_producto
                WHERE $where
                ORDER BY dd.id_devoluciondetalle DESC";
        
        return $this->paginateCustomQuery($sql, $where, $params, $page, $perPage);
    }

    /**
     * Paginación personalizada
     */
    protected function paginateCustomQuery($sql, $where, $params, $page, $perPage)
    {
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM devoluciondetalle dd
                     INNER JOIN devolucion d ON dd.rela_devolucion = d.id_devolucion
                     INNER JOIN consumo c ON dd.rela_consumo = c.id_consumo
                     INNER JOIN producto p ON c.rela_producto = p.id_producto
                     WHERE $where";
        
        $countStmt = $this->db->prepare($countSql);
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];
        
        // Obtener registros paginados
        $paginatedSql = $sql . " LIMIT $perPage OFFSET $offset";
        $stmt = $this->db->prepare($paginatedSql);
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        
        return [
            'data' => $records,
            'total' => $totalRecords,
            'current_page' => $page,
            'total_pages' => ceil($totalRecords / $perPage),
            'per_page' => $perPage,
            'offset' => $offset,
            'limit' => $perPage
        ];
    }

    /**
     * Buscar detalle con relaciones completas
     */
    public function findWithRelations($id)
    {
        $sql = "SELECT dd.*,
                       p.producto_nombre,
                       p.producto_foto,
                       p.producto_codigo,
                       c.consumo_cantidad as cantidad_original,
                       c.consumo_preciounitario as precio_original,
                       c.consumo_total as total_original,
                       d.devolucion_fechahora,
                       d.devolucion_total,
                       d.devolucion_estado
                FROM devoluciondetalle dd
                INNER JOIN devolucion d ON dd.rela_devolucion = d.id_devolucion
                INNER JOIN consumo c ON dd.rela_consumo = c.id_consumo
                INNER JOIN producto p ON c.rela_producto = p.id_producto
                WHERE dd.{$this->primaryKey} = ?";
        
        $result = $this->query($sql, [$id]);
        return $result->fetch_assoc();
    }

    /**
     * Validar cantidad a devolver
     */
    public function validarCantidad($consumoId, $cantidadDevolver)
    {
        // Obtener consumo original
        $consumoModel = new Consumo();
        $consumo = $consumoModel->find($consumoId);
        
        if (!$consumo) {
            throw new \Exception("Consumo no encontrado");
        }
        
        // Obtener cantidad ya devuelta
        $sql = "SELECT SUM(dd.devoluciondetalle_cantidad) as cantidad_devuelta
                FROM devoluciondetalle dd
                INNER JOIN devolucion d ON dd.rela_devolucion = d.id_devolucion
                WHERE dd.rela_consumo = ?
                AND d.devolucion_estado = 1";
        
        $result = $this->query($sql, [$consumoId]);
        $devuelto = $result->fetch_assoc();
        $cantidadDevuelta = $devuelto['cantidad_devuelta'] ?? 0;
        
        $cantidadDisponible = $consumo['consumo_cantidad'] - $cantidadDevuelta;
        
        if ($cantidadDevolver > $cantidadDisponible) {
            throw new \Exception("Cantidad a devolver ($cantidadDevolver) excede cantidad disponible ($cantidadDisponible)");
        }
        
        return [
            'cantidad_original' => $consumo['consumo_cantidad'],
            'cantidad_devuelta' => $cantidadDevuelta,
            'cantidad_disponible' => $cantidadDisponible,
            'valido' => true
        ];
    }

    /**
     * Crear detalle con validaciones
     */
    public function createWithValidation($data)
    {
        // Validar cantidad
        $this->validarCantidad($data['rela_consumo'], $data['devoluciondetalle_cantidad']);
        
        // Calcular total si no viene
        if (!isset($data['devoluciondetalle_total'])) {
            $data['devoluciondetalle_total'] = 
                $data['devoluciondetalle_preciounitario'] * $data['devoluciondetalle_cantidad'];
        }
        
        // Crear detalle
        return $this->create($data);
    }

    /**
     * Obtener estadísticas por producto devuelto
     */
    public function getProductosMasDevueltos($limit = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND d.devolucion_fechahora >= ?";
            $params[] = $filters['fecha_desde'];
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND d.devolucion_fechahora <= ?";
            $params[] = $filters['fecha_hasta'];
        }
        
        $sql = "SELECT p.id_producto,
                       p.producto_nombre,
                       p.producto_foto,
                       COUNT(dd.id_devoluciondetalle) as veces_devuelto,
                       SUM(dd.devoluciondetalle_cantidad) as cantidad_total,
                       SUM(dd.devoluciondetalle_total) as monto_total
                FROM devoluciondetalle dd
                INNER JOIN devolucion d ON dd.rela_devolucion = d.id_devolucion
                INNER JOIN consumo c ON dd.rela_consumo = c.id_consumo
                INNER JOIN producto p ON c.rela_producto = p.id_producto
                WHERE $where AND d.devolucion_estado = 1
                GROUP BY p.id_producto, p.producto_nombre, p.producto_foto
                ORDER BY cantidad_total DESC
                LIMIT ?";
        
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $types = str_repeat('s', count($params) - 1) . 'i';
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        
        return $productos;
    }

    /**
     * Cambiar estado de detalle
     */
    public function changeStatus($id, $status)
    {
        return $this->update($id, ['devoluciondetalle_estado' => $status]);
    }

    /**
     * Obtener cantidad devuelta de un consumo
     */
    public function getCantidadDevueltaByConsumo($consumoId)
    {
        $sql = "SELECT SUM(dd.devoluciondetalle_cantidad) as cantidad_devuelta
                FROM devoluciondetalle dd
                INNER JOIN devolucion d ON dd.rela_devolucion = d.id_devolucion
                WHERE dd.rela_consumo = ?
                AND d.devolucion_estado = 1";
        
        $result = $this->query($sql, [$consumoId]);
        $row = $result->fetch_assoc();
        
        return $row['cantidad_devuelta'] ?? 0;
    }

    /**
     * Verificar si consumo tiene devoluciones activas
     */
    public function consumoTieneDevoluciones($consumoId)
    {
        $sql = "SELECT COUNT(*) as count
                FROM devoluciondetalle dd
                INNER JOIN devolucion d ON dd.rela_devolucion = d.id_devolucion
                WHERE dd.rela_consumo = ?
                AND d.devolucion_estado = 1";
        
        $result = $this->query($sql, [$consumoId]);
        $row = $result->fetch_assoc();
        
        return $row['count'] > 0;
    }

    /**
     * Exportar todos los detalles sin paginación
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        if (!empty($filters['rela_devolucion'])) {
            $where .= " AND dd.rela_devolucion = ?";
            $params[] = (int) $filters['rela_devolucion'];
        }
        
        if (isset($filters['devoluciondetalle_estado']) && $filters['devoluciondetalle_estado'] !== '') {
            $where .= " AND dd.devoluciondetalle_estado = ?";
            $params[] = (int) $filters['devoluciondetalle_estado'];
        }
        
        $sql = "SELECT dd.*,
                       p.producto_nombre,
                       c.consumo_cantidad as cantidad_original,
                       d.devolucion_fechahora
                FROM devoluciondetalle dd
                INNER JOIN devolucion d ON dd.rela_devolucion = d.id_devolucion
                INNER JOIN consumo c ON dd.rela_consumo = c.id_consumo
                INNER JOIN producto p ON c.rela_producto = p.id_producto
                WHERE $where
                ORDER BY dd.id_devoluciondetalle DESC";
        
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $detalles = [];
        while ($row = $result->fetch_assoc()) {
            $detalles[] = $row;
        }
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM devoluciondetalle dd
                     INNER JOIN devolucion d ON dd.rela_devolucion = d.id_devolucion
                     INNER JOIN consumo c ON dd.rela_consumo = c.id_consumo
                     INNER JOIN producto p ON c.rela_producto = p.id_producto
                     WHERE $where";
        
        $countStmt = $this->db->prepare($countSql);
        if (!empty($params)) {
            $countStmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $total = $countResult->fetch_assoc()['total'];
        
        return [
            'data' => $detalles,
            'total' => $total
        ];
    }
}
