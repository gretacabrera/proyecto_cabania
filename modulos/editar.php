<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Editar modulo</title>
		<link rel="stylesheet" href="../estilos.css">
	</head>
	<body class="home">
		<?php
			include("../menu.php");
		?>
		<div class="content">
			<?php
			require("../conexion.php");

			$registro = $mysql->query("select * from modulo where id_modulo=$_REQUEST[id_modulo]") or
				die($mysql->error);

			if ($reg = $registro->fetch_array()) {
				?>
				<h1>Formulario de modificación de moduloes</h1>
				<form method="post" action="modificacion.php">
					<fieldset>
						<label>Descripcion:</label>
						<input type="text" name="modulo_descripcion" size="45" value="<?php echo $reg['modulo_descripcion']; ?>" required><br>
						<label>Ruta:</label>
						<input type="text" name="modulo_ruta" size="45" value="<?php echo $reg['modulo_ruta']; ?>" required><br>
						<input type="hidden" name="id_modulo" value="<?php echo $_REQUEST['id_modulo']; ?>" required>
						<input type="submit" value="Confirmar">
					</fieldset>
				</form>
			<?php
			} else
				echo 'No existe un modulo con ese id';

			$mysql->close();

			?>
		</div>
	</body>	
</html>
