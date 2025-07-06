<?php
	require("../conexion.php");
	require_once("../funciones.php");
	
	$resultado = $mysql->query("UPDATE categoria SET categoria_estado = 1 WHERE id_categoria = $_REQUEST[id_categoria]");
	
    if ($resultado) {
        echo 'Categoría recuperada correctamente';
    } else {
        echo 'Error: ' . $mysql->error;
    }

    $mysql->close();
?>
