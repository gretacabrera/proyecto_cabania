<?php

namespace App\Core;

/**
 * Clase para el manejo de vistas
 */
class View
{
    protected $viewPath;
    protected $layoutPath;

    public function __construct()
    {
        $this->viewPath = __DIR__ . '/../Views/';
        $this->layoutPath = __DIR__ . '/../Views/shared/layouts/';
    }

    /**
     * Renderizar vista
     */
    public function render($template, $data = [], $layout = 'main')
    {
        // Extraer variables para usar en la vista
        extract($data);

        // Capturar contenido de la vista
        ob_start();
        
        $templateFile = $this->viewPath . $template . '.php';
        if (file_exists($templateFile)) {
            include $templateFile;
        } else {
            throw new \Exception("Vista no encontrada: " . $template);
        }
        
        $content = ob_get_clean();

        // Si hay layout, renderizar con layout
        if ($layout) {
            $layoutFile = $this->layoutPath . $layout . '.php';
            if (file_exists($layoutFile)) {
                ob_start();
                // Pasar el contexto de View al layout para que tenga acceso a $this
                $view = $this;
                include $layoutFile;
                return ob_get_clean();
            }
        }

        return $content;
    }

    /**
     * Renderizar vista directamente sin layout
     */
    public function renderPartial($template, $data = [])
    {
        extract($data);
        
        $templateFile = $this->viewPath . $template . '.php';
        if (file_exists($templateFile)) {
            include $templateFile;
        } else {
            throw new \Exception("Vista no encontrada: " . $template);
        }
    }

    /**
     * Mostrar página de error
     */
    public function error($code)
    {
        http_response_code($code);
        
        $errorFile = $this->viewPath . 'shared/errors/' . $code . '.php';
        if (file_exists($errorFile)) {
            include $errorFile;
        } else {
            // Fallback genérico
            echo "<h1>Error $code</h1>";
        }
        exit;
    }

    /**
     * Incluir componente
     */
    public function component($name, $data = [])
    {
        extract($data);
        
        $componentFile = $this->viewPath . 'shared/components/' . $name . '.php';
        if (file_exists($componentFile)) {
            // Pasar el contexto de View al componente
            $view = $this;
            include $componentFile;
        }
    }

    /**
     * Escapar datos para output HTML
     */
    public function escape($data)
    {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generar URL
     */
    public function url($path = '')
    {
        $baseUrl = $this->getBaseUrl();
        return $baseUrl . '/' . ltrim($path, '/');
    }

    /**
     * Obtener URL base
     */
    private function getBaseUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $script = dirname($_SERVER['SCRIPT_NAME']);
        
        // Si el script está en la raíz, no agregar barra extra
        if ($script === '/' || $script === '\\') {
            $script = '';
        }
        
        return $protocol . '://' . $host . $script;
    }

    /**
     * Incluir asset (CSS, JS)
     */
    public function asset($file)
    {
        $baseUrl = $this->getBaseUrl();
        return $baseUrl . '/' . ltrim($file, '/');
    }
}