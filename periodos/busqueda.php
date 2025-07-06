<form method="post" action="index.php">
    Descripción:
    <input type="text" name="periodo_descripcion" value="<?php if (isset($_REQUEST["periodo_descripcion"])){ echo $_REQUEST["periodo_descripcion"]; } ?>">
	Año:
    <input type="text" name="periodo_anio" value="<?php if (isset($_REQUEST["periodo_anio"])){ echo $_REQUEST["periodo_anio"]; } ?>">
	Estado:
	<select name="periodo_estado">
		<option value="">Seleccione el estado del periodo...</option>
		<option value="1"
		<?php
			if (isset($_REQUEST["periodo_estado"])){
				if ($_REQUEST["periodo_estado"] == 1){
					echo "selected";
				}
			}
		?>
		>Activo</option>
		<option value="0"<?php
			if (isset($_REQUEST["periodo_estado"])){
				if ($_REQUEST["periodo_estado"] == 0){
					echo "selected";
				}
			}
		?>
		>Baja</option>
	</select>
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>