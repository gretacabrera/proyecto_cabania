<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $this->escape($title) : 'Casa de Palos - Cabañas' ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Estilos CSS Centralizados -->
    <link href="<?= $this->asset('assets/css/main.css?v=' . time()) ?>" rel="stylesheet">
    <link href="<?= $this->asset('assets/css/components.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('assets/css/forms.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('assets/css/public.css') ?>" rel="stylesheet">
    <!-- Override para mensajes minimalistas -->
    <link href="<?= $this->asset('assets/css/messages-override.css?v=' . time()) ?>" rel="stylesheet">
</head>
<body class="auth">
    <!-- Contenedor principal de autenticación -->
    <div class="auth-main-container">
        <!-- Panel izquierdo con información -->
        <div class="auth-info-panel">
            <div class="auth-brand">
                <i class="fas fa-mountain"></i>
                <h1>Casa de Palos</h1>
                <p>Cabañas & Experiencias</p>
            </div>
            
            <div class="auth-features">
                <div class="auth-feature">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Acceso Seguro</h3>
                    <p>Tu información está protegida con la mejor seguridad</p>
                </div>
                <div class="auth-feature">
                    <i class="fas fa-clock"></i>
                    <h3>Disponible 24/7</h3>
                    <p>Gestiona tus reservas en cualquier momento</p>
                </div>
                <div class="auth-feature">
                    <i class="fas fa-heart"></i>
                    <h3>Experiencia Única</h3>
                    <p>Vive momentos inolvidables en la naturaleza</p>
                </div>
            </div>
        </div>
        
        <!-- Panel derecho con formulario -->
        <div class="auth-form-panel">
            <div class="auth-form-container">
                <!-- Header del formulario -->
                <div class="auth-form-header">
                    <h2><?= isset($pageTitle) ? $this->escape($pageTitle) : 'Iniciar Sesión' ?></h2>
                    <p>
                        <?php 
                        if (isset($pageTitle)) {
                            switch($pageTitle) {
                                case 'Recuperar Contraseña':
                                    echo 'Ingrese su email para recibir instrucciones de recuperación';
                                    break;
                                case 'Restablecer Contraseña':
                                    echo 'Establezca una nueva contraseña segura para su cuenta';
                                    break;
                                case 'Cambiar Contraseña':
                                    echo 'Actualice su contraseña por una más segura';
                                    break;
                                default:
                                    echo 'Accede a tu cuenta para gestionar tus reservas';
                            }
                        } else {
                            echo 'Accede a tu cuenta para gestionar tus reservas';
                        }
                        ?>
                    </p>
                </div>
                
                <!-- Contenido del formulario -->
                <div class="auth-form-content">
                    <?php $this->component('messages'); ?>
                    <?= $content ?>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- Botón de volver al inicio -->
    <a href="<?= $this->url('/') ?>" class="auth-home-btn">
        <i class="fas fa-home"></i>
        <span>Volver al Inicio</span>
    </a>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $this->asset('assets/js/main.js') ?>"></script>
    <script src="<?= $this->asset('assets/js/components.js?v=' . time()) ?>"></script>
    <script src="<?= $this->asset('assets/js/forms.js') ?>"></script>
    <script src="<?= $this->asset('assets/js/public.js') ?>"></script>
</body>
</html>