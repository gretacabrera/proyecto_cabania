<?php
// Los datos ya vienen preparados desde el controlador
// Variables disponibles:
// $servicios - array con los servicios paginados
// $paginacion - información de paginación  
// $tipos_servicio - tipos de servicio para el select
// $filtros_aplicados - filtros actualmente activos

// Configuración de paginación (recibida del controlador)
$registros_por_pagina = $paginacion['registros_por_pagina'] ?? 10;
$pagina_actual = $paginacion['pagina_actual'] ?? 1;
?>

<h1>Gestión de Servicios</h1>

<!-- Formulario de búsqueda -->
<div class="search-container">
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" class="search-form">
        <div class="search-fields">
            <div class="form-group">
                <label for="servicio_nombre">Nombre del servicio:</label>
                <input type="text" id="servicio_nombre" name="servicio_nombre" 
                       value="<?php echo isset($filtros_aplicados["servicio_nombre"]) ? htmlspecialchars($filtros_aplicados["servicio_nombre"]) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="servicio_descripcion">Descripción:</label>
                <input type="text" id="servicio_descripcion" name="servicio_descripcion" 
                       value="<?php echo isset($filtros_aplicados["servicio_descripcion"]) ? htmlspecialchars($filtros_aplicados["servicio_descripcion"]) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="rela_tiposervicio">Tipo de Servicio:</label>
                <select name="rela_tiposervicio" id="rela_tiposervicio">
                    <option value="">Seleccione un tipo...</option>
                    <?php
                        if (isset($tipos_servicio) && is_array($tipos_servicio)) {
                            foreach ($tipos_servicio as $tipo) {
                                $selected = (isset($filtros_aplicados["rela_tiposervicio"]) && $filtros_aplicados["rela_tiposervicio"] == $tipo["id_tiposervicio"]) ? "selected" : "";
                                echo "<option value='".$tipo["id_tiposervicio"]."' $selected>".htmlspecialchars($tipo["tiposervicio_descripcion"])."</option>";
                            }
                        }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="servicio_estado">Estado:</label>
                <select name="servicio_estado" id="servicio_estado">
                    <option value="">Todos los estados</option>
                    <option value="1" <?php echo (isset($filtros_aplicados["servicio_estado"]) && $filtros_aplicados["servicio_estado"] == "1") ? "selected" : ""; ?>>Activo</option>
                    <option value="0" <?php echo (isset($filtros_aplicados["servicio_estado"]) && $filtros_aplicados["servicio_estado"] == "0") ? "selected" : ""; ?>>Inactivo</option>
                </select>
            </div>
        </div>
        
        <div class="search-buttons">
            <input type="submit" value="Buscar" class="btn btn-search">
            <button type="button" onclick="limpiarFiltros()" class="btn btn-clear">Limpiar</button>
        </div>
    </form>
</div>

<!-- Botones de acción -->
<div class="botonera-abm">
    <button class="abm-button alta-button" onclick="window.location.href='/proyecto_cabania/servicios/create'">Nuevo Servicio</button>
</div>

<!-- Selector de registros por página -->
<div class="form-controls-container">
    <form method="get" class="inline-form">
        <!-- Mantener filtros existentes -->
        <?php if (isset($filtros_aplicados["servicio_nombre"]) && $filtros_aplicados["servicio_nombre"] != ""): ?>
            <input type="hidden" name="servicio_nombre" value="<?php echo htmlspecialchars($filtros_aplicados["servicio_nombre"]); ?>">
        <?php endif; ?>
        <?php if (isset($filtros_aplicados["servicio_descripcion"]) && $filtros_aplicados["servicio_descripcion"] != ""): ?>
            <input type="hidden" name="servicio_descripcion" value="<?php echo htmlspecialchars($filtros_aplicados["servicio_descripcion"]); ?>">
        <?php endif; ?>
        <?php if (isset($filtros_aplicados["rela_tiposervicio"]) && $filtros_aplicados["rela_tiposervicio"] != ""): ?>
            <input type="hidden" name="rela_tiposervicio" value="<?php echo htmlspecialchars($filtros_aplicados["rela_tiposervicio"]); ?>">
        <?php endif; ?>
        <?php if (isset($filtros_aplicados["servicio_estado"]) && $filtros_aplicados["servicio_estado"] != ""): ?>
            <input type="hidden" name="servicio_estado" value="<?php echo htmlspecialchars($filtros_aplicados["servicio_estado"]); ?>">
        <?php endif; ?>
        
        <label for="registros_por_pagina">Mostrar:</label>
        <select name="registros_por_pagina" id="registros_por_pagina" onchange="this.form.submit()">
            <option value="10" <?php echo $registros_por_pagina == 10 ? 'selected' : ''; ?>>10 registros</option>
            <option value="25" <?php echo $registros_por_pagina == 25 ? 'selected' : ''; ?>>25 registros</option>
            <option value="50" <?php echo $registros_por_pagina == 50 ? 'selected' : ''; ?>>50 registros</option>
        </select>
    </form>
</div>

<?php
// Verificar que los datos necesarios estén disponibles  
if (!isset($servicios)) {
    echo "<div class='alert alert-error'>Error: No se pudieron cargar los servicios.</div>";
    exit;
}

// Los filtros, queries y permisos ya fueron procesados en el controlador
// Solo necesitamos mostrar los datos que nos llegaron
?>

<!-- Información de registros -->
<div class="pagination-info">
    <?php echo mostrar_info_paginacion($paginacion); ?>
</div>

<table class="data-table"> 
    <thead>
        <tr>
            <th><font face="Arial">Nombre</font></th>
            <th><font face="Arial">Descripción</font></th>
            <th><font face="Arial">Tipo de Servicio</font></th>
            <th><font face="Arial">Precio</font></th>
            <th><font face="Arial">Estado</font></th>
            <th><font face="Arial">Acciones</font></th>
        </tr>
    </thead>
    <tbody>
    <?php
    if (empty($servicios)) {
        echo "<tr><td colspan='6' class='no-records-message'>No se encontraron servicios</td></tr>";
    } else {
        foreach ($servicios as $row) {
            echo "<tr>";
            echo "<td>".htmlspecialchars($row["servicio_nombre"])."</td>";
            echo "<td>".htmlspecialchars($row["servicio_descripcion"])."</td>";
            echo "<td>".htmlspecialchars($row["tiposervicio_descripcion"] ?? 'No especificado')."</td>";
            echo "<td>$".number_format($row["servicio_precio"], 2)."</td>";
            echo "<td>".($row["servicio_estado"] ? "Activo" : "Inactivo")."</td>";
            
            echo "<td>";
            echo "<button class='abm-button mod-button' onclick='window.location.href=\"/proyecto_cabania/servicios/".$row["id_servicio"]."/edit\"'>Editar</button>";
            
            // Mostrar botones según estado y permisos
            if ($row["servicio_estado"]) {
                if (es_administrador()) {
                    echo "<button class='abm-button baja-button' onclick='confirmarAccion(\"/proyecto_cabania/servicios/".$row["id_servicio"]."/delete\", \"dar de baja este servicio\")'>Eliminar</button>";
                }
            } else {
                if (es_administrador()) {
                    echo "<button class='abm-button alta-button' onclick='confirmarAccion(\"/proyecto_cabania/servicios/".$row["id_servicio"]."/restore\", \"recuperar este servicio\")'>Recuperar</button>";
                }
            }
            
            echo "</td>";
            echo "</tr>";
        }
    }
    ?>
    </tbody>
</table>

<?php
// Generar enlaces de paginación
$parametros_url = [];
if (isset($_REQUEST["servicio_nombre"]) && $_REQUEST["servicio_nombre"] != "") {
    $parametros_url['servicio_nombre'] = $_REQUEST["servicio_nombre"];
}
if (isset($_REQUEST["servicio_descripcion"]) && $_REQUEST["servicio_descripcion"] != "") {
    $parametros_url['servicio_descripcion'] = $_REQUEST["servicio_descripcion"];
}
if (isset($_REQUEST["rela_tiposervicio"]) && $_REQUEST["rela_tiposervicio"] != "") {
    $parametros_url['rela_tiposervicio'] = $_REQUEST["rela_tiposervicio"];
}
if (isset($_REQUEST["servicio_estado"]) && $_REQUEST["servicio_estado"] != "") {
    $parametros_url['servicio_estado'] = $_REQUEST["servicio_estado"];
}
if (isset($_REQUEST["registros_por_pagina"]) && $_REQUEST["registros_por_pagina"] != "") {
    $parametros_url['registros_por_pagina'] = $_REQUEST["registros_por_pagina"];
}

echo generar_enlaces_paginacion($paginacion, $_SERVER['REQUEST_URI'], $parametros_url);

$mysql->close();
?>

<?php $this->endSection(); ?>