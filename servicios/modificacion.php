
<?php
  require("../conexion.php");

  $mysql->query("update servicio set 
				servicio_nombre='$_REQUEST[servicio_nombre]',
				servicio_descripcion='$_REQUEST[servicio_descripcion]',
				servicio_precio=$_REQUEST[servicio_precio],
				rela_tiposervicio=$_REQUEST[rela_tiposervicio],
				servicio_estado=$_REQUEST[servicio_estado],
				where id_servicio=$_REQUEST[id_servicio]") or
    die($mysql->error);

  echo 'Se modificaron los datos del servicio';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';
  
  $mysql->close();

  ?>