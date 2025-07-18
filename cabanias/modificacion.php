<?php
require("conexion.php");

// Manejar la foto
$cabania_foto = $_REQUEST['cabania_foto_actual']; // Mantener la foto actual por defecto

if (isset($_FILES['cabania_foto']) && $_FILES['cabania_foto']['error'] == 0) {
    $target_dir = "imagenes/cabanias/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["cabania_foto"]["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($_FILES["cabania_foto"]["tmp_name"], $target_file)) {
        // Eliminar la foto anterior si existe
        if ($cabania_foto && file_exists($target_dir . $cabania_foto)) {
            unlink($target_dir . $cabania_foto);
        }
        $cabania_foto = $new_filename;
    }
}

$resultado = $mysql->query("update cabania set 
    cabania_codigo='$_REQUEST[cabania_codigo]', 
    cabania_nombre='$_REQUEST[cabania_nombre]', 
    cabania_descripcion='$_REQUEST[cabania_descripcion]', 
    cabania_capacidad='$_REQUEST[cabania_capacidad]', 
    cabania_precio='$_REQUEST[cabania_precio]', 
    cabania_ubicacion='$_REQUEST[cabania_ubicacion]', 
    cabania_cantidadbanios='$_REQUEST[cabania_cantidadbanios]', 
    cabania_cantidadhabitaciones='$_REQUEST[cabania_cantidadhabitaciones]', 
    cabania_foto='$cabania_foto' 
    where id_cabania=$_REQUEST[id_cabania]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Cabañas&ruta=cabanias&archivo=listado.php',
		'Se modificó la cabaña correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
