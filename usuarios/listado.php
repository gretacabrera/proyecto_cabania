<h1>Listado de Usuarios</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='formulario.php'">Nuevo usuario</button><br><br>
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
<?php
	require("../conexion.php");
	require_once("../funciones.php");
	
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
	
	$registros = $mysql->query("select * from vw_usuario
								".$filtro_estado."
								".$filtro."
								order by usuario_nombre asc") or
	die($mysql->error);
	$mysql->close();
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["usuario_nombre"]."</td>
			<td>".$row["perfil_descripcion"]."</td> 
			<td>".$row["persona_apellido"]." ",$row["persona_nombre"]."</td>
			<td>".$row["contacto_email"]."</td>
			<td>".$row["usuario_estado"]."</td>
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_usuario=".$row["id_usuario"]."\"'>Editar</button>";
		
		// Mostrar botón Eliminar o Recuperar según el estado
		if ($row["usuario_estado"] == 3 || $row["usuario_estado"] == 'baja') {
			// Si está de baja (estado 3 o 'baja') y es administrador, mostrar botón Recuperar
			if (es_administrador()) {
				echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"quitar_baja_logica.php?id_usuario=".$row["id_usuario"]."\", \"recuperar este usuario\")'>Recuperar</button>";
			}
		} else {
			// Si está activo, mostrar botón Eliminar
			echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_usuario=".$row["id_usuario"]."\", \"dar de baja este usuario\")'>Eliminar</button>";
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>