<?php
  require("../conexion.php");

  $mysql->query("update periodo set periodo_estado = 0 WHERE id_periodo=$_REQUEST[id_periodo]") or
    die($mysql->error);

  echo 'Se dió de baja correctamente al periodo';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';

  $mysql->close();

  ?>