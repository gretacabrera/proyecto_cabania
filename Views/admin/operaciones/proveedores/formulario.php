<?php
/**
 * Vista: Formulario de Proveedor
 * Descripción: Formulario para crear/editar proveedores
 */

$isEdit = isset($proveedor) && !empty($proveedor);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/proveedores') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Formulario principal -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit"></i> 
                        <?= $isEdit ? 'Modificar datos del proveedor' : 'Datos del nuevo proveedor' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formProveedor" method="POST" 
                          action="<?= $isEdit ? url('/proveedores/' . $proveedor['id_proveedor'] . '/edit') : url('/proveedores/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_proveedor" value="<?= $proveedor['id_proveedor'] ?>">
                        <?php endif; ?>

                        <div class="row">
                            <!-- Denominación -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="persona_denominacion" class="required">
                                        <i class="fas fa-building"></i> Denominación / Razón Social
                                    </label>
                                    <input type="text" class="form-control" id="persona_denominacion" name="persona_denominacion" 
                                           value="<?= htmlspecialchars($proveedor['persona_denominacion'] ?? '') ?>"
                                           required maxlength="250" placeholder="Ej: Distribuidora ABC S.A.">
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Nombre completo de la empresa proveedora</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- CUIT -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="personajuridica_cuit" class="required">
                                        <i class="fas fa-id-card"></i> CUIT
                                    </label>
                                    <input type="text" class="form-control" id="personajuridica_cuit" name="personajuridica_cuit" 
                                           value="<?= htmlspecialchars($proveedor['personajuridica_cuit'] ?? '') ?>"
                                           required maxlength="13" placeholder="XX-XXXXXXXX-X" pattern="[0-9]{2}-[0-9]{8}-[0-9]{1}">
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">CUIT de la empresa (formato: XX-XXXXXXXX-X)</small>
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="persona_direccion" class="required">
                                        <i class="fas fa-map-marker-alt"></i> Dirección
                                    </label>
                                    <input type="text" class="form-control" id="persona_direccion" name="persona_direccion" 
                                           value="<?= htmlspecialchars($proveedor['persona_direccion'] ?? '') ?>"
                                           required maxlength="45" placeholder="Ej: Av. Principal 1234">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Correo Electrónico -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contacto_correo" class="required">
                                        <i class="fas fa-envelope"></i> Correo Electrónico
                                    </label>
                                    <input type="email" class="form-control" id="contacto_correo" name="contacto_correo" 
                                           value="<?= htmlspecialchars($proveedor['contacto_correo'] ?? '') ?>"
                                           required maxlength="45" placeholder="contacto@empresa.com">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contacto_telefono" class="required">
                                        <i class="fas fa-phone"></i> Teléfono
                                    </label>
                                    <input type="tel" class="form-control" id="contacto_telefono" name="contacto_telefono" 
                                           value="<?= htmlspecialchars($proveedor['contacto_telefono'] ?? '') ?>"
                                           required maxlength="45" placeholder="Ej: +54 9 11 1234-5678">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $isEdit ? 'Actualizar' : 'Guardar' ?> Proveedor
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-undo"></i> Limpiar
                            </button>
                            <a href="<?= url('/proveedores') ?>" class="btn btn-outline-dark">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel lateral con información -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información
                    </h6>
                </div>
                <div class="card-body">
                    <div class="info-section">
                        <h6><i class="fas fa-lightbulb text-warning"></i> Consejos</h6>
                        <ul class="list-unstyled small text-muted">
                            <li>• Use la denominación completa y oficial de la empresa</li>
                            <li>• Verifique que el CUIT sea correcto (11 dígitos)</li>
                            <li>• Mantenga los datos de contacto actualizados</li>
                            <li>• La dirección debe ser precisa para facturación</li>
                        </ul>
                    </div>

                    <?php if ($isEdit && isset($estadisticas)): ?>
                        <hr>
                        
                        <div class="info-section">
                            <h6><i class="fas fa-chart-line text-success"></i> Estadísticas</h6>
                            <div class="small">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total de compras:</span>
                                    <strong><?= number_format($estadisticas['total_compras'] ?? 0) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Monto total gastado:</span>
                                    <strong class="text-success">$<?= number_format($estadisticas['total_gastado'] ?? 0, 2, ',', '.') ?></strong>
                                </div>
                                <?php if (!empty($estadisticas['ultima_compra'])): ?>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Última compra:</span>
                                        <strong><?= date('d/m/Y', strtotime($estadisticas['ultima_compra'])) ?></strong>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-info-circle"></i> Sin compras registradas
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para validación -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formProveedor');
    
    // Validación en tiempo real
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Formatear CUIT automáticamente (XX-XXXXXXXX-X)
    const cuitInput = document.getElementById('personajuridica_cuit');
    if (cuitInput) {
        // Formatear valor inicial si existe
        if (cuitInput.value) {
            let value = cuitInput.value.replace(/\D/g, '');
            if (value.length === 11) {
                cuitInput.value = value.substr(0, 2) + '-' + value.substr(2, 8) + '-' + value.substr(10);
            }
        }

        cuitInput.addEventListener('input', function(e) {
            // Obtener solo números
            let value = e.target.value.replace(/\D/g, '');
            
            // Limitar a 11 dígitos
            if (value.length > 11) {
                value = value.substr(0, 11);
            }
            
            // Formatear según la cantidad de dígitos
            let formatted = '';
            if (value.length === 11) {
                // Formato completo: XX-XXXXXXXX-X
                formatted = value.substr(0, 2) + '-' + value.substr(2, 8) + '-' + value.substr(10);
            } else if (value.length > 2) {
                // Formato parcial: XX-XXXXXXXX...
                formatted = value.substr(0, 2) + '-' + value.substr(2);
            } else {
                // Solo números iniciales
                formatted = value;
            }
            
            e.target.value = formatted;
        });
    }
});
</script>

<style>
.required::after {
    content: " *";
    color: #dc3545;
    font-weight: bold;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #495057;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid #dee2e6;
}

.card-header {
    border-bottom: 2px solid #dee2e6;
}

.alert {
    border-radius: 0.375rem;
}

.alert-heading {
    font-size: 0.95rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.alert ul {
    margin-bottom: 0;
}

.alert ul li {
    line-height: 1.6;
}

.border-success {
    border-color: #198754 !important;
}

.border-secondary {
    border-color: #6c757d !important;
}

code {
    font-size: 0.9rem;
    padding: 0.2rem 0.4rem;
}

.btn-group {
    gap: 0.5rem;
}

.invalid-feedback {
    display: block;
    margin-top: 0.25rem;
}

.was-validated .form-control:invalid {
    border-color: #dc3545;
}

.was-validated .form-control:valid {
    border-color: #198754;
}

@media (max-width: 992px) {
    .col-lg-4 {
        margin-top: 1.5rem;
    }
}
</style>
