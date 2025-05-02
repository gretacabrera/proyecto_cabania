<div class="botonera-abm">
	<button onclick="location.href='formulario.php'">Nuevo producto</button>
</div>
<table> 
	<tr> 
		<td> <font face="Arial">Nombre</font> </td> 
		<td> <font face="Arial">Descripcion</font> </td> 
		<td> <font face="Arial">Categoría</font> </td> 
		<td> <font face="Arial">Marca</font> </td> 
		<td> <font face="Arial">Precio Unitario</font> </td> 
		<td> <font face="Arial">Stock</font> </td>  
		<td> <font face="Arial">Estado</font> </td> 
		<td> <font face="Arial">Acciones</font> </td> 
	</tr>
<?php
	require("../conexion.php");
	
	$registros = $mysql->query("select * from producto 
								left join marca on rela_marca = id_marca
								left join categoria on rela_categoria = id_categoria
								left join estadoproducto on rela_estadoproducto = id_estadoproducto
								where rela_estadoproducto <> 4") or
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
				<button onclick='location.href=\"editar.php?id_producto=".$row["id_producto"]."\"'>Editar</button>
				<button onclick='location.href=\"baja_logica.php?id_producto=".$row["id_producto"]."\"'>Borrar</button>
			</td>
		</tr>";
	}
?>
<table>