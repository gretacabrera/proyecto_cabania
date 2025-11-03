<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la entidad Usuario
 */
class Usuario extends Model
{
    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';

    /**
     * Autenticar usuario
     */
    public function authenticate($username, $password)
    {
        // Buscar usuario por nombre (incluir estados 1 y 2 para dar mensajes específicos)
        $usuario = $this->findWhere("usuario_nombre = ? AND usuario_estado IN (1, 2)", [$username]);
        
        if ($usuario && password_verify($password, $usuario['usuario_contrasenia'])) {
            // Verificar si el usuario está pendiente de verificación
            if ($usuario['usuario_estado'] == 2) {
                // Usuario existe pero no ha verificado su email
                throw new \Exception('Su cuenta está pendiente de verificación de email. Por favor revise su correo electrónico.');
            }
            
            // Usuario verificado y activo
            return $usuario;
        }
        
        return false;
    }

    /**
     * Obtener usuario con perfil
     */
    public function findWithProfile($id)
    {
        $sql = "SELECT u.*, p.perfil_descripcion, p.perfil_estado
                FROM usuario u
                LEFT JOIN perfil p ON u.rela_perfil = p.id_perfil
                WHERE u.id_usuario = ?";
        
        $result = $this->query($sql, [$id]);
        return $result->fetch_assoc();
    }

    /**
     * Obtener usuarios activos con paginación
     */
    public function getActivePaginated($page = 1, $perPage = 10, $search = '')
    {
        $baseWhere = "u.usuario_estado = 1";
        $params = [];
        
        if ($search) {
            $searchPattern = '%' . $search . '%';
            $baseWhere .= " AND (u.usuario_nombre LIKE ? OR pe.persona_nombre LIKE ? OR pe.persona_apellido LIKE ?)";
            $params = [$searchPattern, $searchPattern, $searchPattern];
        }
        
        $sql = "SELECT u.*, pe.persona_nombre, pe.persona_apellido,
                       (SELECT c.contacto_descripcion FROM contacto c 
                        JOIN tipocontacto tc ON c.rela_tipocontacto = tc.id_tipocontacto 
                        WHERE tc.tipocontacto_descripcion = 'email' 
                        AND c.rela_persona = pe.id_persona 
                        AND c.contacto_estado = 1 
                        LIMIT 1) AS persona_email,
                       pr.perfil_descripcion
                FROM usuario u
                LEFT JOIN persona pe ON u.rela_persona = pe.id_persona
                LEFT JOIN perfil pr ON u.rela_perfil = pr.id_perfil
                WHERE $baseWhere
                ORDER BY u.usuario_nombre";
        
        $countSql = "SELECT COUNT(*) as total 
                     FROM usuario u
                     LEFT JOIN persona pe ON u.rela_persona = pe.id_persona
                     LEFT JOIN perfil pr ON u.rela_perfil = pr.id_perfil
                     WHERE $baseWhere";
        
        return $this->paginateCustom($sql, $countSql, $page, $perPage, $params);
    }

    /**
     * Crear usuario con hash de contraseña
     */
    public function createUser($data)
    {
        if (isset($data['usuario_contrasenia'])) {
            $data['usuario_contrasenia'] = password_hash($data['usuario_contrasenia'], PASSWORD_DEFAULT);
        }
        return $this->create($data);
    }

    /**
     * Actualizar contraseña
     */
    public function updatePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        try {
            $result = $this->update($userId, [
                'usuario_contrasenia' => $hashedPassword
            ]);
            
            if ($result) {
                // Limpiar tokens de recuperación si existen
                $this->clearPasswordResetTokens($userId);
                error_log("updatePassword: Contraseña actualizada exitosamente para usuario ID: $userId");
            }
            
            return $result;
        } catch (\Exception $e) {
            error_log("updatePassword: Error - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar por nombre de usuario (incluyendo inactivos)
     */
    public function findByUsername($username)
    {
        return $this->findWhere("usuario_nombre = ?", [$username]);
    }

    /**
     * Verificar si el usuario existe (solo usuarios activos y no eliminados)
     */
    public function userExists($username, $excludeId = null)
    {
        // Solo considerar usuarios activos (estado 1) como "existentes"
        // Estados 0 (inactivo) y 3 (eliminado) deberían permitir reutilizar el nombre
        $where = "usuario_nombre = ? AND usuario_estado = 1";
        $params = [$username];
        
        if ($excludeId) {
            $where .= " AND id_usuario != ?";
            $params[] = $excludeId;
        }
        
        $usuario = $this->findWhere($where, $params);
        // Corregir: verificar que no sea null Y no sea false
        return $usuario !== false && $usuario !== null;
    }

    /**
     * Verificar si el usuario está disponible para registro/creación
     */
    public function isUsernameAvailable($username, $excludeId = null)
    {
        return !$this->userExists($username, $excludeId);
    }

    /**
     * Validar datos de usuario para creación
     */
    public function validateUserData($data, $isUpdate = false, $userId = null)
    {
        $errors = [];

        // Validar nombre de usuario
        if (empty($data['usuario_nombre'])) {
            $errors[] = 'El nombre de usuario es obligatorio';
        } elseif (strlen($data['usuario_nombre']) < 3) {
            $errors[] = 'El nombre de usuario debe tener al menos 3 caracteres';
        } elseif (!$this->isUsernameAvailable($data['usuario_nombre'], $userId)) {
            $errors[] = 'El nombre de usuario ya existe';
        }

        // Validar contraseña (solo en creación o si se proporciona en actualización)
        if (!$isUpdate || (!empty($data['usuario_contrasenia']) || !empty($data['confirmar_contrasenia']))) {
            if (empty($data['usuario_contrasenia'])) {
                $errors[] = 'La contraseña es obligatoria';
            } elseif (strlen($data['usuario_contrasenia']) < 6) {
                $errors[] = 'La contraseña debe tener al menos 6 caracteres';
            }

            if (isset($data['confirmar_contrasenia']) && $data['usuario_contrasenia'] !== $data['confirmar_contrasenia']) {
                $errors[] = 'Las contraseñas no coinciden';
            }
        }

        // Validar perfil
        if (empty($data['rela_perfil'])) {
            $errors[] = 'El perfil es obligatorio';
        }

        return $errors;
    }

    /**
     * Obtener módulos del usuario
     */
    public function getUserModules($userId)
    {
        $sql = "SELECT m.modulo_ruta, m.modulo_descripcion, men.menu_nombre, m.rela_menu
                FROM modulo m
                LEFT JOIN perfil_modulo pm ON pm.rela_modulo = m.id_modulo
                LEFT JOIN usuario u ON u.rela_perfil = pm.rela_perfil
                LEFT JOIN menu men ON m.rela_menu = men.id_menu
                WHERE u.id_usuario = ?
                AND m.modulo_estado = 1
                AND pm.perfilmodulo_estado = 1
                AND u.usuario_estado = 1
                ORDER BY men.menu_nombre, m.modulo_descripcion";
        
        $result = $this->query($sql, [$userId]);
        
        $modules = [];
        while ($row = $result->fetch_assoc()) {
            $modules[] = $row;
        }
        
        return $modules;
    }

    /**
     * Paginación personalizada
     */
    protected function paginateCustom($sql, $countSql, $page, $perPage, $params = [])
    {
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        if (!empty($params)) {
            $countResult = $this->query($countSql, $params);
        } else {
            $countResult = $this->db->query($countSql);
        }
        $totalRecords = $countResult->fetch_assoc()['total'];
        
        // Obtener registros
        $paginatedSql = $sql . " LIMIT ? OFFSET ?";
        $allParams = array_merge($params, [$perPage, $offset]);
        
        if (!empty($allParams)) {
            $result = $this->query($paginatedSql, $allParams);
        } else {
            $result = $this->db->query($paginatedSql);
        }
        
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
     * Buscar usuarios
     */
    public function search($query, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        $searchPattern = '%' . $query . '%';
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE usuario_estado = 1 AND usuario_nombre LIKE ?
                ORDER BY usuario_nombre ASC 
                LIMIT ? OFFSET ?";
        
        $result = $this->query($sql, [$searchPattern, $perPage, $offset]);
        
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        
        return $records;
    }

    /**
     * Obtener total de páginas
     */
    public function getTotalPages($query = null, $perPage = 10)
    {
        if ($query) {
            $searchPattern = '%' . $query . '%';
            $total = $this->count("usuario_estado = 1 AND usuario_nombre LIKE ?", [$searchPattern]);
        } else {
            $total = $this->count("usuario_estado = 1");
        }
        
        return ceil($total / $perPage);
    }

    /**
     * Crear usuario completo con transacción (para registro desde auth/register)
     */
    public function createUsuarioCompleto($data)
    {
        $db = \App\Core\Database::getInstance();
        
        return $db->transaction(function($db) use ($data) {
            // 1. Crear persona con contactos y huésped
            $personaModel = new Persona();
            $dataPersona = array_merge($data, ['crear_huesped' => true]);
            $personaId = $personaModel->createPersonaCompleta($dataPersona);
            
            // 2. Crear usuario con perfil huésped
            $userId = $this->insertUsuarioTransaction($db, $data, $personaId);
            
            return $userId;
        });
    }

    /**
     * Crear usuario administrativo (desde admin/usuarios/create)
     */
    public function createUsuarioAdmin($data, $personaId, $perfilId)
    {
        $db = \App\Core\Database::getInstance();
        
        return $db->transaction(function($db) use ($data, $personaId, $perfilId) {
            // Verificar si necesita crear huésped
            $needsHuesped = $this->isPerfilHuesped($db, $perfilId);
            
            if ($needsHuesped) {
                $personaModel = new Persona();
                $personaModel->insertHuespedTransaction($db, $personaId);
            }
            
            // Crear usuario con el perfil especificado
            return $this->insertUsuarioWithPerfil($db, $data, $personaId, $perfilId);
        });
    }

    /**
     * Insertar usuario en transacción (siempre con perfil huésped)
     */
    private function insertUsuarioTransaction($db, $data, $personaId)
    {
        // Obtener ID del perfil huésped
        $stmt = $db->prepare("SELECT id_perfil FROM perfil WHERE perfil_descripcion = 'huesped' OR perfil_descripcion = 'huésped' LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new \Exception('Perfil huésped no encontrado');
        }
        
        $perfilId = $result->fetch_assoc()['id_perfil'];
        
        return $this->insertUsuarioWithPerfilPendiente($db, $data, $personaId, $perfilId);
    }

    /**
     * Insertar usuario con perfil específico
     */
    private function insertUsuarioWithPerfil($db, $data, $personaId, $perfilId)
    {
        $hashedPassword = password_hash($data['usuario_contrasenia'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO usuario (usuario_nombre, usuario_contrasenia, rela_persona, rela_perfil, usuario_estado) VALUES (?, ?, ?, ?, 1)";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ssii", 
            $data['usuario_nombre'],
            $hashedPassword,
            $personaId,
            $perfilId
        );
        
        if ($stmt->execute()) {
            return $db->insertId();
        }
        
        throw new \Exception('Error al insertar usuario: ' . $stmt->error);
    }

    /**
     * Insertar usuario con perfil específico en estado pendiente de verificación
     */
    private function insertUsuarioWithPerfilPendiente($db, $data, $personaId, $perfilId)
    {
        $hashedPassword = password_hash($data['usuario_contrasenia'], PASSWORD_DEFAULT);
        
        // Estado 2 = Pendiente de verificación de email
        $sql = "INSERT INTO usuario (usuario_nombre, usuario_contrasenia, rela_persona, rela_perfil, usuario_estado) VALUES (?, ?, ?, ?, 2)";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ssii", 
            $data['usuario_nombre'],
            $hashedPassword,
            $personaId,
            $perfilId
        );
        
        if ($stmt->execute()) {
            return $db->insertId();
        }
        
        throw new \Exception('Error al insertar usuario: ' . $stmt->error);
    }

    /**
     * Verificar si un perfil es de huésped
     */
    private function isPerfilHuesped($db, $perfilId)
    {
        $stmt = $db->prepare("SELECT perfil_descripcion FROM perfil WHERE id_perfil = ?");
        $stmt->bind_param("i", $perfilId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        $descripcion = strtolower($result->fetch_assoc()['perfil_descripcion']);
        return in_array($descripcion, ['huesped', 'huésped']);
    }

    /**
     * Verificar si el usuario actual tiene perfil de huésped
     */
    public function esPerfilHuesped($usuarioData = null)
    {
        $perfil = null;
        
        if ($usuarioData && isset($usuarioData['perfil_descripcion'])) {
            $perfil = $usuarioData['perfil_descripcion'];
        } elseif (isset($_SESSION['user']['perfil_descripcion'])) {
            $perfil = $_SESSION['user']['perfil_descripcion'];
        } else {
            return true; // Por defecto, usuarios sin perfil definido son huéspedes
        }
        
        $perfilLower = strtolower($perfil);
        return strpos($perfilLower, 'huesped') !== false || 
               strpos($perfilLower, 'huésped') !== false || 
               strpos($perfilLower, 'cliente') !== false;
    }

    /**
     * Verificar si el usuario actual tiene perfil de cajero
     */
    public function esPerfilCajero($usuarioData = null)
    {
        $perfil = null;
        
        if ($usuarioData && isset($usuarioData['perfil_descripcion'])) {
            $perfil = $usuarioData['perfil_descripcion'];
        } elseif (isset($_SESSION['user']['perfil_descripcion'])) {
            $perfil = $_SESSION['user']['perfil_descripcion'];
        } else {
            return false;
        }
        
        $perfilLower = strtolower($perfil);
        return strpos($perfilLower, 'cajero') !== false || 
               strpos($perfilLower, 'caja') !== false;
    }

    /**
     * Obtener tipo de perfil del usuario (huesped, cajero, admin, etc.)
     */
    public function getTipoPerfil($usuarioData = null)
    {
        if ($this->esPerfilHuesped($usuarioData)) {
            return 'huesped';
        } elseif ($this->esPerfilCajero($usuarioData)) {
            return 'cajero';
        } else {
            return 'admin'; // Otros perfiles se consideran administrativos
        }
    }

    /**
     * Verificar si el usuario tiene permisos para un tipo específico de operación
     */
    public function tienePermisoPara($operacion, $usuarioData = null)
    {
        $tipoPerfil = $this->getTipoPerfil($usuarioData);
        
        switch ($operacion) {
            case 'pago_manual':
                return $tipoPerfil === 'cajero' || $tipoPerfil === 'admin';
            
            case 'reserva_online':
                return true; // Todos pueden hacer reservas online
            
            case 'acceso_admin':
                return $tipoPerfil === 'cajero' || $tipoPerfil === 'admin';
            
            case 'todos_metodos_pago':
                return $tipoPerfil === 'cajero' || $tipoPerfil === 'admin';
            
            default:
                return false;
        }
    }

    /**
     * Obtener perfiles disponibles
     */
    public function getPerfiles()
    {
        $sql = "SELECT * FROM perfil WHERE perfil_estado = 1 ORDER BY perfil_descripcion";
        $result = $this->db->query($sql);
        
        $perfiles = [];
        while ($row = $result->fetch_assoc()) {
            $perfiles[] = $row;
        }
        
        return $perfiles;
    }

    /**
     * Obtener personas disponibles
     */
    public function getPersonas()
    {
        $sql = "SELECT * FROM persona WHERE rela_estadopersona = 1 ORDER BY persona_nombre, persona_apellido";
        $result = $this->db->query($sql);
        
        $personas = [];
        while ($row = $result->fetch_assoc()) {
            $personas[] = $row;
        }
        
        return $personas;
    }

    /**
     * Obtener usuario con relaciones
     */
    public function findWithRelations($id)
    {
        $sql = "SELECT u.*, p.persona_nombre, p.persona_apellido,
                       (SELECT c.contacto_descripcion FROM contacto c 
                        JOIN tipocontacto tc ON c.rela_tipocontacto = tc.id_tipocontacto 
                        WHERE tc.tipocontacto_descripcion = 'email' 
                        AND c.rela_persona = p.id_persona 
                        AND c.contacto_estado = 1 
                        LIMIT 1) AS persona_email,
                       pr.perfil_descripcion
                FROM {$this->table} u
                LEFT JOIN persona p ON u.rela_persona = p.id_persona
                LEFT JOIN perfil pr ON u.rela_perfil = pr.id_perfil
                WHERE u.{$this->primaryKey} = ?";
        
        $result = $this->query($sql, [$id]);
        return $result->fetch_assoc();
    }

    /**
     * Generar y almacenar token de verificación para un usuario
     */
    public function generateVerificationToken($userId)
    {
        error_log("generateVerificationToken: Iniciando para usuario ID: $userId");
        
        // Verificar que el usuario existe
        try {
            $usuario = $this->find($userId);
            if (!$usuario) {
                error_log("generateVerificationToken: Usuario ID $userId no encontrado");
                return false;
            }
            error_log("generateVerificationToken: Usuario encontrado - " . $usuario['usuario_nombre']);
        } catch (\Exception $e) {
            error_log("generateVerificationToken: Error al buscar usuario - " . $e->getMessage());
            return false;
        }
        
        $token = bin2hex(random_bytes(32)); // Token de 64 caracteres
        
        error_log("generateVerificationToken: Token generado, preparando SQL...");
        
        $sql = "UPDATE {$this->table} 
                SET usuario_token = ?, 
                    usuario_fhtoken = NOW()
                WHERE {$this->primaryKey} = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("generateVerificationToken: Error al preparar statement");
                return false;
            }
            
            $stmt->bind_param("si", $token, $userId);
            error_log("generateVerificationToken: Statement preparado, ejecutando...");
            
            if ($stmt->execute()) {
                error_log("generateVerificationToken: Statement ejecutado - Filas afectadas: " . $stmt->affected_rows);
                if ($stmt->affected_rows > 0) {
                    error_log("generateVerificationToken: Token guardado exitosamente para usuario ID: $userId");
                    return $token;
                } else {
                    error_log("generateVerificationToken: No se actualizó ninguna fila para usuario ID: $userId");
                    return false;
                }
            } else {
                error_log("generateVerificationToken: Error SQL al ejecutar - Error: " . $stmt->error);
                return false;
            }
        } catch (\Exception $e) {
            error_log("generateVerificationToken: Excepción SQL - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar token de verificación
     */
    public function verifyToken($token)
    {
        // Verificar que el token existe y no ha expirado (24 horas)
        // Estado 2 = Pendiente de verificación
        $sql = "SELECT u.{$this->primaryKey}, u.usuario_nombre, 
                       p.persona_nombre, p.persona_apellido
                FROM {$this->table} u
                LEFT JOIN persona p ON u.rela_persona = p.id_persona
                WHERE u.usuario_token = ? 
                AND u.usuario_fhtoken > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND u.usuario_estado = 2";
        
        $result = $this->query($sql, [$token]);
        $usuario = $result->fetch_assoc();
        
        if ($usuario) {
            // Marcar como verificado (estado 1) y limpiar token
            $updateSql = "UPDATE {$this->table} 
                          SET usuario_estado = 1,
                              usuario_fhverificacion = NOW(),
                              usuario_token = NULL,
                              usuario_fhtoken = NULL
                          WHERE {$this->primaryKey} = ?";
            
            $stmt = $this->db->prepare($updateSql);
            $stmt->bind_param("i", $usuario[$this->primaryKey]);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                error_log("Usuario verificado exitosamente: " . $usuario['usuario_nombre']);
                return $usuario;
            } else {
                error_log("Error al verificar usuario ID: " . $usuario[$this->primaryKey] . " - Filas afectadas: " . $stmt->affected_rows);
            }
        }
        
        return false;
    }

    /**
     * Verificar si un usuario ya tiene email verificado
     * Estado 1 = Activo y verificado
     */
    public function isEmailVerified($userId)
    {
        $sql = "SELECT usuario_estado FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $result = $this->query($sql, [$userId]);
        $row = $result->fetch_assoc();
        
        return $row && $row['usuario_estado'] == 1;
    }

    /**
     * Obtener email del usuario a través de su contacto
     */
    public function getUserEmail($userId)
    {
        $sql = "SELECT c.contacto_descripcion as email
                FROM {$this->table} u
                JOIN persona p ON u.rela_persona = p.id_persona
                JOIN contacto c ON c.rela_persona = p.id_persona
                JOIN tipocontacto tc ON c.rela_tipocontacto = tc.id_tipocontacto
                WHERE u.{$this->primaryKey} = ? 
                AND tc.tipocontacto_descripcion = 'email'
                AND c.contacto_estado = 1
                LIMIT 1";
        
        $result = $this->query($sql, [$userId]);
        $row = $result->fetch_assoc();
        
        return $row ? $row['email'] : null;
    }

    /**
     * Obtener datos completos del usuario para envío de email
     */
    public function getUserForEmail($userId)
    {
        $sql = "SELECT u.{$this->primaryKey}, u.usuario_nombre,
                       p.persona_nombre, p.persona_apellido,
                       (SELECT c.contacto_descripcion FROM contacto c 
                        JOIN tipocontacto tc ON c.rela_tipocontacto = tc.id_tipocontacto 
                        WHERE tc.tipocontacto_descripcion = 'email' 
                        AND c.rela_persona = p.id_persona 
                        AND c.contacto_estado = 1 
                        LIMIT 1) AS persona_email
                FROM {$this->table} u
                LEFT JOIN persona p ON u.rela_persona = p.id_persona
                WHERE u.{$this->primaryKey} = ?";
        
        $result = $this->query($sql, [$userId]);
        return $result->fetch_assoc();
    }

    /**
     * Limpiar tokens de verificación expirados (más de 24 horas)
     * Estado 2 = Pendiente de verificación
     */
    public function cleanupExpiredTokens()
    {
        $sql = "UPDATE {$this->table} 
                SET usuario_token = NULL,
                    usuario_fhtoken = NULL
                WHERE usuario_fhtoken < DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND usuario_estado = 2";
        
        return $this->query($sql);
    }

    /**
     * ============================
     * FUNCIONES DE RECUPERACIÓN DE CONTRASEÑA
     * Reutilizan las mismas columnas que la verificación de email
     * ============================
     */

    /**
     * Generar token para recuperación de contraseña
     * Reutiliza las mismas columnas de verificación de email
     */
    public function generatePasswordResetToken($email)
    {
        // Buscar usuario por email - solo usuarios activos (estado 1) para recuperación
        $sql = "SELECT u.{$this->primaryKey}, u.usuario_nombre, u.usuario_estado
                FROM {$this->table} u
                LEFT JOIN persona p ON u.rela_persona = p.id_persona
                LEFT JOIN contacto c ON c.rela_persona = p.id_persona
                LEFT JOIN tipocontacto tc ON c.rela_tipocontacto = tc.id_tipocontacto
                WHERE c.contacto_descripcion = ?
                AND tc.tipocontacto_descripcion = 'email'
                AND c.contacto_estado = 1
                AND u.usuario_estado = 1"; // Solo usuarios activos pueden recuperar contraseña
        
        $result = $this->query($sql, [$email]);
        $usuario = $result->fetch_assoc();
        
        if (!$usuario) {
            error_log("generatePasswordResetToken: No se encontró usuario activo con email: $email");
            return false;
        }
        
        $userId = $usuario[$this->primaryKey];
        $token = bin2hex(random_bytes(32)); // Token de 64 caracteres
        
        // Usar NOW() de MySQL para evitar problemas de zona horaria
        $sql = "UPDATE {$this->table} 
                SET usuario_token = ?, 
                    usuario_fhtoken = NOW()
                WHERE {$this->primaryKey} = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("generatePasswordResetToken: Error al preparar statement");
                return false;
            }
            
            $stmt->bind_param("si", $token, $userId);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                error_log("generatePasswordResetToken: Token de reset generado exitosamente para usuario ID: $userId");
                return [
                    'token' => $token,
                    'usuario_id' => $userId,
                    'usuario_nombre' => $usuario['usuario_nombre']
                ];
            } else {
                error_log("generatePasswordResetToken: No se pudo generar token - Filas afectadas: " . $stmt->affected_rows);
                return false;
            }
        } catch (\Exception $e) {
            error_log("generatePasswordResetToken: Excepción SQL - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar token de recuperación de contraseña
     * Diferencia de verificación de email: tiempo más corto (1 hora vs 24 horas) y solo usuarios activos
     */
    public function verifyPasswordResetToken($token)
    {
        // Verificar que el token existe y no ha expirado (1 hora para recuperación vs 24 horas para verificación)
        // Solo usuarios activos (estado 1) pueden usar recuperación de contraseña
        $sql = "SELECT u.{$this->primaryKey}, u.usuario_nombre, u.usuario_estado,
                       p.persona_nombre, p.persona_apellido
                FROM {$this->table} u
                LEFT JOIN persona p ON u.rela_persona = p.id_persona
                WHERE u.usuario_token = ? 
                AND u.usuario_fhtoken > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                AND u.usuario_estado = 1"; // Solo usuarios activos
        
        $result = $this->query($sql, [$token]);
        return $result->fetch_assoc();
    }

    /**
     * Restablecer contraseña usando token
     */
    public function resetPasswordWithToken($token, $newPassword)
    {
        // Verificar token primero
        $usuario = $this->verifyPasswordResetToken($token);
        
        if (!$usuario) {
            error_log("resetPasswordWithToken: Token inválido o expirado");
            return false;
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $userId = $usuario[$this->primaryKey];
        
        // Actualizar contraseña y limpiar token
        $sql = "UPDATE {$this->table} 
                SET usuario_contrasenia = ?,
                    usuario_token = NULL,
                    usuario_fhtoken = NULL
                WHERE {$this->primaryKey} = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("si", $hashedPassword, $userId);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                error_log("resetPasswordWithToken: Contraseña restablecida exitosamente para usuario ID: $userId");
                return $usuario;
            } else {
                error_log("resetPasswordWithToken: Error al restablecer contraseña - Filas afectadas: " . $stmt->affected_rows);
                return false;
            }
        } catch (\Exception $e) {
            error_log("resetPasswordWithToken: Excepción SQL - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar usuario por email para recuperación
     */
    public function findUserByEmail($email)
    {
        $sql = "SELECT u.{$this->primaryKey}, u.usuario_nombre, u.usuario_estado,
                       p.persona_nombre, p.persona_apellido,
                       c.contacto_descripcion as persona_email
                FROM {$this->table} u
                LEFT JOIN persona p ON u.rela_persona = p.id_persona
                LEFT JOIN contacto c ON c.rela_persona = p.id_persona
                LEFT JOIN tipocontacto tc ON c.rela_tipocontacto = tc.id_tipocontacto
                WHERE c.contacto_descripcion = ?
                AND tc.tipocontacto_descripcion = 'email'
                AND c.contacto_estado = 1
                AND u.usuario_estado = 1
                LIMIT 1";
        
        $result = $this->query($sql, [$email]);
        return $result->fetch_assoc();
    }

    /**
     * Limpiar tokens expirados específicamente para recuperación de contraseña
     * Solo limpia tokens de usuarios activos que hayan expirado hace más de 1 hora
     */
    public function cleanupExpiredPasswordResetTokens()
    {
        // Solo limpiar tokens de usuarios activos que hayan expirado hace más de 1 hora
        // No afecta tokens de verificación de email de usuarios pendientes
        $sql = "UPDATE {$this->table} 
                SET usuario_token = NULL,
                    usuario_fhtoken = NULL
                WHERE usuario_fhtoken < DATE_SUB(NOW(), INTERVAL 1 HOUR)
                AND usuario_estado = 1"; // Solo usuarios activos
        
        try {
            $result = $this->query($sql);
            error_log("cleanupExpiredPasswordResetTokens: Tokens de reset expirados limpiados");
            return true;
        } catch (\Exception $e) {
            error_log("cleanupExpiredPasswordResetTokens: Error - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar contraseña de usuario logueado
     * Requiere verificar la contraseña actual por seguridad
     */
    public function changePassword($userId, $currentPassword, $newPassword)
    {
        // Verificar que el usuario existe y obtener la contraseña actual
        $usuario = $this->find($userId);
        
        if (!$usuario) {
            error_log("changePassword: Usuario no encontrado - ID: $userId");
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        // Verificar que el usuario está activo
        if ($usuario['usuario_estado'] != 1) {
            error_log("changePassword: Usuario inactivo - ID: $userId");
            return ['success' => false, 'message' => 'Usuario inactivo'];
        }

        // Verificar contraseña actual
        if (!password_verify($currentPassword, $usuario['usuario_contrasenia'])) {
            error_log("changePassword: Contraseña actual incorrecta - Usuario: " . $usuario['usuario_nombre']);
            return ['success' => false, 'message' => 'La contraseña actual es incorrecta'];
        }

        // Verificar que la nueva contraseña es diferente a la actual
        if (password_verify($newPassword, $usuario['usuario_contrasenia'])) {
            return ['success' => false, 'message' => 'La nueva contraseña debe ser diferente a la actual'];
        }

        // Hashear la nueva contraseña
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Actualizar contraseña en la base de datos
        try {
            $sql = "UPDATE {$this->table} 
                    SET usuario_contrasenia = ?
                    WHERE {$this->primaryKey} = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("si", $hashedPassword, $userId);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                error_log("changePassword: Contraseña cambiada exitosamente - Usuario: " . $usuario['usuario_nombre']);
                
                // Opcional: Limpiar tokens de recuperación si existen
                $this->clearPasswordResetTokens($userId);
                
                return [
                    'success' => true, 
                    'message' => 'Contraseña cambiada exitosamente',
                    'usuario' => $usuario['usuario_nombre']
                ];
            } else {
                error_log("changePassword: No se pudo actualizar la contraseña - Usuario ID: $userId");
                return ['success' => false, 'message' => 'Error al actualizar la contraseña'];
            }
        } catch (\Exception $e) {
            error_log("changePassword: Excepción SQL - " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    /**
     * Limpiar tokens de recuperación de contraseña para un usuario específico
     */
    private function clearPasswordResetTokens($userId)
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET usuario_token = NULL,
                        usuario_fhtoken = NULL
                    WHERE {$this->primaryKey} = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            
            error_log("clearPasswordResetTokens: Tokens limpiados para usuario ID: $userId");
        } catch (\Exception $e) {
            error_log("clearPasswordResetTokens: Error - " . $e->getMessage());
        }
    }
}