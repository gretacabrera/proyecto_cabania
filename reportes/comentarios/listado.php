<?php

// Parámetros de filtrado
$registros_por_pagina = isset($_POST['registros_por_pagina']) ? intval($_POST['registros_por_pagina']) : 10;
$pagina_actual = isset($_POST['pagina']) ? intval($_POST['pagina']) : 1;
$filtro_fecha_desde = isset($_POST['fecha_desde']) ? $_POST['fecha_desde'] : '';
$filtro_fecha_hasta = isset($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : '';
$filtro_puntuacion = isset($_POST['puntuacion']) ? intval($_POST['puntuacion']) : '';
$filtro_cabania = isset($_POST['cabania']) ? intval($_POST['cabania']) : '';

// Construir filtros
$where_conditions = ["c.comentario_estado = 1"];
if (!empty($filtro_fecha_desde)) {
    $where_conditions[] = "DATE(c.comentario_fechahora) >= '$filtro_fecha_desde'";
}
if (!empty($filtro_fecha_hasta)) {
    $where_conditions[] = "DATE(c.comentario_fechahora) <= '$filtro_fecha_hasta'";
}
if ($filtro_puntuacion > 0 && $filtro_puntuacion <= 5) {
    $where_conditions[] = "c.comentario_puntuacion = $filtro_puntuacion";
}
if ($filtro_cabania > 0) {
    $where_conditions[] = "cab.id_cabania = $filtro_cabania";
}
$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Query para contar total de registros
$query_count = "SELECT COUNT(*) FROM (
    SELECT c.id_comentario
    FROM comentario c
    LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
    LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
    LEFT JOIN huesped h ON c.rela_huesped = h.id_huesped
    LEFT JOIN persona p ON h.rela_persona = p.id_persona
    $where_clause
    GROUP BY c.id_comentario
) as subquery";

// Query base para obtener registros
$query_base = "SELECT c.comentario_fechahora, c.comentario_texto, c.comentario_puntuacion, cab.cabania_nombre, p.persona_nombre, p.persona_apellido
    FROM comentario c
    LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
    LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
    LEFT JOIN huesped h ON c.rela_huesped = h.id_huesped
    LEFT JOIN persona p ON h.rela_persona = p.id_persona
    $where_clause
    ORDER BY c.comentario_fechahora DESC";

$resultado = obtener_registros_paginados($mysql, $query_base, $query_count, $pagina_actual, $registros_por_pagina);
$registros_data = $resultado['registros'];
$paginacion = $resultado['paginacion'];
?>

<h1>Reporte de Comentarios</h1>
<?php include("busqueda.php"); ?>

<!-- Información de registros -->
<div class="pagination-info">
    <?php echo mostrar_info_paginacion($paginacion); ?>
</div>

<div class="export">
    <input type="button" onclick="tableToExcel('tableResultados','Reporte_de_Comentarios')" value="Exportar a Excel">
</div>

<table id="tableResultados" border="1" cellpadding="5" style="margin-top:20px;">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Cabaña</th>
            <th>Puntuación</th>
            <th>Comentario</th>
            <th>Autor</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (empty($registros_data)) {
            echo "<tr><td colspan='5' style='text-align:center;'>No hay comentarios para los filtros seleccionados</td></tr>";
        } else {
            foreach ($registros_data as $row) {
                $fecha = date('d/m/Y H:i', strtotime($row['comentario_fechahora']));
                $estrellas = str_repeat('★', $row['comentario_puntuacion']) . str_repeat('☆', 5 - $row['comentario_puntuacion']);
                $autor = htmlspecialchars($row['persona_nombre'] . ' ' . $row['persona_apellido']);
                echo "<tr>";
                echo "<td>$fecha</td>";
                echo "<td>" . htmlspecialchars($row['cabania_nombre']) . "</td>";
                echo "<td>$estrellas ({$row['comentario_puntuacion']}/5)</td>";
                echo "<td style='max-width:300px;word-wrap:break-word;'>" . nl2br(htmlspecialchars($row['comentario_texto'])) . "</td>";
                echo "<td>$autor</td>";
                echo "</tr>";
            }
        }
        ?>
    </tbody>
</table>

<?php
// Generar enlaces de paginación
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
if (!empty($filtro_cabania)) {
    $parametros_url['cabania'] = $filtro_cabania;
}
if ($registros_por_pagina != 10) {
    $parametros_url['registros_por_pagina'] = $registros_por_pagina;
}

echo generar_enlaces_paginacion($paginacion, '/proyecto_cabania/plantilla_modulo.php?titulo=Reporte de comentarios&ruta=reportes/comentarios', $parametros_url);
$mysql->close();
?>
