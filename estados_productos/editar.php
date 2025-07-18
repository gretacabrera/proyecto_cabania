<?php
$registro = $mysql->query("select * from estadoproducto where id_estadoproducto=$_REQUEST[id_estadoproducto]") or
	die($mysql->error);

if ($reg = $registro->fetch_array()) {
	?>
	<h1>Formulario de modificación de estado de producto</h1>
	<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Estados de productos&ruta=estados_productos&archivo=modificacion.php">
		<fieldset>
			<label>Descripcion:</label>
			<input type="text" name="estadoproducto_descripcion" size="45" value="<?php echo $reg['estadoproducto_descripcion']; ?>" required><br>
			<input type="hidden" name="id_estadoproducto" value="<?php echo $_REQUEST['id_estadoproducto']; ?>" required>
			<input type="submit" value="Confirmar">
		</fieldset>
	</form>
<?php
} else
	echo 'No existe un estado de producto con ese id';

$mysql->close();
?>
