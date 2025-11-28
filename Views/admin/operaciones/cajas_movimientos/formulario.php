<div class="container-fluid">

    <div class="row">
        <!-- Formulario Principal -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header text-dark py-3">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i> Registrar Movimiento de Caja</h5>
                </div>
                <div class="card-body">
                    <form id="formMovimiento" method="POST" action="<?= url('/movimientos') ?>" novalidate>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="cajamovimiento_descripcion" class="form-label">
                                    Descripción <span class="text-danger">*</span>
                                </label>
                                <textarea name="cajamovimiento_descripcion" 
                                          id="cajamovimiento_descripcion" 
                                          class="form-control" 
                                          rows="3" 
                                          required 
                                          placeholder="Describa el motivo del movimiento..."></textarea>
                                <div class="invalid-feedback">
                                    Por favor ingrese una descripción
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Tipo de Movimiento <span class="text-danger">*</span>
                                </label>
                                <div class="card">
                                    <div class="card-body p-3">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" 
                                                   type="radio" 
                                                   name="cajamovimiento_tipo" 
                                                   id="tipoIngreso" 
                                                   value="I" 
                                                   checked 
                                                   required>
                                            <label class="form-check-label d-flex align-items-center" for="tipoIngreso">
                                                <i class="fas fa-arrow-down text-success fa-2x me-3"></i>
                                                <div>
                                                    <strong class="text-success">Ingreso</strong>
                                                    <div class="small text-muted">Entrada de dinero a la caja</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="radio" 
                                                   name="cajamovimiento_tipo" 
                                                   id="tipoEgreso" 
                                                   value="E" 
                                                   required>
                                            <label class="form-check-label d-flex align-items-center" for="tipoEgreso">
                                                <i class="fas fa-arrow-up text-danger fa-2x me-3"></i>
                                                <div>
                                                    <strong class="text-danger">Egreso</strong>
                                                    <div class="small text-muted">Salida de dinero de la caja</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="cajamovimiento_monto" class="form-label">
                                    Monto <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           name="cajamovimiento_monto" 
                                           id="cajamovimiento_monto" 
                                           class="form-control" 
                                           step="0.01" 
                                           min="0.01" 
                                           required 
                                           placeholder="0.00">
                                </div>
                                <div class="invalid-feedback">
                                    Por favor ingrese un monto válido
                                </div>
                                <div class="small text-muted mt-1">
                                    Ingrese el monto sin puntos ni comas
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Guardar Movimiento
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="limpiarFormulario()">
                                <i class="fas fa-eraser me-2"></i> Limpiar
                            </button>
                            <a href="<?= url('/movimientos') ?>" class="btn btn-outline-danger">
                                <i class="fas fa-times me-2"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header text-dark py-3">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información</h6>
                </div>
                <div class="card-body">
                    <!-- Información del Turno -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-clock me-2"></i>Turno Activo</h6>
                        <hr>
                        <div class="small">
                            <div class="mb-2">
                                <strong>Caja:</strong> <?= htmlspecialchars($turnoActual['caja_descripcion']) ?>
                            </div>
                            <div class="mb-2">
                                <strong>Apertura:</strong> <?= date('d/m/Y H:i', strtotime($turnoActual['cajaturno_fhapertura'])) ?>
                            </div>
                            <div class="mb-2">
                                <strong>Usuario:</strong> <?= htmlspecialchars($turnoActual['usuario_nombre']) ?>
                            </div>
                            <div>
                                <strong>Monto Inicial:</strong> 
                                <span class="text-success fw-bold">$<?= number_format($turnoActual['cajaturno_contadoinicial'], 2, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Consejos -->
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-lightbulb me-2"></i>Consejos</h6>
                        <hr>
                        <ul class="small mb-0 ps-3">
                            <li class="mb-2">Sea específico en la descripción del movimiento</li>
                            <li class="mb-2">Verifique el tipo de movimiento (Ingreso/Egreso)</li>
                            <li class="mb-2">Verifique el monto antes de guardar</li>
                            <li class="mb-0">Los movimientos quedan registrados en el turno actual</li>
                        </ul>
                    </div>

                    <!-- Ejemplos -->
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="small fw-bold mb-2"><i class="fas fa-list me-1"></i> Ejemplos de Movimientos</h6>
                            <div class="small">
                                <div class="mb-2">
                                    <span class="badge text-white bg-success me-1">Ingreso</span>
                                    <span class="text-muted">Cobro de servicios extras</span>
                                </div>
                                <div class="mb-2">
                                    <span class="badge text-white bg-danger me-1">Egreso</span>
                                    <span class="text-muted">Compra de insumos</span>
                                </div>
                                <div>
                                    <span class="badge text-white bg-danger me-1">Egreso</span>
                                    <span class="text-muted">Pago a proveedor</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación del formulario
(function() {
    'use strict';
    
    const form = document.getElementById('formMovimiento');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
})();

// Limpiar formulario
function limpiarFormulario() {
    document.getElementById('formMovimiento').reset();
    document.getElementById('formMovimiento').classList.remove('was-validated');
    document.getElementById('tipoIngreso').checked = true;
}

// Formatear monto en tiempo real
document.getElementById('cajamovimiento_monto').addEventListener('blur', function() {
    if (this.value) {
        this.value = parseFloat(this.value).toFixed(2);
    }
});

// Cambiar color del input según tipo
document.querySelectorAll('input[name="cajamovimiento_tipo"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        const montoInput = document.getElementById('cajamovimiento_monto');
        if (this.value === 'I') {
            montoInput.classList.remove('border-danger');
            montoInput.classList.add('border-success');
        } else {
            montoInput.classList.remove('border-success');
            montoInput.classList.add('border-danger');
        }
    });
});
</script>
