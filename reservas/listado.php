<?php
// Configuración de paginación
$registros_por_pagina = isset($_REQUEST['registros_por_pagina']) ? intval($_REQUEST['registros_por_pagina']) : 10;
$pagina_actual = isset($_REQUEST['pagina']) ? intval($_REQUEST['pagina']) : 1;
?>

<h1>Listado de Reservas</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='formulario_reserva.php'">Nueva reserva</button><br><br>
</div>

<!-- Selector de registros por página -->
<div style="margin-bottom: 10px;">
	<form method="get" style="display: inline;">
		<!-- Mantener filtros existentes -->
		<?php if (isset($_REQUEST["reserva_fhinicio"]) && $_REQUEST["reserva_fhinicio"] != ""): ?>
			<input type="hidden" name="reserva_fhinicio" value="<?php echo htmlspecialchars($_REQUEST["reserva_fhinicio"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["reserva_fhfin"]) && $_REQUEST["reserva_fhfin"] != ""): ?>
			<input type="hidden" name="reserva_fhfin" value="<?php echo htmlspecialchars($_REQUEST["reserva_fhfin"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["rela_cabania"]) && $_REQUEST["rela_cabania"] != ""): ?>
			<input type="hidden" name="rela_cabania" value="<?php echo htmlspecialchars($_REQUEST["rela_cabania"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["rela_periodo"]) && $_REQUEST["rela_periodo"] != ""): ?>
			<input type="hidden" name="rela_periodo" value="<?php echo htmlspecialchars($_REQUEST["rela_periodo"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["rela_estadoreserva"]) && $_REQUEST["rela_estadoreserva"] != ""): ?>
			<input type="hidden" name="rela_estadoreserva" value="<?php echo htmlspecialchars($_REQUEST["rela_estadoreserva"]); ?>">
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

$reserva_fhinicio = "2000-01-01T00:00";
$reserva_fhfin = "2030-12-31T00:00";

if (isset($_REQUEST["reserva_fhinicio"])){
	if ($_REQUEST["reserva_fhinicio"] != ""){
		$reserva_fhinicio = date_format(date_create($_REQUEST["reserva_fhinicio"]), 'Y-m-d H:i');
	}
}
if (isset($_REQUEST["reserva_fhfin"])){
	if ($_REQUEST["reserva_fhfin"] != ""){
		$reserva_fhfin = date_format(date_create($_REQUEST["reserva_fhfin"]), 'Y-m-d H:i');
	}
}

$filtro = " DATE_FORMAT(reserva_fhinicio, '%Y-%m-%d %H:%i') BETWEEN '".$reserva_fhinicio."' AND '".$reserva_fhfin."' ";

if (isset($_REQUEST["rela_cabania"])){
	if ($_REQUEST["rela_cabania"] != ""){
		$filtro .= " and rela_cabania = ".$_REQUEST["rela_cabania"]." ";
	}
}
if (isset($_REQUEST["rela_periodo"])){
	if ($_REQUEST["rela_periodo"] != ""){
		$filtro .= " and rela_periodo = ".$_REQUEST["rela_periodo"]." ";
	}
}
if (isset($_REQUEST["rela_estadoreserva"])){
	if ($_REQUEST["rela_estadoreserva"] != ""){
		$filtro .= " and rela_estadoreserva = ".$_REQUEST["rela_estadoreserva"]." ";
	}
}

// Aplicar filtro de estado según el tipo de usuario
if (!es_administrador()) {
	$filtro .= " and rela_estadoreserva != 6 ";
}

$where_clause = "where " . $filtro;

// Query para contar total de registros
$query_count = "SELECT COUNT(*) FROM reserva
				LEFT JOIN cabania ON rela_cabania = id_cabania
				LEFT JOIN estadoreserva ON rela_estadoreserva = id_estadoreserva
				LEFT JOIN periodo ON rela_periodo = id_periodo 
				" . $where_clause;

// Query base para obtener registros
$query_base = "SELECT * FROM reserva
				LEFT JOIN cabania ON rela_cabania = id_cabania
				LEFT JOIN estadoreserva ON rela_estadoreserva = id_estadoreserva
				LEFT JOIN periodo ON rela_periodo = id_periodo 
				" . $where_clause . "
				ORDER BY reserva_fhinicio ASC";

// Obtener registros paginados
$resultado = obtener_registros_paginados($mysql, $query_base, $query_count, $pagina_actual, $registros_por_pagina);
$reservas = $resultado['registros'];
$paginacion = $resultado['paginacion'];

$mysql->close();
?>

<!-- Información de registros -->
<div class="pagination-info">
	<?php echo mostrar_info_paginacion($paginacion); ?>
</div>

<table> 
	<thead> 
		<th> <font face="Arial">Fecha y hora de Inicio</font> </th> 
		<th> <font face="Arial">Fecha y hora de Fin</font> </th> 
		<th> <font face="Arial">Cabaña</font> </th> 
		<th> <font face="Arial">Período</font> </th>
		<th> <font face="Arial">Estado</font> </th> 
		<th> <font face="Arial">Acciones</font> </th> 
	</thead>
	<tbody>
	<?php
	if (empty($reservas)) {
		echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>No se encontraron reservas</td></tr>";
	} else {
		foreach ($reservas as $row) {
			echo 
			"<tr> 
				<td>".$row["reserva_fhinicio"]."</td> 
				<td>".$row["reserva_fhfin"]."</td>  
				<td>".$row["cabania_nombre"]."</td> 
				<td>".$row["periodo_descripcion"]."</td>
				<td>".$row["estadoreserva_descripcion"]."</td> 
				<td>
					<button class='abm-button mod-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Reservas&ruta=reservas&archivo=editar.php&id_reserva=".$row["id_reserva"]."\"'>Editar</button>";
			
			// Mostrar botón Anular o Reactivar según el estado
			if ($row["rela_estadoreserva"] == 6) {
				// Si está anulada (estado 6) y es administrador, mostrar botón Reactivar
				if (es_administrador()) {
					echo "<button class='abm-button alta-button' onclick='confirmarAccion(\"/proyecto_cabania/reservas/quitar_baja_logica.php?id_reserva=".$row["id_reserva"]."\", \"reactivar esta reserva\")'>Reactivar</button>";
				}
			} else {
				// Si está activa, mostrar botón Anular
				echo "<button class='abm-button baja-button' onclick='confirmarAccion(\"/proyecto_cabania/reservas/baja_logica.php?id_reserva=".$row["id_reserva"]."\", \"anular esta reserva\")'>Anular</button>";
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
if (isset($_REQUEST["reserva_fhinicio"]) && $_REQUEST["reserva_fhinicio"] != "") {
	$parametros_url['reserva_fhinicio'] = $_REQUEST["reserva_fhinicio"];
}
if (isset($_REQUEST["reserva_fhfin"]) && $_REQUEST["reserva_fhfin"] != "") {
	$parametros_url['reserva_fhfin'] = $_REQUEST["reserva_fhfin"];
}
if (isset($_REQUEST["rela_cabania"]) && $_REQUEST["rela_cabania"] != "") {
	$parametros_url['rela_cabania'] = $_REQUEST["rela_cabania"];
}
if (isset($_REQUEST["rela_periodo"]) && $_REQUEST["rela_periodo"] != "") {
	$parametros_url['rela_periodo'] = $_REQUEST["rela_periodo"];
}
if (isset($_REQUEST["rela_estadoreserva"]) && $_REQUEST["rela_estadoreserva"] != "") {
	$parametros_url['rela_estadoreserva'] = $_REQUEST["rela_estadoreserva"];
}
if ($registros_por_pagina != 10) {
	$parametros_url['registros_por_pagina'] = $registros_por_pagina;
}

echo generar_enlaces_paginacion($paginacion, '/proyecto_cabania/plantilla_modulo.php?titulo=Reservas&ruta=reservas', $parametros_url);
?>