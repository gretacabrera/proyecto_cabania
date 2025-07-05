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
								where 1=1") or
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
				<button class='abm-button mod-button' onclick='location.href=\"editar.php?id_producto=".$row["id_producto"]."\"'>Editar</button>";
		
		// Mostrar botón Eliminar o Recuperar según el estado
		if ($row["rela_estadoproducto"] == 4) {
			// Si está de baja (estado 4), mostrar botón Recuperar
			echo "<button class='abm-button alta-button' onclick='confirmarEliminacion(\"quitar_baja_logica.php?id_producto=".$row["id_producto"]."\", \"recuperar este producto\")'>Recuperar</button>";
		} else {
			// Si está activo, mostrar botón Eliminar
			echo "<button class='abm-button baja-button' onclick='confirmarEliminacion(\"baja_logica.php?id_producto=".$row["id_producto"]."\", \"dar de baja este producto\")'>Eliminar</button>";
		}
		
		echo "</td>
		</tr>";
	}
?>
<table>