<?php
	require("../conexion.php");
	require("../includes/mensajes.php");
	
	$resultado = $mysql->query("UPDATE estadopersona SET estadopersona_estado = 1 WHERE id_estadopersona = $_REQUEST[id_estadopersona]");
	
    if ($resultado) {
        echo 'Estado de persona recuperado correctamente';
    } else {
        echo 'Error: ' . $mysql->error;
    }

    $mysql->close();
?>
