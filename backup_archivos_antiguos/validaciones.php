<?php
    function validar_permiso($modulo){
        require("../conexion.php");
        $tiene_permiso = false;
        if (isset($_SESSION["usuario_nombre"])){
            $registro = $mysql->query("select count(*) resultados
                                        from modulo m
                                        left join perfil_modulo pm on pm.rela_modulo = m.id_modulo
                                        left join perfil p on pm.rela_perfil = p.id_perfil
                                        left join usuario u on u.rela_perfil = p.id_perfil
                                        where m.modulo_estado = 1
                                        and u.usuario_estado = 1
                                        and u.usuario_nombre = '$_SESSION[usuario_nombre]'
                                        and m.modulo_ruta = '$modulo'") or
            die($mysql->error);
            if ((int) $registro->fetch_array()["resultados"] > 0) {
                $tiene_permiso = true;
            } 
        }
        return $tiene_permiso;
    }
    
    function es_administrador(){
        require("../conexion.php");
        
        // Verificar si hay sesión activa
        if (!isset($_SESSION["usuario_nombre"])) {
            return false;
        }
        
        // Consultar si el usuario tiene perfil de administrador
        $registro = $mysql->query("select count(*) as es_admin
                                   from usuario u
                                   left join perfil p on u.rela_perfil = p.id_perfil
                                   where u.usuario_nombre = '$_SESSION[usuario_nombre]'
                                   and u.usuario_estado = 1
                                   and p.perfil_descripcion = 'administrador'
                                   and p.perfil_estado = 1") or
        die($mysql->error);
        
        $resultado = $registro->fetch_array();
        return (int) $resultado["es_admin"] > 0;
    }
    
?>