<?php
/**
 * Punto de entrada principal del sistema MVC
 * Casa de Palos - Sistema de Gestión de Cabañas
 */

// Configurar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cargar autoload de Composer para dependencias externas
require_once __DIR__ . '/vendor/autoload.php';

// Cargar el autoloader personalizado para las clases del proyecto
require_once __DIR__ . '/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Core\Application;

try {
    // Crear y ejecutar la aplicación MVC
    $app = new Application();
    $app->run();
    
} catch (Exception $e) {
    // Manejo de errores detallado
    echo "<!DOCTYPE html><html><head><title>Error del Sistema</title></head><body>";
    echo "<h1>Error del Sistema</h1>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</body></html>";
} catch (Error $e) {
    // Capturar errores fatales también
    echo "<!DOCTYPE html><html><head><title>Error Fatal</title></head><body>";
    echo "<h1>Error Fatal del Sistema</h1>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</body></html>";
}
?>
