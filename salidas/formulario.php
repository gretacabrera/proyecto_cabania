<h1>Registrar salida del complejo</h1>
<?php
    if (isset($_SESSION["usuario_nombre"])){

        require("../conexion.php");

        $registro = $mysql->query("select * from reserva r
                                    left join cabania c on r.rela_cabania = c.id_cabania
                                    left join huesped_reserva hr on hr.rela_reserva = r.id_reserva
                                    left join huesped h on hr.rela_huesped = h.id_huesped
                                    left join persona p on h.rela_persona = p.id_persona
                                    left join usuario u on u.rela_persona = p.id_persona
                                    where u.usuario_nombre='$_SESSION[usuario_nombre]'
                                    and r.rela_estadoreserva IN 
                                        (select id_estadoreserva from estadoreserva
                                        where estadoreserva_descripcion = 'en curso')
                                    and now() between r.reserva_fhinicio and r.reserva_fhfin") or
            die($mysql->error);
        
        $mostrar = "<h2>No se ha encontrado una reserva en curso en este momento.</h2>";

        while ($row = $registro->fetch_assoc()) {
            $mostrar = 
                "<form method='post' action='alta.php'>
                    <fieldset>
                        <p><b>Fecha de inicio:</b> ".date_format(date_create($row["reserva_fhinicio"]), 'Y-m-d H:i')."</p>
                        <p><b>Fecha de fin:</b> ".date_format(date_create($row["reserva_fhfin"]), 'Y-m-d H:i')."</p>
                        <p><b>Cabaña:</b> $row[cabania_nombre]</p>
                        <input type='hidden' name='id_reserva' value='$row[id_reserva]' required>
                        <input type='submit' class='abm-button' value='Confirmar'>
                    </fieldset>
                </form>";
        }

        echo $mostrar;

        $mysql->close();
    }
    else{
        echo "Para registrar una salida, primero debe iniciar sesión.";
    }
?>