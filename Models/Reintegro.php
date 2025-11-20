<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Reintegro
 * Gestiona reembolsos/reintegros a clientes por cancelaciones de reservas
 */
class Reintegro extends Model
{
    protected $table = 'reintegro';
    protected $primaryKey = 'id_reintegro';

    /**
     * Obtener reintegros con detalles y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['reintegro_dni'])) {
            $where .= " AND r.reintegro_dni LIKE ?";
            $params[] = '%' . $filters['reintegro_dni'] . '%';
        }
        
        if (!empty($filters['reintegro_titular'])) {
            $where .= " AND r.reintegro_titular LIKE ?";
            $params[] = '%' . $filters['reintegro_titular'] . '%';
        }
        
        if (!empty($filters['rela_reserva'])) {
            $where .= " AND r.rela_reserva = ?";
            $params[] = (int) $filters['rela_reserva'];
        }
        
        if (isset($filters['reintegro_estado']) && $filters['reintegro_estado'] !== '') {
            $where .= " AND r.reintegro_estado = ?";
            $params[] = (int) $filters['reintegro_estado'];
        }
        
        if (!empty($filters['monto_min'])) {
            $where .= " AND r.reintegro_monto >= ?";
            $params[] = (float) $filters['monto_min'];
        }
        
        if (!empty($filters['monto_max'])) {
            $where .= " AND r.reintegro_monto <= ?";
            $params[] = (float) $filters['monto_max'];
        }
        
        $sql = "SELECT r.*,
                       res.reserva_fhinicio,
                       res.reserva_fhfin,
                       c.cabania_nombre,
                       er.estadoreserva_descripcion
                FROM reintegro r
                INNER JOIN reserva res ON r.rela_reserva = res.id_reserva
                INNER JOIN cabania c ON res.rela_cabania = c.id_cabania
                INNER JOIN estadoreserva er ON res.rela_estadoreserva = er.id_estadoreserva
                WHERE $where
                ORDER BY r.id_reintegro DESC";
        
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
                     FROM reintegro r
                     INNER JOIN reserva res ON r.rela_reserva = res.id_reserva
                     INNER JOIN cabania c ON res.rela_cabania = c.id_cabania
                     INNER JOIN estadoreserva er ON res.rela_estadoreserva = er.id_estadoreserva
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
     * Buscar reintegro con datos completos
     */
    public function findWithRelations($id)
    {
        $sql = "SELECT r.*,
                       res.reserva_fhinicio,
                       res.reserva_fhfin,
                       res.reserva_fhreserva,
                       c.cabania_nombre,
                       c.cabania_capacidad,
                       er.estadoreserva_descripcion
                FROM reintegro r
                INNER JOIN reserva res ON r.rela_reserva = res.id_reserva
                INNER JOIN cabania c ON res.rela_cabania = c.id_cabania
                INNER JOIN estadoreserva er ON res.rela_estadoreserva = er.id_estadoreserva
                WHERE r.{$this->primaryKey} = ?";
        
        $result = $this->query($sql, [$id]);
        return $result->fetch_assoc();
    }

    /**
     * Buscar reintegro por reserva
     */
    public function findByReserva($reservaId)
    {
        $sql = "SELECT r.*,
                       res.reserva_fhinicio,
                       res.reserva_fhfin,
                       c.cabania_nombre
                FROM reintegro r
                INNER JOIN reserva res ON r.rela_reserva = res.id_reserva
                INNER JOIN cabania c ON res.rela_cabania = c.id_cabania
                WHERE r.rela_reserva = ?
                AND r.reintegro_estado = 1
                LIMIT 1";
        
        $result = $this->query($sql, [$reservaId]);
        return $result->fetch_assoc();
    }

    /**
     * Calcular monto de reintegro basado en parámetros del sistema
     */
    public function calcularMontoReintegro($reservaId)
    {
        $parametrosModel = new ParametroGeneral();
        
        // Obtener reserva
        $reservaModel = new Reserva();
        $reserva = $reservaModel->find($reservaId);
        
        if (!$reserva) {
            throw new \Exception("Reserva no encontrada");
        }
        
        // Obtener porcentaje de reintegro del parámetro general
        $porcentajeReintegro = $parametrosModel->getPorcentajeReintegro();
        
        // Calcular total de la reserva (suma de pagos realizados)
        $pagoModel = new Pago();
        $totalPagado = $pagoModel->getTotalPagadoReserva($reservaId);
        
        // Calcular monto de reintegro
        $montoReintegro = $parametrosModel->calcularMontoReintegro($totalPagado);
        
        return [
            'monto_pagado' => $totalPagado,
            'porcentaje_reintegro' => $porcentajeReintegro,
            'monto_reintegro' => $montoReintegro
        ];
    }

    /**
     * Validar si reserva puede tener reintegro
     */
    public function validarReintegro($reservaId)
    {
        $parametrosModel = new ParametroGeneral();
        $reservaModel = new Reserva();
        
        $reserva = $reservaModel->find($reservaId);
        
        if (!$reserva) {
            throw new \Exception("Reserva no encontrada");
        }
        
        // Validar margen de horas
        $validacionMargen = $parametrosModel->validarMargenReintegro($reserva['reserva_fhreserva']);
        
        if (!$validacionMargen['valido']) {
            return [
                'valido' => false,
                'mensaje' => "Fuera del margen de tiempo permitido para reintegro ({$validacionMargen['horas_margen']} horas)",
                'detalles' => $validacionMargen
            ];
        }
        
        // Validar si ya existe reintegro para esta reserva
        $reintegroExistente = $this->findByReserva($reservaId);
        
        if ($reintegroExistente) {
            return [
                'valido' => false,
                'mensaje' => 'Ya existe un reintegro para esta reserva',
                'reintegro' => $reintegroExistente
            ];
        }
        
        return [
            'valido' => true,
            'mensaje' => 'Reserva válida para reintegro',
            'detalles' => $validacionMargen
        ];
    }

    /**
     * Crear reintegro con validaciones
     */
    public function createWithValidation($data)
    {
        // Validar que la reserva pueda tener reintegro
        $validacion = $this->validarReintegro($data['rela_reserva']);
        
        if (!$validacion['valido']) {
            throw new \Exception($validacion['mensaje']);
        }
        
        // Calcular monto si no viene en los datos
        if (!isset($data['reintegro_monto']) || empty($data['reintegro_monto'])) {
            $calculo = $this->calcularMontoReintegro($data['rela_reserva']);
            $data['reintegro_monto'] = $calculo['monto_reintegro'];
        }
        
        // Estado por defecto: pendiente (1)
        if (!isset($data['reintegro_estado'])) {
            $data['reintegro_estado'] = 1;
        }
        
        return $this->create($data);
    }

    /**
     * Validar CBU argentino
     */
    public function validarCBU($cbu)
    {
        // CBU debe tener exactamente 22 dígitos
        $cbuLimpio = preg_replace('/[^0-9]/', '', $cbu);
        
        if (strlen($cbuLimpio) != 22) {
            return [
                'valido' => false,
                'mensaje' => 'CBU debe tener exactamente 22 dígitos'
            ];
        }
        
        // Validación básica de formato
        if (!preg_match('/^\d{22}$/', $cbuLimpio)) {
            return [
                'valido' => false,
                'mensaje' => 'CBU debe contener solo números'
            ];
        }
        
        return [
            'valido' => true,
            'mensaje' => 'CBU válido',
            'cbu_formateado' => $this->formatearCBU($cbuLimpio)
        ];
    }

    /**
     * Formatear CBU para visualización
     */
    public function formatearCBU($cbu)
    {
        $cbuLimpio = preg_replace('/[^0-9]/', '', $cbu);
        
        if (strlen($cbuLimpio) == 22) {
            return substr($cbuLimpio, 0, 8) . ' ' . 
                   substr($cbuLimpio, 8, 6) . ' ' . 
                   substr($cbuLimpio, 14, 8);
        }
        
        return $cbu;
    }

    /**
     * Cambiar estado de reintegro
     */
    public function changeStatus($id, $status)
    {
        return $this->update($id, ['reintegro_estado' => $status]);
    }

    /**
     * Exportar todos sin paginación
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        if (!empty($filters['reintegro_dni'])) {
            $where .= " AND r.reintegro_dni LIKE ?";
            $params[] = '%' . $filters['reintegro_dni'] . '%';
        }
        
        if (isset($filters['reintegro_estado']) && $filters['reintegro_estado'] !== '') {
            $where .= " AND r.reintegro_estado = ?";
            $params[] = (int) $filters['reintegro_estado'];
        }
        
        $sql = "SELECT r.*,
                       res.reserva_fhinicio,
                       res.reserva_fhfin,
                       c.cabania_nombre,
                       er.estadoreserva_descripcion
                FROM reintegro r
                INNER JOIN reserva res ON r.rela_reserva = res.id_reserva
                INNER JOIN cabania c ON res.rela_cabania = c.id_cabania
                INNER JOIN estadoreserva er ON res.rela_estadoreserva = er.id_estadoreserva
                WHERE $where
                ORDER BY r.id_reintegro DESC";
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM reintegro r
                     INNER JOIN reserva res ON r.rela_reserva = res.id_reserva
                     WHERE $where";
        
        $countStmt = $this->db->prepare($countSql);
        if (!empty($params)) {
            $countStmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $total = $countResult->fetch_assoc()['total'];
        
        // Obtener registros
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reintegros = [];
        while ($row = $result->fetch_assoc()) {
            $reintegros[] = $row;
        }
        
        return [
            'data' => $reintegros,
            'total' => $total
        ];
    }

    /**
     * Obtener estadísticas de reintegros
     */
    public function getEstadisticas($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND res.reserva_fhreserva >= ?";
            $params[] = $filters['fecha_desde'];
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND res.reserva_fhreserva <= ?";
            $params[] = $filters['fecha_hasta'];
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_reintegros,
                    SUM(r.reintegro_monto) as monto_total,
                    AVG(r.reintegro_monto) as promedio_reintegro,
                    MAX(r.reintegro_monto) as monto_maximo,
                    MIN(r.reintegro_monto) as monto_minimo,
                    COUNT(CASE WHEN r.reintegro_estado = 1 THEN 1 END) as pendientes,
                    COUNT(CASE WHEN r.reintegro_estado = 2 THEN 1 END) as procesados,
                    COUNT(CASE WHEN r.reintegro_estado = 0 THEN 1 END) as anulados
                FROM reintegro r
                INNER JOIN reserva res ON r.rela_reserva = res.id_reserva
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
     * Obtener reintegros pendientes
     */
    public function getPendientes()
    {
        $sql = "SELECT r.*,
                       res.reserva_fhinicio,
                       res.reserva_fhfin,
                       c.cabania_nombre
                FROM reintegro r
                INNER JOIN reserva res ON r.rela_reserva = res.id_reserva
                INNER JOIN cabania c ON res.rela_cabania = c.id_cabania
                WHERE r.reintegro_estado = 1
                ORDER BY r.id_reintegro ASC";
        
        $result = $this->db->query($sql);
        
        $reintegros = [];
        while ($row = $result->fetch_assoc()) {
            $reintegros[] = $row;
        }
        
        return $reintegros;
    }
}
