<?php

/**
 * Vista: Detalle de Usuario
 * Descripción: Muestra información completa de un usuario
 */

// Validar que existe el usuario
if (!isset($usuario) || empty($usuario)) {
    echo '<div class="alert alert-danger">Usuario no encontrado.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= $this->url('/usuarios') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= $this->url('/usuarios/' . $usuario['id_usuario']) . '/edit' ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Usuario
                </a>
                
                <?php if ($usuario['usuario_estado'] == 1): ?>
                    <!-- Usuario activo: puede desactivar -->
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstadoUsuario(<?= $usuario['id_usuario'] ?>, 0, '<?= addslashes($usuario['usuario_nombre']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php elseif ($usuario['usuario_estado'] == 2): ?>
                    <!-- Usuario pendiente: puede reenviar verificación o desactivar -->
                    <a href="<?= $this->url('/usuarios/' . $usuario['id_usuario']) . '/resend-verification' ?>" 
                       class="btn btn-info ms-2">
                        <i class="fas fa-envelope"></i> Reenviar Verificación
                    </a>
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstadoUsuario(<?= $usuario['id_usuario'] ?>, 0, '<?= addslashes($usuario['usuario_nombre']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <!-- Usuario inactivo: solo puede activar -->
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstadoUsuario(<?= $usuario['id_usuario'] ?>, 1, '<?= addslashes($usuario['usuario_nombre']) ?>')">
                        <i class="fas fa-check"></i> Activar
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información principal -->
        <div class="col-lg-8">
            <!-- Información General -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información General
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-group">
                                <label class="info-label">Usuario:</label>
                                <div class="info-value">
                                    <strong><?= htmlspecialchars($usuario['usuario_nombre']) ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="info-group">
                                <label class="info-label">Persona:</label>
                                <div class="info-value">
                                    <strong><?= htmlspecialchars(trim(($usuario['persona_nombre'] ?? '') . ' ' . ($usuario['persona_apellido'] ?? ''))) ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <label class="info-label">Estado:</label>
                                <div class="info-value">
                                    <?php if ($usuario['usuario_estado'] == 1): ?>
                                        <span class="text-success">Activo</span>
                                    <?php elseif ($usuario['usuario_estado'] == 2): ?>
                                        <span class="text-warning">Pendiente</span>
                                    <?php else: ?>
                                        <span class="text-danger">Inactivo</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">Email:</label>
                                <div class="info-value">
                                    <?= htmlspecialchars($usuario['persona_email'] ?? 'No registrado') ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">Perfil:</label>
                                <div class="info-value">
                                    <?= htmlspecialchars($usuario['perfil_descripcion'] ?? 'Sin perfil') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-group">
                                <label class="info-label">Estado de Verificación:</label>
                                <div class="info-value">
                                    <?php if ($usuario['usuario_estado'] == 1): ?>
                                        <span class="text-success">Verificado</span>
                                        <?php if (!empty($usuario['usuario_fhverificacion'])): ?>
                                            - Verificado el <?= date('d/m/Y H:i', strtotime($usuario['usuario_fhverificacion'])) ?>
                                        <?php endif; ?>
                                    <?php elseif ($usuario['usuario_estado'] == 2): ?>
                                        <span class="text-warning">Pendiente de Verificación</span>
                                    <?php else: ?>
                                        <span class="text-danger">Inactivo</span>
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
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (isset($estadisticas)): ?>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="metric-box">
                                    <div class="metric-value text-primary"><?= $estadisticas['total_reservas'] ?? 0 ?></div>
                                    <div class="metric-label">Total Reservas</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="metric-box">
                                    <div class="metric-value text-warning"><?= $estadisticas['reservas_activas'] ?? 0 ?></div>
                                    <div class="metric-label">Reservas Activas</div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="metric-box">
                                    <div class="metric-value text-success">$<?= number_format($estadisticas['total_gastado'] ?? 0, 0, ',', '.') ?></div>
                                    <div class="metric-label">Total Gastado</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="metric-box">
                                    <div class="metric-value text-info">
                                        <?php if (!empty($estadisticas['ultima_reserva'])): ?>
                                            <?= date('d/m/Y', strtotime($estadisticas['ultima_reserva']['reserva_fhinicio'])) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                    <div class="metric-label">Última Reserva</div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0 small">
                            <i class="fas fa-info-circle"></i> No hay estadísticas disponibles
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= $this->url('/usuarios/' . $usuario['id_usuario']) . '/edit' ?>" 
                           class="btn btn-outline-warning">
                            <i class="fas fa-edit"></i> Editar Información
                        </a>
                        
                        <?php if ($usuario['usuario_estado'] == 2): ?>
                            <a href="<?= $this->url('/usuarios/' . $usuario['id_usuario']) . '/resend-verification' ?>" 
                               class="btn btn-outline-info">
                                <i class="fas fa-envelope"></i> Reenviar Verificación
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($usuario['usuario_estado'] == 1): ?>
                            <button onclick="cambiarEstadoUsuario(<?= $usuario['id_usuario'] ?>, 0, '<?= addslashes($usuario['usuario_nombre']) ?>')" 
                                    class="btn btn-outline-danger">
                                <i class="fas fa-ban"></i> Desactivar Usuario
                            </button>
                        <?php else: ?>
                            <button onclick="cambiarEstadoUsuario(<?= $usuario['id_usuario'] ?>, 1, '<?= addslashes($usuario['usuario_nombre']) ?>')" 
                                    class="btn btn-outline-success">
                                <i class="fas fa-check"></i> Activar Usuario
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cambiarEstadoUsuario(id, nuevoEstado, nombre) {
    const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
    
    Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Deseas ${accion} el usuario "${nombre}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, ' + accion,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`<?= $this->url('/usuarios') ?>/${id}/estado`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `estado=${nuevoEstado}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('¡Éxito!', data.message, 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Hubo un problema al procesar la solicitud', 'error');
            });
        }
    });
}
</script>

<style>
.info-group {
    padding: 0.5rem 0;
}

.info-label {
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.info-value {
    color: #212529;
}

.metric-box {
    padding: 1rem;
}

.metric-value {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.metric-label {
    font-size: 0.875rem;
    color: #6c757d;
    text-transform: uppercase;
    font-weight: 600;
}

.badge-lg {
    font-size: 0.875rem;
    padding: 0.35rem 0.65rem;
}
</style>
