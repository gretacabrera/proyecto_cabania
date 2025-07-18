<?php
$registro = $mysql->query("select * from vw_usuario 
							where id_usuario=$_REQUEST[id_usuario]") or
	die($mysql->error);

if ($reg = $registro->fetch_array()) {
	?>
	<h1>Formulario de modificación de Usuario</h1>
	<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Usuarios&ruta=usuarios&archivo=modificacion.php">
		<fieldset>
			<legend>Datos de usuario</legend>
			<label>Perfil:</label>
			<select name="rela_perfil" required>
				<option value="">Seleccione el perfil del usuario...</option>
				<?php
					$registros = $mysql->query("select * from perfil where perfil_estado = 1") or
					die($mysql->error);
					while ($row = $registros->fetch_assoc()) {
						echo "<option value=".$row["id_perfil"];
						if ($row["perfil_descripcion"] == $reg['perfil_descripcion']){
							echo " selected";
						}
						echo ">".$row["perfil_descripcion"]."</option>";
					}
				?>
			</select><br>
			<label>Estado:</label>
			<select name="usuario_estado" required>
				<option value="1" <?php if ($reg['usuario_estado'] == 'activo') { echo "selected"; } ?> >Activo</option>
				<option value="2" <?php if ($reg['usuario_estado'] == 'bloqueado') { echo "selected"; } ?>>Bloqueado</option>
				<option value="3" <?php if ($reg['usuario_estado'] == 'baja') { echo "selected"; } ?>>Baja</option>
			</select><br>
		</fieldset>
		<fieldset>
			<legend>Datos personales</legend>
			<label>Nombre:</label>
			<input type="text" name="persona_nombre" value="<?php echo $reg['persona_nombre']; ?>" required><br>
			<label>Apellido:</label>
			<input type="text" name="persona_apellido" value="<?php echo $reg['persona_apellido']; ?>" required><br>
			<label>Fecha de nacimiento:</label>
			<input type="date" name="persona_fechanac" max="2024-11-25" value="<?php echo $reg['persona_fechanac']; ?>" required><br>
			<label>Dirección:</label>
			<input type="text" name="persona_direccion" value="<?php echo $reg['persona_direccion']; ?>" required><br>
		</fieldset>
		<fieldset>
			<legend>Contactos</legend>
			<label>Email:</label>
			<input type="text" name="contacto_email" value="<?php echo $reg['contacto_email']; ?>" required><br>
			<label>Teléfono:</label>
			<input type="number" name="contacto_telefono" value="<?php echo $reg['contacto_telefono']; ?>"><br>
			<label>Instragram:</label>
			<input type="text" name="contacto_instagram" value="<?php echo $reg['contacto_instagram']; ?>"><br>
			<label>Facebook:</label>
			<input type="text" name="contacto_facebook" value="<?php echo $reg['contacto_facebook']; ?>"><br>
		</fieldset>
		<br>
		<input type="hidden" name="id_usuario" value="<?php echo $_REQUEST['id_usuario']; ?>" required>
		<input type="submit" value="Confirmar">
	</form>
<?php
} else
	echo 'No existe un usuario con ese id';
$mysql->close();
?>
