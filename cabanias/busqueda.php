<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Cabañas&ruta=cabanias">
    Código:
    <input type="text" name="cabania_codigo" value="<?php if (isset($_REQUEST["cabania_codigo"])){ echo $_REQUEST["cabania_codigo"]; } ?>">
    
    Nombre:
    <input type="text" name="cabania_nombre" value="<?php if (isset($_REQUEST["cabania_nombre"])){ echo $_REQUEST["cabania_nombre"]; } ?>">
    
    Ubicación:
    <input type="text" name="cabania_ubicacion" value="<?php if (isset($_REQUEST["cabania_ubicacion"])){ echo $_REQUEST["cabania_ubicacion"]; } ?>">
    
    Capacidad:
    <input type="number" name="cabania_capacidad" value="<?php if (isset($_REQUEST["cabania_capacidad"])){ echo $_REQUEST["cabania_capacidad"]; } ?>">
    
	Estado:
	<select name="cabania_estado">
		<option value="">Seleccione el estado de la cabaña...</option>
		<option value="1"
		<?php
			if (isset($_REQUEST["cabania_estado"])){
				if ($_REQUEST["cabania_estado"] == 1){
					echo "selected";
				}
			}
		?>
		>Activo</option>
		<option value="0"<?php
			if (isset($_REQUEST["cabania_estado"])){
				if ($_REQUEST["cabania_estado"] == 0){
					echo "selected";
				}
			}
		?>
		>Baja</option>
	</select>
    <input type="submit" value="Filtrar">
</form>
