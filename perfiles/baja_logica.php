<?php
require_once("../conexion.php");

// Verificar que el perfil a eliminar no sea "administrador"
$verificar = $mysql->query("SELECT perfil_descripcion FROM perfil WHERE id_perfil = $_REQUEST[id_perfil]");
$perfil = $verificar->fetch_assoc();

if ($perfil && strtolower($perfil["perfil_descripcion"]) == "administrador") {
	echo 'Error: No se puede eliminar el perfil administrador por motivos de seguridad';
	$mysql->close();
	exit;
}

$resultado = $mysql->query("update perfil set perfil_estado = 0 WHERE id_perfil=$_REQUEST[id_perfil]");

if ($resultado) {
	echo 'Se dió de baja correctamente el perfil';
} else {
	echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>