<?php
/**
 * Plantilla unificada para archivos index.php de módulos
 * 
 * Variables requeridas antes de incluir esta plantilla:
 * - $modulo_nombre: Nombre del módulo (ej: "categorias", "usuarios", etc.)
 * - $modulo_titulo: Título del módulo para mostrar (ej: "Categorías", "Usuarios", etc.)
 * - $incluir_listado: (opcional) Si se debe incluir listado.php automáticamente (default: true)
 */

// Validar que se hayan definido las variables requeridas
if (!isset($modulo_nombre)) {
    die("Error: Variable \$modulo_nombre no definida. Debe establecerse antes de incluir la plantilla.");
}

if (!isset($modulo_titulo)) {
    die("Error: Variable \$modulo_titulo no definida. Debe establecerse antes de incluir la plantilla.");
}

// Valor por defecto para incluir listado
if (!isset($incluir_listado)) {
    $incluir_listado = true;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <title>ABM de <?php echo htmlspecialchars($modulo_titulo); ?></title>
    <link rel="stylesheet" href="../estilos.css">
    <script src="../funciones.js"></script>
</head>
<body class="home">
    <?php include("../menu.php"); ?>
    
    <div class="content">
        <?php
        require_once("../funciones.php");
        
        // Mostrar mensajes si la función existe
        if (function_exists('mostrar_mensaje')) {
            mostrar_mensaje();
        }
        
        // Validar permisos para el módulo
        if (validar_permiso($modulo_nombre)) {
            if ($incluir_listado && file_exists("listado.php")) {
                include("listado.php");
            } elseif (!$incluir_listado && file_exists("formulario.php")) {
                include("formulario.php");
            } elseif (isset($contenido_personalizado)) {
                // Permitir contenido personalizado si se define
                echo $contenido_personalizado;
            } else {
                // Incluir página 404 si no se encuentra el archivo requerido
                include("../404.php");
            }
        } else {
            // Incluir página 403 si no tiene permisos
            include("../403.php");
        }
        ?>
    </div>
</body>
</html>
