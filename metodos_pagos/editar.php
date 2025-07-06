<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Editar metodo de pago</title>
		<link rel="stylesheet" href="../estilos.css">
	</head>
	<body class="home">
		<?php
			include("../menu.php");
		?>
		<div class="content">
			<?php
			require("../conexion.php");

			$registro = $mysql->query("select * from metodopago where id_metodopago=$_REQUEST[id_metodopago]") or
				die($mysql->error);

			if ($reg = $registro->fetch_array()) {
				?>
				<h1>Formulario de modificación de metodo de pago</h1>
				<form method="post" action="modificacion.php">
					<fieldset>
						<label>Descripcion:</label>
						<input type="text" name="metodopago_descripcion" size="45" value="<?php echo $reg['metodopago_descripcion']; ?>" required><br>
						<input type="hidden" name="id_metodopago" value="<?php echo $_REQUEST['id_metodopago']; ?>" required>
						<input type="submit" value="Confirmar">
					</fieldset>
				</form>
			<?php
			} else
				echo 'No existe una  metodo de pago con ese id';

			$mysql->close();

			?>
		</div>
	</body>	
</html>
