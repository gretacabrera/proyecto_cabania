<?php
// Configuración de paginación
$registros_por_pagina = isset($_REQUEST['registros_por_pagina']) ? intval($_REQUEST['registros_por_pagina']) : 10;
$pagina_actual = isset($_REQUEST['pagina']) ? intval($_REQUEST['pagina']) : 1;
?>

<h1>Listado de Estados de Personas</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='/proyecto_cabania/plantilla_modulo.php?titulo=Estados de Personas&ruta=estados_personas&archivo=formulario.php'">Nuevo estado de persona</button><br><br>
</div>

<!-- Selector de registros por página -->
<div style="margin-bottom: 10px;">
	<form method="get" style="display: inline;">
		<?php if (isset($_REQUEST["estadopersona_descripcion"]) && $_REQUEST["estadopersona_descripcion"] != ""): ?>
			<input type="hidden" name="estadopersona_descripcion" value="<?php echo htmlspecialchars($_REQUEST["estadopersona_descripcion"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["estadopersona_estado"]) && $_REQUEST["estadopersona_estado"] != ""): ?>
			<input type="hidden" name="estadopersona_estado" value="<?php echo htmlspecialchars($_REQUEST["estadopersona_estado"]); ?>">
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
	
	if (isset($_REQUEST["estadopersona_descripcion"])){
		if ($_REQUEST["estadopersona_descripcion"] != ""){
			$filtro .= " and estadopersona_descripcion LIKE '%".$_REQUEST["estadopersona_descripcion"]."%' ";
		}
	}
	if (isset($_REQUEST["estadopersona_estado"])){
		if ($_REQUEST["estadopersona_estado"] != ""){
			$filtro .= " and estadopersona_estado = ".$_REQUEST["estadopersona_estado"]." ";
		}
	}

	// Aplicar filtro de estado según el tipo de usuario
	$where_clause = "where 1=1";
	if (!es_administrador()) {
		$filtro .= " and estadopersona_estado = 1 ";
	}

	// Query para contar total de registros
	$query_count = "SELECT COUNT(*) FROM estadopersona " . $where_clause . " " . $filtro;

	// Query base para obtener registros
	$query_base = "SELECT * FROM estadopersona " . $where_clause . " " . $filtro . " ORDER BY estadopersona_descripcion ASC";

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
			echo "<td>".$row["estadopersona_descripcion"]."</td>";
			echo "<td>".((($row["estadopersona_estado"]) ? "Activo" : "Baja"))."</td>";

			echo "<td>";
			echo "<button class='abm-button mod-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Estados de Personas&ruta=estados_personas&archivo=editar.php&id_estadopersona=".$row["id_estadopersona"]."\"'>Editar</button>";
			
			// Mostrar botón Eliminar o Recuperar según el estado
			if ($row["estadopersona_estado"]) {
				if (es_administrador()) {
					echo "<button class='abm-button baja-button' onclick='confirmarAccion(\"/proyecto_cabania/estados_personas/baja_logica.php?id_estadopersona=".$row["id_estadopersona"]."\", \"dar de baja este estado de persona\")'>Eliminar</button>";
				}
			} else {
				if (es_administrador()) {
					echo "<button class='abm-button alta-button' onclick='confirmarAccion(\"/proyecto_cabania/estados_personas/quitar_baja_logica.php?id_estadopersona=".$row["id_estadopersona"]."\", \"recuperar este estado de persona\")'>Recuperar</button>";
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
if (isset($_REQUEST["estadopersona_descripcion"]) && $_REQUEST["estadopersona_descripcion"] != "") {
	$parametros_url['estadopersona_descripcion'] = $_REQUEST["estadopersona_descripcion"];
}
if (isset($_REQUEST["estadopersona_estado"]) && $_REQUEST["estadopersona_estado"] != "") {
	$parametros_url['estadopersona_estado'] = $_REQUEST["estadopersona_estado"];
}

if ($registros_por_pagina != 10) {
	$parametros_url['registros_por_pagina'] = $registros_por_pagina;
}

echo generar_enlaces_paginacion($paginacion, '/proyecto_cabania/plantilla_modulo.php?titulo=Estados de Personas&ruta=estados_personas', $parametros_url);
?>