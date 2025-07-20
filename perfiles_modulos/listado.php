<?php
// Configuración de paginación
$registros_por_pagina = isset($_REQUEST['registros_por_pagina']) ? intval($_REQUEST['registros_por_pagina']) : 10;
$pagina_actual = isset($_REQUEST['pagina']) ? intval($_REQUEST['pagina']) : 1;
?>

<h1>Listado de Asignaciones de Módulos a Perfiles</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='/proyecto_cabania/plantilla_modulo.php?titulo=Asignar Módulo a Perfil&ruta=perfiles_modulos&archivo=formulario.php'">Nueva asignación</button><br><br>
</div>

<!-- Selector de registros por página -->
<div style="margin-bottom: 10px;">
	<form method="get" style="display: inline;">
		<!-- Mantener filtros existentes -->
		<?php if (isset($_REQUEST["rela_perfil"]) && $_REQUEST["rela_perfil"] != ""): ?>
			<input type="hidden" name="rela_perfil" value="<?php echo htmlspecialchars($_REQUEST["rela_perfil"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["rela_modulo"]) && $_REQUEST["rela_modulo"] != ""): ?>
			<input type="hidden" name="rela_modulo" value="<?php echo htmlspecialchars($_REQUEST["rela_modulo"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["perfilmodulo_estado"]) && $_REQUEST["perfilmodulo_estado"] != ""): ?>
			<input type="hidden" name="perfilmodulo_estado" value="<?php echo htmlspecialchars($_REQUEST["perfilmodulo_estado"]); ?>">
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
	
	if (isset($_REQUEST["rela_perfil"])){
		if ($_REQUEST["rela_perfil"] != ""){
			$filtro .= " and rela_perfil = ".$_REQUEST["rela_perfil"]." ";
		}
	}
	if (isset($_REQUEST["rela_modulo"])){
		if ($_REQUEST["rela_modulo"] != ""){
			$filtro .= " and rela_modulo = ".$_REQUEST["rela_modulo"]." ";
		}
	}
	if (isset($_REQUEST["perfilmodulo_estado"])){
		if ($_REQUEST["perfilmodulo_estado"] != ""){
			$filtro .= " and perfilmodulo_estado = ".$_REQUEST["perfilmodulo_estado"]." ";
		}
	}
	
	// Aplicar filtro de estado según el tipo de usuario
	$where_clause = "where 1=1";
	if (!es_administrador()) {
		$filtro .= " and perfilmodulo_estado = 1 ";
	}

	// Query para contar total de registros
	$query_count = "SELECT COUNT(*) FROM perfil_modulo
					LEFT JOIN perfil ON rela_perfil = id_perfil
					LEFT JOIN modulo ON rela_modulo = id_modulo
					" . $where_clause . " " . $filtro;

	// Query base para obtener registros
	$query_base = "SELECT * FROM perfil_modulo
					LEFT JOIN perfil ON rela_perfil = id_perfil
					LEFT JOIN modulo ON rela_modulo = id_modulo
					" . $where_clause . " " . $filtro . "
					ORDER BY perfil_descripcion ASC, modulo_descripcion ASC";

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

<table> 
	<thead>
		<th> <font face="Arial">Perfil</font> </th>
		<th> <font face="Arial">Módulo</font> </th> 
		<th> <font face="Arial">Estado</font> </th> 
		<th> <font face="Arial">Acciones</font> </th> 
	</thead>
	<tbody>
<?php
	if (empty($registros_data)) {
		echo "<tr><td colspan='4' style='text-align: center; padding: 20px;'>No se encontraron registros</td></tr>";
	} else {
		foreach ($registros_data as $row) {
			echo 
			"<tr> 
				<td>".$row["perfil_descripcion"]."</td> 
				<td>".$row["modulo_descripcion"]."</td> 
				<td>".(($row["perfilmodulo_estado"]) ? "Activo" : "Baja")."</td> 
				<td>
					<button class='abm-button mod-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Asignar Módulo a Perfil&ruta=perfiles_modulos&archivo=editar.php&id_perfilmodulo=".$row["id_perfilmodulo"]."\"'>Editar</button>";

			// Mostrar botón Eliminar o Recuperar según el estado
			if ($row["perfilmodulo_estado"]) {
				// Si está activo, mostrar botón Eliminar
				// Verificar si es el perfil "administrador" para evitar su eliminación
				if (strtolower($row["perfil_descripcion"]) != "administrador") {
					echo "<button class='abm-button baja-button' onclick='confirmarAccion(\"/proyecto_cabania/perfiles_modulos/baja_logica.php?id_perfilmodulo=".$row["id_perfilmodulo"]."\", \"dar de baja esta relación perfil-módulo\")'>Eliminar</button>";
				} else {
					echo "<button class='abm-button baja-button' disabled title='No se puede eliminar permisos del perfil administrador por seguridad' style='opacity: 0.5; cursor: not-allowed;'>Eliminar</button>";
				}
			} else if (es_administrador()) {
				// Si está de baja y es administrador, mostrar botón Recuperar
				echo "<button class='abm-button alta-button' onclick='confirmarAccion(\"/proyecto_cabania/perfiles_modulos/quitar_baja_logica.php?id_perfilmodulo=".$row["id_perfilmodulo"]."\", \"recuperar esta relación perfil-módulo\")'>Recuperar</button>";
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
if (isset($_REQUEST["rela_perfil"]) && $_REQUEST["rela_perfil"] != "") {
	$parametros_url['rela_perfil'] = $_REQUEST["rela_perfil"];
}
if (isset($_REQUEST["rela_modulo"]) && $_REQUEST["rela_modulo"] != "") {
	$parametros_url['rela_modulo'] = $_REQUEST["rela_modulo"];
}
if (isset($_REQUEST["perfilmodulo_estado"]) && $_REQUEST["perfilmodulo_estado"] != "") {
	$parametros_url['perfilmodulo_estado'] = $_REQUEST["perfilmodulo_estado"];
}
if ($registros_por_pagina != 10) {
	$parametros_url['registros_por_pagina'] = $registros_por_pagina;
}

echo generar_enlaces_paginacion($paginacion, '/proyecto_cabania/plantilla_modulo.php?titulo=Asignaciones de Módulos a Perfiles&ruta=perfiles_modulos', $parametros_url);
?>