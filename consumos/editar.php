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

			$registro = $mysql->query("select * from producto where id_producto=$_REQUEST[id_producto]") or
				die($mysql->error);

			if ($reg = $registro->fetch_array()) {
				?>
				<form method="post" action="modificacion.php">
					Nombre:
					<input type="text" name="producto_nombre" size="50" value="<?php echo $reg['producto_nombre']; ?>">
					<br>
					Descripcion:
					<input type="text" name="producto_descripcion" size="50" value="<?php echo $reg['producto_descripcion']; ?>">
					<br>
					Precio Unitario:
					<input type="number" name="producto_precio" size="10" value="<?php echo $reg['producto_precio']; ?>">
					<br>
					Stock:
					<input type="number" name="producto_stock" size="10" value="<?php echo $reg['producto_stock']; ?>">
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
								echo "<option value=".$row["id_marca"];
								if ($row["id_marca"] == $reg['rela_marca']){
									echo " selected";
								}
								echo ">".$row["marca_descripcion"]."</option>";
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
								echo "<option value=".$row["id_categoria"];
								if ($row["id_categoria"] == $reg['rela_categoria']){
									echo " selected";
								}
								echo ">".$row["categoria_descripcion"]."</option>";
							}
						?>
					</select>
					<br>
					Estado:
					<select name="rela_estadoproducto">
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
					</select>
					<input type="hidden" name="id_producto" value="<?php echo $_REQUEST['id_producto']; ?>">
					<br>
					<input type="submit" value="Confirmar">
				</form>
			<?php
			} else
				echo 'No existe un producto con ese id';

			$mysql->close();

			?>
		</div>
	</body>	
</html>
