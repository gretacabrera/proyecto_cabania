<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>Alta de nueva asignación modulo-perfil</title>
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
			<h1>Formulario de alta de asignación de módulo a perfil</h1>
			<form method="post" action="alta.php" onsubmit="return procesarFormularioAsincrono(this, 'Perfil creado correctamente', 'index.php')">
				<fieldset>
					Perfil:
					<select name="rela_perfil">
						<option value="">Seleccione el perfil...</option>
						<?php
							$registros = $mysql->query("select * from perfil where perfil_estado = 1") or
							die($mysql->error);
							while ($row = $registros->fetch_assoc()) {
								echo "<option value=".$row["id_perfil"].">".$row["perfil_descripcion"]."</option>";
							}
						?>
					</select><br>
					Módulo:
					<select name="rela_modulo">
						<option value="">Seleccione el modulo...</option>
						<?php
							$registros = $mysql->query("select * from modulo where modulo_estado = 1") or
							die($mysql->error);
							while ($row = $registros->fetch_assoc()) {
								echo "<option value=".$row["id_modulo"].">".$row["modulo_descripcion"]."</option>";
							}
						?>
					</select><br><br>
					<input type="submit" value="Confirmar">
				</fieldset>
			</form>
		</div>
	</body>
</html>
