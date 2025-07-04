<?php
	require("../conexion.php");
?>
<form method="post" action="index.php">
    Nombre del servicio:
    <input type="text" name="servicio_nombre" value="<?php if (isset($_REQUEST["servicio_nombre"])){ echo $_REQUEST["servicio_nombre"]; } ?>">
	Descripción:
    <input type="text" name="servicio_descripcion" value="<?php if (isset($_REQUEST["servicio_descripcion"])){ echo $_REQUEST["servicio_descripcion"]; } ?>">
	Tipo de Servicio:
	<select name="rela_tiposervicio">
		<option value="">Seleccione una categoría...</option>
		<?php
			$registros = $mysql->query("select * from tiposervicio where tiposervicio_estado = 1") or
			die($mysql->error);
			while ($row = $registros->fetch_assoc()) {
				echo "<option value='".$row["id_tiposervicio"]."'";
				if (isset($_REQUEST["rela_tiposervicio"])){
					if ($_REQUEST["rela_tiposervicio"] == $row["id_tiposervicio"]){
						echo "selected";
					}
				}
				echo ">".$row["tiposervicio_descripcion"]."</option>";
			}
		?>
	</select>
	Estado:
	<select name="servicio_estado">
		<option value="">Seleccione el estado del tipo de servicio...</option>
		<option value="1"
		<?php
			if (isset($_REQUEST["servicio_estado"])){
				if ($_REQUEST["servicio_estado"] == 1){
					echo "selected";
				}
			}
		?>
		>Activo</option>
		<option value="0"<?php
			if (isset($_REQUEST["servicio_estado"])){
				if ($_REQUEST["servicio_estado"] == 0){
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