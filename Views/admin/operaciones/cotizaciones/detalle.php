<?php
/**
 * Vista: Detalle de Cotización
 * Descripción: Muestra información completa de una cotización
 */

// Validar que existe la cotización
if (!isset($cotizacion) || empty($cotizacion)) {
    echo '<div class="alert alert-danger">Cotización no encontrada.</div>';
    return;
}
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/cotizaciones') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
            <div class="action-buttons">
                <a href="<?= url('/cotizaciones/' . $cotizacion['id_cotizacion'] . '/edit') ?>" 
                   class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Cotización
                </a>
                
                <a href="<?= url('/cotizaciones/create?proveedor=' . $cotizacion['rela_proveedor'] . '&producto=' . $cotizacion['rela_producto']) ?>" 
                   class="btn btn-info ms-2">
                    <i class="fas fa-redo"></i> Recotizar
                </a>
                
                <?php if ($cotizacion['cotizacion_estado'] == 1): ?>
                    <form method="POST" action="<?= url('/cotizaciones/' . $cotizacion['id_cotizacion'] . '/delete') ?>" 
                          style="display: inline-block;"
                          onsubmit="return confirm('¿Está seguro de anular esta cotización?');">
                        <button type="submit" class="btn btn-danger ms-2">
                            <i class="fas fa-ban"></i> Anular
                        </button>
                    </form>
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
                            <div class="info-group mb-3">
                                <label class="info-label"><i class="fas fa-truck text-muted"></i> Proveedor:</label>
                                <div class="info-value"><?= htmlspecialchars($cotizacion['proveedor_nombre']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group mb-3">
                                <label class="info-label"><i class="fas fa-box text-muted"></i> Producto:</label>
                                <div class="info-value"><?= htmlspecialchars($cotizacion['producto_nombre']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group mb-3">
                                <label class="info-label"><i class="fas fa-dollar-sign text-muted"></i> Monto:</label>
                                <div class="info-value text-success fw-bold">$<?= number_format($cotizacion['cotizacion_monto'], 2, ',', '.') ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group mb-3">
                                <label class="info-label"><i class="fas fa-calendar text-muted"></i> Fecha/Hora:</label>
                                <div class="info-value"><?= date('d/m/Y H:i', strtotime($cotizacion['cotizacion_fechahora'])) ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group mb-3">
                                <label class="info-label"><i class="fas fa-info-circle text-muted"></i> Estado:</label>
                                <div class="info-value">
                                    <?php if ($cotizacion['cotizacion_estado'] == 1): ?>
                                        <span class="badge bg-success"><i class="fas fa-check"></i> Activa</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="fas fa-ban"></i> Anulada</span>
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
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="stat-box text-center p-3 rounded">
                                <div class="stat-value text-primary fw-bold"><?= $estadisticas['total_cotizaciones'] ?? 0 ?></div>
                                <div class="stat-label small text-muted">Cotizaciones</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box text-center p-3 rounded">
                                <div class="stat-value text-success fw-bold">$<?= number_format($estadisticas['precio_minimo'] ?? 0, 0) ?></div>
                                <div class="stat-label small text-muted">Mínimo</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box text-center p-3 rounded">
                                <div class="stat-value text-danger fw-bold">$<?= number_format($estadisticas['precio_maximo'] ?? 0, 0) ?></div>
                                <div class="stat-label small text-muted">Máximo</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box text-center p-3 rounded">
                                <div class="stat-value text-info fw-bold">$<?= number_format($estadisticas['precio_promedio'] ?? 0, 0) ?></div>
                                <div class="stat-label small text-muted">Promedio</div>
                            </div>
                        </div>
                    </div>
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
                        <a href="<?= url('/cotizaciones/historial?producto=' . $cotizacion['rela_producto'] . '&proveedor=' . $cotizacion['rela_proveedor']) ?>" 
                           class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-history"></i> Ver Historial Completo
                        </a>
                        <a href="<?= url('/proveedores/' . $cotizacion['rela_proveedor']) ?>" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-truck"></i> Ver Proveedor
                        </a>
                        <a href="<?= url('/productos/' . $cotizacion['rela_producto']) ?>" 
                           class="btn btn-outline-info btn-sm">
                            <i class="fas fa-box"></i> Ver Producto
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-group {
    margin-bottom: 1rem;
}

.info-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
    display: block;
}

.info-value {
    font-size: 1rem;
    color: #212529;
    font-weight: 500;
}

.stat-box {
    transition: all 0.2s;
    background-color: transparent;
}

.stat-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.75rem;
}
</style>
