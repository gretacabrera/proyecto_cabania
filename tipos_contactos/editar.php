<?php
require("conexion.php");

$registro = $mysql->query("select * from tipocontacto where id_tipocontacto=$_REQUEST[id_tipocontacto]") or
	die($mysql->error);

if ($reg = $registro->fetch_array()) {
?>
	<h1>Formulario de modificación de tipo de contacto</h1>
	<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Tipos de Contacto&ruta=tipos_contactos&archivo=modificacion.php">
		<fieldset>
			<label>Descripcion:</label>
			<input type="text" name="tipocontacto_descripcion" size="45" value="<?php echo $reg['tipocontacto_descripcion']; ?>" required><br>
			<input type="hidden" name="id_tipocontacto" value="<?php echo $_REQUEST['id_tipocontacto']; ?>" required>
			<input type="submit" value="Confirmar">
		</fieldset>
	</form>
<?php
} else
	echo 'No existe un tipo de contacto con ese id';

$mysql->close();

?>