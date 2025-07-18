<?php
require("conexion.php");

$registro = $mysql->query("select * from periodo where id_periodo=$_REQUEST[id_periodo]") or
	die($mysql->error);

if ($reg = $registro->fetch_array()) {
?>
	<h1>Formulario de modificación de periodos</h1>
	<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Períodos&ruta=periodos&archivo=modificacion.php">
		<fieldset>
			<label>Descripcion:</label>
			<input type="text" name="periodo_descripcion" size="45" value="<?php echo $reg['periodo_descripcion']; ?>" required><br>
			<label>Año:</label>
			<input type="number" name="periodo_anio" size="4" value="<?php echo $reg['periodo_anio']; ?>"><br>
			<label>Fecha de Inicio:</label>
			<input type="datetime-local" name="periodo_fechainicio" value="<?php echo $reg["periodo_fechainicio"]; ?>"><br>
			<label>Fecha de Fin:</label>
			<input type="datetime-local" name="periodo_fechafin" value="<?php echo $reg["periodo_fechafin"]; ?>"><br>
			<label>Orden:</label>
			<input type="number" name="periodo_orden" size="1" value="<?php echo $reg['periodo_orden']; ?>"><br>
			<input type="hidden" name="id_periodo" value="<?php echo $_REQUEST['id_periodo']; ?>" required>
			<input type="submit" value="Confirmar">
		</fieldset>
	</form>
<?php
} else
	echo 'No existe un periodo con ese id';

$mysql->close();

?>