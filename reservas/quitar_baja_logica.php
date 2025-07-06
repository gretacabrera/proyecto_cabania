<?php
	require("../conexion.php");
	require_once("../funciones.php");
	
	$resultado = $mysql->query("UPDATE reserva SET rela_estadoreserva = 1 WHERE id_reserva = $_REQUEST[id_reserva]");
	
    if ($resultado) {
        echo 'Reserva recuperada correctamente';
    } else {
        echo 'Error: ' . $mysql->error;
    }

    $mysql->close();
?>
