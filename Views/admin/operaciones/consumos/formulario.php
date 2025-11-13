<?php
/**
 * Vista: Formulario de Consumo (Unificado)
 * Descripción: Formulario para crear/editar consumos - individual en edición, múltiple en creación
 */

$isEdit = isset($consumo) && !empty($consumo);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/consumos') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Formulario principal -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-<?= $isEdit ? 'edit' : 'shopping-cart' ?>"></i>
                        <?= $isEdit ? 'Modificar datos del consumo' : 'Registrar nuevos consumos' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formConsumo" method="POST"
                          action="<?= $isEdit ? url('/consumos/' . $consumo['id_consumo'] . '/edit') : url('/consumos/create') ?>"
                          novalidate>

                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_consumo" value="<?= $consumo['id_consumo'] ?>">
                            <input type="hidden" name="rela_reserva" value="<?= $consumo['rela_reserva'] ?>">
                        <?php endif; ?>

                        <!-- Información de la Reserva -->
                        <div class="row">

                            <?php if ($isEdit): ?>
                                <!-- MODO EDICIÓN: Mostrar datos de la reserva -->
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label><i class="fas fa-bookmark"></i> Reserva</label>
                                        <input type="text" class="form-control form-control-sm bg-light" 
                                               value="#<?= $consumo['rela_reserva'] ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label><i class="fas fa-user"></i> Huésped</label>
                                        <input type="text" class="form-control form-control-sm bg-light" 
                                               value="<?= htmlspecialchars(($consumo['huesped_nombre'] ?? '') . ' ' . ($consumo['huesped_apellido'] ?? '')) ?>" readonly>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label><i class="fas fa-home"></i> Cabaña</label>
                                        <input type="text" class="form-control form-control-sm bg-light" 
                                               value="<?= htmlspecialchars($consumo['cabania_nombre'] ?? 'N/A') ?>" readonly>
                                    </div>
                                </div>

                            <?php else: ?>
                                <!-- MODO CREACIÓN: Selector de reserva en curso -->
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="rela_reserva" class="required">
                                            <i class="fas fa-calendar-check"></i> Reserva en Curso
                                        </label>
                                        <select class="form-select form-select-sm" id="rela_reserva" name="rela_reserva" required>
                                            <option value="">Seleccione una reserva en curso...</option>
                                            <?php if (isset($reservas)): ?>
                                                <?php foreach ($reservas as $reserva): ?>
                                                    <option value="<?= $reserva['id_reserva'] ?>"
                                                            data-huesped="<?= htmlspecialchars($reserva['persona_nombre'] . ' ' . $reserva['persona_apellido']) ?>"
                                                            data-cabania="<?= htmlspecialchars($reserva['cabania_nombre']) ?>">
                                                        #<?= $reserva['id_reserva'] ?> - <?= htmlspecialchars($reserva['persona_nombre'] . ' ' . $reserva['persona_apellido']) ?> - 
                                                        <?= htmlspecialchars($reserva['cabania_nombre']) ?> (<?= date('d/m/Y', strtotime($reserva['reserva_fhinicio'])) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <small class="form-text text-muted">Solo se muestran reservas activas en curso</small>
                                    </div>
                                </div>

                                <div class="col-12" id="reservaInfo" style="display: none;">
                                    <div class="alert alert-info">
                                        <strong>Detalles:</strong>
                                        <i class="fas fa-user"></i> <span id="infoHuesped">-</span> |
                                        <i class="fas fa-home"></i> <span id="infoCabania">-</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <hr class="my-4">

                        <?php if ($isEdit): ?>
                            <!-- MODO EDICIÓN: Formulario individual simple -->
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="rela_producto"><i class="fas fa-box"></i> Producto</label>
                                        <select class="form-select form-select-sm" id="rela_producto" name="rela_producto">
                                            <option value="">Ninguno</option>
                                            <?php if (isset($productos)): ?>
                                                <?php foreach ($productos as $producto): ?>
                                                    <option value="<?= $producto['id_producto'] ?>"
                                                            data-precio="<?= $producto['producto_precio'] ?>"
                                                            data-nombre="<?= htmlspecialchars($producto['producto_nombre']) ?>"
                                                            <?= ($consumo['rela_producto'] == $producto['id_producto']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($producto['producto_nombre']) ?> - $<?= number_format($producto['producto_precio'], 2) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="rela_servicio"><i class="fas fa-concierge-bell"></i> Servicio</label>
                                        <select class="form-select form-select-sm" id="rela_servicio" name="rela_servicio">
                                            <option value="">Ninguno</option>
                                            <?php if (isset($servicios)): ?>
                                                <?php foreach ($servicios as $servicio): ?>
                                                    <option value="<?= $servicio['id_servicio'] ?>"
                                                            data-precio="<?= $servicio['servicio_precio'] ?>"
                                                            data-nombre="<?= htmlspecialchars($servicio['servicio_nombre']) ?>"
                                                            <?= ($consumo['rela_servicio'] == $servicio['id_servicio']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($servicio['servicio_nombre']) ?> - $<?= number_format($servicio['servicio_precio'], 2) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <small class="text-muted"><i class="fas fa-info-circle"></i> Seleccione producto O servicio</small>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="consumo_cantidad" class="required"><i class="fas fa-sort-numeric-up"></i> Cantidad</label>
                                        <input type="number" class="form-control form-control-sm" id="consumo_cantidad" name="consumo_cantidad"
                                               value="<?= $consumo['consumo_cantidad'] ?>" required min="1" step="1">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label><i class="fas fa-tag"></i> Precio Unitario</label>
                                        <input type="text" class="form-control form-control-sm bg-light" id="precio_unitario" readonly
                                               value="$<?= number_format($consumo['consumo_total'] / $consumo['consumo_cantidad'], 2) ?>">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label><i class="fas fa-dollar-sign"></i> Total</label>
                                        <input type="text" class="form-control form-control-sm bg-light fw-bold text-success" id="consumo_total_display" readonly
                                               value="$<?= number_format($consumo['consumo_total'], 2) ?>">
                                        <input type="hidden" name="consumo_total" id="consumo_total" value="<?= $consumo['consumo_total'] ?>">
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label for="consumo_descripcion" class="required"><i class="fas fa-file-alt"></i> Descripción</label>
                                        <textarea class="form-control form-control-sm bg-light" id="consumo_descripcion" name="consumo_descripcion"
                                                  rows="2" required readonly><?= htmlspecialchars($consumo['consumo_descripcion']) ?></textarea>
                                        <small class="text-muted">Se genera automáticamente</small>
                                    </div>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- MODO CREACIÓN: Tabla múltiple de items -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0">
                                        <i class="fas fa-box text-success"></i> Items a Consumir
                                    </label>
                                    <button type="button" class="btn btn-success btn-sm btnAgregarItem">
                                        <i class="fas fa-plus"></i> Agregar Item
                                    </button>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered" id="tablaConsumos">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="10%">Tipo</th>
                                                <th width="35%">Producto/Servicio</th>
                                                <th width="15%">Precio Unit.</th>
                                                <th width="15%">Cantidad</th>
                                                <th width="15%">Subtotal</th>
                                                <th width="10%" class="text-center">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemsConsumo">
                                            <!-- Se agregan dinámicamente con JavaScript -->
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold">TOTAL:</td>
                                                <td class="fw-bold text-success" id="totalGeneral">$0.00</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <br>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-success btn-sm btnAgregarItem">
                                        <i class="fas fa-plus"></i> Agregar Item
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= url('/consumos') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?= $isEdit ? 'Actualizar Consumo' : 'Registrar Consumos' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel lateral de información -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($isEdit): ?>
                        <!-- Consejos para edición -->
                        <div class="mb-3">
                            <ul class="mb-0 ps-3 small">
                                <li>Elige producto o servicio (no ambos)</li>
                                <li>La cantidad debe ser mayor a cero</li>
                                <li>El total se calcula automáticamente</li>
                                <li>La descripción se genera automáticamente</li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Consejos para creación -->
                        <div class="mb-3">
                            <ul class="mb-0 ps-3 small">
                                <li>Selecciona la reserva activa en curso</li>
                                <li>Elige el tipo (Producto o Servicio)</li>
                                <li>Selecciona el item específico</li>
                                <li>Define la cantidad deseada</li>
                                <li>Puedes agregar múltiples items</li>
                                <li>Los totales se calculan automáticamente</li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!$isEdit): ?>
<!-- Template para fila de consumo (solo en creación) -->
<template id="templateItemConsumo">
    <tr class="item-consumo">
        <td>
            <select class="form-select form-select-sm select-tipo" required>
                <option value="">--</option>
                <option value="producto">Producto</option>
                <option value="servicio">Servicio</option>
            </select>
        </td>
        <td>
            <select name="items[]" class="form-select form-select-sm select-item" required disabled>
                <option value="">Seleccione tipo primero...</option>
            </select>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm precio-unitario bg-light" readonly>
        </td>
        <td>
            <input type="number" name="cantidades[]" class="form-control form-control-sm cantidad" 
                   value="1" min="1" step="1" required>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm subtotal bg-light" readonly>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm btn-eliminar-item" title="Eliminar">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<!-- Datos PHP para JavaScript -->
<script id="productosData" type="application/json">
<?= json_encode($productos ?? []) ?>
</script>
<script id="serviciosData" type="application/json">
<?= json_encode($servicios ?? []) ?>
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const formConsumo = document.getElementById('formConsumo');
    const isEdit = <?= $isEdit ? 'true' : 'false' ?>;

    if (isEdit) {
        // ==================== MODO EDICIÓN ====================
        const productoSelect = document.getElementById('rela_producto');
        const servicioSelect = document.getElementById('rela_servicio');
        const cantidadInput = document.getElementById('consumo_cantidad');
        const precioUnitarioInput = document.getElementById('precio_unitario');
        const totalDisplayInput = document.getElementById('consumo_total_display');
        const totalHiddenInput = document.getElementById('consumo_total');
        const descripcionInput = document.getElementById('consumo_descripcion');

        function calcularTotal() {
            let precioUnitario = 0;
            let nombreItem = '';
            let tipoItem = '';

            if (productoSelect.value) {
                const selected = productoSelect.options[productoSelect.selectedIndex];
                precioUnitario = parseFloat(selected.dataset.precio) || 0;
                nombreItem = selected.dataset.nombre || '';
                tipoItem = 'Producto';
            } else if (servicioSelect.value) {
                const selected = servicioSelect.options[servicioSelect.selectedIndex];
                precioUnitario = parseFloat(selected.dataset.precio) || 0;
                nombreItem = selected.dataset.nombre || '';
                tipoItem = 'Servicio';
            }

            const cantidad = parseFloat(cantidadInput.value) || 1;
            const total = precioUnitario * cantidad;

            precioUnitarioInput.value = '$' + precioUnitario.toFixed(2);
            totalDisplayInput.value = '$' + total.toFixed(2);
            totalHiddenInput.value = total.toFixed(2);

            if (nombreItem) {
                descripcionInput.value = `${tipoItem}: ${nombreItem}`;
            }
        }

        function limpiarServicio() {
            if (productoSelect.value) servicioSelect.value = '';
            calcularTotal();
        }

        function limpiarProducto() {
            if (servicioSelect.value) productoSelect.value = '';
            calcularTotal();
        }

        productoSelect.addEventListener('change', limpiarServicio);
        servicioSelect.addEventListener('change', limpiarProducto);
        cantidadInput.addEventListener('input', calcularTotal);

        calcularTotal();

        formConsumo.addEventListener('submit', function(e) {
            if (!productoSelect.value && !servicioSelect.value) {
                e.preventDefault();
                alert('Debe seleccionar un producto o un servicio');
            }
        });

    } else {
        // ==================== MODO CREACIÓN MÚLTIPLE ====================
        const reservaSelect = document.getElementById('rela_reserva');
        const reservaInfo = document.getElementById('reservaInfo');
        const itemsConsumo = document.getElementById('itemsConsumo');
        const botonesAgregarItem = document.querySelectorAll('.btnAgregarItem');
        const template = document.getElementById('templateItemConsumo');
        const totalGeneral = document.getElementById('totalGeneral');

        // Mostrar info de reserva
        reservaSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (selected.value) {
                document.getElementById('infoHuesped').textContent = selected.dataset.huesped || '-';
                document.getElementById('infoCabania').textContent = selected.dataset.cabania || '-';
                reservaInfo.style.display = 'block';
            } else {
                reservaInfo.style.display = 'none';
            }
        });

        // Agregar primera fila
        agregarItem();

        // Agregar evento a todos los botones de agregar item
        botonesAgregarItem.forEach(btn => {
            btn.addEventListener('click', agregarItem);
        });

        function agregarItem() {
            const clone = template.content.cloneNode(true);
            const row = clone.querySelector('.item-consumo');
            
            const selectTipo = row.querySelector('.select-tipo');
            const selectItem = row.querySelector('.select-item');
            const inputCantidad = row.querySelector('.cantidad');
            const btnEliminar = row.querySelector('.btn-eliminar-item');
            
            // Obtener datos de productos y servicios
            const productosData = JSON.parse(document.getElementById('productosData').textContent);
            const serviciosData = JSON.parse(document.getElementById('serviciosData').textContent);
            
            // Evento cambio de tipo (Producto/Servicio)
            selectTipo.addEventListener('change', function() {
                const tipo = this.value;
                selectItem.innerHTML = '<option value="">-- Seleccionar --</option>';
                selectItem.disabled = false;
                
                if (tipo === 'producto') {
                    productosData.forEach(producto => {
                        const option = document.createElement('option');
                        option.value = 'p_' + producto.id_producto;
                        option.dataset.precio = producto.producto_precio;
                        option.dataset.nombre = producto.producto_nombre;
                        option.textContent = producto.producto_nombre + ' - $' + parseFloat(producto.producto_precio).toFixed(2);
                        selectItem.appendChild(option);
                    });
                } else if (tipo === 'servicio') {
                    serviciosData.forEach(servicio => {
                        const option = document.createElement('option');
                        option.value = 's_' + servicio.id_servicio;
                        option.dataset.precio = servicio.servicio_precio;
                        option.dataset.nombre = servicio.servicio_nombre;
                        option.textContent = servicio.servicio_nombre + ' - $' + parseFloat(servicio.servicio_precio).toFixed(2);
                        selectItem.appendChild(option);
                    });
                } else {
                    selectItem.innerHTML = '<option value="">Seleccione tipo primero...</option>';
                    selectItem.disabled = true;
                }
                
                // Limpiar precio y subtotal
                row.querySelector('.precio-unitario').value = '';
                row.querySelector('.subtotal').value = '';
                calcularTotalGeneral();
            });
            
            // Evento cambio de item
            selectItem.addEventListener('change', function() {
                const option = this.options[this.selectedIndex];
                const precio = parseFloat(option.dataset.precio || 0);
                row.querySelector('.precio-unitario').value = '$' + precio.toFixed(2);
                calcularSubtotal(row);
            });
            
            // Evento cambio de cantidad
            inputCantidad.addEventListener('input', function() {
                calcularSubtotal(row);
            });
            
            // Evento eliminar fila
            btnEliminar.addEventListener('click', function() {
                if (itemsConsumo.children.length > 1) {
                    row.remove();
                    calcularTotalGeneral();
                } else {
                    alert('Debe mantener al menos un item');
                }
            });
            
            itemsConsumo.appendChild(row);
        }

        function calcularSubtotal(row) {
            const precioText = row.querySelector('.precio-unitario').value.replace('$', '');
            const precio = parseFloat(precioText || 0);
            const cantidad = parseFloat(row.querySelector('.cantidad').value || 0);
            const subtotal = precio * cantidad;
            row.querySelector('.subtotal').value = '$' + subtotal.toFixed(2);
            calcularTotalGeneral();
        }

        function calcularTotalGeneral() {
            let total = 0;
            document.querySelectorAll('.subtotal').forEach(input => {
                const valorText = input.value.replace('$', '');
                total += parseFloat(valorText || 0);
            });
            totalGeneral.textContent = '$' + total.toFixed(2);
        }

        formConsumo.addEventListener('submit', function(e) {
            if (!reservaSelect.value) {
                e.preventDefault();
                alert('Debe seleccionar una reserva');
                return;
            }

            const items = document.querySelectorAll('.select-item');
            let hayItems = false;
            items.forEach(select => {
                if (select.value) hayItems = true;
            });

            if (!hayItems) {
                e.preventDefault();
                alert('Debe agregar al menos un producto o servicio');
            }
        });
    }
});
</script>

<style>
.item-consumo:hover {
    background-color: #f8f9fa;
}
</style>