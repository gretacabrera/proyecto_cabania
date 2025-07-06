<h1>Listado de Perfiles</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='formulario.php'">Nuevo perfil</button><br><br>
</div>
<table> 
	<thead>
		<th> <font face="Arial">Perfil</font> </th>
		<th> <font face="Arial">Módulo</font> </th> 
		<th> <font face="Arial">Estado</font> </th> 
		<th> <font face="Arial">Acciones</font> </th> 
	</thead>
<?php
	require("../conexion.php");

	$filtro = "";
	
	if (isset($_REQUEST["rela_perfil"])){
		if ($_REQUEST["rela_perfil"] != ""){
			$filtro .= " and rela_perfil = $_REQUEST[rela_perfil] ";
		}
	}
	if (isset($_REQUEST["rela_modulo"])){
		if ($_REQUEST["rela_modulo"] != ""){
			$filtro .= " and rela_modulo = $_REQUEST[rela_modulo] ";
		}
	}
	if (isset($_REQUEST["perfilmodulo_estado"])){
		if ($_REQUEST["perfilmodulo_estado"] != ""){
			$filtro .= " and perfilmodulo_estado = $_REQUEST[perfilmodulo_estado] ";
		}
	}
	
	$registros = $mysql->query("select * from perfil_modulo
								left join perfil on rela_perfil = id_perfil
								left join modulo on rela_modulo = id_modulo
								where 1=1 ".$filtro) 
	or die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["perfil_descripcion"]."</td> 
			<td>".$row["modulo_descripcion"]."</td> 
			<td>".(($row["perfilmodulo_estado"]) ? "Activo" : "Baja")."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_perfilmodulo=".$row["id_perfilmodulo"]."\"'>Editar</button>";
		
		// Mostrar botón Eliminar o Recuperar según el estado
		if ($row["perfil_estado"]) {
			// Si está activo, mostrar botón Eliminar
			echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_perfilmodulo=".$row["id_perfilmodulo"]."\", \"dar de baja este perfil\")'>Eliminar</button>";
		} else {
			// Si está de baja, mostrar botón Recuperar
			echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"quitar_baja_logica.php?id_perfilmodulo=".$row["id_perfilmodulo"]."\", \"recuperar este perfil\")'>Recuperar</button>";
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>