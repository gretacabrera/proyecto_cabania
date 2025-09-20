<?php
require("conexion.php");

// Validar que se recibieron todos los datos necesarios
if (!isset($_POST['id_reserva']) || !isset($_POST['id_huesped']) || 
    !isset($_POST['comentario_texto']) || !isset($_POST['puntuacion'])) {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Salidas&ruta=salidas&archivo=formulario.php',
        'Error: Faltan datos requeridos para guardar el comentario.',
        'error'
    );
    exit;
}

// Sanitizar y validar los datos
$id_reserva = intval($_POST['id_reserva']);
$id_huesped = intval($_POST['id_huesped']);
$comentario_texto = trim($_POST['comentario_texto']);
$puntuacion = intval($_POST['puntuacion']);

// Validaciones
if ($id_reserva <= 0 || $id_huesped <= 0) {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Salidas&ruta=salidas&archivo=formulario.php',
        'Error: IDs de reserva o huésped inválidos.',
        'error'
    );
    exit;
}

if (empty($comentario_texto) || strlen($comentario_texto) > 400) {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=formulario.php&id_reserva=' . $id_reserva,
        'Error: El comentario debe tener entre 1 y 400 caracteres.',
        'error'
    );
    exit;
}

if ($puntuacion < 1 || $puntuacion > 5) {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=formulario.php&id_reserva=' . $id_reserva,
        'Error: La puntuación debe estar entre 1 y 5.',
        'error'
    );
    exit;
}

// Verificar que la reserva pertenece al usuario actual
$verificacion = $mysql->query("SELECT COUNT(*) as valido
                               FROM reserva r
                               LEFT JOIN huesped_reserva hr ON hr.rela_reserva = r.id_reserva
                               LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                               LEFT JOIN persona p ON h.rela_persona = p.id_persona
                               LEFT JOIN usuario u ON u.rela_persona = p.id_persona
                               WHERE r.id_reserva = $id_reserva
                               AND hr.rela_huesped = $id_huesped
                               AND u.usuario_nombre = '$_SESSION[usuario_nombre]'") or die($mysql->error);

$es_valido = $verificacion->fetch_array()['valido'];

if ($es_valido == 0) {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Salidas&ruta=salidas&archivo=formulario.php',
        'Error: No tiene permisos para comentar esta reserva.',
        'error'
    );
    exit;
}

// Verificar que no existe ya un comentario para esta reserva del mismo huésped
$comentario_existente = $mysql->query("SELECT COUNT(*) as existe
                                       FROM comentario
                                       WHERE rela_reserva = $id_reserva
                                       AND rela_huesped = $id_huesped
                                       AND comentario_estado = 1") or die($mysql->error);

$ya_existe = $comentario_existente->fetch_array()['existe'];

if ($ya_existe > 0) {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=formulario.php&id_reserva=' . $id_reserva,
        'Ya existe un comentario para esta reserva.',
        'warning'
    );
    exit;
}

// Escapar el texto del comentario para evitar inyección SQL
$comentario_texto_escapado = $mysql->real_escape_string($comentario_texto);

// Insertar el comentario
$fecha_actual = date('Y-m-d H:i:s');
$query_insertar = "INSERT INTO comentario (comentario_fechahora, comentario_texto, comentario_puntuacion, rela_huesped, rela_reserva, comentario_estado)
                   VALUES ('$fecha_actual', '$comentario_texto_escapado', $puntuacion, $id_huesped, $id_reserva, 1)";

$resultado = $mysql->query($query_insertar);

if ($resultado) {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php',
        'Comentario guardado exitosamente. ¡Gracias por compartir tu experiencia!',
        'exito'
    );
} else {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=formulario.php&id_reserva=' . $id_reserva,
        'Error al guardar el comentario: ' . $mysql->error,
        'error'
    );
}

$mysql->close();
?>
