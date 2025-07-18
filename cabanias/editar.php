<?php
$registro = $mysql->query("select * from cabania where id_cabania=$_REQUEST[id_cabania]") or
	die($mysql->error);

if ($reg = $registro->fetch_array()) {
	?>
	<h1>Formulario de modificación de cabaña</h1>
	<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Cabañas&ruta=cabanias&archivo=modificacion.php" enctype="multipart/form-data">
		<fieldset>
			<label>Código:</label>
			<input type="text" name="cabania_codigo" size="45" value="<?php echo $reg['cabania_codigo']; ?>" required><br>
			
			<label>Nombre:</label>
			<input type="text" name="cabania_nombre" size="45" value="<?php echo $reg['cabania_nombre']; ?>" required><br>
			
			<label>Descripción:</label>
			<textarea name="cabania_descripcion" rows="4" cols="45" required><?php echo $reg['cabania_descripcion']; ?></textarea><br>
			
			<label>Capacidad:</label>
			<input type="number" name="cabania_capacidad" min="1" value="<?php echo $reg['cabania_capacidad']; ?>" required><br>
			
			<label>Precio:</label>
			<input type="number" name="cabania_precio" step="0.01" min="0" value="<?php echo $reg['cabania_precio']; ?>" required><br>
			
			<label>Ubicación:</label>
			<input type="text" name="cabania_ubicacion" size="45" value="<?php echo $reg['cabania_ubicacion']; ?>" required><br>
			
			<label>Cantidad de Baños:</label>
			<input type="number" name="cabania_cantidadbanios" min="1" value="<?php echo $reg['cabania_cantidadbanios']; ?>" required><br>
			
			<label>Cantidad de Habitaciones:</label>
			<input type="number" name="cabania_cantidadhabitaciones" min="1" value="<?php echo $reg['cabania_cantidadhabitaciones']; ?>" required><br>
			
			<label>Foto actual:</label>
			<?php if ($reg['cabania_foto']): ?>
				<img src="imagenes/cabanias/<?php echo $reg['cabania_foto']; ?>" width="100" height="100"><br>
			<?php else: ?>
				<span>Sin foto</span><br>
			<?php endif; ?>
			
			<label>Nueva foto (opcional):</label>
			<input type="file" name="cabania_foto" accept="image/*"><br>
			
			<input type="hidden" name="id_cabania" value="<?php echo $_REQUEST['id_cabania']; ?>" required>
			<input type="hidden" name="cabania_foto_actual" value="<?php echo $reg['cabania_foto']; ?>">
			<input type="submit" value="Confirmar">
		</fieldset>
	</form>
<?php
} else
	echo 'No existe una cabaña con ese id';

$mysql->close();
?>
