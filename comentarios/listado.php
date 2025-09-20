<?php
require("conexion.php");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["usuario_nombre"])) {
    echo "Para ver sus comentarios, primero debe iniciar sesión.";
    exit;
}

// Mostrar mensaje si existe
if (function_exists('mostrar_mensaje')) {
    mostrar_mensaje();
}

// Parámetros de paginación
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$registros_por_pagina = 10;

// Parámetros de filtrado solo por POST
$filtro_fecha_desde = isset($_POST['fecha_desde']) ? $_POST['fecha_desde'] : '';
$filtro_fecha_hasta = isset($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : '';
$filtro_puntuacion = isset($_POST['puntuacion']) ? intval($_POST['puntuacion']) : '';

// Construir WHERE clause para filtros
$where_conditions = [
    "c.comentario_estado = 1",
    "u.usuario_nombre = '" . $_SESSION["usuario_nombre"] . "'"
];
if (!empty($filtro_fecha_desde)) {
    $where_conditions[] = "DATE(c.comentario_fechahora) >= '$filtro_fecha_desde'";
}
if (!empty($filtro_fecha_hasta)) {
    $where_conditions[] = "DATE(c.comentario_fechahora) <= '$filtro_fecha_hasta'";
}
if ($filtro_puntuacion > 0 && $filtro_puntuacion <= 5) {
    $where_conditions[] = "c.comentario_puntuacion = $filtro_puntuacion";
}
$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Query para contar registros
$query_count = "SELECT COUNT(*) as total
                        FROM comentario c
                        LEFT JOIN huesped h ON c.rela_huesped = h.id_huesped
                        LEFT JOIN persona p ON h.rela_persona = p.id_persona
                        LEFT JOIN usuario u ON u.rela_persona = p.id_persona
                        LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                        LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
                        $where_clause";

// Query base para los comentarios del usuario actual
$query_base = "SELECT c.id_comentario,
                              c.comentario_fechahora,
                              c.comentario_texto,
                              c.comentario_puntuacion,
                              p.persona_nombre,
                              p.persona_apellido,
                              cab.cabania_nombre,
                              r.reserva_fhinicio,
                              r.reserva_fhfin,
                              c.comentario_estado
                       FROM comentario c
                       LEFT JOIN huesped h ON c.rela_huesped = h.id_huesped
                       LEFT JOIN persona p ON h.rela_persona = p.id_persona
                       LEFT JOIN usuario u ON u.rela_persona = p.id_persona
                       LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                       LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
                       $where_clause
                       ORDER BY c.comentario_fechahora DESC";

// Obtener datos paginados usando la función global
$resultado = obtener_registros_paginados($mysql, $query_base, $query_count, $pagina, $registros_por_pagina);
$registros_data = $resultado['registros'];
$paginacion = $resultado['paginacion'];
?>

<h1>Mis Comentarios</h1>
<?php include("busqueda.php"); ?>

<!-- Información de registros -->
<div class="pagination-info">
    <?php echo mostrar_info_paginacion($paginacion); ?>
</div>

<table>
    <thead>
        <th><font face="Arial">Fecha</font></th>
        <th><font face="Arial">Cabaña</font></th>
        <th><font face="Arial">Estadía</font></th>
        <th><font face="Arial">Puntuación</font></th>
        <th><font face="Arial">Comentario</font></th>
        <th><font face="Arial">Autor</font></th>
        <th><font face="Arial">Acciones</font></th>
    </thead>
    <tbody>
        <?php
        if (empty($registros_data)) {
            echo "<tr><td colspan='7' style='text-align: center; padding: 20px;'>No tienes comentarios registrados</td></tr>";
        } else {
            foreach ($registros_data as $row) {
                echo "<tr>";
                $fecha_comentario = date('d/m/Y H:i', strtotime($row["comentario_fechahora"]));
                $fecha_inicio = date('d/m/Y', strtotime($row["reserva_fhinicio"]));
                $fecha_fin = date('d/m/Y', strtotime($row["reserva_fhfin"]));
                $autor = htmlspecialchars($row['persona_nombre'] . ' ' . $row['persona_apellido']);
                $estrellas = '';
                for ($i = 1; $i <= 5; $i++) {
                    $estrellas .= ($i <= $row['comentario_puntuacion']) ? '★' : '☆';
                }
                echo "<td>$fecha_comentario</td>";
                echo "<td>{$row['cabania_nombre']}</td>";
                echo "<td>$fecha_inicio - $fecha_fin</td>";
                echo "<td>$estrellas ({$row['comentario_puntuacion']}/5)</td>";
                echo "<td style='max-width: 300px; word-wrap: break-word;'>" . nl2br(htmlspecialchars($row['comentario_texto'])) . "</td>";
                echo "<td>$autor</td>";
                echo "<td>";
                echo "<button class='abm-button mod-button' onclick='location.href=\"/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=editar.php&id_comentario=" . $row["id_comentario"] . "\"'>Editar</button>";
                // Mostrar botón Eliminar o Recuperar según el estado
                if ($row["comentario_estado"]) {
                    echo "<button class='abm-button baja-button' onclick='confirmarAccion(\"/proyecto_cabania/comentarios/baja_logica.php?id_comentario=" . $row["id_comentario"] . "\", \"dar de baja este comentario\")'>Eliminar</button>";
                } else {
                    echo "<button class='abm-button alta-button' onclick='confirmarAccion(\"/proyecto_cabania/comentarios/quitar_baja_logica.php?id_comentario=" . $row["id_comentario"] . "\", \"recuperar este comentario\")'>Recuperar</button>";
                }
                echo "</td>";
                echo "</tr>";
            }
        }
        ?>
    </tbody>
</table>

<?php
// Generar enlaces de paginación usando la función estándar
$parametros_url = [];
if (!empty($filtro_fecha_desde)) {
    $parametros_url['fecha_desde'] = $filtro_fecha_desde;
}
if (!empty($filtro_fecha_hasta)) {
    $parametros_url['fecha_hasta'] = $filtro_fecha_hasta;
}
if (!empty($filtro_puntuacion)) {
    $parametros_url['puntuacion'] = $filtro_puntuacion;
}

echo generar_enlaces_paginacion($paginacion, '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios', $parametros_url);

$mysql->close();
?>
