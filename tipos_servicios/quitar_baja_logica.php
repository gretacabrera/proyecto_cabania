<?php
	require("../conexion.php");
	require_once("../funciones.php");
	
	$resultado = $mysql->query("UPDATE tiposervicio SET tiposervicio_estado = 1 WHERE id_tiposervicio = $_REQUEST[id_tiposervicio]");
	
	if ($resultado) {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Tipo de servicio recuperado correctamente', 'exito');
	} else {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
	}
?>
