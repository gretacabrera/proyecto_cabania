<?php
// Configuración de paginación
$registros_por_pagina = isset($_REQUEST['registros_por_pagina']) ? intval($_REQUEST['registros_por_pagina']) : 10;
$pagina_actual = isset($_REQUEST['pagina']) ? intval($_REQUEST['pagina']) : 1;
?>

<h1>Listado de cabañas</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='/proyecto_cabania/plantilla_modulo.php?titulo=Cabañas&ruta=cabanias&archivo=formulario.php'">Nueva cabaña</button><br><br>
</div>

<!-- Selector de registros por página -->
<div style="margin-bottom: 10px;">
	<form method="get" style="display: inline;">
		<!-- Mantener filtros existentes -->
		<?php if (isset($_REQUEST["cabania_codigo"]) && $_REQUEST["cabania_codigo"] != ""): ?>
			<input type="hidden" name="cabania_codigo" value="<?php echo htmlspecialchars($_REQUEST["cabania_codigo"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["cabania_nombre"]) && $_REQUEST["cabania_nombre"] != ""): ?>
			<input type="hidden" name="cabania_nombre" value="<?php echo htmlspecialchars($_REQUEST["cabania_nombre"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["cabania_ubicacion"]) && $_REQUEST["cabania_ubicacion"] != ""): ?>
			<input type="hidden" name="cabania_ubicacion" value="<?php echo htmlspecialchars($_REQUEST["cabania_ubicacion"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["cabania_capacidad"]) && $_REQUEST["cabania_capacidad"] != ""): ?>
			<input type="hidden" name="cabania_capacidad" value="<?php echo htmlspecialchars($_REQUEST["cabania_capacidad"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["cabania_estado"]) && $_REQUEST["cabania_estado"] != ""): ?>
			<input type="hidden" name="cabania_estado" value="<?php echo htmlspecialchars($_REQUEST["cabania_estado"]); ?>">
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

if (isset($_REQUEST["cabania_codigo"])){
	if ($_REQUEST["cabania_codigo"] != ""){
		$filtro .= " and cabania_codigo LIKE '%".$_REQUEST["cabania_codigo"]."%' ";
	}
}
if (isset($_REQUEST["cabania_nombre"])){
	if ($_REQUEST["cabania_nombre"] != ""){
		$filtro .= " and cabania_nombre LIKE '%".$_REQUEST["cabania_nombre"]."%' ";
	}
}
if (isset($_REQUEST["cabania_ubicacion"])){
	if ($_REQUEST["cabania_ubicacion"] != ""){
		$filtro .= " and cabania_ubicacion LIKE '%".$_REQUEST["cabania_ubicacion"]."%' ";
	}
}
if (isset($_REQUEST["cabania_capacidad"])){
	if ($_REQUEST["cabania_capacidad"] != ""){
		$filtro .= " and cabania_capacidad = ".$_REQUEST["cabania_capacidad"]." ";
	}
}
if (isset($_REQUEST["cabania_estado"])){
	if ($_REQUEST["cabania_estado"] != ""){
		$filtro .= " and cabania_estado = ".$_REQUEST["cabania_estado"]." ";
	}
}

// Aplicar filtro de estado según el tipo de usuario
$where_clause = "where 1=1";
if (!es_administrador()) {
	$where_clause .= " and cabania_estado = 1 ";
}

// Query para contar total de registros
$query_count = "SELECT COUNT(*) FROM cabania " . $where_clause . " " . $filtro;

// Query base para obtener registros
$query_base = "SELECT * FROM cabania " . $where_clause . " " . $filtro . " ORDER BY cabania_nombre ASC";

// Obtener registros paginados
$resultado = obtener_registros_paginados($mysql, $query_base, $query_count, $pagina_actual, $registros_por_pagina);
$cabanias = $resultado['registros'];
$paginacion = $resultado['paginacion'];

$mysql->close();
?>

<!-- Información de registros -->
<div class="pagination-info">
	<?php echo mostrar_info_paginacion($paginacion); ?>
</div>

<table> 
	<thead>
		<th> <font face="Arial">Código</font> </th>
		<th> <font face="Arial">Nombre</font> </th>
		<th> <font face="Arial">Descripción</font> </th>
		<th> <font face="Arial">Capacidad</font> </th>
		<th> <font face="Arial">Precio</font> </th>
		<th> <font face="Arial">Ubicación</font> </th>
		<th> <font face="Arial">Baños</font> </th>
		<th> <font face="Arial">Habitaciones</font> </th>
		<th> <font face="Arial">Foto</font> </th>
		<th> <font face="Arial">Estado</font> </th> 
		<th> <font face="Arial">Acciones</font> </th> 
	</thead>
	<tbody>
	<?php
	if (empty($cabanias)) {
		echo "<tr><td colspan='11' style='text-align: center; padding: 20px;'>No se encontraron cabañas</td></tr>";
	} else {
		foreach ($cabanias as $row) {
			echo 
			"<tr> 
				<td>".$row["cabania_codigo"]."</td>
				<td>".$row["cabania_nombre"]."</td> 
				<td>".substr($row["cabania_descripcion"], 0, 50)."...</td>
				<td>".$row["cabania_capacidad"]."</td>
				<td>$".$row["cabania_precio"]."</td>
				<td>".$row["cabania_ubicacion"]."</td>
				<td>".$row["cabania_cantidadbanios"]."</td>
				<td>".$row["cabania_cantidadhabitaciones"]."</td>
				<td>".($row["cabania_foto"] ? "<img src='imagenes/cabanias/".$row["cabania_foto"]."' width='50' height='50'>" : "Sin foto")."</td>
				<td>".(($row["cabania_estado"]) ? "Activo" : "Baja")."</td> 
				<td>
					<button class='abm-button mod-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Cabañas&ruta=cabanias&archivo=editar.php&id_cabania=".$row["id_cabania"]."\"'>Editar</button>";
			
			// Mostrar botón Eliminar o Recuperar según el estado y permisos
			if ($row["cabania_estado"]) {
				if (es_administrador()) {
					echo "<button class='abm-button baja-button' onclick='confirmarAccion(\"/proyecto_cabania/cabanias/baja_logica.php?id_cabania=".$row["id_cabania"]."\", \"dar de baja esta cabaña\")'>Eliminar</button>";
				}
			} else {
				if (es_administrador()) {
					echo "<button class='abm-button alta-button' onclick='confirmarAccion(\"/proyecto_cabania/cabanias/quitar_baja_logica.php?id_cabania=".$row["id_cabania"]."\", \"recuperar esta cabaña\")'>Recuperar</button>";
				}
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
if (isset($_REQUEST["cabania_codigo"]) && $_REQUEST["cabania_codigo"] != "") {
	$parametros_url['cabania_codigo'] = $_REQUEST["cabania_codigo"];
}
if (isset($_REQUEST["cabania_nombre"]) && $_REQUEST["cabania_nombre"] != "") {
	$parametros_url['cabania_nombre'] = $_REQUEST["cabania_nombre"];
}
if (isset($_REQUEST["cabania_ubicacion"]) && $_REQUEST["cabania_ubicacion"] != "") {
	$parametros_url['cabania_ubicacion'] = $_REQUEST["cabania_ubicacion"];
}
if (isset($_REQUEST["cabania_capacidad"]) && $_REQUEST["cabania_capacidad"] != "") {
	$parametros_url['cabania_capacidad'] = $_REQUEST["cabania_capacidad"];
}
if (isset($_REQUEST["cabania_estado"]) && $_REQUEST["cabania_estado"] != "") {
	$parametros_url['cabania_estado'] = $_REQUEST["cabania_estado"];
}
if ($registros_por_pagina != 10) {
	$parametros_url['registros_por_pagina'] = $registros_por_pagina;
}

echo generar_enlaces_paginacion($paginacion, '/proyecto_cabania/plantilla_modulo.php?titulo=Cabañas&ruta=cabanias', $parametros_url);
?>
