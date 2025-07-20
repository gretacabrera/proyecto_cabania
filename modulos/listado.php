<?php
// Configuración de paginación
$registros_por_pagina = isset($_REQUEST['registros_por_pagina']) ? intval($_REQUEST['registros_por_pagina']) : 10;
$pagina_actual = isset($_REQUEST['pagina']) ? intval($_REQUEST['pagina']) : 1;
?>

<h1>Listado de Modulos</h1>

<?php include("busqueda.php"); ?>

<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='/proyecto_cabania/plantilla_modulo.php?titulo=Módulos&ruta=modulos&archivo=formulario.php'">Nuevo modulo</button><br><br>
</div>

<!-- Selector de registros por página -->
<div style="margin-bottom: 10px;">
	<form method="get" style="display: inline;">
		<!-- Mantener filtros existentes -->
		<?php if (isset($_REQUEST["modulo_descripcion"]) && $_REQUEST["modulo_descripcion"] != ""): ?>
			<input type="hidden" name="modulo_descripcion" value="<?php echo htmlspecialchars($_REQUEST["modulo_descripcion"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["modulo_estado"]) && $_REQUEST["modulo_estado"] != ""): ?>
			<input type="hidden" name="modulo_estado" value="<?php echo htmlspecialchars($_REQUEST["modulo_estado"]); ?>">
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

if (isset($_REQUEST["modulo_descripcion"])){
	if ($_REQUEST["modulo_descripcion"] != ""){
		$filtro .= " and m.modulo_descripcion LIKE '%".$_REQUEST["modulo_descripcion"]."%' ";
	}
}
if (isset($_REQUEST["modulo_estado"])){
	if ($_REQUEST["modulo_estado"] != ""){
		$filtro .= " and m.modulo_estado = $_REQUEST[modulo_estado] ";
	}
}

// Aplicar filtro de estado según el tipo de usuario
if (!es_administrador()) {
	$filtro .= " and m.modulo_estado = 1 ";
}

// Query para contar total de registros
$query_count = "SELECT COUNT(*) FROM modulo m LEFT JOIN menu men ON m.rela_menu = men.id_menu WHERE 1=1 " . $filtro;

// Query base para obtener registros
$query_base = "SELECT m.*, men.menu_nombre FROM modulo m LEFT JOIN menu men ON m.rela_menu = men.id_menu WHERE 1=1 " . $filtro . " ORDER BY m.modulo_descripcion ASC";

// Obtener registros paginados
$resultado = obtener_registros_paginados($mysql, $query_base, $query_count, $pagina_actual, $registros_por_pagina);
$modulos = $resultado['registros'];
$paginacion = $resultado['paginacion'];
?>

<!-- Información de registros -->
<div class="pagination-info">
	<?php echo mostrar_info_paginacion($paginacion); ?>
</div>

<table> 
	<thead>
		<th> <font face="Arial">Descripcion</font> </th>
		<th> <font face="Arial">Ruta</font> </th>
		<th> <font face="Arial">Menú</font> </th>
		<th> <font face="Arial">Estado</font> </th> 
		<th> <font face="Arial">Acciones</font> </th> 
	</thead>
	<tbody>
	<?php
	if (empty($modulos)) {
		echo "<tr><td colspan='5' style='text-align: center; padding: 20px;'>No se encontraron módulos</td></tr>";
	} else {
		foreach ($modulos as $row) {
		echo 
		"<tr> 
			<td>".$row["modulo_descripcion"]."</td> 
			<td>".$row["modulo_ruta"]."</td> 
			<td>".($row["menu_nombre"] ? $row["menu_nombre"] : " - ")."</td>
			<td>".(($row["modulo_estado"]) ? "Activo" : "Baja")."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Módulos&ruta=modulos&archivo=editar.php&id_modulo=".$row["id_modulo"]."\"'>Editar</button>";
		
		// Mostrar botón Eliminar o Recuperar según el estado
		if ($row["modulo_estado"]) {
			// Si está activo, mostrar botón Eliminar
			echo "<button class='abm-button baja-button' onclick='confirmarAccion(\"/proyecto_cabania/modulos/baja_logica.php?id_modulo=".$row["id_modulo"]."\", \"dar de baja este módulo\")'>Eliminar</button>";
		} else if (es_administrador()) {
			// Si está de baja y es administrador, mostrar botón Recuperar
			echo "<button class='abm-button alta-button' onclick='confirmarAccion(\"/proyecto_cabania/modulos/quitar_baja_logica.php?id_modulo=".$row["id_modulo"]."\", \"recuperar este módulo\")'>Recuperar</button>";
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
if (isset($_REQUEST["modulo_descripcion"]) && $_REQUEST["modulo_descripcion"] != "") {
	$parametros_url['modulo_descripcion'] = $_REQUEST["modulo_descripcion"];
}
if (isset($_REQUEST["modulo_estado"]) && $_REQUEST["modulo_estado"] != "") {
	$parametros_url['modulo_estado'] = $_REQUEST["modulo_estado"];
}
if ($registros_por_pagina != 10) {
	$parametros_url['registros_por_pagina'] = $registros_por_pagina;
}

echo generar_enlaces_paginacion($paginacion, '/proyecto_cabania/plantilla_modulo.php?titulo=Módulos&ruta=modulos', $parametros_url);
?>