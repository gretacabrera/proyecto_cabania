<?php
	require("../conexion.php");
	require("../includes/mensajes.php");
	
	$resultado = $mysql->query("UPDATE modulo SET modulo_estado = 1 WHERE id_modulo = $_REQUEST[id_modulo]");

    if ($resultado) {
        echo 'Módulo recuperado correctamente';
    } else {
        echo 'Error: ' . $mysql->error;
    }

    $mysql->close();
?>
