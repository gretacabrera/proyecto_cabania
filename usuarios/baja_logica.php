<?php
  require("../conexion.php");

  $mysql->query("update usuario set usuario_estado = 3 WHERE id_usuario=$_REQUEST[id_usuario]") or
    die($mysql->error);

  echo 'Se dió de baja correctamente al usuario';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';

  $mysql->close();

  ?>