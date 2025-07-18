<h1>Formulario de alta de Servicio</h1>
<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Servicios&ruta=servicios&archivo=alta.php">
	<fieldset>
		<label>Nombre:</label>
		<input type="text" name="servicio_nombre" size="50" required><br>
		<label>Descripcion:</label>
		<input type="text" name="servicio_descripcion" size="50" required><br>
		<label>Precio Unitario:</label>
		<input type="number" name="servicio_precio" size="10" required><br>
		<label>Tipo de Servicio:</label>
		<select name="rela_tiposervicio" required>
			<option value="">Seleccione la tipo de servicio...</option>
			<?php
			$registros = $mysql->query("select * from tiposervicio where tiposervicio_estado = 1") or
				die($mysql->error);
			while ($row = $registros->fetch_assoc()) {
				echo "<option value=" . $row["id_tiposervicio"] . ">" . $row["tiposervicio_descripcion"] . "</option>";
			}
			?>
		</select><br><br>
		<input type="submit" value="Confirmar">
	</fieldset>
</form>