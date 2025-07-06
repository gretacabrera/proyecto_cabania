<h1>Listado de servicios</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='formulario.php'">Nuevo servicio</button><br><br>
</div>
<table> 
	<thead>
		<th> <font face="Arial">Nombre</font> </th> 
		<th> <font face="Arial">Descripcion</font> </th> 
		<th> <font face="Arial">Precio Unitario</font> </th> 
		<th> <font face="Arial">Tipo de Servicio</font> </th>  
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
	
	if (isset($_REQUEST["servicio_nombre"])){
		if ($_REQUEST["servicio_nombre"] != ""){
			$filtro .= " and servicio_nombre LIKE '%".$_REQUEST["servicio_nombre"]."%' ";
		}
	}
	if (isset($_REQUEST["servicio_descripcion"])){
		if ($_REQUEST["servicio_descripcion"] != ""){
			$filtro .= " and servicio_descripcion LIKE '%".$_REQUEST["servicio_descripcion"]."%' ";
		}
	}
	if (isset($_REQUEST["rela_tiposervicio"])){
		if ($_REQUEST["rela_tiposervicio"] != ""){
			$filtro .= " and rela_tiposervicio = $_REQUEST[rela_tiposervicio] ";
		}
	}
	if (isset($_REQUEST["servicio_estado"])){
		if ($_REQUEST["servicio_estado"] != ""){
			$filtro .= " and servicio_estado = $_REQUEST[servicio_estado] ";
		}
	}
	
	// Aplicar filtro de estado según el tipo de usuario
	if (!es_administrador()) {
		$filtro .= " and servicio_estado = 1 ";
	}
	
	$registros = $mysql->query("select * from servicio 
								left join tiposervicio on rela_tiposervicio = id_tiposervicio
								where 1=1 ".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["servicio_nombre"]."</td> 
			<td>".$row["servicio_descripcion"]."</td>  
			<td>".$row["servicio_precio"]."</td>
			<td>".$row["tiposervicio_descripcion"]."</td> 
			<td>".(($row["servicio_estado"]) ? "Activo" : "Baja")."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_servicio=".$row["id_servicio"]."\"'>Editar</button>";
		
		// Mostrar botón Eliminar o Recuperar según el estado
		if ($row["servicio_estado"]) {
			// Si está activo, mostrar botón Eliminar
			echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_servicio=".$row["id_servicio"]."\", \"dar de baja este servicio\")'>Eliminar</button>";
		} else if (es_administrador()) {
			// Si está de baja y es administrador, mostrar botón Recuperar
			echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"quitar_baja_logica.php?id_servicio=".$row["id_servicio"]."\", \"recuperar este servicio\")'>Recuperar</button>";
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>