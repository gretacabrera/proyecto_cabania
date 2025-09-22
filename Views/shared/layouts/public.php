<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $this->escape($title) : 'Casa de Palos - Cabañas' ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= isset($description) ? $this->escape($description) : 'Descubre nuestras cabañas en la naturaleza. Reserva tu escapada perfecta en Casa de Palos.' ?>">
    <meta name="keywords" content="cabañas, naturaleza, reservas, turismo rural, Casa de Palos">
    <meta name="author" content="Casa de Palos">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?= isset($title) ? $this->escape($title) : 'Casa de Palos - Cabañas' ?>">
    <meta property="og:description" content="<?= isset($description) ? $this->escape($description) : 'Descubre nuestras cabañas en la naturaleza' ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $this->url($_SERVER['REQUEST_URI']) ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= $this->asset('favicon.ico') ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Estilos CSS Públicos -->
    <link href="<?= $this->asset('assets/css/main.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('assets/css/components.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('assets/css/public.css') ?>" rel="stylesheet">
</head>
<body class="public-layout">
    <!-- Navegación pública -->
    <?php $this->component('public-menu'); ?>
    
    <!-- Contenido principal -->
    <main class="main-content public-content">
        <?= $content ?>
    </main>
    
    <!-- Footer -->
    <?php $this->component('footer'); ?>
    
    <!-- JavaScript Públicos -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $this->asset('assets/js/public.js') ?>"></script>
    
    <!-- Body class para identificar página -->
    <script>
        document.body.classList.add('<?= isset($bodyClass) ? $this->escape($bodyClass) : 'public-page' ?>');
    </script>
</body>
</html>