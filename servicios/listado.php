<h1>Listado de servicios</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='formulario.php'">Nuevo servicio</button><br><br>
</div>
<table> 
	<thead>
		<th> <font face="Arial">Nombre</font> </th> 
		<th> <font face="Arial">Descripcion</font> </th> 
		<th> <font face="Arial">Precio Unitario</font> </th> 
		<th> <font face="Arial">Tipo de Servicio</font> </th>  
		<th> <font face="Arial">Estado</font> </th> 
		<th> <font face="Arial">Acciones</font> </th> 
	</thead>
<?php
	require("../conexion.php");

	$filtro = "";
	
	if (isset($_REQUEST["servicio_nombre"])){
		if ($_REQUEST["servicio_nombre"] != ""){
			$filtro .= " and servicio_nombre LIKE '%".$_REQUEST["servicio_nombre"]."%' ";
		}
	}
	if (isset($_REQUEST["servicio_descripcion"])){
		if ($_REQUEST["servicio_descripcion"] != ""){
			$filtro .= " and servicio_descripcion LIKE '%".$_REQUEST["servicio_descripcion"]."%' ";
		}
	}
	if (isset($_REQUEST["rela_tiposervicio"])){
		if ($_REQUEST["rela_tiposervicio"] != ""){
			$filtro .= " and rela_tiposervicio = $_REQUEST[rela_tiposervicio] ";
		}
	}
	if (isset($_REQUEST["servicio_estado"])){
		if ($_REQUEST["servicio_estado"] != ""){
			$filtro .= " and servicio_estado = $_REQUEST[servicio_estado] ";
		}
	}
	
	$registros = $mysql->query("select * from servicio 
								left join tiposervicio on rela_tiposervicio = id_tiposervicio
								where 1=1 ".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["servicio_nombre"]."</td> 
			<td>".$row["servicio_descripcion"]."</td>  
			<td>".$row["servicio_precio"]."</td>
			<td>".$row["tiposervicio_descripcion"]."</td> 
			<td>".$row["servicio_estado"]."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_servicio=".$row["id_servicio"]."\"'>Editar</button>
				<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_servicio=".$row["id_servicio"]."\")'>Borrar</button>
			</td>
		</tr>";
	}
?>
<table>