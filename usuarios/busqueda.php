<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Usuarios&ruta=usuarios">
    Nombre de usuario:
    <input type="text" name="usuario_nombre" value="<?php if (isset($_REQUEST["usuario_nombre"])){ echo $_REQUEST["usuario_nombre"]; } ?>">
	Perfil:
	<select name="rela_perfil">
		<option value="">Seleccione un perfil...</option>
		<?php
			$registros = $mysql->query("select * from perfil where perfil_estado = 1") or
			die($mysql->error);
			
			while ($row = $registros->fetch_assoc()) {
				echo "<option value='".$row["id_perfil"]."'";
				if (isset($_REQUEST["id_perfil"])){
					if ($_REQUEST["id_perfil"] == $row["id_perfil"]){
						echo "selected";
					}
				}
				echo ">".$row["perfil_descripcion"]."</option>";
			}
		?>
	</select>
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>