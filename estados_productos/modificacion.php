
<?php
  require("../conexion.php");
  require("../includes/mensajes.php");

  $resultado = $mysql->query("update estadoproducto set 
				estadoproducto_descripcion='$_REQUEST[estadoproducto_descripcion]'
				where id_estadoproducto=$_REQUEST[id_estadoproducto]");

  if ($resultado) {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Se modificaron los datos del estado de producto correctamente', 'exito');
  } else {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
  }
?>