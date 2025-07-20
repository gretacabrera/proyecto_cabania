<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Períodos&ruta=periodos">
    <!-- Mantener parámetros de paginación -->
    <?php if (isset($_REQUEST['registros_por_pagina'])): ?>
        <input type="hidden" name="registros_por_pagina" value="<?php echo htmlspecialchars($_REQUEST['registros_por_pagina']); ?>">
    <?php endif; ?>
    <?php if (isset($_REQUEST['pagina'])): ?>
        <input type="hidden" name="pagina" value="<?php echo htmlspecialchars($_REQUEST['pagina']); ?>">
    <?php endif; ?>
    
    Descripción:
    <input type="text" name="periodo_descripcion" value="<?php if (isset($_REQUEST["periodo_descripcion"])){ echo $_REQUEST["periodo_descripcion"]; } ?>">
	Año:
    <input type="text" name="periodo_anio" value="<?php if (isset($_REQUEST["periodo_anio"])){ echo $_REQUEST["periodo_anio"]; } ?>">
	Estado:
	<select name="periodo_estado">
		<option value="">Seleccione el estado del periodo...</option>
		<option value="1"
		<?php
			if (isset($_REQUEST["periodo_estado"])){
				if ($_REQUEST["periodo_estado"] == 1){
					echo "selected";
				}
			}
		?>
		>Activo</option>
		<option value="0"<?php
			if (isset($_REQUEST["periodo_estado"])){
				if ($_REQUEST["periodo_estado"] == 0){
					echo "selected";
				}
			}
		?>
		>Baja</option>
	</select>
	<input type="submit" value="Buscar">
	<input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
</form>