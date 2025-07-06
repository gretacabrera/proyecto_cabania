<?php
http_response_code(403); // Establecer código de respuesta HTTP 403
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acceso Denegado</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body class="home">
    <?php 
    // Incluir menú solo si el usuario está logueado
    if (isset($_SESSION["usuario_nombre"])) {
        include("menu.php"); 
    }
    ?>
    
    <div class="content">
        <div class="error-container error-403">
            <div class="icon-forbidden">🚫</div>
            <h1 class="error-code error-403">403</h1>
            <h2 class="error-title">Acceso Denegado</h2>
            <div class="error-message">
                <p>Si crees que esto es un error, contacta al administrador del sistema.</p>
            </div>
            
            <div class="error-actions">
                <a href="index.php" class="btn-error">Ir al Inicio</a>
            </div>
        </div>
    </div>
</body>
</html>
