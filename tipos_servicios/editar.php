<?php
$registro = $mysql->query("select * from tiposervicio where id_tiposervicio=$_REQUEST[id_tiposervicio]");

if ($reg = $registro->fetch_array()) {
	?>
	<h1>Formulario de modificación de tipo de servicio</h1>
	<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Tipos de Servicios&ruta=tipos_servicios&archivo=modificacion.php">
		<fieldset>
			<label>Descripcion:</label>
			<input type="text" name="tiposervicio_descripcion" size="45" value="<?php echo $reg['tiposervicio_descripcion']; ?>" required><br>
			<input type="hidden" name="id_tiposervicio" value="<?php echo $_REQUEST['id_tiposervicio']; ?>" required>
			<input type="submit" value="Confirmar">
		</fieldset>
	</form>
<?php
} else
	echo 'No existe un tipo de servicio con ese id';

$mysql->close();
?>
