<?php
/**
 * Vista: Detalle de Parámetro General
 * Descripción: Muestra información completa de un parámetro de configuración
 */

// Validar que existe el parámetro
if (!isset($parametro) || empty($parametro)) {
    echo '<div class="alert alert-danger">Parámetro no encontrado.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/parametrosgenerales') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= url('/parametrosgenerales/' . $parametro['id_parametrogeneral'] . '/edit') ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Parámetro
                </a>
                
                <?php if ($parametro['parametrogeneral_estado'] == 1): ?>
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstado(<?= $parametro['id_parametrogeneral'] ?>)">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstado(<?= $parametro['id_parametrogeneral'] ?>)">
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
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información General
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <i class="fas fa-barcode text-muted"></i> Código:
                                <code class="fs-5"><?= htmlspecialchars($parametro['parametrogeneral_codigo']) ?></code>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php if ($parametro['parametrogeneral_estado'] == 1): ?>
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
                                    <i class="fas fa-align-left text-muted"></i> Descripción / Valor:
                                </label>
                                <div class="info-value">
                                    <?= nl2br(htmlspecialchars($parametro['parametrogeneral_descripcion'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <!-- Acciones rápidas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= url('/parametrosgenerales/' . $parametro['id_parametrogeneral'] . '/edit') ?>" 
                           class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-edit"></i> Editar Parámetro
                        </a>
                        
                        <?php if ($parametro['parametrogeneral_estado'] == 1): ?>
                            <button onclick="cambiarEstado(<?= $parametro['id_parametrogeneral'] ?>)" 
                                    class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-ban"></i> Desactivar
                            </button>
                        <?php else: ?>
                            <button onclick="cambiarEstado(<?= $parametro['id_parametrogeneral'] ?>)" 
                                    class="btn btn-outline-success btn-sm">
                                <i class="fas fa-check"></i> Activar
                            </button>
                        <?php endif; ?>
                        
                        <a href="<?= url('/parametrosgenerales') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list"></i> Ver Todos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cambiarEstado(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Deseas cambiar el estado de este parámetro?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`<?= url('/parametrosgenerales/') ?>${id}/estado`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        '¡Actualizado!',
                        data.message,
                        'success'
                    ).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire(
                        'Error',
                        data.message,
                        'error'
                    );
                }
            })
            .catch(error => {
                Swal.fire(
                    'Error',
                    'Ocurrió un error al cambiar el estado',
                    'error'
                );
            });
        }
    });
}
</script>

<style>
.info-group {
    margin-bottom: 1rem;
}

.info-group label {
    display: block;
    margin-bottom: 0.25rem;
}

pre code {
    font-size: 0.875rem;
}

.metric-box {
    padding: 1rem;
    text-align: center;
}

.metric-value {
    font-size: 2rem;
    font-weight: bold;
}

.metric-label {
    font-size: 0.875rem;
    color: #6c757d;
}
</style>
