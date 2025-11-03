<?php

/**
 * Vista: Detalle de Categoría
 * Descripción: Muestra información completa de una categoría
 * Autor: Sistema MVC
 * Fecha: 2025-11-03
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
                <a href="<?= $this->url('/categorias') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= $this->url('/categorias/' . $categoria['id_categoria']) . '/edit' ?>"
                   class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Categoría
                </a>
                
                <?php if ($categoria['categoria_estado'] == 1): ?>
                    <!-- Categoría activa: puede desactivar -->
                    <button class="btn btn-danger ms-2"
                            onclick="cambiarEstadoCategoria(<?= $categoria['id_categoria'] ?>, 0, '<?= addslashes($categoria['categoria_descripcion']) ?>')">
                        <i class="fas fa-ban"></i> Desactivar
                    </button>
                <?php else: ?>
                    <!-- Categoría inactiva: puede activar -->
                    <button class="btn btn-success ms-2"
                            onclick="cambiarEstadoCategoria(<?= $categoria['id_categoria'] ?>, 1, '<?= addslashes($categoria['categoria_descripcion']) ?>')">
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
                                <strong><?= htmlspecialchars($categoria['categoria_descripcion']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group">
                                <label class="info-label">
                                    <?php if ($categoria['categoria_estado'] == 1): ?>
                                        <i class="fas fa-toggle-on text-success"></i> Estado: 
                                        <span class="badge badge-success badge-lg">
                                            <i class="fas fa-check"></i> Activa
                                        </span>
                                    <?php else: ?>
                                        <i class="fas fa-toggle-off text-danger"></i> Estado: 
                                        <span class="badge badge-danger badge-lg">
                                            <i class="fas fa-times"></i> Inactiva
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
                    <?php if (isset($estadisticas)): ?>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="metric-box">
                                    <div class="metric-value text-primary"><?= number_format($estadisticas['total_productos'] ?? 0) ?></div>
                                    <div class="metric-label">Total productos</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="metric-box">
                                    <div class="metric-value text-success"><?= number_format($estadisticas['productos_vendidos'] ?? 0) ?></div>
                                    <div class="metric-label">Unidades vendidas</div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row text-center">
                            <div class="col-6">
                                <div class="metric-box">
                                    <div class="metric-value text-warning"><?= number_format($estadisticas['porcentaje_categoria'] ?? 0, 2) ?></div>
                                    <div class="metric-label">% del inventario</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="metric-box">
                                    <div class="metric-value text-info">$<?= number_format($estadisticas['ingresos_ventas'] ?? 0, 2) ?></div>
                                    <div class="metric-label">Ingresos totales</div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Las estadísticas se actualizarán cuando se agreguen productos a esta categoría.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acciones adicionales -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= $this->url('/productos/create') ?>?categoria=<?= $categoria['id_categoria'] ?>"
                            class="btn btn-outline-primary">
                            <i class="fas fa-box"></i> Crear Producto
                        </a>
                        <a href="<?= $this->url('/productos') ?>?categoria=<?= $categoria['id_categoria'] ?>"
                            class="btn btn-outline-info">
                            <i class="fas fa-boxes"></i> Ver Productos
                        </a>
                        <a href="<?= $this->url('/categorias/create') ?>"
                            class="btn btn-outline-success">
                            <i class="fas fa-plus"></i> Nueva Categoría
                        </a>
                        <a href="<?= $this->url('/categorias/exportar') ?>"
                            class="btn btn-outline-secondary">
                            <i class="fas fa-file-excel"></i> Exportar Listado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para funcionalidades -->
<script>
function cambiarEstadoCategoria(id, nuevoEstado, nombre) {
    let accion, mensaje, color;
    
    switch(nuevoEstado) {
        case 1:
            accion = 'activar';
            mensaje = 'La categoría estará disponible para productos';
            color = '#28a745';
            break;
        case 0:
            accion = 'desactivar';
            mensaje = 'La categoría no estará disponible para nuevos productos';
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
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} categoría?`,
            text: `¿Está seguro que desea ${accion} la categoría "${nombre}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then(result => result.isConfirmed) :
        Promise.resolve(confirm(`¿Está seguro que desea ${accion} la categoría "${nombre}"?`));
    
    confirmar.then(confirmed => {
        if (confirmed) {
            const url = `<?= $this->url('/categorias') ?>/${id}/estado`;
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
                            text: `Categoría ${accion}da correctamente`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        alert(`Categoría ${accion}da correctamente`);
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
                const errorMsg = 'Error al cambiar el estado de la categoría: ' + error.message;
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

<style>
/* Estilos personalizados siguiendo el patrón de productos */
.info-group {
    margin-bottom: 0.75rem;
}

.info-group .info-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.25rem;
    display: block;
}

.info-group .info-value {
    color: #212529;
    margin-left: 1.5rem;
}

.metric-box {
    padding: 0.5rem;
}

.metric-value {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.25rem;
}

.metric-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card-title {
    font-size: 0.875rem;
    font-weight: 600;
}

.badge-lg {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
}

.btn-group-vertical .btn {
    border-radius: 0.25rem !important;
}
</style>