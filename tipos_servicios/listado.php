<h1>Listado de tipo de servicios</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='formulario.php'">Nuevo tipo de servicio</button><br><br>
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
	
	if (isset($_REQUEST["tiposervicio_descripcion"])){
		if ($_REQUEST["tiposervicio_descripcion"] != ""){
			$filtro .= " and tiposervicio_descripcion LIKE '%".$_REQUEST["tiposervicio_descripcion"]."%' ";
		}
	}
	if (isset($_REQUEST["tiposervicio_estado"])){
		if ($_REQUEST["tiposervicio_estado"] != ""){
			$filtro .= " and tiposervicio_estado = $_REQUEST[tiposervicio_estado] ";
		}
	}
	
	$registros = $mysql->query("select * from tiposervicio where 1=1 ".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["tiposervicio_descripcion"]."</td> 
			<td>".(($row["tiposervicio_estado"]) ? "Activo" : "Baja")."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_tiposervicio=".$row["id_tiposervicio"]."\"'>Editar</button>";
		
		// Mostrar botón Eliminar o Recuperar según el estado
		if ($row["tiposervicio_estado"] == 0) {
			// Si está de baja (estado 0), mostrar botón Recuperar
			echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"quitar_baja_logica.php?id_tiposervicio=".$row["id_tiposervicio"]."\", \"recuperar este tipo de servicio\")'>Recuperar</button>";
		} else {
			// Si está activo (estado 1), mostrar botón Eliminar
			echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_tiposervicio=".$row["id_tiposervicio"]."\", \"dar de baja este tipo de servicio\")'>Eliminar</button>";
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>