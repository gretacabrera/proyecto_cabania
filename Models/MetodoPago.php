<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad MetodoPago
 */
class MetodoPago extends Model
{
    protected $table = 'metododepago';
    protected $primaryKey = 'id_metododepago';

    /**
     * Obtener métodos de pago activos
     */
    public function getActive()
    {
        return $this->findAll("metododepago_estado = 1", "metododepago_descripcion");
    }

    /**
     * Buscar método de pago por descripción
     */
    public function findByDescripcion($descripcion)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE metododepago_descripcion = ? 
                AND metododepago_estado = 1 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param('s', $descripcion);
        
        if (!$stmt->execute()) {
            return false;
        }
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Obtener métodos de pago con filtros y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['metododepago_descripcion'])) {
            $where .= " AND metododepago_descripcion LIKE ?";
            $params[] = '%' . $filters['metododepago_descripcion'] . '%';
        }
        
        if (isset($filters['metododepago_estado']) && $filters['metododepago_estado'] !== '') {
            $where .= " AND metododepago_estado = ?";
            $params[] = (int) $filters['metododepago_estado'];
        }
        
        return $this->paginateWithParams($page, $perPage, $where, "metododepago_descripcion ASC", $params);
    }

    /**
     * Obtener todos los métodos de pago con filtros para exportación (sin paginación)
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar los mismos filtros que getWithDetails
        if (!empty($filters['metododepago_descripcion'])) {
            $where .= " AND metododepago_descripcion LIKE ?";
            $params[] = '%' . $filters['metododepago_descripcion'] . '%';
        }
        
        if (isset($filters['metododepago_estado']) && $filters['metododepago_estado'] !== '') {
            $where .= " AND metododepago_estado = ?";
            $params[] = (int) $filters['metododepago_estado'];
        }
        
        // Query para contar total (para estadísticas)
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $where";
        $totalResult = $this->queryWithParams($countSql, $params);
        $totalRow = $totalResult->fetch_assoc();
        $total = (int) $totalRow['total'];
        
        // Query para obtener TODOS los registros (sin LIMIT)
        $dataSql = "SELECT * FROM {$this->table} WHERE $where ORDER BY metododepago_descripcion ASC";
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
     * Obtener métodos de pago con paginación usando parámetros preparados
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
     * Obtener estadísticas de un método de pago específico
     */
    public function getStatistics($metodoId)
    {
        $stats = [
            'total_pagos' => $this->getTotalPagos($metodoId),
            'monto_total' => $this->getMontoTotal($metodoId),
            'ultimo_uso' => $this->getUltimoUso($metodoId),
            'pagos_mes_actual' => $this->getPagosMesActual($metodoId)
        ];
        
        return $stats;
    }

    /**
     * Obtener número total de pagos realizados con este método
     */
    private function getTotalPagos($metodoId)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM pago 
                WHERE rela_metododepago = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $metodoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
    }

    /**
     * Obtener monto total de pagos realizados con este método
     */
    private function getMontoTotal($metodoId)
    {
        $sql = "SELECT SUM(pago_total) as total 
                FROM pago 
                WHERE rela_metododepago = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $metodoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (float)($row['total'] ?? 0);
    }

    /**
     * Obtener fecha del último uso del método de pago
     */
    private function getUltimoUso($metodoId)
    {
        $sql = "SELECT MAX(pago_fechahora) as ultimo_uso 
                FROM pago 
                WHERE rela_metododepago = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $metodoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['ultimo_uso'];
    }

    /**
     * Obtener número de pagos del mes actual
     */
    private function getPagosMesActual($metodoId)
    {
        $mesActual = date('Y-m');
        $inicioMes = $mesActual . '-01';
        $finMes = date('Y-m-t');
        
        $sql = "SELECT COUNT(*) as total 
                FROM pago 
                WHERE rela_metododepago = ? 
                AND DATE(pago_fechahora) BETWEEN ? AND ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iss', $metodoId, $inicioMes, $finMes);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int)$row['total'];
    }
}