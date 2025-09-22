<?php
$this->extend('layouts/main');
$this->section('title', $title);
$this->section('content');
?>

<div class="admin-header">
    <h1><?= $title ?></h1>
    <div class="header-actions">
        <a href="/consumos" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
        <a href="/consumos/<?= $consumo['id_consumo'] ?>/edit" class="btn btn-warning">
            <i class="fas fa-edit"></i> Editar
        </a>
    </div>
</div>

<div class="detail-container">
    <!-- Información Principal del Consumo -->
    <div class="detail-sections">
        <div class="detail-section primary-info">
            <h3>Información del Consumo</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>ID de Consumo:</label>
                    <span class="value">#<?= $consumo['id_consumo'] ?></span>
                </div>
                <div class="detail-item">
                    <label>Fecha y Hora:</label>
                    <span class="value date-value">
                        <i class="fas fa-calendar-alt"></i>
                        <?= date('d/m/Y H:i:s', strtotime($consumo['consumo_fecha'])) ?>
                    </span>
                </div>
                <div class="detail-item">
                    <label>Estado:</label>
                    <span class="value">
                        <?php if ($consumo['consumo_estado']): ?>
                            <?php if ($consumo['consumo_facturado'] ?? false): ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle"></i> Facturado
                                </span>
                            <?php else: ?>
                                <span class="badge badge-warning">
                                    <i class="fas fa-clock"></i> Pendiente
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge badge-danger">
                                <i class="fas fa-times-circle"></i> Eliminado
                            </span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="detail-item full-width">
                    <label>Observaciones:</label>
                    <span class="value">
                        <?= !empty($consumo['consumo_observaciones']) 
                            ? nl2br(htmlspecialchars($consumo['consumo_observaciones'])) 
                            : '<em class="text-muted">Sin observaciones</em>' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Información de la Reserva -->
        <div class="detail-section reservation-info">
            <h3>Información de la Reserva</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>ID Reserva:</label>
                    <span class="value">
                        <a href="/reservas/<?= $consumo['rela_reserva'] ?>" class="text-primary">
                            #<?= $consumo['rela_reserva'] ?>
                        </a>
                    </span>
                </div>
                <div class="detail-item">
                    <label>Huésped:</label>
                    <span class="value guest-name">
                        <i class="fas fa-user"></i>
                        <?= htmlspecialchars($consumo['huesped_nombre'] ?? '') ?>
                        <?= htmlspecialchars($consumo['huesped_apellido'] ?? '') ?>
                    </span>
                </div>
                <div class="detail-item">
                    <label>Cabaña:</label>
                    <span class="value cabin-name">
                        <i class="fas fa-home"></i>
                        <?= htmlspecialchars($consumo['cabania_nombre'] ?? '') ?>
                    </span>
                </div>
                <div class="detail-item">
                    <label>Período:</label>
                    <span class="value period-dates">
                        <i class="fas fa-calendar-week"></i>
                        <?= date('d/m/Y', strtotime($consumo['reserva_fecha_desde'] ?? '')) ?> - 
                        <?= date('d/m/Y', strtotime($consumo['reserva_fecha_hasta'] ?? '')) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Información del Producto -->
        <div class="detail-section product-info">
            <h3>Información del Producto</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Producto:</label>
                    <span class="value product-name">
                        <i class="fas fa-shopping-bag"></i>
                        <?= htmlspecialchars($consumo['producto_nombre']) ?>
                    </span>
                </div>
                <div class="detail-item">
                    <label>Categoría:</label>
                    <span class="value">
                        <?= htmlspecialchars($consumo['categoria_descripcion'] ?? '') ?>
                    </span>
                </div>
                <div class="detail-item">
                    <label>Marca:</label>
                    <span class="value">
                        <?= htmlspecialchars($consumo['marca_descripcion'] ?? '') ?>
                    </span>
                </div>
                <div class="detail-item">
                    <label>Descripción:</label>
                    <span class="value">
                        <?= !empty($consumo['producto_descripcion']) 
                            ? htmlspecialchars($consumo['producto_descripcion']) 
                            : '<em class="text-muted">Sin descripción</em>' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Información de Cantidades y Precios -->
        <div class="detail-section pricing-info">
            <h3>Cantidades y Precios</h3>
            <div class="pricing-grid">
                <div class="pricing-item">
                    <div class="pricing-card quantity">
                        <div class="pricing-icon">
                            <i class="fas fa-sort-numeric-up"></i>
                        </div>
                        <div class="pricing-content">
                            <label>Cantidad</label>
                            <span class="value"><?= number_format($consumo['consumo_cantidad'], 0) ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="pricing-item">
                    <div class="pricing-card unit-price">
                        <div class="pricing-icon">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div class="pricing-content">
                            <label>Precio Unitario</label>
                            <span class="value">$<?= number_format($consumo['consumo_precio_unitario'], 2) ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="pricing-item">
                    <div class="pricing-card total">
                        <div class="pricing-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="pricing-content">
                            <label>Subtotal</label>
                            <span class="value total-amount">$<?= number_format($consumo['consumo_subtotal'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial y Acciones -->
        <?php if (!empty($consumo['consumo_fecha_creacion']) || !empty($consumo['consumo_fecha_modificacion'])): ?>
        <div class="detail-section audit-info">
            <h3>Información de Auditoría</h3>
            <div class="detail-grid">
                <?php if (!empty($consumo['consumo_fecha_creacion'])): ?>
                <div class="detail-item">
                    <label>Fecha de Registro:</label>
                    <span class="value">
                        <i class="fas fa-plus-circle"></i>
                        <?= date('d/m/Y H:i:s', strtotime($consumo['consumo_fecha_creacion'])) ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($consumo['consumo_fecha_modificacion'])): ?>
                <div class="detail-item">
                    <label>Última Modificación:</label>
                    <span class="value">
                        <i class="fas fa-edit"></i>
                        <?= date('d/m/Y H:i:s', strtotime($consumo['consumo_fecha_modificacion'])) ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($consumo['usuario_creacion'])): ?>
                <div class="detail-item">
                    <label>Registrado por:</label>
                    <span class="value">
                        <i class="fas fa-user-shield"></i>
                        <?= htmlspecialchars($consumo['usuario_creacion']) ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Panel de Acciones -->
    <div class="actions-panel">
        <h4>Acciones Disponibles</h4>
        
        <?php if ($consumo['consumo_estado']): ?>
            <?php if (!($consumo['consumo_facturado'] ?? false)): ?>
                <a href="/consumos/<?= $consumo['id_consumo'] ?>/edit" class="action-button edit">
                    <i class="fas fa-edit"></i>
                    <span>Editar Consumo</span>
                </a>
                
                <a href="/consumos/facturar/<?= $consumo['rela_reserva'] ?>" class="action-button invoice">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Facturar</span>
                </a>
            <?php endif; ?>
            
            <?php if ($this->userCan('consumos_delete')): ?>
                <button type="button" class="action-button delete" 
                        data-action="delete" data-id="<?= $consumo['id_consumo'] ?>">
                    <i class="fas fa-trash"></i>
                    <span>Eliminar</span>
                </button>
            <?php endif; ?>
        <?php else: ?>
            <?php if ($this->userCan('consumos_restore')): ?>
                <button type="button" class="action-button restore" 
                        data-action="restore" data-id="<?= $consumo['id_consumo'] ?>">
                    <i class="fas fa-undo"></i>
                    <span>Restaurar</span>
                </button>
            <?php endif; ?>
        <?php endif; ?>
        
        <a href="/consumos/by-reserva/<?= $consumo['rela_reserva'] ?>" class="action-button related">
            <i class="fas fa-list"></i>
            <span>Ver Todos los Consumos de esta Reserva</span>
        </a>
    </div>
</div>


<?php $this->endSection(); ?>

<?php $this->endSection(); ?>