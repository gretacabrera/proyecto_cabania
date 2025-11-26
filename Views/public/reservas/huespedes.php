<?php
// Esta vista ha sido movida a Views/public/huespedes/listado.php
// Redirigir automáticamente
$reserva_id = $reserva_id ?? $_GET['reserva_id'] ?? '';
header('Location: ' . url('/huespedes?reserva_id=' . $reserva_id));
exit;
