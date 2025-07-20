<?php
// Configuración de paginación
$registros_por_pagina = isset($_REQUEST['registros_por_pagina']) ? intval($_REQUEST['registros_por_pagina']) : 10;
$pagina_actual = isset($_REQUEST['pagina']) ? intval($_REQUEST['pagina']) : 1;
?>

<h1>Listado de Productos por Categoría</h1>
<?php
	include("busqueda.php");
?>

<!-- Selector de registros por página -->
<div style="margin-bottom: 10px;">
	<form method="get" style="display: inline;">
		<!-- Mantener filtros existentes -->
		<?php if (isset($_REQUEST["producto_nombre"]) && $_REQUEST["producto_nombre"] != ""): ?>
			<input type="hidden" name="producto_nombre" value="<?php echo htmlspecialchars($_REQUEST["producto_nombre"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["rela_marca"]) && $_REQUEST["rela_marca"] != ""): ?>
			<input type="hidden" name="rela_marca" value="<?php echo htmlspecialchars($_REQUEST["rela_marca"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["rela_estadoproducto"]) && $_REQUEST["rela_estadoproducto"] != ""): ?>
			<input type="hidden" name="rela_estadoproducto" value="<?php echo htmlspecialchars($_REQUEST["rela_estadoproducto"]); ?>">
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
	$filtro = '';
	
	if (isset($_REQUEST["producto_nombre"])){
		if ($_REQUEST["producto_nombre"] != ""){
			$filtro .= " and producto_nombre LIKE '%".$_REQUEST["producto_nombre"]."%' ";
		}
	}
	if (isset($_REQUEST["rela_marca"])){
		if ($_REQUEST["rela_marca"] != ""){
			$filtro .= " and rela_marca = ".$_REQUEST["rela_marca"]." ";
		}
	}
	if (isset($_REQUEST["rela_estadoproducto"])){
		if ($_REQUEST["rela_estadoproducto"] != ""){
			$filtro .= " and rela_estadoproducto = ".$_REQUEST["rela_estadoproducto"]." ";
		}
	}
	
	// Query para contar total de registros
	$query_count = "SELECT COUNT(*) FROM (
					SELECT 
						categoria_descripcion as categoria,
						COUNT(id_producto) as cantidad
					FROM producto
					LEFT JOIN marca ON rela_marca = id_marca
					LEFT JOIN categoria ON rela_categoria = id_categoria
					LEFT JOIN estadoproducto ON rela_estadoproducto = id_estadoproducto
					WHERE 1 = 1 " . $filtro . "
					GROUP BY categoria_descripcion
				) as subquery";
	
	// Query base para obtener registros
	$query_base = "SELECT 
					categoria_descripcion as categoria,
					COUNT(id_producto) as cantidad
				FROM producto
				LEFT JOIN marca ON rela_marca = id_marca
				LEFT JOIN categoria ON rela_categoria = id_categoria
				LEFT JOIN estadoproducto ON rela_estadoproducto = id_estadoproducto
				WHERE 1 = 1 " . $filtro . "
				GROUP BY categoria_descripcion
				ORDER BY categoria_descripcion";
	
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
		<td> <font face="Arial"><b>Categoría</b></font> </td> 
		<td> <font face="Arial"><b>Cantidad</b></font> </td> 
	</tr>
	
<?php
	if (empty($registros_data)) {
		echo "<tr><td colspan='2' style='text-align: center; padding: 20px;'>No se encontraron registros</td></tr>";
	} else {
		foreach ($registros_data as $row) {
			echo 
			"<tr> 
				<td>".$row["categoria"]."</td> 
				<td>".$row["cantidad"]."</td>  
			</tr>";
		}
	}
?>
</table>

<?php
// Generar enlaces de paginación
$parametros_url = [];
if (isset($_REQUEST["producto_nombre"]) && $_REQUEST["producto_nombre"] != "") {
	$parametros_url['producto_nombre'] = $_REQUEST["producto_nombre"];
}
if (isset($_REQUEST["rela_marca"]) && $_REQUEST["rela_marca"] != "") {
	$parametros_url['rela_marca'] = $_REQUEST["rela_marca"];
}
if (isset($_REQUEST["rela_estadoproducto"]) && $_REQUEST["rela_estadoproducto"] != "") {
	$parametros_url['rela_estadoproducto'] = $_REQUEST["rela_estadoproducto"];
}
if ($registros_por_pagina != 10) {
	$parametros_url['registros_por_pagina'] = $registros_por_pagina;
}

echo generar_enlaces_paginacion($paginacion, '/proyecto_cabania/plantilla_modulo.php?titulo=Productos por categoría&ruta=reportes/productos_cantidadxcategoria', $parametros_url);
?>