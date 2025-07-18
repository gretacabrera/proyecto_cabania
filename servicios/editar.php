<?php
require("conexion.php");

$registro = $mysql->query("select * from servicio where id_servicio=$_REQUEST[id_servicio]") or
	die($mysql->error);

if ($reg = $registro->fetch_array()) {
?>
	<h1>Formulario de modificación de servicio</h1>
	<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Servicios&ruta=servicios&archivo=modificacion.php">
		<fieldset>
			<label>Nombre:</label>
			<input type="text" name="servicio_nombre" size="50" value="<?php echo $reg['servicio_nombre']; ?>" required><br>
			<label>Descripcion:</label>
			<input type="text" name="servicio_descripcion" size="50" value="<?php echo $reg['servicio_descripcion']; ?>" required><br>
			<label>Precio Unitario:</label>
			<input type="number" name="servicio_precio" size="10" value="<?php echo $reg['servicio_precio']; ?>" required><br>
			<label>Tipo de Servicio:</label>
			<select name="rela_tiposervicio" required>
				<option value="">Seleccione la tipo de servicio...</option>
				<?php
				$registros = $mysql->query("select * from tiposervicio where tiposervicio_estado = 1") or
					die($mysql->error);
				while ($row = $registros->fetch_assoc()) {
					echo "<option value=" . $row["id_tiposervicio"];
					if ($row["id_tiposervicio"] == $reg['rela_tiposervicio']) {
						echo " selected";
					}
					echo ">" . $row["tiposervicio_descripcion"] . "</option>";
				}
				?>
			</select><br>
			<input type="hidden" name="id_servicio" value="<?php echo $_REQUEST['id_servicio']; ?>" required>
			<input type="submit" value="Confirmar">
		</fieldset>
	</form>
<?php
} else
	echo 'No existe un servicio con ese id';

$mysql->close();

?>