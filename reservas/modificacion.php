
<?php
  require("../conexion.php");

  $mysql->query("update reserva set 
				reserva_fhinicio='$_REQUEST[reserva_fhinicio]',
				reserva_fhfin='$_REQUEST[reserva_fhfin]',
				rela_cabania=$_REQUEST[rela_cabania],
				rela_estadoreserva=$_REQUEST[rela_estadoreserva]
				where id_reserva=$_REQUEST[id_reserva]") or
    die($mysql->error);

  echo 'Se modificaron los datos de la reserva';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';
  
  $mysql->close();

  ?>