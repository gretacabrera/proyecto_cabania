<?php
// Los datos ya vienen preparados desde el controlador
// No necesitamos cargar helpers ni hacer conexiones directas aquí

// Variables que recibimos del controlador:
// $comentarios - array con los comentarios paginados  
// $paginacion - información de paginación
// $filtros_aplicados - filtros actualmente activos

// Configuración de paginación (recibida del controlador)
$registros_por_pagina = $paginacion['registros_por_pagina'] ?? 10;
$pagina_actual = $paginacion['pagina_actual'] ?? 1;
?>

<h1>Mis Comentarios</h1>

<!-- Formulario de búsqueda -->
<div class="search-container">
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" class="search-form">
        <div class="search-fields">
            <div class="form-group">
                <label for="fecha_desde">Fecha desde:</label>
                <input type="date" id="fecha_desde" name="fecha_desde" 
                       min="2000-01-01" max="2030-12-31"
                       value="<?php echo isset($filtros_aplicados['fecha_desde']) ? $filtros_aplicados['fecha_desde'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="fecha_hasta">Fecha hasta:</label>
                <input type="date" id="fecha_hasta" name="fecha_hasta" 
                       min="2000-01-01" max="2030-12-31"
                       value="<?php echo isset($filtros_aplicados['fecha_hasta']) ? $filtros_aplicados['fecha_hasta'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="puntuacion">Puntuación:</label>
                <select name="puntuacion" id="puntuacion">
                    <option value="">Todas</option>
                    <?php
                        for ($i = 5; $i >= 1; $i--) {
                            $selected = (isset($filtros_aplicados['puntuacion']) && $filtros_aplicados['puntuacion'] == $i) ? "selected" : "";
                            echo "<option value='$i' $selected>{$i} estrella" . ($i > 1 ? 's' : '') . "</option>";
                        }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="comentario_estado">Estado:</label>
                <select name="comentario_estado" id="comentario_estado">
                    <option value="">Todos</option>
                    <option value="1" <?php echo (isset($filtros_aplicados['comentario_estado']) && $filtros_aplicados['comentario_estado'] == "1") ? "selected" : ""; ?>>Activo</option>
                    <option value="0" <?php echo (isset($filtros_aplicados['comentario_estado']) && $filtros_aplicados['comentario_estado'] == "0") ? "selected" : ""; ?>>Eliminado</option>
                </select>
            </div>
        </div>
        
        <div class="search-buttons">
            <input type="submit" value="Buscar" class="btn btn-search">
            <button type="button" class="btn btn-clear" data-action="clear-filters">Limpiar</button>
        </div>
    </form>
</div>

<!-- Selector de registros por página -->
<div class="records-selector">
    <form method="get" class="inline-form">
        <!-- Mantener filtros existentes -->
        <?php if (isset($_REQUEST["fecha_desde"]) && $_REQUEST["fecha_desde"] != ""): ?>
            <input type="hidden" name="fecha_desde" value="<?php echo htmlspecialchars($_REQUEST["fecha_desde"]); ?>">
        <?php endif; ?>
        <?php if (isset($_REQUEST["fecha_hasta"]) && $_REQUEST["fecha_hasta"] != ""): ?>
            <input type="hidden" name="fecha_hasta" value="<?php echo htmlspecialchars($_REQUEST["fecha_hasta"]); ?>">
        <?php endif; ?>
        <?php if (isset($_REQUEST["puntuacion"]) && $_REQUEST["puntuacion"] != ""): ?>
            <input type="hidden" name="puntuacion" value="<?php echo htmlspecialchars($_REQUEST["puntuacion"]); ?>">
        <?php endif; ?>
        <?php if (isset($_REQUEST["comentario_estado"]) && $_REQUEST["comentario_estado"] != ""): ?>
            <input type="hidden" name="comentario_estado" value="<?php echo htmlspecialchars($_REQUEST["comentario_estado"]); ?>">
        <?php endif; ?>
        
        <label for="registros_por_pagina">Mostrar:</label>
        <select name="registros_por_pagina" id="registros_por_pagina" data-action="auto-submit">
            <option value="10" <?php echo $registros_por_pagina == 10 ? 'selected' : ''; ?>>10 registros</option>
            <option value="25" <?php echo $registros_por_pagina == 25 ? 'selected' : ''; ?>>25 registros</option>
            <option value="50" <?php echo $registros_por_pagina == 50 ? 'selected' : ''; ?>>50 registros</option>
        </select>
    </form>
</div>

<?php
// Verificar que los datos necesarios estén disponibles
if (!isset($comentarios)) {
    echo "<div class='alert alert-error'>Error: No se pudieron cargar los comentarios.</div>";
    exit;
}

// La verificación de autenticación ya se hizo en el controlador
// Los filtros y queries ya fueron procesados en el controlador
// Solo necesitamos mostrar los datos que nos llegaron
?>

<!-- Información de registros -->
<div class="pagination-info">
    <?php 
    if (isset($paginacion) && is_array($paginacion)) {
        echo "Mostrando registros " . $paginacion['desde'] . " al " . $paginacion['hasta'] . " de " . $paginacion['total_registros'] . " total";
    }
    ?>
</div>

<table class="data-table comments-table">
    <thead>
        <tr>
            <th><font face="Arial">Fecha</font></th>
            <th><font face="Arial">Cabaña</font></th>
            <th><font face="Arial">Estadía</font></th>
            <th><font face="Arial">Puntuación</font></th>
            <th><font face="Arial">Comentario</font></th>
            <th><font face="Arial">Autor</font></th>
            <th><font face="Arial">Acciones</font></th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (empty($comentarios)) {
            echo "<tr><td colspan='7' class='no-results'>No se encontraron comentarios</td></tr>";
        } else {
            foreach ($comentarios as $row) {
                $fecha_comentario = date('d/m/Y H:i', strtotime($row['comentario_fechahora']));
                $fecha_inicio = $row['reserva_fhinicio'] ? date('d/m/Y', strtotime($row['reserva_fhinicio'])) : 'N/A';
                $fecha_fin = $row['reserva_fhfin'] ? date('d/m/Y', strtotime($row['reserva_fhfin'])) : 'N/A';
                
                // Generar estrellas
                $estrellas = str_repeat('★', $row['comentario_puntuacion']) . str_repeat('☆', 5 - $row['comentario_puntuacion']);
                
                $autor = $row['persona_nombre'] . ' ' . $row['persona_apellido'];
                
                echo "<tr>";
                echo "<td>$fecha_comentario</td>";
                echo "<td>" . htmlspecialchars($row['cabania_nombre'] ?? 'N/A') . "</td>";
                echo "<td>$fecha_inicio - $fecha_fin</td>";
                echo "<td><span class='rating-stars'>$estrellas</span> ({$row['comentario_puntuacion']}/5)</td>";
                echo "<td class='comment-text'>" . nl2br(htmlspecialchars($row['comentario_texto'])) . "</td>";
                echo "<td>$autor</td>";
                echo "<td class='actions-cell'>";
                
                echo "<button class='abm-button mod-button' data-action='edit' data-id='".$row["id_comentario"]."'>Editar</button>";
                
                // Mostrar botón según estado
                if ($row["comentario_estado"]) {
                    echo "<button class='abm-button baja-button' data-action='delete' data-id='".$row["id_comentario"]."' data-message='dar de baja este comentario'>Eliminar</button>";
                } else {
                    echo "<button class='abm-button alta-button' data-action='restore' data-id='".$row["id_comentario"]."' data-message='recuperar este comentario'>Recuperar</button>";
                }
                
                echo "</td>";
                echo "</tr>";
            }
        }
        ?>
    </tbody>
</table>

<?php
// Los enlaces de paginación también vienen preparados del controlador
if (isset($paginacion['enlaces_paginacion'])) {
    echo $paginacion['enlaces_paginacion'];
} else if (isset($paginacion['total_paginas']) && $paginacion['total_paginas'] > 1) {
    // Generar enlaces básicos si no vienen del controlador
    echo "<div class='pagination'>";
    for ($i = 1; $i <= $paginacion['total_paginas']; $i++) {
        $active = ($i == $paginacion['pagina_actual']) ? ' class="active"' : '';
        $url = $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') ? '&' : '?') . "pagina=$i";
        echo "<a href='$url'$active>$i</a>";
    }
    echo "</div>";
}
?>

<?php $this->endSection(); ?>