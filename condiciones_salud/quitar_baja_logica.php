<?php
	require("../conexion.php");
	require_once("../funciones.php");
	
	$resultado = $mysql->query("UPDATE condicionsalud SET condicionsalud_estado = 1 WHERE id_condicionsalud = $_REQUEST[id_condicionsalud]");
	
    if ($resultado) {
        echo 'Condicion de salud recuperada correctamente';
    } else {
        echo 'Error: ' . $mysql->error;
    }

    $mysql->close();
?>
