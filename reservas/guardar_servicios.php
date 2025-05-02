<?php
    if (isset($_POST['servicios'])){

        require("../conexion.php");

        $servicios_seleccionados = $_POST['servicios'];
        
        foreach ($servicios_seleccionados as $id_servicio) {

            // consumo_cantidad = 1 --> los servicios se consumen de forma individual
            // consumo_estado = 1 --> activo

            $registro = $mysql->query("select * from servicio where id_servicio = $id_servicio") or
                die($mysql->error);

            $datos_servicio = $registro->fetch_array();

            $mysql->query("insert into consumo (consumo_descripcion, consumo_cantidad, consumo_total, rela_reserva, rela_servicio, consumo_estado) 
						values ('$datos_servicio[servicio_nombre]', 1, $datos_servicio[servicio_precio], $_REQUEST[rela_reserva], $id_servicio, 1)") or 
            die($mysql->error);
        }

        $mysql->close();
        
        echo "Se registraron correctamente sus consumos.";
        echo '<br>';
		echo '<button onclick="location.href=\'mis_reservas.php\'">Volver</button>';
    }
?>
