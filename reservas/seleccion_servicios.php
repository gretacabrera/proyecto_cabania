<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <title>Alta de nueva reserva</title>
        <link rel="stylesheet" href="../estilos.css">
    </head>
    <body class="home">
        <?php
            include("../menu.php");
        ?>
        <div class="content">
            <?php
                $pago_online = true;
                include("pagar.php");
            ?>
            <p>¿Desea adicionar servicios de valor agregado a su reserva?</p>
            <div class="galeria">
                <form method="post" action="guardar_servicios.php">
                    <ul class="galeria-lista">
                        <?php
                            require("conexion.php");
                            
                            $registros = $mysql->query("select * from servicio where servicio_estado <> 2") or
                            die($mysql->error);
                            
                            while ($row = $registros->fetch_assoc()) {
                                echo 
                                "<div class='galeria-item'>
                                    <div>
                                        <input type='checkbox' name='servicios[]' value='$row[id_servicio]'> 
                                        <label><b>".$row["servicio_nombre"]."</b></label><br>
                                        <label>".$row["servicio_descripcion"]."</label><br>
                                        <label>$".$row["servicio_precio"]."</label><br><br>
                                    </div>      
                                </div>";
                            }
                        ?>
                    </ul>
                    <input type='hidden' name="rela_reserva" value='<?php echo $_REQUEST["rela_reserva"]; ?>'>
                    <input type='button' onclick='location.href="mis_reservas.php"' value='Omitir'>
                    <input type='submit' value='Confirmar'>
                </form>
            </div>
        </div>
    </body>
</html>