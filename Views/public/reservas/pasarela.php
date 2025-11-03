<?php
/**
 * Vista de Pasarela de Pago Simulada
 * Basada en el diseño de MercadoPago
 */
?>

<!-- Estilos específicos para MercadoPago -->
<style>
.mp-container {
    background: linear-gradient(135deg, #009ee3 0%, #0070ba 100%);
    min-height: 100vh;
    padding: 20px 0;
}

.mp-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    border: none;
}

.mp-header {
    background: white;
    border-bottom: 1px solid #e5e5e5;
    padding: 20px 30px;
    border-radius: 12px 12px 0 0;
}

.mp-logo {
    font-size: 24px;
    font-weight: 700;
    color: #009ee3;
}

.mp-body {
    padding: 30px;
}

.mp-payment-method {
    border: 2px solid #e5e5e5;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.mp-payment-method:hover {
    border-color: #009ee3;
    box-shadow: 0 2px 8px rgba(0, 158, 227, 0.2);
}

.mp-payment-method.selected {
    border-color: #009ee3;
    background: #f0f9ff;
}

.mp-input {
    border: 2px solid #e5e5e5;
    border-radius: 6px;
    padding: 12px 16px;
    font-size: 16px;
    transition: border-color 0.3s ease;
}

.mp-input:focus {
    border-color: #009ee3;
    box-shadow: 0 0 0 3px rgba(0, 158, 227, 0.1);
    outline: none;
}

.mp-button {
    background: #009ee3;
    border: none;
    border-radius: 6px;
    color: white;
    font-weight: 600;
    padding: 12px 24px;
    transition: all 0.3s ease;
}

.mp-button:hover {
    background: #0085c3;
    transform: translateY(-1px);
}

.mp-button:disabled {
    background: #cccccc;
    cursor: not-allowed;
    transform: none;
}

.mp-security {
    background: #f8f9fa;
    border-left: 4px solid #28a745;
    padding: 15px;
    border-radius: 4px;
}

.mp-amount {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
}

.method-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    margin-right: 12px;
}

.icon-card { background: linear-gradient(45deg, #667eea 0%, #764ba2 100%); }
.icon-pse { background: linear-gradient(45deg, #28a745 0%, #20c997 100%); }
.icon-nequi { background: linear-gradient(45deg, #009ee3 0%, #0070ba 100%); }
.icon-daviplata { background: linear-gradient(45deg, #fd7e14 0%, #ffc107 100%); }
</style>

<div class="mp-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-6">
                <div class="mp-card">
                    <!-- Header estilo MercadoPago -->
                    <div class="mp-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="mp-logo">mercado</div>
                                <span style="color: #ffb800; font-weight: 700; font-size: 24px;">pago</span>
                            </div>
                            <div class="text-end">
                                <small class="text-muted">Pago seguro</small>
                                <div><i class="fas fa-lock text-success me-1"></i><small class="text-success">SSL</small></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mp-body">

                        <!-- Resumen de compra estilo MercadoPago -->
                        <div class="mb-4">
                            <div class="row align-items-center mb-3">
                                <div class="col">
                                    <h5 class="mb-0">Resumen de tu compra</h5>
                                    <small class="text-muted">Casa de Palos Cabañas</small>
                                </div>
                                <div class="col-auto">
                                    <div class="mp-amount">$<?= number_format($reserva['total_general'] ?? 0, 0, ',', '.') ?></div>
                                </div>
                            </div>
                            
                            <div class="mp-security mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-shield-alt text-success me-2"></i>
                                    <div>
                                        <strong class="text-success">Compra Protegida</strong>
                                        <br><small class="text-muted">Tus datos están seguros y protegidos</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Métodos de pago estilo MercadoPago -->
                        <div class="mb-4">
                            <h5 class="mb-3">¿Cómo querés pagar?</h5>
                            
                            <form id="formMetodoPago">
                                <!-- Mercado Crédito (Dinero en cuenta) - PRIMERO -->
                                <div class="mp-payment-method" data-method="mercado_credito">
                                    <div class="d-flex align-items-center">
                                        <div class="method-icon icon-nequi">
                                            <i class="fas fa-hand-holding-usd text-white"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <strong>Mercado Crédito</strong>
                                            <br><small class="text-muted">Hasta 12 cuotas sin tarjeta</small>
                                        </div>
                                        <input class="form-check-input" type="radio" name="metodo_pasarela" id="mercado_credito" value="mercado_credito" checked>
                                    </div>
                                </div>
                                
                                <div id="campos_mercado_credito" class="metodo-campos mt-3 mb-4">
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold">Cuotas disponibles</label>
                                        <select class="form-select mp-input" id="cuotas_mercado">
                                            <option value="1">1 cuota sin interés</option>
                                            <option value="3">3 cuotas sin interés</option>
                                            <option value="6">6 cuotas sin interés</option>
                                            <option value="12">12 cuotas sin interés</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Tarjeta de Crédito/Débito -->
                                <div class="mp-payment-method" data-method="tarjeta">
                                    <div class="d-flex align-items-center">
                                        <div class="method-icon icon-card">
                                            <i class="fas fa-credit-card text-white"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <strong>Tarjeta de crédito o débito</strong>
                                            <br><small class="text-muted">Visa, Mastercard, American Express, Cabal, Maestro</small>
                                        </div>
                                        <input class="form-check-input" type="radio" name="metodo_pasarela" id="tarjeta" value="tarjeta">
                                    </div>
                                </div>
                                
                                <div id="campos_tarjeta" class="metodo-campos mt-3 mb-4" style="display: none;">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Número de la tarjeta</label>
                                            <input type="text" class="form-control mp-input" id="numero_tarjeta" placeholder="0000 0000 0000 0000" maxlength="19">
                                            <small class="text-warning"><i class="fas fa-info-circle me-1"></i>Usa números que contengan "1234" para simular un rechazo</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Vencimiento</label>
                                            <input type="text" class="form-control mp-input" id="vencimiento" placeholder="MM/AA" maxlength="5">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Código de seguridad</label>
                                            <input type="text" class="form-control mp-input" id="cvv" placeholder="123" maxlength="3">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Nombre y apellido</label>
                                            <input type="text" class="form-control mp-input" id="nombre_tarjeta" placeholder="Como aparece en la tarjeta">
                                        </div>
                                    </div>
                                </div>


                            </form>
                        </div>

                        <!-- Botón de pago principal estilo MercadoPago -->
                        <div class="d-grid mb-4">
                            <form method="POST" action="<?= $this->url('/reservas/procesar-pasarela') ?>" id="formPagar">
                                <input type="hidden" name="estado" value="aprobado">
                                <input type="hidden" name="metodo_pasarela" id="metodo_pagar" value="mercado_credito">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <button type="submit" class="btn mp-button w-100 py-3" id="btnPagar" disabled>
                                    <span class="fs-5 fw-bold">Pagar $<?= number_format($reserva['total_general'] ?? 0, 0, ',', '.') ?></span>
                                </button>
                            </form>
                        </div>

                        <!-- Simulación - Solo para pruebas -->
                        <div class="border rounded p-3 mb-4" style="background: #fff3cd;">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-flask text-warning me-2"></i>
                                <strong class="text-warning">Modo Simulación</strong>
                            </div>
                            <p class="mb-3 small text-muted">Esta es una demo. Para simular diferentes resultados:</p>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <form method="POST" action="<?= $this->url('/reservas/procesar-pasarela') ?>" id="formRechazar">
                                        <input type="hidden" name="estado" value="rechazado">
                                        <input type="hidden" name="metodo_pasarela" id="metodo_rechazar" value="">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100" id="btnRechazar">
                                            <i class="fas fa-times me-1"></i> Simular Rechazo
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <button class="btn btn-outline-info btn-sm w-100" onclick="window.location.href='/reservas/resumen'">
                                        <i class="fas fa-arrow-left me-1"></i> Volver al Resumen
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Información de seguridad -->
                        <div class="text-center">
                            <div class="d-inline-flex align-items-center text-muted small">
                                <i class="fas fa-lock me-2"></i>
                                <span>Tus datos están protegidos por</span>
                                <strong class="ms-1">SSL 256-bits</strong>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Certificado por DigiCert • PCI DSS Compliant
                                </small>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts estilo MercadoPago -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnRechazar = document.getElementById('btnRechazar');
    const btnPagar = document.getElementById('btnPagar');
    const metodosRadio = document.querySelectorAll('input[name="metodo_pasarela"]');
    const camposMetodos = document.querySelectorAll('.metodo-campos');
    const paymentMethods = document.querySelectorAll('.mp-payment-method');
    const numeroTarjeta = document.getElementById('numero_tarjeta');
    const vencimiento = document.getElementById('vencimiento');

    // Manejar clicks en los métodos de pago estilo MercadoPago
    paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
            // Remover selección previa
            paymentMethods.forEach(m => m.classList.remove('selected'));
            
            // Seleccionar método actual
            this.classList.add('selected');
            
            // Marcar radio correspondiente
            const methodType = this.dataset.method;
            const radio = document.getElementById(methodType);
            if (radio) {
                radio.checked = true;
            }
            
            // Ocultar todos los campos específicos
            camposMetodos.forEach(campo => {
                campo.style.display = 'none';
            });

            // Mostrar campos del método seleccionado
            const campoId = 'campos_' + methodType;
            const campoMetodo = document.getElementById(campoId);
            
            if (campoMetodo) {
                campoMetodo.style.display = 'block';
            }

            // Actualizar formularios cuando cambie la selección
            actualizarMetodoEnFormularios();
            
            // Verificar si se puede habilitar el botón de pago
            verificarFormulario();
        });
    });

    // También manejar cambios directos en los radios
    metodosRadio.forEach(radio => {
        radio.addEventListener('change', function() {
            const method = document.querySelector(`[data-method="${this.value}"]`);
            if (method) {
                method.click();
            }
        });
    });

    // Formatear número de tarjeta con validación en tiempo real
    if (numeroTarjeta) {
        numeroTarjeta.addEventListener('input', function() {
            let valor = this.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let valorFormateado = valor.match(/.{1,4}/g)?.join(' ');
            this.value = valorFormateado || valor;
            
            // Verificar formulario cada vez que cambie
            verificarFormulario();
        });
    }



    // Formatear vencimiento de crédito
    if (vencimiento) {
        vencimiento.addEventListener('input', function() {
            let valor = this.value.replace(/\D/g, '');
            if (valor.length >= 2) {
                this.value = valor.substring(0, 2) + '/' + valor.substring(2, 4);
            } else {
                this.value = valor;
            }
            verificarFormulario();
        });
    }



    // Agregar validación a todos los campos
    document.querySelectorAll('.mp-input').forEach(input => {
        input.addEventListener('input', verificarFormulario);
        input.addEventListener('change', verificarFormulario);
    });

    // Función para obtener método seleccionado
    function obtenerMetodoSeleccionado() {
        const metodoSeleccionado = document.querySelector('input[name="metodo_pasarela"]:checked');
        return metodoSeleccionado ? metodoSeleccionado.value : null;
    }

    // Función para actualizar campos ocultos con el método seleccionado
    function actualizarMetodoEnFormularios() {
        const metodo = obtenerMetodoSeleccionado();
        if (metodo) {
            const metodoRechazar = document.getElementById('metodo_rechazar');
            const metodoPagar = document.getElementById('metodo_pagar');
            
            if (metodoRechazar) metodoRechazar.value = metodo;
            if (metodoPagar) metodoPagar.value = metodo;
        }
    }

    // Función para verificar si el formulario está completo
    function verificarFormulario() {
        const metodoSeleccionado = document.querySelector('input[name="metodo_pasarela"]:checked');
        
        if (!metodoSeleccionado) {
            btnPagar.disabled = true;
            return false;
        }

        const metodo = metodoSeleccionado.value;
        let formularioCompleto = true;

        // Validaciones específicas por método
        if (metodo === 'tarjeta') {
            const numero = numeroTarjeta ? numeroTarjeta.value.replace(/\s/g, '') : '';
            const cvv = document.getElementById('cvv')?.value || '';
            const venc = document.getElementById('vencimiento')?.value || '';
            const nombre = document.getElementById('nombre_tarjeta')?.value || '';

            formularioCompleto = numero.length >= 13 && cvv.length >= 3 && venc.length === 5 && nombre.length >= 2;

        } else if (metodo === 'mercado_credito') {
            const cuotas = document.getElementById('cuotas_mercado')?.value || '';
            formularioCompleto = cuotas !== '';
        }

        btnPagar.disabled = !formularioCompleto;
        
        // Cambiar apariencia del botón según el estado
        if (formularioCompleto) {
            btnPagar.classList.remove('mp-button:disabled');
            btnPagar.style.background = '#009ee3';
        } else {
            btnPagar.style.background = '#cccccc';
        }

        return formularioCompleto;
    }

    // Función para validar método de pago
    function validarMetodoPago() {
        const metodoSeleccionado = document.querySelector('input[name="metodo_pasarela"]:checked');
        
        if (!metodoSeleccionado) {
            Swal.fire({
                icon: 'error',
                title: 'Método de pago requerido',
                text: 'Por favor selecciona un método de pago antes de continuar',
                confirmButtonColor: '#dc3545'
            });
            return false;
        }

        const metodo = metodoSeleccionado.value;
        
        // Actualizar formularios con el método seleccionado
        actualizarMetodoEnFormularios();

        // Validaciones específicas por método
        if (metodo === 'tarjeta') {
            const numero = numeroTarjeta ? numeroTarjeta.value.replace(/\s/g, '') : '';
            const cvv = document.getElementById('cvv').value;
            const venc = document.getElementById('vencimiento').value;
            const nombre = document.getElementById('nombre_tarjeta').value;

            if (!numero || !cvv || !venc || !nombre) {
                Swal.fire({
                    icon: 'error',
                    title: 'Datos incompletos',
                    text: 'Por favor completa todos los campos de la tarjeta de crédito',
                    confirmButtonColor: '#009ee3'
                });
                return false;
            }

        } else if (metodo === 'mercado_credito') {
            const cuotas = document.getElementById('cuotas_mercado')?.value || '';
            if (!cuotas) {
                Swal.fire({
                    icon: 'error',
                    title: 'Selecciona las cuotas',
                    text: 'Por favor selecciona la cantidad de cuotas para Mercado Crédito',
                    confirmButtonColor: '#009ee3'
                });
                return false;
            }
        }

        return true;
    }

    // Función para verificar rechazo automático
    function verificarRechazoAutomatico() {
        const metodoSeleccionado = document.querySelector('input[name="metodo_pasarela"]:checked');
        
        if (metodoSeleccionado && metodoSeleccionado.value === 'tarjeta' && numeroTarjeta) {
            const numero = numeroTarjeta.value.replace(/\s/g, '');
            if (numero && numero.includes('1234')) {
                return true; // Forzar rechazo
            }
        }
        
        return false;
    }

    // Event listener para el botón principal de pago (estilo MercadoPago)
    if (btnPagar) {
        btnPagar.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (this.disabled) return;
            
            // Verificar rechazo automático para tarjeta
            if (verificarRechazoAutomatico()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Tarjeta rechazada',
                    text: 'Los datos ingresados no son válidos. Por favor, verifica la información.',
                    confirmButtonText: 'Intentar de nuevo',
                    confirmButtonColor: '#009ee3'
                });
                return;
            }
            
            // Confirmar pago
            Swal.fire({
                title: '¿Confirmar pago?',
                html: `Se procesará el pago de <strong>$<?= number_format($reserva['total_general'] ?? 0, 0, ',', '.') ?></strong>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#009ee3',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, pagar',
                cancelButtonText: 'Revisar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading estilo MercadoPago
                    Swal.fire({
                        title: 'Procesando pago...',
                        html: '<div class="d-flex align-items-center justify-content-center"><div class="spinner-border text-primary me-2" role="status"></div>Verificando datos...</div>',
                        allowOutsideClick: false,
                        showConfirmButton: false
                    });
                    
                    setTimeout(() => {
                        this.closest('form').submit();
                    }, 2500);
                }
            });
        });
    }

    // Event listener para simulación de rechazo
    if (btnRechazar) {
        btnRechazar.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (!verificarFormulario()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Completa los datos',
                    text: 'Por favor completa todos los campos requeridos',
                    confirmButtonColor: '#009ee3'
                });
                return;
            }
            
            Swal.fire({
                title: '¿Simular rechazo?',
                text: 'Esto simulará un pago rechazado para pruebas',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, simular rechazo',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Simulando rechazo...',
                        text: 'Procesando simulación',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    setTimeout(() => {
                        this.closest('form').submit();
                    }, 2000);
                }
            });
        });
    }

    // Inicializar - seleccionar primer método por defecto
    const firstMethod = document.querySelector('.mp-payment-method');
    if (firstMethod) {
        firstMethod.click();
    }
    
    // Verificar formulario inicial
    verificarFormulario();
    actualizarMetodoEnFormularios();
});
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>