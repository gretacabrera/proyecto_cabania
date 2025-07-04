
<?php
  require("../conexion.php");

  $mysql->query("update perfil set 
				perfil_descripcion='$_REQUEST[perfil_descripcion]',
				perfil_estado=$_REQUEST[perfil_estado]
				where id_perfil=$_REQUEST[id_perfil]") or
    die($mysql->error);

  echo 'Se modificaron los datos del perfil';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';
  
  $mysql->close();

  ?>