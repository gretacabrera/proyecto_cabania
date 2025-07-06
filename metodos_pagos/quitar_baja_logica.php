<?php
	require("../conexion.php");
	require("../includes/mensajes.php");
	
	$resultado = $mysql->query("UPDATE metodopago SET metodopago_estado = 1 WHERE id_metodopago = $_REQUEST[id_metodopago]");
	
    if ($resultado) {
        echo 'Categoría recuperada correctamente';
    } else {
        echo 'Error: ' . $mysql->error;
    }

    $mysql->close();
?>
