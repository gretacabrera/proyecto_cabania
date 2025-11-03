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
<body class="home">
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
    <script src="<?= asset('assets/js/main.js?v=' . time()) ?>"></script>
    <script src="<?= asset('assets/js/components.js?v=' . time()) ?>"></script>
    <script src="<?= asset('assets/js/forms.js') ?>"></script>
    <script src="<?= asset('assets/js/public.js') ?>"></script><?php if (isset($isAdminArea) && $isAdminArea): ?>
    <script src="<?= asset('assets/js/admin.js') ?>"></script><?php endif; ?>
    
    <?php if (isset($showReservaButton) && $showReservaButton): ?>
    <!-- Botón flotante para reservas -->
    <div class="floating-action">
        <button class="btn-float btn-primary" data-action="navegar-reserva" data-url="<?= url('/catalogo') ?>">>
            <i class="fas fa-calendar-plus"></i>
            <span>Nueva Reserva</span>
        </button>
    </div>
    <?php endif; ?>
</body>
</html>