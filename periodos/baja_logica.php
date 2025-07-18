<?php
require_once("../conexion.php");

$resultado = $mysql->query("update periodo set periodo_estado = 0 WHERE id_periodo=$_REQUEST[id_periodo]");

if ($resultado) {
  echo 'Se dió de baja correctamente el período';
} else {
  echo 'Error: ' . $mysql->error;
}

$mysql->close();
?>