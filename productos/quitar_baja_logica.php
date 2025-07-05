<?php
	require("../conexion.php");
	require("../includes/mensajes.php");
	
	$resultado = $mysql->query("UPDATE producto SET rela_estadoproducto = 1 WHERE id_producto = $_REQUEST[id_producto]");
	
    if ($resultado) {
        echo 'Producto recuperado correctamente';
    } else {
        echo 'Error: ' . $mysql->error;
    }

    $mysql->close();
?>
