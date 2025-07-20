<h1>Listado de Usuarios</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='/proyecto_cabania/plantilla_modulo.php?titulo=Usuarios&ruta=usuarios&archivo=formulario.php'">Nuevo usuario</button><br><br>
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
				<button class='abm-button mod-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Usuarios&ruta=usuarios&archivo=editar.php&id_usuario=".$row["id_usuario"]."\"'>Editar</button>";
		
		if (es_administrador()) {
			if ($row["usuario_estado"] == 'bloqueado') {
				// Si está bloqueado, mostrar botón Desbloquear
				echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"/proyecto_cabania/usuarios/cambiar_estado.php?id_usuario=".$row["id_usuario"]."&accion=desbloquear\", \"desbloquear este usuario\")'>Desbloquear</button>";
			}elseif ($row["usuario_estado"] == 'baja') {
				// Si está de baja, mostrar botón Recuperar
				echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"/proyecto_cabania/usuarios/cambiar_estado.php?id_usuario=".$row["id_usuario"]."&accion=recuperar\", \"recuperar este usuario\")'>Recuperar</button>";
			} else {
				// Si está activo, mostrar botones Bloquear y Eliminar
				echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"/proyecto_cabania/usuarios/cambiar_estado.php?id_usuario=".$row["id_usuario"]."&accion=bloquear\", \"bloquear este usuario\")'>Bloquear</button>";
				echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"/proyecto_cabania/usuarios/cambiar_estado.php?id_usuario=".$row["id_usuario"]."&accion=baja\", \"dar de baja este usuario\")'>Eliminar</button>";
			}
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>