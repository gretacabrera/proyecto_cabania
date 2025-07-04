<h1>Listado de Modulos</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='formulario.php'">Nuevo modulo</button><br><br>
</div>
<table> 
	<thead>
		<th> <font face="Arial">Descripcion</font> </th>
		<th> <font face="Arial">Ruta</font> </th>
		<th> <font face="Arial">Estado</font> </th> 
		<th> <font face="Arial">Acciones</font> </th> 
	</thead>
<?php
	require("../conexion.php");

	$filtro = "";
	
	if (isset($_REQUEST["modulo_descripcion"])){
		if ($_REQUEST["modulo_descripcion"] != ""){
			$filtro .= " and modulo_descripcion LIKE '%".$_REQUEST["modulo_descripcion"]."%' ";
		}
	}
	if (isset($_REQUEST["modulo_estado"])){
		if ($_REQUEST["modulo_estado"] != ""){
			$filtro .= " and modulo_estado = $_REQUEST[modulo_estado] ";
		}
	}
	
	$registros = $mysql->query("select * from modulo where 1=1 ".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["modulo_descripcion"]."</td> 
			<td>".$row["modulo_ruta"]."</td> 
			<td>".(($row["modulo_estado"]) ? "Activo" : "Baja")."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_modulo=".$row["id_modulo"]."\"'>Editar</button>
				<button class='abm-button baja-button' onclick='location.href=\"baja_logica.php?id_modulo=".$row["id_modulo"]."\"'>Borrar</button>
			</td>
		</tr>";
	}
?>
<table>