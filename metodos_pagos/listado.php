<h1>Listado de metodos de pago</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='formulario.php'">Nuevo metodo de pago</button><br><br>
</div>
<table> 
	<thead>
		<th> <font face="Arial">Descripcion</font> </th>
		<th> <font face="Arial">Estado</font> </th> 
		<th> <font face="Arial">Acciones</font> </th> 
	</thead>
<?php
	// Iniciar sesión si no está iniciada
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	$filtro = "";
	
	if (isset($_REQUEST["metododepago_descripcion"])){
		if ($_REQUEST["metododepago_descripcion"] != ""){
			$filtro .= " and metododepago_descripcion LIKE '%".$_REQUEST["metododepago_descripcion"]."%' ";
		}
	}
	if (isset($_REQUEST["metododepago_estado"])){
		if ($_REQUEST["metododepago_estado"] != ""){
			$filtro .= " and metododepago_estado = $_REQUEST[metododepago_estado] ";
		}
	}
	
	// Aplicar filtro de estado según el tipo de usuario
	if (!es_administrador()) {
		$filtro .= " and metododepago_estado = 1 ";
	}
	
	$registros = $mysql->query("select * from metododepago where 1=1 ".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["metododepago_descripcion"]."</td> 
			<td>".(($row["metododepago_estado"]) ? "Activo" : "Baja")."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_metododepago=".$row["id_metododepago"]."\"'>Editar</button>";
		
		// Mostrar botón Eliminar o Recuperar según el estado y permisos
		if ($row["metododepago_estado"]) {
			// Si está activo, mostrar botón Eliminar
			echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_metododepago=".$row["id_metododepago"]."\", \"dar de baja este método de pago\")'>Eliminar</button>";
		} else {
			// Si está de baja, solo mostrar botón Recuperar a administradores
			if (es_administrador()) {
				echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"quitar_baja_logica.php?id_metododepago=".$row["id_metododepago"]."\", \"recuperar este método de pago\")'>Recuperar</button>";
			}
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>