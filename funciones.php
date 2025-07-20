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

// ============================================================================
// FUNCIONES DE PAGINACIÓN
// Implementación procedimental para compatibilidad con el proyecto existente
// ============================================================================

/**
 * Calcula la información de paginación
 */
function calcular_paginacion($total_registros, $registros_por_pagina = 10, $pagina_actual = 1) {
    $pagina_actual = max(1, intval($pagina_actual));
    $total_paginas = ceil($total_registros / $registros_por_pagina);
    
    // Asegurar que la página actual no exceda el total
    if ($pagina_actual > $total_paginas && $total_paginas > 0) {
        $pagina_actual = $total_paginas;
    }
    
    $offset = ($pagina_actual - 1) * $registros_por_pagina;
    
    return [
        'pagina_actual' => $pagina_actual,
        'total_paginas' => $total_paginas,
        'total_registros' => $total_registros,
        'registros_por_pagina' => $registros_por_pagina,
        'offset' => $offset,
        'limite' => $registros_por_pagina
    ];
}

/**
 * Genera los enlaces de paginación
 */
function generar_enlaces_paginacion($paginacion, $url_base, $parametros_adicionales = []) {
    if ($paginacion['total_paginas'] <= 1) {
        return '';
    }
    
    $html = '<div class="pagination-container">';
    $html .= '<div class="pagination">';
    
    // Botón anterior
    if ($paginacion['pagina_actual'] > 1) {
        $url_anterior = construir_url_paginacion($url_base, $paginacion['pagina_actual'] - 1, $parametros_adicionales);
        $html .= '<a href="' . $url_anterior . '" class="pagination-link">« Anterior</a>';
    } else {
        $html .= '<span class="pagination-link disabled">« Anterior</span>';
    }
    
    // Números de página
    $inicio = max(1, $paginacion['pagina_actual'] - 2);
    $fin = min($paginacion['total_paginas'], $paginacion['pagina_actual'] + 2);
    
    if ($inicio > 1) {
        $url_primera = construir_url_paginacion($url_base, 1, $parametros_adicionales);
        $html .= '<a href="' . $url_primera . '" class="pagination-link">1</a>';
        if ($inicio > 2) {
            $html .= '<span class="pagination-link disabled">...</span>';
        }
    }
    
    for ($i = $inicio; $i <= $fin; $i++) {
        if ($i == $paginacion['pagina_actual']) {
            $html .= '<span class="pagination-link active">' . $i . '</span>';
        } else {
            $url_pagina = construir_url_paginacion($url_base, $i, $parametros_adicionales);
            $html .= '<a href="' . $url_pagina . '" class="pagination-link">' . $i . '</a>';
        }
    }
    
    if ($fin < $paginacion['total_paginas']) {
        if ($fin < $paginacion['total_paginas'] - 1) {
            $html .= '<span class="pagination-link disabled">...</span>';
        }
        $url_ultima = construir_url_paginacion($url_base, $paginacion['total_paginas'], $parametros_adicionales);
        $html .= '<a href="' . $url_ultima . '" class="pagination-link">' . $paginacion['total_paginas'] . '</a>';
    }
    
    // Botón siguiente
    if ($paginacion['pagina_actual'] < $paginacion['total_paginas']) {
        $url_siguiente = construir_url_paginacion($url_base, $paginacion['pagina_actual'] + 1, $parametros_adicionales);
        $html .= '<a href="' . $url_siguiente . '" class="pagination-link">Siguiente »</a>';
    } else {
        $html .= '<span class="pagination-link disabled">Siguiente »</span>';
    }
    
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Construye URL con parámetros de paginación
 */
function construir_url_paginacion($url_base, $pagina, $parametros_adicionales = []) {
    $parametros = array_merge($parametros_adicionales, ['pagina' => $pagina]);
    return $url_base . '&' . http_build_query($parametros);
}

/**
 * Genera información de registros mostrados
 */
function mostrar_info_paginacion($paginacion) {
    if ($paginacion['total_registros'] == 0) {
        return "No se encontraron registros";
    }
    
    $inicio = $paginacion['offset'] + 1;
    $fin = min($paginacion['offset'] + $paginacion['registros_por_pagina'], $paginacion['total_registros']);
    return "Mostrando {$inicio} a {$fin} de {$paginacion['total_registros']} registros";
}

/**
 * Función helper para obtener registros paginados usando mysqli
 */
function obtener_registros_paginados($mysql, $query_base, $query_count, $pagina = 1, $por_pagina = 10) {
    // Contar total de registros
    $result_count = $mysql->query($query_count);
    if (!$result_count) {
        die("Error en query count: " . $mysql->error);
    }
    $total_registros = $result_count->fetch_row()[0];
    
    // Calcular paginación
    $paginacion = calcular_paginacion($total_registros, $por_pagina, $pagina);
    
    // Obtener registros paginados
    $query_paginada = $query_base . " LIMIT " . $paginacion['limite'] . " OFFSET " . $paginacion['offset'];
    $result = $mysql->query($query_paginada);
    
    if (!$result) {
        die("Error en query paginada: " . $mysql->error);
    }
    
    $registros = [];
    while ($row = $result->fetch_assoc()) {
        $registros[] = $row;
    }
    
    return [
        'registros' => $registros,
        'paginacion' => $paginacion
    ];
}

?>
