<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú de Navegación</title>
    <link rel="stylesheet" href="../estilos.css">
</head>
<body class="home">
    <?php
        include("../menu.php");
    ?>
    <div class="content">
        <?php
            require("../perfiles/validar_permiso.php");
			if (validar_permiso("reportes/index.php")){
				echo "<h1>Bienvenido al Área de Reportes</h1>
                        <p>Selecciona una opción del menú para navegar entre los diferentes reportes.</p><br><br>
                        <a href='/proyecto_cabania/reportes/productos_cantidadxcategoria/index.php'>Productos por Categoría</a><br><br>
                        <a href='/proyecto_cabania/reportes/consumos_importexcabania/index.php'>Consumos por Cabaña</a><br><br>
                        <a href='/proyecto_cabania/reportes/producto_mas_vendido_x_mes/index.php'>Productos Mas Vendidos por Mes</a><br><br>
                        <a href='/proyecto_cabania/reportes/temporada_alta_x_anio/index.php'>Temporadas Altas por Año</a><br><br>
                        <a href='/proyecto_cabania/reportes/top_grupos_etarios_x_periodo/index.php'>Frecuencias de Reservas Por Grupo Etario en un Período</a>
                    ";
			}
			else{
				echo "No tiene permiso para acceder a este modulo.";
			}
        ?>
    </div>
</body>
</html>
