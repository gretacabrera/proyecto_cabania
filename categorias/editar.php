<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Editar categoria</title>
		<link rel="stylesheet" href="../estilos.css">
	</head>
	<body class="home">
		<?php
			include("../menu.php");
		?>
		<div class="content">
			<?php
			require("../conexion.php");

			$registro = $mysql->query("select * from categoria where id_categoria=$_REQUEST[id_categoria]") or
				die($mysql->error);

			if ($reg = $registro->fetch_array()) {
				?>
				<h1>Formulario de modificación de categoria</h1>
				<form method="post" action="modificacion.php">
					<fieldset>
						<label>Descripcion:</label>
						<input type="text" name="categoria_descripcion" size="45" value="<?php echo $reg['categoria_descripcion']; ?>" required><br>
						<input type="hidden" name="id_categoria" value="<?php echo $_REQUEST['id_categoria']; ?>" required>
						<input type="submit" value="Confirmar">
					</fieldset>
				</form>
			<?php
			} else
				echo 'No existe una categoria con ese id';

			$mysql->close();

			?>
		</div>
	</body>	
</html>
