<?php
// Obtener datos para el formulario
include_once(dirname(__FILE__) . '/../../../conexion.php');

$producto = null;
$isEdit = false;

// Si es edición, obtener datos del producto
if (isset($_REQUEST['id_producto']) && !empty($_REQUEST['id_producto'])) {
    $id_producto = intval($_REQUEST['id_producto']);
    $registro = $mysql->query("SELECT * FROM producto WHERE id_producto = $id_producto") or die($mysql->error);
    
    if ($reg = $registro->fetch_array()) {
        $producto = $reg;
        $isEdit = true;
    } else {
        echo '<div class="alert alert-error">No existe un producto con ese ID</div>';
        exit;
    }
}

$pageTitle = $isEdit ? 'Editar Producto' : 'Nuevo Producto';
$actionUrl = $isEdit ? "/proyecto_cabania/productos/{$id_producto}/update" : "/proyecto_cabania/productos/store";
?>

<h1><?php echo $pageTitle; ?></h1>

<form method="post" action="<?php echo $actionUrl; ?>" enctype="multipart/form-data" class="form-container">
    <fieldset>
        <legend><?php echo $pageTitle; ?></legend>
        
        <div class="form-group">
            <label for="producto_nombre">Nombre del Producto: <span class="required">*</span></label>
            <input type="text" id="producto_nombre" name="producto_nombre" size="50" 
                   value="<?php echo $isEdit ? htmlspecialchars($producto['producto_nombre']) : ''; ?>" 
                   required maxlength="100">
        </div>

        <div class="form-group">
            <label for="producto_descripcion">Descripción: <span class="required">*</span></label>
            <textarea id="producto_descripcion" name="producto_descripcion" rows="3" cols="50" 
                      required maxlength="255"><?php echo $isEdit ? htmlspecialchars($producto['producto_descripcion']) : ''; ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="producto_precio">Precio Unitario: <span class="required">*</span></label>
                <input type="number" id="producto_precio" name="producto_precio" step="0.01" min="0" 
                       value="<?php echo $isEdit ? $producto['producto_precio'] : ''; ?>" 
                       required>
            </div>

            <div class="form-group">
                <label for="producto_stock">Stock:</label>
                <input type="number" id="producto_stock" name="producto_stock" min="0" 
                       value="<?php echo $isEdit ? $producto['producto_stock'] : '0'; ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="rela_marca">Marca: <span class="required">*</span></label>
            <select name="rela_marca" id="rela_marca" required>
                <option value="">Seleccione una marca...</option>
                <?php
                    $registros = $mysql->query("SELECT * FROM marca WHERE marca_estado = 1 ORDER BY marca_descripcion") or die($mysql->error);
                    while ($row = $registros->fetch_assoc()) {
                        $selected = ($isEdit && $row["id_marca"] == $producto['rela_marca']) ? "selected" : "";
                        echo "<option value='".$row["id_marca"]."' $selected>".$row["marca_descripcion"]."</option>";
                    }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="rela_categoria">Categoría: <span class="required">*</span></label>
            <select name="rela_categoria" id="rela_categoria" required>
                <option value="">Seleccione una categoría...</option>
                <?php
                    $registros = $mysql->query("SELECT * FROM categoria WHERE categoria_estado = 1 ORDER BY categoria_descripcion") or die($mysql->error);
                    while ($row = $registros->fetch_assoc()) {
                        $selected = ($isEdit && $row["id_categoria"] == $producto['rela_categoria']) ? "selected" : "";
                        echo "<option value='".$row["id_categoria"]."' $selected>".$row["categoria_descripcion"]."</option>";
                    }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="rela_estadoproducto">Estado del Producto:</label>
            <select name="rela_estadoproducto" id="rela_estadoproducto">
                <option value="">Seleccione el estado del producto...</option>
                <?php
                    $registros = $mysql->query("SELECT * FROM estadoproducto WHERE estadoproducto_estado = 1 ORDER BY estadoproducto_descripcion") or die($mysql->error);
                    while ($row = $registros->fetch_assoc()) {
                        $selected = "";
                        if ($isEdit && $row["id_estadoproducto"] == $producto['rela_estadoproducto']) {
                            $selected = "selected";
                        } elseif (!$isEdit && $row["id_estadoproducto"] == 1) {
                            $selected = "selected"; // Estado activo por defecto para nuevos productos
                        }
                        echo "<option value='".$row["id_estadoproducto"]."' $selected>".$row["estadoproducto_descripcion"]."</option>";
                    }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="producto_foto">Foto del Producto:</label>
            <?php if ($isEdit && !empty($producto['producto_foto'])): ?>
                <div class="current-image">
                    <p>Imagen actual:</p>
                    <img src="/proyecto_cabania/imagenes/productos/<?php echo $producto['producto_foto']; ?>" 
                         width="100" height="100" alt="Imagen actual">
                </div>
            <?php endif; ?>
            <input type="file" id="producto_foto" name="producto_foto" 
                   accept="image/jpeg,image/jpg,image/png,image/gif">
            <small class="form-help">Formatos permitidos: JPG, JPEG, PNG, GIF. Tamaño máximo: 2MB</small>
            
            <?php if ($isEdit): ?>
                <input type="hidden" name="producto_foto_actual" value="<?php echo $producto['producto_foto']; ?>">
            <?php endif; ?>
        </div>

        <?php if ($isEdit): ?>
            <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
        <?php endif; ?>

        <div class="form-buttons">
            <input type="submit" value="<?php echo $isEdit ? 'Actualizar' : 'Crear'; ?>" class="btn btn-primary">
            <button type="button" data-action="navegar-listado-productos" class="btn btn-secondary">Cancelar</button>
        </div>
    </fieldset>
</form>

<?php
$mysql->close();
?>