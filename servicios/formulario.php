<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>Alta de nuevo producto</title>
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
			<h1>Formulario de alta de Servicio</h1>
			<form method="post" action="alta.php" onsubmit="return procesarFormularioAsincrono(this, 'Servicio creado correctamente', 'index.php')">
				<fieldset>
					<label>Nombre:</label>
					<input type="text" name="producto_nombre" size="50" required><br>
					<label>Descripcion:</label>
					<input type="text" name="producto_descripcion" size="50" required><br>
					<label>Precio Unitario:</label>
					<input type="number" name="producto_precio" size="10" required><br>
					<label>Tipo de Servicio:</label>
					<select name="rela_tiposervicio" required>
						<option value="">Seleccione la tiposervicio del producto...</option>
						<?php
							$registros = $mysql->query("select * from tiposervicio where tiposervicio_estado = 1") or
							die($mysql->error);
							while ($row = $registros->fetch_assoc()) {
								echo "<option value=".$row["id_tiposervicio"].">".$row["tiposervicio_descripcion"]."</option>";
							}
						?>
					</select><br><br>
					<input type="submit" value="Confirmar">
				</fieldset>
			</form>
		</div>
	</body>
</html>
