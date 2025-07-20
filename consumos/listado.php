<?php
// Configuración de paginación
$registros_por_pagina = isset($_REQUEST['registros_por_pagina']) ? intval($_REQUEST['registros_por_pagina']) : 10;
$pagina_actual = isset($_REQUEST['pagina']) ? intval($_REQUEST['pagina']) : 1;
?>

<h1>Listado de Consumos</h1>

<!-- Selector de registros por página -->
<div style="margin-bottom: 10px;">
	<form method="get" style="display: inline;">
		<label for="registros_por_pagina">Mostrar:</label>
		<select name="registros_por_pagina" id="registros_por_pagina" onchange="this.form.submit()">
			<option value="10" <?php echo $registros_por_pagina == 10 ? 'selected' : ''; ?>>10 registros</option>
			<option value="25" <?php echo $registros_por_pagina == 25 ? 'selected' : ''; ?>>25 registros</option>
			<option value="50" <?php echo $registros_por_pagina == 50 ? 'selected' : ''; ?>>50 registros</option>
		</select>
	</form>
</div>

<div class="botonera-abm">
	<button onclick="location.href='/proyecto_cabania/plantilla_modulo.php?titulo=Consumos&ruta=consumos&archivo=formulario.php'">Nuevo producto</button>
</div>

<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

$filtro = "";

// Aplicar filtro de estado según el tipo de usuario
$where_clause = "
	FROM consumo c
	INNER JOIN producto p ON c.id_producto = p.id_producto
	INNER JOIN categoria cat ON p.id_categoria = cat.id_categoria
	INNER JOIN marca m ON p.id_marca = m.id_marca
	INNER JOIN estadoproducto ep ON p.id_estadoproducto = ep.id_estadoproducto
	WHERE 1=1
";

if (!es_administrador()) {
	$filtro .= " AND p.producto_estado = 1 ";
}

// Query para contar total de registros
$query_count = "SELECT COUNT(*) " . $where_clause . " " . $filtro;

// Query base para obtener registros
$query_base = "
	SELECT c.*, p.producto_nombre, p.producto_descripcion, p.producto_precio_unitario, p.producto_stock,
		   cat.categoria_descripcion, m.marca_descripcion, ep.estadoproducto_descripcion
	" . $where_clause . " " . $filtro . " 
	ORDER BY p.producto_nombre ASC
";

// Obtener registros paginados
$resultado = obtener_registros_paginados($mysql, $query_base, $query_count, $pagina_actual, $registros_por_pagina);
$registros = $resultado['registros'];
$paginacion = $resultado['paginacion'];

$mysql->close();
?>

<!-- Información de registros -->
<div class="pagination-info">
	<?php echo mostrar_info_paginacion($paginacion); ?>
</div>

<table> 
	<thead>
		<tr> 
			<th> <font face="Arial">Nombre</font> </th> 
			<th> <font face="Arial">Descripción</font> </th> 
			<th> <font face="Arial">Categoría</font> </th> 
			<th> <font face="Arial">Marca</font> </th> 
			<th> <font face="Arial">Precio Unitario</font> </th> 
			<th> <font face="Arial">Stock</font> </th>  
			<th> <font face="Arial">Estado</font> </th> 
			<th> <font face="Arial">Acciones</font> </th> 
		</tr>
	</thead>
	<tbody>
	<?php
	if (empty($registros)) {
		echo "<tr><td colspan='8' style='text-align: center; padding: 20px;'>No se encontraron registros</td></tr>";
	} else {
		foreach ($registros as $row) {
			echo "<tr>";
			echo "<td>".$row["producto_nombre"]."</td>";
			echo "<td>".$row["producto_descripcion"]."</td>";
			echo "<td>".$row["categoria_descripcion"]."</td>";
			echo "<td>".$row["marca_descripcion"]."</td>";
			echo "<td>".$row["producto_precio_unitario"]."</td>";
			echo "<td>".$row["producto_stock"]."</td>";
			echo "<td>".$row["estadoproducto_descripcion"]."</td>";
			echo "<td>";
			echo "<button class='abm-button mod-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Consumos&ruta=consumos&archivo=editar.php&id_consumo=".$row["id_consumo"]."\"'>Editar</button>";
			
			// Botones según estado del producto
			if ($row["producto_estado"]) {
				if (es_administrador()) {
					echo "<button class='abm-button baja-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Consumos&ruta=consumos&archivo=baja_logica.php&id_consumo=".$row["id_consumo"]."\"'>Eliminar</button>";
				}
			} else {
				if (es_administrador()) {
					echo "<button class='abm-button alta-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Consumos&ruta=consumos&archivo=quitar_baja_logica.php&id_consumo=".$row["id_consumo"]."\"'>Recuperar</button>";
				}
			}
			echo "</td>";
			echo "</tr>";
		}
	}
	?>
	</tbody>
</table>

<?php
// Generar enlaces de paginación
$parametros_url = [];

if ($registros_por_pagina != 10) {
	$parametros_url['registros_por_pagina'] = $registros_por_pagina;
}

echo generar_enlaces_paginacion($paginacion, '/proyecto_cabania/plantilla_modulo.php?titulo=Consumos&ruta=consumos', $parametros_url);
?>