<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Editar servicio</title>
		<link rel="stylesheet" href="../estilos.css">
	</head>
	<body class="home">
		<?php
			include("../menu.php");
		?>
		<div class="content">
			<?php
			require("../conexion.php");

			$registro = $mysql->query("select * from servicio where id_servicio=$_REQUEST[id_servicio]") or
				die($mysql->error);

			if ($reg = $registro->fetch_array()) {
				?>
				<h1>Formulario de modificación de servicio</h1>
				<form method="post" action="modificacion.php">
					<fieldset>
						<label>Nombre:</label>
						<input type="text" name="servicio_nombre" size="50" value="<?php echo $reg['servicio_nombre']; ?>" required><br>
						<label>Descripcion:</label>
						<input type="text" name="servicio_descripcion" size="50" value="<?php echo $reg['servicio_descripcion']; ?>" required><br>
						<label>Precio Unitario:</label>
						<input type="number" name="servicio_precio" size="10" value="<?php echo $reg['servicio_precio']; ?>" required><br>
						<label>Tipo de Servicio:</label>
						<select name="rela_tiposervicio" required>
							<option value="">Seleccione la tipo de servicio...</option>
							<?php
								$registros = $mysql->query("select * from tiposervicio where tiposervicio_estado = 1") or
								die($mysql->error);
								while ($row = $registros->fetch_assoc()) {
									echo "<option value=".$row["id_tiposervicio"];
									if ($row["id_tiposervicio"] == $reg['rela_tiposervicio']){
										echo " selected";
									}
									echo ">".$row["tiposervicio_descripcion"]."</option>";
								}
							?>
						</select><br>
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
						<input type="hidden" name="id_servicio" value="<?php echo $_REQUEST['id_servicio']; ?>" required>
						<input type="submit" value="Confirmar">
					</fieldset>
				</form>
			<?php
			} else
				echo 'No existe un servicio con ese id';

			$mysql->close();

			?>
		</div>
	</body>	
</html>
