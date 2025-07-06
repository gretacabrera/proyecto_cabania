<form method="post" action="index.php">
    Perfil:
	<select name="rela_perfil">
		<option value="">Seleccione un perfil...</option>
		<?php
			$registros = $mysql->query("select * from perfil where perfil_estado = 1") or
			die($mysql->error);
			while ($row = $registros->fetch_assoc()) {
				echo "<option value='".$row["id_perfil"]."'";
				if (isset($_REQUEST["rela_perfil"])){
					if ($_REQUEST["rela_perfil"] == $row["id_perfil"]){
						echo "selected";
					}
				}
				echo ">".$row["perfil_descripcion"]."</option>";
			}
		?>
	</select>
	Módulo:
	<select name="rela_modulo">
		<option value="">Seleccione un modulo...</option>
		<?php
			$registros = $mysql->query("select * from modulo where modulo_estado = 1") or
			die($mysql->error);
			while ($row = $registros->fetch_assoc()) {
				echo "<option value='".$row["id_modulo"]."'";
				if (isset($_REQUEST["rela_modulo"])){
					if ($_REQUEST["rela_modulo"] == $row["id_modulo"]){
						echo "selected";
					}
				}
				echo ">".$row["modulo_descripcion"]."</option>";
			}
		?>
	</select>
	Estado:
	<select name="perfilmodulo_estado">
		<option value="">Seleccione el estado...</option>
		<option value="1"
		<?php
			if (isset($_REQUEST["perfilmodulo_estado"])){
				if ($_REQUEST["perfilmodulo_estado"] == 1){
					echo "selected";
				}
			}
		?>
		>Activo</option>
		<option value="0"<?php
			if (isset($_REQUEST["perfilmodulo_estado"])){
				if ($_REQUEST["perfilmodulo_estado"] == 0){
					echo "selected";
				}
			}
		?>
		>Baja</option>
	</select>
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>