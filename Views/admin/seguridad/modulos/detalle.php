<?php

/**
 * Vista: Detalle de Módulo
 * Descripción: Muestra información completa de un módulo
 * Autor: Sistema MVC
 * Fecha: 2025-11-11
 */

// Validar que existe el módulo
if (!isset($modulo) || empty($modulo)) {
    echo '<div class="alert alert-danger">Módulo no encontrado.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= $this->url('/modulos') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= $this->url('/modulos/' . $modulo['id_modulo']) . '/edit' ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Módulo
                </a>
                
                <?php if ($modulo['modulo_estado'] == 1): ?>
                    <!-- Módulo activo: puede desactivar -->
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstadoModulo(<?= $modulo['id_modulo'] ?>, 0, '<?= addslashes($modulo['modulo_descripcion']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <!-- Módulo inactivo: solo puede activar -->
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstadoModulo(<?= $modulo['id_modulo'] ?>, 1, '<?= addslashes($modulo['modulo_descripcion']) ?>')">
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
                            <div class="info-group">
                                <i class="fas fa-align-left text-muted"></i> Descripción:
                                <strong><?= htmlspecialchars($modulo['modulo_descripcion']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php if ($modulo['modulo_estado'] == 1): ?>
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
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-link text-muted"></i> Ruta:
                                </label>
                                <div class="info-value">
                                    <code><?= htmlspecialchars($modulo['modulo_ruta']) ?></code>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-bars text-muted"></i> Menú Padre:
                                </label>
                                <div class="info-value">
                                    <?php if (isset($modulo['menu_nombre']) && $modulo['menu_nombre']): ?>
                                        <?= htmlspecialchars($modulo['menu_nombre']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Sin menú padre</span>
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
                                <div class="metric-value text-primary"><?= number_format($estadisticas['perfiles_asignados']) ?></div>
                                <div class="metric-label">Perfiles Asignados</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-success"><?= number_format($estadisticas['usuarios_con_permiso']) ?></div>
                                <div class="metric-label">Usuarios Con Permiso</div>
                            </div>
                        </div>
                    </div>
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
                        <a href="<?= $this->url('/perfiles-modulos') ?>?modulo=<?= $modulo['id_modulo'] ?>"
                            class="btn btn-outline-primary">
                            <i class="fas fa-users-cog"></i> Asignar Perfiles
                        </a>
                        <a href="<?= $this->url('/perfiles-modulos') ?>?modulo=<?= $modulo['id_modulo'] ?>"
                            class="btn btn-outline-info">
                            <i class="fas fa-list"></i> Ver Asignaciones
                        </a>
                        <?php if ($modulo['modulo_ruta']): ?>
                            <a href="<?= $this->url($modulo['modulo_ruta']) ?>"
                                target="_blank" class="btn btn-outline-secondary">
                                <i class="fas fa-external-link-alt"></i> Visitar Módulo
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
function cambiarEstadoModulo(id, nuevoEstado, descripcion) {
    let accion, mensaje, color;
    
    switch(nuevoEstado) {
        case 1:
            accion = 'activar';
            mensaje = 'El módulo estará disponible en el sistema';
            color = '#28a745';
            break;
        case 0:
            accion = 'desactivar';
            mensaje = 'El módulo no estará disponible en el sistema';
            color = '#dc3545';
            break;
        default:
            accion = 'cambiar estado';
            mensaje = '';
            color = '#6c757d';
    }
    
    console.log('Cambiando estado:', { id, nuevoEstado, descripcion, accion });

    // Usar SweetAlert si está disponible, sino usar confirm simple
    const confirmar = typeof Swal !== 'undefined' ? 
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} módulo?`,
            text: `¿Está seguro que desea ${accion} el módulo "${descripcion}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then(result => result.isConfirmed) :
        Promise.resolve(confirm(`¿Está seguro que desea ${accion} el módulo "${descripcion}"?`));
    
    confirmar.then(confirmed => {
        if (confirmed) {
            const url = `<?= $this->url('/modulos') ?>/${id}/estado`;
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
                            text: `Módulo ${accion}do correctamente`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        alert(`Módulo ${accion}do correctamente`);
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
                const errorMsg = 'Error al cambiar el estado del módulo: ' + error.message;
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
