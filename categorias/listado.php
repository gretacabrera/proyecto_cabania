<h1>Listado de categorias</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='formulario.php'">Nuevo categoria</button><br><br>
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
	
	if (isset($_REQUEST["categoria_descripcion"])){
		if ($_REQUEST["categoria_descripcion"] != ""){
			$filtro .= " and categoria_descripcion LIKE '%".$_REQUEST["categoria_descripcion"]."%' ";
		}
	}
	if (isset($_REQUEST["categoria_estado"])){
		if ($_REQUEST["categoria_estado"] != ""){
			$filtro .= " and categoria_estado = $_REQUEST[categoria_estado] ";
		}
	}
	
	$registros = $mysql->query("select * from categoria where 1=1 ".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["categoria_descripcion"]."</td> 
			<td>".(($row["categoria_estado"]) ? "Activo" : "Baja")."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_categoria=".$row["id_categoria"]."\"'>Editar</button>";
		
		// Mostrar botón Eliminar o Recuperar según el estado
		if ($row["categoria_estado"]) {
			// Si está activo, mostrar botón Eliminar
			echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_categoria=".$row["id_categoria"]."\", \"dar de baja esta categoría\")'>Eliminar</button>";
		} else {
			// Si está de baja, mostrar botón Recuperar
			echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"quitar_baja_logica.php?id_categoria=".$row["id_categoria"]."\", \"recuperar esta categoría\")'>Recuperar</button>";
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>