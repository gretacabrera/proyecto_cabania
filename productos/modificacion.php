
<?php
require("conexion.php");

// Manejar la foto
$producto_foto = $_REQUEST['producto_foto_actual']; // Mantener la foto actual por defecto

if (isset($_FILES['producto_foto']) && $_FILES['producto_foto']['error'] == 0) {
    $target_dir = "imagenes/productos/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["producto_foto"]["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($_FILES["producto_foto"]["tmp_name"], $target_file)) {
        // Eliminar la foto anterior si existe
        if ($producto_foto && file_exists($target_dir . $producto_foto)) {
            unlink($target_dir . $producto_foto);
        }
        $producto_foto = $new_filename;
    }
}

$resultado = $mysql->query("update producto set 
			producto_nombre='$_REQUEST[producto_nombre]',
			producto_descripcion='$_REQUEST[producto_descripcion]',
			producto_precio=$_REQUEST[producto_precio],
			producto_stock=$_REQUEST[producto_stock],
			producto_foto='$producto_foto',
			rela_marca=$_REQUEST[rela_marca],
			rela_categoria=$_REQUEST[rela_categoria],
			rela_estadoproducto=$_REQUEST[rela_estadoproducto]
			where id_producto=$_REQUEST[id_producto]");

if ($resultado) {
	redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Productos&ruta=productos&archivo=listado.php',
		'Se modificaron los datos del producto correctamente',
		'exito'
	);
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>