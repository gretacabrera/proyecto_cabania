<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->getSection('title') ?? 'Tótem - Cabañas' ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .totem-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .totem-header {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .totem-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        
        .producto-totem {
            transition: all 0.3s;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
        }
        
        .producto-totem:hover {
            transform: scale(1.05);
            border-color: #667eea;
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }
        
        .producto-totem.selected {
            border-color: #28a745;
            background-color: #f0fff4;
        }
        
        .btn-totem {
            font-size: 1.2rem;
            padding: 15px 30px;
            border-radius: 10px;
            font-weight: bold;
        }
        
        .cantidad-control {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .cantidad-control button {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
            border-radius: 50%;
        }
        
        .cantidad-display {
            font-size: 2rem;
            font-weight: bold;
            min-width: 80px;
            text-align: center;
        }
        
        .precio-tag {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="totem-container">
        <?= $this->getSection('content') ?>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <?= $this->getSection('scripts') ?>
</body>
</html>
