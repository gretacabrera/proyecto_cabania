<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php">
	Fecha desde:
	<input type="date" name="fecha_desde" min="2000-01-01" max="2030-12-31" value="<?php if (isset($_REQUEST['fecha_desde'])){ echo $_REQUEST['fecha_desde']; } ?>">
	Fecha hasta:
	<input type="date" name="fecha_hasta" min="2000-01-01" max="2030-12-31" value="<?php if (isset($_REQUEST['fecha_hasta'])){ echo $_REQUEST['fecha_hasta']; } ?>">
	Puntuación:
	<select name="puntuacion">
		<option value="">Todas</option>
		<?php
			for ($i = 5; $i >= 1; $i--) {
				echo "<option value='$i'";
				if (isset($_REQUEST['puntuacion']) && $_REQUEST['puntuacion'] == $i) {
					echo " selected";
				}
				echo ">{$i} estrella" . ($i > 1 ? 's' : '') . "</option>";
			}
		?>
	</select>
	Estado:
	<select name="comentario_estado">
		<option value="">Todos</option>
		<option value="1" <?php if (isset($_REQUEST['comentario_estado']) && $_REQUEST['comentario_estado'] == "1") echo "selected"; ?>>Activo</option>
		<option value="0" <?php if (isset($_REQUEST['comentario_estado']) && $_REQUEST['comentario_estado'] == "0") echo "selected"; ?>>Baja</option>
	</select>
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>