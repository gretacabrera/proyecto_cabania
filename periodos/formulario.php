<h1>Formulario de alta de periodo</h1>
<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Períodos&ruta=periodos&archivo=alta.php">
	<fieldset>
		<label>Descripcion:</label>
		<input type="text" name="periodo_descripcion" size="45" required><br>
		<label>Año:</label>
		<input type="number" name="periodo_anio" size="4"><br>
		<label>Fecha y hora de inicio:</label>
		<input type="datetime-local" name="periodo_fechainicio" required><br><br>
		<label>Fecha y hora de fin:</label>
		<input type="datetime-local" name="periodo_fechafin" required><br><br>
		<label>Orden:</label>
		<input type="number" name="periodo_orden" size="1"><br><br>
		<input type="submit" value="Confirmar">
	</fieldset>
</form>