<?php
  require("../conexion.php");

  $mysql->query("update reserva set rela_estadoreserva = 6 WHERE id_reserva=$_REQUEST[id_reserva]") or
    die($mysql->error);

  echo 'Se anuló correctamente la reserva';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';

  $mysql->close();

  ?>