<?php
	require("../conexion.php");
	require("../includes/mensajes.php");

	$resultado = $mysql->query("update tiposervicio set tiposervicio_estado = 0 WHERE id_tiposervicio=$_REQUEST[id_tiposervicio]");

	if ($resultado) {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Tipo de servicio dado de baja correctamente', 'exito');
	} else {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
	}
?>