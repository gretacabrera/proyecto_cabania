<?php
$registro = $mysql->query("select * from metododepago where id_metododepago=$_REQUEST[id_metododepago]") or
	die($mysql->error);

if ($reg = $registro->fetch_array()) {
	?>
	<h1>Formulario de modificación de metodo de pago</h1>
	<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Métodos de pago&ruta=metodos_pagos&archivo=modificacion.php">
		<fieldset>
			<label>Descripcion:</label>
			<input type="text" name="metododepago_descripcion" size="45" value="<?php echo $reg['metododepago_descripcion']; ?>" required><br>
			<input type="hidden" name="id_metododepago" value="<?php echo $_REQUEST['id_metododepago']; ?>" required>
			<input type="submit" value="Confirmar">
		</fieldset>
	</form>
<?php
} else
	echo 'No existe una  metodo de pago con ese id';

$mysql->close();
?>
