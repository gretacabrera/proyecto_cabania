
<?php
require("conexion.php");
$resultado = $mysql->query("update periodo set 
			periodo_descripcion='$_REQUEST[periodo_descripcion]',
			periodo_fechainicio='$_REQUEST[periodo_fechainicio]',
			periodo_fechafin='$_REQUEST[periodo_fechafin]',
			periodo_anio=$_REQUEST[periodo_anio],
			periodo_orden=$_REQUEST[periodo_orden]
			where id_periodo=$_REQUEST[id_periodo]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Períodos&ruta=periodos&archivo=listado.php',
		'Se modificaron los datos del período correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>