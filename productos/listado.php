<h1>Listado de Productos</h1>
<?php
	include("busqueda.php");
?>
<div class="botonera-abm">
	<button class="abm-button alta-button" onclick="location.href='formulario.php'">Nuevo producto</button><br><br>
</div>
<table> 
	<thead>
		<th> <font face="Arial">Nombre</font> </th> 
		<th> <font face="Arial">Descripcion</font> </th> 
		<th> <font face="Arial">Categoría</font> </th> 
		<th> <font face="Arial">Marca</font> </th> 
		<th> <font face="Arial">Precio Unitario</font> </th> 
		<th> <font face="Arial">Stock</font> </th>  
		<th> <font face="Arial">Estado</font> </th> 
		<th> <font face="Arial">Acciones</font> </th> 
	</thead>
<?php
	require("../conexion.php");

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
	
	$registros = $mysql->query("select * from producto 
								left join marca on rela_marca = id_marca
								left join categoria on rela_categoria = id_categoria
								left join estadoproducto on rela_estadoproducto = id_estadoproducto
								where rela_estadoproducto IN 
									(select id_estadoproducto from estadoproducto 
									where estadoproducto_estado = 1 
									and estadoproducto_descripcion <> 'baja')
								".$filtro) or
	die($mysql->error);
	
	while ($row = $registros->fetch_assoc()) {
		echo 
		"<tr> 
			<td>".$row["producto_nombre"]."</td> 
			<td>".$row["producto_descripcion"]."</td>  
			<td>".$row["categoria_descripcion"]."</td> 
			<td>".$row["marca_descripcion"]."</td> 
			<td>".$row["producto_precio"]."</td>
			<td>".$row["producto_stock"]."</td> 
			<td>".$row["estadoproducto_descripcion"]."</td> 
			<td>
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_producto=".$row["id_producto"]."\"'>Editar</button>
				<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_producto=".$row["id_producto"]."\")'>Borrar</button>
			</td>
		</tr>";
	}
?>
<table>