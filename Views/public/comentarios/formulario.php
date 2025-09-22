<?php
// Los datos ya vienen preparados desde el controlador
// Variables disponibles:
// $comentario - datos del comentario (si es edición) o null (si es nuevo)
// $reserva_info - información de la reserva (si aplica)
// $isEdit - boolean indicando si es edición
// $error_message - mensaje de error (si aplica)

if (isset($error_message)) {
    echo '<div class="alert alert-error">' . htmlspecialchars($error_message) . '</div>';
    exit;
}

$pageTitle = $isEdit ? 'Editar Comentario' : 'Nuevo Comentario';
$actionUrl = $isEdit ? "/proyecto_cabania/comentarios/{$comentario['id_comentario']}/update" : "/proyecto_cabania/comentarios/store";
?>

<h1><?php echo $pageTitle; ?></h1>

<?php if ($comentario && isset($comentario['cabania_nombre'])): ?>
<div class="info-reserva">
    <h3>Información de la estadía:</h3>
    <p><strong>Cabaña:</strong> <?php echo htmlspecialchars($comentario['cabania_nombre']); ?></p>
    <?php if (isset($comentario['reserva_fechainicio'])): ?>
        <p><strong>Fecha de inicio:</strong> <?php echo date('d/m/Y', strtotime($comentario['reserva_fechainicio'])); ?></p>
        <p><strong>Fecha de fin:</strong> <?php echo date('d/m/Y', strtotime($comentario['reserva_fechafin'])); ?></p>
    <?php endif; ?>
    <?php if ($isEdit && isset($comentario['comentario_fechahora'])): ?>
        <p><strong>Comentario creado:</strong> <?php echo date('d/m/Y H:i', strtotime($comentario['comentario_fechahora'])); ?></p>
    <?php endif; ?>
</div>
<?php endif; ?>

<form method="post" action="<?php echo $actionUrl; ?>" class="formulario-comentarios">
    <fieldset>
        <legend><?php echo $isEdit ? 'Editar tu experiencia' : 'Comparte tu experiencia'; ?></legend>
        
        <div class="campo-formulario">
            <label>¿Cómo calificarías tu estadía?</label>
            <div class="rating-stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <input type="radio" id="star<?php echo $i; ?>" name="puntuacion" value="<?php echo $i; ?>"
                           <?php echo ($isEdit && $comentario['comentario_puntuacion'] == $i) || (!$isEdit && $i == 5) ? 'checked' : ''; ?>>
                    <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> estrella<?php echo $i > 1 ? 's' : ''; ?>">★</label>
                <?php endfor; ?>
            </div>
        </div>
        
        <div class="campo-formulario">
            <label for="comentario_texto">
                Cuéntanos sobre tu experiencia:
                <span class="required">*</span>
            </label>
            <textarea id="comentario_texto" name="comentario_texto" rows="6" 
                      maxlength="400" placeholder="Comparte los detalles de tu estadía..." 
                      required><?php echo $isEdit ? htmlspecialchars($comentario['comentario_texto']) : ''; ?></textarea>
            <div class="contador-caracteres">
                <span id="contador"><?php echo $isEdit ? strlen($comentario['comentario_texto']) : 0; ?></span>/400 caracteres
            </div>
        </div>
        
        <?php if ($isEdit): ?>
            <input type="hidden" name="id_comentario" value="<?php echo $comentario['id_comentario']; ?>">
            <input type="hidden" name="id_reserva" value="<?php echo $comentario['rela_reserva']; ?>">
            <input type="hidden" name="id_huesped" value="<?php echo $comentario['rela_huesped']; ?>">
        <?php elseif (isset($reserva_info)): ?>
            <input type="hidden" name="id_reserva" value="<?php echo $reserva_info['id_reserva']; ?>">
        <?php endif; ?>
        
        <div class="botones-formulario">
            <input type="submit" value="<?php echo $isEdit ? 'Actualizar' : 'Guardar'; ?>" class="button-primary">
            
            <?php if ($isEdit): ?>
                <a href="/proyecto_cabania/comentarios/<?php echo $comentario['id_comentario']; ?>/delete" 
                   class="abm-button button-danger" 
                   data-action="confirm-delete" data-message="¿Está seguro que desea eliminar este comentario?">Eliminar</a>
            <?php endif; ?>
            
            <a href="/proyecto_cabania/comentarios" class="abm-button button-secondary">
                <?php echo $isEdit ? 'Cancelar' : 'Omitir comentario'; ?>
            </a>
        </div>
    </fieldset>
</form>

<style>
.info-reserva {
    background-color: #f8f9fa;
    border-left: 4px solid #007bff;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.formulario-comentarios fieldset {
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 25px;
    margin: 0;
}

.formulario-comentarios legend {
    font-weight: bold;
    padding: 0 10px;
    color: #333;
}

.campo-formulario {
    margin-bottom: 20px;
}

.campo-formulario label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
    color: #555;
}

.required {
    color: #e74c3c;
}

.rating-stars {
    display: flex;
    direction: row-reverse;
    justify-content: flex-end;
    margin-bottom: 10px;
}

.rating-stars input[type="radio"] {
    display: none;
}

.rating-stars label {
    font-size: 30px;
    color: #ddd;
    cursor: pointer;
    margin-right: 5px;
    transition: color 0.2s;
}

.rating-stars input[type="radio"]:checked ~ label,
.rating-stars input[type="radio"]:checked + label,
.rating-stars label:hover,
.rating-stars label:hover ~ label {
    color: #f39c12;
}

textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: Arial, sans-serif;
    font-size: 14px;
    resize: vertical;
}

.contador-caracteres {
    text-align: right;
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.botones-formulario {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.button-primary, .button-secondary, .button-danger {
    padding: 12px 24px;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: bold;
    text-decoration: none;
    cursor: pointer;
    display: inline-block;
    text-align: center;
    transition: background-color 0.3s;
}

.button-primary {
    background-color: #007bff;
    color: white;
}

.button-primary:hover {
    background-color: #0056b3;
}

.button-secondary {
    background-color: #6c757d;
    color: white;
}

.button-secondary:hover {
    background-color: #545b62;
}

.button-danger {
    background-color: #dc3545;
    color: white;
}

.button-danger:hover {
    background-color: #c82333;
}

@media (max-width: 600px) {
    .botones-formulario {
        flex-direction: column;
    }
    
    .button-primary, .button-secondary, .button-danger {
        width: 100%;
    }

<?php include __DIR__ . '/../layouts/footer.php'; ?>