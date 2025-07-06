<div class="menu">
    <div class="menu-botonera">
        <a href="/proyecto_cabania/home.php">Inicio</a>
        <?php
            require("conexion.php");  
            session_start();   
            if (isset($_SESSION["usuario_nombre"])){
                $registro = $mysql->query("select p.perfil_descripcion
                                        from perfil p
                                        left join usuario u on u.rela_perfil = p.id_perfil
                                        where u.usuario_nombre = '$_SESSION[usuario_nombre]'") or
                die($mysql->error);
                if ($registro->fetch_array()["perfil_descripcion"] == "huesped") {
                    echo 
                    "<a href='/proyecto_cabania/reservas/mis_reservas.php'>Mis Reservas</a>";
                }
                $registros = $mysql->query("select distinct m.modulo_ruta, m.modulo_descripcion
                                            from modulo m
                                            left join perfil_modulo pm on pm.rela_modulo = m.id_modulo
                                            left join perfil p on pm.rela_perfil = p.id_perfil
                                            left join usuario u on u.rela_perfil = p.id_perfil
                                            where m.modulo_estado = 1
                                            and u.usuario_estado = 1
                                            and u.usuario_nombre = '$_SESSION[usuario_nombre]'") or
                die($mysql->error);
                while ($row = $registros->fetch_assoc()) {
                    echo 
                    "<a href='/proyecto_cabania/$row[modulo_ruta]/index.php'>$row[modulo_descripcion]</a>";
                }
            }
            else{
                header("Location: usuarios/login.php");
            }
        ?>
        <a href="/proyecto_cabania/usuarios/logout.php">Cerrar Sesion</a>
    </div>
</div>