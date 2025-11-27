<?php

namespace App\Core;

/**
 * Clase base para todos los modelos
 */
abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Encontrar registro por ID
     */
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Encontrar todos los registros
     */
    public function findAll($where = "", $orderBy = "", $limit = "")
    {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($where) {
            $sql .= " WHERE " . $where;
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY " . $orderBy;
        }
        
        if ($limit) {
            $sql .= " LIMIT " . $limit;
        }

        $result = $this->db->query($sql);
        
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        
        return $records;
    }

    /**
     * Encontrar un registro con condiciones
     */
    public function findWhere($where, $params = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE " . $where;
        
        if (empty($params)) {
            $result = $this->db->query($sql);
            return $result->fetch_assoc();
        }
        
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Contar registros
     */
    public function count($where = "", $params = [])
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        
        if ($where) {
            $sql .= " WHERE " . $where;
        }

        if (empty($params)) {
            $result = $this->db->query($sql);
            $row = $result->fetch_assoc();
            return (int) $row['total'];
        }

        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (int) $row['total'];
    }

    /**
     * Crear nuevo registro
     */
    public function create($data)
    {
        $fields = array_keys($data);
        $values = array_values($data);
        
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        $sql = "INSERT INTO {$this->table} (" . implode(',', $fields) . ") VALUES ($placeholders)";
        
        $stmt = $this->db->prepare($sql);
        $types = str_repeat('s', count($values));
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            return $this->db->insertId();
        }
        
        return false;
    }

    /**
     * Actualizar registro
     */
    public function update($id, $data)
    {
        $fields = [];
        $values = [];
        $types = '';
        
        foreach ($data as $field => $value) {
            $fields[] = "$field = ?";
            $values[] = $value;
            
            // Determinar el tipo correcto para bind_param
            if (is_null($value)) {
                $types .= 's'; // NULL se maneja como string
            } elseif (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value) || is_double($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = ?";
        
        // Agregar el ID al final
        $values[] = $id;
        $types .= 'i'; // El ID siempre es entero
        
        $stmt = $this->db->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando statement: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        
        return $result;
    }

    /**
     * Eliminar registro
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Baja lógica (marcar como eliminado)
     */
    public function softDelete($id, $field = 'estado')
    {
        return $this->update($id, [$field => 0]);
    }

    /**
     * Restaurar registro de baja lógica
     */
    public function restore($id, $field = 'estado')
    {
        return $this->update($id, [$field => 1]);
    }

    /**
     * Obtener registros paginados
     */
    public function paginate($page = 1, $perPage = 10, $where = "", $orderBy = "")
    {
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($where) {
            $countSql .= " WHERE " . $where;
        }
        
        $countResult = $this->db->query($countSql);
        $totalRecords = $countResult->fetch_assoc()['total'];
        
        // Obtener registros
        $sql = "SELECT * FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE " . $where;
        }
        if ($orderBy) {
            $sql .= " ORDER BY " . $orderBy;
        }
        $sql .= " LIMIT $perPage OFFSET $offset";
        
        $result = $this->db->query($sql);
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
     * Ejecutar query personalizada
     */
    protected function query($sql, $params = [])
    {
        if (empty($params)) {
            return $this->db->query($sql);
        }
        
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }
}