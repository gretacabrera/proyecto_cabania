<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesion</title>
    <link rel="stylesheet" href="../estilos.css">
</head>
<body class="centered-body login">
    <div class="loginform">
        <h2>CASA DE PALOS</h2>
        <h3>CABAÑAS</h3>
        <br><br>
        <form action="validar_credenciales.php" method="post">
            <fieldset>
                <input type="text" name="usuario_nombre" placeholder="Ingrese su usuario..." required><br><br>
                <input type="password" name="usuario_contrasenia" placeholder="Ingrese su contraseña..." required><br><br>
                <input type="submit" value="INICIAR SESION">
            </fieldset>
        </form>
        <br><br>
        <label><b>¿Olvidó su contraseña?</b></label><br><br>
        <label><b>¿No tiene una cuenta? <a href="registro.php">Registrarse</a></b></label>
    </div>
</body>
</html>