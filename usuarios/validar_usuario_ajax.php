<?php
// Archivo para validaciones AJAX sin orientación a objetos
header('Content-Type: application/json');

require_once('../conexion.php');

// Función para validar si un usuario está disponible
function validar_usuario_disponible($usuario_nombre) {
    global $mysql;
    
    // Escapar datos para prevenir SQL injection
    $usuario_nombre = $mysql->real_escape_string($usuario_nombre);
    
    $consulta = $mysql->query("SELECT usuario_nombre FROM usuario WHERE usuario_nombre = '$usuario_nombre' AND usuario_estado <> 3");
    
    $respuesta = array();
    
    if ($consulta->num_rows > 0) {
        $respuesta['disponible'] = false;
        $respuesta['mensaje'] = 'Este nombre de usuario ya está en uso';
    } else {
        $respuesta['disponible'] = true;
        $respuesta['mensaje'] = 'Usuario disponible';
    }
    
    return $respuesta;
}

// Función para validar email
function validar_email_disponible($email) {
    global $mysql;
    
    $email = $mysql->real_escape_string($email);
    
    // Consulta para verificar si el email ya existe
    $consulta = $mysql->query("SELECT c.contacto_descripcion 
                              FROM contacto c 
                              INNER JOIN tipocontacto tc ON c.rela_tipocontacto = tc.id_tipocontacto 
                              WHERE c.contacto_descripcion = '$email' 
                              AND tc.tipocontacto_descripcion = 'email' 
                              AND c.contacto_estado = 1");
    
    $respuesta = array();
    
    if ($consulta->num_rows > 0) {
        $respuesta['disponible'] = false;
        $respuesta['mensaje'] = 'Este email ya está registrado';
    } else {
        $respuesta['disponible'] = true;
        $respuesta['mensaje'] = 'Email disponible';
    }
    
    return $respuesta;
}

// Procesar peticiones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['usuario_nombre'])) {
        $resultado = validar_usuario_disponible($_POST['usuario_nombre']);
        echo json_encode($resultado);
    }
    
    elseif (isset($_POST['contacto_email'])) {
        $resultado = validar_email_disponible($_POST['contacto_email']);
        echo json_encode($resultado);
    }
    
    else {
        echo json_encode(array('error' => 'Parámetros inválidos'));
    }
    
} else {
    echo json_encode(array('error' => 'Método no permitido'));
}

$mysql->close();
?>
