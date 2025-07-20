<?php
// Configuración de paginación
$registros_por_pagina = isset($_REQUEST['registros_por_pagina']) ? intval($_REQUEST['registros_por_pagina']) : 10;
$pagina_actual = isset($_REQUEST['pagina']) ? intval($_REQUEST['pagina']) : 1;
?>

<h1>Listado de Estados de Productos</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='/proyecto_cabania/plantilla_modulo.php?titulo=Estados de Productos&ruta=estados_productos&archivo=formulario.php'">Nuevo estado de producto</button><br><br>
</div>

<!-- Selector de registros por página -->
<div style="margin-bottom: 10px;">
	<form method="get" style="display: inline;">
		<?php if (isset($_REQUEST["estadoproducto_descripcion"]) && $_REQUEST["estadoproducto_descripcion"] != ""): ?>
			<input type="hidden" name="estadoproducto_descripcion" value="<?php echo htmlspecialchars($_REQUEST["estadoproducto_descripcion"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["estadoproducto_estado"]) && $_REQUEST["estadoproducto_estado"] != ""): ?>
			<input type="hidden" name="estadoproducto_estado" value="<?php echo htmlspecialchars($_REQUEST["estadoproducto_estado"]); ?>">
		<?php endif; ?>

		<label for="registros_por_pagina">Mostrar:</label>
		<select name="registros_por_pagina" id="registros_por_pagina" onchange="this.form.submit()">
			<option value="10" <?php echo $registros_por_pagina == 10 ? 'selected' : ''; ?>>10 registros</option>
			<option value="25" <?php echo $registros_por_pagina == 25 ? 'selected' : ''; ?>>25 registros</option>
			<option value="50" <?php echo $registros_por_pagina == 50 ? 'selected' : ''; ?>>50 registros</option>
		</select>
	</form>
</div>

<table> 
	<thead>
		<th> <font face="Arial">Descripcion</font> </th>
		<th> <font face="Arial">Estado</font> </th> 
		<th> <font face="Arial">Acciones</font> </th> 
	</thead>
<?php
	// Iniciar sesión si no está iniciada
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	$filtro = "";
	
	if (isset($_REQUEST["estadoproducto_descripcion"])){
		if ($_REQUEST["estadoproducto_descripcion"] != ""){
			$filtro .= " and estadoproducto_descripcion LIKE '%".$_REQUEST["estadoproducto_descripcion"]."%' ";
		}
	}
	if (isset($_REQUEST["estadoproducto_estado"])){
		if ($_REQUEST["estadoproducto_estado"] != ""){
			$filtro .= " and estadoproducto_estado = ".$_REQUEST["estadoproducto_estado"]." ";
		}
	}

	// Aplicar filtro de estado según el tipo de usuario
	$where_clause = "where 1=1";
	if (!es_administrador()) {
		$filtro .= " and estadoproducto_estado = 1 ";
	}

	// Query para contar total de registros
	$query_count = "SELECT COUNT(*) FROM estadoproducto " . $where_clause . " " . $filtro;

	// Query base para obtener registros
	$query_base = "SELECT * FROM estadoproducto " . $where_clause . " " . $filtro . " ORDER BY estadoproducto_descripcion ASC";

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

	<tbody>
	<?php
	if (empty($registros_data)) {
		echo "<tr><td colspan='3' style='text-align: center; padding: 20px;'>No se encontraron registros</td></tr>";
	} else {
		foreach ($registros_data as $row) {
			echo "<tr>";
			echo "<td>".$row["estadoproducto_descripcion"]."</td>";
			echo "<td>".((($row["estadoproducto_estado"]) ? "Activo" : "Baja"))."</td>";

			echo "<td>";
			echo "<button class='abm-button mod-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Estados de Productos&ruta=estados_productos&archivo=editar.php&id_estadoproducto=".$row["id_estadoproducto"]."\"'>Editar</button>";
			
			// Mostrar botón Eliminar o Recuperar según el estado
			if ($row["estadoproducto_estado"]) {
				if (es_administrador()) {
					echo "<button class='abm-button baja-button' onclick='confirmarAccion(\"/proyecto_cabania/estados_productos/baja_logica.php?id_estadoproducto=".$row["id_estadoproducto"]."\", \"dar de baja este estado de producto\")'>Eliminar</button>";
				}
			} else {
				if (es_administrador()) {
					echo "<button class='abm-button alta-button' onclick='confirmarAccion(\"/proyecto_cabania/estados_productos/quitar_baja_logica.php?id_estadoproducto=".$row["id_estadoproducto"]."\", \"recuperar este estado de producto\")'>Recuperar</button>";
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
if (isset($_REQUEST["estadoproducto_descripcion"]) && $_REQUEST["estadoproducto_descripcion"] != "") {
	$parametros_url['estadoproducto_descripcion'] = $_REQUEST["estadoproducto_descripcion"];
}
if (isset($_REQUEST["estadoproducto_estado"]) && $_REQUEST["estadoproducto_estado"] != "") {
	$parametros_url['estadoproducto_estado'] = $_REQUEST["estadoproducto_estado"];
}

if ($registros_por_pagina != 10) {
	$parametros_url['registros_por_pagina'] = $registros_por_pagina;
}

echo generar_enlaces_paginacion($paginacion, '/proyecto_cabania/plantilla_modulo.php?titulo=Estados de Productos&ruta=estados_productos', $parametros_url);
?>