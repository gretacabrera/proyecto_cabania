<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Editar perfil</title>
		<link rel="stylesheet" href="../estilos.css">
	</head>
	<body class="home">
		<?php
			include("../menu.php");
		?>
		<div class="content">
			<?php
			require("../conexion.php");

			$registro = $mysql->query("select * from perfil where id_perfil=$_REQUEST[id_perfil]") or
				die($mysql->error);

			if ($reg = $registro->fetch_array()) {
				?>
				<h1>Formulario de modificación de Perfiles</h1>
				<form method="post" action="modificacion.php">
					<fieldset>
						<label>Descripcion:</label>
						<input type="text" name="perfil_descripcion" size="45" value="<?php echo $reg['perfil_descripcion']; ?>" required><br>
						<label>Estado:</label>
						<select name="perfil_estado">
							<option value="">Seleccione el estado del perfil...</option>
							<option value="1"
							<?php
								if (isset($_REQUEST["perfil_estado"])){
									if ($_REQUEST["perfil_estado"] == 1){
										echo "selected";
									}
								}
							?>
							>Activo</option>
							<option value="0"<?php
								if (isset($_REQUEST["perfil_estado"])){
									if ($_REQUEST["perfil_estado"] == 0){
										echo "selected";
									}
								}
							?>
							>Baja</option>
						</select><br><br>
						<input type="hidden" name="id_perfil" value="<?php echo $_REQUEST['id_perfil']; ?>" required>
						<input type="submit" value="Confirmar">
					</fieldset>
				</form>
			<?php
			} else
				echo 'No existe un perfil con ese id';

			$mysql->close();

			?>
		</div>
	</body>	
</html>
