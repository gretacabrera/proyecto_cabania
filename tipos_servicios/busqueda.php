<form method="post" action="index.php">
    Descripción:
    <input type="text" name="tiposervicio_descripcion" value="<?php if (isset($_REQUEST["tiposervicio_descripcion"])){ echo $_REQUEST["tiposervicio_descripcion"]; } ?>">
	Estado:
	<select name="tiposervicio_estado">
		<option value="">Seleccione el estado del tipo de servicio...</option>
		<option value="1"
		<?php
			if (isset($_REQUEST["tiposervicio_estado"])){
				if ($_REQUEST["tiposervicio_estado"] == 1){
					echo "selected";
				}
			}
		?>
		>Activo</option>
		<option value="0"<?php
			if (isset($_REQUEST["tiposervicio_estado"])){
				if ($_REQUEST["tiposervicio_estado"] == 0){
					echo "selected";
				}
			}
		?>
		>Baja</option>
	</select>
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>