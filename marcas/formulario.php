<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>Alta de nueva marca</title>
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
			<h1>Formulario de alta de marca</h1>
			<form method="post" action="alta.php">
				<fieldset>
					<label>Descripcion:</label>
					<input type="text" name="marca_descripcion" size="45" required><br>
					<label>Estado:</label>
					<select name="marca_estado">
						<option value="">Seleccione el estado de la marca...</option>
						<option value="1">Activo</option>
						<option value="0">Baja</option>
					</select><br><br>
					<input type="submit" value="Confirmar">
				</fieldset>
			</form>
		</div>
	</body>
</html>
