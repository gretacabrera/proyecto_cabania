<?php
	require("../conexion.php");
	require_once("../funciones.php");
	
	$resultado = $mysql->query("UPDATE estadoreserva SET estadoreserva_estado = 1 WHERE id_estadoreserva = $_REQUEST[id_estadoreserva]");
	
    if ($resultado) {
        echo 'Estado de reserva recuperado correctamente';
    } else {
        echo 'Error: ' . $mysql->error;
    }

    $mysql->close();
?>
