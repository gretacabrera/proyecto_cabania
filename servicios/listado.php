<?php
// Configuración de paginación
$registros_por_pagina = isset($_REQUEST['registros_por_pagina']) ? intval($_REQUEST['registros_por_pagina']) : 10;
$pagina_actual = isset($_REQUEST['pagina']) ? intval($_REQUEST['pagina']) : 1;
?>

<h1>Listado de Servicios</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='/proyecto_cabania/plantilla_modulo.php?titulo=Servicios&ruta=servicios&archivo=formulario.php'">Nuevo servicio</button><br><br>
</div>

<!-- Selector de registros por página -->
<div style="margin-bottom: 10px;">
	<form method="get" style="display: inline;">
		<?php if (isset($_REQUEST["servicio_descripcion"]) && $_REQUEST["servicio_descripcion"] != ""): ?>
			<input type="hidden" name="servicio_descripcion" value="<?php echo htmlspecialchars($_REQUEST["servicio_descripcion"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["servicio_estado"]) && $_REQUEST["servicio_estado"] != ""): ?>
			<input type="hidden" name="servicio_estado" value="<?php echo htmlspecialchars($_REQUEST["servicio_estado"]); ?>">
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

if (isset($_REQUEST["servicio_descripcion"])){
	if ($_REQUEST["servicio_descripcion"] != ""){
		$filtro .= " and servicio_descripcion LIKE '%".$_REQUEST["servicio_descripcion"]."%' ";
	}
}
if (isset($_REQUEST["servicio_estado"])){
	if ($_REQUEST["servicio_estado"] != ""){
		$filtro .= " and servicio_estado = ".$_REQUEST["servicio_estado"]." ";
	}
}


// Aplicar filtro de estado según el tipo de usuario
$where_clause = "where 1=1";
if (!es_administrador()) {
	$filtro .= " and servicio_estado = 1 ";
}

// Query para contar total de registros
$query_count = "SELECT COUNT(*) FROM servicio " . $where_clause . " " . $filtro;

// Query base para obtener registros
$query_base = "SELECT * FROM servicio " . $where_clause . " " . $filtro . " ORDER BY servicio_descripcion ASC";

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
		<th> <font face="Arial">Descripción</font> </th>
		<th> <font face="Arial">Estado</font> </th>
		<th> <font face="Arial">Acciones</font> </th>
	</thead>
	<tbody>
	<?php
	if (empty($registros)) {
		echo "<tr><td colspan='" . (count($columnas) + 1) . "' style='text-align: center; padding: 20px;'>No se encontraron registros</td></tr>";
	} else {
		foreach ($registros as $row) {
			echo "<tr>";
			echo "<td>".$row["servicio_descripcion"]."</td>";
			echo "<td>".((($row["servicio_estado"]) ? "Activo" : "Baja"))."</td>";

			echo "<td>";
			echo "<button class='abm-button mod-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Servicios&ruta=servicios&archivo=editar.php&id_servicio=".$row["id_servicio"]."\"'>Editar</button>";
			
			// Mostrar botón Eliminar o Recuperar según el estado
			if ($row["servicio_estado"]) {
				if (es_administrador()) {
					echo "<button class='abm-button baja-button' onclick='confirmarAccion(\"/proyecto_cabania/servicios/baja_logica.php?id_servicio=".$row["id_servicio"]."\", \"dar de baja este servicio\")'>Eliminar</button>";
				}
			} else {
				if (es_administrador()) {
					echo "<button class='abm-button alta-button' onclick='confirmarAccion(\"/proyecto_cabania/servicios/quitar_baja_logica.php?id_servicio=".$row["id_servicio"]."\", \"recuperar este servicio\")'>Recuperar</button>";
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
if (isset($_REQUEST["servicio_descripcion"]) && $_REQUEST["servicio_descripcion"] != "") {
	$parametros_url['servicio_descripcion'] = $_REQUEST["servicio_descripcion"];
}
if (isset($_REQUEST["servicio_estado"]) && $_REQUEST["servicio_estado"] != "") {
	$parametros_url['servicio_estado'] = $_REQUEST["servicio_estado"];
}

if ($registros_por_pagina != 10) {
	$parametros_url['registros_por_pagina'] = $registros_por_pagina;
}

echo generar_enlaces_paginacion($paginacion, '/proyecto_cabania/plantilla_modulo.php?titulo=Servicios&ruta=servicios', $parametros_url);
?>