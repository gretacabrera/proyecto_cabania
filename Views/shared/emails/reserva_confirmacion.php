<?php
/**
 * Template unificado para email de confirmación de reserva
 * Genera tanto versión HTML como texto plano según el parámetro $formato
 * 
 * Variables disponibles:
 * - $datos: Array con información de la reserva
 * - $complejo: Array con información del complejo
 * - $formato: 'html' o 'texto' (por defecto: 'html')
 */

// Establecer formato por defecto si no se especifica
if (!isset($formato)) {
    $formato = 'html';
}

// === FUNCIONES HELPER USANDO CLOSURES ===

$generarSaludo = function($datos, $formato) {
    $nombre = htmlspecialchars($datos['huesped_nombre_completo']);
    $fechas = htmlspecialchars($datos['fecha_llegada']) . ' – ' . htmlspecialchars($datos['fecha_salida']);
    $cabania = htmlspecialchars($datos['cabania_nombre']);
    
    if ($formato === 'html') {
        return "<p>Estimad@ <strong>$nombre</strong>,</p>
                <p>Su reserva se realizó con éxito para los días <strong>$fechas</strong> de la cabaña <strong>$cabania</strong>.</p>";
    } else {
        return "Estimad@ $nombre,\n\nSu reserva se realizó con éxito para los días $fechas de la cabaña $cabania.\n";
    }
};

$generarDetallesCompletos = function($datos, $complejo, $formato) {
    // Datos básicos
    $reservaId = htmlspecialchars($datos['reserva_id']);
    $fechaConfirmacion = htmlspecialchars($datos['fecha_confirmacion']);
    $cabaniaNombre = htmlspecialchars($datos['cabania_nombre']);
    $fechaLlegada = htmlspecialchars($datos['fecha_llegada']);
    $fechaSalida = htmlspecialchars($datos['fecha_salida']);
    $diasEstancia = htmlspecialchars($datos['dias_estancia']);
    $adultos = intval($datos['adultos'] ?? 0);
    $menores = intval($datos['menores'] ?? 0);
    $totalHuespedes = $adultos + $menores;
    
    // Fallback si no hay datos de huéspedes específicos o son inválidos
    if ($totalHuespedes == 0 || ($adultos == 0 && $menores == 0)) {
        $adultos = 2; // Valor por defecto mínimo
        $menores = 0;
        $totalHuespedes = 2;
    }
    $monto = number_format($datos['monto_pagado'], 2, ',', '.');
    $metodoPago = htmlspecialchars($datos['metodo_pago']);
    
    // Horarios y políticas
    $checkIn = htmlspecialchars($complejo['politicas']['check_in'] ?? '15:00');
    $checkOut = htmlspecialchars($complejo['politicas']['check_out'] ?? '11:00');
    
    if ($formato === 'html') {
        return "
        <div class='reservation-details'>
            <h3>Detalles de la Reserva</h3>
            
            <div class='detail-row'>
                <span class='detail-label'>Fecha y hora de confirmación:</span>
                <span class='detail-value'>$fechaConfirmacion</span>
            </div>
            
            <div class='detail-row'>
                <span class='detail-label'>Cabaña seleccionada:</span>
                <span class='detail-value'>$cabaniaNombre</span>
            </div>
            
            <div class='detail-row'>
                <span class='detail-label'>Fechas de la estadía:</span>
                <span class='detail-value'>$fechaLlegada - $fechaSalida ($diasEstancia noche" . ($diasEstancia > 1 ? 's' : '') . ")</span>
            </div>
            
            <div class='detail-row'>
                <span class='detail-label'>Cantidad de huéspedes:</span>
                <span class='detail-value'>$totalHuespedes persona" . ($totalHuespedes > 1 ? 's' : '') . " ($adultos adulto" . ($adultos > 1 ? 's' : '') . ($menores > 0 ? ", $menores menor" . ($menores > 1 ? 'es' : '') : '') . ")</span>
            </div>
            
            <div class='detail-row'>
                <span class='detail-label'>Total abonado:</span>
                <span class='detail-value amount'>$$monto</span>
            </div>
            
            <div class='detail-row'>
                <span class='detail-label'>Método de pago:</span>
                <span class='detail-value'>$metodoPago</span>
            </div>
            
            <div class='detail-row'>
                <span class='detail-label'>Horarios check-in / check-out:</span>
                <span class='detail-value'>$checkIn hs / $checkOut hs</span>
            </div>
            
            <div class='detail-row'>
                <span class='detail-label'>Políticas de cancelación:</span>
                <span class='detail-value'>Gratuita hasta 48 hs antes del check-in</span>
            </div>
        </div>";
    } else {
        return "DETALLES DE LA RESERVA:

Fecha y hora de confirmación: $fechaConfirmacion
Cabaña seleccionada: $cabaniaNombre
Fechas de la estadía: $fechaLlegada - $fechaSalida ($diasEstancia noche" . ($diasEstancia > 1 ? 's' : '') . ")
Cantidad de huéspedes: $totalHuespedes persona" . ($totalHuespedes > 1 ? 's' : '') . " ($adultos adulto" . ($adultos > 1 ? 's' : '') . ($menores > 0 ? ", $menores menor" . ($menores > 1 ? 'es' : '') : '') . ")
Total abonado: $$monto
Método de pago: $metodoPago
Horarios check-in / check-out: $checkIn hs / $checkOut hs
Políticas de cancelación: Gratuita hasta 48 hs antes del check-in\n";
    }
};





$generarServiciosAdicionales = function($datos, $formato) {
    // Solo mostrar si hay servicios adicionales
    if (!isset($datos['servicios_adicionales']) || empty($datos['servicios_adicionales'])) {
        return '';
    }
    
    $servicios = $datos['servicios_adicionales'];
    
    if ($formato === 'html') {
        $html = "
        <div class='additional-services'>
            <h3>Servicios Adicionales Incluidos</h3>";
        
        foreach ($servicios as $servicio) {
            $nombre = htmlspecialchars($servicio['nombre']);
            $precio = number_format($servicio['precio'], 2, ',', '.');
            $html .= "<div class='detail-row'>
                        <span class='detail-label'>$nombre</span>
                        <span class='detail-value'>$$precio</span>
                      </div>";
        }
        
        $html .= "</div>";
        return $html;
    } else {
        $texto = "SERVICIOS ADICIONALES INCLUIDOS:\n";
        foreach ($servicios as $servicio) {
            $nombre = $servicio['nombre'];
            $precio = number_format($servicio['precio'], 2, ',', '.');
            $texto .= "- $nombre: $$precio\n";
        }
        return $texto;
    }
};

// === GENERAR CONTENIDO ===

if ($formato === 'html'): ?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Confirmación de Reserva - <?= htmlspecialchars($complejo['nombre']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #2c5530, #4a7c59);
            color: white;
            text-align: center;
            padding: 30px 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .success-badge {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 25px;
            border: 1px solid #c3e6cb;
        }
        .success-badge h2 {
            margin: 0;
            font-size: 24px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .reservation-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #2c5530;
            margin: 20px 0;
        }
        .reservation-details h3 {
            color: #2c5530;
            margin-top: 0;
        }
        .detail-row {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
        }
        .detail-value {
            color: #333;
        }
        .amount {
            font-size: 20px;
            font-weight: bold;
            color: #2c5530;
        }
        .additional-services {
            background-color: #e7f3ff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .additional-services h3 {
            color: #0056b3;
            margin-top: 0;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        
        /* Responsivo para móviles */
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }
            .content {
                padding: 20px;
            }
            .header {
                padding: 20px 15px;
            }
            .header h1 {
                font-size: 24px;
            }
            .detail-row {
                flex-direction: column;
                gap: 5px;
            }
            .amount {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1><?= htmlspecialchars($complejo['nombre']) ?></h1>
            <p>Sistema de Reservas Online</p>
        </div>
        
        <div class='content'>
            <div class='success-badge'>
                <h2>✅ ¡Reserva Confirmada!</h2>
            </div>
            
            <div class='greeting'>
                <?= $generarSaludo($datos, 'html') ?>
            </div>
            
            <?= $generarDetallesCompletos($datos, $complejo, 'html') ?>
            
            <?= $generarServiciosAdicionales($datos, 'html') ?>
            
            <p style='font-size: 18px; color: #2c5530; text-align: center; margin: 30px 0;'>
                <strong>Te esperamos con ansias para que juntos tengamos una experiencia inolvidable.</strong>
            </p>
        </div>
        
        <div class='footer'>
            <p>Este es un email automático, por favor no responder.</p>
            <p>Si tiene consultas, contáctenos a través de nuestros medios oficiales.</p>
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($complejo['nombre']) ?>. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
<?php else: ?>
<?= htmlspecialchars($complejo['nombre']) ?>

Sistema de Reservas Online

✅ ¡RESERVA CONFIRMADA!

<?= $generarSaludo($datos, 'texto') ?>

<?= $generarDetallesCompletos($datos, $complejo, 'texto') ?>

<?= $generarServiciosAdicionales($datos, 'texto') ?>

Te esperamos con ansias para que juntos tengamos una experiencia inolvidable.

Este es un email automático, por favor no responder.
Si tiene consultas, contáctenos a través de nuestros medios oficiales.

© <?= date('Y') ?> <?= htmlspecialchars($complejo['nombre']) ?>. Todos los derechos reservados.
<?php endif; ?>