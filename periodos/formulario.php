<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>Alta de nuevo periodo</title>
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
			<h1>Formulario de alta de periodo</h1>
			<form method="post" action="alta.php" onsubmit="return procesarFormularioAsincrono(this, 'Módulo creado correctamente', 'index.php')">
				<fieldset>
					<label>Descripcion:</label>
					<input type="text" name="periodo_descripcion" size="45" required><br>
					<label>Año:</label>
					<input type="number" name="periodo_anio" size="4"><br>
					<label>Fecha y hora de inicio:</label>
					<input type="datetime-local" name="periodo_fechainicio" required><br><br>
					<label>Fecha y hora de fin:</label>
					<input type="datetime-local" name="periodo_fechafin" required><br><br>
					<label>Orden:</label>
					<input type="number" name="periodo_orden" size="1"><br><br>
					<input type="submit" value="Confirmar">
				</fieldset>
			</form>
		</div>
	</body>
</html>
