<?php

$registro = $mysql->query("select * from condicionsalud where id_condicionsalud=$_REQUEST[id_condicionsalud]") or
	die($mysql->error);

if ($reg = $registro->fetch_array()) {
?>
	<h1>Formulario de modificación de condicion de salud</h1>
	<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Condiciones de Salud&ruta=condiciones_salud&archivo=modificacion.php">
		<fieldset>
			<label>Descripcion:</label>
			<input type="text" name="condicionsalud_descripcion" size="45" value="<?php echo $reg['condicionsalud_descripcion']; ?>" required><br>
			<input type="hidden" name="id_condicionsalud" value="<?php echo $_REQUEST['id_condicionsalud']; ?>" required>
			<input type="submit" value="Confirmar">
		</fieldset>
	</form>
<?php
} else
	echo 'No existe una condicion de salud con ese id';

$mysql->close();

?>