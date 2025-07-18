
<?php
require("conexion.php");
$resultado = $mysql->query("update categoria set 
			categoria_descripcion='$_REQUEST[categoria_descripcion]'
			where id_categoria=$_REQUEST[id_categoria]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Categorías&ruta=categorias&archivo=listado.php',
		'Se modificaron los datos de la categoría correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>