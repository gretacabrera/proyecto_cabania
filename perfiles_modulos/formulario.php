<h1>Formulario de alta de asignación de módulo a perfil</h1>
<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Asignar Módulo a Perfil&ruta=perfiles_modulos&archivo=alta.php">
	<fieldset>
		Perfil:
		<select name="rela_perfil">
			<option value="">Seleccione el perfil...</option>
			<?php
			$registros = $mysql->query("select * from perfil where perfil_estado = 1") or
				die($mysql->error);
			while ($row = $registros->fetch_assoc()) {
				echo "<option value=" . $row["id_perfil"] . ">" . $row["perfil_descripcion"] . "</option>";
			}
			?>
		</select><br>
		Módulo:
		<select name="rela_modulo">
			<option value="">Seleccione el modulo...</option>
			<?php
			$registros = $mysql->query("select * from modulo where modulo_estado = 1") or
				die($mysql->error);
			while ($row = $registros->fetch_assoc()) {
				echo "<option value=" . $row["id_modulo"] . ">" . $row["modulo_descripcion"] . "</option>";
			}
			?>
		</select><br><br>
		<input type="submit" value="Confirmar">
	</fieldset>
</form>