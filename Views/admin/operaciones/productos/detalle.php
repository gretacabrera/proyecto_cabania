<?php
// Los datos ya vienen preparados desde el controlador
// Variables disponibles:
// $producto - datos completos del producto con relaciones
// $error_message - mensaje de error (si aplica)

if (isset($error_message)) {
    echo '<div class="alert alert-error">' . htmlspecialchars($error_message) . '</div>';
    exit;
}

if (!$producto) {
    echo '<div class="alert alert-error">Producto no encontrado</div>';
    exit;
}
?>

<h1>Detalle del Producto</h1>

<div class="product-detail-container">
    <div class="product-header">
        <div class="product-image">
            <?php if (!empty($producto['producto_foto'])): ?>
                <img src="/proyecto_cabania/imagenes/productos/<?php echo $producto['producto_foto']; ?>" 
                     alt="<?php echo htmlspecialchars($producto['producto_nombre']); ?>"
                     class="product-main-image">
            <?php else: ?>
                <div class="no-image">
                    <i class="fa fa-image"></i>
                    <p>Sin imagen disponible</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="product-info">
            <h2><?php echo htmlspecialchars($producto['producto_nombre']); ?></h2>
            
            <div class="product-meta">
                <div class="meta-item">
                    <label>Categoría:</label>
                    <span><?php echo htmlspecialchars($producto['categoria_descripcion'] ?? 'No especificada'); ?></span>
                </div>
                
                <div class="meta-item">
                    <label>Marca:</label>
                    <span><?php echo htmlspecialchars($producto['marca_descripcion'] ?? 'No especificada'); ?></span>
                </div>
                
                <div class="meta-item">
                    <label>Estado:</label>
                    <span class="status-<?php echo $producto['rela_estadoproducto']; ?>">
                        <?php echo htmlspecialchars($producto['estadoproducto_descripcion'] ?? 'No especificado'); ?>
                    </span>
                </div>
            </div>
            
            <div class="product-pricing">
                <div class="price-item">
                    <label>Precio:</label>
                    <span class="price">$<?php echo number_format($producto['producto_precio'], 2); ?></span>
                </div>
                
                <div class="price-item">
                    <label>Stock Disponible:</label>
                    <span class="stock <?php echo $producto['producto_stock'] <= 5 ? 'low-stock' : ''; ?>">
                        <?php echo $producto['producto_stock']; ?> unidades
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="product-description">
        <h3>Descripción</h3>
        <p><?php echo nl2br(htmlspecialchars($producto['producto_descripcion'])); ?></p>
    </div>
    
    <div class="product-actions">
        <button class="btn btn-primary" data-action="navegar-editar-producto" data-id="<?php echo $producto['id_producto']; ?>">
            <i class="fa fa-edit"></i> Editar Producto
        </button>
        
        <?php if ($producto['rela_estadoproducto'] != 4): ?>
            <button class="btn btn-danger" data-action="confirmar-eliminacion-producto" data-id="<?php echo $producto['id_producto']; ?>">
                <i class="fa fa-trash"></i> Eliminar Producto
            </button>
        <?php else: ?>
            <button class="btn btn-success" data-action="confirmar-recuperacion-producto" data-id="<?php echo $producto['id_producto']; ?>">
                <i class="fa fa-undo"></i> Recuperar Producto
            </button>
        <?php endif; ?>
        
        <button class="btn btn-secondary" data-action="navegar-listado-productos">
            <i class="fa fa-arrow-left"></i> Volver al Listado
        </button>
    </div>
    
    <div class="product-details">
        <h3>Información Adicional</h3>
        <table class="details-table">
            <tr>
                <td><strong>ID del Producto:</strong></td>
                <td><?php echo $producto['id_producto']; ?></td>
            </tr>
            <tr>
                <td><strong>Fecha de Creación:</strong></td>
                <td><?php echo isset($producto['fecha_creacion']) ? date('d/m/Y H:i', strtotime($producto['fecha_creacion'])) : 'No disponible'; ?></td>
            </tr>
            <tr>
                <td><strong>Última Actualización:</strong></td>
                <td><?php echo isset($producto['fecha_actualizacion']) ? date('d/m/Y H:i', strtotime($producto['fecha_actualizacion'])) : 'No disponible'; ?></td>
            </tr>
        </table>
    </div>
</div>

<?php
$mysql->close();
?>

<?php $this->endSection(); ?>
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.product-header {
    display: flex;
    gap: 30px;
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 2px solid #eee;
}

.product-image {
    flex: 0 0 300px;
}

.product-main-image {
    width: 100%;
    max-width: 300px;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.no-image {
    width: 300px;
    height: 300px;
    background-color: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

.no-image i {
    font-size: 48px;
    margin-bottom: 10px;
}

.product-info {
    flex: 1;
}

.product-info h2 {
    color: #333;
    margin-bottom: 20px;
    font-size: 28px;
}

.product-meta {
    margin-bottom: 20px;
}

.meta-item {
    display: flex;
    margin-bottom: 10px;
}

.meta-item label {
    font-weight: bold;
    min-width: 100px;
    color: #555;
}

.meta-item span {
    color: #333;
}

.status-1 { color: #28a745; }
.status-2 { color: #ffc107; }
.status-3 { color: #dc3545; }
.status-4 { color: #6c757d; }

.product-pricing {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.price-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.price-item:last-child {
    margin-bottom: 0;
}

.price {
    font-size: 24px;
    font-weight: bold;
    color: #28a745;
}

.stock {
    font-weight: bold;
}

.low-stock {
    color: #dc3545;
}

.product-description {
    margin-bottom: 30px;
    padding: 20px;
    background-color: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
}

.product-description h3 {
    color: #333;
    margin-bottom: 15px;
}

.product-actions {
    text-align: center;
    margin-bottom: 30px;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    margin: 0 5px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
    transition: background-color 0.3s;
}

.btn i {
    margin-right: 5px;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background-color: #0056b3;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
}

.btn-success {
    background-color: #28a745;
    color: white;
}

.btn-success:hover {
    background-color: #218838;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #545b62;
}

.product-details {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}

.product-details h3 {
    color: #333;
    margin-bottom: 15px;
}

.details-table {
    width: 100%;
    border-collapse: collapse;
}

.details-table td {
    padding: 8px 12px;
    border-bottom: 1px solid #dee2e6;
}

.details-table tr:last-child td {
    border-bottom: none;
}

@media (max-width: 768px) {
    .product-header {
        flex-direction: column;
    }
    
    .product-image {
        flex: none;
    }
    
    .product-main-image,
    .no-image {
        max-width: 100%;
        width: 100%;
    }
}