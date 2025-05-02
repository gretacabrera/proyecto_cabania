<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>Alta de nuevo Usuario</title>
		<link rel="stylesheet" href="../estilos.css">
	</head>
	<body class="home">
		<?php
			include("../menu.php");
		?>
		<div class="content">
			<?php
				require("../conexion.php");
			?>
			<h1>Formulario de alta de Usuario</h1>
			<form method="post" action="alta.php">
				<fieldset>
					<legend>Datos de usuario</legend>
					<label>Usuario:</label>
					<input type="text" name="usuario_nombre" required><br>
					<label>Contraseña:</label>
					<input type="password" name="usuario_contrasenia" required><br>
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
					<label>DNI:</label>
					<input type="number" name="persona_dni" size="10" required><br>
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
		</div>
	</body>
</html>
