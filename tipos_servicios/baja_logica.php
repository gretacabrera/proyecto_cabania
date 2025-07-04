<?php
  require("../conexion.php");

  $mysql->query("update tiposervicio set tiposervicio_estado = 0 WHERE id_tiposervicio=$_REQUEST[id_tiposervicio]") or
    die($mysql->error);

  echo 'Se dió de baja correctamente al tipo de servicio';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';

  $mysql->close();

  ?>