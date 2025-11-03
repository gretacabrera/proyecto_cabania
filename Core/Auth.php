<?php

namespace App\Core;

/**
 * Clase para manejo de autenticación y autorización
 */
class Auth
{
    /**
     * Verificar si el usuario está autenticado
     */
    public static function check()
    {
        return isset($_SESSION["usuario_nombre"]);
    }

    /**
     * Obtener usuario actual
     */
    public static function user()
    {
        if (!self::check()) {
            return null;
        }

        return $_SESSION["usuario_nombre"];
    }

    /**
     * Obtener ID del usuario actual
     */
    public static function id()
    {
        if (!self::check()) {
            return null;
        }

        return isset($_SESSION["usuario_id"]) ? $_SESSION["usuario_id"] : null;
    }

    /**
     * Iniciar sesión
     */
    public static function login($username, $userId = null)
    {
        $_SESSION["usuario_nombre"] = $username;
        if ($userId) {
            $_SESSION["usuario_id"] = $userId;
        }
    }

    /**
     * Cerrar sesión
     */
    public static function logout()
    {
        session_unset();
        session_destroy();
    }

    /**
     * Verificar si tiene permiso para un módulo
     */
    public static function hasPermission($module)
    {
        if (!self::check()) {
            return false;
        }

        $db = Database::getInstance();
        
        // Primero intentar validación exacta
        $stmt = $db->prepare("SELECT COUNT(*) as resultados
                             FROM modulo m
                             LEFT JOIN perfil_modulo pm ON pm.rela_modulo = m.id_modulo
                             LEFT JOIN perfil p ON pm.rela_perfil = p.id_perfil
                             LEFT JOIN usuario u ON u.rela_perfil = p.id_perfil
                             WHERE m.modulo_estado = 1
                             AND u.usuario_estado = 1
                             AND u.usuario_nombre = ?
                             AND m.modulo_ruta = ?");
        
        $stmt->bind_param("ss", $_SESSION["usuario_nombre"], $module);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ((int) $row["resultados"] > 0) {
            return true;
        }
        
        // Si no tiene permiso exacto, intentar con rutas que comiencen con el módulo
        $modulePattern = $module . '/%';
        $stmt2 = $db->prepare("SELECT COUNT(*) as resultados
                              FROM modulo m
                              LEFT JOIN perfil_modulo pm ON pm.rela_modulo = m.id_modulo
                              LEFT JOIN perfil p ON pm.rela_perfil = p.id_perfil
                              LEFT JOIN usuario u ON u.rela_perfil = p.id_perfil
                              WHERE m.modulo_estado = 1
                              AND u.usuario_estado = 1
                              AND u.usuario_nombre = ?
                              AND m.modulo_ruta LIKE ?");
        
        $stmt2->bind_param("ss", $_SESSION["usuario_nombre"], $modulePattern);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $row2 = $result2->fetch_assoc();
        
        return (int) $row2["resultados"] > 0;
    }

    /**
     * Verificar si es administrador
     */
    public static function isAdmin()
    {
        if (!self::check()) {
            return false;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) as es_admin
                             FROM usuario u
                             LEFT JOIN perfil p ON u.rela_perfil = p.id_perfil
                             WHERE u.usuario_nombre = ?
                             AND u.usuario_estado = 1
                             AND p.perfil_descripcion = 'administrador'
                             AND p.perfil_estado = 1");
        
        $stmt->bind_param("s", $_SESSION["usuario_nombre"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return (int) $row["es_admin"] > 0;
    }

    /**
     * Obtener perfil del usuario actual
     */
    public static function getUserProfile()
    {
        if (!self::check()) {
            error_log('DEBUG Auth::getUserProfile: Usuario no autenticado');
            return null;
        }

        error_log('DEBUG Auth::getUserProfile: Usuario autenticado: ' . $_SESSION["usuario_nombre"]);

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT p.perfil_descripcion
                             FROM perfil p
                             LEFT JOIN usuario u ON u.rela_perfil = p.id_perfil
                             WHERE u.usuario_nombre = ?");
        
        $stmt->bind_param("s", $_SESSION["usuario_nombre"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $profile = $row ? $row["perfil_descripcion"] : null;
        error_log('DEBUG Auth::getUserProfile: Perfil detectado: ' . ($profile ?? 'NULL'));
        
        return $profile;
    }

    /**
     * Obtener módulos del usuario actual
     */
    public static function getUserModules()
    {
        if (!self::check()) {
            return [];
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT men.menu_nombre, m.modulo_ruta, m.modulo_descripcion, m.rela_menu
                             FROM modulo m
                             LEFT JOIN perfil_modulo pm ON pm.rela_modulo = m.id_modulo
                             LEFT JOIN perfil p ON pm.rela_perfil = p.id_perfil
                             LEFT JOIN usuario u ON u.rela_perfil = p.id_perfil
                             LEFT JOIN menu men ON m.rela_menu = men.id_menu
                             WHERE pm.perfilmodulo_estado = 1
                             AND m.modulo_estado = 1
                             AND u.usuario_estado = 1
                             AND u.usuario_nombre = ?
                             ORDER BY men.menu_nombre, m.modulo_descripcion");
        
        $stmt->bind_param("s", $_SESSION["usuario_nombre"]);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $modules = [];
        while ($row = $result->fetch_assoc()) {
            $modules[] = $row;
        }
        
        return $modules;
    }
}