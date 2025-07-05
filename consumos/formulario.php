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
			<form method="post" action="alta.php">
				Nombre:
				<input type="text" name="producto_nombre" size="100">
				<br>
				Descripcion:
				<input type="text" name="producto_descripcion" size="100">
				<br>
				Precio Unitario:
				<input type="number" name="producto_precio" size="10">
				<br>
				Stock:
				<input type="number" name="producto_stock" size="10">
				<br>
				Ruta de la foto:
				<input type="text" name="producto_foto" size="10">
				<br>
				Marca:
				<select name="rela_marca">
					<option value="">Seleccione la marca del producto...</option>
					<?php
						$registros = $mysql->query("select * from marca where marca_estado = 1") or
						die($mysql->error);
						while ($row = $registros->fetch_assoc()) {
							echo "<option value=".$row["id_marca"].">".$row["marca_descripcion"]."</option>";
						}
					?>
				</select>
				<br>
				Categoria:
				<select name="rela_categoria">
					<option value="">Seleccione la categoria del producto...</option>
					<?php
						$registros = $mysql->query("select * from categoria where categoria_estado = 1") or
						die($mysql->error);
						while ($row = $registros->fetch_assoc()) {
							echo "<option value=".$row["id_categoria"].">".$row["categoria_descripcion"]."</option>";
						}
					?>
				</select><br>
				<input type="submit" value="Confirmar">
			</form>
		</div>
	</body>
</html>
