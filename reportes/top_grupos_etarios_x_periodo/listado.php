
<?php
// Configuración de paginación
$registros_por_pagina = isset($_REQUEST['registros_por_pagina']) ? intval($_REQUEST['registros_por_pagina']) : 10;
$pagina_actual = isset($_REQUEST['pagina']) ? intval($_REQUEST['pagina']) : 1;
?>

<h1>Listado de Frecuencias de Reservas por Grupo Etario</h1>
<?php
	include("busqueda.php");
?>

<!-- Selector de registros por página -->
<div style="margin-bottom: 10px;">
	<form method="get" style="display: inline;">
		<!-- Mantener filtros existentes -->
		<?php if (isset($_REQUEST["id_periodo"]) && $_REQUEST["id_periodo"] != ""): ?>
			<input type="hidden" name="id_periodo" value="<?php echo htmlspecialchars($_REQUEST["id_periodo"]); ?>">
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
	<input type="button" onclick="tableToExcel('tableResultados','Reporte_de_Frecuencias_de_Reservas_por_Grupo_Etario')" value="Exportar a Excel">
</div>

<?php
	$registros_data = [];
	$paginacion = [];
	
	if (isset($_REQUEST["id_periodo"]) && $_REQUEST["id_periodo"] != ""){
		// Query para contar total de registros
		$query_count = "SELECT COUNT(*) FROM (
							SELECT
								CASE 
									WHEN edad < 18 THEN 'menores de 18 años'
									WHEN edad BETWEEN 18 AND 27 THEN 'jovenes (18-27 años)'
									WHEN edad BETWEEN 28 AND 50 THEN 'adultos (28-50 años)'
									WHEN edad BETWEEN 51 AND 70 THEN 'adultos mayores (51-70 años)'
									ELSE 'ancianos (mayores de 70 años)'
								END as grupo_etario,
								SUM(cantidad_reservas) cantidad_reservas
							FROM (
								SELECT 
									TIMESTAMPDIFF(YEAR, persona_fechanac, CURDATE()) AS edad,
									COUNT(id_reserva) as cantidad_reservas
								FROM huesped
								LEFT JOIN persona ON rela_persona = id_persona
								LEFT JOIN huesped_reserva ON rela_huesped = id_huesped
								LEFT JOIN reserva ON rela_reserva = id_reserva
								WHERE rela_periodo = ".$_REQUEST["id_periodo"]."
								GROUP BY edad
							) reservas_x_edad
							GROUP BY grupo_etario
						) as subquery";
		
		// Query base para obtener registros
		$query_base = "SELECT
						CASE 
							WHEN edad < 18 THEN 'menores de 18 años'
							WHEN edad BETWEEN 18 AND 27 THEN 'jovenes (18-27 años)'
							WHEN edad BETWEEN 28 AND 50 THEN 'adultos (28-50 años)'
							WHEN edad BETWEEN 51 AND 70 THEN 'adultos mayores (51-70 años)'
							ELSE 'ancianos (mayores de 70 años)'
						END as grupo_etario,
						SUM(cantidad_reservas) cantidad_reservas
					FROM (
						SELECT 
							TIMESTAMPDIFF(YEAR, persona_fechanac, CURDATE()) AS edad,
							COUNT(id_reserva) as cantidad_reservas
						FROM huesped
						LEFT JOIN persona ON rela_persona = id_persona
						LEFT JOIN huesped_reserva ON rela_huesped = id_huesped
						LEFT JOIN reserva ON rela_reserva = id_reserva
						WHERE rela_periodo = ".$_REQUEST["id_periodo"]."
						GROUP BY edad
					) reservas_x_edad
					GROUP BY grupo_etario
					ORDER BY cantidad_reservas DESC";
		
		// Obtener registros paginados
		$resultado = obtener_registros_paginados($mysql, $query_base, $query_count, $pagina_actual, $registros_por_pagina);
		$registros_data = $resultado['registros'];
		$paginacion = $resultado['paginacion'];
		
		$mysql->close();
	}
?>

<!-- Información de registros -->
<?php if (!empty($registros_data)): ?>
<div class="pagination-info">
	<?php echo mostrar_info_paginacion($paginacion); ?>
</div>
<?php endif; ?>

<table id="tableResultados">
	<tr> 
		<td> <font face="Arial"><b>Grupo Etario</b></font> </td> 
		<td> <font face="Arial"><b>Frecuencias de Reservas</b></font> </td> 
	</tr>
	<?php
		if (isset($_REQUEST["id_periodo"]) && $_REQUEST["id_periodo"] != ""){
			if (empty($registros_data)) {
				echo "<tr><td colspan='2' style='text-align: center; padding: 20px;'>No se encontraron registros</td></tr>";
			} else {
				foreach ($registros_data as $row) {
					echo 
					"<tr> 
						<td>".$row["grupo_etario"]."</td> 
						<td>".$row["cantidad_reservas"]."</td>
					</tr>";
				}
			}
		} else {
			echo "<tr><td colspan='2' style='text-align: center; padding: 20px;'>Seleccione un período para ver los resultados</td></tr>";
		}
	?>
</table>

<?php
// Generar enlaces de paginación solo si hay datos
if (!empty($registros_data)):
	$parametros_url = [];
	if (isset($_REQUEST["id_periodo"]) && $_REQUEST["id_periodo"] != "") {
		$parametros_url['id_periodo'] = $_REQUEST["id_periodo"];
	}
	if ($registros_por_pagina != 10) {
		$parametros_url['registros_por_pagina'] = $registros_por_pagina;
	}

	echo generar_enlaces_paginacion($paginacion, '/proyecto_cabania/plantilla_modulo.php?titulo=Frecuencias de reservas por grupo etario&ruta=reportes/top_grupos_etarios_x_periodo', $parametros_url);
endif;
?>