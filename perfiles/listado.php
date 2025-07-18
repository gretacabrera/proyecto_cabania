<h1>Listado de Perfiles</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='/proyecto_cabania/plantilla_modulo.php?titulo=Perfiles&ruta=perfiles&archivo=formulario.php'">Nuevo perfil</button><br><br>
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
	
	if (isset($_REQUEST["perfil_descripcion"])){
		if ($_REQUEST["perfil_descripcion"] != ""){
			$filtro .= " and perfil_descripcion LIKE '%".$_REQUEST["perfil_descripcion"]."%' ";
		}
	}
	if (isset($_REQUEST["perfil_estado"])){
		if ($_REQUEST["perfil_estado"] != ""){
			$filtro .= " and perfil_estado = $_REQUEST[perfil_estado] ";
		}
	}
	
	// Aplicar filtro de estado según el tipo de usuario
	if (!es_administrador()) {
		$filtro .= " and perfil_estado = 1 ";
	}
	
	$registros = $mysql->query("select * from perfil where 1=1 ".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["perfil_descripcion"]."</td> 
			<td>".(($row["perfil_estado"]) ? "Activo" : "Baja")."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Perfiles&ruta=perfiles&archivo=editar.php&id_perfil=".$row["id_perfil"]."\"'>Editar</button>";
		
		// Mostrar botón Eliminar o Recuperar según el estado
		if ($row["perfil_estado"]) {
			// Si está activo, mostrar botón Eliminar
			echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"/proyecto_cabania/perfiles/baja_logica.php?id_perfil=".$row["id_perfil"]."\", \"dar de baja este perfil\")'>Eliminar</button>";
		} else if (es_administrador()) {
			// Si está de baja y es administrador, mostrar botón Recuperar
			echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"/proyecto_cabania/perfiles/quitar_baja_logica.php?id_perfil=".$row["id_perfil"]."\", \"recuperar este perfil\")'>Recuperar</button>";
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>