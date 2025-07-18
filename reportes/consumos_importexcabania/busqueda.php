
<h1>Filtros de busqueda:</h1>
<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Consumos Importe por Cabaña&ruta=reportes/consumos_importexcabania">
	Cabaña:
	<select name="rela_cabania">
		<option value="">Seleccione la cabania...</option>
		<?php
			$registros = $mysql->query("select * from cabania") or
			die($mysql->error);
			while ($row = $registros->fetch_assoc()) {
				echo "<option value='".$row["id_cabania"]."'";
				if (isset($_REQUEST["rela_cabania"])){
					if ($_REQUEST["rela_cabania"] == $row["id_cabania"]){
						echo "selected";
					}
				}
				echo ">".$row["cabania_codigo"]." - ".$row["cabania_nombre"]."</option>";
			}
		?>
	</select>
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>
