<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Editar tipo de servicio</title>
		<link rel="stylesheet" href="../estilos.css">
	</head>
	<body class="home">
		<?php
			include("../menu.php");
		?>
		<div class="content">
			<?php
			require("../conexion.php");

			$registro = $mysql->query("select * from tiposervicio where id_tiposervicio=$_REQUEST[id_tiposervicio]") or
				die($mysql->error);

			if ($reg = $registro->fetch_array()) {
				?>
				<h1>Formulario de modificación de tipo de servicio</h1>
				<form method="post" action="modificacion.php">
					<fieldset>
						<label>Descripcion:</label>
						<input type="text" name="tiposervicio_descripcion" size="45" value="<?php echo $reg['tiposervicio_descripcion']; ?>" required><br>
						<label>Estado:</label>
						<select name="tiposervicio_estado">
							<option value="">Seleccione el estado del tipo de servicio...</option>
							<option value="1"
							<?php
								if (isset($_REQUEST["tiposervicio_estado"])){
									if ($_REQUEST["tiposervicio_estado"] == 1){
										echo "selected";
									}
								}
							?>
							>Activo</option>
							<option value="0"<?php
								if (isset($_REQUEST["tiposervicio_estado"])){
									if ($_REQUEST["tiposervicio_estado"] == 0){
										echo "selected";
									}
								}
							?>
							>Baja</option>
						</select><br><br>
						<input type="hidden" name="id_tiposervicio" value="<?php echo $_REQUEST['id_tiposervicio']; ?>" required>
						<input type="submit" value="Confirmar">
					</fieldset>
				</form>
			<?php
			} else
				echo 'No existe un tipo de servicio con ese id';

			$mysql->close();

			?>
		</div>
	</body>	
</html>
