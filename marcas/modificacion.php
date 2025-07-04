
<?php
  require("../conexion.php");

  $mysql->query("update marca set 
				marca_descripcion='$_REQUEST[marca_descripcion]',
				marca_estado=$_REQUEST[marca_estado]
				where id_marca=$_REQUEST[id_marca]") or
    die($mysql->error);

  echo 'Se modificaron los datos de la marca';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';
  
  $mysql->close();

  ?>