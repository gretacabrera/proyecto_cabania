<h1>Listado de Productos</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='/proyecto_cabania/plantilla_modulo.php?titulo=Productos&ruta=productos&archivo=formulario.php'">Nuevo producto</button><br><br>
</div>
<table> 
	<thead>
		<th> <font face="Arial">Nombre</font> </th> 
		<th> <font face="Arial">Descripcion</font> </th> 
		<th> <font face="Arial">Categoría</font> </th> 
		<th> <font face="Arial">Marca</font> </th> 
		<th> <font face="Arial">Precio Unitario</font> </th> 
		<th> <font face="Arial">Stock</font> </th>
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
	
	if (isset($_REQUEST["producto_nombre"])){
		if ($_REQUEST["producto_nombre"] != ""){
			$filtro .= " and producto_nombre LIKE '%".$_REQUEST["producto_nombre"]."%' ";
		}
	}
	if (isset($_REQUEST["rela_categoria"])){
		if ($_REQUEST["rela_categoria"] != ""){
			$filtro .= " and rela_categoria = $_REQUEST[rela_categoria] ";
		}
	}
	if (isset($_REQUEST["rela_marca"])){
		if ($_REQUEST["rela_marca"] != ""){
			$filtro .= " and rela_marca = $_REQUEST[rela_marca] ";
		}
	}
	if (isset($_REQUEST["rela_estadoproducto"])){
		if ($_REQUEST["rela_estadoproducto"] != ""){
			$filtro .= " and rela_estadoproducto = $_REQUEST[rela_estadoproducto] ";
		}
	}
	
	// Aplicar filtro de estado según el tipo de usuario
	if (!es_administrador()) {
		$filtro .= " and rela_estadoproducto != 4 ";
	}
	
	$registros = $mysql->query("select * from producto 
								left join marca on rela_marca = id_marca
								left join categoria on rela_categoria = id_categoria
								left join estadoproducto on rela_estadoproducto = id_estadoproducto
								where 1=1 
								".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["producto_nombre"]."</td> 
			<td>".$row["producto_descripcion"]."</td>  
			<td>".$row["categoria_descripcion"]."</td> 
			<td>".$row["marca_descripcion"]."</td> 
			<td>$".$row["producto_precio"]."</td>
			<td>".$row["producto_stock"]."</td>
			<td>".($row["producto_foto"] ? "<img src='imagenes/productos/".$row["producto_foto"]."' width='50' height='50'>" : "Sin foto")."</td> 
			<td>".$row["estadoproducto_descripcion"]."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Productos&ruta=productos&archivo=editar.php&id_producto=".$row["id_producto"]."\"'>Editar</button>";
		
		// Mostrar botón Eliminar o Recuperar según el estado
		if ($row["rela_estadoproducto"] == 4) {
			// Si está de baja (estado 4) y es administrador, mostrar botón Recuperar
			if (es_administrador()) {
				echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"/proyecto_cabania/plantilla_modulo.php?titulo=Productos&ruta=productos&archivo=quitar_baja_logica.php&id_producto=".$row["id_producto"]."\", \"recuperar este producto\")'>Recuperar</button>";
			}
		} else {
			// Si está activo, mostrar botón Eliminar
			echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"/proyecto_cabania/plantilla_modulo.php?titulo=Productos&ruta=productos&archivo=baja_logica.php&id_producto=".$row["id_producto"]."\", \"dar de baja este producto\")'>Eliminar</button>";
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>