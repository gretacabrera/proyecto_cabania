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
    <title><?php echo htmlspecialchars($titulo); ?></title>
    <link rel="stylesheet" href="estilos.css">
    <script src="funciones.js"></script>
</head>
<body class="home">  
    <?php
        require_once("conexion.php");
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
            // Verificar si se especifica un archivo específico
            if (isset($_GET["archivo"])) {
                $archivo = $_GET["archivo"];
                if (file_exists($ruta."/".$archivo)) {
                    // Pasar todos los parámetros GET al archivo incluido
                    foreach ($_GET as $key => $value) {
                        if ($key != 'ruta' && $key != 'titulo' && $key != 'archivo') {
                            $_REQUEST[$key] = $value;
                            $_GET[$key] = $value;
                        }
                    }
                    include($ruta."/".$archivo);
                } else {
                    // Incluir página 404 si no se encuentra el archivo específico
                    include("404.php");
                }
            } elseif (file_exists($ruta."/listado.php")) {
                include($ruta."/listado.php");
            } elseif (file_exists($ruta.".php")) {
                include($ruta.".php");
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
