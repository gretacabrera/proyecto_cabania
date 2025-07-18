<?php
require_once("../conexion.php");

$resultado = $mysql->query("UPDATE tipocontacto SET tipocontacto_estado = 1 WHERE id_tipocontacto = $_REQUEST[id_tipocontacto]");

if ($resultado) {
	echo 'Tipo de contacto recuperado correctamente';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
