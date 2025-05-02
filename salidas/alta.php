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

                // si se registraron pagos que saldan la deuda
                // el estado de la reserva cambia a "finalizada"
                // de lo contrario, cambia a "pendiente de pago"
                $estado_reserva = "pendiente de pago";

                $registro = $mysql->query("select
                                                CASE 
                                                    when (importe_estadia + importe_consumos) <= total_pagado then 'OK'
                                                    else 'NO'
                                                END as resultado
                                            FROM
                                            (SELECT
                                                (SELECT 
                                                    (cabania_precio  *
                                                    (case DATEDIFF(reserva_fhfin, reserva_fhinicio)
                                                        when 0 then 1
                                                        else DATEDIFF(reserva_fhfin, reserva_fhinicio)
                                                    end))
                                                FROM cabania
                                                WHERE id_cabania = r.rela_cabania) as importe_estadia,
                                                (SELECT IFNULL(SUM(consumo_total),0)
                                                FROM consumo
                                                WHERE rela_reserva = r.id_reserva) as importe_consumos,
                                                (SELECT IFNULL(sum(pago_total),0)
                                                FROM pago
                                                where rela_reserva = r.id_reserva) as total_pagado
                                            FROM reserva r
                                            WHERE r.id_reserva = $_REQUEST[id_reserva]) totales") or
                    die($mysql->error);
                
                $estado_pagos = $registro->fetch_array()["resultado"];
                
                if ($estado_pagos == "OK"){
                    $estado_reserva = "finalizada";
                }
                
                $mysql->query("update reserva 
                            set rela_estadoreserva = 
                                (SELECT id_estadoreserva FROM estadoreserva
                                WHERE estadoreserva_descripcion = '$estado_reserva')
                            WHERE id_reserva = $_REQUEST[id_reserva]") or die($mysql->error);
                
                // cabania_estado = 1 --> libre

                $mysql->query("update cabania 
                                left join reserva on rela_cabania = id_cabania
                                set cabania_estado = 1
                                WHERE id_reserva = $_REQUEST[id_reserva]") or die($mysql->error);

                echo 'Se registró correctamente la salida de la cabaña';
                
                $mysql->close();
            ?>
		</div>
	</body>
</html>
