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
	require("../conexion.php");

	$filtro = "";
	
	if (isset($_REQUEST["metodopago_descripcion"])){
		if ($_REQUEST["metodopago_descripcion"] != ""){
			$filtro .= " and metodopago_descripcion LIKE '%".$_REQUEST["metodopago_descripcion"]."%' ";
		}
	}
	if (isset($_REQUEST["metodopago_estado"])){
		if ($_REQUEST["metodopago_estado"] != ""){
			$filtro .= " and metodopago_estado = $_REQUEST[metodopago_estado] ";
		}
	}
	
	$registros = $mysql->query("select * from metodopago where 1=1 ".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["metodopago_descripcion"]."</td> 
			<td>".(($row["metodopago_estado"]) ? "Activo" : "Baja")."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_metodopago=".$row["id_metodopago"]."\"'>Editar</button>";
		
		// Mostrar botón Eliminar o Recuperar según el estado
		if ($row["metodopago_estado"]) {
			// Si está activo, mostrar botón Eliminar
			echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_metodopago=".$row["id_metodopago"]."\", \"dar de baja esta categoría\")'>Eliminar</button>";
		} else {
			// Si está de baja, mostrar botón Recuperar
			echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"quitar_baja_logica.php?id_metodopago=".$row["id_metodopago"]."\", \"recuperar esta categoría\")'>Recuperar</button>";
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>