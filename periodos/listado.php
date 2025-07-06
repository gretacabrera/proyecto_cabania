<h1>Listado de periodos</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='formulario.php'">Nuevo periodo</button><br><br>
</div>
<table> 
	<thead>
		<th> <font face="Arial">Descripcion</font> </th>
		<th> <font face="Arial">Año</font> </th>
		<th> <font face="Arial">Fecha Inicio</font> </th>
		<th> <font face="Arial">Fecha Fin</font> </th>
		<th> <font face="Arial">Orden</font> </th>
		<th> <font face="Arial">Estado</font> </th> 
		<th> <font face="Arial">Acciones</font> </th> 
	</thead>
<?php
	require("../conexion.php");

	$filtro = "";
	
	if (isset($_REQUEST["periodo_descripcion"])){
		if ($_REQUEST["periodo_descripcion"] != ""){
			$filtro .= " and periodo_descripcion LIKE '%".$_REQUEST["periodo_descripcion"]."%' ";
		}
	}
	if (isset($_REQUEST["periodo_anio"])){
		if ($_REQUEST["periodo_anio"] != ""){
			$filtro .= " and periodo_anio = $_REQUEST[periodo_anio] ";
		}
	}
	if (isset($_REQUEST["periodo_estado"])){
		if ($_REQUEST["periodo_estado"] != ""){
			$filtro .= " and periodo_estado = $_REQUEST[periodo_estado] ";
		}
	}
	
	$registros = $mysql->query("select * from periodo where 1=1 ".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["periodo_descripcion"]."</td> 
			<td>".$row["periodo_anio"]."</td> 
			<td>".$row["periodo_fechainicio"]."</td> 
			<td>".$row["periodo_fechafin"]."</td> 
			<td>".$row["periodo_orden"]."</td> 
			<td>".(($row["periodo_estado"]) ? "Activo" : "Baja")."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_periodo=".$row["id_periodo"]."\"'>Editar</button>";
		
		// Mostrar botón Eliminar o Recuperar según el estado
		if ($row["periodo_estado"]) {
			// Si está activo, mostrar botón Eliminar
			echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_periodo=".$row["id_periodo"]."\", \"dar de baja este módulo\")'>Eliminar</button>";
		} else {
			// Si está de baja, mostrar botón Recuperar
			echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"quitar_baja_logica.php?id_periodo=".$row["id_periodo"]."\", \"recuperar este módulo\")'>Recuperar</button>";
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>