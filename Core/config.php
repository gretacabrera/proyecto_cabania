<?php
/**
 * Configuración de la aplicación
 */

// Cargar variables de entorno
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Ignorar comentarios
        }
        $parts = explode('=', trim($line), 2);
        if (count($parts) == 2) {
            list($key, $value) = $parts;
            putenv("$key=$value");
        }
    }
}

// Configuración de base de datos
return [
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
        'database' => getenv('DB_SCHEMA') ?: 'proyecto_cabania',
        'charset' => 'utf8',
    ],
    
    'app' => [
        'name' => 'Casa de Palos - Cabañas',
        'url' => getenv('APP_URL') ?: 'http://localhost',
        'debug' => getenv('APP_DEBUG') === 'true',
        'timezone' => 'America/Argentina/Buenos_Aires',
    ],
    
    'session' => [
        'lifetime' => 7200, // 2 horas
        'secure' => false,
        'httponly' => true,
    ],
    
    'pagination' => [
        'per_page' => 10,
    ],
    
    'mail' => [
        'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
        'port' => getenv('MAIL_PORT') ?: 587,
        'username' => getenv('MAIL_USERNAME') ?: '',
        'password' => getenv('MAIL_PASSWORD') ?: '',
        'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
        'from_name' => 'SIRCA Software',
    ],
];