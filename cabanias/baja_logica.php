<?php
require("../conexion.php");
$resultado = $mysql->query("update cabania set cabania_estado=0 where id_cabania=$_REQUEST[id_cabania]");

if ($resultado) {
	echo 'Se eliminó la cabaña correctamente';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
