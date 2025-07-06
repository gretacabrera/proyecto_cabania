
<?php
  require("../conexion.php");

  $mysql->query("update periodo set 
				periodo_descripcion='$_REQUEST[periodo_descripcion]',
        periodo_fechainicio='$_REQUEST[periodo_fechainicio]',
        periodo_fechafin='$_REQUEST[periodo_fechafin]',
        periodo_anio=$_REQUEST[periodo_anio],
        periodo_orden=$_REQUEST[periodo_orden]
				where id_periodo=$_REQUEST[id_periodo]") or
    die($mysql->error);

  echo 'Se modificaron los datos del periodo';
  echo '<br>';
  echo '<button onclick="location.href=\'index.php\'">Volver</button>';
  
  $mysql->close();

  ?>