
<?php
// Configuración de paginación
$registros_por_pagina = isset($_REQUEST['registros_por_pagina']) ? intval($_REQUEST['registros_por_pagina']) : 10;
$pagina_actual = isset($_REQUEST['pagina']) ? intval($_REQUEST['pagina']) : 1;
?>

<h1>Listado de Temporadas Altas por Año</h1>

<!-- Selector de registros por página -->
<div style="margin-bottom: 10px;">
	<form method="get" style="display: inline;">
		<!-- Mantener filtros existentes -->
		<?php if (isset($_REQUEST["anio"]) && $_REQUEST["anio"] != ""): ?>
			<input type="hidden" name="anio" value="<?php echo htmlspecialchars($_REQUEST["anio"]); ?>">
		<?php endif; ?>
		
		<label for="registros_por_pagina">Mostrar:</label>
		<select name="registros_por_pagina" id="registros_por_pagina" onchange="this.form.submit()">
			<option value="10" <?php echo $registros_por_pagina == 10 ? 'selected' : ''; ?>>10 registros</option>
			<option value="25" <?php echo $registros_por_pagina == 25 ? 'selected' : ''; ?>>25 registros</option>
			<option value="50" <?php echo $registros_por_pagina == 50 ? 'selected' : ''; ?>>50 registros</option>
		</select>
	</form>
</div>

<div>
	<div>
		<div class="export">
			<input type="button" onclick="tableToExcel('tableAnios','Reporte_de_Temporadas_Altas_Por_Anio')" value="Exportar a Excel">
		</div>

		<?php			
			// Query para contar total de registros de temporadas altas
			$query_count_anios = "SELECT COUNT(*) FROM (
								SELECT 
									reservas_x_periodo.periodo_anio as anio,
									periodo_descripcion as temporada_alta
								FROM
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
									(SELECT 
										periodo_anio,
										MAX(cantidad_reservas) maximo_reservas
									FROM (SELECT
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
								ON reservas_x_periodo.cantidad_reservas = maximo_reserva_x_anio.maximo_reservas
							) as subquery";
			
			// Query base para obtener registros de temporadas altas
			$query_base_anios = "SELECT 
								reservas_x_periodo.periodo_anio as anio,
								periodo_descripcion as temporada_alta
							FROM
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
								(SELECT 
									periodo_anio,
									MAX(cantidad_reservas) maximo_reservas
								FROM (SELECT
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
							ON reservas_x_periodo.cantidad_reservas = maximo_reserva_x_anio.maximo_reservas
							ORDER BY anio";
			
			// Obtener registros paginados de temporadas altas
			$resultado_anios = obtener_registros_paginados($mysql, $query_base_anios, $query_count_anios, $pagina_actual, $registros_por_pagina);
			$registros_anios = $resultado_anios['registros'];
			$paginacion_anios = $resultado_anios['paginacion'];
		?>

		<!-- Información de registros de temporadas altas -->
		<div class="pagination-info">
			<?php echo mostrar_info_paginacion($paginacion_anios); ?>
		</div>

		<table id="tableAnios"> 
			<tr> 
				<td> <font face="Arial"><b>Año</b></font> </td> 
				<td> <font face="Arial"><b>Temporada Alta</b></font> </td> 
				<td> <font face="Arial"><b>Ver detalle por Período</b></font> </td>
			</tr>
			
		<?php
			if (empty($registros_anios)) {
				echo "<tr><td colspan='3' style='text-align: center; padding: 20px;'>No se encontraron registros</td></tr>";
			} else {
				foreach ($registros_anios as $row) {
					echo 
					"<tr> 
						<td>".$row["anio"]."</td> 
						<td>".$row["temporada_alta"]."</td>
						<td><button onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Temporadas Altas por Año&ruta=reportes/temporada_alta_x_anio&archivo=listado.php&anio=".$row["anio"]."\"'>Ver</button></td>
					</tr>";
				}
			}
			?>
		</table>
		
		<?php
		// Generar enlaces de paginación para temporadas altas
		$parametros_url_anios = [];
		if ($registros_por_pagina != 10) {
			$parametros_url_anios['registros_por_pagina'] = $registros_por_pagina;
		}

		echo generar_enlaces_paginacion($paginacion_anios, '/proyecto_cabania/plantilla_modulo.php?titulo=Temporadas Altas por Año&ruta=reportes/temporada_alta_x_anio', $parametros_url_anios);
		?>
	</div>
	<br>
	<div>
		<div class="export">
			<input type="button" onclick="tableToExcel('tablePeriodos','Reporte_de_Cantidad_de_Reservas_por_Periodo')" value="Exportar a Excel">
		</div>
		
		<?php
		// Segunda tabla: detalle por períodos si se seleccionó un año
		if (isset($_REQUEST["anio"]) && $_REQUEST["anio"] != ""){
			// Query para contar registros de períodos
			$query_count_periodos = "SELECT COUNT(*) FROM (
									SELECT
										periodo_descripcion,
										periodo_anio,
										periodo_orden,
										COUNT(id_reserva) cantidad_reservas
									FROM reserva
									LEFT JOIN periodo ON rela_periodo = id_periodo
									WHERE periodo_anio = ".$_REQUEST["anio"]."
									GROUP BY periodo_descripcion, periodo_anio, periodo_orden
								) as subquery";
			
			// Query base para obtener registros de períodos
			$query_base_periodos = "SELECT
								periodo_descripcion,
								periodo_anio,
								periodo_orden,
								COUNT(id_reserva) cantidad_reservas
							FROM reserva
							LEFT JOIN periodo ON rela_periodo = id_periodo
							WHERE periodo_anio = ".$_REQUEST["anio"]."
							GROUP BY periodo_descripcion, periodo_anio, periodo_orden
							ORDER BY periodo_anio ASC, periodo_orden ASC";
			
			// Usar una página separada para la segunda tabla
			$pagina_actual_periodos = isset($_REQUEST['pagina_periodos']) ? intval($_REQUEST['pagina_periodos']) : 1;
			
			// Obtener registros paginados de períodos
			$resultado_periodos = obtener_registros_paginados($mysql, $query_base_periodos, $query_count_periodos, $pagina_actual_periodos, $registros_por_pagina);
			$registros_periodos = $resultado_periodos['registros'];
			$paginacion_periodos = $resultado_periodos['paginacion'];
			
			// Mostrar información de paginación para períodos
			echo '<div class="pagination-info">';
			echo mostrar_info_paginacion($paginacion_periodos);
			echo '</div>';
		}
		?>
		
		<table id="tablePeriodos">
			<tr> 
				<td> <font face="Arial"><b>Período</b></font> </td> 
				<td> <font face="Arial"><b>Cantidad de Reservas</b></font> </td> 
			</tr>
			<?php
				if (isset($_REQUEST["anio"]) && $_REQUEST["anio"] != ""){
					if (empty($registros_periodos)) {
						echo "<tr><td colspan='2' style='text-align: center; padding: 20px;'>No se encontraron registros para el año seleccionado</td></tr>";
					} else {
						foreach ($registros_periodos as $row) {
							echo 
							"<tr> 
								<td>".$row["periodo_descripcion"]."</td> 
								<td>".$row["cantidad_reservas"]."</td>
							</tr>";
						}
					}
				} else {
					echo "<tr><td colspan='2' style='text-align: center; padding: 20px;'>Seleccione un año para ver el detalle por períodos</td></tr>";
				}
			?>
		</table>
		
		<?php
		// Generar enlaces de paginación para períodos si hay datos
		if (isset($_REQUEST["anio"]) && $_REQUEST["anio"] != "" && !empty($registros_periodos)):
			$parametros_url_periodos = [];
			$parametros_url_periodos['anio'] = $_REQUEST["anio"];
			if ($registros_por_pagina != 10) {
				$parametros_url_periodos['registros_por_pagina'] = $registros_por_pagina;
			}

			// Modificar la paginación para usar pagina_periodos en lugar de pagina
			$enlaces_periodos = generar_enlaces_paginacion($paginacion_periodos, '/proyecto_cabania/plantilla_modulo.php?titulo=Temporadas Altas por Año&ruta=reportes/temporada_alta_x_anio', $parametros_url_periodos);
			$enlaces_periodos = str_replace('&pagina=', '&pagina_periodos=', $enlaces_periodos);
			echo $enlaces_periodos;
		endif;
		
		$mysql->close();
		?>
	</div>
</div>