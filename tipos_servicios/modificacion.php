
<?php
require("conexion.php");
$resultado = $mysql->query("update tiposervicio set 
			tiposervicio_descripcion='$_REQUEST[tiposervicio_descripcion]'
			where id_tiposervicio=$_REQUEST[id_tiposervicio]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Tipos de servicios&ruta=tipos_servicios&archivo=listado.php',
		'Se modificaron los datos del tipo de servicio correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>

  ?>