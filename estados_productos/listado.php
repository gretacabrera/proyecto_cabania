<h1>Listado de estados de productos</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='formulario.php'">Nuevo estado de producto</button><br><br>
</div>
<table> 
	<thead>
		<th> <font face="Arial">Descripcion</font> </th>
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
	
	if (isset($_REQUEST["estadoproducto_descripcion"])){
		if ($_REQUEST["estadoproducto_descripcion"] != ""){
			$filtro .= " and estadoproducto_descripcion LIKE '%".$_REQUEST["estadoproducto_descripcion"]."%' ";
		}
	}
	if (isset($_REQUEST["estadoproducto_estado"])){
		if ($_REQUEST["estadoproducto_estado"] != ""){
			$filtro .= " and estadoproducto_estado = $_REQUEST[estadoproducto_estado] ";
		}
	}
	
	// Aplicar filtro de estado según el tipo de usuario
	if (!es_administrador()) {
		$filtro .= " and estadoproducto_estado = 1 ";
	}
	
	$registros = $mysql->query("select * from estadoproducto where 1=1 ".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["estadoproducto_descripcion"]."</td> 
			<td>".(($row["estadoproducto_estado"]) ? "Activo" : "Baja")."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_estadoproducto=".$row["id_estadoproducto"]."\"'>Editar</button>";
		
		// Mostrar botón Eliminar o Recuperar según el estado y permisos
		if ($row["estadoproducto_estado"]) {
			// Si está activo, mostrar botón Eliminar
			echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_estadoproducto=".$row["id_estadoproducto"]."\", \"dar de baja este estado de producto\")'>Eliminar</button>";
		} else {
			// Si está de baja, solo mostrar botón Recuperar a administradores
			if (es_administrador()) {
				echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"quitar_baja_logica.php?id_estadoproducto=".$row["id_estadoproducto"]."\", \"recuperar este estado de producto\")'>Recuperar</button>";
			}
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>