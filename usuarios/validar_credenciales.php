<?php

include('../conexion.php');
require_once('../funciones.php');

// Validaciones del lado del servidor
if (empty($_REQUEST['usuario_nombre']) || empty($_REQUEST['usuario_contrasenia'])) {
    redireccionar_con_mensaje('login.php', 'Todos los campos son obligatorios', 'error');
}

if (strlen($_REQUEST['usuario_nombre']) < 3) {
    redireccionar_con_mensaje('login.php', 'El usuario debe tener al menos 3 caracteres', 'error');
}

$registro = $mysql->query("select * from usuario 
                        where usuario_nombre = '$_REQUEST[usuario_nombre]'
                        and usuario_estado <> 3") 
or die($mysql->error);

if ($reg = $registro->fetch_array()) {
    if (password_verify($_REQUEST["usuario_contrasenia"], $reg["usuario_contrasenia"])){
        session_start();
        $_SESSION["usuario_nombre"] = $_REQUEST["usuario_nombre"];
        $mysql->close(); // se cierra la conexion antes de redireccionar
        header("Location: ../home.php");
    }
    else{
        redireccionar_con_mensaje('login.php', 'Contraseña incorrecta. Por favor, reintente.', 'error');
    }
}
else{
    redireccionar_con_mensaje('login.php', 'No existe un usuario registrado con ese nombre.', 'error');
}
$mysql->close();

?>