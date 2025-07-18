
<h1>Filtros de busqueda:</h1>
<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Producto Más Vendido por Mes&ruta=reportes/producto_mas_vendido_x_mes">
	Fecha desde:
	<input type="month" name="fecha_desde" min="2000-01" max="2030-12" value="<?php if (isset($_REQUEST["fecha_desde"])){ echo $_REQUEST["fecha_desde"]; } ?>">
	<br>
	Fecha hasta:
	<input type="month" name="fecha_hasta" min="2000-01" max="2030-12" value="<?php if (isset($_REQUEST["fecha_hasta"])){ echo $_REQUEST["fecha_hasta"]; } ?>">
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>
