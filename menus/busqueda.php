<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Menus&ruta=menus">
    Nombre:
    <input type="text" name="menu_nombre" value="<?php if (isset($_REQUEST["menu_nombre"])){ echo $_REQUEST["menu_nombre"]; } ?>">
	Estado:
	<select name="menu_estado">
		<option value="">Seleccione el estado del menu...</option>
		<option value="1"
		<?php
			if (isset($_REQUEST["menu_estado"])){
				if ($_REQUEST["menu_estado"] == 1){
					echo "selected";
				}
			}
		?>
		>Activo</option>
		<option value="0"<?php
			if (isset($_REQUEST["menu_estado"])){
				if ($_REQUEST["menu_estado"] == 0){
					echo "selected";
				}
			}
		?>
		>Baja</option>
	</select>
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>
