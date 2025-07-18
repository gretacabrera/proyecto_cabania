
<?php
require("conexion.php");

  $registro = $mysql->query("select rela_persona from usuario 
										where id_usuario=$_REQUEST[id_usuario]") or
	die($mysql->error);

  $rela_persona = $registro->fetch_array()["rela_persona"];

  $mysql->query("update usuario set 
                rela_perfil=$_REQUEST[rela_perfil],
                usuario_estado=$_REQUEST[usuario_estado]
                where id_usuario=$_REQUEST[id_usuario]") or
  die($mysql->error);

  $mysql->query("update persona set 
                persona_nombre='$_REQUEST[persona_nombre]',
                persona_apellido='$_REQUEST[persona_apellido]',
                persona_fechanac='$_REQUEST[persona_fechanac]',
                persona_direccion='$_REQUEST[persona_direccion]'
                where id_persona=$rela_persona") or
  die($mysql->error);

  $mysql->query("update contacto
                left join tipocontacto on rela_tipocontacto = id_tipocontacto
                left join persona on rela_persona = id_persona
                set contacto_descripcion='$_REQUEST[contacto_email]'
                where tipocontacto_descripcion = 'email'
                and id_persona=$rela_persona") or
  die($mysql->error);

  if (isset($_REQUEST["contacto_telefono"])){
    if ($_REQUEST["contacto_telefono"] != ""){
      $mysql->query("update contacto
            left join tipocontacto on rela_tipocontacto = id_tipocontacto
            left join persona on rela_persona = id_persona
            set contacto_descripcion='$_REQUEST[contacto_telefono]'
            where tipocontacto_descripcion = 'telefono'
            and id_persona=$rela_persona") or
      die($mysql->error);
    }
  }

  if (isset($_REQUEST["contacto_instagram"])){
    if ($_REQUEST["contacto_instagram"] != ""){
      $mysql->query("update contacto
            left join tipocontacto on rela_tipocontacto = id_tipocontacto
            left join persona on rela_persona = id_persona
            set contacto_descripcion='$_REQUEST[contacto_instagram]'
            where tipocontacto_descripcion = 'instagram'
            and id_persona=$rela_persona") or
      die($mysql->error);
    }
  }

  if (isset($_REQUEST["contacto_facebook"])){
    if ($_REQUEST["contacto_facebook"] != ""){
      $mysql->query("update contacto
            left join tipocontacto on rela_tipocontacto = id_tipocontacto
            left join persona on rela_persona = id_persona
            set contacto_descripcion='$_REQUEST[contacto_facebook]'
            where tipocontacto_descripcion = 'facebook'
            and id_persona=$rela_persona") or
      die($mysql->error);
    }
  }

  redireccionar_con_mensaje(
		'/proyecto_cabania/plantilla_modulo.php?titulo=Usuarios&ruta=usuarios&archivo=listado.php',
		'Se modificaron los datos del usuario correctamente',
		'exito'
	);
  
  $mysql->close();

  ?>