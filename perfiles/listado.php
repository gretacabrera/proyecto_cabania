<h1>Listado de Perfiles</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='formulario.php'">Nuevo perfil</button><br><br>
</div>
<table> 
	<thead>
		<th> <font face="Arial">Descripcion</font> </th>
		<th> <font face="Arial">Estado</font> </th> 
		<th> <font face="Arial">Acciones</font> </th> 
	</thead>
<?php
	require("../conexion.php");

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
	
	$registros = $mysql->query("select * from perfil where 1=1 ".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["perfil_descripcion"]."</td> 
			<td>".(($row["perfil_estado"]) ? "Activo" : "Baja")."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_perfil=".$row["id_perfil"]."\"'>Editar</button>
				<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_perfil=".$row["id_perfil"]."\")'>Borrar</button>
			</td>
		</tr>";
	}
?>
<table>