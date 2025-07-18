<h1>Formulario de alta de menu</h1>
<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Menus&ruta=menus&archivo=alta.php">
	<fieldset>
		<label>Nombre:</label>
		<input type="text" name="menu_nombre" size="45" required><br>
		<label>Orden:</label>
		<input type="number" name="menu_orden" min="1" required><br>
		<input type="submit" value="Confirmar">
	</fieldset>
</form>
