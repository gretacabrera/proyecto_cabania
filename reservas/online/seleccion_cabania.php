<?php

$filtro = '';

if (isset($_REQUEST["capacidad"])) {
    if ($_REQUEST["capacidad"] != "") {
        $filtro .= " and cabania_capacidad = " . $_REQUEST["capacidad"] . " ";
    }
}
if (isset($_REQUEST["banios"])) {
    if ($_REQUEST["banios"] != "") {
        $filtro .= " and cabania_cantidadbanios = " . $_REQUEST["banios"] . " ";
    }
}
if (isset($_REQUEST["habitaciones"])) {
    if ($_REQUEST["habitaciones"] != "") {
        $filtro .= " and cabania_cantidadhabitaciones = " . $_REQUEST["habitaciones"] . " ";
    }
}

$query = "select * from cabania
                where cabania_estado = 1
                and id_cabania not in
                (select id_cabania from cabania
                left join reserva on rela_cabania = id_cabania
                left join estadoreserva on rela_estadoreserva = id_estadoreserva
                where rela_estadoreserva <> 6
                and reserva_fhinicio between '$_REQUEST[reserva_fhinicio]' 
                                        and '$_REQUEST[reserva_fhfin]')
                $filtro";

$registros = $mysql->query($query) or
    die($mysql->error);
?>

<div class="content">
    <form method="post" action="plantilla_modulo.php?titulo=Seleccion de Cabaña&ruta=reservas/online&archivo=seleccion_cabania.php">
        Capacidad de personas:
        <select name="capacidad">
            <option value="">Seleccione la cantidad...</option>
            <?php
            $capacidades = $mysql->query("select distinct a.cabania_capacidad as capacidad from ($query) a") or
                die($mysql->error);
            while ($row = $capacidades->fetch_assoc()) {
                echo "<option value='" . $row["capacidad"] . "'";
                if (isset($_REQUEST["capacidad"])) {
                    if ($_REQUEST["capacidad"] == $row["capacidad"]) {
                        echo "selected";
                    }
                }
                echo ">" . $row["capacidad"] . "</option>";
            }
            ?>
        </select>
        Cantidad de baños:
        <select name="banios">
            <option value="">Seleccione la cantidad...</option>
            <?php
            $banios = $mysql->query("select distinct a.cabania_cantidadbanios as banios from ($query) a") or
                die($mysql->error);
            while ($row = $banios->fetch_assoc()) {
                echo "<option value='" . $row["banios"] . "'";
                if (isset($_REQUEST["banios"])) {
                    if ($_REQUEST["banios"] == $row["banios"]) {
                        echo "selected";
                    }
                }
                echo ">" . $row["banios"] . "</option>";
            }
            ?>
        </select>
        Cantidad de habitaciones:
        <select name="habitaciones">
            <option value="">Seleccione la cantidad...</option>
            <?php
            $habitaciones = $mysql->query("select distinct a.cabania_cantidadhabitaciones as habitaciones from ($query) a") or
                die($mysql->error);
            while ($row = $habitaciones->fetch_assoc()) {
                echo "<option value='" . $row["habitaciones"] . "'";
                if (isset($_REQUEST["habitaciones"])) {
                    if ($_REQUEST["habitaciones"] == $row["habitaciones"]) {
                        echo "selected";
                    }
                }
                echo ">" . $row["habitaciones"] . "</option>";
            }
            ?>
        </select>
        <input type="hidden" name="reserva_fhinicio" value="<?php echo $_REQUEST["reserva_fhinicio"]; ?>">
        <input type="hidden" name="reserva_fhfin" value="<?php echo $_REQUEST["reserva_fhfin"]; ?>">
        <input type="submit" value="Buscar">
        <input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
    </form>
    <div>

        <div class="galeria">
            <?php
            while ($row = $registros->fetch_assoc()) {
                echo
                "<div class='galeria-item galeria-item-foto'>
                <figure>
                    <img src='imagenes/cabanias/" . $row["cabania_foto"] . "'>
                </figure>
                <div>
                    <label><b>" . $row["cabania_nombre"] . "</b></label><br>
                    <label>" . $row["cabania_descripcion"] . "</label><br><br>
                    <label>Capacidad de Personas: " . $row["cabania_capacidad"] . "</label><br>
                    <label>Baños: " . $row["cabania_cantidadbanios"] . "</label><br>
                    <label>Habitaciones: " . $row["cabania_cantidadhabitaciones"] . "</label><br>
                    <label>Ubicación: " . $row["cabania_ubicacion"] . "</label><br>
                    <label>Precio por día: <b>$" . $row["cabania_precio"] . "</b></label><br><br>
                    <form method='post' action='plantilla_modulo.php?titulo=Ver Resumen de Reserva&ruta=reservas/online&archivo=ver_resumen.php'>
                        <input type='hidden' name='reserva_fhinicio' value='" . $_REQUEST["reserva_fhinicio"] . "'>
                        <input type='hidden' name='reserva_fhfin' value='" . $_REQUEST["reserva_fhfin"] . "'>
                        <input type='hidden' name='rela_cabania' value='" . $row["id_cabania"] . "'>
                        <input type='submit' value='Seleccionar'>
                    </form>      
                </div>      
            </div>";
            }
            ?>
        </div>
    </div>
</div>