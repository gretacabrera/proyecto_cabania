<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : 'Casa de Palos - Cabañas' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <!-- Estilos CSS Centralizados -->
    <link href="<?= $this->asset('assets/css/main.css?v=' . time()) ?>" rel="stylesheet">
    <link href="<?= $this->asset('assets/css/components.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('assets/css/forms.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('assets/css/admin.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('assets/css/notifications.css') ?>" rel="stylesheet">
    
    <!-- Chart.js para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Pusher para notificaciones en tiempo real -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript Centralizado -->
    <script src="<?= $this->asset('assets/js/main.js') ?>"></script>
    <script src="<?= $this->asset('assets/js/components.js?v=' . time()) ?>"></script>
    <script src="<?= $this->asset('assets/js/forms.js') ?>"></script>
    <script src="<?= $this->asset('assets/js/admin.js') ?>"></script>
    <script src="<?= $this->asset('assets/js/notifications.js') ?>"></script>
    
    <!-- Configuración de notificaciones Pusher -->
    <?php 
    // Inicializar Pusher solo para usuarios huéspedes autenticados
    $userProfile = isset($_SESSION['perfil_nombre']) ? strtolower($_SESSION['perfil_nombre']) : '';
    $isHuesped = ($userProfile === 'huesped');
    ?>
    <?php if (isset($_SESSION['usuario_id']) && $isHuesped): ?>
    <script>
        // Inicializar Pusher para usuarios huéspedes
        document.addEventListener('DOMContentLoaded', function() {
            <?php
            $config = require __DIR__ . '/../../../Core/config.php';
            $pusherKey = $config['pusher']['app_key'] ?? '';
            $pusherCluster = $config['pusher']['app_cluster'] ?? 'us2';
            $userId = $_SESSION['usuario_id'] ?? null;
            ?>
            
            <?php if (!empty($pusherKey) && $userId): ?>
                NotificationService.init('<?= $pusherKey ?>', '<?= $pusherCluster ?>', <?= $userId ?>);
            <?php else: ?>
                console.log('Pusher no configurado - Notificaciones en tiempo real deshabilitadas');
            <?php endif; ?>
        });
    </script>
    <?php endif; ?>
</head>
<body>
    <div id="app">