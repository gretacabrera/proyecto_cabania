<form method="post" action="index.php">
    Descripción:
    <input type="text" name="perfil_descripcion" value="<?php if (isset($_REQUEST["perfil_descripcion"])){ echo $_REQUEST["perfil_descripcion"]; } ?>">
	Estado:
	<select name="perfil_estado">
		<option value="">Seleccione el estado del perfil...</option>
		<option value="1"
		<?php
			if (isset($_REQUEST["perfil_estado"])){
				if ($_REQUEST["perfil_estado"] == 1){
					echo "selected";
				}
			}
		?>
		>Activo</option>
		<option value="0"<?php
			if (isset($_REQUEST["perfil_estado"])){
				if ($_REQUEST["perfil_estado"] == 0){
					echo "selected";
				}
			}
		?>
		>Baja</option>
	</select>
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>