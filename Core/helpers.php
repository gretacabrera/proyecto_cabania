<?php

/**
 * Funciones Helper Globales
 * Funciones auxiliares para usar en vistas y controladores
 */

if (!function_exists('session')) {
    /**
     * Obtener o establecer datos de sesión
     * 
     * @param string|null $key Clave de sesión
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    function session($key = null, $default = null)
    {
        // Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si no se proporciona clave, devolver toda la sesión
        if ($key === null) {
            return new class {
                public function has($key) {
                    return isset($_SESSION[$key]);
                }
                
                public function get($key, $default = null) {
                    return $_SESSION[$key] ?? $default;
                }
                
                public function set($key, $value) {
                    $_SESSION[$key] = $value;
                }
                
                public function forget($key) {
                    unset($_SESSION[$key]);
                }
                
                public function flush() {
                    $_SESSION = [];
                }
            };
        }

        // Devolver valor específico de sesión
        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('old')) {
    /**
     * Obtener valores de input anteriores (para formularios)
     * 
     * @param string $key Clave del input
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    function old($key, $default = null)
    {
        // Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Buscar en datos de sesión old
        if (isset($_SESSION['old_input'][$key])) {
            return $_SESSION['old_input'][$key];
        }

        // Buscar en POST actual
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }

        // Buscar en GET actual
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }

        return $default;
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Generar token CSRF
     * 
     * @return string
     */
    function csrf_token()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generar campo oculto CSRF para formularios
     * 
     * @return string
     */
    function csrf_field()
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('method_field')) {
    /**
     * Generar campo de método para formularios (PUT, DELETE, etc.)
     * 
     * @param string $method
     * @return string
     */
    function method_field($method)
    {
        return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
    }
}

if (!function_exists('asset')) {
    /**
     * Generar URL de asset
     * 
     * @param string $path
     * @return string
     */
    function asset($path)
    {
        // Construir URL base dinámicamente
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $script = dirname($_SERVER['SCRIPT_NAME']);
        $baseUrl = $protocol . $host . rtrim($script, '/');
        
        // Fallback a variable de entorno si está disponible
        if (empty($host)) {
            $baseUrl = getenv('APP_URL') ?: 'http://localhost';
        }
        
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    /**
     * Generar URL completa
     * 
     * @param string $path
     * @return string
     */
    function url($path = '')
    {
        // Construir URL base dinámicamente
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $script = dirname($_SERVER['SCRIPT_NAME']);
        $baseUrl = $protocol . $host . rtrim($script, '/');
        
        // Fallback a variable de entorno si está disponible
        if (empty($host)) {
            $baseUrl = getenv('APP_URL') ?: 'http://localhost';
        }
        
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    /**
     * Realizar redirección (función global)
     * 
     * @param string $url
     * @param int $statusCode
     * @return void
     */
    function redirect($url, $statusCode = 302)
    {
        http_response_code($statusCode);
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('back')) {
    /**
     * Redireccionar a la página anterior
     * 
     * @return void
     */
    function back()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        redirect($referer);
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die - para debugging
     * 
     * @param mixed ...$vars
     * @return void
     */
    function dd(...$vars)
    {
        echo '<style>
            .dd-container { 
                background: #f8f9fa; 
                padding: 20px; 
                margin: 10px; 
                border: 1px solid #dee2e6; 
                border-radius: 5px;
                font-family: monospace;
            }
        </style>';
        
        foreach ($vars as $var) {
            echo '<div class="dd-container">';
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
            echo '</div>';
        }
        
        die();
    }
}

if (!function_exists('env')) {
    /**
     * Obtener variable de entorno
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        // Convertir valores booleanos
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
        }
        
        return $value;
    }
}

if (!function_exists('config')) {
    /**
     * Obtener configuración
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function config($key, $default = null)
    {
        // Por ahora, usar variables de entorno
        // En el futuro se puede expandir para usar archivos de configuración
        return env($key, $default);
    }
}

if (!function_exists('flash')) {
    /**
     * Establecer mensaje flash
     * 
     * @param string $key
     * @param string $message
     * @return void
     */
    function flash($key, $message)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['flash'][$key] = $message;
    }
}

if (!function_exists('get_flash')) {
    /**
     * Obtener mensaje flash (y eliminarlo)
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function get_flash($key, $default = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['flash'][$key])) {
            $value = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $value;
        }
        
        return $default;
    }
}

if (!function_exists('has_flash')) {
    /**
     * Verificar si existe mensaje flash
     * 
     * @param string $key
     * @return bool
     */
    function has_flash($key)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['flash'][$key]);
    }
}

if (!function_exists('e')) {
    /**
     * Escapar HTML entities (equivalente a htmlspecialchars)
     * 
     * @param string $value
     * @return string
     */
    function e($value)
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('str_limit')) {
    /**
     * Limitar longitud de string
     * 
     * @param string $value
     * @param int $limit
     * @param string $end
     * @return string
     */
    function str_limit($value, $limit = 100, $end = '...')
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }
        
        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
    }
}

// ===================================================================
// FUNCIONES LEGACY PARA COMPATIBILIDAD CON VISTAS NO MIGRADAS
// NOTA: Estas funciones son temporales. Las vistas que las usan
// deberían ser migradas completamente al sistema MVC.
// ===================================================================

if (!function_exists('es_administrador')) {
    /**
     * Verificar si el usuario actual es administrador
     * LEGACY: Usar en controladores MVC instead
     */
    function es_administrador() {
        return isset($_SESSION['usuario_perfil']) && 
               strtolower($_SESSION['usuario_perfil']) === 'administrador';
    }
}

if (!function_exists('obtener_registros_paginados')) {
    /**
     * Función legacy para paginación
     * DEPRECADA: Usar el sistema de paginación del modelo MVC
     */
    function obtener_registros_paginados($mysql, $query_base, $query_count, $pagina_actual, $registros_por_pagina) {
        $offset = ($pagina_actual - 1) * $registros_por_pagina;
        
        // Ejecutar query de conteo
        $count_result = $mysql->query($query_count);
        $count_row = $count_result->fetch_row();
        $total_registros = $count_row[0];
        
        // Ejecutar query principal con LIMIT
        $query_with_limit = $query_base . " LIMIT $registros_por_pagina OFFSET $offset";
        $result = $mysql->query($query_with_limit);
        
        $registros = [];
        while ($row = $result->fetch_assoc()) {
            $registros[] = $row;
        }
        
        // Calcular información de paginación
        $total_paginas = ceil($total_registros / $registros_por_pagina);
        
        return [
            'registros' => $registros,
            'paginacion' => [
                'pagina_actual' => $pagina_actual,
                'total_paginas' => $total_paginas,
                'total_registros' => $total_registros,
                'registros_por_pagina' => $registros_por_pagina,
                'inicio' => $offset + 1,
                'fin' => min($offset + $registros_por_pagina, $total_registros)
            ]
        ];
    }
}

if (!function_exists('mostrar_info_paginacion')) {
    /**
     * Mostrar información de paginación
     * DEPRECADA: Usar las vistas MVC apropiadas
     */
    function mostrar_info_paginacion($paginacion) {
        if (empty($paginacion)) return '';
        
        $inicio = $paginacion['inicio'] ?? 0;
        $fin = $paginacion['fin'] ?? 0;
        $total = $paginacion['total_registros'] ?? 0;
        
        return "Mostrando registros del {$inicio} al {$fin} de {$total} total";
    }
}

if (!function_exists('generar_enlaces_paginacion')) {
    /**
     * Generar enlaces de paginación
     * DEPRECADA: Usar las vistas MVC apropiadas
     */
    function generar_enlaces_paginacion($paginacion, $base_url, $params = []) {
        if (empty($paginacion) || $paginacion['total_paginas'] <= 1) {
            return '';
        }
        
        $pagina_actual = $paginacion['pagina_actual'];
        $total_paginas = $paginacion['total_paginas'];
        
        // Limpiar URL base
        $base_url = strtok($base_url, '?');
        
        // Convertir parámetros a query string
        $query_params = [];
        foreach ($params as $key => $value) {
            if ($key !== 'pagina' && $value !== '') {
                $query_params[] = urlencode($key) . '=' . urlencode($value);
            }
        }
        
        $html = '<nav aria-label="Navegación de páginas">';
        $html .= '<ul class="pagination justify-content-center">';
        
        // Botón "Anterior"
        if ($pagina_actual > 1) {
            $prev_url = $base_url . '?' . implode('&', $query_params) . '&pagina=' . ($pagina_actual - 1);
            $html .= '<li class="page-item"><a class="page-link" href="' . $prev_url . '">&laquo; Anterior</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">&laquo; Anterior</span></li>';
        }
        
        // Números de página
        $start = max(1, $pagina_actual - 2);
        $end = min($total_paginas, $pagina_actual + 2);
        
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $pagina_actual) {
                $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                $page_url = $base_url . '?' . implode('&', $query_params) . '&pagina=' . $i;
                $html .= '<li class="page-item"><a class="page-link" href="' . $page_url . '">' . $i . '</a></li>';
            }
        }
        
        // Botón "Siguiente"
        if ($pagina_actual < $total_paginas) {
            $next_url = $base_url . '?' . implode('&', $query_params) . '&pagina=' . ($pagina_actual + 1);
            $html .= '<li class="page-item"><a class="page-link" href="' . $next_url . '">Siguiente &raquo;</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Siguiente &raquo;</span></li>';
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
    }
}