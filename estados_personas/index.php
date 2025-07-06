<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>ABM de Estados de persona</title>
		<link rel="stylesheet" href="../estilos.css">
		<script src="../funciones.js"></script>
	</head>
	<body class="home">
		<?php
			include("../menu.php");
		?>
		<div class="content">
			<?php
				require("../includes/mensajes.php");
				mostrar_mensaje();
				require("../perfiles/validar_permiso.php");
				if (validar_permiso("estados_personas")){
					include("listado.php");
				}
				else{
					echo "No tiene permiso para acceder a este modulo.";
				}
			?>
		</div>
	</body>
</html>
