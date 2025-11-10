<?php require_once 'app/Views/layouts/header.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="content-header">
            <h1 class="h3 mb-2 text-gray-800"><?= $title ?></h1>
            <div class="mb-4">
                <a href="/metodos_pagos" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <?= $method ? 'Editar Método de Pago' : 'Nuevo Método de Pago' ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <h6><i class="fas fa-exclamation-triangle"></i> Se encontraron los siguientes errores:</h6>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= $action ?>" id="metodoPagoForm">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="metododepago_descripcion" class="form-label required">
                                            Descripción del Método de Pago:
                                        </label>
                                        <input type="text" 
                                               class="form-control <?= !empty($errors) && strpos(implode(' ', $errors), 'descripción') !== false ? 'is-invalid' : '' ?>" 
                                               id="metododepago_descripcion" 
                                               name="metododepago_descripcion" 
                                               value="<?= htmlspecialchars($method->metododepago_descripcion ?? '') ?>" 
                                               maxlength="45"
                                               placeholder="Ej: Tarjeta de Crédito, Efectivo, Transferencia..."
                                               required>
                                        <small class="form-text text-muted">
                                            Máximo 45 caracteres. Este es el nombre que aparecerá en los reportes y formularios.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <?php if ($method): ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="metododepago_estado" class="form-label">Estado:</label>
                                            <select class="form-control" 
                                                    id="metododepago_estado" 
                                                    name="metododepago_estado">
                                                <option value="1" <?= ($method->metododepago_estado ?? 1) == 1 ? 'selected' : '' ?>>
                                                    Activo
                                                </option>
                                                <option value="0" <?= ($method->metododepago_estado ?? 1) == 0 ? 'selected' : '' ?>>
                                                    Inactivo
                                                </option>
                                            </select>
                                            <small class="form-text text-muted">
                                                Solo los métodos activos estarán disponibles para nuevas transacciones.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="fas fa-save"></i>
                                            <?= $method ? 'Actualizar Método de Pago' : 'Crear Método de Pago' ?>
                                        </button>
                                        <a href="/metodos_pagos" class="btn btn-secondary ml-2">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel de Ayuda -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-info-circle"></i> Información
                        </h6>
                    </div>
                    <div class="card-body">
                        <h6 class="text-primary">Acerca de los Métodos de Pago</h6>
                        <p class="text-muted small">
                            Los métodos de pago son las formas en que los huéspedes pueden realizar 
                            el pago de sus reservas.
                        </p>

                        <h6 class="text-primary mt-3">Consejos</h6>
                        <ul class="text-muted small">
                            <li>Use nombres descriptivos y claros</li>
                            <li>Mantenga activos solo los métodos disponibles</li>
                            <li>Los métodos inactivos no aparecen en nuevas reservas</li>
                            <li>No se pueden eliminar métodos que ya han sido utilizados</li>
                        </ul>

                        <h6 class="text-primary mt-3">Ejemplos Comunes</h6>
                        <ul class="text-muted small">
                            <li>Efectivo</li>
                            <li>Tarjeta de Crédito</li>
                            <li>Tarjeta de Débito</li>
                            <li>Transferencia Bancaria</li>
                            <li>Cheque</li>
                            <li>PayPal</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/Views/layouts/footer.php'; ?>