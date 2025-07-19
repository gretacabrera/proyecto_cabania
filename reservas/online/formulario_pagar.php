<div class="content">
    <?php
        include("reservas/alta.php");
    ?>
    <br><br>
    <p>Para confirmar su reserva, deberá abonar el monto correspondiente a:</p>
    <?php
    $registro = $mysql->query("select 
                                cabania_precio,
                                case reserva_dias
                                    when 0 then 1
                                    else reserva_dias
                                end as reserva_dias
                                from (select
                                cabania_precio,
                                DATEDIFF(reserva_fhfin, reserva_fhinicio) as reserva_dias
                                from reserva
                                left join cabania on rela_cabania = id_cabania
                                where id_reserva = $rela_reserva) a") or
        die($mysql->error);
    $datos_reserva = $registro->fetch_array();
    $cabania_precio = $datos_reserva["cabania_precio"];
    $reserva_dias = $datos_reserva["reserva_dias"];
    $mysql->close();
    $reserva_total = $cabania_precio * $reserva_dias;
    echo "
        <p>Precio cabaña: $" . $cabania_precio . " x" . $reserva_dias . "dias = $" . $reserva_total . "<p>
        <form method='post' action='plantilla_modulo.php?titulo=Seleccionar Servicios Adicionales&ruta=reservas/online&archivo=seleccion_servicios.php'>
            <input type='hidden' name='reserva_total' value='$reserva_total'>
            <input type='hidden' name='rela_reserva' value='$rela_reserva'>
            <input type='submit' value='Pagar'>
        </form>
    ";
    ?>