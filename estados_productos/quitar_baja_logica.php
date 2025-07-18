<?php
	require_once("../conexion.php");
	
	$resultado = $mysql->query("UPDATE estadoproducto SET estadoproducto_estado = 1 WHERE id_estadoproducto = $_REQUEST[id_estadoproducto]");
	
    if ($resultado) {
        echo 'Estado de producto recuperado correctamente';
    } else {
        echo 'Error: ' . $mysql->error;
    }

    $mysql->close();
?>
