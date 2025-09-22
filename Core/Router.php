<?php

namespace App\Core;

/**
 * Router para el sistema MVC
 */
class Router
{
    protected $routes = [];
    
    /**
     * Agregar ruta GET
     */
    public function get($pattern, $callback)
    {
        $this->addRoute('GET', $pattern, $callback);
    }
    
    /**
     * Agregar ruta POST
     */
    public function post($pattern, $callback)
    {
        $this->addRoute('POST', $pattern, $callback);
    }
    
    /**
     * Agregar ruta para cualquier método
     */
    public function any($pattern, $callback)
    {
        $this->addRoute(['GET', 'POST'], $pattern, $callback);
    }
    
    /**
     * Agregar ruta
     */
    protected function addRoute($method, $pattern, $callback)
    {
        $this->routes[] = [
            'method' => is_array($method) ? $method : [$method],
            'pattern' => $pattern,
            'callback' => $callback
        ];
    }
    
    /**
     * Resolver ruta
     */
    public function resolve($uri, $method)
    {
        foreach ($this->routes as $route) {
            if (in_array($method, $route['method']) && $this->matchPattern($route['pattern'], $uri, $params)) {
                return $this->callAction($route['callback'], $params);
            }
        }
        
        // No se encontró ruta
        return $this->notFound();
    }
    
    /**
     * Verificar si el patrón coincide con la URI
     */
    protected function matchPattern($pattern, $uri, &$params)
    {
        $params = [];
        
        // Convertir patrón a regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches);
            $params = $matches;
            return true;
        }
        
        return false;
    }
    
    /**
     * Ejecutar acción
     */
    protected function callAction($callback, $params)
    {
        if (is_string($callback)) {
            // Formato: "ControllerName@method"
            if (strpos($callback, '@') !== false) {
                list($controller, $method) = explode('@', $callback);
                return $this->callControllerMethod($controller, $method, $params);
            }
            
            // Formato: "ControllerName" (método index por defecto)
            return $this->callControllerMethod($callback, 'index', $params);
        }
        
        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }
        
        throw new \Exception("Callback no válido");
    }
    
    /**
     * Llamar método de controlador
     */
    protected function callControllerMethod($controllerName, $method, $params)
    {
        $controllerClass = "App\\Controllers\\" . $controllerName;
        
        if (!class_exists($controllerClass)) {
            throw new \Exception("Controlador no encontrado: " . $controllerClass);
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            throw new \Exception("Método no encontrado: " . $method);
        }
        
        return call_user_func_array([$controller, $method], $params);
    }
    
    /**
     * Página no encontrada
     */
    protected function notFound()
    {
        http_response_code(404);
        $view = new View();
        return $view->error(404);
    }
    
    /**
     * Obtener URI limpia
     */
    public function getCurrentUri()
    {
        $uri = $_SERVER['REQUEST_URI'];
        
        // Remover query string
        if (strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        
        // Remover base path si existe
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/' && $basePath !== '\\') {
            $uri = substr($uri, strlen($basePath));
        }
        
        return '/' . ltrim($uri, '/');
    }
}