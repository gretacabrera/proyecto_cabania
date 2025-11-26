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
        'port' => getenv('DB_PORT') ?: '3306',
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
        'host' => getenv('MAIL_HOST'),
        'port' => getenv('MAIL_PORT'),
        'username' => getenv('MAIL_USERNAME'),
        'password' => getenv('MAIL_PASSWORD'),
        'encryption' => getenv('MAIL_ENCRYPTION'),
        'from_name' => getenv('MAIL_FROM_NAME'),
    ],
    
    'complejo' => [
        'nombre' => getenv('COMPLEJO_NOMBRE'),
        'direccion' => getenv('COMPLEJO_DIRECCION'),
        'telefono' => getenv('COMPLEJO_TELEFONO'),
        'email' => getenv('COMPLEJO_EMAIL'),
        'politicas' => [
            'check_in' => getenv('COMPLEJO_CHECKIN'),
            'check_out' => getenv('COMPLEJO_CHECKOUT')
        ]
    ],
    
    'mercadopago' => [
        // Credenciales de PRUEBA (pestaña "Credenciales" > "Prueba" en MercadoPago)
        // Formato APP_USR-xxx cuando provienen de una aplicación en modo PRUEBA
        // Solo usar credenciales de PRODUCCIÓN cuando el sistema esté en producción
        'access_token' => getenv('MERCADOPAGO_ACCESS_TOKEN'),
        'public_key' => getenv('MERCADOPAGO_PUBLIC_KEY'),
        'base_url' => getenv('MERCADOPAGO_BASE_URL'),
    ],
    
    'pusher' => [
        'app_id' => getenv('PUSHER_APP_ID'),
        'app_key' => getenv('PUSHER_APP_KEY'),
        'app_secret' => getenv('PUSHER_APP_SECRET'),
        'app_cluster' => getenv('PUSHER_APP_CLUSTER') ?: 'us2',
        'use_tls' => true,
    ],
];