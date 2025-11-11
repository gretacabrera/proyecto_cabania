<?php

/**
 * Vista: Detalle de Menú
 * Descripción: Muestra información completa de un menú
 * Autor: Sistema MVC
 * Fecha: 2025-11-11
 */

// Validar que existe el menú
if (!isset($menu) || empty($menu)) {
    echo '<div class="alert alert-danger">Menú no encontrado.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= $this->url('/menus') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= $this->url('/menus/' . $menu['id_menu']) . '/edit' ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Menú
                </a>
                
                <?php if ($menu['menu_estado'] == 1): ?>
                    <!-- Menú activo: puede desactivar -->
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstadoMenu(<?= $menu['id_menu'] ?>, 0, '<?= addslashes($menu['menu_nombre']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <!-- Menú inactivo: solo puede activar -->
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstadoMenu(<?= $menu['id_menu'] ?>, 1, '<?= addslashes($menu['menu_nombre']) ?>')">
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
                                <i class="fas fa-tag text-muted"></i> Nombre:
                                <strong><?= htmlspecialchars($menu['menu_nombre']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <i class="fas fa-sort-numeric-down text-muted"></i> Orden:
                                <code><?= $menu['menu_orden'] ?></code>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php if ($menu['menu_estado'] == 1): ?>
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
                                <div class="metric-value text-info"><?= number_format($estadisticas['perfiles_usando'] ?? 0) ?></div>
                                <div class="metric-label">Perfiles usando</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-primary"><?= number_format($estadisticas['total_modulos'] ?? 0) ?></div>
                                <div class="metric-label">Total módulos</div>
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
                        <a href="<?= $this->url('/modulos/create') ?>?menu=<?= $menu['id_menu'] ?>"
                            class="btn btn-outline-primary">
                            <i class="fas fa-plus"></i> Crear Módulo
                        </a>
                        <a href="<?= $this->url('/modulos') ?>?menu=<?= $menu['id_menu'] ?>"
                            class="btn btn-outline-info">
                            <i class="fas fa-list"></i> Ver Módulos
                        </a>
                        <a href="<?= $this->url('/menus') ?>"
                            class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Listado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para funcionalidades -->
<script>
function cambiarEstadoMenu(id, nuevoEstado, nombre) {
    let accion, mensaje, color;
    
    switch(nuevoEstado) {
        case 1:
            accion = 'activar';
            mensaje = 'El menú estará visible en el sistema';
            color = '#28a745';
            break;
        case 0:
            accion = 'desactivar';
            mensaje = 'El menú no estará visible en el sistema';
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
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} menú?`,
            text: `¿Está seguro que desea ${accion} el menú "${nombre}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then(result => result.isConfirmed) :
        Promise.resolve(confirm(`¿Está seguro que desea ${accion} el menú "${nombre}"?`));
    
    confirmar.then(confirmed => {
        if (confirmed) {
            const url = `<?= $this->url('/menus') ?>/${id}/estado`;
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
                            text: `Menú ${accion}do correctamente`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        alert(`Menú ${accion}do correctamente`);
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
                const errorMsg = 'Error al cambiar el estado del menú: ' + error.message;
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
