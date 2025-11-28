<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad CajaArqueo
 */
class CajaArqueo extends Model
{
    protected $table = 'cajaarqueo';
    protected $primaryKey = 'id_cajaarqueo';

    /**
     * Obtener arqueos con detalles del turno y caja
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Filtro por turno (nuevo - prioritario)
        if (!empty($filters['turno'])) {
            $where .= " AND ct.id_cajaturno = ?";
            $params[] = (int)$filters['turno'];
        }
        
        // Filtro por fecha
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND DATE(ca.cajaarqueo_fechahora) >= ?";
            $params[] = $filters['fecha_desde'];
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND DATE(ca.cajaarqueo_fechahora) <= ?";
            $params[] = $filters['fecha_hasta'];
        }
        
        // Filtro por caja
        if (!empty($filters['caja'])) {
            $where .= " AND c.id_caja = ?";
            $params[] = (int)$filters['caja'];
        }
        
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total
                     FROM cajaarqueo ca
                     INNER JOIN cajaturno ct ON ca.rela_cajaturno = ct.id_cajaturno
                     INNER JOIN caja c ON ct.rela_caja = c.id_caja
                     WHERE {$where}";
        
        $countResult = $this->query($countSql, $params);
        $totalRow = $countResult->fetch_assoc();
        $total = (int)$totalRow['total'];
        
        // Obtener registros
        $sql = "SELECT ca.*, 
                ct.cajaturno_fhapertura,
                ct.cajaturno_contadoinicial,
                ct.cajaturno_fhcierre,
                c.caja_descripcion,
                u.usuario_nombre,
                p.persona_denominacion
                FROM cajaarqueo ca
                INNER JOIN cajaturno ct ON ca.rela_cajaturno = ct.id_cajaturno
                INNER JOIN caja c ON ct.rela_caja = c.id_caja
                LEFT JOIN usuario u ON ct.rela_usuarioapertura = u.id_usuario
                LEFT JOIN persona p ON u.rela_persona = p.id_persona
                WHERE {$where}
                ORDER BY ca.cajaarqueo_fechahora DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        $result = $this->query($sql, $params);
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => ceil($total / $perPage),
            'per_page' => $perPage,
            'offset' => $offset,
            'limit' => $perPage
        ];
    }

    /**
     * Obtener todos los arqueos para exportación
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND DATE(ca.cajaarqueo_fechahora) >= ?";
            $params[] = $filters['fecha_desde'];
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND DATE(ca.cajaarqueo_fechahora) <= ?";
            $params[] = $filters['fecha_hasta'];
        }
        
        if (!empty($filters['caja'])) {
            $where .= " AND c.id_caja = ?";
            $params[] = (int)$filters['caja'];
        }
        
        $sql = "SELECT ca.*, 
                ct.cajaturno_fhapertura,
                ct.cajaturno_contadoinicial,
                ct.cajaturno_fhcierre,
                c.caja_descripcion,
                u.usuario_nombre,
                p.persona_denominacion
                FROM cajaarqueo ca
                INNER JOIN cajaturno ct ON ca.rela_cajaturno = ct.id_cajaturno
                INNER JOIN caja c ON ct.rela_caja = c.id_caja
                LEFT JOIN usuario u ON ct.rela_usuarioapertura = u.id_usuario
                LEFT JOIN persona p ON u.rela_persona = p.id_persona
                WHERE {$where}
                ORDER BY ca.cajaarqueo_fechahora DESC";
        
        $result = $this->query($sql, $params);
        
        $arqueos = [];
        while ($row = $result->fetch_assoc()) {
            $arqueos[] = $row;
        }
        
        return [
            'data' => $arqueos,
            'total' => count($arqueos)
        ];
    }

    /**
     * Guardar arqueo de caja
     */
    public function guardarArqueo($turnoId, $montoContado)
    {
        $sql = "INSERT INTO cajaarqueo (rela_cajaturno, cajaarqueo_fechahora, cajaarqueo_montocontado)
                VALUES (?, NOW(), ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('id', $turnoId, $montoContado);
        
        if ($stmt->execute()) {
            $arqueoId = $stmt->insert_id;
            $stmt->close();
            return $arqueoId;
        }
        
        $stmt->close();
        return false;
    }

    /**
     * Obtener el último arqueo de un turno
     */
    public function getUltimoArqueoTurno($turnoId)
    {
        $sql = "SELECT * FROM cajaarqueo 
                WHERE rela_cajaturno = ?
                ORDER BY cajaarqueo_fechahora DESC
                LIMIT 1";
        
        $result = $this->query($sql, [$turnoId]);
        return $result->fetch_assoc();
    }

    /**
     * Contar arqueos de un turno
     */
    public function contarArqueosTurno($turnoId)
    {
        $sql = "SELECT COUNT(*) as total FROM cajaarqueo WHERE rela_cajaturno = ?";
        $result = $this->query($sql, [$turnoId]);
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }
}
