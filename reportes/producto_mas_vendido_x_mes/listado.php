
<h1>Listado de Productos Mas Vendidos por Mes</h1>

<div class="export">
	<input type="button" onclick="tableToExcel('tableResultados','Reporte_de_Productos_por_Categoria')" value="Exportar a Excel">
</div>
<table id="tableResultados"> 
	<tr> 
		<td> <font face="Arial"><b>Fecha</b></font> </td> 
		<td> <font face="Arial"><b>Producto</b></font> </td> 
		<td> <font face="Arial"><b>Cantidad de ventas</b></font> </td> 
		<td> <font face="Arial"><b>Importe total</b></font> </td> 
	</tr>
	
<?php
	require("../../conexion.php");
	
	$fecha_desde = "2000-01";
	$fecha_hasta = "2030-12";
	
	if (isset($_REQUEST["fecha_desde"])){
		if ($_REQUEST["fecha_desde"] != ""){
			$fecha_desde = $_REQUEST["fecha_desde"];
		}
	}
	if (isset($_REQUEST["fecha_hasta"])){
		if ($_REQUEST["fecha_hasta"] != ""){
			$fecha_hasta = $_REQUEST["fecha_hasta"];
		}
	}
	
	$filtro = " AND DATE_FORMAT(reserva_fhfin, '%Y-%m') BETWEEN '".$fecha_desde."' AND '".$fecha_hasta."' ";
	
	$consulta_sql = "SELECT 
						ventas.fecha, 
						ventas.producto_nombre, 
						ventas.cantidad_ventas, 
						ventas.importe_ventas
					FROM
						(SELECT 
							DATE_FORMAT(reserva_fhfin, '%Y-%m') as fecha, 
							producto_nombre, 
							SUM(consumo_cantidad) AS cantidad_ventas,
							SUM(consumo_total) AS importe_ventas
						 FROM consumo
						 LEFT JOIN reserva ON rela_reserva = id_reserva
						 LEFT JOIN producto ON rela_producto = id_producto
						 WHERE rela_servicio is null
						 ".$filtro."
						 GROUP BY DATE_FORMAT(reserva_fhfin, '%Y-%m'), producto_nombre) AS ventas
					JOIN
						(SELECT fecha, MAX(cantidad_ventas) AS max_ventas
						 FROM
							 (SELECT 
							 		DATE_FORMAT(reserva_fhfin, '%Y-%m') as fecha, 
									producto_nombre, 
									SUM(consumo_cantidad) AS cantidad_ventas,
									SUM(consumo_total) AS importe_ventas
								FROM consumo
								LEFT JOIN reserva ON rela_reserva = id_reserva
								LEFT JOIN producto ON rela_producto = id_producto
								WHERE rela_servicio is null
								".$filtro."
								GROUP BY DATE_FORMAT(reserva_fhfin, '%Y-%m'), producto_nombre) AS sub
						 GROUP BY fecha) AS max_ventas
					ON ventas.fecha = max_ventas.fecha AND ventas.cantidad_ventas = max_ventas.max_ventas
					ORDER BY ventas.fecha";
					
	$registros = $mysql->query($consulta_sql) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["fecha"]."</td> 
			<td>".$row["producto_nombre"]."</td>
			<td>".$row["cantidad_ventas"]."</td> 
			<td>".$row["importe_ventas"]."</td>
		</tr>";
	}
?>
<table>