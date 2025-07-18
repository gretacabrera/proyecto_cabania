<?php
    require("conexion.php");

    // el metodo de pago seleccionado por el huesped es informado
    // por la pasarela de pago externa
    $metododepago = "EFECTIVO"; // se simula que devolvió "EFECTIVO" como metodo de pago seleccionado
    
    $registro = $mysql->query("select id_metododepago
                                from metododepago
                                where metododepago_descripcion = '$metododepago'") or
    die($mysql->error);

    $rela_metododepago = $registro->fetch_array()["id_metododepago"]; 

    // pago_estado = 1 --> activo

    $mysql->query("insert into pago (pago_fechahora, pago_total, rela_reserva, rela_metododepago, pago_estado)
                    values (now(), $_REQUEST[reserva_total], $_REQUEST[rela_reserva], $rela_metododepago, 1)") or
        die($mysql->error);

    $mysql->query("update reserva 
                    set rela_estadoreserva = CASE
                                                WHEN rela_estadoreserva =
                                                    (select id_estadoreserva from estadoreserva 
                                                    where estadoreserva_descripcion = 'pendiente')
                                                    THEN (select id_estadoreserva from estadoreserva 
                                                    where estadoreserva_descripcion = 'confirmada')
                                                WHEN rela_estadoreserva =
                                                    (select id_estadoreserva from estadoreserva 
                                                    where estadoreserva_descripcion = 'pendiente de pago')
                                                    THEN (select id_estadoreserva from estadoreserva 
                                                    where estadoreserva_descripcion = 'finalizada')
                                            END
                    where id_reserva = $_REQUEST[rela_reserva]") or
    die($mysql->error);

    echo 'Se realizó el pago con éxito';

    if (!isset($pago_online)){
        echo "<br>";
        echo "<input type='button' onclick='location.href=\"mis_reservas.php\"' value='Volver'>";
    }
    
    $mysql->close();

?>