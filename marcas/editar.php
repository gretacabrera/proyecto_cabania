<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Editar marca</title>
		<link rel="stylesheet" href="../estilos.css">
	</head>
	<body class="home">
		<?php
			include("../menu.php");
		?>
		<div class="content">
			<?php
			require("../conexion.php");

			$registro = $mysql->query("select * from marca where id_marca=$_REQUEST[id_marca]") or
				die($mysql->error);

			if ($reg = $registro->fetch_array()) {
				?>
				<h1>Formulario de modificación de marca</h1>
				<form method="post" action="modificacion.php">
					<fieldset>
						<label>Descripcion:</label>
						<input type="text" name="marca_descripcion" size="45" value="<?php echo $reg['marca_descripcion']; ?>" required><br>
						<label>Estado:</label>
						<select name="marca_estado">
							<option value="">Seleccione el estado de la marca...</option>
							<option value="1"
							<?php
								if (isset($_REQUEST["marca_estado"])){
									if ($_REQUEST["marca_estado"] == 1){
										echo "selected";
									}
								}
							?>
							>Activo</option>
							<option value="0"<?php
								if (isset($_REQUEST["marca_estado"])){
									if ($_REQUEST["marca_estado"] == 0){
										echo "selected";
									}
								}
							?>
							>Baja</option>
						</select><br><br>
						<input type="hidden" name="id_marca" value="<?php echo $_REQUEST['id_marca']; ?>" required>
						<input type="submit" value="Confirmar">
					</fieldset>
				</form>
			<?php
			} else
				echo 'No existe una marca con ese id';

			$mysql->close();

			?>
		</div>
	</body>	
</html>
