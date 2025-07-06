<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>ABM de Marcas</title>
		<link rel="stylesheet" href="../estilos.css">
		<script src="../funciones.js"></script>
	</head>
	<body class="home">
		<?php
			include("../menu.php");
		?>
		<div class="content">
			<?php
				require_once("../funciones.php");
				mostrar_mensaje();
				require_once("../funciones.php");
				if (validar_permiso("marcas")){
					include("listado.php");
				}
				else{
					echo "No tiene permiso para acceder a este modulo.";
				}
			?>
		</div>
	</body>
</html>
