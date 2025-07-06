<form method="post" action="index.php">
	Fecha y hora inicio:
	<input type="datetime-local" name="reserva_fhinicio" min="2000-01-01T00:00" max="2030-12-31T00:00" value="<?php if (isset($_REQUEST["reserva_fhinicio"])){ echo $_REQUEST["reserva_fhinicio"]; } ?>">
	Fecha y hora fin:
	<input type="datetime-local" name="reserva_fhfin" min="2000-01-01T00:00" max="2030-12-31T00:00" value="<?php if (isset($_REQUEST["reserva_fhfin"])){ echo $_REQUEST["reserva_fhfin"]; } ?>">
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
	Periodo:
	<select name="rela_periodo">
		<option value="">Seleccione el periodo de reserva...</option>
		<?php
			$registros = $mysql->query("select * from periodo") or
			die($mysql->error);
			while ($row = $registros->fetch_assoc()) {
				echo "<option value='".$row["id_periodo"]."'";
				if (isset($_REQUEST["rela_periodo"])){
					if ($_REQUEST["rela_periodo"] == $row["id_periodo"]){
						echo "selected";
					}
				}
				echo ">".$row["periodo_descripcion"]."</option>";
			}
		?>
	</select>
	Estado:
	<select name="rela_estadoreserva">
		<option value="">Seleccione el estado de reserva...</option>
		<?php
			$registros = $mysql->query("select * from estadoreserva where estadoreserva_estado = 1") or
			die($mysql->error);
			while ($row = $registros->fetch_assoc()) {
				echo "<option value='".$row["id_estadoreserva"]."'";
				if (isset($_REQUEST["rela_estadoreserva"])){
					if ($_REQUEST["rela_estadoreserva"] == $row["id_estadoreserva"]){
						echo "selected";
					}
				}
				echo ">".$row["estadoreserva_descripcion"]."</option>";
			}
		?>
	</select>
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>