<?php
// Obtener datos del comentario
include_once(dirname(__FILE__) . '/../../../conexion.php');

if (!isset($_REQUEST['id_comentario']) || empty($_REQUEST['id_comentario'])) {
    echo '<div class="alert alert-error">ID de comentario no especificado</div>';
    exit;
}

$id_comentario = intval($_REQUEST['id_comentario']);
$query = "SELECT c.*, 
                 p.persona_nombre, p.persona_apellido,
                 cab.cabania_nombre,
                 r.reserva_fechainicio, r.reserva_fechafin,
                 r.reserva_fechareserva
          FROM comentario c
          LEFT JOIN huesped h ON c.rela_huesped = h.id_huesped
          LEFT JOIN persona p ON h.rela_persona = p.id_persona
          LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
          LEFT JOIN cabania cab ON r.rela_cabania = cab.id_cabania
          WHERE c.id_comentario = $id_comentario";

$resultado = $mysql->query($query) or die($mysql->error);

if (!$comentario = $resultado->fetch_array()) {
    echo '<div class="alert alert-error">Comentario no encontrado</div>';
    exit;
}
?>

<h1>Detalle del Comentario</h1>

<div class="comment-detail-container">
    <div class="comment-header">
        <div class="comment-info">
            <h2>Comentario de <?php echo htmlspecialchars($comentario['persona_nombre'] . ' ' . $comentario['persona_apellido']); ?></h2>
            <div class="comment-meta">
                <span class="comment-date">
                    <i class="fa fa-calendar"></i>
                    <?php echo date('d/m/Y H:i', strtotime($comentario['comentario_fechahora'])); ?>
                </span>
                <span class="comment-status status-<?php echo $comentario['comentario_estado']; ?>">
                    <?php echo $comentario['comentario_estado'] ? 'Activo' : 'Eliminado'; ?>
                </span>
            </div>
        </div>
        
        <div class="rating-display">
            <div class="stars">
                <?php 
                for ($i = 1; $i <= 5; $i++) {
                    echo $i <= $comentario['comentario_puntuacion'] ? '★' : '☆';
                }
                ?>
            </div>
            <div class="rating-text">
                <?php echo $comentario['comentario_puntuacion']; ?>/5 estrellas
            </div>
        </div>
    </div>
    
    <div class="reservation-info">
        <h3>Información de la Estadía</h3>
        <div class="info-grid">
            <div class="info-item">
                <label>Cabaña:</label>
                <span><?php echo htmlspecialchars($comentario['cabania_nombre'] ?? 'No especificada'); ?></span>
            </div>
            
            <div class="info-item">
                <label>Fecha de Reserva:</label>
                <span><?php echo $comentario['reserva_fechareserva'] ? date('d/m/Y', strtotime($comentario['reserva_fechareserva'])) : 'N/A'; ?></span>
            </div>
            
            <div class="info-item">
                <label>Check-in:</label>
                <span><?php echo $comentario['reserva_fechainicio'] ? date('d/m/Y', strtotime($comentario['reserva_fechainicio'])) : 'N/A'; ?></span>
            </div>
            
            <div class="info-item">
                <label>Check-out:</label>
                <span><?php echo $comentario['reserva_fechafin'] ? date('d/m/Y', strtotime($comentario['reserva_fechafin'])) : 'N/A'; ?></span>
            </div>
            
            <?php if ($comentario['reserva_fechainicio'] && $comentario['reserva_fechafin']): ?>
            <div class="info-item">
                <label>Duración:</label>
                <span>
                    <?php 
                    $inicio = new DateTime($comentario['reserva_fechainicio']);
                    $fin = new DateTime($comentario['reserva_fechafin']);
                    $duracion = $inicio->diff($fin);
                    echo $duracion->days . ' día' . ($duracion->days != 1 ? 's' : '');
                    ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="comment-content">
        <h3>Comentario</h3>
        <div class="comment-text">
            <?php echo nl2br(htmlspecialchars($comentario['comentario_texto'])); ?>
        </div>
        
        <div class="comment-stats">
            <div class="stat-item">
                <span class="stat-value"><?php echo strlen($comentario['comentario_texto']); ?></span>
                <span class="stat-label">caracteres</span>
            </div>
            
            <div class="stat-item">
                <span class="stat-value"><?php echo str_word_count($comentario['comentario_texto']); ?></span>
                <span class="stat-label">palabras</span>
            </div>
        </div>
    </div>
    
    <div class="comment-actions">
        <button class="btn btn-primary" data-action="edit" data-id="<?php echo $comentario['id_comentario']; ?>">
            <i class="fa fa-edit"></i> Editar Comentario
        </button>
        
        <?php if ($comentario['comentario_estado']): ?>
            <button class="btn btn-danger" data-action="delete" data-id="<?php echo $comentario['id_comentario']; ?>">
                <i class="fa fa-trash"></i> Eliminar Comentario
            </button>
        <?php else: ?>
            <button class="btn btn-success" data-action="restore" data-id="<?php echo $comentario['id_comentario']; ?>">
                <i class="fa fa-undo"></i> Recuperar Comentario
            </button>
        <?php endif; ?>
        
        <button class="btn btn-secondary" data-action="navigate" data-url="/proyecto_cabania/comentarios">
            <i class="fa fa-arrow-left"></i> Volver al Listado
        </button>
    </div>
    
    <div class="additional-info">
        <h3>Información Adicional</h3>
        <table class="details-table">
            <tr>
                <td><strong>ID del Comentario:</strong></td>
                <td><?php echo $comentario['id_comentario']; ?></td>
            </tr>
            <tr>
                <td><strong>ID de Reserva:</strong></td>
                <td><?php echo $comentario['rela_reserva']; ?></td>
            </tr>
            <tr>
                <td><strong>ID de Huésped:</strong></td>
                <td><?php echo $comentario['rela_huesped']; ?></td>
            </tr>
            <tr>
                <td><strong>Estado del Comentario:</strong></td>
                <td>
                    <span class="status-badge status-<?php echo $comentario['comentario_estado']; ?>">
                        <?php echo $comentario['comentario_estado'] ? 'Activo' : 'Eliminado'; ?>
                    </span>
                </td>
            </tr>
        </table>
    </div>
</div>

<?php
$mysql->close();
?>

<?php $this->endSection(); ?>
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
<?php $this->endSection(); ?>