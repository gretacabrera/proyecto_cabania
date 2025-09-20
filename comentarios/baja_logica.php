<?php
require("conexion.php");

// Validar que se haya proporcionado el ID del comentario
if (!isset($_GET['id_comentario']) || empty($_GET['id_comentario'])) {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php',
        'Error: No se proporcionó información del comentario.',
        'error'
    );
    exit;
}

$id_comentario = intval($_GET['id_comentario']);

// Verificar que el comentario existe y pertenece al usuario actual
$verificacion = $mysql->query("SELECT COUNT(*) as valido, c.comentario_texto
                               FROM comentario c
                               LEFT JOIN huesped h ON c.rela_huesped = h.id_huesped
                               LEFT JOIN persona p ON h.rela_persona = p.id_persona
                               LEFT JOIN usuario u ON u.rela_persona = p.id_persona
                               WHERE c.id_comentario = $id_comentario
                               AND c.comentario_estado = 1
                               AND u.usuario_nombre = '$_SESSION[usuario_nombre]'") or die($mysql->error);

$resultado_verificacion = $verificacion->fetch_array();
$es_valido = $resultado_verificacion['valido'];

if ($es_valido == 0) {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php',
        'Error: No tiene permisos para eliminar este comentario o el comentario no existe.',
        'error'
    );
    exit;
}

// Realizar baja lógica del comentario
$query_baja = "UPDATE comentario 
               SET comentario_estado = 0
               WHERE id_comentario = $id_comentario
               AND comentario_estado = 1";

$resultado = $mysql->query($query_baja);

if ($resultado) {
    // Verificar que se actualizó al menos un registro
    if ($mysql->affected_rows > 0) {
        redireccionar_con_mensaje(
            '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php',
            'Comentario eliminado exitosamente.',
            'exito'
        );
    } else {
        redireccionar_con_mensaje(
            '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php',
            'No se pudo eliminar el comentario. Es posible que ya haya sido eliminado.',
            'warning'
        );
    }
} else {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php',
        'Error al eliminar el comentario: ' . $mysql->error,
        'error'
    );
}

$mysql->close();
?>
