
<?php
  require("../conexion.php");

  $mysql->query("update modulo set 
				modulo_descripcion='$_REQUEST[modulo_descripcion]',
				modulo_estado=$_REQUEST[modulo_estado]
				where id_modulo=$_REQUEST[id_modulo]") or
    die($mysql->error);

  echo 'Se modificaron los datos del modulo';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';
  
  $mysql->close();

  ?>