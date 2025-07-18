<h1>Formulario de alta de modulo</h1>
<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Módulos&ruta=modulos&archivo=alta.php">
	<fieldset>
		<label>Descripcion:</label>
		<input type="text" name="modulo_descripcion" size="45" required><br>
		<label>Ruta:</label>
		<input type="text" name="modulo_ruta" size="45" required><br>
		<label>Menu:</label>
		<select name="rela_menu">
			<option value="">Seleccione un menu (opcional)...</option>
			<?php
			$menus = $mysql->query("select * from menu where menu_estado = 1 order by menu_nombre") or die($mysql->error);
			while ($menu = $menus->fetch_assoc()) {
				echo "<option value='".$menu["id_menu"]."'>".$menu["menu_nombre"]."</option>";
			}
			?>
		</select><br>
		<input type="submit" value="Confirmar">
	</fieldset>
</form>
