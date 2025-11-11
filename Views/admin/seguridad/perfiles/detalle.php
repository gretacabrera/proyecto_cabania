<?php

/**
 * Vista: Detalle de Perfil
 * Descripción: Muestra información completa de un perfil
 * Autor: Sistema MVC
 * Fecha: 2025-11-11
 */

// Validar que existe el perfil
if (!isset($perfil) || empty($perfil)) {
    echo '<div class="alert alert-danger">Perfil no encontrado.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/perfiles') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= url('/perfiles/' . $perfil['id_perfil']) . '/edit' ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Perfil
                </a>
                
                <?php if ($perfil['perfil_estado'] == 1): ?>
                    <!-- Perfil activo: puede desactivar -->
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstadoPerfil(<?= $perfil['id_perfil'] ?>, 0, '<?= addslashes($perfil['perfil_descripcion']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <!-- Perfil inactivo: solo puede activar -->
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstadoPerfil(<?= $perfil['id_perfil'] ?>, 1, '<?= addslashes($perfil['perfil_descripcion']) ?>')">
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
                        <div class="col-md-9">
                            <div class="info-group">
                                <i class="fas fa-tag text-muted"></i> Descripción:
                                <strong><?= htmlspecialchars($perfil['perfil_descripcion']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php if ($perfil['perfil_estado'] == 1): ?>
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
                                <div class="metric-value text-primary"><?= number_format($estadisticas['usuarios_totales']) ?></div>
                                <div class="metric-label">Total Usuarios</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-box">
                                <div class="metric-value text-info"><?= number_format($estadisticas['modulos_asignados']) ?></div>
                                <div class="metric-label">Módulos Asignados</div>
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
                        <a href="<?= url('/usuarios/create') ?>?perfil=<?= $perfil['id_perfil'] ?>"
                            class="btn btn-outline-primary">
                            <i class="fas fa-user-plus"></i> Asignar Usuario
                        </a>
                        <a href="<?= url('/usuarios') ?>?perfil=<?= $perfil['id_perfil'] ?>"
                            class="btn btn-outline-info">
                            <i class="fas fa-users"></i> Ver Usuarios
                        </a>
                        <a href="<?= url('/perfiles-modulos') ?>?perfil=<?= $perfil['id_perfil'] ?>"
                            class="btn btn-outline-secondary">
                            <i class="fas fa-key"></i> Gestionar Permisos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para funcionalidades -->
<script>
function cambiarEstadoPerfil(id, nuevoEstado, nombre) {
    const accion = nuevoEstado === 1 ? 'activar' : 'desactivar';
    const titulo = nuevoEstado === 1 ? '¿Activar perfil?' : '¿Desactivar perfil?';
    const mensaje = nuevoEstado === 1 
        ? 'El perfil estará disponible para asignar a usuarios'
        : 'El perfil no estará disponible para asignar a usuarios';
    const color = nuevoEstado === 1 ? '#28a745' : '#dc3545';
    
    console.log('Cambiando estado:', { id, nuevoEstado, nombre, accion });

    // Usar SweetAlert si está disponible, sino usar confirm simple
    const confirmar = typeof Swal !== 'undefined' ? 
        Swal.fire({
            title: titulo,
            text: `¿Está seguro que desea ${accion} el perfil "${nombre}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then(result => result.isConfirmed) :
        Promise.resolve(confirm(`¿Está seguro que desea ${accion} el perfil "${nombre}"?`));
    
    confirmar.then(confirmed => {
        if (confirmed) {
            const url = nuevoEstado === 0 
                ? '<?= url('/perfiles/') ?>' + id + '/delete'
                : '<?= url('/perfiles/') ?>' + id + '/restore';
            
            console.log('Redirigiendo a:', url);
            window.location.href = url;
        }
    });
}
</script>
