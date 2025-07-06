<?php
	require("../conexion.php");
	require("../includes/mensajes.php");
	
	$resultado = $mysql->query("insert into perfil_modulo (rela_perfil, rela_modulo, perfilmodulo_estado) values ($_REQUEST[rela_perfil], $_REQUEST[rela_modulo], 1)");
	
	if ($resultado) {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Se dió de alta la asignación perfil-modulo correctamente', 'exito');
	} else {
		$mysql->close();
		redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
	}
?>
