<h1>Listado de tipo de contactos</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='formulario.php'">Nuevo tipo de contacto</button><br><br>
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
	
	if (isset($_REQUEST["tipocontacto_descripcion"])){
		if ($_REQUEST["tipocontacto_descripcion"] != ""){
			$filtro .= " and tipocontacto_descripcion LIKE '%".$_REQUEST["tipocontacto_descripcion"]."%' ";
		}
	}
	if (isset($_REQUEST["tipocontacto_estado"])){
		if ($_REQUEST["tipocontacto_estado"] != ""){
			$filtro .= " and tipocontacto_estado = $_REQUEST[tipocontacto_estado] ";
		}
	}
	
	$registros = $mysql->query("select * from tipocontacto where 1=1 ".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["tipocontacto_descripcion"]."</td> 
			<td>".(($row["tipocontacto_estado"]) ? "Activo" : "Baja")."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_tipocontacto=".$row["id_tipocontacto"]."\"'>Editar</button>";
		
		// Mostrar botón Eliminar o Recuperar según el estado
		if ($row["tipocontacto_estado"] == 0) {
			// Si está de baja (estado 0), mostrar botón Recuperar
			echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"quitar_baja_logica.php?id_tipocontacto=".$row["id_tipocontacto"]."\", \"recuperar este tipo de contacto\")'>Recuperar</button>";
		} else {
			// Si está activo (estado 1), mostrar botón Eliminar
			echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_tipocontacto=".$row["id_tipocontacto"]."\", \"dar de baja este tipo de contacto\")'>Eliminar</button>";
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>