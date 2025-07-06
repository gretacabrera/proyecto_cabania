<?php
	require("../conexion.php");
	require_once("../funciones.php");
	
	$resultado = $mysql->query("UPDATE perfil SET perfil_estado = 1 WHERE id_perfil = $_REQUEST[id_perfil]");

    if ($resultado) {
        echo 'Perfil recuperado correctamente';
    } else {
        echo 'Error: ' . $mysql->error;
    }

    $mysql->close();
?>
