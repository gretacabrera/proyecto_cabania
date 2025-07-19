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
    <form method="post" action="plantilla_modulo.php?titulo=Pagar Reserva&ruta=reservas/online&archivo=formulario_pagar.php">
        <input type="hidden" name="reserva_fhinicio" value="<?php echo $_REQUEST["reserva_fhinicio"]; ?>">
        <input type="hidden" name="reserva_fhfin" value="<?php echo $_REQUEST["reserva_fhfin"]; ?>">
        <input type="hidden" name="rela_cabania" value="<?php echo $_REQUEST["rela_cabania"]; ?>">
        <input type="submit" value="Confirmar">
    </form>
</div>