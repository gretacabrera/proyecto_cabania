<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Edición de producto.</title>
		<link rel="stylesheet" href="../estilos.css">
	</head>
	<body class="home">
		<?php
			include("../menu.php");
		?>
		<div class="content">
			<?php
				require("conexion.php");
				$registro = $mysql->query("select * from reserva where id_reserva=$_REQUEST[id_reserva]") or
					die($mysql->error);

				if ($reg = $registro->fetch_array()) {
					?>
					<form method="post" action="modificacion.php">
						<label>Fecha y hora de inicio:</label>
						<input type="datetime-local" name="reserva_fhinicio" value="<?php echo $reg['reserva_fhinicio']; ?>" required><br>
						<label>Fecha y hora de fin:</label>
						<input type="datetime-local" name="reserva_fhfin" value="<?php echo $reg['reserva_fhfin']; ?>" required><br>
						<label>Cabaña:</label>
						<select name="rela_cabania" required>
							<option value="">Seleccione ...</option>
							<?php
								$registros = $mysql->query("select * from cabania") or
								die($mysql->error);
								while ($row = $registros->fetch_assoc()) {
									echo "<option value=".$row["id_cabania"];
									if ($row["id_cabania"] == $reg['rela_cabania']){
										echo " selected";
									}
									echo ">".$row["cabania_nombre"]."</option>";
								}
							?>
						</select>
						<br>
						<label>Estado:</label>
						<select name="rela_estadoreserva" required>
							<option value="">Seleccione ...</option>
							<?php
								$registros = $mysql->query("select * from estadoreserva where estadoreserva_estado <> 2") or
								die($mysql->error);
								while ($row = $registros->fetch_assoc()) {
									echo "<option value=".$row["id_estadoreserva"];
									if ($row["id_estadoreserva"] == $reg['rela_estadoreserva']){
										echo " selected";
									}
									echo ">".$row["estadoreserva_descripcion"]."</option>";
								}
							?>
						</select>
						<br>
						<input type="hidden" name="id_reserva" value="<?php echo $_REQUEST['id_reserva']; ?>">
						<br>
						<input type="submit" value="Confirmar">
					</form>
				<?php
				} else
					echo 'No existe un producto con ese id';

				$mysql->close();
			?>
	</body>	
</html>
