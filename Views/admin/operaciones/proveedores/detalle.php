<?php
/**
 * Vista: Detalle de Proveedor
 * Descripción: Muestra información completa de un proveedor
 */

// Validar que existe el proveedor
if (!isset($proveedor) || empty($proveedor)) {
    echo '<div class="alert alert-danger">Proveedor no encontrado.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/proveedores') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= url('/proveedores/' . $proveedor['id_proveedor'] . '/edit') ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Proveedor
                </a>
                
                <?php if ($proveedor['proveedor_estado'] == 1): ?>
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstadoProveedor(<?= $proveedor['id_proveedor'] ?>, 0, '<?= addslashes($proveedor['persona_denominacion']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstadoProveedor(<?= $proveedor['id_proveedor'] ?>, 1, '<?= addslashes($proveedor['persona_denominacion']) ?>')">
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
                                <i class="fas fa-building text-muted"></i> Denominación Social:
                                <strong><?= htmlspecialchars($proveedor['persona_denominacion']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <i class="fas fa-id-card text-muted"></i> CUIT:
                                <code><?= htmlspecialchars($proveedor['personajuridica_cuit']) ?></code>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php if ($proveedor['proveedor_estado'] == 1): ?>
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
                        <div class="col-md-12">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-map-marker-alt text-muted"></i> Dirección:
                                </label>
                                <div class="info-value">
                                    <?= htmlspecialchars($proveedor['persona_direccion']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-envelope text-muted"></i> Correo Electrónico:
                                </label>
                                <div class="info-value">
                                    <?php if (!empty($proveedor['contacto_correo'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($proveedor['contacto_correo']) ?>">
                                            <?= htmlspecialchars($proveedor['contacto_correo']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No especificado</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <i class="fas fa-phone text-muted"></i> Teléfono:
                                </label>
                                <div class="info-value">
                                    <?php if (!empty($proveedor['contacto_telefono'])): ?>
                                        <a href="tel:<?= htmlspecialchars($proveedor['contacto_telefono']) ?>">
                                            <?= htmlspecialchars($proveedor['contacto_telefono']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No especificado</span>
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
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <i class="fas fa-shopping-cart fa-2x text-primary mb-2"></i>
                            <div class="metric-value text-primary">
                                <?= number_format($estadisticas['total_compras'] ?? 0) ?>
                            </div>
                            <div class="metric-label">Compras Realizadas</div>
                        </div>
                        <div class="col-6">
                            <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                            <div class="metric-value text-success">
                                $<?= number_format($estadisticas['total_gastado'] ?? 0, 2, ',', '.') ?>
                            </div>
                            <div class="metric-label">Total Gastado</div>
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
                        <a href="<?= url('/compras/create?proveedor=' . $proveedor['id_proveedor']) ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-shopping-cart"></i> Nueva Compra
                        </a>
                        <a href="<?= url('/compras?proveedor=' . $proveedor['id_proveedor']) ?>" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-list"></i> Ver Historial de Compras
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
function cambiarEstadoProveedor(id, nuevoEstado, denominacion) {
    const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
    const mensaje = nuevoEstado == 1 ? 'El proveedor estará disponible para compras' : 'El proveedor no estará disponible';
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} proveedor?`,
            text: `¿Está seguro que desea ${accion} el proveedor "${denominacion}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: nuevoEstado == 1 ? '#28a745' : '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`<?= url('/proveedores/') ?>${id}/estado`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ estado: nuevoEstado })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('¡Listo!', data.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Hubo un problema al procesar la solicitud', 'error');
                });
            }
        });
    } else {
        if (confirm(`¿Está seguro que desea ${accion} el proveedor "${denominacion}"?`)) {
            window.location.href = `<?= url('/proveedores/') ?>${id}/${accion}`;
        }
    }
}
</script>

<style>
.info-group {
    margin-bottom: 0.75rem;
}

.info-label {
    font-weight: 500;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.info-value {
    color: #212529;
}

.info-value a {
    color: #0d6efd;
    text-decoration: none;
}

.info-value a:hover {
    text-decoration: underline;
}

.metric-value {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
    word-break: break-word;
}

.metric-label {
    font-size: 0.8rem;
    color: #6c757d;
    font-weight: 500;
}

.badge-lg {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid #dee2e6;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

@media (max-width: 768px) {
    .metric-value {
        font-size: 1.1rem;
    }
}
</style>
