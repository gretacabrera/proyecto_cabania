
<?php
  require("../conexion.php");

  $mysql->query("update categoria set 
				categoria_descripcion='$_REQUEST[categoria_descripcion]',
				categoria_estado=$_REQUEST[categoria_estado]
				where id_categoria=$_REQUEST[id_categoria]") or
    die($mysql->error);

  echo 'Se modificaron los datos de la categoria';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';
  
  $mysql->close();

  ?>