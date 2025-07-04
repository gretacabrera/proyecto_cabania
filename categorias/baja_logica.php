<?php
  require("../conexion.php");

  $mysql->query("update categoria set categoria_estado = 0 WHERE id_categoria=$_REQUEST[id_categoria]") or
    die($mysql->error);

  echo 'Se dió de baja correctamente la categoria';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';

  $mysql->close();

  ?>