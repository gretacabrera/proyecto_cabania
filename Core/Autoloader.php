<?php

namespace App\Core;

/**
 * Autoloader para el sistema MVC
 * Maneja la carga automática de clases del proyecto
 */

class Autoloader
{
    /**
     * Mapeo de prefijos de namespace a directorios
     */
    protected static $classMaps = [
        'App\\Models\\' => __DIR__ . '/../Models/',
        'App\\Controllers\\' => __DIR__ . '/../Controllers/',
        'App\\Core\\' => __DIR__ . '/../Core/',
    ];

    /**
     * Registra el autoloader
     */
    public static function register()
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * Función de autoload
     * @param string $className
     */
    public static function autoload($className)
    {
        // Convertir namespace separators a directory separators
        $className = str_replace('\\', '/', $className);
        
        // Buscar en el mapeo de clases
        foreach (self::$classMaps as $prefix => $baseDir) {
            $prefix = str_replace('\\', '/', $prefix);
            
            if (strpos($className, $prefix) === 0) {
                // Remover el prefijo del nombre de clase
                $relativeClass = substr($className, strlen($prefix));
                $file = $baseDir . $relativeClass . '.php';
                
                if (file_exists($file)) {
                    require $file;
                    return;
                }
            }
        }
        
        // Fallback: buscar en el directorio raíz de la aplicación
        $file = __DIR__ . '/../' . $className . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
}