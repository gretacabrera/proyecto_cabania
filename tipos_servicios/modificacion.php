
<?php
  require("../conexion.php");

  $mysql->query("update tiposervicio set 
				tiposervicio_descripcion='$_REQUEST[tiposervicio_descripcion]',
				tiposervicio_estado=$_REQUEST[tiposervicio_estado]
				where id_tiposervicio=$_REQUEST[id_tiposervicio]") or
    die($mysql->error);

  echo 'Se modificaron los datos del tipo de servicio';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';
  
  $mysql->close();

  ?>