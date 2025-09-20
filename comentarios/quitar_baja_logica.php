<?php
require("conexion.php");

// Verificar que el usuario esté autenticado
if (!isset($_SESSION["usuario_nombre"])) {
    redireccionar_con_mensaje("/proyecto_cabania/index.php", "Debe iniciar sesión para realizar esta acción.", "error");
    exit;
}

// Obtener el ID del comentario
$id_comentario = isset($_GET['id_comentario']) ? intval($_GET['id_comentario']) : 0;

if ($id_comentario <= 0) {
    redireccionar_con_mensaje("/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php", "ID de comentario no válido.", "error");
    exit;
}

// Verificar que el comentario existe y está dado de baja
$query_verificar = "SELECT c.*, p.persona_nombre, p.persona_apellido 
                    FROM comentario c
                    LEFT JOIN huesped h ON c.rela_huesped = h.id_huesped
                    LEFT JOIN persona p ON h.rela_persona = p.id_persona
                    WHERE c.id_comentario = $id_comentario AND c.comentario_estado = 0";

$resultado_verificar = $mysql->query($query_verificar) or die($mysql->error);

if ($resultado_verificar->num_rows == 0) {
    redireccionar_con_mensaje("/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php", "Comentario no encontrado o no está dado de baja.", "error");
    exit;
}

$comentario = $resultado_verificar->fetch_assoc();

// Reactivar el comentario (cambiar estado a 1)
$query_reactivar = "UPDATE comentario 
                    SET comentario_estado = 1
                    WHERE id_comentario = $id_comentario";

if ($mysql->query($query_reactivar)) {
    $mensaje = "Comentario de " . $comentario['persona_nombre'] . " " . $comentario['persona_apellido'] . " reactivado exitosamente.";
    redireccionar_con_mensaje("/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php", $mensaje, "exito");
} else {
    redireccionar_con_mensaje("/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php", "Error al reactivar el comentario: " . $mysql->error, "error");
}

$mysql->close();
?>
