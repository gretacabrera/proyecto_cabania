<?php
$registro = $mysql->query("select * from producto where id_producto=$_REQUEST[id_producto]") or
	die($mysql->error);

if ($reg = $registro->fetch_array()) {
	?>
	<h1>Formulario de modificación de Producto</h1>
	<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Productos&ruta=productos&archivo=modificacion.php" enctype="multipart/form-data">
		<fieldset>
			<label>Nombre:</label>
			<input type="text" name="producto_nombre" size="50" value="<?php echo $reg['producto_nombre']; ?>" required><br>
			<label>Descripcion:</label>
			<input type="text" name="producto_descripcion" size="50" value="<?php echo $reg['producto_descripcion']; ?>" required><br>
			<label>Precio Unitario:</label>
			<input type="number" name="producto_precio" size="10" value="<?php echo $reg['producto_precio']; ?>" required><br>
			<label>Stock:</label>
			<input type="number" name="producto_stock" size="10" value="<?php echo $reg['producto_stock']; ?>" required><br>
			
			<label>Foto actual:</label>
			<?php if ($reg['producto_foto']): ?>
				<img src="imagenes/productos/<?php echo $reg['producto_foto']; ?>" width="100" height="100"><br>
			<?php else: ?>
				<span>Sin foto</span><br>
			<?php endif; ?>
			
			<label>Nueva foto (opcional):</label>
			<input type="file" name="producto_foto" accept="image/*"><br>
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
			<input type="hidden" name="producto_foto_actual" value="<?php echo $reg['producto_foto']; ?>">
			<input type="submit" value="Confirmar">
		</fieldset>
	</form>
<?php
} else
	echo 'No existe un producto con ese id';

$mysql->close();
?>
