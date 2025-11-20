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
    

</head>
<body>
    <div id="app">