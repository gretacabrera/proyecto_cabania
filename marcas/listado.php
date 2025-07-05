<h1>Listado de Marcas</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='formulario.php'">Nuevo marca</button><br><br>
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
	
	if (isset($_REQUEST["marca_descripcion"])){
		if ($_REQUEST["marca_descripcion"] != ""){
			$filtro .= " and marca_descripcion LIKE '%".$_REQUEST["marca_descripcion"]."%' ";
		}
	}
	if (isset($_REQUEST["marca_estado"])){
		if ($_REQUEST["marca_estado"] != ""){
			$filtro .= " and marca_estado = $_REQUEST[marca_estado] ";
		}
	}
	
	$registros = $mysql->query("select * from marca where 1=1 ".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["marca_descripcion"]."</td> 
			<td>".(($row["marca_estado"]) ? "Activo" : "Baja")."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_marca=".$row["id_marca"]."\"'>Editar</button>
				<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_marca=".$row["id_marca"]."\")'>Borrar</button>
			</td>
		</tr>";
	}
?>
<table>