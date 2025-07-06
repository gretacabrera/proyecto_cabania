
<?php
  require("../conexion.php");

  $mysql->query("update tipocontacto set 
				tipocontacto_descripcion='$_REQUEST[tipocontacto_descripcion]'
				where id_tipocontacto=$_REQUEST[id_tipocontacto]") or
    die($mysql->error);

  echo 'Se modificaron los datos del tipo de contacto';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';
  
  $mysql->close();

  ?>