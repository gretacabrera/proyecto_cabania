<h1>Listado de condiciones de salud</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='formulario.php'">Nueva condicion de salud</button><br><br>
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
	
	if (isset($_REQUEST["condicionsalud_descripcion"])){
		if ($_REQUEST["condicionsalud_descripcion"] != ""){
			$filtro .= " and condicionsalud_descripcion LIKE '%".$_REQUEST["condicionsalud_descripcion"]."%' ";
		}
	}
	if (isset($_REQUEST["condicionsalud_estado"])){
		if ($_REQUEST["condicionsalud_estado"] != ""){
			$filtro .= " and condicionsalud_estado = $_REQUEST[condicionsalud_estado] ";
		}
	}
	
	$registros = $mysql->query("select * from condicionsalud where 1=1 ".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["condicionsalud_descripcion"]."</td> 
			<td>".(($row["condicionsalud_estado"]) ? "Activo" : "Baja")."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_condicionsalud=".$row["id_condicionsalud"]."\"'>Editar</button>";
		
		// Mostrar botón Eliminar o Recuperar según el estado
		if ($row["condicionsalud_estado"]) {
			// Si está activo, mostrar botón Eliminar
			echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_condicionsalud=".$row["id_condicionsalud"]."\", \"dar de baja esta categoría\")'>Eliminar</button>";
		} else {
			// Si está de baja, mostrar botón Recuperar
			echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"quitar_baja_logica.php?id_condicionsalud=".$row["id_condicionsalud"]."\", \"recuperar esta categoría\")'>Recuperar</button>";
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>