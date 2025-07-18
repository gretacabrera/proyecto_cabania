<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Métodos de Pagos&ruta=metodos_pagos">
    Descripción:
    <input type="text" name="metododepago_descripcion" value="<?php if (isset($_REQUEST["metododepago_descripcion"])){ echo $_REQUEST["metododepago_descripcion"]; } ?>">
	Estado:
	<select name="metododepago_estado">
		<option value="">Seleccione el estado de la metodo de pago...</option>
		<option value="1"
		<?php
			if (isset($_REQUEST["metododepago_estado"])){
				if ($_REQUEST["metododepago_estado"] == 1){
					echo "selected";
				}
			}
		?>
		>Activo</option>
		<option value="0"<?php
			if (isset($_REQUEST["metododepago_estado"])){
				if ($_REQUEST["metododepago_estado"] == 0){
					echo "selected";
				}
			}
		?>
		>Baja</option>
	</select>
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>