<?php
	require("../conexion.php");
?>
<form method="post" action="index.php">
    Descripción:
    <input type="text" name="modulo_descripcion" value="<?php if (isset($_REQUEST["modulo_descripcion"])){ echo $_REQUEST["modulo_descripcion"]; } ?>">
	Estado:
	<select name="modulo_estado">
		<option value="">Seleccione el estado del modulo...</option>
		<option value="1"
		<?php
			if (isset($_REQUEST["modulo_estado"])){
				if ($_REQUEST["modulo_estado"] == 1){
					echo "selected";
				}
			}
		?>
		>Activo</option>
		<option value="0"<?php
			if (isset($_REQUEST["modulo_estado"])){
				if ($_REQUEST["modulo_estado"] == 0){
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