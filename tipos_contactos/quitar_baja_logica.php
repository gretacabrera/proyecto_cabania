<?php
	require("../conexion.php");
	require("../includes/mensajes.php");
	
	$resultado = $mysql->query("UPDATE tipocontacto SET tipocontacto_estado = 1 WHERE id_tipocontacto = $_REQUEST[id_tipocontacto]");
	
	if ($resultado) {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Tipo de contacto recuperado correctamente', 'exito');
	} else {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
	}
?>
