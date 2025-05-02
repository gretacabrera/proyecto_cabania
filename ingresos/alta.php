<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>Registrar ingreso al complejo</title>
		<link rel="stylesheet" href="../estilos.css">
		<script src="../funciones.js"></script>
	</head>
	<body class="home">
		<?php
			include("../menu.php");
		?>
		<div class="content">
            <?php
                require("../conexion.php");
                
                $mysql->query("update reserva 
                            set rela_estadoreserva = 
                                (SELECT id_estadoreserva FROM estadoreserva
                                WHERE estadoreserva_descripcion = 'en curso')
                            WHERE id_reserva = $_REQUEST[id_reserva]") or die($mysql->error);
                
                // cabania_estado = 2 --> ocupada

                $mysql->query("update cabania 
                                left join reserva on rela_cabania = id_cabania
                                set cabania_estado = 2
                                WHERE id_reserva = $_REQUEST[id_reserva]") or die($mysql->error);

                $registro = $mysql->query("select * from cabania 
                                            left join reserva on rela_cabania = id_cabania
                                            WHERE id_reserva = $_REQUEST[id_reserva]") or
                    die($mysql->error);
                $datos_cabania = $registro->fetch_array();
                $cabania_nombre = $datos_cabania["cabania_nombre"];
                $cabania_ubicacion = $datos_cabania["cabania_ubicacion"];

                echo 'Se registró correctamente el ingreso al complejo';
                echo '<br><br>';
                echo '<h1><b>La '.$cabania_nombre.' se encuentra '.$cabania_ubicacion.'</b></h1>';
                
                $mysql->close();
            ?>
		</div>
	</body>
</html>
