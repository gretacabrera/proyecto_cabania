<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Categorías&ruta=categorias">
    Descripción:
    <input type="text" name="categoria_descripcion" value="<?php if (isset($_REQUEST["categoria_descripcion"])){ echo $_REQUEST["categoria_descripcion"]; } ?>">
	Estado:
	<select name="categoria_estado">
		<option value="">Seleccione el estado de la categoria...</option>
		<option value="1"
		<?php
			if (isset($_REQUEST["categoria_estado"])){
				if ($_REQUEST["categoria_estado"] == 1){
					echo "selected";
				}
			}
		?>
		>Activo</option>
		<option value="0"<?php
			if (isset($_REQUEST["categoria_estado"])){
				if ($_REQUEST["categoria_estado"] == 0){
					echo "selected";
				}
			}
		?>
		>Baja</option>
	</select>
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>