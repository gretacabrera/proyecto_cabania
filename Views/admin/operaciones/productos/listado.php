<?php
// Cargar funciones helper para compatibilidad legacy
require_once __DIR__ . '/../../Core/helpers.php';

// Configuración de paginación
$registros_por_pagina = isset($_REQUEST['registros_por_pagina']) ? intval($_REQUEST['registros_por_pagina']) : 10;
$pagina_actual = isset($_REQUEST['pagina']) ? intval($_REQUEST['pagina']) : 1;
?>

<h1>Gestión de Productos</h1>

<!-- Formulario de búsqueda -->
<div class="search-container">
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" class="search-form">
        <div class="search-fields">
            <div class="form-group">
                <label for="producto_nombre">Nombre del producto:</label>
                <input type="text" id="producto_nombre" name="producto_nombre" 
                       value="<?php echo isset($_REQUEST["producto_nombre"]) ? htmlspecialchars($_REQUEST["producto_nombre"]) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="rela_categoria">Categoría:</label>
                <select name="rela_categoria" id="rela_categoria">
                    <option value="">Seleccione una categoría...</option>
                    <?php
                        include(dirname(__FILE__) . '/../../../conexion.php');
                        $registros = $mysql->query("SELECT * FROM categoria WHERE categoria_estado = 1 ORDER BY categoria_descripcion") or die($mysql->error);
                        while ($row = $registros->fetch_assoc()) {
                            $selected = (isset($_REQUEST["rela_categoria"]) && $_REQUEST["rela_categoria"] == $row["id_categoria"]) ? "selected" : "";
                            echo "<option value='".$row["id_categoria"]."' $selected>".$row["categoria_descripcion"]."</option>";
                        }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="rela_marca">Marca:</label>
                <select name="rela_marca" id="rela_marca">
                    <option value="">Seleccione una marca...</option>
                    <?php
                        $registros = $mysql->query("SELECT * FROM marca WHERE marca_estado = 1 ORDER BY marca_descripcion") or die($mysql->error);
                        while ($row = $registros->fetch_assoc()) {
                            $selected = (isset($_REQUEST["rela_marca"]) && $_REQUEST["rela_marca"] == $row["id_marca"]) ? "selected" : "";
                            echo "<option value='".$row["id_marca"]."' $selected>".$row["marca_descripcion"]."</option>";
                        }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="rela_estadoproducto">Estado:</label>
                <select name="rela_estadoproducto" id="rela_estadoproducto">
                    <option value="">Seleccione un estado...</option>
                    <?php
                        $registros = $mysql->query("SELECT * FROM estadoproducto WHERE estadoproducto_estado = 1 ORDER BY estadoproducto_descripcion") or die($mysql->error);
                        while ($row = $registros->fetch_assoc()) {
                            $selected = (isset($_REQUEST["rela_estadoproducto"]) && $_REQUEST["rela_estadoproducto"] == $row["id_estadoproducto"]) ? "selected" : "";
                            echo "<option value='".$row["id_estadoproducto"]."' $selected>".$row["estadoproducto_descripcion"]."</option>";
                        }
                    ?>
                </select>
            </div>
        </div>
        
        <div class="search-buttons">
            <input type="submit" value="Buscar" class="btn btn-search">
            <button type="button" data-action="limpiar-filtros-productos" class="btn btn-clear">Limpiar</button>
        </div>
    </form>
</div>

<!-- Botones de acción -->
<div class="botonera-abm">
    <button class="abm-button alta-button" data-action="navegar-crear-producto">Nuevo Producto</button>
</div>

<!-- Selector de registros por página -->
<div class="form-controls-container">
    <form method="get" class="inline-form">
        <!-- Mantener filtros existentes -->
        <?php if (isset($_REQUEST["producto_nombre"]) && $_REQUEST["producto_nombre"] != ""): ?>
            <input type="hidden" name="producto_nombre" value="<?php echo htmlspecialchars($_REQUEST["producto_nombre"]); ?>">
        <?php endif; ?>
        <?php if (isset($_REQUEST["rela_categoria"]) && $_REQUEST["rela_categoria"] != ""): ?>
            <input type="hidden" name="rela_categoria" value="<?php echo htmlspecialchars($_REQUEST["rela_categoria"]); ?>">
        <?php endif; ?>
        <?php if (isset($_REQUEST["rela_marca"]) && $_REQUEST["rela_marca"] != ""): ?>
            <input type="hidden" name="rela_marca" value="<?php echo htmlspecialchars($_REQUEST["rela_marca"]); ?>">
        <?php endif; ?>
        <?php if (isset($_REQUEST["rela_estadoproducto"]) && $_REQUEST["rela_estadoproducto"] != ""): ?>
            <input type="hidden" name="rela_estadoproducto" value="<?php echo htmlspecialchars($_REQUEST["rela_estadoproducto"]); ?>">
        <?php endif; ?>
        
        <label for="registros_por_pagina">Mostrar:</label>
        <select name="registros_por_pagina" id="registros_por_pagina">
            <option value="10" <?php echo $registros_por_pagina == 10 ? 'selected' : ''; ?>>10 registros</option>
            <option value="25" <?php echo $registros_por_pagina == 25 ? 'selected' : ''; ?>>25 registros</option>
            <option value="50" <?php echo $registros_por_pagina == 50 ? 'selected' : ''; ?>>50 registros</option>
        </select>
    </form>
</div>

<?php
// Procesar filtros de búsqueda
include_once(dirname(__FILE__) . '/../../../funciones.php');

$filtro = "";
$where_clause = "FROM producto 
                LEFT JOIN marca ON rela_marca = id_marca
                LEFT JOIN categoria ON rela_categoria = id_categoria
                LEFT JOIN estadoproducto ON rela_estadoproducto = id_estadoproducto
                WHERE 1=1";

// Aplicar filtros según permisos de usuario
if (!es_administrador()) {
    $filtro .= " AND rela_estadoproducto != 4 ";
}

if (isset($_REQUEST["producto_nombre"]) && $_REQUEST["producto_nombre"] != "") {
    $filtro .= " AND producto_nombre LIKE '%".mysqli_real_escape_string($mysql, $_REQUEST["producto_nombre"])."%' ";
}
if (isset($_REQUEST["rela_categoria"]) && $_REQUEST["rela_categoria"] != "") {
    $filtro .= " AND rela_categoria = ".intval($_REQUEST["rela_categoria"])." ";
}
if (isset($_REQUEST["rela_marca"]) && $_REQUEST["rela_marca"] != "") {
    $filtro .= " AND rela_marca = ".intval($_REQUEST["rela_marca"])." ";
}
if (isset($_REQUEST["rela_estadoproducto"]) && $_REQUEST["rela_estadoproducto"] != "") {
    $filtro .= " AND rela_estadoproducto = ".intval($_REQUEST["rela_estadoproducto"])." ";
}

// Query para contar registros
$query_count = "SELECT COUNT(*) as total " . $where_clause . " " . $filtro;
$query_base = "SELECT producto.*, marca.marca_descripcion, categoria.categoria_descripcion, estadoproducto.estadoproducto_descripcion 
              " . $where_clause . " " . $filtro . "
              ORDER BY producto_nombre ASC";

// Obtener registros paginados
$resultado = obtener_registros_paginados($mysql, $query_base, $query_count, $pagina_actual, $registros_por_pagina);
$productos = $resultado['registros'];
$paginacion = $resultado['paginacion'];
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
            <th><font face="Arial">Categoría</font></th>
            <th><font face="Arial">Marca</font></th>
            <th><font face="Arial">Precio Unitario</font></th>
            <th><font face="Arial">Stock</font></th>
            <th><font face="Arial">Foto</font></th>
            <th><font face="Arial">Estado</font></th>
            <th><font face="Arial">Acciones</font></th>
        </tr>
    </thead>
    <tbody>
    <?php
    if (empty($productos)) {
        echo "<tr><td colspan='9' class='no-records-message'>No se encontraron productos</td></tr>";
    } else {
        foreach ($productos as $row) {
            echo "<tr>";
            echo "<td>".$row["producto_nombre"]."</td>";
            echo "<td>".$row["producto_descripcion"]."</td>";
            echo "<td>".$row["categoria_descripcion"]."</td>";
            echo "<td>".$row["marca_descripcion"]."</td>";
            echo "<td>$".$row["producto_precio"]."</td>";
            echo "<td>".$row["producto_stock"]."</td>";
            echo "<td>".($row["producto_foto"] ? "<img src='/proyecto_cabania/imagenes/productos/".$row["producto_foto"]."' width='50' height='50'>" : "Sin foto")."</td>";
            echo "<td>".$row["estadoproducto_descripcion"]."</td>";
            
            echo "<td>";
            echo "<button class='abm-button mod-button' data-action='navegar-editar-producto' data-id='".$row["id_producto"]."'>Editar</button>";
            
            // Mostrar botones según estado y permisos
            if ($row["rela_estadoproducto"] == 4) {
                // Si está de baja (estado 4) y es administrador, mostrar botón Recuperar
                if (es_administrador()) {
                    echo "<button class='abm-button alta-button' data-action='confirmar-accion-producto' data-url='/proyecto_cabania/productos/".$row["id_producto"]."/restore' data-accion='recuperar este producto'>Recuperar</button>";
                }
            } else {
                // Si está activo, mostrar botón Eliminar
                echo "<button class='abm-button baja-button' data-action='confirmar-accion-producto' data-url='/proyecto_cabania/productos/".$row["id_producto"]."/delete' data-accion='dar de baja este producto'>Eliminar</button>";
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
if (isset($_REQUEST["producto_nombre"]) && $_REQUEST["producto_nombre"] != "") {
    $parametros_url['producto_nombre'] = $_REQUEST["producto_nombre"];
}
if (isset($_REQUEST["rela_categoria"]) && $_REQUEST["rela_categoria"] != "") {
    $parametros_url['rela_categoria'] = $_REQUEST["rela_categoria"];
}
if (isset($_REQUEST["rela_marca"]) && $_REQUEST["rela_marca"] != "") {
    $parametros_url['rela_marca'] = $_REQUEST["rela_marca"];
}
if (isset($_REQUEST["rela_estadoproducto"]) && $_REQUEST["rela_estadoproducto"] != "") {
    $parametros_url['rela_estadoproducto'] = $_REQUEST["rela_estadoproducto"];
}
if (isset($_REQUEST["registros_por_pagina"]) && $_REQUEST["registros_por_pagina"] != "") {
    $parametros_url['registros_por_pagina'] = $_REQUEST["registros_por_pagina"];
}

echo generar_enlaces_paginacion($paginacion, $_SERVER['REQUEST_URI'], $parametros_url);

$mysql->close();
?>

<?php $this->endSection(); ?>