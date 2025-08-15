<?php
require("conexion.php");

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

$resultado1 = $mysql->query("update reserva 
            set rela_estadoreserva = 
                (SELECT id_estadoreserva FROM estadoreserva
                WHERE estadoreserva_descripcion = '$estado_reserva')
            WHERE id_reserva = $_REQUEST[id_reserva]") or die($mysql->error);

// cabania_estado = 1 --> libre
$resultado2 = $mysql->query("update cabania 
                left join reserva on rela_cabania = id_cabania
                set cabania_estado = 1
                WHERE id_reserva = $_REQUEST[id_reserva]") or die($mysql->error);

if ($resultado1 && $resultado2) {
    $mensaje = "Se registró correctamente la salida de la cabaña";
    
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Salidas&ruta=salidas&archivo=formulario.php',
        $mensaje,
        'exito'
    );
} else {
    echo 'Error al registrar la salida: ' . $mysql->error;
}

$mysql->close();
?>
