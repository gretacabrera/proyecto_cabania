<?php
// Obtener datos del servicio
include_once(dirname(__FILE__) . '/../../../conexion.php');

if (!isset($_REQUEST['id_servicio']) || empty($_REQUEST['id_servicio'])) {
    echo '<div class="alert alert-error">ID de servicio no especificado</div>';
    exit;
}

$id_servicio = intval($_REQUEST['id_servicio']);
$query = "SELECT s.*, 
                 ts.tiposervicio_descripcion
          FROM servicio s
          LEFT JOIN tiposervicio ts ON s.rela_tiposervicio = ts.id_tiposervicio
          WHERE s.id_servicio = $id_servicio";

$resultado = $mysql->query($query) or die($mysql->error);

if (!$servicio = $resultado->fetch_array()) {
    echo '<div class="alert alert-error">Servicio no encontrado</div>';
    exit;
}
?>

<h1>Detalle del Servicio</h1>

<div class="service-detail-container">
    <div class="service-header">
        <h2><?php echo htmlspecialchars($servicio['servicio_nombre']); ?></h2>
        
        <div class="service-status">
            <span class="status-badge <?php echo $servicio['servicio_estado'] ? 'active' : 'inactive'; ?>">
                <?php echo $servicio['servicio_estado'] ? 'Activo' : 'Inactivo'; ?>
            </span>
        </div>
    </div>
    
    <div class="service-info">
        <div class="info-grid">
            <div class="info-item">
                <label>ID del Servicio:</label>
                <span><?php echo $servicio['id_servicio']; ?></span>
            </div>
            
            <div class="info-item">
                <label>Tipo de Servicio:</label>
                <span><?php echo htmlspecialchars($servicio['tiposervicio_descripcion'] ?? 'No especificado'); ?></span>
            </div>
            
            <div class="info-item">
                <label>Precio:</label>
                <span class="price">$<?php echo number_format($servicio['servicio_precio'], 2); ?></span>
            </div>
            
            <div class="info-item">
                <label>Estado:</label>
                <span class="<?php echo $servicio['servicio_estado'] ? 'text-success' : 'text-danger'; ?>">
                    <?php echo $servicio['servicio_estado'] ? 'Activo' : 'Inactivo'; ?>
                </span>
            </div>
        </div>
    </div>
    
    <div class="service-description">
        <h3>Descripción</h3>
        <p><?php echo nl2br(htmlspecialchars($servicio['servicio_descripcion'])); ?></p>
    </div>
    
    <?php
    // Obtener estadísticas de uso del servicio
    $stats_query = "SELECT 
                        COUNT(*) as total_consumos,
                        SUM(consumo_cantidad) as cantidad_total,
                        SUM(consumo_total) as ingresos_total
                    FROM consumo 
                    WHERE rela_servicio = {$id_servicio} AND consumo_estado = 1";
    
    $stats_result = $mysql->query($stats_query);
    $stats = $stats_result->fetch_array();
    ?>
    
    <div class="service-stats">
        <h3>Estadísticas de Uso</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value"><?php echo $stats['total_consumos'] ?? 0; ?></div>
                <div class="stat-label">Consumos Totales</div>
            </div>
            
            <div class="stat-item">
                <div class="stat-value"><?php echo $stats['cantidad_total'] ?? 0; ?></div>
                <div class="stat-label">Cantidad Consumida</div>
            </div>
            
            <div class="stat-item">
                <div class="stat-value">$<?php echo number_format($stats['ingresos_total'] ?? 0, 2); ?></div>
                <div class="stat-label">Ingresos Generados</div>
            </div>
        </div>
    </div>
    
    <div class="service-actions">
        <button class="btn btn-primary" onclick="window.location.href='/proyecto_cabania/servicios/<?php echo $servicio['id_servicio']; ?>/edit'">
            <i class="fa fa-edit"></i> Editar Servicio
        </button>
        
        <?php if ($servicio['servicio_estado']): ?>
            <button class="btn btn-danger" onclick="confirmarDesactivacion(<?php echo $servicio['id_servicio']; ?>)">
                <i class="fa fa-ban"></i> Desactivar Servicio
            </button>
        <?php else: ?>
            <button class="btn btn-success" onclick="confirmarActivacion(<?php echo $servicio['id_servicio']; ?>)">
                <i class="fa fa-check"></i> Activar Servicio
            </button>
        <?php endif; ?>
        
        <button class="btn btn-secondary" onclick="window.location.href='/proyecto_cabania/servicios'">
            <i class="fa fa-arrow-left"></i> Volver al Listado
        </button>
    </div>
    
    <?php if ($stats['total_consumos'] > 0): ?>
    <div class="recent-consumos">
        <h3>Consumos Recientes</h3>
        <?php
        $recent_query = "SELECT c.*, r.reserva_fechainicio, r.reserva_fechafin
                        FROM consumo c
                        LEFT JOIN reserva r ON c.rela_reserva = r.id_reserva
                        WHERE c.rela_servicio = {$id_servicio} AND c.consumo_estado = 1
                        ORDER BY c.id_consumo DESC
                        LIMIT 5";
        
        $recent_result = $mysql->query($recent_query);
        
        if ($recent_result->num_rows > 0):
        ?>
            <table class="recent-table">
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                        <th>Fecha Reserva</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($consumo = $recent_result->fetch_array()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($consumo['consumo_descripcion']); ?></td>
                        <td><?php echo $consumo['consumo_cantidad']; ?></td>
                        <td>$<?php echo number_format($consumo['consumo_total'], 2); ?></td>
                        <td><?php echo $consumo['reserva_fechainicio'] ? date('d/m/Y', strtotime($consumo['reserva_fechainicio'])) : 'N/A'; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay consumos recientes registrados.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php
$mysql->close();
?>

<?php $this->endSection(); ?>