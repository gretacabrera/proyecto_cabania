
<?php
require("conexion.php");
$resultado = $mysql->query("update servicio set 
			servicio_nombre='$_REQUEST[servicio_nombre]',
			servicio_descripcion='$_REQUEST[servicio_descripcion]',
			servicio_precio=$_REQUEST[servicio_precio],
			rela_tiposervicio=$_REQUEST[rela_tiposervicio]
			where id_servicio=$_REQUEST[id_servicio]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Servicios&ruta=servicios&archivo=listado.php',
		'Se modificaron los datos del servicio correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>

  ?>