<?php
/**
 * Vista de Pago - Paso 4
 * Simulación de pasarela de pago con validación de métodos
 */
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Finalizar Pago
                    </h4>
                    <small class="text-light">Paso 4 de 4 - Procesamiento de pago seguro</small>
                </div>
                
                <div class="card-body p-4">
                    <!-- Progress Bar -->
                    <div class="progress mb-4" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                    </div>

                    <div class="row g-4">
                        <!-- Columna Izquierda: Método de Pago -->
                        <div class="col-lg-8">
                            <form method="POST" action="<?= $this->url('/reservas/confirmar-pago') ?>" id="formPago">
                                <input type="hidden" name="reserva_temp_id" value="<?= htmlspecialchars($reserva['temp_id']) ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                
                                <!-- Selección de Método de Pago -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0 text-primary">
                                            <i class="fas fa-wallet me-2"></i>
                                            Método de Pago
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <?php foreach ($metodos_pago as $metodo): ?>
                                                <?php
                                                // Compatibilidad con diferentes estructuras de datos
                                                $nombre = $metodo['metodopago_nombre'] ?? $metodo['nombre'] ?? 'Sin nombre';
                                                $descripcion = $metodo['metodopago_descripcion'] ?? $metodo['descripcion'] ?? '';
                                                
                                                // Asignar iconos según el nombre
                                                $icono = 'fas fa-money-bill-alt'; // Por defecto
                                                if (stripos($nombre, 'tarjeta') !== false || stripos($nombre, 'credito') !== false) {
                                                    $icono = 'fas fa-credit-card';
                                                } elseif (stripos($nombre, 'transferencia') !== false || stripos($nombre, 'banco') !== false) {
                                                    $icono = 'fas fa-university';
                                                } elseif (stripos($nombre, 'efectivo') !== false) {
                                                    $icono = 'fas fa-money-bill-alt';
                                                }
                                                ?>
                                                <div class="col-md-6">
                                                    <div class="form-check metodo-pago-card p-3 border rounded">
                                                        <input class="form-check-input" type="radio" 
                                                               name="metodo_pago" 
                                                               value="<?= $metodo['id'] ?>"
                                                               id="metodo_<?= $metodo['id'] ?>"
                                                               data-nombre="<?= htmlspecialchars($nombre) ?>"
                                                               required>
                                                        <label class="form-check-label w-100" for="metodo_<?= $metodo['id'] ?>">
                                                            <div class="d-flex align-items-center">
                                                                <i class="<?= $icono ?> fa-2x text-primary me-3"></i>
                                                                <div>
                                                                    <strong><?= htmlspecialchars($nombre) ?></strong>
                                                                    <?php if ($descripcion): ?>
                                                                    <br><small class="text-muted"><?= htmlspecialchars($descripcion) ?></small>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Campos Específicos por Método de Pago -->
                                
                                <!-- Tarjeta de Crédito -->
                                <div class="card mb-4 metodo-fields" id="fields_tarjeta_credito" style="display: none;">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0">
                                            <i class="fas fa-credit-card me-2"></i>
                                            Datos de la Tarjeta de Crédito
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Nota:</strong> Esta es una simulación. No se procesarán pagos reales.
                                        </div>
                                        
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Número de Tarjeta</label>
                                                <input type="text" name="numero_tarjeta" class="form-control" 
                                                       placeholder="1234 5678 9012 3456" 
                                                       pattern="[0-9\s]{13,19}" 
                                                       maxlength="19"
                                                       id="numeroTarjeta">
                                                <small class="text-muted">Ingrese cualquier número para la simulación</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Titular de la Tarjeta</label>
                                                <input type="text" name="titular_tarjeta" class="form-control" 
                                                       placeholder="Nombre como aparece en la tarjeta"
                                                       value="<?= htmlspecialchars($reserva['huesped_nombre']) ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Vencimiento</label>
                                                <input type="text" name="vencimiento" class="form-control" 
                                                       placeholder="MM/AA" pattern="[0-9]{2}/[0-9]{2}" 
                                                       maxlength="5" id="vencimiento">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">CVV</label>
                                                <input type="text" name="cvv" class="form-control" 
                                                       placeholder="123" pattern="[0-9]{3,4}" 
                                                       maxlength="4">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transferencia Bancaria -->
                                <div class="card mb-4 metodo-fields" id="fields_transferencia" style="display: none;">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-university me-2"></i>
                                            Transferencia Bancaria
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <strong>Datos para la transferencia:</strong>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <strong>Banco:</strong> Banco Nacional<br>
                                                <strong>Cuenta:</strong> 1234-5678-90<br>
                                                <strong>CUIT:</strong> 20-12345678-9
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Titular:</strong> Casa de Palos Cabañas<br>
                                                <strong>CBU:</strong> 0123456789012345678901<br>
                                                <strong>Alias:</strong> CABANIAS.PALOS
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Número de Comprobante de Transferencia</label>
                                                <input type="text" name="comprobante_transferencia" class="form-control" 
                                                       placeholder="Ingrese el número del comprobante">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Efectivo -->
                                <div class="card mb-4 metodo-fields" id="fields_efectivo" style="display: none;">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-money-bills me-2"></i>
                                            Pago en Efectivo
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-secondary">
                                            <i class="fas fa-info-circle me-2"></i>
                                            El pago en efectivo se realizará al momento del check-in en nuestras instalaciones.
                                            La reserva quedará confirmada pero pendiente de pago hasta su llegada.
                                        </div>
                                    </div>
                                </div>

                                <!-- Botones de Acción -->
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <a href="/reservas/resumen" class="btn btn-outline-secondary w-100">
                                            <i class="fas fa-arrow-left me-1"></i>
                                            Volver al Resumen
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-success w-100" id="btnConfirmarPago">
                                            <i class="fas fa-check me-1"></i>
                                            Confirmar Pago
                                        </button>
                                    </div>
                                </div>

                                <!-- Botón Cancelar -->
                                <div class="row mt-2">
                                    <div class="col-12 text-center">
                                        <form method="POST" action="/" class="d-inline">
                                            <button type="submit" class="btn btn-link text-danger">
                                                <i class="fas fa-times me-1"></i>
                                                Cancelar y volver al inicio
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Columna Derecha: Resumen del Pago -->
                        <div class="col-lg-4">
                            <div class="card bg-success text-white sticky-top">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-receipt me-2"></i>
                                        Total a Pagar
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <h2 class="fw-bold">$<?= number_format($reserva['total'], 0, ',', '.') ?></h2>
                                        <small class="opacity-75">Pesos Argentinos</small>
                                    </div>

                                    <hr class="bg-light">

                                    <!-- Detalles -->
                                    <div class="small">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Alojamiento (<?= $reserva['noches'] ?> noche<?= $reserva['noches'] > 1 ? 's' : '' ?>)</span>
                                            <span>$<?= number_format($reserva['subtotal_alojamiento'], 0, ',', '.') ?></span>
                                        </div>
                                        <?php if ($reserva['total_servicios'] > 0): ?>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Servicios Adicionales</span>
                                                <span>$<?= number_format($reserva['total_servicios'], 0, ',', '.') ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($reserva['impuestos'] > 0): ?>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Impuestos</span>
                                                <span>$<?= number_format($reserva['impuestos'], 0, ',', '.') ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <hr class="bg-light">

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estilos CSS -->
<style>
.metodo-pago-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.metodo-pago-card:hover {
    background-color: rgba(13, 110, 253, 0.05);
    border-color: #0d6efd !important;
}

.metodo-pago-card input[type="radio"]:checked + label {
    color: #0d6efd;
}

.metodo-pago-card:has(input[type="radio"]:checked) {
    background-color: rgba(13, 110, 253, 0.1);
    border-color: #0d6efd !important;
}
</style>

<!-- Scripts JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const metodosRadio = document.querySelectorAll('input[name="metodo_pago"]');
    const camposMetodos = document.querySelectorAll('.metodo-fields');
    const numeroTarjeta = document.getElementById('numeroTarjeta');
    const vencimiento = document.getElementById('vencimiento');
    const formPago = document.getElementById('formPago');

    // Manejar selección de método de pago
    metodosRadio.forEach(radio => {
        radio.addEventListener('change', function() {
            // Ocultar todos los campos específicos
            camposMetodos.forEach(campo => {
                campo.style.display = 'none';
            });

            // Mostrar campos del método seleccionado
            const metodoNombre = this.dataset.nombre.toLowerCase().replace(/\s+/g, '_');
            const campoMetodo = document.getElementById('fields_' + metodoNombre);
            if (campoMetodo) {
                campoMetodo.style.display = 'block';
            }
        });
    });

    // Formatear número de tarjeta
    if (numeroTarjeta) {
        numeroTarjeta.addEventListener('input', function() {
            let valor = this.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let valorFormateado = valor.match(/.{1,4}/g)?.join(' ');
            this.value = valorFormateado || valor;
        });
    }

    // Formatear vencimiento
    if (vencimiento) {
        vencimiento.addEventListener('input', function() {
            let valor = this.value.replace(/\D/g, '');
            if (valor.length >= 2) {
                this.value = valor.substring(0, 2) + '/' + valor.substring(2, 4);
            } else {
                this.value = valor;
            }
        });
    }

    // Manejar envío del formulario
    formPago.addEventListener('submit', function(e) {
        const metodoSeleccionado = document.querySelector('input[name="metodo_pago"]:checked');
        
        if (metodoSeleccionado && metodoSeleccionado.dataset.nombre === 'TARJETA DE CREDITO') {
            // Ejemplo hardcodeado: rechazar si el número de tarjeta contiene "1234"
            const numero = numeroTarjeta.value.replace(/\s/g, '');
            if (numero.includes('1234')) {
                e.preventDefault();
                
                // Mostrar mensaje de rechazo
                Swal.fire({
                    icon: 'error',
                    title: 'Pago Rechazado',
                    text: 'Su tarjeta de crédito ha sido rechazada. Por favor, utilice otro método de pago.',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#dc3545'
                });
                
                return false;
            }
        }

        // Confirmar el pago
        e.preventDefault();
        
        Swal.fire({
            title: '¿Confirmar pago?',
            text: `Se procesará el pago de $<?= number_format($reserva['total'], 0, ',', '.') ?>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, confirmar pago',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: 'Procesando pago...',
                    text: 'Por favor espere mientras procesamos su transacción',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Simular delay de procesamiento
                setTimeout(() => {
                    this.submit();
                }, 2000);
            }
        });
    });
});
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>