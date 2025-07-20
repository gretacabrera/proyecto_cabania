
<?php
// Configuración de paginación
$registros_por_pagina = isset($_REQUEST['registros_por_pagina']) ? intval($_REQUEST['registros_por_pagina']) : 10;
$pagina_actual = isset($_REQUEST['pagina']) ? intval($_REQUEST['pagina']) : 1;
?>

<h1>Listado de Productos Mas Vendidos por Mes</h1>
<?php
	include("busqueda.php");
?>

<!-- Selector de registros por página -->
<div style="margin-bottom: 10px;">
	<form method="get" style="display: inline;">
		<!-- Mantener filtros existentes -->
		<?php if (isset($_REQUEST["fecha_desde"]) && $_REQUEST["fecha_desde"] != ""): ?>
			<input type="hidden" name="fecha_desde" value="<?php echo htmlspecialchars($_REQUEST["fecha_desde"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["fecha_hasta"]) && $_REQUEST["fecha_hasta"] != ""): ?>
			<input type="hidden" name="fecha_hasta" value="<?php echo htmlspecialchars($_REQUEST["fecha_hasta"]); ?>">
		<?php endif; ?>
		
		<label for="registros_por_pagina">Mostrar:</label>
		<select name="registros_por_pagina" id="registros_por_pagina" onchange="this.form.submit()">
			<option value="10" <?php echo $registros_por_pagina == 10 ? 'selected' : ''; ?>>10 registros</option>
			<option value="25" <?php echo $registros_por_pagina == 25 ? 'selected' : ''; ?>>25 registros</option>
			<option value="50" <?php echo $registros_por_pagina == 50 ? 'selected' : ''; ?>>50 registros</option>
		</select>
	</form>
</div>

<div class="export">
	<input type="button" onclick="tableToExcel('tableResultados','Reporte_de_Productos_por_Categoria')" value="Exportar a Excel">
</div>

<?php
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
	
	// Query para contar total de registros
	$query_count = "SELECT COUNT(*) FROM (
						SELECT 
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
					) as subquery";
	
	// Query base para obtener registros
	$query_base = "SELECT 
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
	
	// Obtener registros paginados
	$resultado = obtener_registros_paginados($mysql, $query_base, $query_count, $pagina_actual, $registros_por_pagina);
	$registros_data = $resultado['registros'];
	$paginacion = $resultado['paginacion'];
	
	$mysql->close();
?>

<!-- Información de registros -->
<div class="pagination-info">
	<?php echo mostrar_info_paginacion($paginacion); ?>
</div>

<table id="tableResultados"> 
	<tr> 
		<td> <font face="Arial"><b>Fecha</b></font> </td> 
		<td> <font face="Arial"><b>Producto</b></font> </td> 
		<td> <font face="Arial"><b>Cantidad de ventas</b></font> </td> 
		<td> <font face="Arial"><b>Importe total</b></font> </td> 
	</tr>
	
<?php
	if (empty($registros_data)) {
		echo "<tr><td colspan='4' style='text-align: center; padding: 20px;'>No se encontraron registros</td></tr>";
	} else {
		foreach ($registros_data as $row) {
			echo 
			"<tr> 
				<td>".$row["fecha"]."</td> 
				<td>".$row["producto_nombre"]."</td>
				<td>".$row["cantidad_ventas"]."</td> 
				<td>".$row["importe_ventas"]."</td>
			</tr>";
		}
	}
?>
</table>

<?php
// Generar enlaces de paginación
$parametros_url = [];
if (isset($_REQUEST["fecha_desde"]) && $_REQUEST["fecha_desde"] != "") {
	$parametros_url['fecha_desde'] = $_REQUEST["fecha_desde"];
}
if (isset($_REQUEST["fecha_hasta"]) && $_REQUEST["fecha_hasta"] != "") {
	$parametros_url['fecha_hasta'] = $_REQUEST["fecha_hasta"];
}
if ($registros_por_pagina != 10) {
	$parametros_url['registros_por_pagina'] = $registros_por_pagina;
}

echo generar_enlaces_paginacion($paginacion, '/proyecto_cabania/plantilla_modulo.php?titulo=Productos más vendidos por mes&ruta=reportes/producto_mas_vendido_x_mes', $parametros_url);
?>