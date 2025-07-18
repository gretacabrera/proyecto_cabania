<?php
require("conexion.php");

// Manejar la subida de la foto
$producto_foto = null;
if (isset($_FILES['producto_foto']) && $_FILES['producto_foto']['error'] == 0) {
    $target_dir = "imagenes/productos/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["producto_foto"]["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($_FILES["producto_foto"]["tmp_name"], $target_file)) {
        $producto_foto = $new_filename;
    }
}

$resultado = $mysql->query("insert into producto (producto_nombre, producto_descripcion, producto_precio, producto_stock, producto_foto, rela_marca, rela_categoria, rela_estadoproducto) values ('$_REQUEST[producto_nombre]', 
'$_REQUEST[producto_descripcion]', $_REQUEST[producto_precio], $_REQUEST[producto_stock], '$producto_foto', $_REQUEST[rela_marca], $_REQUEST[rela_categoria], $_REQUEST[rela_estadoproducto])");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Productos&ruta=productos&archivo=listado.php',
		'Se dió de alta el producto correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>
