<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Productos&ruta=productos">
    Nombre del producto:
    <input type="text" name="producto_nombre" value="<?php if (isset($_REQUEST["producto_nombre"])){ echo $_REQUEST["producto_nombre"]; } ?>">
	Categoria:
	<select name="rela_categoria">
		<option value="">Seleccione una categoría...</option>
		<?php
			$registros = $mysql->query("select * from categoria where categoria_estado = 1") or
			die($mysql->error);
			while ($row = $registros->fetch_assoc()) {
				echo "<option value='".$row["id_categoria"]."'";
				if (isset($_REQUEST["rela_categoria"])){
					if ($_REQUEST["rela_categoria"] == $row["id_categoria"]){
						echo "selected";
					}
				}
				echo ">".$row["categoria_descripcion"]."</option>";
			}
		?>
	</select>
	Marca:
	<select name="rela_marca">
		<option value="">Seleccione una marca...</option>
		<?php
			$registros = $mysql->query("select * from marca where marca_estado = 1") or
			die($mysql->error);
			while ($row = $registros->fetch_assoc()) {
				echo "<option value='".$row["id_marca"]."'";
				if (isset($_REQUEST["rela_marca"])){
					if ($_REQUEST["rela_marca"] == $row["id_marca"]){
						echo "selected";
					}
				}
				echo ">".$row["marca_descripcion"]."</option>";
			}
		?>
	</select>
	Estado:
	<select name="rela_estadoproducto">
		<option value="">Seleccione el estado del producto...</option>
		<?php
			$registros = $mysql->query("select * from estadoproducto where estadoproducto_estado = 1") or
			die($mysql->error);
			while ($row = $registros->fetch_assoc()) {
				echo "<option value='".$row["id_estadoproducto"]."'";
				if (isset($_REQUEST["rela_estadoproducto"])){
					if ($_REQUEST["rela_estadoproducto"] == $row["id_estadoproducto"]){
						echo "selected";
					}
				}
				echo ">".$row["estadoproducto_descripcion"]."</option>";
			}
		?>
	</select>
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>