<?php
if (!isset($estado) || empty($estado)) {
    echo '<div class="alert alert-danger">Estado no encontrado.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/estadosproductos') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= url('/estadosproductos/' . $estado['id_estadoproducto'] . '/edit') ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Estado
                </a>
                
                <?php if ($estado['estadoproducto_estado'] == 1): ?>
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstadoEstado(<?= $estado['id_estadoproducto'] ?>, 0, '<?= addslashes($estado['estadoproducto_descripcion']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstadoEstado(<?= $estado['id_estadoproducto'] ?>, 1, '<?= addslashes($estado['estadoproducto_descripcion']) ?>')">
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
                        <div class="col-md-8">
                            <div class="info-group mb-3">
                                <label class="text-muted small mb-1">
                                    <i class="fas fa-tag"></i> Descripción
                                </label>
                                <div>
                                    <strong><?= htmlspecialchars($estado['estadoproducto_descripcion']) ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group mb-3">
                                <label class="text-muted small mb-1">
                                    <i class="fas fa-toggle-on"></i> Estado
                                </label>
                                <div>
                                    <?php if ($estado['estadoproducto_estado'] == 1): ?>
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="fas fa-check"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger px-3 py-2">
                                            <i class="fas fa-ban"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <!-- Estadísticas -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-primary">
                                    <?= number_format($estadisticas['productos_asociados'] ?? 0) ?>
                                </div>
                                <div class="metric-label">Productos Asociados</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-info">
                                    <?php 
                                    $total = $estadisticas['total_productos_sistema'] ?? 1;
                                    $asociados = $estadisticas['productos_asociados'] ?? 0;
                                    $porcentaje = $total > 0 ? round(($asociados / $total) * 100, 1) : 0;
                                    echo $porcentaje . '%';
                                    ?>
                                </div>
                                <div class="metric-label">Porcentaje del Total</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= url('/estadosproductos/' . $estado['id_estadoproducto'] . '/edit') ?>" 
                           class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-edit"></i> Editar Estado
                        </a>
                        
                        <?php if ($estado['estadoproducto_estado'] == 1): ?>
                            <button onclick="cambiarEstadoEstado(<?= $estado['id_estadoproducto'] ?>, 0, '<?= addslashes($estado['estadoproducto_descripcion']) ?>')"
                                    class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-ban"></i> Desactivar
                            </button>
                        <?php else: ?>
                            <button onclick="cambiarEstadoEstado(<?= $estado['id_estadoproducto'] ?>, 1, '<?= addslashes($estado['estadoproducto_descripcion']) ?>')"
                                    class="btn btn-outline-success btn-sm">
                                <i class="fas fa-check"></i> Activar
                            </button>
                        <?php endif; ?>
                        
                        <a href="<?= url('/estadosproductos') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list"></i> Ver Todos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para funcionalidades -->
<script>
function cambiarEstadoEstado(id, nuevoEstado, descripcion) {
    let accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
    let mensaje = nuevoEstado == 1 ? 'El estado estará disponible para asignar' : 'El estado no estará disponible';
    let color = nuevoEstado == 1 ? '#28a745' : '#dc3545';
    
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
            const url = `<?= url('/estadosproductos') ?>/${id}/estado`;
            
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
