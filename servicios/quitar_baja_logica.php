<?php
	require("../conexion.php");
	require_once("../funciones.php");
	
	$resultado = $mysql->query("UPDATE servicio SET servicio_estado = 1 WHERE id_servicio = $_REQUEST[id_servicio]");
	
	if ($resultado) {
        echo 'Servicio recuperado correctamente';
    } else {
        echo 'Error: ' . $mysql->error;
    }

    $mysql->close();
?>
