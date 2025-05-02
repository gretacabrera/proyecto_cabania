<?php
	require("../conexion.php");

	$mysql->query("insert into persona (persona_nombre, persona_apellido, persona_dni, persona_fechanac, persona_direccion, rela_estadopersona) 
	values ('$_REQUEST[persona_nombre]','$_REQUEST[persona_apellido]',$_REQUEST[persona_dni],'$_REQUEST[persona_fechanac]','$_REQUEST[persona_direccion]',
	(SELECT id_estadopersona FROM estadopersona WHERE estadopersona_descripcion = 'activo'))");
	$rela_persona = $mysql->insert_id; 

	// si la reserva es online, el usuario es un huesped
	// huesped_estado = 1 --> activo

	if (isset($_REQUEST["registro_online"])){
		$mysql->query("insert into huesped (rela_persona, huesped_estado) values ($rela_persona, 1)");
	}

	// contacto_estado = 1 --> activo

	$mysql->query("insert into contacto (contacto_descripcion, rela_tipocontacto, rela_persona, contacto_estado) 
	values ('$_REQUEST[contacto_email]',(SELECT id_tipocontacto FROM tipocontacto WHERE tipocontacto_descripcion = 'email'), $rela_persona, 1)");

	if (isset($_REQUEST["contacto_telefono"])){
		if ($_REQUEST["contacto_telefono"] != ""){
			$mysql->query("insert into contacto (contacto_descripcion, rela_tipocontacto, rela_persona, contacto_estado) 
			values ('$_REQUEST[contacto_telefono]',(SELECT id_tipocontacto FROM tipocontacto WHERE tipocontacto_descripcion = 'telefono'), $rela_persona, 1)");
		}
	}

	if (isset($_REQUEST["contacto_instagram"])){
		if ($_REQUEST["contacto_instagram"] != ""){
			$mysql->query("insert into contacto (contacto_descripcion, rela_tipocontacto, rela_persona, contacto_estado) 
			values ('$_REQUEST[contacto_instagram]',(SELECT id_tipocontacto FROM tipocontacto WHERE tipocontacto_descripcion = 'instagram'), $rela_persona, 1)");
		}
	}

	if (isset($_REQUEST["contacto_facebook"])){
		if ($_REQUEST["contacto_facebook"] != ""){
			$mysql->query("insert into contacto (contacto_descripcion, rela_tipocontacto, rela_persona, contacto_estado) 
			values ('$_REQUEST[contacto_facebook]',(SELECT id_tipocontacto FROM tipocontacto WHERE tipocontacto_descripcion = 'facebook'), $rela_persona, 1)");
		}
	}

	if (isset($_REQUEST["registro_online"])){
		$rela_perfil = "(SELECT id_perfil FROM perfil WHERE perfil_descripcion = 'huesped')";
		$usuario_estado = 1; // activo
	}
	else{
		$rela_perfil = $_REQUEST["rela_perfil"];
		$usuario_estado = $_REQUEST["usuario_estado"];
	}

	$mysql->query("insert into usuario (usuario_nombre, usuario_contrasenia, rela_persona, rela_perfil, usuario_estado)
			values ('$_REQUEST[usuario_nombre]','$_REQUEST[usuario_contrasenia]', $rela_persona, $rela_perfil, $usuario_estado)") or die($mysql->error);
	
	$mysql->close();

	if (isset($_REQUEST["registro_online"])){
		header("Location: login.php");
	}
	else{
		echo 'Se dió de alta el usuario correctamente';
		echo '<br>';
		echo '<button onclick="location.href=\'index.php\'">Volver</button>';
	}
 ?>
