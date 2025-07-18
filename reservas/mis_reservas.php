<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <title>Pago de nueva reserva</title>
        <link rel="stylesheet" href="../estilos.css">
    </head>
    <body class="home">
        <?php
            include("../menu.php");
        ?>
        <div class="content">
            <?php
                if (isset($_SESSION["usuario_nombre"])){
                    ?>
                    <h1>Mis Reservas</h1>
                    <br>
                    <table> 
                        <thead> 
                            <th> <font face="Arial">Fecha y hora de Inicio</font> </th> 
                            <th> <font face="Arial">Fecha y hora de Fin</font> </th> 
                            <th width="200"> <font face="Arial">Cabaña</font> </th> 
                            <th> <font face="Arial">Precio por dia</font> </th>
                            <th> <font face="Arial">Importe total</font> </th> 
                            <th> <font face="Arial">Estado</font> </th> 
                            <th> <font face="Arial">Acciones</font> </th> 
                        </thead>
                    <?php
                    require("conexion.php");
                    $registros = $mysql->query("select
                                                id_reserva,
                                                reserva_fhinicio,
                                                reserva_fhfin,
                                                estadoreserva_descripcion,
                                                rela_cabania,
                                                cabania_nombre,
                                                cabania_precio,
                                                case DATEDIFF(reserva_fhfin, reserva_fhinicio)
                                                    when 0 then 1
                                                    else DATEDIFF(reserva_fhfin, reserva_fhinicio)
                                                end as reserva_dias
                                                from reserva r
                                                left join estadoreserva er on r.rela_estadoreserva = er.id_estadoreserva
                                                left join cabania c on r.rela_cabania = c.id_cabania
                                                left join huesped_reserva hp on hp.rela_reserva = r.id_reserva
                                                left join huesped h on hp.rela_huesped = h.id_huesped
                                                left join persona p on h.rela_persona = p.id_persona
                                                left join usuario u on u.rela_persona = p.id_persona
                                                where u.usuario_nombre = '$_SESSION[usuario_nombre]'") or
                        die($mysql->error);
                    while ($row = $registros->fetch_assoc()){
                        $reserva_fhinicio = date_format(date_create($row["reserva_fhinicio"]), 'Y-m-d H:i');
                        $reserva_fhfin = date_format(date_create($row["reserva_fhfin"]), 'Y-m-d H:i');
                        $reserva_total = $row["cabania_precio"] * $row["reserva_dias"];
                        echo "
                            <tr>
                                <td>$reserva_fhinicio</td> 
                                <td>$reserva_fhfin</td>
                                <td>$row[cabania_nombre]</td>
                                <td>$$row[cabania_precio]</td>
                                <td>$$reserva_total</td>
                                <td>$row[estadoreserva_descripcion]</td>
                                <td>";
                        if ($row["estadoreserva_descripcion"] == "pendiente" | 
                            $row["estadoreserva_descripcion"] == "pendiente de pago"){
                            echo "
                                    <form method='post' action='pagar.php'>
                                        <input type='hidden' name='reserva_fhinicio' value='$row[reserva_fhinicio]'>
                                        <input type='hidden' name='reserva_fhfin' value='$row[reserva_fhfin]'>
                                        <input type='hidden' name='rela_cabania' value='$row[rela_cabania]'>
                                        <input type='hidden' name='reserva_total' value='$reserva_total'>
                                        <input type='hidden' name='id_reserva' value='$row[id_reserva]'>
                                        <input type='submit' value='Pagar'>
                                    </form>
                                ";
                        }
                        echo "  </td>
                            </tr>";
                    }
                    $mysql->close();
                }
                else{
                    echo "Para ver sus reservas, primero debe iniciar sesión.";
                }
            ?>
        </div>
    </body>
</html>