<?php
  require("../conexion.php");

  $mysql->query("update producto set rela_estadoproducto = 4 WHERE id_producto=$_REQUEST[id_producto]") or
    die($mysql->error);

  echo 'Se dió de baja correctamente al producto';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';

  $mysql->close();

  ?>