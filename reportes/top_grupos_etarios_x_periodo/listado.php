
<h1>Listado de Frecuencias de Reservas por Grupo Etario</h1>

<div class="export">
	<input type="button" onclick="tableToExcel('tableResultados','Reporte_de_Frecuencias_de_Reservas_por_Grupo_Etario')" value="Exportar a Excel">
</div>
<table id="tableResultados">
	<tr> 
		<td> <font face="Arial"><b>Grupo Etario</b></font> </td> 
		<td> <font face="Arial"><b>Frecuencias de Reservas</b></font> </td> 
	</tr>
	<?php
		if (isset($_REQUEST["id_periodo"])){
			if ($_REQUEST["id_periodo"] != ""){

				require("../../conexion.php");
				
				$consulta_sql = "select
								case 
									when edad < 18 then 'menores de 18 años'
									when edad between 18 and 27 then 'jovenes (18-27 años)'
									when edad between 28 and 50 then 'adultos (28-50 años)'
									when edad between 51 and 70 then 'adultos mayores (51-70 años)'
									else 'ancianos (mayores de 70 años)'
								end as grupo_etario,
								sum(cantidad_reservas) cantidad_reservas
								from (select 
								timestampdiff(YEAR, persona_fechanac, curdate()) AS edad,
								count(id_reserva) as cantidad_reservas
								from huesped
								left join persona on rela_persona = id_persona
								left join huesped_reserva on rela_huesped = id_huesped
								left join reserva on rela_reserva = id_reserva
								where rela_periodo = ".$_REQUEST["id_periodo"]."
								group by edad) reservas_x_edad
								group by grupo_etario
								order by cantidad_reservas desc";
				$registros = $mysql->query($consulta_sql) or
				die($mysql->error);
				
				while ($row = $registros->fetch_assoc()) {
					echo 
					"<tr> 
						<td>".$row["grupo_etario"]."</td> 
						<td>".$row["cantidad_reservas"]."</td>
					</tr>";
				}
			}
		}
	?>
</table>