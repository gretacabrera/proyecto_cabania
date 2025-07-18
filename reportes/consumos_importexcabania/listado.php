
<h1>Listado de Consumos en Pesos por Cabaña</h1>
<?php
	include("busqueda.php");
?>
<div class="export">
	<input type="button" onclick="tableToExcel('tableResultados','Reporte_de_Consumos_Importes_Por_Cabania')" value="Exportar a Excel">
</div>
<table id="tableResultados"> 
	<tr> 
		<td> <font face="Arial"><b>Cabaña</b></font> </td> 
		<td> <font face="Arial"><b>Consumo en Pesos</b></font> </td> 
	</tr>
	
<?php
	$filtro = '';
	
	if (isset($_REQUEST["rela_cabania"])){
		if ($_REQUEST["rela_cabania"] != ""){
			$filtro .= " and id_cabania = ".$_REQUEST["rela_cabania"]." ";
		}
	}
	
	$consulta_sql = "select 
				concat(cabania_codigo,' - ',cabania_nombre) as cabania,
				sum(consumo_total) as consumo
				from cabania
				left join reserva on rela_cabania = id_cabania
				left join consumo on rela_reserva = id_reserva
				where 1 = 1
				".$filtro."
				group by cabania_codigo, cabania_nombre
				order by cabania_codigo";
				
	$registros = $mysql->query($consulta_sql) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["cabania"]."</td> 
			<td> $".round($row["consumo"],2)."</td>  
		</tr>";
	}
?>
<table>