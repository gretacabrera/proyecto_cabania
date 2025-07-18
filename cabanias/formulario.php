<h1>Formulario de alta de cabaña</h1>
<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Cabañas&ruta=cabanias&archivo=alta.php" enctype="multipart/form-data">
	<fieldset>
		<label>Código:</label>
		<input type="text" name="cabania_codigo" size="45" required><br>
		
		<label>Nombre:</label>
		<input type="text" name="cabania_nombre" size="45" required><br>
		
		<label>Descripción:</label>
		<textarea name="cabania_descripcion" rows="4" cols="45" required></textarea><br>
		
		<label>Capacidad:</label>
		<input type="number" name="cabania_capacidad" min="1" required><br>
		
		<label>Precio:</label>
		<input type="number" name="cabania_precio" step="0.01" min="0" required><br>
		
		<label>Ubicación:</label>
		<input type="text" name="cabania_ubicacion" size="45" required><br>
		
		<label>Cantidad de Baños:</label>
		<input type="number" name="cabania_cantidadbanios" min="1" required><br>
		
		<label>Cantidad de Habitaciones:</label>
		<input type="number" name="cabania_cantidadhabitaciones" min="1" required><br>
		
		<label>Foto:</label>
		<input type="file" name="cabania_foto" accept="image/*"><br>
		
		<input type="submit" value="Confirmar">
	</fieldset>
</form>
