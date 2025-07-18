<?php
$registro = $mysql->query("select * from categoria where id_categoria=$_REQUEST[id_categoria]") or
	die($mysql->error);

if ($reg = $registro->fetch_array()) {
	?>
	<h1>Formulario de modificación de categoria</h1>
	<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Categorías&ruta=categorias&archivo=modificacion.php">
		<fieldset>
			<label>Descripcion:</label>
			<input type="text" name="categoria_descripcion" size="45" value="<?php echo $reg['categoria_descripcion']; ?>" required><br>
			<input type="hidden" name="id_categoria" value="<?php echo $_REQUEST['id_categoria']; ?>" required>
			<input type="submit" value="Confirmar">
		</fieldset>
	</form>
<?php
} else
	echo 'No existe una categoria con ese id';

$mysql->close();
?>
