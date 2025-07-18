<?php
$registro = $mysql->query("select * from estadoreserva where id_estadoreserva=$_REQUEST[id_estadoreserva]") or
	die($mysql->error);

if ($reg = $registro->fetch_array()) {
	?>
	<h1>Formulario de modificación de estado de reserva</h1>
	<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Estados de reservas&ruta=estados_reservas&archivo=modificacion.php">
		<fieldset>
			<label>Descripcion:</label>
			<input type="text" name="estadoreserva_descripcion" size="45" value="<?php echo $reg['estadoreserva_descripcion']; ?>" required><br>
			<input type="hidden" name="id_estadoreserva" value="<?php echo $_REQUEST['id_estadoreserva']; ?>" required>
			<input type="submit" value="Confirmar">
		</fieldset>
	</form>
<?php
} else
	echo 'No existe un estado de reserva con ese id';

$mysql->close();
?>
