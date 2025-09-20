<?php
require("conexion.php");

// Mostrar mensaje si existe
mostrar_mensaje();

// Validar que se haya proporcionado el ID del comentario
if (!isset($_GET['id_comentario']) || empty($_GET['id_comentario'])) {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php',
        'Error: No se proporcionó información del comentario.',
        'error'
    );
    exit;
}

$id_comentario = intval($_GET['id_comentario']);

// Obtener información del comentario y verificar permisos
$registro = $mysql->query("SELECT c.*, 
                                  p.persona_nombre, p.persona_apellido,
                                  cab.cabania_nombre,
                                  r.reserva_fhinicio, r.reserva_fhfin, r.id_reserva,
                                  hr.rela_huesped
                           FROM comentario c
                           LEFT JOIN huesped h ON c.rela_huesped = h.id_huesped
                           LEFT JOIN persona p ON h.rela_persona = p.id_persona
                           LEFT JOIN usuario u ON u.rela_persona = p.id_persona
                           LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                           LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
                           LEFT JOIN huesped_reserva hr ON hr.rela_reserva = r.id_reserva AND hr.rela_huesped = c.rela_huesped
                           WHERE c.id_comentario = $id_comentario
                           AND c.comentario_estado = 1
                           AND u.usuario_nombre = '$_SESSION[usuario_nombre]'
                           LIMIT 1") or die($mysql->error);

if ($registro->num_rows == 0) {
    redireccionar_con_mensaje(
        '/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php',
        'Error: No se encontró el comentario o no tiene permisos para editarlo.',
        'error'
    );
    exit;
}

$comentario = $registro->fetch_assoc();
?>

<h1>Editar Comentario</h1>

<div class="info-reserva">
    <h3>Información de la estadía:</h3>
    <p><strong>Cabaña:</strong> <?php echo htmlspecialchars($comentario['cabania_nombre']); ?></p>
    <p><strong>Fecha de inicio:</strong> <?php echo date_format(date_create($comentario['reserva_fhinicio']), 'Y-m-d H:i'); ?></p>
    <p><strong>Fecha de fin:</strong> <?php echo date_format(date_create($comentario['reserva_fhfin']), 'Y-m-d H:i'); ?></p>
    <p><strong>Comentario creado:</strong> <?php echo date_format(date_create($comentario['comentario_fechahora']), 'Y-m-d H:i'); ?></p>
</div>

<form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=modificacion.php" class="formulario-comentarios">
    <fieldset>
        <legend>Editar tu experiencia</legend>
        
        <div class="campo-formulario">
            <label for="puntuacion">Puntuación general (1-5 estrellas):</label>
            <div class="rating-stars">
                <input type="radio" id="star5" name="puntuacion" value="5" <?php echo ($comentario['comentario_puntuacion'] == 5) ? 'checked' : ''; ?> required>
                <label for="star5" title="5 estrellas">★</label>
                <input type="radio" id="star4" name="puntuacion" value="4" <?php echo ($comentario['comentario_puntuacion'] == 4) ? 'checked' : ''; ?>>
                <label for="star4" title="4 estrellas">★</label>
                <input type="radio" id="star3" name="puntuacion" value="3" <?php echo ($comentario['comentario_puntuacion'] == 3) ? 'checked' : ''; ?>>
                <label for="star3" title="3 estrellas">★</label>
                <input type="radio" id="star2" name="puntuacion" value="2" <?php echo ($comentario['comentario_puntuacion'] == 2) ? 'checked' : ''; ?>>
                <label for="star2" title="2 estrellas">★</label>
                <input type="radio" id="star1" name="puntuacion" value="1" <?php echo ($comentario['comentario_puntuacion'] == 1) ? 'checked' : ''; ?>>
                <label for="star1" title="1 estrella">★</label>
            </div>
        </div>
        
        <div class="campo-formulario">
            <label for="comentario_texto">Comentario:</label>
            <textarea 
                id="comentario_texto" 
                name="comentario_texto" 
                rows="6" 
                cols="50" 
                placeholder="Cuéntanos sobre tu experiencia en la cabaña... (máximo 400 caracteres)"
                maxlength="400"
                required><?php echo htmlspecialchars($comentario['comentario_texto']); ?></textarea>
            <div class="contador-caracteres">
                <span id="contador"><?php echo strlen($comentario['comentario_texto']); ?></span>/400 caracteres
            </div>
        </div>
        
        <input type="hidden" name="id_comentario" value="<?php echo $id_comentario; ?>">
        <input type="hidden" name="id_reserva" value="<?php echo $comentario['id_reserva']; ?>">
        <input type="hidden" name="id_huesped" value="<?php echo $comentario['rela_huesped']; ?>">
        
        <div>
            <input type="submit" value="Actualizar" class="button-primary">
            <a href="/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=baja_logica.php&id_comentario=<?php echo $id_comentario; ?>" 
               class="abm-button button-danger" 
               onclick="return confirm('¿Está seguro que desea eliminar este comentario?')">Eliminar</a>
            <a href="/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php" class="abm-button button-secondary">Cancelar</a>
        </div>
    </fieldset>
</form>

<script>
    // Contador de caracteres para el textarea
    document.getElementById('comentario_texto').addEventListener('input', function() {
        const contador = document.getElementById('contador');
        const longitud = this.value.length;
        contador.textContent = longitud;
        
        // Cambiar color cuando se acerca al límite
        if (longitud > 350) {
            contador.style.color = '#ff6b6b';
        } else if (longitud > 300) {
            contador.style.color = '#ffa500';
        } else {
            contador.style.color = '#666';
        }
    });

    // Sistema de estrellas interactivo
    const stars = document.querySelectorAll('.rating-stars input[type="radio"]');
    const labels = document.querySelectorAll('.rating-stars label');
    
    // Inicializar estrellas según valor actual
    const valorActual = document.querySelector('.rating-stars input[type="radio"]:checked');
    if (valorActual) {
        highlightStars(parseInt(valorActual.value));
    }
    
    // Hover effect
    labels.forEach((label, index) => {
        label.addEventListener('mouseenter', function() {
            highlightStars(5 - index);
        });
    });
    
    document.querySelector('.rating-stars').addEventListener('mouseleave', function() {
        const checked = document.querySelector('.rating-stars input[type="radio"]:checked');
        if (checked) {
            highlightStars(parseInt(checked.value));
        } else {
            clearStars();
        }
    });
    
    // Click selection
    stars.forEach(star => {
        star.addEventListener('change', function() {
            highlightStars(parseInt(this.value));
        });
    });
    
    function highlightStars(rating) {
        labels.forEach((label, index) => {
            if (5 - index <= rating) {
                label.classList.add('active');
            } else {
                label.classList.remove('active');
            }
        });
    }
    
    function clearStars() {
        labels.forEach(label => {
            label.classList.remove('active');
        });
    }
</script>

<style>
.button-danger {
    background-color: #dc3545 !important;
    color: white !important;
}

.button-danger:hover {
    background-color: #c82333 !important;
}
</style>

<?php
$mysql->close();
?>
