<?php
/**
 * Vista: Detalle de Estado de Consumo
 * Descripción: Muestra información completa de un estado de consumo
 */

// Validar que existe el estado
if (!isset($estado) || empty($estado)) {
    echo '<div class="alert alert-danger">Estado de consumo no encontrado.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/estadosconsumo') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= url('/estadosconsumo/' . $estado['id_estadoconsumo'] . '/edit') ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Estado
                </a>
                
                <?php if ($estado['estadoconsumo_estado'] == 1): ?>
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstado(<?= $estado['id_estadoconsumo'] ?>, 0, '<?= addslashes($estado['estadoconsumo_descripcion']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstado(<?= $estado['id_estadoconsumo'] ?>, 1, '<?= addslashes($estado['estadoconsumo_descripcion']) ?>')">
                        <i class="fas fa-check"></i> Activar
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información principal -->
        <div class="col-lg-8">
            <!-- Datos básicos -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información General
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <i class="fas fa-tag text-muted"></i> Descripción:
                                <strong><?= htmlspecialchars($estado['estadoconsumo_descripcion']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php if ($estado['estadoconsumo_estado'] == 1): ?>
                                        <i class="fas fa-toggle-on text-success"></i> Estado: 
                                        <span class="badge bg-success badge-lg">
                                            <i class="fas fa-check"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <i class="fas fa-toggle-off text-danger"></i> Estado: 
                                        <span class="badge bg-danger badge-lg">
                                            <i class="fas fa-times"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <!-- Estadísticas rápidas -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-primary"><?= number_format($estadisticas['total_consumos']) ?></div>
                                <div class="metric-label">Total consumos</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-success">$<?= number_format($estadisticas['monto_total'], 0, ',', '.') ?></div>
                                <div class="metric-label">Monto total</div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-info">$<?= number_format($estadisticas['monto_promedio'], 0, ',', '.') ?></div>
                                <div class="metric-label">Promedio</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-secondary"><?= $estadisticas['total_consumos'] > 0 ? date('d/m/Y', strtotime($estadisticas['ultimo_uso'])) : 'N/A' ?></div>
                                <div class="metric-label">Último uso</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= url('/consumos') ?>?estado=<?= $estado['id_estadoconsumo'] ?>"
                            class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> Ver Consumos con este Estado
                        </a>
                        <a href="<?= url('/estadosconsumo/create') ?>"
                            class="btn btn-outline-success">
                            <i class="fas fa-plus"></i> Crear Nuevo Estado
                        </a>
                        <a href="<?= url('/estadosconsumo') ?>"
                            class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Listado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estilos para estadísticas -->
<style>
.metric-value {
    font-size: 1.5rem;
    font-weight: bold;
    line-height: 1.2;
    word-break: break-word;
}
.metric-label {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
}
</style>

<!-- JavaScript para funcionalidades -->
<script>
function cambiarEstado(id, nuevoEstado, descripcion) {
    const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
    const mensaje = nuevoEstado == 1 ? 'El estado estará disponible para su uso' : 'El estado no estará disponible';
    const color = nuevoEstado == 1 ? '#28a745' : '#dc3545';

    const confirmar = typeof Swal !== 'undefined' ? 
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} estado?`,
            text: `¿Está seguro que desea ${accion} el estado "${descripcion}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then(result => result.isConfirmed) :
        Promise.resolve(confirm(`¿Está seguro que desea ${accion} el estado "${descripcion}"?`));
    
    confirmar.then(confirmed => {
        if (confirmed) {
            const url = `<?= url('/estadosconsumo') ?>/${id}/estado`;
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({estado: nuevoEstado})
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: `Estado ${accion}do correctamente`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        alert(`Estado ${accion}do correctamente`);
                        location.reload();
                    }
                } else {
                    const errorMsg = 'Error al cambiar el estado: ' + (data.message || 'Error desconocido');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', errorMsg, 'error');
                    } else {
                        alert(errorMsg);
                    }
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                const errorMsg = 'Error al cambiar el estado: ' + error.message;
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', errorMsg, 'error');
                } else {
                    alert(errorMsg);
                }
            });
        }
    });
}
</script>
