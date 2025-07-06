<form method="post" action="index.php">
    Descripción:
    <input type="text" name="tipocontacto_descripcion" value="<?php if (isset($_REQUEST["tipocontacto_descripcion"])){ echo $_REQUEST["tipocontacto_descripcion"]; } ?>">
	Estado:
	<select name="tipocontacto_estado">
		<option value="">Seleccione el estado del tipo de contacto...</option>
		<option value="1"
		<?php
			if (isset($_REQUEST["tipocontacto_estado"])){
				if ($_REQUEST["tipocontacto_estado"] == 1){
					echo "selected";
				}
			}
		?>
		>Activo</option>
		<option value="0"<?php
			if (isset($_REQUEST["tipocontacto_estado"])){
				if ($_REQUEST["tipocontacto_estado"] == 0){
					echo "selected";
				}
			}
		?>
		>Baja</option>
	</select>
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>