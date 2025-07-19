<form id="form_reserva" method="post" action="plantilla_modulo.php?titulo=Seleccion de Cabaña&ruta=reservas/online&archivo=seleccion_cabania.php">
	<fieldset>
		<legend>Ingresar fechas de búsqueda</legend><br>
		<label>Fecha de llegada:</label>
		<input type="datetime-local" min="2024-11-26T00:00" name="reserva_fhinicio" required><br><br>
		<label>Fecha de salida:</label>
		<input type="datetime-local" min="2024-11-26T00:00" name="reserva_fhfin" required><br><br>
		<input type="submit" value="Buscar cabañas >">
	</fieldset>
</form>