<?php
	require("../conexion.php");
?>
<form method="post" action="index.php">
    Descripción:
    <input type="text" name="marca_descripcion" value="<?php if (isset($_REQUEST["marca_descripcion"])){ echo $_REQUEST["marca_descripcion"]; } ?>">
	Estado:
	<select name="marca_estado">
		<option value="">Seleccione el estado de la marca...</option>
		<option value="1"
		<?php
			if (isset($_REQUEST["marca_estado"])){
				if ($_REQUEST["marca_estado"] == 1){
					echo "selected";
				}
			}
		?>
		>Activo</option>
		<option value="0"<?php
			if (isset($_REQUEST["marca_estado"])){
				if ($_REQUEST["marca_estado"] == 0){
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