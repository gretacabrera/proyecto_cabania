<h1>Listado de estados de personas</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='/proyecto_cabania/plantilla_modulo.php?titulo=Estados de Personas&ruta=estados_personas&archivo=formulario.php'">Nuevo estado de persona</button><br><br>
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
			$filtro .= " and estadopersona_estado = $_REQUEST[estadopersona_estado] ";
		}
	}
	
	// Aplicar filtro de estado según el tipo de usuario
	if (!es_administrador()) {
		$filtro .= " and estadopersona_estado = 1 ";
	}
	
	$registros = $mysql->query("select * from estadopersona where 1=1 ".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["estadopersona_descripcion"]."</td> 
			<td>".(($row["estadopersona_estado"]) ? "Activo" : "Baja")."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Estados de Personas&ruta=estados_personas&archivo=editar.php&id_estadopersona=".$row["id_estadopersona"]."\"'>Editar</button>";
		
		// Mostrar botón Eliminar o Recuperar según el estado y permisos
		if ($row["estadopersona_estado"]) {
			// Si está activo, mostrar botón Eliminar
			echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"/proyecto_cabania/estados_personas/baja_logica.php?id_estadopersona=".$row["id_estadopersona"]."\", \"dar de baja este estado de persona\")'>Eliminar</button>";
		} else {
			// Si está de baja, solo mostrar botón Recuperar a administradores
			if (es_administrador()) {
				echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"/proyecto_cabania/estados_personas/quitar_baja_logica.php?id_estadopersona=".$row["id_estadopersona"]."\", \"recuperar este estado de persona\")'>Recuperar</button>";
			}
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>