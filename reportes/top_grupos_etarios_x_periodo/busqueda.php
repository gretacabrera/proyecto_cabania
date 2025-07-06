
<h1>Filtros de busqueda:</h1>

<form method="post" action="index.php">
	Periodo:
	<select name="id_periodo" required>
		<option value="">Seleccione el periodo...</option>
		<?php
			$registros = $mysql->query("select * from periodo") or
			die($mysql->error);
			while ($row = $registros->fetch_assoc()) {
				echo "<option value='".$row["id_periodo"]."'";
				if (isset($_REQUEST["id_periodo"])){
					if ($_REQUEST["id_periodo"] == $row["id_periodo"]){
						echo "selected";
					}
				}
				echo ">".$row["periodo_descripcion"]."</option>";
			}
		?>
	</select>
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>
