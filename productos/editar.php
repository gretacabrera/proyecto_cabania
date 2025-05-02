<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Editar producto</title>
		<link rel="stylesheet" href="../estilos.css">
	</head>
	<body class="home">
		<?php
			include("../menu.php");
		?>
		<div class="content">
			<?php
			require("../conexion.php");

			$registro = $mysql->query("select * from producto where id_producto=$_REQUEST[id_producto]") or
				die($mysql->error);

			if ($reg = $registro->fetch_array()) {
				?>
				<h1>Formulario de modificación de Producto</h1>
				<form method="post" action="modificacion.php">
					<fieldset>
						<label>Nombre:</label>
						<input type="text" name="producto_nombre" size="50" value="<?php echo $reg['producto_nombre']; ?>" required><br>
						<label>Descripcion:</label>
						<input type="text" name="producto_descripcion" size="50" value="<?php echo $reg['producto_descripcion']; ?>" required><br>
						<label>Precio Unitario:</label>
						<input type="number" name="producto_precio" size="10" value="<?php echo $reg['producto_precio']; ?>" required><br>
						<label>Stock:</label>
						<input type="number" name="producto_stock" size="10" value="<?php echo $reg['producto_stock']; ?>" required><br>
						<label>Ruta de la foto:</label>
						<input type="text" name="producto_foto" size="20" value="<?php echo $reg['producto_foto']; ?>" required><br>
						<label>Marca:</label>
						<select name="rela_marca" required>
							<option value="">Seleccione la marca del producto...</option>
							<?php
								$registros = $mysql->query("select * from marca where marca_estado = 1") or
								die($mysql->error);
								while ($row = $registros->fetch_assoc()) {
									echo "<option value=".$row["id_marca"];
									if ($row["id_marca"] == $reg['rela_marca']){
										echo " selected";
									}
									echo ">".$row["marca_descripcion"]."</option>";
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
									echo "<option value=".$row["id_categoria"];
									if ($row["id_categoria"] == $reg['rela_categoria']){
										echo " selected";
									}
									echo ">".$row["categoria_descripcion"]."</option>";
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
									echo "<option value=".$row["id_estadoproducto"];
									if ($row["id_estadoproducto"] == $reg['rela_estadoproducto']){
										echo " selected";
									}
									echo ">".$row["estadoproducto_descripcion"]."</option>";
								}
							?>
						</select><br><br>
						<input type="hidden" name="id_producto" value="<?php echo $_REQUEST['id_producto']; ?>" required>
						<input type="submit" value="Confirmar">
					</fieldset>
				</form>
			<?php
			} else
				echo 'No existe un producto con ese id';

			$mysql->close();

			?>
		</div>
	</body>	
</html>
