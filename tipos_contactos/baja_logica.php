<?php
	require("../conexion.php");
	require_once("../funciones.php");

	$resultado = $mysql->query("update tipocontacto set tipocontacto_estado = 0 WHERE id_tipocontacto=$_REQUEST[id_tipocontacto]");

	if ($resultado) {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Tipo de contacto dado de baja correctamente', 'exito');
	} else {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
	}
?>