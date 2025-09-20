<?php
require("conexion.php");

// Mostrar mensaje si existe
mostrar_mensaje();

// Validar que se haya proporcionado el ID de reserva
if (!isset($_GET['id_reserva']) || empty($_GET['id_reserva'])) {
    echo "<h2>Error: No se proporcionó información de la reserva.</h2>"; 
    echo "<a href='/proyecto_cabania/plantilla_modulo.php?titulo=Salidas&ruta=salidas&archivo=formulario.php' class='abm-button'>Volver a Salidas</a>"; 
    exit; 
}

$id_reserva = intval($_GET['id_reserva']);

// Validar que la reserva existe y obtener información relevante
$registro = $mysql->query("SELECT r.*, c.cabania_nombre,
                                  p.persona_nombre, p.persona_apellido,
                                  hr.rela_huesped
                           FROM reserva r
                           LEFT JOIN cabania c ON r.rela_cabania = c.id_cabania
                           LEFT JOIN huesped_reserva hr ON hr.rela_reserva = r.id_reserva
                           LEFT JOIN huesped h ON hr.rela_huesped = h.id_huesped
                           LEFT JOIN persona p ON h.rela_persona = p.id_persona
                           LEFT JOIN usuario u ON u.rela_persona = p.id_persona
                           WHERE r.id_reserva = $id_reserva
                           AND u.usuario_nombre = '$_SESSION[usuario_nombre]'
                           LIMIT 1") or die($mysql->error);

if ($registro->num_rows == 0) {
    echo "<h2>Error: No se encontró la reserva o no tiene permisos para acceder a ella.</h2>";
    echo "<a href='/proyecto_cabania/plantilla_modulo.php?titulo=Salidas&ruta=salidas&archivo=formulario.php' class='abm-button'>Volver a Salidas</a>";
    exit;
}

$reserva = $registro->fetch_assoc();

// Verificar si ya existe un comentario para esta reserva del huésped actual
$comentario_existente = $mysql->query("SELECT * FROM comentario 
                                       WHERE rela_reserva = $id_reserva 
                                       AND rela_huesped = {$reserva['rela_huesped']}
                                       AND comentario_estado = 1") or die($mysql->error);

$tiene_comentario = $comentario_existente->num_rows > 0;
?>

<h1>Agregar Comentario sobre su Estadía</h1>

<div class="info-reserva">
    <h3>Información de la estadía:</h3>
    <p><strong>Cabaña:</strong> <?php echo htmlspecialchars($reserva['cabania_nombre']); ?></p>
    <p><strong>Fecha de inicio:</strong> <?php echo date_format(date_create($reserva['reserva_fhinicio']), 'Y-m-d H:i'); ?></p>
    <p><strong>Fecha de fin:</strong> <?php echo date_format(date_create($reserva['reserva_fhfin']), 'Y-m-d H:i'); ?></p>
    <p><strong>Huésped:</strong> <?php echo htmlspecialchars($reserva['persona_nombre'] . ' ' . $reserva['persona_apellido']); ?></p>
</div>

<?php if ($tiene_comentario): ?>
    <div class="mensaje-info">
        <h3>Ya has dejado un comentario para esta estadía</h3>
        <p>Gracias por compartir tu experiencia con nosotros. Tu comentario ya ha sido registrado.</p>
        <div class="botones-formulario">
            <a href="/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=listado.php" class="abm-button">Ver mis comentarios</a>
            <a href="/proyecto_cabania/plantilla_modulo.php?titulo=Salidas&ruta=salidas&archivo=formulario.php" class="abm-button button-secondary">Volver a Salidas</a>
        </div>
    </div>
<?php else: ?>
    <form method="post" action="/proyecto_cabania/plantilla_modulo.php?titulo=Comentarios&ruta=comentarios&archivo=alta.php" class="formulario-comentarios">
        <fieldset>
            <legend>Comparte tu experiencia</legend>
            
            <div class="campo-formulario">
                <label for="puntuacion">Puntuación general (1-5 estrellas):</label>
                <div class="rating-stars">
                    <input type="radio" id="star5" name="puntuacion" value="5" required>
                    <label for="star5" title="5 estrellas">★</label>
                    <input type="radio" id="star4" name="puntuacion" value="4">
                    <label for="star4" title="4 estrellas">★</label>
                    <input type="radio" id="star3" name="puntuacion" value="3">
                    <label for="star3" title="3 estrellas">★</label>
                    <input type="radio" id="star2" name="puntuacion" value="2">
                    <label for="star2" title="2 estrellas">★</label>
                    <input type="radio" id="star1" name="puntuacion" value="1">
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
                    required></textarea>
                <div class="contador-caracteres">
                    <span id="contador">0</span>/400 caracteres
                </div>
            </div>
            
            <input type="hidden" name="id_reserva" value="<?php echo $id_reserva; ?>">
            <input type="hidden" name="id_huesped" value="<?php echo $reserva['rela_huesped']; ?>">
            
            <div class="botones-formulario">
                <input type="submit" value="Guardar Comentario" class="abm-button">
                <a href="/proyecto_cabania/plantilla_modulo.php?titulo=Salidas&ruta=salidas&archivo=formulario.php" class="abm-button button-secondary">Omitir comentario</a>
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
<?php endif; ?>

<?php
$mysql->close();
?>
