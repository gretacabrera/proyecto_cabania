<?php
	require("../conexion.php");
	require_once("../funciones.php");
	
	$resultado = $mysql->query("UPDATE usuario SET usuario_estado = 1 WHERE id_usuario = $_REQUEST[id_usuario]");
	
    if ($resultado) {
        echo 'usuario recuperada correctamente';
    } else {
        echo 'Error: ' . $mysql->error;
    }

    $mysql->close();
?>
