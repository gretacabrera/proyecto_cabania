<?php
  require("../conexion.php");

  $mysql->query("update servicio set servicio_estado = 0 WHERE id_servicio=$_REQUEST[id_servicio]") or
    die($mysql->error);

  echo 'Se dió de baja correctamente al servicio';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';

  $mysql->close();

  ?>