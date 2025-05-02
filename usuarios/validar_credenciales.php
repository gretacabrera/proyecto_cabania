<?php

include('../conexion.php');

$registro = $mysql->query("select * from usuario 
                        where usuario_nombre = '$_REQUEST[usuario_nombre]'
                        and usuario_estado <> 3") 
or die($mysql->error);

if ($reg = $registro->fetch_array()) {

    $registro = $mysql->query("select * from usuario 
                            where usuario_nombre = '$_REQUEST[usuario_nombre]'
                            and usuario_contrasenia = '$_REQUEST[usuario_contrasenia]'") 
    or die($mysql->error);

    if ($reg = $registro->fetch_array()) {
        session_start();
        $_SESSION["usuario_nombre"] = $_REQUEST["usuario_nombre"];
        $mysql->close(); // se cierra la conexion antes de redireccionar
        header("Location: ../home.php");
    }
    else{
        echo 'Contraseña incorrecta. Por favor, reintente.';
        echo '<br>';
		echo '<button onclick="location.href=\'login.php\'">Volver</button>';
    }
}
else{
    echo 'No existe un usuario registrado con ese nombre. Por favor, reintente o ingrese a Registrarse.';
    echo '<br>';
    echo '<button onclick="location.href=\'login.php\'">Volver</button>';
}
$mysql->close();

?>