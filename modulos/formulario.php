<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>Alta de nuevo modulo</title>
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
			<h1>Formulario de alta de modulo</h1>
			<form method="post" action="alta.php" onsubmit="return procesarFormularioAsincrono(this, 'Módulo creado correctamente', 'index.php')">
				<fieldset>
					<label>Descripcion:</label>
					<input type="text" name="modulo_descripcion" size="45" required><br>
					<label>Ruta:</label>
					<input type="text" name="modulo_ruta" size="45" required><br>
					<input type="submit" value="Confirmar">
				</fieldset>
			</form>
		</div>
	</body>
</html>
