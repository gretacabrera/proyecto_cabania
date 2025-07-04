<?php
  require("../conexion.php");

  $mysql->query("update perfil set perfil_estado = 0 WHERE id_perfil=$_REQUEST[id_perfil]") or
    die($mysql->error);

  echo 'Se dió de baja correctamente al perfil';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';

  $mysql->close();

  ?>