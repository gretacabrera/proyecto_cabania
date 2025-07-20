
<?php
// Configuración de paginación
$registros_por_pagina = isset($_REQUEST['registros_por_pagina']) ? intval($_REQUEST['registros_por_pagina']) : 10;
$pagina_actual = isset($_REQUEST['pagina']) ? intval($_REQUEST['pagina']) : 1;
?>

<h1>Listado de Consumos en Pesos por Cabaña</h1>
<?php
	include("busqueda.php");
?>

<!-- Selector de registros por página -->
<div style="margin-bottom: 10px;">
	<form method="get" style="display: inline;">
		<!-- Mantener filtros existentes -->
		<?php if (isset($_REQUEST["rela_cabania"]) && $_REQUEST["rela_cabania"] != ""): ?>
			<input type="hidden" name="rela_cabania" value="<?php echo htmlspecialchars($_REQUEST["rela_cabania"]); ?>">
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
	<input type="button" onclick="tableToExcel('tableResultados','Reporte_de_Consumos_Importes_Por_Cabania')" value="Exportar a Excel">
</div>

<?php
	$filtro = '';
	
	if (isset($_REQUEST["rela_cabania"])){
		if ($_REQUEST["rela_cabania"] != ""){
			$filtro .= " and id_cabania = ".$_REQUEST["rela_cabania"]." ";
		}
	}
	
	// Query para contar total de registros
	$query_count = "SELECT COUNT(*) 
					FROM (
						SELECT cabania_codigo, cabania_nombre
						FROM cabania
						LEFT JOIN reserva ON rela_cabania = id_cabania
						LEFT JOIN consumo ON rela_reserva = id_reserva
						WHERE 1 = 1 " . $filtro . "
						GROUP BY cabania_codigo, cabania_nombre
					) as subquery";
	
	// Query base para obtener registros
	$query_base = "SELECT 
					CONCAT(cabania_codigo,' - ',cabania_nombre) as cabania,
					SUM(consumo_total) as consumo
					FROM cabania
					LEFT JOIN reserva ON rela_cabania = id_cabania
					LEFT JOIN consumo ON rela_reserva = id_reserva
					WHERE 1 = 1 " . $filtro . "
					GROUP BY cabania_codigo, cabania_nombre
					ORDER BY cabania_codigo";
	
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
		<td> <font face="Arial"><b>Cabaña</b></font> </td> 
		<td> <font face="Arial"><b>Consumo en Pesos</b></font> </td> 
	</tr>
	
<?php
	if (empty($registros_data)) {
		echo "<tr><td colspan='2' style='text-align: center; padding: 20px;'>No se encontraron registros</td></tr>";
	} else {
		foreach ($registros_data as $row) {
			echo 
			"<tr> 
				<td>".$row["cabania"]."</td> 
				<td> $".round($row["consumo"],2)."</td>  
			</tr>";
		}
	}
?>
</table>

<?php
// Generar enlaces de paginación
$parametros_url = [];
if (isset($_REQUEST["rela_cabania"]) && $_REQUEST["rela_cabania"] != "") {
	$parametros_url['rela_cabania'] = $_REQUEST["rela_cabania"];
}
if ($registros_por_pagina != 10) {
	$parametros_url['registros_por_pagina'] = $registros_por_pagina;
}

echo generar_enlaces_paginacion($paginacion, '/proyecto_cabania/plantilla_modulo.php?titulo=Consumos importes por cabaña&ruta=reportes/consumos_importexcabania', $parametros_url);
?>