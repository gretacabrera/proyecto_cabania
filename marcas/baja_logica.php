<?php
  require("../conexion.php");

  $mysql->query("update marca set marca_estado = 0 WHERE id_marca=$_REQUEST[id_marca]") or
    die($mysql->error);

  echo 'Se dió de baja correctamente la marca';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';

  $mysql->close();

  ?>