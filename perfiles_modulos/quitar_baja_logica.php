<?php
	require("../conexion.php");
	require("../includes/mensajes.php");
	
	$resultado = $mysql->query("UPDATE perfil_modulo SET perfilmodulo_estado = 1 WHERE id_perfilmodulo = $_REQUEST[id_perfilmodulo]");

    if ($resultado) {
        echo 'Asignación perfil-modulo recuperada correctamente';
    } else {
        echo 'Error: ' . $mysql->error;
    }

    $mysql->close();
?>
