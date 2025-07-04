<?php
  require("../conexion.php");

  $mysql->query("update modulo set modulo_estado = 0 WHERE id_modulo=$_REQUEST[id_modulo]") or
    die($mysql->error);

  echo 'Se dió de baja correctamente al modulo';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';

  $mysql->close();

  ?>