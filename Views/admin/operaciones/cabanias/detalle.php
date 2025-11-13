<?php

/**
 * Vista: Detalle de Cabaña
 * Descripción: Muestra información completa de una cabaña
 * Autor: Sistema MVC
 * Fecha: 2025-09-26
 */

// Validar que existe la cabaña
if (!isset($cabania) || empty($cabania)) {
    echo '<div class="alert alert-danger">Cabaña no encontrada.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= $this->url('/cabanias') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= $this->url('/cabanias/' . $cabania['id_cabania']) . '/edit' ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Cabaña
                </a>
                
                <?php if ($cabania['cabania_estado'] == 1): ?>
                    <!-- Cabaña activa: puede marcar como ocupada o desactivar -->
                    <button class="btn btn-info ms-2"
                        onclick="cambiarEstadoCabania(<?= $cabania['id_cabania'] ?>, 2, '<?= addslashes($cabania['cabania_nombre']) ?>')">
                        <i class="fas fa-home"></i> Marcar Ocupada
                    </button>
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstadoCabania(<?= $cabania['id_cabania'] ?>, 0, '<?= addslashes($cabania['cabania_nombre']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php elseif ($cabania['cabania_estado'] == 2): ?>
                    <!-- Cabaña ocupada: puede liberar o desactivar -->
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstadoCabania(<?= $cabania['id_cabania'] ?>, 1, '<?= addslashes($cabania['cabania_nombre']) ?>')">
                        <i class="fas fa-unlock"></i> Liberar Cabaña
                    </button>
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstadoCabania(<?= $cabania['id_cabania'] ?>, 0, '<?= addslashes($cabania['cabania_nombre']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <!-- Cabaña inactiva: solo puede activar -->
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstadoCabania(<?= $cabania['id_cabania'] ?>, 1, '<?= addslashes($cabania['cabania_nombre']) ?>')">
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
                        <div class="col-md-3">
                            <div class="info-group">
                                <i class="fas fa-barcode text-muted"></i> Código:
                                <code><?= htmlspecialchars($cabania['cabania_codigo']) ?></code>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <i class="fas fa-tag text-muted"></i> Nombre:
                                <strong><?= htmlspecialchars($cabania['cabania_nombre']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php if ($cabania['cabania_estado'] == 1): ?>
                                        <i class="fas fa-toggle-on text-success"></i> Estado: 
                                        <span class="badge bg-success badge-lg">
                                            <i class="fas fa-check"></i> Activa
                                        </span>
                                    <?php elseif ($cabania['cabania_estado'] == 2): ?>
                                        <i class="fas fa-home text-warning"></i> Estado: 
                                        <span class="badge bg-warning badge-lg">
                                            <i class="fas fa-home"></i> Ocupada
                                        </span>
                                    <?php else: ?>
                                        <i class="fas fa-toggle-off text-danger"></i> Estado: 
                                        <span class="badge bg-danger badge-lg">
                                            <i class="fas fa-times"></i> Inactiva
                                        </span>
                                    <?php endif; ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-align-left text-muted"></i> Descripción:
                                </label>
                                <div class="info-value">
                                    <?= nl2br(htmlspecialchars($cabania['cabania_descripcion'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-map-marker-alt text-muted"></i> Ubicación:
                                </label>
                                <div class="info-value">
                                    <?= htmlspecialchars($cabania['cabania_ubicacion']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Características -->
                <div class="card-body">
                    <div class="row">
                        <div class="text-center">
                            <i class="fas fa-users fa-2x text-primary"></i>
                            <?= $cabania['cabania_capacidad'] ?> Personas
                        </div>
                        <div class="ml-4 text-center">
                            <i class="fas fa-bed fa-2x text-info"></i>
                            <?= $cabania['cabania_cantidadhabitaciones'] ?> Habitaciones
                        </div>
                        <div class="ml-4 text-center">
                            <i class="fas fa-bath fa-2x text-warning"></i>
                            <?= $cabania['cabania_cantidadbanios'] ?> Baños
                        </div>
                        <div class="ml-4 text-center">
                            <i class="fas fa-dollar-sign fa-2x text-success"></i>
                            <?= number_format($cabania['cabania_precio'], 0) ?> p/Noche
                        </div>
                    </div>
                </div>
                <!-- Foto -->
                <?php if (!empty($cabania['cabania_foto'])): ?>
                    <div class="card-body text-center">
                        <img src="<?= $this->asset('imagenes/cabanias/' . $cabania['cabania_foto']) ?>"
                            alt="Foto de <?= htmlspecialchars($cabania['cabania_nombre']) ?>"
                            class="img-fluid rounded shadow-sm" style="max-height: 400px;">
                    </div>
                <?php endif; ?>
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
                                <div class="metric-value text-primary"><?= number_format($estadisticas['reservas_activas']) ?></div>
                                <div class="metric-label">Reservas activas</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-success"><?= number_format($estadisticas['reservas_totales']) ?></div>
                                <div class="metric-label">Reservas totales</div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-4">
                            <div class="metric-box">
                                <div class="metric-value text-info"><?= $estadisticas['ocupacion_porcentaje'] ?>%</div>
                                <div class="metric-label">Ocupación</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="metric-box">
                                <div class="metric-value text-warning">$<?= number_format($estadisticas['ingresos_mes'], 0, ',', '.') ?></div>
                                <div class="metric-label">Ingresos mes</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="metric-box">
                                <div class="metric-value text-secondary"><?= number_format($estadisticas['items_inventario']) ?></div>
                                <div class="metric-label">Items asignados</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventario asignado -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-box"></i> Inventario Asignado
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($inventarioCabania)): ?>
                        <?php
                        // Filtrar solo los activos para mostrar
                        $inventariosActivos = array_filter($inventarioCabania, function($estado) {
                            return $estado == 1;
                        });
                        ?>
                        
                        <?php if (count($inventariosActivos) > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($todosInventarios as $inventario): ?>
                                    <?php if (isset($inventarioCabania[$inventario['id_inventario']]) && $inventarioCabania[$inventario['id_inventario']] == 1): ?>
                                        <div class="list-group-item px-0 py-2">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <small class="fw-bold"><?= htmlspecialchars($inventario['inventario_descripcion']) ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small mb-0">
                                <i class="fas fa-info-circle"></i> No hay inventario asignado a esta cabaña.
                            </p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-info-circle"></i> No hay inventario asignado a esta cabaña.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acciones adicionales -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-cogs"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= $this->url('/reservas/create') ?>?cabania=<?= $cabania['id_cabania'] ?>"
                            class="btn btn-outline-primary">
                            <i class="fas fa-calendar-plus"></i> Crear Reserva
                        </a>
                        <a href="<?= $this->url('/reservas') ?>?cabania=<?= $cabania['id_cabania'] ?>"
                            class="btn btn-outline-info">
                            <i class="fas fa-list"></i> Ver Reservas
                        </a>
                        <?php if (!empty($cabania['cabania_foto'])): ?>
                            <a href="<?= $this->asset('imagenes/cabanias/' . $cabania['cabania_foto']) ?>"
                                target="_blank" class="btn btn-outline-secondary">
                                <i class="fas fa-eye"></i> Ver Foto Completa
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para funcionalidades -->
<script>
function cambiarEstadoCabania(id, nuevoEstado, nombre) {
    let accion, mensaje, color;
    
    switch(nuevoEstado) {
        case 1:
            accion = 'activar';
            mensaje = 'La cabaña estará disponible para reservas';
            color = '#28a745';
            break;
        case 2:
            accion = 'marcar como ocupada';
            mensaje = 'La cabaña se marcará como ocupada por huéspedes';
            color = '#ffc107';
            break;
        case 0:
            accion = 'desactivar';
            mensaje = 'La cabaña no estará disponible para reservas';
            color = '#dc3545';
            break;
        default:
            accion = 'cambiar estado';
            mensaje = '';
            color = '#6c757d';
    }
    
    console.log('Cambiando estado:', { id, nuevoEstado, nombre, accion });

    // Usar SweetAlert si está disponible, sino usar confirm simple
    const confirmar = typeof Swal !== 'undefined' ? 
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} cabaña?`,
            text: `¿Está seguro que desea ${accion} la cabaña "${nombre}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then(result => result.isConfirmed) :
        Promise.resolve(confirm(`¿Está seguro que desea ${accion} la cabaña "${nombre}"?`));
    
    confirmar.then(confirmed => {
        if (confirmed) {
            const url = `<?= $this->url('/cabanias') ?>/${id}/estado`;
            console.log('URL de petición:', url);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({estado: nuevoEstado})
            })
            .then(response => {
                console.log('Respuesta recibida:', response.status, response.statusText);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                
                if (data.success) {
                    // Usar SweetAlert para éxito si está disponible
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: `Cabaña ${accion}da correctamente`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        alert(`Cabaña ${accion}da correctamente`);
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
                const errorMsg = 'Error al cambiar el estado de la cabaña: ' + error.message;
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