<?php
require("conexion.php");

$resultado = $mysql->query("update tipocontacto set 
			tipocontacto_descripcion='$_REQUEST[tipocontacto_descripcion]'
			where id_tipocontacto=$_REQUEST[id_tipocontacto]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Tipos de contactos&ruta=tipos_contactos&archivo=listado.php',
		'Se modificaron los datos del tipo de contacto correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
