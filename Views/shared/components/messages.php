<?php
// Convertir mensajes del sistema a nuestro nuevo sistema JavaScript minimalista
if (isset($_GET['mensaje']) && isset($_GET['tipo'])):
    $tipo = $_GET['tipo'];
    $mensaje = $_GET['mensaje'];
    
    // Mapear tipos del sistema a nuestros tipos JavaScript
    $tipoMapping = [
        'exito' => 'success',
        'error' => 'error', 
        'warning' => 'warning',
        'info' => 'info',
        'aviso' => 'warning'
    ];
    
    $tipoJS = $tipoMapping[$tipo] ?? 'info';
    $tituloMapping = [
        'exito' => 'Operación exitosa',
        'error' => 'Error',
        'warning' => 'Advertencia', 
        'info' => 'Información',
        'aviso' => 'Aviso'
    ];
    
    $titulo = $tituloMapping[$tipo] ?? 'Información';
?>
    <script>
        // Mostrar mensaje automáticamente cuando la página cargue
        document.addEventListener('DOMContentLoaded', function() {
            // Pequeña espera para que el sistema de mensajes esté listo
            setTimeout(function() {
                if (typeof window.showMessage === 'function') {
                    // Usar directamente el mensaje como título, sin texto secundario
                    window.showMessage('<?= $tipoJS ?>', '<?= addslashes($mensaje) ?>', '');
                } else {
                    // Fallback si el sistema no está listo
                    console.log('Sistema de mensajes no disponible, mensaje: <?= addslashes($mensaje) ?>');
                }
            }, 100);
        });
    </script>
<?php endif; ?>