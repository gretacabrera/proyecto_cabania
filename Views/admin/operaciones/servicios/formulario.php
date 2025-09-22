<?php
// Obtener datos para el formulario
include_once(dirname(__FILE__) . '/../../../conexion.php');

$servicio = null;
$isEdit = false;

// Si es edición, obtener datos del servicio
if (isset($_REQUEST['id_servicio']) && !empty($_REQUEST['id_servicio'])) {
    $id_servicio = intval($_REQUEST['id_servicio']);
    $registro = $mysql->query("SELECT * FROM servicio WHERE id_servicio = $id_servicio") or die($mysql->error);
    
    if ($reg = $registro->fetch_array()) {
        $servicio = $reg;
        $isEdit = true;
    } else {
        echo '<div class="alert alert-error">No existe un servicio con ese ID</div>';
        exit;
    }
}

$pageTitle = $isEdit ? 'Editar Servicio' : 'Nuevo Servicio';
$actionUrl = $isEdit ? "/proyecto_cabania/servicios/{$id_servicio}/update" : "/proyecto_cabania/servicios/store";
?>

<h1><?php echo $pageTitle; ?></h1>

<form method="post" action="<?php echo $actionUrl; ?>" class="form-container">
    <fieldset>
        <legend><?php echo $pageTitle; ?></legend>
        
        <div class="form-group">
            <label for="servicio_nombre">Nombre del Servicio: <span class="required">*</span></label>
            <input type="text" id="servicio_nombre" name="servicio_nombre" size="50" 
                   value="<?php echo $isEdit ? htmlspecialchars($servicio['servicio_nombre']) : ''; ?>" 
                   required maxlength="100">
        </div>

        <div class="form-group">
            <label for="servicio_descripcion">Descripción: <span class="required">*</span></label>
            <textarea id="servicio_descripcion" name="servicio_descripcion" rows="3" cols="50" 
                      required maxlength="255"><?php echo $isEdit ? htmlspecialchars($servicio['servicio_descripcion']) : ''; ?></textarea>
        </div>

        <div class="form-group">
            <label for="servicio_precio">Precio Unitario: <span class="required">*</span></label>
            <input type="number" id="servicio_precio" name="servicio_precio" step="0.01" min="0" 
                   value="<?php echo $isEdit ? $servicio['servicio_precio'] : ''; ?>" 
                   required>
        </div>

        <div class="form-group">
            <label for="rela_tiposervicio">Tipo de Servicio: <span class="required">*</span></label>
            <select name="rela_tiposervicio" id="rela_tiposervicio" required>
                <option value="">Seleccione el tipo de servicio...</option>
                <?php
                    $registros = $mysql->query("SELECT * FROM tiposervicio WHERE tiposervicio_estado = 1 ORDER BY tiposervicio_descripcion") or die($mysql->error);
                    while ($row = $registros->fetch_assoc()) {
                        $selected = ($isEdit && $row["id_tiposervicio"] == $servicio['rela_tiposervicio']) ? "selected" : "";
                        echo "<option value='".$row["id_tiposervicio"]."' $selected>".$row["tiposervicio_descripcion"]."</option>";
                    }
                ?>
            </select>
        </div>

        <?php if ($isEdit): ?>
            <input type="hidden" name="id_servicio" value="<?php echo $servicio['id_servicio']; ?>">
        <?php endif; ?>

        <div class="form-buttons">
            <input type="submit" value="<?php echo $isEdit ? 'Actualizar' : 'Crear'; ?>" class="btn btn-primary">
            <button type="button" onclick="window.location.href='/proyecto_cabania/servicios'" class="btn btn-secondary">Cancelar</button>
        </div>
    </fieldset>
</form>

<?php
$mysql->close();
?>

