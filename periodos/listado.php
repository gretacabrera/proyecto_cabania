<?php
// Configuración de paginación
$registros_por_pagina = isset($_REQUEST['registros_por_pagina']) ? intval($_REQUEST['registros_por_pagina']) : 10;
$pagina_actual = isset($_REQUEST['pagina']) ? intval($_REQUEST['pagina']) : 1;
?>

<h1>Listado de Períodos</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='/proyecto_cabania/plantilla_modulo.php?titulo=Períodos&ruta=periodos&archivo=formulario.php'">Nuevo período</button><br><br>
</div>

<!-- Selector de registros por página -->
<div style="margin-bottom: 10px;">
	<form method="get" style="display: inline;">
		<?php if (isset($_REQUEST["periodo_descripcion"]) && $_REQUEST["periodo_descripcion"] != ""): ?>
			<input type="hidden" name="periodo_descripcion" value="<?php echo htmlspecialchars($_REQUEST["periodo_descripcion"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["periodo_estado"]) && $_REQUEST["periodo_estado"] != ""): ?>
			<input type="hidden" name="periodo_estado" value="<?php echo htmlspecialchars($_REQUEST["periodo_estado"]); ?>">
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
		<th> <font face="Arial">Año</font> </th>
		<th> <font face="Arial">Fecha Inicio</font> </th>
		<th> <font face="Arial">Fecha Fin</font> </th>
		<th> <font face="Arial">Orden</font> </th>
		<th> <font face="Arial">Estado</font> </th> 
		<th> <font face="Arial">Acciones</font> </th> 
	</thead>
<?php
	// Iniciar sesión si no está iniciada
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	$filtro = "";
	
	if (isset($_REQUEST["periodo_descripcion"])){
		if ($_REQUEST["periodo_descripcion"] != ""){
			$filtro .= " and periodo_descripcion LIKE '%".$_REQUEST["periodo_descripcion"]."%' ";
		}
	}
	if (isset($_REQUEST["periodo_anio"])){
		if ($_REQUEST["periodo_anio"] != ""){
			$filtro .= " and periodo_anio = ".$_REQUEST["periodo_anio"]." ";
		}
	}
	if (isset($_REQUEST["periodo_estado"])){
		if ($_REQUEST["periodo_estado"] != ""){
			$filtro .= " and periodo_estado = ".$_REQUEST["periodo_estado"]." ";
		}
	}

	// Aplicar filtro de estado según el tipo de usuario
	if (!es_administrador()) {
		$filtro .= " and periodo_estado = 1 ";
	}

	// Query para contar total de registros
	$query_count = "SELECT COUNT(*) FROM periodo WHERE 1=1 " . $filtro;

	// Query base para obtener registros
	$query_base = "SELECT * FROM periodo WHERE 1=1 " . $filtro . " ORDER BY periodo_anio DESC, periodo_orden ASC";

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
		echo "<tr><td colspan='7' style='text-align: center; padding: 20px;'>No se encontraron registros</td></tr>";
	} else {
		foreach ($registros_data as $row) {
			echo "<tr>";
			echo "<td>".$row["periodo_descripcion"]."</td>";
			echo "<td>".$row["periodo_anio"]."</td>";
			echo "<td>".$row["periodo_fechainicio"]."</td>";
			echo "<td>".$row["periodo_fechafin"]."</td>";
			echo "<td>".$row["periodo_orden"]."</td>";
			echo "<td>".((($row["periodo_estado"]) ? "Activo" : "Baja"))."</td>";

			echo "<td>";
			echo "<button class='abm-button mod-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Períodos&ruta=periodos&archivo=editar.php&id_periodo=".$row["id_periodo"]."\"'>Editar</button>";
			
			// Mostrar botón Eliminar o Recuperar según el estado
			if ($row["periodo_estado"]) {
				if (es_administrador()) {
					echo "<button class='abm-button baja-button' onclick='confirmarAccion(\"/proyecto_cabania/periodos/baja_logica.php?id_periodo=".$row["id_periodo"]."\", \"dar de baja este período\")'>Eliminar</button>";
				}
			} else {
				if (es_administrador()) {
					echo "<button class='abm-button alta-button' onclick='confirmarAccion(\"/proyecto_cabania/periodos/quitar_baja_logica.php?id_periodo=".$row["id_periodo"]."\", \"recuperar este período\")'>Recuperar</button>";
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
if (isset($_REQUEST["periodo_descripcion"]) && $_REQUEST["periodo_descripcion"] != "") {
	$parametros_url['periodo_descripcion'] = $_REQUEST["periodo_descripcion"];
}
if (isset($_REQUEST["periodo_anio"]) && $_REQUEST["periodo_anio"] != "") {
	$parametros_url['periodo_anio'] = $_REQUEST["periodo_anio"];
}
if (isset($_REQUEST["periodo_estado"]) && $_REQUEST["periodo_estado"] != "") {
	$parametros_url['periodo_estado'] = $_REQUEST["periodo_estado"];
}

if ($registros_por_pagina != 10) {
	$parametros_url['registros_por_pagina'] = $registros_por_pagina;
}

echo generar_enlaces_paginacion($paginacion, '/proyecto_cabania/plantilla_modulo.php?titulo=Períodos&ruta=periodos', $parametros_url);

?>
