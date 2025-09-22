<?php
// Mostrar mensajes del sistema modernos
if (isset($_GET['mensaje']) && isset($_GET['tipo'])):
    $tipo = $_GET['tipo'];
    $mensaje = $_GET['mensaje'];
    
    // Configurar iconos y clases según el tipo
    $config = [
        'exito' => [
            'clase' => 'alert-success',
            'icono' => 'fas fa-check-circle',
            'color' => 'success'
        ],
        'error' => [
            'clase' => 'alert-danger',
            'icono' => 'fas fa-exclamation-triangle',
            'color' => 'danger'
        ],
        'warning' => [
            'clase' => 'alert-warning',
            'icono' => 'fas fa-exclamation-circle',
            'color' => 'warning'
        ],
        'info' => [
            'clase' => 'alert-info',
            'icono' => 'fas fa-info-circle',
            'color' => 'info'
        ]
    ];
    
    $alertConfig = $config[$tipo] ?? $config['info'];
?>
    <div class="modern-alert alert <?= $alertConfig['clase'] ?> alert-dismissible fade show" 
         id="mensaje-global" 
         role="alert">
        <div class="alert-content">
            <div class="alert-icon">
                <i class="<?= $alertConfig['icono'] ?>"></i>
            </div>
            <div class="alert-message">
                <strong><?= ucfirst($tipo === 'exito' ? 'Éxito' : ($tipo === 'error' ? 'Error' : ucfirst($tipo))) ?>:</strong>
                <?= e($mensaje) ?>
            </div>
            <button type="button" class="alert-close" onclick="cerrarMensaje()" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="alert-progress">
            <div class="progress-bar" id="progress-bar"></div>
        </div>
    </div>
    

<?php endif; ?>