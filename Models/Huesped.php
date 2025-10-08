<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Huesped
 */
class Huesped extends Model
{
    protected $table = 'huesped';
    protected $primaryKey = 'id_huesped';

    /**
     * Obtener huéspedes activos
     */
    public function getActive()
    {
        return $this->findAll("huesped_estado = 1");
    }

    /**
     * Obtener huésped por persona
     */
    public function findByPersona($personaId)
    {
        return $this->findWhere("rela_persona = ? AND huesped_estado = 1", [$personaId]);
    }

    /**
     * Obtener huésped con información de persona
     */
    public function findWithPersona($id)
    {
        $sql = "SELECT h.*, p.persona_nombre, p.persona_apellido, p.persona_fechanac, p.persona_direccion
                FROM {$this->table} h
                INNER JOIN persona p ON h.rela_persona = p.id_persona
                WHERE h.{$this->primaryKey} = ?";
        
        $result = $this->query($sql, [$id]);
        return $result->fetch_assoc();
    }

    /**
     * Obtener huéspedes con información de persona (paginado)
     */
    public function getWithPersonaPaginated($page = 1, $perPage = 10, $search = '')
    {
        $offset = ($page - 1) * $perPage;
        $baseWhere = "h.huesped_estado = 1";
        $params = [];
        
        if ($search) {
            $searchPattern = '%' . $search . '%';
            $baseWhere .= " AND (p.persona_nombre LIKE ? OR p.persona_apellido LIKE ?)";
            $params = [$searchPattern, $searchPattern];
        }
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} h
                     INNER JOIN persona p ON h.rela_persona = p.id_persona
                     WHERE $baseWhere";
        
        if (!empty($params)) {
            $countResult = $this->query($countSql, $params);
        } else {
            $countResult = $this->db->query($countSql);
        }
        $totalRecords = $countResult->fetch_assoc()['total'];
        
        // Obtener registros
        $sql = "SELECT h.*, p.persona_nombre, p.persona_apellido, p.persona_fechanac, p.persona_direccion
                FROM {$this->table} h
                INNER JOIN persona p ON h.rela_persona = p.id_persona
                WHERE $baseWhere
                ORDER BY p.persona_apellido, p.persona_nombre
                LIMIT ? OFFSET ?";
        
        $allParams = array_merge($params, [$perPage, $offset]);
        $result = $this->query($sql, $allParams);
        
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        
        return [
            'data' => $records,
            'total' => $totalRecords,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalRecords / $perPage)
        ];
    }

    /**
     * Crear huésped
     */
    public function createHuesped($data)
    {
        // Validar datos requeridos
        if (empty($data['rela_persona'])) {
            return false;
        }

        // Establecer valores por defecto
        $data['huesped_estado'] = $data['huesped_estado'] ?? 1;

        return $this->create($data);
    }

    /**
     * Verificar si una persona ya es huésped
     */
    public function personaIsHuesped($personaId)
    {
        $huesped = $this->findWhere("rela_persona = ?", [$personaId]);
        return $huesped !== false && $huesped !== null;
    }

    /**
     * Actualizar huésped
     */
    public function updateHuesped($id, $data)
    {
        return $this->update($id, $data);
    }

    /**
     * Eliminar huésped (baja lógica)
     */
    public function deleteHuesped($id)
    {
        return $this->update($id, ['huesped_estado' => 0]);
    }

    /**
     * Restaurar huésped
     */
    public function restoreHuesped($id)
    {
        return $this->update($id, ['huesped_estado' => 1]);
    }

    /**
     * Obtener reservas del huésped
     */
    public function getReservas($huespedId)
    {
        $sql = "SELECT r.* 
                FROM reserva r 
                WHERE r.rela_huesped = ? 
                ORDER BY r.reserva_fecha DESC";
        
        $result = $this->query($sql, [$huespedId]);
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        
        return $reservas;
    }
}