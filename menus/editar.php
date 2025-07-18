<?php
$registro = $mysql->query("select * from menu where id_menu=$_REQUEST[id_menu]") or
	die($mysql->error);

if ($reg = $registro->fetch_array()) {
	?>
	<h1>Formulario de modificación de menu</h1>
	<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Menus&ruta=menus&archivo=modificacion.php">
		<fieldset>
			<label>Nombre:</label>
			<input type="text" name="menu_nombre" size="45" value="<?php echo $reg['menu_nombre']; ?>" required><br>
			<label>Orden:</label>
			<input type="number" name="menu_orden" min="1" value="<?php echo $reg['menu_orden']; ?>" required><br>
			<input type="hidden" name="id_menu" value="<?php echo $_REQUEST['id_menu']; ?>" required>
			<input type="submit" value="Confirmar">
		</fieldset>
	</form>
<?php
} else
	echo 'No existe un menu con ese id';

$mysql->close();
?>
