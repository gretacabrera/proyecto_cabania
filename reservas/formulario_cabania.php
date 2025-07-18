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
			<form method="post" action="alta.php">
				<label>DNI del huesped:</label>
				<input type="number" value="<?php echo $_REQUEST['persona_dni']; ?>" disabled><br><br>
				<label>Fecha y hora de inicio:</label>
				<input type="datetime-local" value="<?php echo $_REQUEST['reserva_fhinicio']; ?>" disabled><br><br>
				<label>Fecha y hora de fin:</label>
				<input type="datetime-local" value="<?php echo $_REQUEST['reserva_fhfin']; ?>" disabled><br><br>
				<label>Cabaña:</label>
				<select name="rela_cabania" required>
					<option value="">Seleccione ...</option>
					<?php
						require("conexion.php");
						$registros = $mysql->query("select * from cabania
													where cabania_estado = 1
													and id_cabania not in
													(select id_cabania from cabania
													left join reserva on rela_cabania = id_cabania
													left join estadoreserva on rela_estadoreserva = id_estadoreserva
													where rela_estadoreserva <> 6
													and reserva_fhinicio between '$_REQUEST[reserva_fhinicio]' 
																			and '$_REQUEST[reserva_fhfin]')") or
							die($mysql->error);
						while ($row = $registros->fetch_assoc()) {
							echo "<option value='$row[id_cabania]'>$row[cabania_nombre]</option>";
						}
					?>
				</select>
				<br><br>
				<input type="hidden" name="persona_dni" value="<?php echo $_REQUEST["persona_dni"]; ?>">
				<input type="hidden" name="reserva_fhinicio" value="<?php echo $_REQUEST["reserva_fhinicio"]; ?>">
				<input type="hidden" name="reserva_fhfin" value="<?php echo $_REQUEST["reserva_fhfin"]; ?>">
				<input type="submit" value="Confirmar">
			</form>
		</div>
	</body>
</html>
