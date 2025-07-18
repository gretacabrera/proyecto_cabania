<?php

$registro = $mysql->query("select * from perfil_modulo where id_perfilmodulo=$_REQUEST[id_perfilmodulo]") or
	die($mysql->error);

if ($reg = $registro->fetch_array()) {
?>
	<h1>Formulario de modificación de Perfiles</h1>
	<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Asignar Módulo a Perfil&ruta=perfiles_modulos&archivo=modificacion.php">
		<fieldset>
			Perfil:
			<select name="rela_perfil">
				<option value="">Seleccione el perfil...</option>
				<?php
				$registros = $mysql->query("select * from perfil where perfil_estado = 1") or
					die($mysql->error);
				while ($row = $registros->fetch_assoc()) {
					echo "<option value=" . $row["id_perfil"];
					if ($row["id_perfil"] == $reg['rela_perfil']) {
						echo " selected";
					}
					echo ">" . $row["perfil_descripcion"] . "</option>";
				}
				?>
			</select><br>
			Perfil:
			<select name="rela_modulo">
				<option value="">Seleccione el modulo...</option>
				<?php
				$registros = $mysql->query("select * from modulo where modulo_estado = 1") or
					die($mysql->error);
				while ($row = $registros->fetch_assoc()) {
					echo "<option value=" . $row["id_modulo"];
					if ($row["id_modulo"] == $reg['rela_modulo']) {
						echo " selected";
					}
					echo ">" . $row["modulo_descripcion"] . "</option>";
				}
				?>
			</select><br><br>
			<input type="hidden" name="id_perfilmodulo" value="<?php echo $_REQUEST['id_perfilmodulo']; ?>" required>
			<input type="submit" value="Confirmar">
		</fieldset>
	</form>
<?php
} else
	echo 'No existe un perfil con ese id';

$mysql->close();

?>