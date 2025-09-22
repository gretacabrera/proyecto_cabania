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
    </div>
</div>

<div class="form-container">
    <form method="POST" class="form-modern" id="consumoForm">
        <div class="form-sections">
            <!-- Sección de Reserva -->
            <div class="form-section">
                <h3>Información de la Reserva</h3>
                
                <div class="form-group">
                    <label for="rela_reserva" class="required">Reserva:</label>
                    <select name="rela_reserva" id="rela_reserva" required class="form-control">
                        <option value="">Seleccione una reserva...</option>
                        <?php foreach ($reservas as $reserva): ?>
                            <option value="<?= $reserva['id_reserva'] ?>" 
                                    data-huesped="<?= htmlspecialchars($reserva['huesped_nombre'] . ' ' . ($reserva['huesped_apellido'] ?? '')) ?>"
                                    data-cabania="<?= htmlspecialchars($reserva['cabania_nombre']) ?>"
                                    <?= (isset($consumo) && $consumo['rela_reserva'] == $reserva['id_reserva']) ? 'selected' : '' ?>>
                                #<?= $reserva['id_reserva'] ?> - <?= htmlspecialchars($reserva['huesped_nombre']) ?> 
                                (<?= date('d/m/Y', strtotime($reserva['reserva_fecha_desde'])) ?> - 
                                 <?= date('d/m/Y', strtotime($reserva['reserva_fecha_hasta'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="reservaInfo" class="reservation-info" style="display: none;">
                    <div class="info-card">
                        <h4>Detalles de la Reserva</h4>
                        <div class="info-row">
                            <span class="label">Huésped:</span>
                            <span id="infoHuesped">-</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Cabaña:</span>
                            <span id="infoCabania">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de Producto -->
            <div class="form-section">
                <h3>Producto Consumido</h3>
                
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label for="rela_producto" class="required">Producto:</label>
                        <select name="rela_producto" id="rela_producto" required class="form-control">
                            <option value="">Seleccione un producto...</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?= $producto['id_producto'] ?>" 
                                        data-precio="<?= $producto['producto_precio'] ?>"
                                        data-stock="<?= $producto['producto_stock'] ?>"
                                        <?= (isset($consumo) && $consumo['rela_producto'] == $producto['id_producto']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($producto['producto_nombre']) ?> - 
                                    $<?= number_format($producto['producto_precio'], 2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label for="stock_disponible">Stock Disponible:</label>
                        <input type="text" id="stock_disponible" class="form-control" readonly>
                    </div>
                </div>
            </div>

            <!-- Sección de Cantidades -->
            <div class="form-section">
                <h3>Cantidades y Precios</h3>
                
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="consumo_cantidad" class="required">Cantidad:</label>
                        <input type="number" name="consumo_cantidad" id="consumo_cantidad" 
                               value="<?= $consumo['consumo_cantidad'] ?? 1 ?>" 
                               min="1" step="1" required class="form-control">
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label for="consumo_precio_unitario" class="required">Precio Unitario:</label>
                        <input type="number" name="consumo_precio_unitario" id="consumo_precio_unitario" 
                               value="<?= $consumo['consumo_precio_unitario'] ?? '' ?>" 
                               min="0" step="0.01" required class="form-control">
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label for="consumo_subtotal">Subtotal:</label>
                        <input type="number" id="consumo_subtotal" 
                               value="<?= $consumo['consumo_subtotal'] ?? '' ?>" 
                               readonly class="form-control subtotal-display">
                    </div>
                </div>
            </div>

            <!-- Sección de Detalles -->
            <div class="form-section">
                <h3>Detalles Adicionales</h3>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="consumo_fecha" class="required">Fecha y Hora:</label>
                        <input type="datetime-local" name="consumo_fecha" id="consumo_fecha" 
                               value="<?= isset($consumo) ? date('Y-m-d\TH:i', strtotime($consumo['consumo_fecha'])) : date('Y-m-d\TH:i') ?>" 
                               required class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="consumo_observaciones">Observaciones:</label>
                    <textarea name="consumo_observaciones" id="consumo_observaciones" 
                              rows="4" class="form-control" 
                              placeholder="Observaciones adicionales sobre el consumo..."><?= $consumo['consumo_observaciones'] ?? '' ?></textarea>
                    <small class="form-text text-muted">
                        <span id="obs_counter">0</span>/500 caracteres
                    </small>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                <?= isset($consumo) ? 'Actualizar Consumo' : 'Registrar Consumo' ?>
            </button>
            <a href="/consumos" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Cancelar
            </a>
        </div>
    </form>
</div>

<?php $this->endSection(); ?>

<?php $this->endSection(); ?>
    display: flex;
    flex-direction: column;
    gap: 25px;
}

<?php $this->endSection(); ?>