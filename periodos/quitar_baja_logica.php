<?php
	require_once("../conexion.php");
	
	$resultado = $mysql->query("UPDATE periodo SET periodo_estado = 1 WHERE id_periodo = $_REQUEST[id_periodo]");

    if ($resultado) {
        echo 'Período recuperado correctamente';
    } else {
        echo 'Error: ' . $mysql->error;
    }

    $mysql->close();
?>
