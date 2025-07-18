<?php
$registro = $mysql->query("select * from estadopersona where id_estadopersona=$_REQUEST[id_estadopersona]") or
	die($mysql->error);

if ($reg = $registro->fetch_array()) {
	?>
	<h1>Formulario de modificación de estado de persona</h1>
	<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Estados de personas&ruta=estados_personas&archivo=modificacion.php">
		<fieldset>
			<label>Descripcion:</label>
			<input type="text" name="estadopersona_descripcion" size="45" value="<?php echo $reg['estadopersona_descripcion']; ?>" required><br>
			<input type="hidden" name="id_estadopersona" value="<?php echo $_REQUEST['id_estadopersona']; ?>" required>
			<input type="submit" value="Confirmar">
		</fieldset>
	</form>
<?php
} else
	echo 'No existe un estado de persona con ese id';

$mysql->close();
?>
