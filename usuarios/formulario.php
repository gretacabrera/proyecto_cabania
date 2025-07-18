<h1>Formulario de alta de Usuario</h1>
<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Usuarios&ruta=usuarios&archivo=alta.php">
	<fieldset>
		<legend>Datos de usuario</legend>
		<label>Usuario:</label>
		<input type="text" name="usuario_nombre" required><br>
		<label>Contraseña:</label>
		<input type="password" name="usuario_contrasenia" required><br>
		<label>Confirmar Contraseña:</label>
		<input type="password" name="confirmacion_contrasenia" required><br>
		<label>Perfil:</label>
		<select name="rela_perfil" required>
			<option value="">Seleccione el perfil del usuario...</option>
			<?php
				$registros = $mysql->query("select * from perfil where perfil_estado = 1") or
				die($mysql->error);
				while ($row = $registros->fetch_assoc()) {
					echo "<option value=".$row["id_perfil"].">".$row["perfil_descripcion"]."</option>";
				}
			?>
		</select><br>
		<label>Estado:</label>
		<select name="usuario_estado" required>
			<option value="1">Activo</option>
			<option value="2">Bloqueado</option>
			<option value="3">Baja</option>
		</select><br>
	</fieldset>
	<fieldset>
		<legend>Datos personales</legend>
		<label>Nombre:</label>
		<input type="text" name="persona_nombre" required><br>
		<label>Apellido:</label>
		<input type="text" name="persona_apellido" required><br>
		<label>Fecha de nacimiento:</label>
		<input type="date" name="persona_fechanac" max="2024-11-25" required><br>
		<label>Dirección:</label>
		<input type="text" name="persona_direccion" required><br>
	</fieldset>
	<fieldset>
		<legend>Contactos</legend>
		<label>Email:</label>
		<input type="text" name="contacto_email" required><br>
		<label>Teléfono:</label>
		<input type="number" name="contacto_telefono"><br>
		<label>Instragram:</label>
		<input type="text" name="contacto_instagram"><br>
		<label>Facebook:</label>
		<input type="text" name="contacto_facebook"><br>
	</fieldset>
	<br>
	<input type="submit" value="Confirmar">
</form>
