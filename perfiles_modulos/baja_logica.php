<?php
require_once("../conexion.php");

$resultado = $mysql->query("UPDATE perfil_modulo SET perfilmodulo_estado = 0 WHERE id_perfilmodulo=$_REQUEST[id_perfilmodulo]");

if ($resultado) {
  echo 'Se dió de baja correctamente la asignación de módulo al perfil';
} else {
  echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>