<?php
	require("../conexion.php");
	require_once("../funciones.php");
	
	$resultado = $mysql->query("UPDATE metododepago SET metododepago_estado = 1 WHERE id_ = $_REQUEST[id_metododepago]");
	
    if ($resultado) {
        echo 'Categoría recuperada correctamente';
    } else {
        echo 'Error: ' . $mysql->error;
    }

    $mysql->close();
?>
