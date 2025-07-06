
<?php
  require("../conexion.php");
  require_once("../funciones.php");

  $resultado = $mysql->query("update producto set 
				producto_nombre='$_REQUEST[producto_nombre]',
				producto_descripcion='$_REQUEST[producto_descripcion]',
				producto_precio=$_REQUEST[producto_precio],
				producto_stock=$_REQUEST[producto_stock],
				producto_foto='$_REQUEST[producto_foto]',
				rela_marca=$_REQUEST[rela_marca],
				rela_categoria=$_REQUEST[rela_categoria],
				rela_estadoproducto=$_REQUEST[rela_estadoproducto]
				where id_producto=$_REQUEST[id_producto]");

  if ($resultado) {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Se modificaron los datos del producto correctamente', 'exito');
  } else {
	$mysql->close();
	redireccionar_con_mensaje('index.php', 'Error: ' . $mysql->error, 'error');
  }
?>