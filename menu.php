<div class="menu">
    <div class="menu-botonera">
        <a href="/proyecto_cabania/index.php">Inicio</a>
        <?php
            require("conexion.php");  
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }   
            if (isset($_SESSION["usuario_nombre"])){
                // Obtener los módulos del usuario
                $registros = $mysql->query("select men.menu_nombre, m.modulo_ruta, m.modulo_descripcion, m.rela_menu
                                            from modulo m
                                            left join perfil_modulo pm on pm.rela_modulo = m.id_modulo
                                            left join perfil p on pm.rela_perfil = p.id_perfil
                                            left join usuario u on u.rela_perfil = p.id_perfil
                                            left join menu men on m.rela_menu = men.id_menu
                                            where pm.perfilmodulo_estado = 1
                                            and m.modulo_estado = 1
                                            and u.usuario_estado = 1
                                            and u.usuario_nombre = '$_SESSION[usuario_nombre]'
                                            order by men.menu_nombre, m.modulo_descripcion") or
                die($mysql->error);
                
                // Agrupar módulos por menú
                $modulos_por_menu = array();
                $modulos_sin_menu = array();
                
                while ($row = $registros->fetch_assoc()) {
                    if ($row['rela_menu'] && $row['menu_nombre']) {
                        if (!isset($modulos_por_menu[$row['menu_nombre']])) {
                            $modulos_por_menu[$row['menu_nombre']] = array();
                        }
                        $modulos_por_menu[$row['menu_nombre']][] = $row;
                    } else {
                        $modulos_sin_menu[] = $row;
                    }
                }

                // Mostrar módulos sin menú asignado como enlaces normales
                foreach ($modulos_sin_menu as $modulo) {
                    echo "<a href='plantilla_modulo.php?titulo=$modulo[modulo_descripcion]&ruta=$modulo[modulo_ruta]'>$modulo[modulo_descripcion]</a>";
                }
                
                // Mostrar módulos organizados por menú
                foreach ($modulos_por_menu as $menu_nombre => $modulos) {
                    echo "<div class='menu-seccion'>";
                    echo "<span class='menu-titulo'>$menu_nombre</span>";
                    echo "<div class='menu-items'>";
                    foreach ($modulos as $modulo) {
                        echo "<a href='plantilla_modulo.php?titulo=$modulo[modulo_descripcion]&ruta=$modulo[modulo_ruta]' class='menu-item'>$modulo[modulo_descripcion]</a>";
                    }
                    echo "</div>";
                    echo "</div>";
                }
            }
            else{
                header("Location: usuarios/login.php");
            }
        ?>
        <a href="/proyecto_cabania/usuarios/logout.php">Cerrar Sesion</a>
    </div>
</div>