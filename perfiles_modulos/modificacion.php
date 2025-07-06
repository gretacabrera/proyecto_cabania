
<?php
  require("../conexion.php");
  require("../includes/mensajes.php");

  $resultado = $mysql->query("update perfilmodulo set 
				rela_perfil=$_REQUEST[rela_perfil],
        rela_modulo=$_REQUEST[rela_modulo]
				where id_perfilmodulo=$_REQUEST[id_perfilmodulo]");

  if ($resultado) {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Se modificaron los datos de la asignación perfil-módulo correctamente', 'exito');
  } else {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
  }
?>