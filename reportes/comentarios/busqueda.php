
<h1>Filtros de búsqueda:</h1>
<?php
$registros_por_pagina = isset($_POST['registros_por_pagina']) ? intval($_POST['registros_por_pagina']) : 10;
$fecha_desde = isset($_POST['fecha_desde']) ? $_POST['fecha_desde'] : '';
$fecha_hasta = isset($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : '';
$puntuacion = isset($_POST['puntuacion']) ? $_POST['puntuacion'] : '';
$cabania = isset($_POST['cabania']) ? $_POST['cabania'] : '';
?>
<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Reporte de comentarios&ruta=reportes/comentarios" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: end;">
    <div class="filtro-item">
        <label>Fecha desde:</label>
        <input type="date" name="fecha_desde" min="2000-01-01" max="2030-12-31" value="<?php echo htmlspecialchars($fecha_desde); ?>">
    </div>
    <div class="filtro-item">
        <label>Fecha hasta:</label>
        <input type="date" name="fecha_hasta" min="2000-01-01" max="2030-12-31" value="<?php echo htmlspecialchars($fecha_hasta); ?>">
    </div>
    <div class="filtro-item">
        <label>Puntuación:</label>
        <select name="puntuacion">
            <option value="">Todas</option>
            <?php for ($i = 5; $i >= 1; $i--) {
                echo "<option value='$i'" . ($puntuacion == $i ? " selected" : "") . ">$i estrella" . ($i > 1 ? 's' : '') . "</option>";
            } ?>
        </select>
    </div>
    <div class="filtro-item">
        <label>Cabaña:</label>
        <select name="cabania">
            <option value="">Todas</option>
            <?php
            $cabania_res = $mysql->query("SELECT id_cabania, cabania_nombre FROM cabania WHERE cabania_estado = 1 ORDER BY cabania_nombre") or die($mysql->error);
            while ($cab = $cabania_res->fetch_assoc()) {
                echo "<option value='{$cab['id_cabania']}'" . ($cabania == $cab['id_cabania'] ? " selected" : "") . ">" . htmlspecialchars($cab['cabania_nombre']) . "</option>";
            }
            ?>
        </select>
    </div>
    <div class="filtro-item">
        <label for="registros_por_pagina">Mostrar:</label>
        <select name="registros_por_pagina" id="registros_por_pagina" onchange="this.form.submit()">
            <option value="10" <?php echo $registros_por_pagina == 10 ? 'selected' : ''; ?>>10 registros</option>
            <option value="25" <?php echo $registros_por_pagina == 25 ? 'selected' : ''; ?>>25 registros</option>
            <option value="50" <?php echo $registros_por_pagina == 50 ? 'selected' : ''; ?>>50 registros</option>
        </select>
    </div>
    <div class="filtro-item">
    <input type="submit" value="Buscar">
    <input type="button" value="Limpiar" onclick="limpiarFormulario(this)">
    </div>
</form>
