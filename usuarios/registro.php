<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Registro de usuario</title>
	<link rel="stylesheet" href="../estilos.css">
</head>
<body class="centered-body login">
	<?php
		require("../conexion.php");
	?>
	<div class="loginform">
		<h2>CASA DE PALOS</h2>
        <h3>CABAÑAS</h3>
        <br>
		<form method="post" action="alta.php">
			<fieldset>
				<legend>Credenciales de usuario</legend>
				<label>Usuario:</label>
				<input type="text" name="usuario_nombre" required><br>
				<label>Contraseña:</label>
				<input type="password" name="usuario_contrasenia" required><br>
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
				<input type="date" name="persona_fechanac" max="2024-11-19" required><br>
				<label>Dirección:</label>
				<input type="text" name="persona_direccion" required><br>
			</fieldset>
			<fieldset>
				<legend>Contactos</legend>
				<label>Email:</label>
				<input type="text" name="contacto_email" required><br>
				<label>Teléfono:</label>
				<input type="number" name="contacto_telefono" size="10"><br>
				<label>Instragram:</label>
				<input type="text" name="contacto_instagram"><br>
				<label>Facebook:</label>
				<input type="text" name="contacto_facebook"><br>
			</fieldset>
			<br>
			<input type="hidden" name="registro_online" value="1" required>
			<input type="submit" value="REGISTRARSE">
		</form>
	</div>
</body>
</html>
