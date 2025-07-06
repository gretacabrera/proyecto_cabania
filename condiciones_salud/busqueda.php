<?php
	require("../conexion.php");
?>
<form method="post" action="index.php">
    Descripción:
    <input type="text" name="condicionsalud_descripcion" value="<?php if (isset($_REQUEST["condicionsalud_descripcion"])){ echo $_REQUEST["condicionsalud_descripcion"]; } ?>">
	Estado:
	<select name="condicionsalud_estado">
		<option value="">Seleccione el estado de la condicion de salud...</option>
		<option value="1"
		<?php
			if (isset($_REQUEST["condicionsalud_estado"])){
				if ($_REQUEST["condicionsalud_estado"] == 1){
					echo "selected";
				}
			}
		?>
		>Activo</option>
		<option value="0"<?php
			if (isset($_REQUEST["condicionsalud_estado"])){
				if ($_REQUEST["condicionsalud_estado"] == 0){
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