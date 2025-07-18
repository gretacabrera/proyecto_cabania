<?php
require_once("../conexion.php");

$resultado = $mysql->query("update tipocontacto set tipocontacto_estado = 0 WHERE id_tipocontacto=$_REQUEST[id_tipocontacto]");

if ($resultado) {
	echo 'Se dió de baja correctamente el tipo de contacto';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>