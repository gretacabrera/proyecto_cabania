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
    <div class="centered-body reservas">
		<?php
			require("conexion.php");
		?>
		<form id="form_reserva" method="post" action="seleccion_cabania.php">
			<fieldset>
				<legend>Ingresar fechas de búsqueda</legend><br>
				<label>Fecha de llegada:</label>
				<input type="datetime-local" min="2024-11-26T00:00" name="reserva_fhinicio" required><br><br>
				<label>Fecha de salida:</label>
				<input type="datetime-local" min="2024-11-26T00:00" name="reserva_fhfin" required><br><br>
				<input type="submit" value="Buscar cabañas >">
			</fieldset>
		</form>
	<div>
</body>
</html>
