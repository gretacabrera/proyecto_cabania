<?php

// ============================================================================
// ARCHIVO UNIFICADO DE FUNCIONES
// Contiene funciones de validaciones, mensajes y carga de variables de entorno
// ============================================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
} 

// ============================================================================
// FUNCIONES DE CARGA DE VARIABLES DE ENTORNO
// ============================================================================

/**
 * Carga variables de entorno desde un archivo .env
 * @param string $file Ruta al archivo .env
 */
function loadEnv($file) {
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue; // Ignorar comentarios
            }
            $parts = explode('=', trim($line), 2);
            if (count($parts) == 2) {
                list($key, $value) = $parts;
                putenv("$key=$value");
            }
        }
    }
}

// Cargar variables de entorno automáticamente
loadEnv(__DIR__ . '/.env');

// ============================================================================
// FUNCIONES DE VALIDACIÓN Y PERMISOS
// ============================================================================

/**
 * Valida si un usuario tiene permiso para acceder a un módulo específico
 * @param string $modulo Ruta del módulo a validar
 * @return bool True si tiene permiso, False en caso contrario
 */
function validar_permiso($modulo) {
    require("conexion.php");
    $tiene_permiso = false;  
    if (isset($_SESSION["usuario_nombre"])) {
        $resultado = $mysql->query("SELECT COUNT(*) as resultados
                                    FROM modulo m
                                    LEFT JOIN perfil_modulo pm ON pm.rela_modulo = m.id_modulo
                                    LEFT JOIN perfil p ON pm.rela_perfil = p.id_perfil
                                    LEFT JOIN usuario u ON u.rela_perfil = p.id_perfil
                                    WHERE m.modulo_estado = 1
                                    AND u.usuario_estado = 1
                                    AND u.usuario_nombre = '$_SESSION[usuario_nombre]'
                                    AND m.modulo_ruta = '$modulo'") or
        die($mysql->error);
        $fila = $resultado->fetch_array();
        if ((int) $fila["resultados"] > 0) {
            $tiene_permiso = true;
        }
        $mysql->close();
    }
    return $tiene_permiso;
}

/**
 * Verifica si el usuario actual es administrador
 * @return bool True si es administrador, False en caso contrario
 */
function es_administrador() {
    require("conexion.php");
    
    // Verificar si hay sesión activa
    if (!isset($_SESSION["usuario_nombre"])) {
        return false;
    }
    
    if (isset($_SESSION["usuario_nombre"])) {
        $resultado = $mysql->query("SELECT COUNT(*) as es_admin
                                    FROM usuario u
                                    LEFT JOIN perfil p ON u.rela_perfil = p.id_perfil
                                    WHERE u.usuario_nombre = '$_SESSION[usuario_nombre]'
                                    AND u.usuario_estado = 1
                                    AND p.perfil_descripcion = 'administrador'
                                    AND p.perfil_estado = 1") or
        die($mysql->error);
        $fila = $resultado->fetch_array();
        $mysql->close();

        return (int) $fila["es_admin"] > 0;
    }
    return false;
}

// ============================================================================
// FUNCIONES DE MENSAJES DEL SISTEMA
// ============================================================================

/**
 * Muestra un mensaje global basado en los parámetros GET de la URL
 * Soporta tipos: exito, error, warning, info
 */
function mostrar_mensaje() {
    if (isset($_GET['mensaje']) && isset($_GET['tipo'])) {
        $clase_css = '';
        switch ($_GET['tipo']) {
            case 'exito':
                $clase_css = 'mensaje-exito';
                break;
            case 'error':
                $clase_css = 'mensaje-error';
                break;
            case 'warning':
                $clase_css = 'mensaje-warning';
                break;
            default:
                $clase_css = 'mensaje-info';
        }
        
        echo '<div class="' . $clase_css . '" id="mensaje-global">';
        echo htmlspecialchars($_GET['mensaje']);
        echo '<button type="button" class="cerrar-mensaje" onclick="cerrarMensaje()">×</button>';
        echo '</div>';
        
        // Agregar JavaScript para cerrar el mensaje y limpiar URL
        echo '<script>
        function cerrarMensaje() {
            document.getElementById("mensaje-global").style.display = "none";
            // Limpiar solo los parámetros de mensaje manteniendo los demás
            if (window.history && window.history.replaceState) {
                var url_obj = new URL(window.location.href);
                url_obj.searchParams.delete("mensaje");
                url_obj.searchParams.delete("tipo");
                window.history.replaceState({path: url_obj.toString()}, "", url_obj.toString());
            }
        }
        
        // Auto-ocultar mensaje después de 5 segundos
        setTimeout(function() {
            var mensaje = document.getElementById("mensaje-global");
            if (mensaje) {
                mensaje.style.display = "none";
                // Limpiar solo los parámetros de mensaje manteniendo los demás
                if (window.history && window.history.replaceState) {
                    var url_obj = new URL(window.location.href);
                    url_obj.searchParams.delete("mensaje");
                    url_obj.searchParams.delete("tipo");
                    window.history.replaceState({path: url_obj.toString()}, "", url_obj.toString());
                }
            }
        }, 5000);
        </script>';
    }
}

/**
 * Redirecciona a una URL con un mensaje y tipo específico
 * @param string $url URL de destino
 * @param string $mensaje Mensaje a mostrar
 * @param string $tipo Tipo de mensaje (exito, error, warning, info)
 */
function redireccionar_con_mensaje($url, $mensaje, $tipo = 'info') {
    $url_con_mensaje = $url . (strpos($url, '?') !== false ? '&' : '?') . 
                       'mensaje=' . urlencode($mensaje) . '&tipo=' . urlencode($tipo);
    header("Location: " . $url_con_mensaje);
    exit();
}

?>
