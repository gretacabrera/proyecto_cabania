<?php
// Sistema de mensajes globales para validaciones
function mostrar_mensaje() {
    if (isset($_GET['mensaje']) && isset($_GET['tipo'])) {
        $clase_css = '';
        switch ($_GET['tipo']) {
            case 'exito':
                $clase_css = 'mensaje-exito';
                break;
            case 'error':
                $clase_css = 'mensaje-error';
                break;
            case 'warning':
                $clase_css = 'mensaje-warning';
                break;
            default:
                $clase_css = 'mensaje-info';
        }
        
        echo '<div class="' . $clase_css . '" id="mensaje-global">';
        echo htmlspecialchars($_GET['mensaje']);
        echo '<button type="button" class="cerrar-mensaje" onclick="cerrarMensaje()">×</button>';
        echo '</div>';
    }
}

// Función para redireccionar con mensaje
function redireccionar_con_mensaje($url, $mensaje, $tipo = 'info') {
    $url_con_mensaje = $url . (strpos($url, '?') !== false ? '&' : '?') . 
                       'mensaje=' . urlencode($mensaje) . '&tipo=' . urlencode($tipo);
    header("Location: " . $url_con_mensaje);
    exit();
}
?>
