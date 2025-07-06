<?php
	require("../conexion.php");
?>
<form method="post" action="index.php">
    Descripción:
    <input type="text" name="metodopago_descripcion" value="<?php if (isset($_REQUEST["metodopago_descripcion"])){ echo $_REQUEST["metodopago_descripcion"]; } ?>">
	Estado:
	<select name="metodopago_estado">
		<option value="">Seleccione el estado de la metodo de pago...</option>
		<option value="1"
		<?php
			if (isset($_REQUEST["metodopago_estado"])){
				if ($_REQUEST["metodopago_estado"] == 1){
					echo "selected";
				}
			}
		?>
		>Activo</option>
		<option value="0"<?php
			if (isset($_REQUEST["metodopago_estado"])){
				if ($_REQUEST["metodopago_estado"] == 0){
					echo "selected";
				}
			}
		?>
		>Baja</option>
	</select>
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>

<?php
	$mysql->close();
?>