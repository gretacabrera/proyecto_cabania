<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú de Navegación</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body class="home">
    <?php
        include("menu.php");
    ?>
    <div class="fixed-div-left">
        <label>CASA DE PALOS</label><br>
        <label>CABAÑAS</label>
    </div>
    <?php
        require("conexion.php"); 
        if (isset($_SESSION["usuario_nombre"])){
            $registro = $mysql->query("select p.perfil_descripcion
                                        from perfil p
                                        left join usuario u on u.rela_perfil = p.id_perfil
                                        where u.usuario_nombre = '$_SESSION[usuario_nombre]'") or
            die($mysql->error);
            if ($registro->fetch_array()["perfil_descripcion"] == "huesped") {
                echo 
                "<div class='fixed-div-right'>
                    <button class='a-button' onclick='location.href=\"plantilla_modulo.php?titulo=Reserva Online&ruta=reservas/online&archivo=formulario.php\"'>HACER UNA RESERVA</button>
                </div>";
            }
            $mysql->close();
        }
    ?>
</body>
</html>
