<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <!-- Card Principal con diseño mejorado -->
            <div class="card shadow-lg border-0" style="overflow: hidden;">
                
                <!-- Header -->
                <div class="card-header bg-white border-bottom" style="padding: 1.5rem 2rem;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-1 fw-bold text-dark">
                                <i class="fas fa-lock text-secondary me-2"></i>Procesar Pago Seguro
                            </h4>
                            <p class="mb-0 text-muted small">Protegido por MercadoPago</p>
                        </div>
                    </div>
                </div>

                <!-- Resumen de Reserva -->
                <div class="card-body p-4 bg-light">
                    <div class="row g-4">
                        <div class="col-md-7">
                            <div class="mb-4">
                                <label class="text-muted small text-uppercase mb-1">
                                    Cabaña Seleccionada
                                </label>
                                <h5 class="text-dark mb-0 fw-bold">
                                    <?= htmlspecialchars($cabania['cabania_nombre']) ?>
                                </h5>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="p-3 bg-white border rounded">
                                        <div class="row">
                                            <div class="col-6 border-end">
                                                <small class="text-muted d-block mb-1">Check-in</small>
                                                <strong class="text-dark"><?= date('d/m/Y', strtotime($reserva['reserva_ingreso'])) ?></strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block mb-1">Check-out</small>
                                                <strong class="text-dark"><?= date('d/m/Y', strtotime($reserva['reserva_salida'])) ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <div class="p-3 bg-white border rounded">
                                    <small class="text-muted d-block mb-1">Número de Reserva</small>
                                    <strong class="text-dark"><?= htmlspecialchars($reserva['reserva_nro']) ?></strong>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-5">
                            <div class="h-100 d-flex align-items-center">
                                <div class="w-100 p-4 bg-white border rounded text-center">
                                    <small class="text-muted text-uppercase d-block mb-2">Total a Pagar</small>
                                    <h2 class="mb-2 text-dark fw-bold">
                                        $<?= number_format($total_amount, 2, ',', '.') ?>
                                    </h2>
                                    <small class="text-muted">ARS - Pesos Argentinos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Pago -->
                <div class="card-body p-4 p-md-5">
                    
                    <!-- Mensaje informativo -->
                    <div class="alert alert-light border mb-4">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-info-circle text-secondary me-3 mt-1"></i>
                            <div>
                                <h6 class="fw-semibold mb-2">¿Cómo funciona el pago?</h6>
                                <p class="mb-0 small text-muted">
                                    Al hacer clic en el botón de pago, será redirigido a la plataforma segura de MercadoPago. 
                                    Allí podrá elegir su método preferido (tarjeta, transferencia, etc.) y completar la transacción 
                                    de forma 100% protegida. También puede pagar en cuotas sin interés según disponibilidad.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Contenedor del Wallet Brick -->
                    <div class="text-center my-4">
                        <div id="wallet_container" class="d-inline-block w-100" style="max-width: 400px;"></div>
                    </div>

                    <!-- Mensaje de carga elegante -->
                    <div id="loading" class="text-center py-5">
                        <div class="mb-4">
                            <div class="spinner-border text-primary" role="status" style="width: 3.5rem; height: 3.5rem; border-width: 4px;">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-sync-alt fa-spin me-2"></i>
                            Preparando pasarela de pago segura
                        </h6>
                        <p class="text-muted small mb-0">Esto solo tomará unos segundos...</p>
                    </div>
                    
                    <!-- Badges de seguridad -->
                    <div class="text-center mt-4 pt-4 border-top">
                        <div class="d-flex flex-wrap justify-content-center align-items-center gap-4">
                            <div class="d-flex align-items-center text-muted">
                                <i class="fas fa-lock me-2"></i>
                                <small>SSL 256-bits</small>
                            </div>
                            <div class="d-flex align-items-center text-muted">
                                <i class="fas fa-shield-alt me-2"></i>
                                <small>Compra Protegida</small>
                            </div>
                            <div class="d-flex align-items-center text-muted">
                                <i class="fas fa-check-circle me-2"></i>
                                <small>PCI DSS Compliant</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón para volver -->
            <div class="text-center mt-4">
                <a href="<?= url('/reservas/resumen') ?>" class="btn btn-outline-secondary px-4">
                    <i class="fas fa-arrow-left me-2"></i>Volver al Resumen
                </a>
            </div>

        </div>
    </div>
</div>

<!-- SDK de MercadoPago v2 -->
<script src="https://sdk.mercadopago.com/js/v2"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando MercadoPago Checkout Pro...');
    
    try {
        // Inicializar MercadoPago con la public key
        const mp = new MercadoPago('<?= htmlspecialchars($public_key) ?>', {
            locale: 'es-AR'
        });

        console.log('MercadoPago inicializado correctamente');
        console.log('Preference ID:', '<?= htmlspecialchars($preference_id) ?>');

        // Crear Wallet Brick (botón de pago)
        mp.bricks().create("wallet", "wallet_container", {
            initialization: {
                preferenceId: '<?= htmlspecialchars($preference_id) ?>',
                redirectMode: 'self' // Redirige en la misma ventana
            },
            customization: {
                texts: {
                    valueProp: 'security_safety', // Muestra mensaje de seguridad
                    action: 'pay', // Texto del botón: "Pagar"
                }
            },
            callbacks: {
                onReady: () => {
                    // Ocultar mensaje de carga cuando el botón esté listo
                    const loadingElement = document.getElementById('loading');
                    if (loadingElement) {
                        loadingElement.style.display = 'none';
                    }
                    console.log('✓ Wallet Brick cargado y listo');
                },
                onError: (error) => {
                    console.error('✗ Error en Wallet Brick:', error);
                    
                    const loadingElement = document.getElementById('loading');
                    if (loadingElement) {
                        loadingElement.innerHTML = 
                            '<div class="alert alert-danger">' +
                            '<i class="fas fa-exclamation-triangle me-2"></i>' +
                            '<strong>Error al cargar la pasarela de pago</strong><br>' +
                            '<small>Por favor, recargue la página o intente nuevamente más tarde.</small><br>' +
                            '<button class="btn btn-sm btn-danger mt-2" onclick="location.reload()">' +
                            '<i class="fas fa-sync-alt me-1"></i>Reintentar' +
                            '</button>' +
                            '</div>';
                    }
                },
                onSubmit: () => {
                    console.log('Usuario hizo clic en el botón de pago');
                    // El SDK de MercadoPago maneja automáticamente la redirección
                }
            }
        }).catch(error => {
            console.error('Error creando Wallet Brick:', error);
            
            const loadingElement = document.getElementById('loading');
            if (loadingElement) {
                loadingElement.innerHTML = 
                    '<div class="alert alert-danger">' +
                    '<i class="fas fa-times-circle me-2"></i>' +
                    '<strong>No se pudo inicializar el sistema de pagos</strong><br>' +
                    '<small>Error: ' + (error.message || 'Error desconocido') + '</small>' +
                    '</div>';
            }
        });

    } catch (error) {
        console.error('Error fatal inicializando MercadoPago:', error);
        
        const loadingElement = document.getElementById('loading');
        if (loadingElement) {
            loadingElement.innerHTML = 
                '<div class="alert alert-danger">' +
                '<i class="fas fa-exclamation-circle me-2"></i>' +
                '<strong>Error crítico</strong><br>' +
                '<small>No se pudo cargar el sistema de pagos. Por favor, contacte al soporte.</small>' +
                '</div>';
        }
    }
});
</script>

<style>
/* Estilos mejorados para la pasarela */
#wallet_container {
    min-height: 80px;
    transition: all 0.3s ease;
}

.spinner-border {
    animation: spinner-border 0.75s linear infinite;
}

/* Animaciones suaves */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.6s ease;
    border-radius: 1rem !important;
}

/* Efectos hover mejorados */
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
    transition: all 0.3s ease;
}

.btn-outline-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

/* Gradientes suaves */
.bg-gradient-primary {
    background: linear-gradient(135deg, #009ee3 0%, #0070ba 100%);
}

.bg-gradient-purple {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Responsividad mejorada */
@media (max-width: 768px) {
    .card-body {
        padding: 1.5rem !important;
    }
    
    h1, .display-4 {
        font-size: 2.5rem !important;
    }
    
    .card-header {
        padding: 1.5rem !important;
    }
}

/* Mejoras visuales */
.shadow-soft {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

.rounded-3 {
    border-radius: 1rem !important;
}

.gap-4 {
    gap: 1.5rem !important;
}
</style>
