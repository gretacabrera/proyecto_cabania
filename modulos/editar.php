<?php
$registro = $mysql->query("select * from modulo where id_modulo=$_REQUEST[id_modulo]") or
	die($mysql->error);

if ($reg = $registro->fetch_array()) {
	?>
	<h1>Formulario de modificación de modulo</h1>
	<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Módulos&ruta=modulos&archivo=modificacion.php">
		<fieldset>
			<label>Descripcion:</label>
			<input type="text" name="modulo_descripcion" size="45" value="<?php echo $reg['modulo_descripcion']; ?>" required><br>
			<label>Ruta:</label>
			<input type="text" name="modulo_ruta" size="45" value="<?php echo $reg['modulo_ruta']; ?>" required><br>
			<label>Menu:</label>
			<select name="rela_menu">
				<option value="">Seleccione un menu (opcional)...</option>
				<?php
				$menus = $mysql->query("select * from menu where menu_estado = 1 order by menu_nombre") or die($mysql->error);
				while ($menu = $menus->fetch_assoc()) {
					$selected = ($menu["id_menu"] == $reg['rela_menu']) ? "selected" : "";
					echo "<option value='".$menu["id_menu"]."' $selected>".$menu["menu_nombre"]."</option>";
				}
				?>
			</select><br>
			<input type="hidden" name="id_modulo" value="<?php echo $_REQUEST['id_modulo']; ?>" required>
			<input type="submit" value="Confirmar">
		</fieldset>
	</form>
<?php
} else
	echo 'No existe un modulo con ese id';

$mysql->close();
?>
