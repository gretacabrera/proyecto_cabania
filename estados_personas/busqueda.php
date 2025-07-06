<?php
	require("../conexion.php");
?>
<form method="post" action="index.php">
    Descripción:
    <input type="text" name="estadopersona_descripcion" value="<?php if (isset($_REQUEST["estadopersona_descripcion"])){ echo $_REQUEST["estadopersona_descripcion"]; } ?>">
	Estado:
	<select name="estadopersona_estado">
		<option value="">Seleccione el estado de la estado de persona...</option>
		<option value="1"
		<?php
			if (isset($_REQUEST["estadopersona_estado"])){
				if ($_REQUEST["estadopersona_estado"] == 1){
					echo "selected";
				}
			}
		?>
		>Activo</option>
		<option value="0"<?php
			if (isset($_REQUEST["estadopersona_estado"])){
				if ($_REQUEST["estadopersona_estado"] == 0){
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