<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Persona
 */
class Persona extends Model
{
    protected $table = 'persona';
    protected $primaryKey = 'id_persona';

    /**
     * Obtener personas activas
     */
    public function getActive($page = 1, $perPage = 10, $search = '')
    {
        $offset = ($page - 1) * $perPage;
        $baseWhere = "rela_estadopersona = 1";
        $params = [];
        
        if ($search) {
            $searchPattern = '%' . $search . '%';
            $baseWhere .= " AND (persona_nombre LIKE ? OR persona_apellido LIKE ?)";
            $params = [$searchPattern, $searchPattern];
        }
        
        // Contar total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $baseWhere";
        if (!empty($params)) {
            $countResult = $this->query($countSql, $params);
        } else {
            $countResult = $this->db->query($countSql);
        }
        $totalRecords = $countResult->fetch_assoc()['total'];
        
        // Obtener registros
        $sql = "SELECT * FROM {$this->table} WHERE $baseWhere ORDER BY persona_apellido, persona_nombre LIMIT ? OFFSET ?";
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
     * Buscar persona por email (lógica simple: buscar contacto y obtener rela_persona)
     */
    public function findByEmail($email)
    {
        $contactoModel = new Contacto();
        
        // 1. Buscar el contacto con ese email
        $contacto = $contactoModel->findByDescripcionAndTipo($email, 'email');
        
        if (!$contacto) {
            return false; // No existe contacto con ese email
        }
        
        // 2. Obtener la persona usando el rela_persona del contacto
        return $this->find($contacto['rela_persona']);
    }

    /**
     * Obtener persona con estado
     */
    public function getWithState($id)
    {
        $sql = "SELECT p.*, ep.estadopersona_descripcion
                FROM persona p
                LEFT JOIN estadopersona ep ON p.rela_estadopersona = ep.id_estadopersona
                WHERE p.id_persona = ?";
        
        $result = $this->query($sql, [$id]);
        return $result->fetch_assoc();
    }

    /**
     * Obtener persona con sus contactos (email y teléfono) usando modelo Contacto
     */
    public function getWithContacts($id)
    {
        $sql = "SELECT p.*, 
                       pf.personafisica_nombre AS persona_nombre,
                       pf.personafisica_apellido AS persona_apellido,
                       pf.personafisica_fechanac AS persona_fechanac,
                       pf.personafisica_dni AS persona_dni,
                       ep.estadopersona_descripcion
                FROM persona p
                LEFT JOIN personafisica pf ON p.id_persona = pf.rela_persona
                LEFT JOIN estadopersona ep ON p.rela_estadopersona = ep.id_estadopersona
                WHERE p.id_persona = ?";
        
        $result = $this->query($sql, [$id]);
        $persona = $result->fetch_assoc();
        
        if ($persona) {
            // Usar el modelo Contacto para obtener los contactos específicos
            $contactoModel = new Contacto();
            $persona['contacto_email'] = $contactoModel->getContactoByTipoAndPersona('email', $id);
            $persona['contacto_telefono'] = $contactoModel->getContactoByTipoAndPersona('telefono', $id);
        }
        
        return $persona;
    }

    /**
     * Verificar si el email ya existe
     */
    public function emailExists($email, $excludeId = null)
    {
        $contactoModel = new Contacto();
        return $contactoModel->existsByDescripcionAndTipo($email, 'email', $excludeId);
    }

    /**
     * Crear persona con transacción completa
     */
    public function createPersonaCompleta($data)
    {
        $db = \App\Core\Database::getInstance();
        
        return $db->transaction(function($db) use ($data) {
            // 1. Crear persona usando el método create del modelo
            $personaData = [
                'persona_nombre' => $data['persona_nombres'],
                'persona_apellido' => $data['persona_apellidos'],
                'persona_fechanac' => $data['persona_fecha_nacimiento'],
                'persona_direccion' => $data['persona_direccion'],
                'rela_estadopersona' => 1
            ];
            
            $personaId = $this->create($personaData);
            if (!$personaId) {
                throw new \Exception('Error al crear la persona');
            }
            
            // 2. Crear PersonaFisica asociada (NUEVO)
            $this->createPersonaFisicaForPersona($personaId, $data);
            
            // 3. Crear contactos usando el modelo Contacto
            $this->createContactosForPersona($personaId, $data);
            
            // 4. Crear huésped si es necesario
            if (isset($data['crear_huesped']) && $data['crear_huesped']) {
                $this->createHuespedForPersona($personaId);
            }
            
            return $personaId;
        });
    }

    /**
     * Crear PersonaFisica para una persona
     */
    private function createPersonaFisicaForPersona($personaId, $data)
    {
        $personaFisicaModel = new PersonaFisica();
        
        // Preparar datos de persona física
        $personaFisicaData = [
            'rela_persona' => $personaId,
            'personafisica_nombre' => $data['persona_nombres'],
            'personafisica_apellido' => $data['persona_apellidos'],
            'personafisica_fechanac' => $data['persona_fecha_nacimiento'],
            'personafisica_dni' => $data['persona_dni'] ?? null
        ];
        
        $personaFisicaId = $personaFisicaModel->create($personaFisicaData);
        if (!$personaFisicaId) {
            throw new \Exception('Error al crear persona física');
        }
        
        return $personaFisicaId;
    }

    /**
     * Crear contactos para una persona
     */
    private function createContactosForPersona($personaId, $data)
    {
        $contactoModel = new Contacto();
        $tipoContactoModel = new TipoContacto();
        
        $contactos = [
            'email' => $data['persona_email'] ?? '',
            'telefono' => $data['persona_telefono'] ?? '',
            'instagram' => $data['persona_instagram'] ?? '',
            'facebook' => $data['persona_facebook'] ?? ''
        ];

        foreach ($contactos as $tipoDescripcion => $valor) {
            if (!empty($valor)) {
                // Obtener ID del tipo de contacto usando findWhere
                $tipoContacto = $tipoContactoModel->findWhere("tipocontacto_descripcion = ?", [$tipoDescripcion]);
                
                if (!$tipoContacto) {
                    throw new \Exception("Tipo de contacto '$tipoDescripcion' no encontrado");
                }
                
                // Crear contacto usando el método create del modelo
                $contactoData = [
                    'contacto_descripcion' => $valor,
                    'rela_persona' => $personaId,
                    'rela_tipocontacto' => $tipoContacto['id_tipocontacto'],
                    'contacto_estado' => 1
                ];
                
                $contactoId = $contactoModel->create($contactoData);
                if (!$contactoId) {
                    throw new \Exception("Error al crear contacto $tipoDescripcion");
                }
            }
        }
    }

    /**
     * Crear huésped para una persona
     */
    private function createHuespedForPersona($personaId)
    {
        $huespedModel = new Huesped();
        
        // Verificar si ya es huésped
        if ($huespedModel->personaIsHuesped($personaId)) {
            return; // Ya es huésped, no hacer nada
        }
        
        // Crear huésped usando el método create del modelo
        $huespedData = [
            'rela_persona' => $personaId,
            'huesped_estado' => 1
        ];
        
        $huespedId = $huespedModel->create($huespedData);
        if (!$huespedId) {
            throw new \Exception('Error al crear huésped');
        }
        
        return $huespedId;
    }

    /**
     * Método público para crear huésped (usado desde Usuario model)
     */
    public function insertHuespedTransaction($db, $personaId)
    {
        return $this->createHuespedForPersona($personaId);
    }
    
    /**
     * Buscar persona por nombre de usuario
     */
    public function findByUsuario($nombreUsuario)
    {
        $sql = "SELECT p.*, 
                       pf.personafisica_nombre AS persona_nombre,
                       pf.personafisica_apellido AS persona_apellido,
                       pf.personafisica_dni,
                       pf.personafisica_fechanac
                FROM persona p
                LEFT JOIN usuario u ON p.id_persona = u.rela_persona
                LEFT JOIN personafisica pf ON p.id_persona = pf.rela_persona
                WHERE u.usuario_nombre = ? AND u.usuario_estado = 1";
        
        $result = $this->query($sql, [$nombreUsuario]);
        return $result->fetch_assoc();
    }

    /**
     * Verificar si una persona es de tipo física
     * 
     * @param int $personaId ID de la persona
     * @return bool True si es persona física, false en caso contrario
     */
    public function isPersonaFisica($personaId)
    {
        $sql = "SELECT COUNT(*) as existe 
                FROM personafisica 
                WHERE rela_persona = ? 
                LIMIT 1";
        
        $result = $this->query($sql, [(int)$personaId]);
        $row = $result->fetch_assoc();
        
        return (int)$row['existe'] > 0;
    }

    /**
     * Verificar si una persona es de tipo jurídica
     * 
     * @param int $personaId ID de la persona
     * @return bool True si es persona jurídica, false en caso contrario
     */
    public function isPersonaJuridica($personaId)
    {
        $sql = "SELECT COUNT(*) as existe 
                FROM personajuridica 
                WHERE rela_persona = ? 
                LIMIT 1";
        
        $result = $this->query($sql, [(int)$personaId]);
        $row = $result->fetch_assoc();
        
        return (int)$row['existe'] > 0;
    }

    /**
     * Obtener el tipo de persona (física o jurídica)
     * 
     * @param int $personaId ID de la persona
     * @return string 'fisica' | 'juridica' | 'indefinido'
     */
    public function getTipoPersona($personaId)
    {
        if ($this->isPersonaFisica($personaId)) {
            return 'fisica';
        }
        
        if ($this->isPersonaJuridica($personaId)) {
            return 'juridica';
        }
        
        return 'indefinido';
    }

    /**
     * Obtener persona con información de tipo (física o jurídica) mediante LEFT JOIN
     * 
     * @param int $personaId ID de la persona
     * @return array|null Array con datos completos de persona y tipo, o null si no existe
     */
    public function getWithTipoPersona($personaId)
    {
        $sql = "SELECT p.*,
                       ep.estadopersona_descripcion,
                       pf.id_personafisica,
                       pf.personafisica_dni,
                       pf.personafisica_nombre,
                       pf.personafisica_apellido,
                       pf.personafisica_fechanac,
                       pj.id_personajuridica,
                       pj.personajuridica_cuit,
                       CASE 
                           WHEN pf.id_personafisica IS NOT NULL THEN 'fisica'
                           WHEN pj.id_personajuridica IS NOT NULL THEN 'juridica'
                           ELSE 'indefinido'
                       END as tipo_persona
                FROM persona p
                LEFT JOIN estadopersona ep ON p.rela_estadopersona = ep.id_estadopersona
                LEFT JOIN personafisica pf ON p.id_persona = pf.rela_persona
                LEFT JOIN personajuridica pj ON p.id_persona = pj.rela_persona
                WHERE p.id_persona = ?
                LIMIT 1";
        
        $result = $this->query($sql, [(int)$personaId]);
        return $result->fetch_assoc();
    }

    /**
     * Obtener información completa de persona incluyendo contactos y tipo
     * 
     * @param int $personaId ID de la persona
     * @return array|null Array con todos los datos de persona, contactos y tipo específico
     */
    public function getPersonaCompleta($personaId)
    {
        // Obtener datos base con tipo persona
        $persona = $this->getWithTipoPersona($personaId);
        
        if (!$persona) {
            return null;
        }
        
        // Obtener contactos usando el modelo Contacto
        $contactoModel = new Contacto();
        $persona['contacto_email'] = $contactoModel->getContactoByTipoAndPersona('email', $personaId);
        $persona['contacto_telefono'] = $contactoModel->getContactoByTipoAndPersona('telefono', $personaId);
        $persona['contacto_instagram'] = $contactoModel->getContactoByTipoAndPersona('instagram', $personaId);
        $persona['contacto_facebook'] = $contactoModel->getContactoByTipoAndPersona('facebook', $personaId);
        
        // Si es persona física, calcular edad
        if ($persona['tipo_persona'] === 'fisica' && !empty($persona['personafisica_fechanac'])) {
            $fechaNac = new \DateTime($persona['personafisica_fechanac']);
            $hoy = new \DateTime();
            $edad = $hoy->diff($fechaNac);
            $persona['edad'] = $edad->y;
        }
        
        return $persona;
    }

    /**
     * Obtener listado de personas con su tipo (física o jurídica)
     * 
     * @param int $page Número de página
     * @param int $perPage Registros por página
     * @param array $filters Filtros opcionales (tipo, estado, búsqueda)
     * @return array Datos paginados con información de tipo
     */
    public function getWithTipoPersonaPaginated($page = 1, $perPage = 10, $filters = [])
    {
        $where = "1=1";
        $params = [];
        
        // Filtrar por tipo de persona
        if (!empty($filters['tipo'])) {
            if ($filters['tipo'] === 'fisica') {
                $where .= " AND pf.id_personafisica IS NOT NULL";
            } elseif ($filters['tipo'] === 'juridica') {
                $where .= " AND pj.id_personajuridica IS NOT NULL";
            }
        }
        
        // Filtrar por estado
        if (isset($filters['estado']) && $filters['estado'] !== '') {
            $where .= " AND p.rela_estadopersona = ?";
            $params[] = (int)$filters['estado'];
        }
        
        // Búsqueda general
        if (!empty($filters['busqueda'])) {
            $busqueda = '%' . $filters['busqueda'] . '%';
            $where .= " AND (
                p.persona_nombre LIKE ? OR 
                p.persona_apellido LIKE ? OR
                pf.personafisica_nombre LIKE ? OR
                pf.personafisica_apellido LIKE ? OR
                pf.personafisica_dni LIKE ? OR
                pj.personajuridica_cuit LIKE ?
            )";
            $params[] = $busqueda;
            $params[] = $busqueda;
            $params[] = $busqueda;
            $params[] = $busqueda;
            $params[] = $busqueda;
            $params[] = $busqueda;
        }
        
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        $countSql = "SELECT COUNT(DISTINCT p.id_persona) as total
                     FROM persona p
                     LEFT JOIN personafisica pf ON p.id_persona = pf.rela_persona
                     LEFT JOIN personajuridica pj ON p.id_persona = pj.rela_persona
                     WHERE {$where}";
        
        $countResult = $this->query($countSql, $params);
        $totalRecords = (int)$countResult->fetch_assoc()['total'];
        
        // Obtener registros
        $sql = "SELECT p.*,
                       ep.estadopersona_descripcion,
                       pf.id_personafisica,
                       pf.personafisica_dni,
                       pf.personafisica_nombre,
                       pf.personafisica_apellido,
                       pf.personafisica_fechanac,
                       pj.id_personajuridica,
                       pj.personajuridica_cuit,
                       CASE 
                           WHEN pf.id_personafisica IS NOT NULL THEN 'fisica'
                           WHEN pj.id_personajuridica IS NOT NULL THEN 'juridica'
                           ELSE 'indefinido'
                       END as tipo_persona
                FROM persona p
                LEFT JOIN estadopersona ep ON p.rela_estadopersona = ep.id_estadopersona
                LEFT JOIN personafisica pf ON p.id_persona = pf.rela_persona
                LEFT JOIN personajuridica pj ON p.id_persona = pj.rela_persona
                WHERE {$where}
                ORDER BY p.persona_apellido ASC, p.persona_nombre ASC
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
            'total_pages' => ceil($totalRecords / $perPage),
            'offset' => $offset,
            'limit' => $perPage
        ];
    }
}