<form method="post" action="index.php">
    Descripción:
    <input type="text" name="estadoreserva_descripcion" value="<?php if (isset($_REQUEST["estadoreserva_descripcion"])){ echo $_REQUEST["estadoreserva_descripcion"]; } ?>">
	Estado:
	<select name="estadoreserva_estado">
		<option value="">Seleccione el estado de la estado de reserva...</option>
		<option value="1"
		<?php
			if (isset($_REQUEST["estadoreserva_estado"])){
				if ($_REQUEST["estadoreserva_estado"] == 1){
					echo "selected";
				}
			}
		?>
		>Activo</option>
		<option value="0"<?php
			if (isset($_REQUEST["estadoreserva_estado"])){
				if ($_REQUEST["estadoreserva_estado"] == 0){
					echo "selected";
				}
			}
		?>
		>Baja</option>
	</select>
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>