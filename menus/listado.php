<h1>Listado de Menus</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='/proyecto_cabania/plantilla_modulo.php?titulo=Menus&ruta=menus&archivo=formulario.php'">Nuevo menu</button><br><br>
</div>
<table> 
	<thead>
		<th> <font face="Arial">Nombre</font> </th>
		<th> <font face="Arial">Estado</font> </th> 
		<th> <font face="Arial">Acciones</font> </th> 
	</thead>
<?php
	// Iniciar sesión si no está iniciada
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	$filtro = "";
	
	if (isset($_REQUEST["menu_nombre"])){
		if ($_REQUEST["menu_nombre"] != ""){
			$filtro .= " and menu_nombre LIKE '%".$_REQUEST["menu_nombre"]."%' ";
		}
	}
	if (isset($_REQUEST["menu_estado"])){
		if ($_REQUEST["menu_estado"] != ""){
			$filtro .= " and menu_estado = $_REQUEST[menu_estado] ";
		}
	}
	
	// Aplicar filtro de estado según el tipo de usuario
	if (!es_administrador()) {
		$filtro .= " and menu_estado = 1 ";
	}
	
	$registros = $mysql->query("select * from menu where 1=1 ".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["menu_nombre"]."</td> 
			<td>".(($row["menu_estado"]) ? "Activo" : "Baja")."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Menus&ruta=menus&archivo=editar.php&id_menu=".$row["id_menu"]."\"'>Editar</button>";
		
		// Mostrar botón Eliminar o Recuperar según el estado y permisos
		if ($row["menu_estado"]) {
			// Si está activo, mostrar botón Eliminar
			echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"/proyecto_cabania/menus/baja_logica.php?id_menu=".$row["id_menu"]."\", \"dar de baja este menu\")'>Eliminar</button>";
		} else {
			// Si está de baja, solo mostrar botón Recuperar a administradores
			if (es_administrador()) {
				echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"/proyecto_cabania/menus/quitar_baja_logica.php?id_menu=".$row["id_menu"]."\", \"recuperar este menu\")'>Recuperar</button>";
			}
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>
