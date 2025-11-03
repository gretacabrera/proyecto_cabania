<?php

/**
 * Vista: Detalle de Categoría
 * Descripción: Muestra información completa de una categoría
 * Autor: Sistema MVC
 * Fecha: <?php echo date('Y-m-d'); ?>
 */

// Validar que existe la categoría
if (!isset($categoria) || empty($categoria)) {
    echo '<div class="alert alert-danger">Categoría no encontrada.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/categorias') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= url('/categorias/' . $categoria['id_categoria'] . '/edit') ?>"
                    class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Categoría
                </a>
                
                <?php if ($categoria['categoria_estado'] == 1): ?>
                    <!-- Categoría activa: puede desactivar -->
                    <button class="btn btn-danger ms-2"
                        onclick="cambiarEstado(<?= $categoria['id_categoria'] ?>, 0)">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <!-- Categoría inactiva: puede activar -->
                    <button class="btn btn-success ms-2"
                        onclick="cambiarEstado(<?= $categoria['id_categoria'] ?>, 1)">
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
                                <strong>Descripción:</strong>
                                <h4 class="text-primary mt-2"><?= htmlspecialchars($categoria['categoria_descripcion']) ?></h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group">
                                <label class="info-label">
                                    <strong>Estado:</strong><br>
                                    <?php if ($categoria['categoria_estado'] == 1): ?>
                                        <span class="badge bg-success badge-lg">
                                            Activa
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger badge-lg">
                                            Inactiva
                                        </span>
                                    <?php endif; ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas de productos -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Estadísticas de Productos
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($estadisticas)): ?>
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <div class="stat-icon bg-primary">
                                        <i class="fas fa-boxes"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4 class="stat-number"><?= number_format($estadisticas['total_productos']) ?></h4>
                                        <p class="stat-label">Total Productos</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <div class="stat-icon bg-success">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4 class="stat-number"><?= number_format($estadisticas['productos_activos']) ?></h4>
                                        <p class="stat-label">Productos Activos</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <div class="stat-icon bg-danger">
                                        <i class="fas fa-times-circle"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4 class="stat-number"><?= number_format($estadisticas['productos_inactivos']) ?></h4>
                                        <p class="stat-label">Productos Inactivos</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <div class="stat-icon bg-info">
                                        <i class="fas fa-warehouse"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4 class="stat-number"><?= number_format($estadisticas['stock_total']) ?></h4>
                                        <p class="stat-label">Stock Total</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            No hay estadísticas disponibles para esta categoría.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <!-- Información técnica -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Información Técnica</h6>
                </div>
                <div class="card-body">
                    <div class="technical-info">
                        <div class="info-row">
                            <span class="info-label">Estado actual:</span>
                            <span class="badge <?= $categoria['categoria_estado'] == 1 ? 'bg-success' : 'bg-danger' ?>">
                                <?= $categoria['categoria_estado'] == 1 ? 'Activa' : 'Inactiva' ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Longitud del texto:</span>
                            <span><?= strlen($categoria['categoria_descripcion']) ?> de 45 caracteres</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Acciones Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= url('/productos?categoria=' . $categoria['id_categoria']) ?>" 
                           class="btn btn-outline-primary btn-sm">
                            Ver productos de esta categoría
                        </a>
                        <a href="<?= url('/categorias/create') ?>" 
                           class="btn btn-outline-success btn-sm">
                            Crear nueva categoría
                        </a>
                        <hr>
                        <a href="<?= url('/categorias/exportar?categoria_descripcion=' . urlencode($categoria['categoria_descripcion'])) ?>" 
                           class="btn btn-outline-info btn-sm">
                            Exportar esta categoría
                        </a>
                    </div>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Información Adicional</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <strong>¿Qué son las categorías?</strong><br>
                        Las categorías sirven para organizar los productos del sistema, facilitando su búsqueda y gestión.
                    </small>
                    <hr>
                    <small class="text-muted">
                        <strong>Estados disponibles:</strong><br>
                        <span class="badge bg-success">Activa</span>: La categoría está disponible para asignar a productos<br>
                        <span class="badge bg-danger">Inactiva</span>: La categoría no está disponible (oculta)
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-group {
    margin-bottom: 1rem;
}

.info-group i {
    width: 16px;
    margin-right: 8px;
}

.stat-item {
    text-align: center;
    margin-bottom: 1.5rem;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    color: white;
    font-size: 1.5rem;
}

.stat-number {
    font-size: 1.75rem;
    font-weight: bold;
    margin-bottom: 0;
    color: #495057;
}

.stat-label {
    color: #6c757d;
    font-size: 0.875rem;
    margin-bottom: 0;
}

.technical-info .info-row {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    padding: 8px 0;
    border-bottom: 1px solid #f8f9fa;
}

.technical-info .info-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.technical-info .info-row i {
    width: 20px;
    margin-right: 10px;
}

.technical-info .info-label {
    font-weight: 500;
    margin-right: 8px;
    min-width: 80px;
}

.badge-lg {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}
</style>

<script>
/**
 * Cambiar estado de categoría
 */
function cambiarEstado(id, nuevoEstado) {
    const estadoTexto = nuevoEstado === 1 ? 'activa' : 'inactiva';
    
    Swal.fire({
        title: '¿Estás seguro?',
        text: `La categoría será marcada como ${estadoTexto}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cambiar estado',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`<?= url('/categorias') ?>/${id}/estado`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ estado: nuevoEstado })
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
                        data.message || 'Error al cambiar el estado',
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire(
                    'Error',
                    'Error de conexión. Inténtalo de nuevo.',
                    'error'
                );
            });
        }
    });
}
</script>