<?php
$registro = $mysql->query("select * from perfil where id_perfil=$_REQUEST[id_perfil]") or
	die($mysql->error);

if ($reg = $registro->fetch_array()) {
	?>
	<h1>Formulario de modificación de Perfiles</h1>
	<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Perfiles&ruta=perfiles&archivo=modificacion.php">
		<fieldset>
			<label>Descripcion:</label>
			<input type="text" name="perfil_descripcion" size="45" value="<?php echo $reg['perfil_descripcion']; ?>" required><br>
			<input type="hidden" name="id_perfil" value="<?php echo $_REQUEST['id_perfil']; ?>" required>
			<input type="submit" value="Confirmar">
		</fieldset>
	</form>
<?php
} else
	echo 'No existe un perfil con ese id';

$mysql->close();
?>
