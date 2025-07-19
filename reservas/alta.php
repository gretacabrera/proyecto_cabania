<?php
	require("conexion.php");
	
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	if (isset($_SESSION["usuario_nombre"])){

		$reserva_fhinicio = (new DateTime($_REQUEST["reserva_fhinicio"]))->format('Y-m-d H:i:s');
		$reserva_fhfin = (new DateTime($_REQUEST["reserva_fhfin"]))->format('Y-m-d H:i:s');
		$rela_cabania = $_REQUEST["rela_cabania"];

		$registro = $mysql->query("select * from periodo 
									where '$reserva_fhinicio' BETWEEN periodo_fechainicio AND periodo_fechafin") or 
			die($mysql->error);

		if ($registro->num_rows == 0){
			echo "No se encontró el periodo correspondiente a la fecha de inicio.<br><br>";
			echo '<button onclick="location.href=\'../index.php\'">Volver</button>';
			die();
		}

		$rela_periodo = $registro->fetch_array()["id_periodo"];

		if (isset($_REQUEST["persona_dni"])){
			// si se paso este parametro, la solicitud fue realizada por un cajero o administrador
			$registro = $mysql->query("select h.id_huesped
										from huesped h
										left join persona p on h.rela_persona = p.id_persona
										where p.persona_dni = $_REQUEST[persona_dni]") or
				die($mysql->error);
			$rela_huesped = $registro->fetch_array()["id_huesped"];
			$estado = 'confirmada'; // se puede pagar al final de la estadía
		}
		else{
			// si no, el que solicito la reserva fue el mismo huesped
			$registro = $mysql->query("select h.id_huesped
										from huesped h
										left join persona p on h.rela_persona = p.id_persona
										left join usuario u on u.rela_persona = p.id_persona
										where u.usuario_nombre='$_SESSION[usuario_nombre]'") or
				die($mysql->error);
			$rela_huesped = $registro->fetch_array()["id_huesped"];
			$estado = 'pendiente'; // se debe pagar antes para confirmar
		}

		$mysql->query("insert into reserva (reserva_fhinicio, reserva_fhfin, rela_cabania, rela_periodo, rela_estadoreserva) 
						values ('$reserva_fhinicio','$reserva_fhfin', $rela_cabania, $rela_periodo, 
						(select id_estadoreserva from estadoreserva where estadoreserva_descripcion = '$estado'))") or 
		die($mysql->error);

		$rela_reserva = $mysql->insert_id; 

		$mysql->query("insert into huesped_reserva (rela_reserva, rela_huesped) 
						values ($rela_reserva, $rela_huesped)") or 
		die($mysql->error);

		if ($estado == 'confirmada'){
			echo "Se registró correctamente la reserva confirmada.";
			echo '<br>';
			echo '<button onclick="location.href=\'index.php\'">Volver</button>';
		}
		else{
			echo "Se registró correctamente la reserva pendiente de confirmación.";
		}
	}
	else{
		echo "Para registrar una reserva, primero debe iniciar sesion.";
		echo '<br>';
		echo '<button onclick="location.href=\'../usuarios/login.php\'">Ir</button>';
	}
 ?>
