<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error del Servidor - Casa de Palos</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-container {
            max-width: 600px;
            width: 90%;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }
        
        .error-content {
            padding: 3rem 2rem;
            text-align: center;
        }
        
        .error-icon {
            font-size: 4rem;
            color: #f5576c;
            margin-bottom: 1rem;
        }
        
        .error-code {
            font-size: 6rem;
            font-weight: 800;
            color: #f5576c;
            line-height: 1;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .error-title {
            font-size: 2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        
        .error-message {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        
        .error-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            text-align: left;
        }
        
        .error-details h3 {
            color: #dc3545;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        
        .error-details pre {
            background: #343a40;
            color: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 0.9rem;
        }
        
        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
            color: white;
            text-decoration: none;
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 2px solid #e9ecef;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
            color: #495057;
            transform: translateY(-2px);
            text-decoration: none;
        }
        
        .error-footer {
            background: rgba(248, 249, 250, 0.8);
            padding: 1rem;
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        @media (max-width: 576px) {
            .error-code {
                font-size: 4rem;
            }
            
            .error-title {
                font-size: 1.5rem;
            }
            
            .error-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-content">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="error-code">500</div>
            <h1 class="error-title">Error Interno del Servidor</h1>
            <p class="error-message">
                Ha ocurrido un error inesperado en el servidor.<br>
                Por favor, intenta nuevamente más tarde o contacta al administrador del sitio.
            </p>
            
            <?php 
            $config = require_once __DIR__ . '/../../../Core/config.php';
            if (isset($config['app']['debug']) && $config['app']['debug'] && isset($error)): 
            ?>
                <div class="error-details">
                    <h3><i class="fas fa-bug"></i> Detalles del Error (Modo Debug)</h3>
                    <div class="error-message">
                        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php if (isset($trace)): ?>
                        <div class="error-trace">
                            <strong>Stack Trace:</strong>
                            <pre><?php echo htmlspecialchars($trace); ?></pre>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="error-actions">
                <a href="/proyecto_cabania/" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    Volver al Inicio
                </a>
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Volver Atrás
                </a>
            </div>
        </div>
        <div class="error-footer">
            <i class="fas fa-mountain"></i>
            Casa de Palos - Sistema de Gestión
        </div>
    </div>
</body>
</html>