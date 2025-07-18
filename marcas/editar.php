<?php
$registro = $mysql->query("select * from marca where id_marca=$_REQUEST[id_marca]") or
	die($mysql->error);

if ($reg = $registro->fetch_array()) {
	?>
	<h1>Formulario de modificación de marca</h1>
	<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Marcas&ruta=marcas&archivo=modificacion.php">
		<fieldset>
			<label>Descripcion:</label>
			<input type="text" name="marca_descripcion" size="45" value="<?php echo $reg['marca_descripcion']; ?>" required><br>
			<input type="hidden" name="id_marca" value="<?php echo $_REQUEST['id_marca']; ?>" required>
			<input type="submit" value="Confirmar">
		</fieldset>
	</form>
<?php
} else
	echo 'No existe una marca con ese id';

$mysql->close();
?>
