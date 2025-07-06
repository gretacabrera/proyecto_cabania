<h1>Listado de Modulos</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='formulario.php'">Nuevo modulo</button><br><br>
</div>
<table> 
	<thead>
		<th> <font face="Arial">Descripcion</font> </th>
		<th> <font face="Arial">Ruta</font> </th>
		<th> <font face="Arial">Estado</font> </th> 
		<th> <font face="Arial">Acciones</font> </th> 
	</thead>
<?php
	require("../conexion.php");
	require_once("../funciones.php");
	
	// Iniciar sesión si no está iniciada
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	$filtro = "";
	
	if (isset($_REQUEST["modulo_descripcion"])){
		if ($_REQUEST["modulo_descripcion"] != ""){
			$filtro .= " and modulo_descripcion LIKE '%".$_REQUEST["modulo_descripcion"]."%' ";
		}
	}
	if (isset($_REQUEST["modulo_estado"])){
		if ($_REQUEST["modulo_estado"] != ""){
			$filtro .= " and modulo_estado = $_REQUEST[modulo_estado] ";
		}
	}
	
	// Aplicar filtro de estado según el tipo de usuario
	if (!es_administrador()) {
		$filtro .= " and modulo_estado = 1 ";
	}
	
	$registros = $mysql->query("select * from modulo where 1=1 ".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["modulo_descripcion"]."</td> 
			<td>".$row["modulo_ruta"]."</td> 
			<td>".(($row["modulo_estado"]) ? "Activo" : "Baja")."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_modulo=".$row["id_modulo"]."\"'>Editar</button>";
		
		// Mostrar botón Eliminar o Recuperar según el estado
		if ($row["modulo_estado"]) {
			// Si está activo, mostrar botón Eliminar
			echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_modulo=".$row["id_modulo"]."\", \"dar de baja este módulo\")'>Eliminar</button>";
		} else if (es_administrador()) {
			// Si está de baja y es administrador, mostrar botón Recuperar
			echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"quitar_baja_logica.php?id_modulo=".$row["id_modulo"]."\", \"recuperar este módulo\")'>Recuperar</button>";
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>