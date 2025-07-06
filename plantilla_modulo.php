<?php
/**
 * Plantilla unificada para archivos index.php de módulos
 * 
 * Variables requeridas antes de incluir esta plantilla:
 * - $ruta: directorio del módulo (ej: "categorias", "usuarios", etc.)
 * - $titulo: Título del módulo para mostrar (ej: "Categorías", "Usuarios", etc.)
 */

// Validar que se hayan definido las variables requeridas
if (!isset($_GET["ruta"])) {
    die("Error: Variable \$ruta no definida. Debe establecerse antes de incluir la plantilla.");
}

if (!isset($_GET["titulo"])) {
    die("Error: Variable \$titulo no definida. Debe establecerse antes de incluir la plantilla.");
}

$ruta = $_GET["ruta"];
$titulo = $_GET["titulo"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABM de <?php echo htmlspecialchars($titulo); ?></title>
    <link rel="stylesheet" href="estilos.css">
    <script src="funciones.js"></script>
</head>
<body class="home">  
    <?php
        require("conexion.php");
        require_once("funciones.php");
        include("menu.php");
    ?>  
    <div class="content">
        <?php
        // Mostrar mensajes si la función existe
        if (function_exists('mostrar_mensaje')) {
            mostrar_mensaje();
        }  

        if (validar_permiso($ruta)) {
            if (file_exists($ruta."/listado.php")) {
                include($ruta."/listado.php");
            } elseif (file_exists($ruta."/index.php")) {
                include($ruta."/index.php");
            } else {
                // Incluir página 404 si no se encuentra el archivo requerido
                include("404.php");
            }
        } else {
            // Incluir página 403 si no tiene permisos
            include("403.php");
        }
        ?>
    </div>
</body>
</html>
