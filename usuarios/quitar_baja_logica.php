<?php
$resultado = $mysql->query("UPDATE usuario SET usuario_estado = 1 WHERE id_usuario = $_REQUEST[id_usuario]");

if ($resultado) {
	echo 'Usuario recuperado correctamente';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
