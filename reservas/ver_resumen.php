<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <title>Confirmación de nueva reserva</title>
        <link rel="stylesheet" href="../estilos.css">
    </head>
    <body class="home">
        <?php
            include("../menu.php");
        ?>
        <div class="content">
            <p class="subrayado"><b>Resumen de la reserva:</b></p>
            <p><b>Fecha de inicio:</b> <?php echo date_format(date_create($_REQUEST["reserva_fhinicio"]), 'Y-m-d H:i'); ?></p>
            <p><b>Fecha de fin:</b> <?php echo date_format(date_create($_REQUEST["reserva_fhfin"]), 'Y-m-d H:i'); ?></p>
            <?php
                require("conexion.php");
                $registro = $mysql->query("select cabania_nombre from cabania where id_cabania = $_REQUEST[rela_cabania]") or
                    die($mysql->error);
                $cabania_nombre = $registro->fetch_array()["cabania_nombre"];
                $mysql->close();
            ?>
            <p><b>Cabaña:</b> <?php echo $cabania_nombre; ?></p>
            <form method="post" action="pagar_reserva.php">
                <input type="hidden" name="reserva_fhinicio" value="<?php echo $_REQUEST["reserva_fhinicio"]; ?>">
                <input type="hidden" name="reserva_fhfin" value="<?php echo $_REQUEST["reserva_fhfin"]; ?>">
                <input type="hidden" name="rela_cabania" value="<?php echo $_REQUEST["rela_cabania"]; ?>">
                <input type="submit" value="Confirmar">
            </form>
        </div>
    </body>
</html>