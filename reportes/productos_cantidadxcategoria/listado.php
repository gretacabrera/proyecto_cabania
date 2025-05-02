
<h1>Listado de Productos por Categoría</h1>

<div class="export">
	<input type="button" onclick="tableToExcel('tableResultados','Reporte_de_Productos_por_Categoria')" value="Exportar a Excel">
</div>
<table id="tableResultados"> 
	<tr> 
		<td> <font face="Arial"><b>Categoría</b></font> </td> 
		<td> <font face="Arial"><b>Cantidad</b></font> </td> 
	</tr>
	
<?php
	require("../../conexion.php");
	
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
	
	$consulta_sql = "select 
				categoria_descripcion as categoria,
				count(id_producto) as cantidad
				from producto
				left join marca on rela_marca = id_marca
				left join categoria on rela_categoria = id_categoria
				left join estadoproducto on rela_estadoproducto = id_estadoproducto
				where 1 = 1
				".$filtro."
				group by categoria_descripcion";
				
	$registros = $mysql->query($consulta_sql) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["categoria"]."</td> 
			<td>".$row["cantidad"]."</td>  
		</tr>";
	}
?>
<table>