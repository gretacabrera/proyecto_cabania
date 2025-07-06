<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Editar tipo de contacto</title>
		<link rel="stylesheet" href="../estilos.css">
	</head>
	<body class="home">
		<?php
			include("../menu.php");
		?>
		<div class="content">
			<?php
			require("../conexion.php");

			$registro = $mysql->query("select * from tipocontacto where id_tipocontacto=$_REQUEST[id_tipocontacto]") or
				die($mysql->error);

			if ($reg = $registro->fetch_array()) {
				?>
				<h1>Formulario de modificación de tipo de contacto</h1>
				<form method="post" action="modificacion.php">
					<fieldset>
						<label>Descripcion:</label>
						<input type="text" name="tipocontacto_descripcion" size="45" value="<?php echo $reg['tipocontacto_descripcion']; ?>" required><br>
						<input type="hidden" name="id_tipocontacto" value="<?php echo $_REQUEST['id_tipocontacto']; ?>" required>
						<input type="submit" value="Confirmar">
					</fieldset>
				</form>
			<?php
			} else
				echo 'No existe un tipo de contacto con ese id';

			$mysql->close();

			?>
		</div>
	</body>	
</html>
