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
    <link href="<?= $this->asset('assets/css/main.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('assets/css/components.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('assets/css/forms.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('assets/css/admin.css') ?>" rel="stylesheet">
    
    <!-- Chart.js para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript Centralizado -->
    <script src="<?= $this->asset('assets/js/main.js') ?>"></script>
    <script src="<?= $this->asset('assets/js/components.js') ?>"></script>
    <script src="<?= $this->asset('assets/js/forms.js') ?>"></script>
    <script src="<?= $this->asset('assets/js/admin.js') ?>"></script>
</head>
<body>
    <div id="app">