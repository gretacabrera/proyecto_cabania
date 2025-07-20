<?php
require_once("../conexion.php");

// Verificar que la relación a eliminar no pertenezca al perfil "administrador"
$verificar = $mysql->query("SELECT p.perfil_descripcion 
                           FROM perfil_modulo pm 
                           INNER JOIN perfil p ON pm.rela_perfil = p.id_perfil 
                           WHERE pm.id_perfilmodulo = $_REQUEST[id_perfilmodulo]");
$relacion = $verificar->fetch_assoc();

if ($relacion && strtolower($relacion["perfil_descripcion"]) == "administrador") {
	echo 'Error: No se pueden eliminar permisos del perfil administrador por motivos de seguridad';
	$mysql->close();
	exit;
}

$resultado = $mysql->query("UPDATE perfil_modulo SET perfilmodulo_estado = 0 WHERE id_perfilmodulo=$_REQUEST[id_perfilmodulo]");

if ($resultado) {
  echo 'Se dió de baja correctamente la asignación de módulo al perfil';
} else {
  echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>