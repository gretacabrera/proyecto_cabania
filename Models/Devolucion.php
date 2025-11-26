<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Devolucion
 * Gestiona devoluciones/reembolsos de productos consumidos
 */
class Devolucion extends Model
{
    protected $table = 'devolucion';
    protected $primaryKey = 'id_devolucion';

    /**
     * Obtener devoluciones con detalles y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND d.devolucion_fechahora >= ?";
            $params[] = $filters['fecha_desde'];
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND d.devolucion_fechahora <= ?";
            $params[] = $filters['fecha_hasta'];
        }
        
        if (isset($filters['devolucion_estado']) && $filters['devolucion_estado'] !== '') {
            $where .= " AND d.devolucion_estado = ?";
            $params[] = (int) $filters['devolucion_estado'];
        }
        
        if (!empty($filters['monto_min'])) {
            $where .= " AND d.devolucion_total >= ?";
            $params[] = (float) $filters['monto_min'];
        }
        
        if (!empty($filters['monto_max'])) {
            $where .= " AND d.devolucion_total <= ?";
            $params[] = (float) $filters['monto_max'];
        }
        
        $sql = "SELECT d.*,
                       COUNT(dd.id_devoluciondetalle) as total_items
                FROM devolucion d
                LEFT JOIN devoluciondetalle dd ON d.id_devolucion = dd.rela_devolucion
                WHERE $where
                GROUP BY d.id_devolucion
                ORDER BY d.devolucion_fechahora DESC";
        
        return $this->paginateCustomQuery($sql, $where, $params, $page, $perPage);
    }

    /**
     * Paginación personalizada con query complejo
     */
    protected function paginateCustomQuery($sql, $where, $params, $page, $perPage)
    {
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        $countSql = "SELECT COUNT(DISTINCT d.id_devolucion) as total 
                     FROM devolucion d
                     LEFT JOIN devoluciondetalle dd ON d.id_devolucion = dd.rela_devolucion
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
     * Obtener todas las devoluciones para exportación sin paginación
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND d.devolucion_fechahora >= ?";
            $params[] = $filters['fecha_desde'];
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND d.devolucion_fechahora <= ?";
            $params[] = $filters['fecha_hasta'];
        }
        
        if (isset($filters['devolucion_estado']) && $filters['devolucion_estado'] !== '') {
            $where .= " AND d.devolucion_estado = ?";
            $params[] = (int) $filters['devolucion_estado'];
        }
        
        if (!empty($filters['monto_min'])) {
            $where .= " AND d.devolucion_total >= ?";
            $params[] = (float) $filters['monto_min'];
        }
        
        if (!empty($filters['monto_max'])) {
            $where .= " AND d.devolucion_total <= ?";
            $params[] = (float) $filters['monto_max'];
        }
        
        $sql = "SELECT d.*,
                       COUNT(dd.id_devoluciondetalle) as total_items
                FROM devolucion d
                LEFT JOIN devoluciondetalle dd ON d.id_devolucion = dd.rela_devolucion
                WHERE $where
                GROUP BY d.id_devolucion
                ORDER BY d.devolucion_fechahora DESC";
        
        // Contar total para estadísticas
        $countSql = "SELECT COUNT(DISTINCT d.id_devolucion) as total 
                     FROM devolucion d
                     LEFT JOIN devoluciondetalle dd ON d.id_devolucion = dd.rela_devolucion
                     WHERE $where";
        
        $countStmt = $this->db->prepare($countSql);
        if (!empty($params)) {
            $countStmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $total = $countResult->fetch_assoc()['total'];
        
        // Obtener registros sin paginación
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $devoluciones = [];
        while ($row = $result->fetch_assoc()) {
            $devoluciones[] = $row;
        }
        
        return [
            'data' => $devoluciones,
            'total' => $total
        ];
    }

    /**
     * Buscar devolución con todos sus detalles
     */
    public function findWithRelations($id)
    {
        $sql = "SELECT d.*,
                       COUNT(dd.id_devoluciondetalle) as total_items
                FROM devolucion d
                LEFT JOIN devoluciondetalle dd ON d.id_devolucion = dd.rela_devolucion
                WHERE d.{$this->primaryKey} = ?
                GROUP BY d.id_devolucion";
        
        $result = $this->query($sql, [$id]);
        return $result->fetch_assoc();
    }

    /**
     * Obtener detalles de una devolución específica
     */
    public function getDevolucionDetalles($devolucionId)
    {
        $sql = "SELECT dd.*,
                       p.producto_nombre,
                       p.producto_foto,
                       c.consumo_cantidad as cantidad_original,
                       c.consumo_preciounitario as precio_original
                FROM devoluciondetalle dd
                INNER JOIN consumo c ON dd.rela_consumo = c.id_consumo
                INNER JOIN producto p ON c.rela_producto = p.id_producto
                WHERE dd.rela_devolucion = ?
                AND dd.devoluciondetalle_estado = 1
                ORDER BY dd.id_devoluciondetalle";
        
        $result = $this->query($sql, [$devolucionId]);
        
        $detalles = [];
        while ($row = $result->fetch_assoc()) {
            $detalles[] = $row;
        }
        
        return $detalles;
    }

    /**
     * Crear devolución con sus detalles
     */
    public function createWithDetails($devolucionData, $detalles)
    {
        try {
            $this->db->begin_transaction();
            
            // Crear devolución principal
            $devolucionId = $this->create($devolucionData);
            
            // Crear detalles
            $devolucionDetalleModel = new DevolucionDetalle();
            foreach ($detalles as $detalle) {
                $detalle['rela_devolucion'] = $devolucionId;
                $devolucionDetalleModel->create($detalle);
            }
            
            $this->db->commit();
            return $devolucionId;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Obtener estadísticas de devoluciones
     */
    public function getStatistics($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND devolucion_fechahora >= ?";
            $params[] = $filters['fecha_desde'];
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND devolucion_fechahora <= ?";
            $params[] = $filters['fecha_hasta'];
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_devoluciones,
                    SUM(devolucion_total) as monto_total,
                    SUM(devolucion_cantidadproductos) as productos_devueltos,
                    AVG(devolucion_total) as promedio_devolucion,
                    COUNT(CASE WHEN devolucion_estado = 1 THEN 1 END) as devoluciones_activas,
                    COUNT(CASE WHEN devolucion_estado = 0 THEN 1 END) as devoluciones_anuladas
                FROM devolucion
                WHERE $where";
        
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Cambiar estado de devolución
     */
    public function changeStatus($id, $status)
    {
        return $this->update($id, ['devolucion_estado' => $status]);
    }

    /**
     * Obtener devoluciones pendientes de procesamiento
     */
    public function getPendientes()
    {
        $sql = "SELECT d.*,
                       COUNT(dd.id_devoluciondetalle) as total_items
                FROM devolucion d
                LEFT JOIN devoluciondetalle dd ON d.id_devolucion = dd.rela_devolucion
                WHERE d.devolucion_estado = 1
                GROUP BY d.id_devolucion
                ORDER BY d.devolucion_fechahora ASC";
        
        $result = $this->db->query($sql);
        
        $devoluciones = [];
        while ($row = $result->fetch_assoc()) {
            $devoluciones[] = $row;
        }
        
        return $devoluciones;
    }

    /**
     * Calcular total de devolución desde detalles
     */
    public function recalcularTotal($devolucionId)
    {
        $sql = "SELECT SUM(devoluciondetalle_total) as total,
                       SUM(devoluciondetalle_cantidad) as cantidad
                FROM devoluciondetalle
                WHERE rela_devolucion = ?
                AND devoluciondetalle_estado = 1";
        
        $result = $this->query($sql, [$devolucionId]);
        $totales = $result->fetch_assoc();
        
        // Actualizar devolución con totales recalculados
        $this->update($devolucionId, [
            'devolucion_total' => $totales['total'] ?? 0,
            'devolucion_cantidadproductos' => $totales['cantidad'] ?? 0
        ]);
        
        return $totales;
    }

    /**
     * Validar si consumo ya tiene devolución
     */
    public function consumoTieneDevolucion($consumoId)
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
}
