
<h1>Listado de Temporadas Altas por Año</h1>
<div>
	<div>
		<div class="export">
			<input type="button" onclick="tableToExcel('tableAnios','Reporte_de_Temporadas_Altas_Por_Anio')" value="Exportar a Excel">
		</div>
		<table id="tableAnios"> 
			<tr> 
				<td> <font face="Arial"><b>Año</b></font> </td> 
				<td> <font face="Arial"><b>Temporada Alta</b></font> </td> 
				<td> <font face="Arial"><b>Ver detalle por Período</b></font> </td>
			</tr>
			
		<?php			
			$consulta_sql = "select 
							reservas_x_periodo.periodo_anio as anio,
							periodo_descripcion as temporada_alta
							from
							(SELECT
							periodo_descripcion,
							periodo_anio,
							periodo_orden,
							COUNT(id_reserva) cantidad_reservas
							FROM reserva
							LEFT JOIN periodo ON rela_periodo = id_periodo
							GROUP BY periodo_descripcion, periodo_anio, periodo_orden
							ORDER BY periodo_anio ASC, periodo_orden ASC) reservas_x_periodo
							JOIN 
							(select 
							periodo_anio,
							MAX(cantidad_reservas) maximo_reservas
							from (SELECT
							periodo_descripcion,
							periodo_anio,
							periodo_orden,
							COUNT(id_reserva) cantidad_reservas
							FROM reserva
							LEFT JOIN periodo ON rela_periodo = id_periodo
							GROUP BY periodo_descripcion, periodo_anio, periodo_orden
							ORDER BY periodo_anio ASC, periodo_orden ASC) reservas_x_periodo
							GROUP BY periodo_anio
							ORDER BY periodo_anio ASC) maximo_reserva_x_anio
							ON reservas_x_periodo.cantidad_reservas = maximo_reserva_x_anio.maximo_reservas";
						
			$registros = $mysql->query($consulta_sql) or
			die($mysql->error);
			
			while ($row = $registros->fetch_assoc()) {
				echo 
				"<tr> 
					<td>".$row["anio"]."</td> 
					<td>".$row["temporada_alta"]."</td>
					<td><button onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Temporadas Altas por Año&ruta=reportes/temporada_alta_x_anio&archivo=listado.php&anio=".$row["anio"]."\"'>Ver</button></td>
				</tr>";
			}
			?>
		</table>
	</div>
	<br>
	<div>
		<div class="export">
			<input type="button" onclick="tableToExcel('tablePeriodos','Reporte_de_Cantidad_de_Reservas_por_Periodo')" value="Exportar a Excel">
		</div>
		<table id="tablePeriodos">
			<tr> 
				<td> <font face="Arial"><b>Período</b></font> </td> 
				<td> <font face="Arial"><b>Cantidad de Reservas</b></font> </td> 
			</tr>
			<?php
				if (isset($_REQUEST["anio"])){
					if ($_REQUEST["anio"] != ""){
						$consulta_sql = "SELECT
									periodo_descripcion,
									periodo_anio,
									periodo_orden,
									COUNT(id_reserva) cantidad_reservas
									FROM reserva
									LEFT JOIN periodo ON rela_periodo = id_periodo
									WHERE periodo_anio = ".$_REQUEST["anio"]."
									GROUP BY periodo_descripcion, periodo_anio, periodo_orden
									ORDER BY periodo_anio ASC, periodo_orden ASC";
						$registros = $mysql->query($consulta_sql) or
						die($mysql->error);
						
						while ($row = $registros->fetch_assoc()) {
							echo 
							"<tr> 
								<td>".$row["periodo_descripcion"]."</td> 
								<td>".$row["cantidad_reservas"]."</td>
							</tr>";
						}
					}
				}
			?>
		</table>
	</div>
</div>