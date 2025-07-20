<?php
require("../conexion.php");
$resultado = $mysql->query("update cabania set cabania_estado=1 where id_cabania=$_REQUEST[id_cabania]");

if ($resultado) {
	echo 'Se recuperó la cabaña correctamente';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
