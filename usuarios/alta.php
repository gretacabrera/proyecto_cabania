<?php

	require("../conexion.php");

	// Validaciones del lado del servidor
	if (empty($_REQUEST["usuario_nombre"]) || empty($_REQUEST["usuario_contrasenia"]) || 
		empty($_REQUEST["confirmacion_contrasenia"]) || empty($_REQUEST["persona_nombre"]) ||
		empty($_REQUEST["persona_apellido"]) || empty($_REQUEST["persona_fechanac"]) ||
		empty($_REQUEST["contacto_email"])) {
		echo 'Error: Todos los campos obligatorios deben ser completados';
		$mysql->close();
		exit;
	}

	// Validar longitud mínima de usuario
	if (strlen($_REQUEST["usuario_nombre"]) < 3) {
		redireccionar_con_mensaje('registro.php', 'El nombre de usuario debe tener al menos 3 caracteres', 'error');
	}

	// agrego validacion para revisar la confirmacion de la contraseña
	if ($_REQUEST["usuario_contrasenia"] != $_REQUEST["confirmacion_contrasenia"]) {
		redireccionar_con_mensaje('registro.php', 'Las contraseñas no coinciden', 'error');
	}

	// agrego validacion para que no se registren menores de edad
	$fecha_nacimiento = $_REQUEST["persona_fechanac"];
	$fecha_actual = date("Y-m-d");
	if (strtotime($fecha_nacimiento) > strtotime($fecha_actual) - 568025136) { // 18 años en segundos
		redireccionar_con_mensaje('registro.php', 'Debe ser mayor de edad para registrarse', 'error');
	}

	// agrego validacion para validar el formato del email
	if (!filter_var($_REQUEST["contacto_email"], FILTER_VALIDATE_EMAIL)) {
		redireccionar_con_mensaje('registro.php', 'El formato del email es incorrecto', 'error');
	}

	// agrego validacion para que no se agreguen nombres de usuario ya existentes
	$usuario_nombre = $mysql->real_escape_string($_REQUEST["usuario_nombre"]);
	$consulta = $mysql->query("SELECT * FROM usuario WHERE usuario_nombre = '$usuario_nombre'");
	$filas = $consulta->num_rows;
	if ($filas > 0){
		redireccionar_con_mensaje('registro.php', 'El nombre de usuario ya existe. Por favor, elija otro', 'error');
	}

	// Validar que el email no esté ya registrado
	$email = $mysql->real_escape_string($_REQUEST["contacto_email"]);
	$consulta_email = $mysql->query("SELECT c.contacto_descripcion 
	                                FROM contacto c 
	                                INNER JOIN tipocontacto tc ON c.rela_tipocontacto = tc.id_tipocontacto 
	                                WHERE c.contacto_descripcion = '$email' 
	                                AND tc.tipocontacto_descripcion = 'email' 
	                                AND c.contacto_estado = 1");
	if ($consulta_email->num_rows > 0) {
		redireccionar_con_mensaje('registro.php', 'Este email ya está registrado', 'error');
	}

	$mysql->query("insert into persona (persona_nombre, persona_apellido, persona_fechanac, persona_direccion, rela_estadopersona) 
	values ('$_REQUEST[persona_nombre]','$_REQUEST[persona_apellido]','$_REQUEST[persona_fechanac]','$_REQUEST[persona_direccion]',
	(SELECT id_estadopersona FROM estadopersona WHERE estadopersona_descripcion = 'activo'))");
	$rela_persona = $mysql->insert_id; 

	// si la reserva es online, el usuario es un huesped
	// huesped_estado = 1 --> activo

	if (isset($_REQUEST["registro_online"])){
		$mysql->query("insert into huesped (rela_persona, huesped_estado) values ($rela_persona, 1)");

		// Asignar perfil 'huesped' al usuario
		$consulta_perfil = $mysql->query("SELECT id_perfil FROM perfil WHERE perfil_descripcion = 'huesped' LIMIT 1");
		if ($consulta_perfil && $consulta_perfil->num_rows > 0) {
			$fila_perfil = $consulta_perfil->fetch_assoc();
			$rela_perfil = $fila_perfil['id_perfil'];
		}
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

	// $usuario_estado = 1 --> activo

	$usuario_constrasenia = password_hash($_REQUEST["usuario_contrasenia"], PASSWORD_DEFAULT);

	$mysql->query("insert into usuario (usuario_nombre, usuario_contrasenia, rela_persona, rela_perfil, usuario_estado)
			values ('$_REQUEST[usuario_nombre]','$usuario_constrasenia', $rela_persona, $rela_perfil, 1)") or die($mysql->error);
	
	$mysql->close();

	if (isset($_REQUEST["registro_online"])){

		// Enviar correo de confirmación al usuario
		$asunto = 'Confirmación de registro';
		$cuerpo = '<h2>¡Bienvenido/a!</h2><p>Su usuario ha sido registrado exitosamente en el sistema.</p>';
		$altCuerpo = 'Su usuario ha sido registrado exitosamente en el sistema.';
		enviarCorreo($_REQUEST["contacto_email"], $asunto, $cuerpo, $altCuerpo);

		redireccionar_con_mensaje('login.php', 'Usuario registrado exitosamente. Ya puede iniciar sesión', 'exito');
	}
	else{
		echo 'Se dió de alta el usuario correctamente';
		echo '<br>';
		echo '<button onclick="location.href=\'index.php\'">Volver</button>';
	}
 ?>
