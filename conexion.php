<?php

$mysql = new mysqli("localhost", "root", "0205", "proyecto_cabania");

if ($mysql->connect_error)
	die("Problemas con la conexión a la base de datos");
	
?>