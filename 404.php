<?php
// Establecer código de respuesta HTTP 404
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página No Encontrada</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body class="home">
    <div class="content">
        <div class="error-container error-404">
            <div class="icon-notfound">🔍</div>
            <h1 class="error-code error-404">404</h1>
            <h2 class="error-title">Página No Encontrada</h2>
            <div class="error-message">
                <p>Lo sentimos, la página que estás buscando no existe o ha sido movida.</p>
                <p>Verifica que la URL esté escrita correctamente o navega desde el menú principal.</p>
            </div>            
            <div class="error-actions">
                <a href="index.php" class="btn-error">Ir al Inicio</a>
            </div>
        </div>
    </div>
</body>
</html>
