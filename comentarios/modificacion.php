<?php
require("conexion.php");

// Validar que se recibieron todos los datos necesarios
if (!isset($_POST['id_comentario']) || !isset($_POST['id_reserva']) || !isset($_POST['id_huesped']) || 
    !isset($_POST['comentario_texto']) || !isset($_POST['puntuacion'])) {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php',
        'Error: Faltan datos requeridos para actualizar el comentario.',
        'error'
    );
    exit;
}

// Sanitizar y validar los datos
$id_comentario = intval($_POST['id_comentario']);
$id_reserva = intval($_POST['id_reserva']);
$id_huesped = intval($_POST['id_huesped']);
$comentario_texto = trim($_POST['comentario_texto']);
$puntuacion = intval($_POST['puntuacion']);

// Validaciones básicas
if ($id_comentario <= 0 || $id_reserva <= 0 || $id_huesped <= 0) {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php',
        'Error: IDs inválidos.',
        'error'
    );
    exit;
}

if (empty($comentario_texto) || strlen($comentario_texto) > 400) {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=editar.php&id_comentario=' . $id_comentario,
        'Error: El comentario debe tener entre 1 y 400 caracteres.',
        'error'
    );
    exit;
}

if ($puntuacion < 1 || $puntuacion > 5) {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=editar.php&id_comentario=' . $id_comentario,
        'Error: La puntuación debe estar entre 1 y 5.',
        'error'
    );
    exit;
}

// Verificar que el comentario existe y pertenece al usuario actual
$verificacion = $mysql->query("SELECT COUNT(*) as valido
                               FROM comentario c
                               LEFT JOIN huesped h ON c.rela_huesped = h.id_huesped
                               LEFT JOIN persona p ON h.rela_persona = p.id_persona
                               LEFT JOIN usuario u ON u.rela_persona = p.id_persona
                               WHERE c.id_comentario = $id_comentario
                               AND c.rela_reserva = $id_reserva
                               AND c.rela_huesped = $id_huesped
                               AND c.comentario_estado = 1
                               AND u.usuario_nombre = '$_SESSION[usuario_nombre]'") or die($mysql->error);

$es_valido = $verificacion->fetch_array()['valido'];

if ($es_valido == 0) {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php',
        'Error: No tiene permisos para editar este comentario o el comentario no existe.',
        'error'
    );
    exit;
}

// Escapar el texto del comentario para evitar inyección SQL
$comentario_texto_escapado = $mysql->real_escape_string($comentario_texto);

// Actualizar el comentario
$query_actualizar = "UPDATE comentario 
                     SET comentario_texto = '$comentario_texto_escapado',
                         comentario_puntuacion = $puntuacion
                     WHERE id_comentario = $id_comentario
                     AND comentario_estado = 1";

$resultado = $mysql->query($query_actualizar);

if ($resultado) {
    // Verificar que se actualizó al menos un registro
    if ($mysql->affected_rows > 0) {
        redireccionar_con_mensaje(
            '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php',
            'Comentario actualizado exitosamente.',
            'exito'
        );
    } else {
        redireccionar_con_mensaje(
            '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=editar.php&id_comentario=' . $id_comentario,
            'No se realizaron cambios en el comentario.',
            'warning'
        );
    }
} else {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=editar.php&id_comentario=' . $id_comentario,
        'Error al actualizar el comentario: ' . $mysql->error,
        'error'
    );
}

$mysql->close();
?>
