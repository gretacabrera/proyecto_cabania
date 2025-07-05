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
			<h1>Formulario de alta de Producto</h1>
			<form method="post" action="alta.php" onsubmit="return procesarFormularioAsincrono(this, 'Producto creado correctamente', 'index.php')">
				<fieldset>
					<label>Nombre:</label>
					<input type="text" name="producto_nombre" size="50" required><br>
					<label>Descripcion:</label>
					<input type="text" name="producto_descripcion" size="50" required><br>
					<label>Precio Unitario:</label>
					<input type="number" name="producto_precio" size="10" required><br>
					<label>Stock:</label>
					<input type="number" name="producto_stock" size="10" required><br>
					<label>Ruta de la foto:</label>
					<input type="text" name="producto_foto" size="10" required><br>
					<label>Marca:</label>
					<select name="rela_marca" required>
						<option value="">Seleccione la marca del producto...</option>
						<?php
							$registros = $mysql->query("select * from marca where marca_estado = 1") or
							die($mysql->error);
							while ($row = $registros->fetch_assoc()) {
								echo "<option value=".$row["id_marca"].">".$row["marca_descripcion"]."</option>";
							}
						?>
					</select><br>
					<label>Categoria:</label>
					<select name="rela_categoria" required>
						<option value="">Seleccione la categoria del producto...</option>
						<?php
							$registros = $mysql->query("select * from categoria where categoria_estado = 1") or
							die($mysql->error);
							while ($row = $registros->fetch_assoc()) {
								echo "<option value=".$row["id_categoria"].">".$row["categoria_descripcion"]."</option>";
							}
						?>
					</select><br>
					<label>Estado:</label>
					<select name="rela_estadoproducto" required>
						<option value="">Seleccione el estado del producto...</option>
						<?php
							$registros = $mysql->query("select * from estadoproducto where estadoproducto_estado = 1") or
							die($mysql->error);
							while ($row = $registros->fetch_assoc()) {
								echo "<option value=".$row["id_estadoproducto"].">".$row["estadoproducto_descripcion"]."</option>";
							}
						?>
					</select><br><br>
					<input type="submit" value="Confirmar">
				</fieldset>
			</form>
		</div>
	</body>
</html>
