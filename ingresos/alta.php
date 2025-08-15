<?php
require("conexion.php");

$resultado1 = $mysql->query("update reserva 
            set rela_estadoreserva = 
                (SELECT id_estadoreserva FROM estadoreserva
                WHERE estadoreserva_descripcion = 'en curso')
            WHERE id_reserva = $_REQUEST[id_reserva]") or die($mysql->error);

// cabania_estado = 2 --> ocupada
$resultado2 = $mysql->query("update cabania 
                left join reserva on rela_cabania = id_cabania
                set cabania_estado = 2
                WHERE id_reserva = $_REQUEST[id_reserva]") or die($mysql->error);

if ($resultado1 && $resultado2) {
    $registro = $mysql->query("select * from cabania 
                                left join reserva on rela_cabania = id_cabania
                                WHERE id_reserva = $_REQUEST[id_reserva]") or
        die($mysql->error);
    $datos_cabania = $registro->fetch_array();
    $cabania_nombre = $datos_cabania["cabania_nombre"];
    $cabania_ubicacion = $datos_cabania["cabania_ubicacion"];
    
    $mensaje = "Se registró correctamente el ingreso al complejo. La $cabania_nombre se encuentra $cabania_ubicacion";
    
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Ingresos&ruta=ingresos&archivo=formulario.php',
        $mensaje,
        'exito'
    );
} else {
    echo 'Error al registrar el ingreso: ' . $mysql->error;
}

$mysql->close();
?>
