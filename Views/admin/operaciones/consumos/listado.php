<?php
$this->extend('layouts/main');
$this->section('title', $title);
$this->section('content');
?>

<div class="admin-header">
    <h1><?= $title ?></h1>
    <div class="header-actions">
        <a href="/consumos/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Registrar Consumo
        </a>
        <a href="/consumos/reporte" class="btn btn-secondary">
            <i class="fas fa-chart-bar"></i> Reportes
        </a>
    </div>
</div>

<!-- Filtros de búsqueda -->
<div class="search-filters">
    <form method="GET" action="/consumos" class="filters-form">
        <div class="filter-row">
            <div class="filter-group">
                <label for="reserva">Reserva:</label>
                <select name="reserva" id="reserva">
                    <option value="">Todas las reservas</option>
                    <?php foreach ($reservas as $reserva): ?>
                        <option value="<?= $reserva['id_reserva'] ?>" 
                                <?= $filters['reserva'] == $reserva['id_reserva'] ? 'selected' : '' ?>>
                            #<?= $reserva['id_reserva'] ?> - <?= htmlspecialchars($reserva['huesped_nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="producto">Producto:</label>
                <select name="producto" id="producto">
                    <option value="">Todos los productos</option>
                    <?php foreach ($productos as $producto): ?>
                        <option value="<?= $producto['id_producto'] ?>" 
                                <?= $filters['producto'] == $producto['id_producto'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($producto['producto_nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="filter-row">
            <div class="filter-group">
                <label for="fecha_desde">Fecha desde:</label>
                <input type="date" name="fecha_desde" id="fecha_desde" 
                       value="<?= $filters['fecha_desde'] ?? '' ?>">
            </div>
            
            <div class="filter-group">
                <label for="fecha_hasta">Fecha hasta:</label>
                <input type="date" name="fecha_hasta" id="fecha_hasta" 
                       value="<?= $filters['fecha_hasta'] ?? '' ?>">
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <a href="/consumos" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </div>
    </form>
</div>

<?php if (empty($consumos)): ?>
    <div class="no-results">
        <i class="fas fa-shopping-cart"></i>
        <h3>No se encontraron consumos</h3>
        <p>No hay registros de consumos que coincidan con los filtros especificados.</p>
        <a href="/consumos/create" class="btn btn-primary">Registrar Primer Consumo</a>
    </div>
<?php else: ?>
    <div class="table-container">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Reserva</th>
                    <th>Huésped</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unit.</th>
                    <th>Subtotal</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($consumos as $consumo): ?>
                    <tr class="<?= $consumo['consumo_estado'] ? '' : 'table-row-disabled' ?>">
                        <td>
                            <span class="date-cell">
                                <?= date('d/m/Y H:i', strtotime($consumo['consumo_fecha'])) ?>
                            </span>
                        </td>
                        <td>
                            <a href="/consumos/by-reserva/<?= $consumo['rela_reserva'] ?>" class="text-primary">
                                #<?= $consumo['rela_reserva'] ?>
                            </a>
                        </td>
                        <td>
                            <div class="guest-info">
                                <strong><?= htmlspecialchars($consumo['huesped_nombre'] ?? '') ?></strong>
                                <?php if (!empty($consumo['huesped_apellido'])): ?>
                                    <span><?= htmlspecialchars($consumo['huesped_apellido']) ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="product-info">
                                <strong><?= htmlspecialchars($consumo['producto_nombre']) ?></strong>
                                <?php if (!empty($consumo['categoria_descripcion'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($consumo['categoria_descripcion']) ?></small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-quantity">
                                <?= number_format($consumo['consumo_cantidad'], 0) ?>
                            </span>
                        </td>
                        <td>
                            <span class="price-cell">
                                $<?= number_format($consumo['consumo_precio_unitario'], 2) ?>
                            </span>
                        </td>
                        <td>
                            <strong class="total-cell">
                                $<?= number_format($consumo['consumo_subtotal'], 2) ?>
                            </strong>
                        </td>
                        <td>
                            <?php if ($consumo['consumo_estado']): ?>
                                <?php if ($consumo['consumo_facturado'] ?? false): ?>
                                    <span class="badge badge-success">Facturado</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pendiente</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge badge-danger">Eliminado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="/consumos/<?= $consumo['id_consumo'] ?>" 
                                   class="btn btn-sm btn-info" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if ($consumo['consumo_estado']): ?>
                                    <?php if (!($consumo['consumo_facturado'] ?? false)): ?>
                                        <a href="/consumos/<?= $consumo['id_consumo'] ?>/edit" 
                                           class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($this->userCan('consumos_delete')): ?>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                data-action="delete" data-id="<?= $consumo['id_consumo'] ?>"
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if ($this->userCan('consumos_restore')): ?>
                                        <button type="button" class="btn btn-sm btn-success" 
                                                data-action="restore" data-id="<?= $consumo['id_consumo'] ?>"
                                                title="Restaurar">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Resumen de totales -->
    <div class="table-summary">
        <?php 
        $totalConsumos = count($consumos);
        $totalImporte = array_sum(array_column($consumos, 'consumo_subtotal'));
        ?>
        <div class="summary-item">
            <strong>Total registros:</strong> <?= $totalConsumos ?>
        </div>
        <div class="summary-item">
            <strong>Total importe:</strong> $<?= number_format($totalImporte, 2) ?>
        </div>
    </div>

    <!-- Paginación -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination-container">
            <nav aria-label="Paginación de consumos">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <?php 
                            $url = '/consumos?page=' . $i;
                            foreach ($filters as $key => $value) {
                                if (!empty($value)) {
                                    $url .= '&' . $key . '=' . urlencode($value);
                                }
                            }
                            ?>
                            <a class="page-link" href="<?= $url ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php $this->endSection(); ?>

.guest-info, .product-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.guest-info strong, .product-info strong {
    font-weight: 600;
    color: #333;
}

.guest-info span, .product-info small {
    font-size: 0.85em;
    color: #666;
}

<?php $this->endSection(); ?>