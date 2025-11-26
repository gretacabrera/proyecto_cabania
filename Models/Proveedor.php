<?php

namespace App\Models;

use App\Core\Model;

class Proveedor extends Model
{
    protected $table = 'proveedor';
    protected $primaryKey = 'id_proveedor';

    /**
     * Obtener proveedores con detalles y paginación
     */
    public function getWithDetails($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];

        // Filtro por denominación
        if (!empty($filters['denominacion'])) {
            $where .= " AND p.persona_denominacion LIKE ?";
            $params[] = '%' . $filters['denominacion'] . '%';
        }

        // Filtro por CUIT
        if (!empty($filters['cuit'])) {
            $where .= " AND pj.personajuridica_cuit LIKE ?";
            $params[] = '%' . $filters['cuit'] . '%';
        }

        // Filtro por estado
        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where .= " AND pr.proveedor_estado = ?";
            $params[] = (int) $filters['estado'];
        }

        // Construir SQL con subqueries para contactos
        $sql = "SELECT 
                    pr.id_proveedor,
                    pr.proveedor_estado,
                    p.persona_denominacion,
                    p.persona_direccion,
                    pj.personajuridica_cuit,
                    (SELECT c.contacto_descripcion 
                     FROM contacto c 
                     WHERE c.rela_persona = p.id_persona 
                     AND c.rela_tipocontacto = 1 
                     AND c.contacto_estado = 1 
                     LIMIT 1) as contacto_correo,
                    (SELECT c.contacto_descripcion 
                     FROM contacto c 
                     WHERE c.rela_persona = p.id_persona 
                     AND c.rela_tipocontacto = 2 
                     AND c.contacto_estado = 1 
                     LIMIT 1) as contacto_telefono
                FROM {$this->table} pr
                INNER JOIN persona p ON pr.rela_persona = p.id_persona
                INNER JOIN personajuridica pj ON p.rela_personajuridica = pj.id_personajuridica
                WHERE $where
                ORDER BY p.persona_denominacion ASC";

        // Preparar consulta con MySQLi
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        } else {
            $result = $this->db->query($sql);
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        // Contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} pr
                     INNER JOIN persona p ON pr.rela_persona = p.id_persona
                     INNER JOIN personajuridica pj ON p.rela_personajuridica = pj.id_personajuridica
                     WHERE $where";
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmtCount = $this->db->prepare($countSql);
            $stmtCount->bind_param($types, ...$params);
            $stmtCount->execute();
            $resultCount = $stmtCount->get_result();
            $totalRow = $resultCount->fetch_assoc();
        } else {
            $resultCount = $this->db->query($countSql);
            $totalRow = $resultCount->fetch_assoc();
        }
        $total = (int) $totalRow['total'];

        // Paginación manual
        $offset = ($page - 1) * $perPage;
        $pagedData = array_slice($data, $offset, $perPage);

        return [
            'data' => $pagedData,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => ceil($total / $perPage),
            'per_page' => $perPage,
            'offset' => $offset,
            'limit' => $perPage
        ];
    }

    /**
     * Obtener todos los proveedores para exportación
     */
    public function getAllWithDetailsForExport($filters = [])
    {
        $where = "1=1";
        $params = [];

        if (!empty($filters['denominacion'])) {
            $where .= " AND p.persona_denominacion LIKE ?";
            $params[] = '%' . $filters['denominacion'] . '%';
        }

        if (!empty($filters['cuit'])) {
            $where .= " AND pj.personajuridica_cuit LIKE ?";
            $params[] = '%' . $filters['cuit'] . '%';
        }

        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where .= " AND pr.proveedor_estado = ?";
            $params[] = (int) $filters['estado'];
        }

        $sql = "SELECT 
                    pr.id_proveedor,
                    pr.proveedor_estado,
                    p.persona_denominacion,
                    p.persona_direccion,
                    pj.personajuridica_cuit,
                    (SELECT c.contacto_descripcion 
                     FROM contacto c 
                     WHERE c.rela_persona = p.id_persona 
                     AND c.rela_tipocontacto = 1 
                     AND c.contacto_estado = 1 
                     LIMIT 1) as contacto_correo,
                    (SELECT c.contacto_descripcion 
                     FROM contacto c 
                     WHERE c.rela_persona = p.id_persona 
                     AND c.rela_tipocontacto = 2 
                     AND c.contacto_estado = 1 
                     LIMIT 1) as contacto_telefono
                FROM {$this->table} pr
                INNER JOIN persona p ON pr.rela_persona = p.id_persona
                INNER JOIN personajuridica pj ON p.rela_personajuridica = pj.id_personajuridica
                WHERE $where
                ORDER BY p.persona_denominacion ASC";

        // Ejecutar con MySQLi
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        } else {
            $result = $this->db->query($sql);
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return [
            'data' => $data,
            'total' => count($data)
        ];
    }

    /**
     * Obtener proveedor completo
     */
    public function getProveedorCompleto($id)
    {
        $sql = "SELECT 
                    pr.*,
                    p.id_persona,
                    p.persona_denominacion,
                    p.persona_direccion,
                    p.rela_personajuridica,
                    pj.id_personajuridica,
                    pj.personajuridica_cuit,
                    (SELECT c.id_contacto 
                     FROM contacto c 
                     WHERE c.rela_persona = p.id_persona 
                     AND c.rela_tipocontacto = 1 
                     AND c.contacto_estado = 1 
                     LIMIT 1) as id_contacto_correo,
                    (SELECT c.contacto_descripcion 
                     FROM contacto c 
                     WHERE c.rela_persona = p.id_persona 
                     AND c.rela_tipocontacto = 1 
                     AND c.contacto_estado = 1 
                     LIMIT 1) as contacto_correo,
                    (SELECT c.id_contacto 
                     FROM contacto c 
                     WHERE c.rela_persona = p.id_persona 
                     AND c.rela_tipocontacto = 2 
                     AND c.contacto_estado = 1 
                     LIMIT 1) as id_contacto_telefono,
                    (SELECT c.contacto_descripcion 
                     FROM contacto c 
                     WHERE c.rela_persona = p.id_persona 
                     AND c.rela_tipocontacto = 2 
                     AND c.contacto_estado = 1 
                     LIMIT 1) as contacto_telefono
                FROM {$this->table} pr
                INNER JOIN persona p ON pr.rela_persona = p.id_persona
                INNER JOIN personajuridica pj ON p.rela_personajuridica = pj.id_personajuridica
                WHERE pr.id_proveedor = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Obtener estadísticas del proveedor
     */
    public function getStatistics($id)
    {
        // Retornar valores por defecto ya que no hay tabla compra implementada
        return [
            'total_compras' => 0,
            'total_gastado' => 0,
            'ultima_compra' => null,
            'productos_top' => []
        ];
    }

    /**
     * Obtener proveedores activos con información de contacto
     */
    public function getProveedoresActivos()
    {
        $sql = "SELECT 
                    pr.id_proveedor,
                    p.persona_denominacion,
                    pj.personajuridica_cuit,
                    (SELECT c.contacto_descripcion 
                     FROM contacto c 
                     WHERE c.rela_persona = p.id_persona 
                     AND c.rela_tipocontacto = 1 
                     AND c.contacto_estado = 1 
                     LIMIT 1) as contacto_correo
                FROM proveedor pr
                INNER JOIN persona p ON pr.rela_persona = p.id_persona
                LEFT JOIN personajuridica pj ON p.rela_personajuridica = pj.id_personajuridica
                WHERE pr.proveedor_estado = 1
                ORDER BY p.persona_denominacion ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $proveedores = [];
        while ($row = $result->fetch_assoc()) {
            $proveedores[] = $row;
        }
        
        return $proveedores;
    }

    /**
     * Iniciar transacción
     */
    public function beginTransaction()
    {
        $this->db->beginTransaction();
    }

    /**
     * Confirmar transacción
     */
    public function commit()
    {
        $this->db->commit();
    }

    /**
     * Revertir transacción
     */
    public function rollback()
    {
        $this->db->rollback();
    }
}
