<h1>Listado de cabañas</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='/proyecto_cabania/plantilla_modulo.php?titulo=Cabañas&ruta=cabanias&archivo=formulario.php'">Nueva cabaña</button><br><br>
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
			$filtro .= " and cabania_capacidad = $_REQUEST[cabania_capacidad] ";
		}
	}
	if (isset($_REQUEST["cabania_estado"])){
		if ($_REQUEST["cabania_estado"] != ""){
			$filtro .= " and cabania_estado = $_REQUEST[cabania_estado] ";
		}
	}
	
	// Aplicar filtro de estado según el tipo de usuario
	if (!es_administrador()) {
		$filtro .= " and cabania_estado = 1 ";
	}
	
	$registros = $mysql->query("select * from cabania where 1=1 ".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
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
				echo "<button class='abm-button baja-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Cabañas&ruta=cabanias&archivo=baja_logica.php&id_cabania=".$row["id_cabania"]."\"'>Eliminar</button>";
			}
		} else {
			if (es_administrador()) {
				echo "<button class='abm-button alta-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Cabañas&ruta=cabanias&archivo=quitar_baja_logica.php&id_cabania=".$row["id_cabania"]."\"'>Recuperar</button>";
			}
		}
		echo "</td>
		</tr>";
	}
	$mysql->close();
?> 
</table>
