<?php
// Las funciones de paginación ahora están incluidas en funciones.php
// que se carga automáticamente desde conexion.php

// Configuración de paginación
$registros_por_pagina = isset($_REQUEST['registros_por_pagina']) ? intval($_REQUEST['registros_por_pagina']) : 10;
$pagina_actual = isset($_REQUEST['pagina']) ? intval($_REQUEST['pagina']) : 1;
?>

<h1>Listado de Usuarios</h1>

<?php include("busqueda.php"); ?>

<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='/proyecto_cabania/plantilla_modulo.php?titulo=Usuarios&ruta=usuarios&archivo=formulario.php'">Nuevo usuario</button><br><br>
</div>

<!-- Selector de registros por página -->
<div style="margin-bottom: 10px;">
	<form method="get" style="display: inline;">
		<!-- Mantener filtros existentes -->
		<?php if (isset($_REQUEST["usuario_nombre"]) && $_REQUEST["usuario_nombre"] != ""): ?>
			<input type="hidden" name="usuario_nombre" value="<?php echo htmlspecialchars($_REQUEST["usuario_nombre"]); ?>">
		<?php endif; ?>
		<?php if (isset($_REQUEST["rela_perfil"]) && $_REQUEST["rela_perfil"] != ""): ?>
			<input type="hidden" name="rela_perfil" value="<?php echo htmlspecialchars($_REQUEST["rela_perfil"]); ?>">
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

if (isset($_REQUEST["usuario_nombre"])){
	if ($_REQUEST["usuario_nombre"] != ""){
		$filtro .= " and usuario_nombre LIKE '%".$_REQUEST["usuario_nombre"]."%' ";
	}
}
if (isset($_REQUEST["rela_perfil"])){
	if ($_REQUEST["rela_perfil"] != ""){
		$filtro .= " and perfil_descripcion = (SELECT perfil_descripcion FROM perfil WHERE id_perfil = ".$_REQUEST["rela_perfil"].") ";
	}
}

// Aplicar filtro de estado según el tipo de usuario
$filtro_estado = "where persona_estado <> 'baja'";
if (!es_administrador()) {
	// Los no administradores solo ven usuarios activos (no en estado 'baja' o estado 3)
	$filtro_estado .= " and usuario_estado not in (3, 'baja')";
}

// Query para contar total de registros
$query_count = "SELECT COUNT(*) FROM vw_usuario " . $filtro_estado . " " . $filtro;

// Query base para obtener registros
$query_base = "SELECT * FROM vw_usuario " . $filtro_estado . " " . $filtro . " ORDER BY usuario_nombre ASC";

// Obtener registros paginados
$resultado = obtener_registros_paginados($mysql, $query_base, $query_count, $pagina_actual, $registros_por_pagina);
$usuarios = $resultado['registros'];
$paginacion = $resultado['paginacion'];

$mysql->close();
?>

<!-- Información de registros -->
<div class="pagination-info">
	<?php echo mostrar_info_paginacion($paginacion); ?>
</div>

<table> 
	<thead> 
		<th> <font face="Arial">Usuario</font> </th> 
		<th> <font face="Arial">Perfil</font> </th> 
		<th> <font face="Arial">Apellido y Nombre</font> </th>
		<th> <font face="Arial">Email</font> </th> 
		<th> <font face="Arial">Estado</font> </th> 
		<th> <font face="Arial">Acciones</font> </th> 
	</thead>
	<tbody>
	<?php
	if (empty($usuarios)) {
		echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>No se encontraron usuarios</td></tr>";
	} else {
		foreach ($usuarios as $row) {
			echo 
			"<tr> 
				<td>".$row["usuario_nombre"]."</td>
				<td>".$row["perfil_descripcion"]."</td> 
				<td>".$row["persona_apellido"]." ".$row["persona_nombre"]."</td>
				<td>".$row["contacto_email"]."</td>
				<td>".$row["usuario_estado"]."</td>
				<td>
					<button class='abm-button mod-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Usuarios&ruta=usuarios&archivo=editar.php&id_usuario=".$row["id_usuario"]."\"'>Editar</button>";
			
			if (es_administrador()) {
				if ($row["usuario_estado"] == 'bloqueado') {
					// Si está bloqueado, mostrar botón Desbloquear
					echo "<button class='abm-button alta-button' onclick='confirmarAccion(\"/proyecto_cabania/usuarios/cambiar_estado.php?id_usuario=".$row["id_usuario"]."&accion=desbloquear\", \"desbloquear este usuario\")'>Desbloquear</button>";
				}elseif ($row["usuario_estado"] == 'baja') {
					// Si está de baja, mostrar botón Recuperar
					echo "<button class='abm-button alta-button' onclick='confirmarAccion(\"/proyecto_cabania/usuarios/cambiar_estado.php?id_usuario=".$row["id_usuario"]."&accion=recuperar\", \"recuperar este usuario\")'>Recuperar</button>";
				} else {
					// Si está activo, mostrar botones Bloquear y Eliminar
					echo "<button class='abm-button baja-button' onclick='confirmarAccion(\"/proyecto_cabania/usuarios/cambiar_estado.php?id_usuario=".$row["id_usuario"]."&accion=bloquear\", \"bloquear este usuario\")'>Bloquear</button>";
					echo "<button class='abm-button baja-button' onclick='confirmarAccion(\"/proyecto_cabania/usuarios/cambiar_estado.php?id_usuario=".$row["id_usuario"]."&accion=baja\", \"dar de baja este usuario\")'>Eliminar</button>";
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
if (isset($_REQUEST["usuario_nombre"]) && $_REQUEST["usuario_nombre"] != "") {
	$parametros_url['usuario_nombre'] = $_REQUEST["usuario_nombre"];
}
if (isset($_REQUEST["rela_perfil"]) && $_REQUEST["rela_perfil"] != "") {
	$parametros_url['rela_perfil'] = $_REQUEST["rela_perfil"];
}
if ($registros_por_pagina != 10) {
	$parametros_url['registros_por_pagina'] = $registros_por_pagina;
}

echo generar_enlaces_paginacion($paginacion, '/proyecto_cabania/plantilla_modulo.php?titulo=Usuarios&ruta=usuarios', $parametros_url);
?>