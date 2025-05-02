<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>Alta de nueva reserva</title>
		<link rel="stylesheet" href="../estilos.css">
	</head>
	<body class="home">
		<?php
			include("../menu.php");
		?>
		<div class="content">
			<h1>Formulario de alta de Reserva</h1>
			<form method="post" action="formulario_cabania.php">
				<label>DNI del huesped:</label>
				<input type="number" name="persona_dni" size="10" required><br><br>
				<label>Fecha y hora de inicio:</label>
				<input type="datetime-local" name="reserva_fhinicio" min="2024-11-26T00:00" value="<?php echo $reg['reserva_fhinicio']; ?>" required><br><br>
				<label>Fecha y hora de fin:</label>
				<input type="datetime-local" name="reserva_fhfin" min="2024-11-26T00:00" value="<?php echo $reg['reserva_fhfin']; ?>" required><br><br>
				<br>
				<input type="submit" value="Continuar">
			</form>
		</div>
	</body>
</html>
