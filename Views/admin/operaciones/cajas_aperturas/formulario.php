<div class="container-fluid">
    <!-- Encabezado -->


    <form id="formAperturaCaja" method="POST" action="<?= url('/aperturas/apertura') ?>" novalidate>
        <div class="row">
            <!-- Formulario principal -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-unlock-alt me-2"></i>Conteo de Efectivo Inicial</h5>
                    </div>
                    <div class="card-body">
                        <!-- Encabezados de columnas -->
                        <div class="row mb-3">
                            <div class="col-md-6 border-end">
                                <div class="row text-center fw-bold text-muted small text-uppercase">
                                    <div class="col-4">Denominación</div>
                                    <div class="col-4">Cantidad</div>
                                    <div class="col-4">Subtotal</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row text-center fw-bold text-muted small text-uppercase">
                                    <div class="col-4">Denominación</div>
                                    <div class="col-4">Cantidad</div>
                                    <div class="col-4">Subtotal</div>
                                </div>
                            </div>
                        </div>

                        <!-- Denominaciones en dos columnas -->
                        <div class="row">
                            <?php 
                            // Primera columna: hasta $200 (5 denominaciones)
                            // Segunda columna: desde $500 en adelante (5 denominaciones)
                            foreach ($denominaciones as $index => $denom): 
                                // Abrir columna izquierda (primeras 5 denominaciones: 10, 20, 50, 100, 200)
                                if ($index === 0):
                                    echo '<div class="col-md-6 border-end">';
                                endif;
                                
                                // Abrir columna derecha (últimas 5 denominaciones: 500, 1000, 2000, 10000, 20000)
                                if ($index === 5):
                                    echo '</div><div class="col-md-6">';
                                endif;
                            ?>
                            <div class="row g-2 mb-3 align-items-center denom-row py-2">
                                <div class="col-4 text-center">
                                    <span class="fs-5 fw-bold text-primary">$<?= number_format($denom['valor'], 0, ',', '.') ?></span>
                                </div>
                                <div class="col-4">
                                    <input type="number" 
                                           name="denom_<?= $denom['valor'] ?>" 
                                           id="denom_<?= $denom['valor'] ?>"
                                           class="form-control text-center cantidad-input" 
                                           value="0" 
                                           min="0" 
                                           max="9999"
                                           data-valor="<?= $denom['valor'] ?>"
                                           style="font-size: 1.1rem; font-weight: 600; border-radius: 8px;">
                                </div>
                                <div class="col-4 text-center">
                                    <span class="fs-6 fw-bold text-success subtotal-display" id="subtotal_<?= $denom['valor'] ?>">$0,00</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Total y botones -->
                        <div class="row mt-4 pt-3 border-top">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calculator text-primary me-2 fs-5"></i>
                                    <span class="fw-bold me-3">TOTAL INICIAL:</span>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="fs-4 fw-bold text-primary" id="total-display">$0,00</div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="submit" class="btn btn-success" id="btnAbrirCaja">
                                        <i class="fas fa-unlock-alt me-2"></i> Abrir Caja
                                    </button>
                                    <button type="button" class="btn btn-dark" onclick="limpiarConteo()">
                                        <i class="fas fa-eraser me-2"></i> Limpiar
                                    </button>
                                    <a href="<?= url('/aperturas') ?>" class="btn btn-danger">
                                        <i class="fas fa-times me-2"></i> Cancelar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel lateral de Información -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información</h6>
                    </div>
                    <div class="card-body">
                        <!-- Información de la caja -->
                        <div class="alert alert-primary mb-3">
                            <p class="mb-0 fw-bold"><?= htmlspecialchars($caja['caja_descripcion']) ?></p>
                        </div>

                        <!-- Consejos -->
                        <div class="alert alert-info mb-3">
                            <h6 class="alert-heading">
                                <i class="fas fa-lightbulb me-2"></i>Consejos
                            </h6>
                            <ul class="small mb-0 ps-3">
                                <li class="mb-2">Cuente cuidadosamente todos los billetes antes de abrir la caja</li>
                                <li class="mb-2">Verifique que no haya billetes falsos o deteriorados</li>
                                <li class="mb-2">El total debe coincidir con el efectivo físico en la caja</li>
                                <li class="mb-0">Use el botón "Limpiar" para reiniciar el conteo si es necesario</li>
                            </ul>
                        </div>

                        <!-- Resumen visual -->
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="small text-muted mb-3">RESUMEN DEL CONTEO</h6>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small">Total de billetes:</span>
                                    <strong id="total-billetes">0</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">Denominaciones contadas:</span>
                                    <strong id="denoms-contadas">0 de <?= count($denominaciones) ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- JavaScript para cálculo automático -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cantidadInputs = document.querySelectorAll('.cantidad-input');
    
    // Función para formatear números a moneda argentina
    function formatCurrency(valor) {
        return '$' + parseFloat(valor).toLocaleString('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    // Función para calcular totales
    function calcularTotales() {
        let totalGeneral = 0;
        let totalBilletes = 0;
        let denomsContadas = 0;
        
        cantidadInputs.forEach(function(input) {
            const valor = parseFloat(input.dataset.valor);
            const cantidad = parseInt(input.value) || 0;
            const subtotal = valor * cantidad;
            
            // Actualizar subtotal individual
            const subtotalDisplay = document.getElementById('subtotal_' + valor);
            if (subtotalDisplay) {
                subtotalDisplay.textContent = formatCurrency(subtotal);
            }
            
            // Acumular totales
            totalGeneral += subtotal;
            totalBilletes += cantidad;
            if (cantidad > 0) {
                denomsContadas++;
            }
        });
        
        // Actualizar displays
        document.getElementById('total-display').textContent = formatCurrency(totalGeneral);
        document.getElementById('total-billetes').textContent = totalBilletes;
        document.getElementById('denoms-contadas').textContent = denomsContadas + ' de <?= count($denominaciones) ?>';
        
        // Habilitar/deshabilitar botón de abrir caja
        const btnAbrirCaja = document.getElementById('btnAbrirCaja');
        btnAbrirCaja.disabled = totalGeneral <= 0;
    }
    
    // Event listeners para todos los inputs
    cantidadInputs.forEach(function(input) {
        input.addEventListener('input', calcularTotales);
        input.addEventListener('change', calcularTotales);
    });
    
    // Calcular totales iniciales
    calcularTotales();
});

// Función para limpiar todos los campos
function limpiarConteo() {
    if (confirm('¿Está seguro de que desea limpiar todos los campos?')) {
        document.querySelectorAll('.cantidad-input').forEach(function(input) {
            input.value = 0;
        });
        
        // Recalcular totales
        document.querySelectorAll('.cantidad-input')[0].dispatchEvent(new Event('input'));
    }
}

// Validación del formulario antes de enviar
document.getElementById('formAperturaCaja').addEventListener('submit', function(e) {
    const totalDisplay = document.getElementById('total-display').textContent;
    const totalGeneral = parseFloat(totalDisplay.replace(/[^0-9,-]/g, '').replace(',', '.')) || 0;
    
    if (totalGeneral <= 0) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Debe contar al menos un billete para abrir la caja',
            confirmButtonText: 'Entendido'
        });
        return false;
    }
    
    // Confirmación final
    e.preventDefault();
    Swal.fire({
        icon: 'question',
        title: '¿Confirmar apertura de caja?',
        html: `
            <p>Se abrirá la caja con un monto inicial de:</p>
            <h3 class="text-success"><strong>${totalDisplay}</strong></h3>
            <p class="small text-muted">Verifique que el monto sea correcto antes de continuar.</p>
        `,
        showCancelButton: true,
        confirmButtonText: 'Sí, abrir caja',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            this.submit();
        }
    });
});
</script>

<style>
.cantidad-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.denom-row {
    transition: background-color 0.2s ease;
}

.denom-row:hover {
    background-color: #f8f9fa;
    border-radius: 4px;
}

.cantidad-input {
    border: 1px solid #dee2e6;
}

.border-end {
    border-right: 2px solid #e9ecef !important;
}

.sticky-top {
    z-index: 1020;
}
</style>
