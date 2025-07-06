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
				<form method="post" action="modificacion.php" onsubmit="return procesarFormularioAsincrono(this, 'Perfil modificado correctamente', 'index.php')">
					<fieldset>
						Perfil:
						<select name="rela_perfil">
							<option value="">Seleccione el perfil...</option>
							<?php
								$registros = $mysql->query("select * from perfil where perfil_estado = 1") or
								die($mysql->error);
								while ($row = $registros->fetch_assoc()) {
									echo "<option value=".$row["id_perfil"];
									if ($row["id_perfil"] == $reg['rela_perfil']){
										echo " selected";
									}
									echo ">".$row["perfil_descripcion"]."</option>";
								}
							?>
						</select><br>
						Perfil:
						<select name="rela_modulo">
							<option value="">Seleccione el modulo...</option>
							<?php
								$registros = $mysql->query("select * from modulo where modulo_estado = 1") or
								die($mysql->error);
								while ($row = $registros->fetch_assoc()) {
									echo "<option value=".$row["id_modulo"];
									if ($row["id_modulo"] == $reg['rela_modulo']){
										echo " selected";
									}
									echo ">".$row["modulo_descripcion"]."</option>";
								}
							?>
						</select><br><br>
						<input type="hidden" name="id_perfilmodulo" value="<?php echo $_REQUEST['id_perfilmodulo']; ?>" required>
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
