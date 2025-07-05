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
	
	$registros = $mysql->query("select * from vw_usuario
								where usuario_estado <> 'baja'
								and persona_estado <> 'baja'
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
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_usuario=".$row["id_usuario"]."\"'>Editar</button>
				<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_usuario=".$row["id_usuario"]."\")'>Borrar</button>
			</td>
		</tr>";
	}
?>
<table>