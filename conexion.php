<?php

require_once("funciones.php");

$hostname = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');
$schema = getenv('DB_SCHEMA');

$mysql = new mysqli($hostname, $username, $password, $schema);

if ($mysql->connect_error)
	die("Problemas con la conexión a la base de datos");
	
?>