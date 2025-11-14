<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? e($title) : 'Tótem de Pedidos - Casa de Palos' ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Estilos personalizados para Totem -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            overflow-x: hidden;
        }
        
        .totem-header {
            background: white;
            padding: 25px;
            margin-bottom: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .totem-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            margin-bottom: 30px;
        }
        
        .btn-totem {
            font-size: 1.2rem;
            padding: 15px 30px;
            font-weight: 600;
            border-radius: 8px;
        }
        
        .producto-totem {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            transition: all 0.3s;
            background: white;
        }
        
        .producto-totem:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #667eea;
        }
        
        .producto-totem.selected {
            border-color: #28a745;
            background: #f0fff4;
        }
        
        .cantidad-control {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .cantidad-display {
            font-size: 1.8rem;
            font-weight: bold;
            min-width: 50px;
            text-align: center;
            color: #333;
        }
        
        .precio-tag {
            color: #28a745;
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .stock-badge {
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <?= $content ?>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
