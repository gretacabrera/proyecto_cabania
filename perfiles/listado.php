<?php
// Configuración de paginación
$registros_por_pagina = isset($_REQUEST['registros_por_pagina']) ? intval($_REQUEST['registros_por_pagina']) : 10;
$pagina_actual = isset($_REQUEST['pagina']) ? intval($_REQUEST['pagina']) : 1;
?>

<h1>Listado de Perfiles</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='/proyecto_cabania/plantilla_modulo.php?titulo=Perfiles&ruta=perfiles&archivo=formulario.php'">Nuevo perfil</button><br><br>
</div>

<!-- Selector de registros por página -->
<div style="margin-bottom: 10px;">
	<form method="get" style="display: inline;">
		<?php if (isset($_REQUEST["perfil_descripcion"]) && $_REQUEST["perfil_descripcion"] != ""): ?>
			<input type="hidden" name="perfil_descripcion" value="<?php echo htmlspecialchars($_REQUEST["perfil_descripcion"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["perfil_estado"]) && $_REQUEST["perfil_estado"] != ""): ?>
			<input type="hidden" name="perfil_estado" value="<?php echo htmlspecialchars($_REQUEST["perfil_estado"]); ?>">
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

// Aplicar filtro de estado según el perfil de usuario
if (!es_administrador()) {
	$filtro .= " and perfil_estado = 1 ";
}

if (isset($_REQUEST["perfil_descripcion"])){
	if ($_REQUEST["perfil_descripcion"] != ""){
		$filtro .= " and perfil_descripcion LIKE '%".$_REQUEST["perfil_descripcion"]."%' ";
	}
}
if (isset($_REQUEST["perfil_estado"])){
	if ($_REQUEST["perfil_estado"] != ""){
		$filtro .= " and perfil_estado = ".$_REQUEST["perfil_estado"]." ";
	}
}

// Query para contar total de registros
$query_count = "SELECT COUNT(*) FROM perfil WHERE 1=1 " . $filtro;

// Query base para obtener registros
$query_base = "SELECT * FROM perfil WHERE 1=1 " . $filtro . " ORDER BY perfil_descripcion ASC";

// Obtener registros paginados
$resultado = obtener_registros_paginados($mysql, $query_base, $query_count, $pagina_actual, $registros_por_pagina);
$registros = $resultado['registros'];
$paginacion = $resultado['paginacion'];

//$mysql->close();

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
			echo "<td>".$row["perfil_descripcion"]."</td>";
			echo "<td>".((($row["perfil_estado"]) ? "Activo" : "Baja"))."</td>";

			echo "<td>";
			echo "<button class='abm-button mod-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Perfiles&ruta=perfiles&archivo=editar.php&id_perfil=".$row["id_perfil"]."\"'>Editar</button>";
			
			// Mostrar botón Eliminar o Recuperar según el estado
			if ($row["perfil_estado"]) {
				if (es_administrador()) {
					// Verificar si es el perfil "administrador" para evitar su eliminación
					if (strtolower($row["perfil_descripcion"]) != "administrador") {
						echo "<button class='abm-button baja-button' onclick='confirmarAccion(\"/proyecto_cabania/perfiles/baja_logica.php?id_perfil=".$row["id_perfil"]."\", \"dar de baja este perfil\")'>Eliminar</button>";
					} else {
						echo "<button class='abm-button baja-button' disabled title='No se puede eliminar el perfil administrador por seguridad' style='opacity: 0.5; cursor: not-allowed;'>Eliminar</button>";
					}
				}
			} else {
				if (es_administrador()) {
					echo "<button class='abm-button alta-button' onclick='confirmarAccion(\"/proyecto_cabania/perfiles/quitar_baja_logica.php?id_perfil=".$row["id_perfil"]."\", \"recuperar este perfil\")'>Recuperar</button>";
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
if (isset($_REQUEST["perfil_descripcion"]) && $_REQUEST["perfil_descripcion"] != "") {
	$parametros_url['perfil_descripcion'] = $_REQUEST["perfil_descripcion"];
}
if (isset($_REQUEST["perfil_estado"]) && $_REQUEST["perfil_estado"] != "") {
	$parametros_url['perfil_estado'] = $_REQUEST["perfil_estado"];
}

if ($registros_por_pagina != 10) {
	$parametros_url['registros_por_pagina'] = $registros_por_pagina;
}

echo generar_enlaces_paginacion($paginacion, '/proyecto_cabania/plantilla_modulo.php?titulo=Perfiles&ruta=perfiles', $parametros_url);
?>