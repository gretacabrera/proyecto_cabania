<?php
// Configuración de paginación
$registros_por_pagina = isset($_REQUEST['registros_por_pagina']) ? intval($_REQUEST['registros_por_pagina']) : 10;
$pagina_actual = isset($_REQUEST['pagina']) ? intval($_REQUEST['pagina']) : 1;
?>

<h1>Listado de Productos</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='/proyecto_cabania/plantilla_modulo.php?titulo=Productos&ruta=productos&archivo=formulario.php'">Nuevo producto</button><br><br>
</div>

<!-- Selector de registros por página -->
<div style="margin-bottom: 10px;">
	<form method="get" style="display: inline;">
		<!-- Mantener filtros existentes -->
		<?php if (isset($_REQUEST["producto_nombre"]) && $_REQUEST["producto_nombre"] != ""): ?>
			<input type="hidden" name="producto_nombre" value="<?php echo htmlspecialchars($_REQUEST["producto_nombre"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["rela_categoria"]) && $_REQUEST["rela_categoria"] != ""): ?>
			<input type="hidden" name="rela_categoria" value="<?php echo htmlspecialchars($_REQUEST["rela_categoria"]); ?>">
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

<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

$filtro = "";

if (isset($_REQUEST["producto_nombre"])){
	if ($_REQUEST["producto_nombre"] != ""){
		$filtro .= " and producto_nombre LIKE '%".$_REQUEST["producto_nombre"]."%' ";
	}
}
if (isset($_REQUEST["rela_categoria"])){
	if ($_REQUEST["rela_categoria"] != ""){
		$filtro .= " and rela_categoria = ".$_REQUEST["rela_categoria"]." ";
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

// Aplicar filtro de estado según el tipo de usuario
$where_clause = "where 1=1";
if (!es_administrador()) {
	$filtro .= " and rela_estadoproducto != 4 ";
}

// Query para contar total de registros
$query_count = "SELECT COUNT(*) FROM producto 
				LEFT JOIN marca ON rela_marca = id_marca
				LEFT JOIN categoria ON rela_categoria = id_categoria
				LEFT JOIN estadoproducto ON rela_estadoproducto = id_estadoproducto
				" . $where_clause . " " . $filtro;

// Query base para obtener registros
$query_base = "SELECT * FROM producto 
				LEFT JOIN marca ON rela_marca = id_marca
				LEFT JOIN categoria ON rela_categoria = id_categoria
				LEFT JOIN estadoproducto ON rela_estadoproducto = id_estadoproducto
				" . $where_clause . " " . $filtro . "
				ORDER BY producto_nombre ASC";

// Obtener registros paginados
$resultado = obtener_registros_paginados($mysql, $query_base, $query_count, $pagina_actual, $registros_por_pagina);
$productos = $resultado['registros'];
$paginacion = $resultado['paginacion'];

$mysql->close();
?>

<!-- Información de registros -->
<div class="pagination-info">
	<?php echo mostrar_info_paginacion($paginacion); ?>
</div>

<table> 
	<thead>
		<th> <font face="Arial">Nombre</font> </th> 
		<th> <font face="Arial">Descripcion</font> </th> 
		<th> <font face="Arial">Categoría</font> </th> 
		<th> <font face="Arial">Marca</font> </th> 
		<th> <font face="Arial">Precio Unitario</font> </th> 
		<th> <font face="Arial">Stock</font> </th>
		<th> <font face="Arial">Foto</font> </th>  
		<th> <font face="Arial">Estado</font> </th> 
		<th> <font face="Arial">Acciones</font> </th> 
	</thead>
	<tbody>
	<?php
	if (empty($productos)) {
		echo "<tr><td colspan='9' style='text-align: center; padding: 20px;'>No se encontraron productos</td></tr>";
	} else {
		foreach ($productos as $row) {
			echo 
			"<tr> 
				<td>".$row["producto_nombre"]."</td> 
				<td>".$row["producto_descripcion"]."</td>  
				<td>".$row["categoria_descripcion"]."</td> 
				<td>".$row["marca_descripcion"]."</td> 
				<td>$".$row["producto_precio"]."</td>
				<td>".$row["producto_stock"]."</td>
				<td>".($row["producto_foto"] ? "<img src='imagenes/productos/".$row["producto_foto"]."' width='50' height='50'>" : "Sin foto")."</td> 
				<td>".$row["estadoproducto_descripcion"]."</td> 
				<td>
					<button class='abm-button mod-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Productos&ruta=productos&archivo=editar.php&id_producto=".$row["id_producto"]."\"'>Editar</button>";
			
			// Mostrar botón Eliminar o Recuperar según el estado
			if ($row["rela_estadoproducto"] == 4) {
				// Si está de baja (estado 4) y es administrador, mostrar botón Recuperar
				if (es_administrador()) {
					echo "<button class='abm-button alta-button' onclick='confirmarAccion(\"/proyecto_cabania/productos/quitar_baja_logica.php?id_producto=".$row["id_producto"]."\", \"recuperar este producto\")'>Recuperar</button>";
				}
			} else {
				// Si está activo, mostrar botón Eliminar
				echo "<button class='abm-button baja-button' onclick='confirmarAccion(\"/proyecto_cabania/productos/baja_logica.php?id_producto=".$row["id_producto"]."\", \"dar de baja este producto\")'>Eliminar</button>";
			}
			
			echo "</td>
			</tr>";
		}
	}
	?>
	</tbody>
</table>

<?php
// Generar enlaces de paginación
$parametros_url = [];
if (isset($_REQUEST["producto_nombre"]) && $_REQUEST["producto_nombre"] != "") {
	$parametros_url['producto_nombre'] = $_REQUEST["producto_nombre"];
}
if (isset($_REQUEST["rela_categoria"]) && $_REQUEST["rela_categoria"] != "") {
	$parametros_url['rela_categoria'] = $_REQUEST["rela_categoria"];
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

echo generar_enlaces_paginacion($paginacion, '/proyecto_cabania/plantilla_modulo.php?titulo=Productos&ruta=productos', $parametros_url);
?>