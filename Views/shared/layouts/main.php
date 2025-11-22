<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? e($title) : 'Casa de Palos - Cabañas' ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Estilos CSS Centralizados -->
    <link href="<?= asset('assets/css/main.css?v=' . time()) ?>" rel="stylesheet">
    <link href="<?= asset('assets/css/components.css') ?>" rel="stylesheet">
    <link href="<?= asset('assets/css/forms.css') ?>" rel="stylesheet">
    <link href="<?= asset('assets/css/public.css') ?>" rel="stylesheet">
    <link href="<?= asset('assets/css/dashboard.css') ?>" rel="stylesheet"><?php if (isset($isAdminArea) && $isAdminArea): ?>
    <link href="<?= asset('assets/css/admin.css') ?>" rel="stylesheet"><?php endif; ?>
    <!-- Override para mensajes minimalistas -->
    <link href="<?= asset('assets/css/messages-override.css?v=' . time()) ?>" rel="stylesheet">
</head>
<body class="<?= isset($isAdminArea) && $isAdminArea ? 'admin-area' : 'home' ?>">
    <!-- Navegación moderna -->
    <?php $this->component('menu'); ?>
    
    <!-- Contenido principal -->
    <main class="main-content">
        <?php $this->component('messages'); ?>
        <?= $content ?>
    </main>
    
    <!-- Footer moderno -->
    <?php require_once __DIR__ . '/footer.php'; ?>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="<?= asset('assets/js/main.js?v=' . time()) ?>"></script>
    <script src="<?= asset('assets/js/components.js?v=' . time()) ?>"></script>
    <script src="<?= asset('assets/js/forms.js') ?>"></script>
    <script src="<?= asset('assets/js/public.js') ?>"></script>
    <script src="<?= asset('assets/js/notifications.js?v=3.0.7') ?>"></script><?php if (isset($isAdminArea) && $isAdminArea): ?>
    <script src="<?= asset('assets/js/admin.js') ?>"></script><?php endif; ?>
    
    <!-- Configuración de notificaciones Pusher -->
    <?php 
    // Inicializar Pusher solo para usuarios huéspedes autenticados
    $userProfile = isset($_SESSION['perfil_nombre']) ? strtolower($_SESSION['perfil_nombre']) : '';
    $isHuesped = ($userProfile === 'huesped' || $userProfile === 'huésped');
    $userId = $_SESSION['usuario_id'] ?? null;
    
    // Debug: mostrar información en consola
    echo "<!-- Debug Pusher -->\n";
    echo "<!-- Usuario ID: " . ($userId ?? 'NO DEFINIDO') . " -->\n";
    echo "<!-- Perfil: " . ($userProfile ?: 'NO DEFINIDO') . " -->\n";
    echo "<!-- Es Huésped: " . ($isHuesped ? 'SÍ' : 'NO') . " -->\n";
    echo "<!-- Sesión activa: " . (isset($_SESSION['usuario_id']) ? 'SÍ' : 'NO') . " -->\n";
    ?>
    <?php if (isset($_SESSION['usuario_id']) && $isHuesped): ?>
    <script>
        // Inicializar Pusher para usuarios huéspedes
        document.addEventListener('DOMContentLoaded', function() {
            <?php
            $config = require __DIR__ . '/../../../Core/config.php';
            $pusherKey = $config['pusher']['app_key'] ?? '';
            $pusherCluster = $config['pusher']['app_cluster'] ?? 'us2';
            ?>
            
            <?php if (!empty($pusherKey) && $userId): ?>
                if (typeof NotificationService !== 'undefined') {
                    console.log('🔔 Iniciando Pusher...');
                    
                    // Registrar callback para cuando la suscripción esté lista
                    NotificationService.onSubscriptionReady(function() {
                        console.log('📡 Suscripción confirmada. Verificando pagos pendientes...');
                        
                        // Llamar al endpoint que verifica pagos pendientes
                        fetch('<?= url('/api/verificar-pagos-pendientes') ?>', {
                            method: 'POST',
                            credentials: 'include',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }).then(response => response.json())
                          .then(data => {
                              if (data.success) {
                                  console.log('✅ Verificación de pagos pendientes completada');
                              } else {
                                  console.warn('⚠️ Error en verificación:', data.message);
                              }
                          })
                          .catch(error => console.error('❌ Error verificando pagos:', error));
                    });
                    
                    // Inicializar Pusher (el callback se ejecutará cuando esté listo)
                    NotificationService.init('<?= $pusherKey ?>', '<?= $pusherCluster ?>', <?= $userId ?>);
                }
            <?php endif; ?>
        });
    </script>
    <?php endif; ?>

</body>
</html>