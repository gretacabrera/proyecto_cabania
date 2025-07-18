<?php
require_once("../conexion.php");

$resultado = $mysql->query("update producto set rela_estadoproducto = 4 WHERE id_producto=$_REQUEST[id_producto]");

if ($resultado) {
	echo 'Se dió de baja correctamente el producto';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>