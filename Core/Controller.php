<?php

namespace App\Core;

/**
 * Clase base para todos los controladores
 */
abstract class Controller
{
    protected $view;

    public function __construct()
    {
        $this->view = new View();
    }

    /**
     * Renderizar vista
     */
    protected function render($template, $data = [], $layout = null)
    {
        return $this->view->render($template, $data, $layout);
    }

    /**
     * Renderizar JSON
     */
    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redireccionar
     */
    protected function redirect($url, $message = null, $type = 'info')
    {
        // Construir URL completa si es una ruta relativa
        if (strpos($url, 'http') !== 0) {
            $url = url($url);
        }
        
        if ($message) {
            $url .= (strpos($url, '?') !== false ? '&' : '?') . 
                   'mensaje=' . urlencode($message) . '&tipo=' . urlencode($type);
        }
        header("Location: " . $url);
        exit;
    }

    /**
     * Validar permisos
     */
    protected function hasPermission($module)
    {
        return Auth::hasPermission($module);
    }

    /**
     * Verificar autenticación
     */
    protected function requireAuth()
    {
        if (!Auth::check()) {
            $this->redirect('/auth/login');
        }
    }

    /**
     * Verificar permiso específico
     */
    protected function requirePermission($module)
    {
        $this->requireAuth();
        
        if (!$this->hasPermission($module)) {
            return $this->view->error(403);
        }
    }

    /**
     * Obtener parámetros POST
     */
    protected function post($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    /**
     * Obtener parámetros GET
     */
    protected function get($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    /**
     * Validar si es petición POST
     */
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Validar si es petición AJAX
     */
    protected function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}