<h1>Listado de Reservas</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='formulario_reserva.php'">Nueva reserva</button><br><br>
</div>
<table> 
	<thead> 
		<th> <font face="Arial">Fecha y hora de Inicio</font> </th> 
		<th> <font face="Arial">Fecha y hora de Fin</font> </th> 
		<th> <font face="Arial">Cabaña</font> </th> 
		<th> <font face="Arial">Período</font> </th>
		<th> <font face="Arial">Estado</font> </th> 
		<th> <font face="Arial">Acciones</font> </th> 
	</thead>
<?php
	require("../conexion.php");

	$reserva_fhinicio = "2000-01-01T00:00";
	$reserva_fhfin = "2030-12-31T00:00";

	if (isset($_REQUEST["reserva_fhinicio"])){
		if ($_REQUEST["reserva_fhinicio"] != ""){
			$reserva_fhinicio = date_format(date_create($_REQUEST["reserva_fhinicio"]), 'Y-m-d H:i');
		}
	}
	if (isset($_REQUEST["reserva_fhfin"])){
		if ($_REQUEST["reserva_fhfin"] != ""){
			$reserva_fhfin = date_format(date_create($_REQUEST["reserva_fhfin"]), 'Y-m-d H:i');
		}
	}
	
	$filtro = " DATE_FORMAT(reserva_fhinicio, '%Y-%m-%d %H:%i') BETWEEN '".$reserva_fhinicio."' AND '".$reserva_fhfin."' ";
	
	if (isset($_REQUEST["rela_cabania"])){
		if ($_REQUEST["rela_cabania"] != ""){
			$filtro .= " and rela_cabania = ".$_REQUEST["rela_cabania"]." ";
		}
	}
	if (isset($_REQUEST["rela_periodo"])){
		if ($_REQUEST["rela_periodo"] != ""){
			$filtro .= " and rela_periodo = $_REQUEST[rela_periodo] ";
		}
	}
	if (isset($_REQUEST["rela_estadoreserva"])){
		if ($_REQUEST["rela_estadoreserva"] != ""){
			$filtro .= " and rela_estadoreserva = $_REQUEST[rela_estadoreserva] ";
		}
	}
	
	$registros = $mysql->query("select * from reserva
								left join cabania on rela_cabania = id_cabania
								left join estadoreserva on rela_estadoreserva = id_estadoreserva
								left join periodo on rela_periodo = id_periodo 
								where ".$filtro."
								order by reserva_fhinicio asc") or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["reserva_fhinicio"]."</td> 
			<td>".$row["reserva_fhfin"]."</td>  
			<td>".$row["cabania_nombre"]."</td> 
			<td>".$row["periodo_descripcion"]."</td>
			<td>".$row["estadoreserva_descripcion"]."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_reserva=".$row["id_reserva"]."\"'>Editar</button>";
		
		// Mostrar botón Anular o Reactivar según el estado
		if ($row["rela_estadoreserva"] == 6) {
			// Si está anulada (estado 6), mostrar botón Reactivar
			echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"quitar_baja_logica.php?id_reserva=".$row["id_reserva"]."\", \"reactivar esta reserva\")'>Reactivar</button>";
		} else {
			// Si está activa, mostrar botón Anular
			echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_reserva=".$row["id_reserva"]."\", \"anular esta reserva\")'>Anular</button>";
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>