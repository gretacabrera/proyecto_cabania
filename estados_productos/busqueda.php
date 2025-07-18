<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Estados de Productos&ruta=estados_productos">
    Descripción:
    <input type="text" name="estadoproducto_descripcion" value="<?php if (isset($_REQUEST["estadoproducto_descripcion"])){ echo $_REQUEST["estadoproducto_descripcion"]; } ?>">
	Estado:
	<select name="estadoproducto_estado">
		<option value="">Seleccione el estado de la estado de producto...</option>
		<option value="1"
		<?php
			if (isset($_REQUEST["estadoproducto_estado"])){
				if ($_REQUEST["estadoproducto_estado"] == 1){
					echo "selected";
				}
			}
		?>
		>Activo</option>
		<option value="0"<?php
			if (isset($_REQUEST["estadoproducto_estado"])){
				if ($_REQUEST["estadoproducto_estado"] == 0){
					echo "selected";
				}
			}
		?>
		>Baja</option>
	</select>
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>