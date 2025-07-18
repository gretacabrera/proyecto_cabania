
<h1>Filtros de busqueda:</h1>
<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Productos Cantidad por Categoría&ruta=reportes/productos_cantidadxcategoria">
	Nombre:
	<input type="text" name="producto_nombre" size="50" value="<?php if (isset($_REQUEST["producto_nombre"])){ echo $_REQUEST["producto_nombre"]; } ?>">
	<br>
	Marca:
	<select name="rela_marca">
		<option value="">Seleccione la marca de los productos...</option>
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
	<br>
	Estado:
	<select name="rela_estadoproducto">
		<option value="">Seleccione el estado de los productos...</option>
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
