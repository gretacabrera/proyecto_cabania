<?php
/**
 * Vista: Formulario de Reserva
 * Descripción: Formulario unificado para crear/editar reservas
 * Autor: Sistema MVC
 * Fecha: <?php echo date('Y-m-d'); ?>
 */

$isEdit = isset($reserva) && !empty($reserva);
$title = $isEdit ? 'Editar Reserva' : 'Nueva Reserva';
$currentModule = 'reservas';

require_once 'app/Views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <?php if ($isEdit): ?>
                                Editar Reserva #<?= $reserva['id_reserva'] ?>
                            <?php else: ?>
                                Nueva Reserva
                            <?php endif; ?>
                        </h4>
                        <a href="/reservas" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al listado
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle"></i> Por favor corrija los siguientes errores:</h6>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                                        <form action="<?= $isEdit ? '/reservas/update/' . $reserva['id_reserva'] : '/reservas/store' ?>" 
                          method="POST" 
                          class="formulario-reserva" 
                          id="form-reserva">
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="_method" value="PUT">
                            <input type="hidden" name="id_reserva" value="<?= $reserva['id_reserva'] ?>">
                        <?php endif; ?>
                        <!-- Información del huésped -->
                        <div class="form-group">
                            <label for="persona_dni" class="required">DNI del huésped:</label>
                            <input type="number" 
                                   class="form-control <?= isset($errors) && !empty($errors) ? 'is-invalid' : '' ?>" 
                                   id="persona_dni" 
                                   name="persona_dni" 
                                   value="<?= $isEdit ? htmlspecialchars($reserva['persona_dni']) : (isset($data['persona_dni']) ? htmlspecialchars($data['persona_dni']) : '') ?>"
                                   placeholder="Ingrese el DNI del huésped"
                                   <?= $isEdit ? 'readonly' : '' ?>
                                   required>
                            <small class="form-text text-muted">
                                <?= $isEdit ? 'El DNI no se puede modificar una vez creada la reserva.' : 'Ingrese el número de DNI del huésped que realizará la reserva.' ?>
                            </small>
                            <div class="invalid-feedback">
                                Por favor ingrese un DNI válido.
                            </div>
                        </div>

                        <!-- Fechas de reserva -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reserva_fhinicio" class="required">Fecha y hora de inicio:</label>
                                    <input type="datetime-local" 
                                           class="form-control <?= isset($errors) && !empty($errors) ? 'is-invalid' : '' ?>" 
                                           id="reserva_fhinicio" 
                                           name="reserva_fhinicio" 
                                           value="<?= $isEdit ? htmlspecialchars($reserva['reserva_fhinicio']) : (isset($data['reserva_fhinicio']) ? htmlspecialchars($data['reserva_fhinicio']) : '') ?>"
                                           required>
                                    <div class="invalid-feedback">
                                        Por favor ingrese una fecha y hora de inicio válida.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reserva_fhfin" class="required">Fecha y hora de fin:</label>
                                    <input type="datetime-local" 
                                           class="form-control <?= isset($errors) && !empty($errors) ? 'is-invalid' : '' ?>" 
                                           id="reserva_fhfin" 
                                           name="reserva_fhfin" 
                                           value="<?= $isEdit ? htmlspecialchars($reserva['reserva_fhfin']) : (isset($data['reserva_fhfin']) ? htmlspecialchars($data['reserva_fhfin']) : '') ?>"
                                           required>
                                    <div class="invalid-feedback">
                                        Por favor ingrese una fecha y hora de fin válida.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selección de cabaña -->
                        <div class="form-group">
                            <label for="rela_cabania" class="required">Cabaña:</label>
                            <select class="form-control <?= isset($errors) && !empty($errors) ? 'is-invalid' : '' ?>" 
                                    id="rela_cabania" 
                                    name="rela_cabania" 
                                    required>
                                <option value="">Seleccione una cabaña...</option>
                                <?php foreach ($cabanias as $cabania): ?>
                                    <option value="<?= $cabania['id_cabania'] ?>" 
                                            <?php 
                                            $selected = $isEdit ? 
                                                ($reserva['rela_cabania'] == $cabania['id_cabania']) : 
                                                (($data['rela_cabania'] ?? '') == $cabania['id_cabania']); 
                                            echo $selected ? 'selected' : ''; 
                                            ?>>
                                        <?= htmlspecialchars($cabania['cabania_codigo'] . ' - ' . $cabania['cabania_nombre']) ?>
                                        (Capacidad: <?= $cabania['cabania_capacidad'] ?> personas)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                Seleccione la cabaña para la reserva. Solo se muestran cabañas activas.
                            </small>
                            <div class="invalid-feedback">
                                Por favor seleccione una cabaña.
                            </div>
                        </div>

                        <!-- Información adicional -->
                        <div class="form-group">
                            <label for="reserva_cantidadpersonas">Cantidad de personas:</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="reserva_cantidadpersonas" 
                                   name="reserva_cantidadpersonas" 
                                   value="<?= $isEdit ? htmlspecialchars($reserva['reserva_cantidadpersonas']) : (isset($data['reserva_cantidadpersonas']) ? htmlspecialchars($data['reserva_cantidadpersonas']) : '1') ?>"
                                   min="1" 
                                   max="10">
                            <small class="form-text text-muted">
                                Número de personas que se alojarán en la cabaña.
                            </small>
                        </div>

                        <!-- Método de pago -->
                        <div class="form-group">
                            <label for="rela_metodopago">Método de pago:</label>
                            <select class="form-control" id="rela_metodopago" name="rela_metodopago">
                                <option value="">Seleccione método de pago...</option>
                                <option value="1" <?php 
                                    $selected = $isEdit ? 
                                        ($reserva['rela_metodopago'] == '1') : 
                                        (($data['rela_metodopago'] ?? '') == '1'); 
                                    echo $selected ? 'selected' : ''; 
                                    ?>>Efectivo</option>
                                <option value="2" <?php 
                                    $selected = $isEdit ? 
                                        ($reserva['rela_metodopago'] == '2') : 
                                        (($data['rela_metodopago'] ?? '') == '2'); 
                                    echo $selected ? 'selected' : ''; 
                                    ?>>Tarjeta de débito</option>
                                <option value="3" <?php 
                                    $selected = $isEdit ? 
                                        ($reserva['rela_metodopago'] == '3') : 
                                        (($data['rela_metodopago'] ?? '') == '3'); 
                                    echo $selected ? 'selected' : ''; 
                                    ?>>Tarjeta de crédito</option>
                                <option value="4" <?php 
                                    $selected = $isEdit ? 
                                        ($reserva['rela_metodopago'] == '4') : 
                                        (($data['rela_metodopago'] ?? '') == '4'); 
                                    echo $selected ? 'selected' : ''; 
                                    ?>>Transferencia</option>
                            </select>
                        </div>

                        <!-- Observaciones -->
                        <div class="form-group">
                            <label for="reserva_observaciones">Observaciones:</label>
                            <textarea class="form-control" 
                                      id="reserva_observaciones" 
                                      name="reserva_observaciones" 
                                      rows="3" 
                                      placeholder="Observaciones adicionales sobre la reserva..."><?= $isEdit ? htmlspecialchars($reserva['reserva_observaciones']) : (isset($data['reserva_observaciones']) ? htmlspecialchars($data['reserva_observaciones']) : '') ?></textarea>
                            <small class="form-text text-muted">
                                Información adicional o requerimientos especiales.
                            </small>
                        </div>

                        <hr>
                        
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                <?= $isEdit ? 'Actualizar Reserva' : 'Crear Reserva' ?>
                            </button>
                            <a href="/reservas" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información de ayuda -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Información
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Instrucciones para <?= $isEdit ? 'editar' : 'crear' ?> una reserva:</h6>
                            <ul>
                                <?php if ($isEdit): ?>
                                    <li><strong>DNI del huésped:</strong> No se puede modificar una vez creada la reserva</li>
                                    <li><strong>Fechas:</strong> Puede modificar las fechas respetando las restricciones</li>
                                    <li><strong>Estado:</strong> Puede cambiar el estado según el progreso de la reserva</li>
                                <?php else: ?>
                                    <li><strong>DNI del huésped:</strong> Ingrese el DNI de la persona que realizará la reserva</li>
                                    <li><strong>Fechas:</strong> La fecha de inicio debe ser posterior a hoy y la fecha de fin posterior al inicio</li>
                                    <li><strong>Estado:</strong> La reserva se creará con estado "Pendiente" hasta ser confirmada</li>
                                <?php endif; ?>
                                <li><strong>Cabaña:</strong> Seleccione una cabaña disponible para las fechas elegidas</li>
                            </ul>
                            
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-lightbulb"></i>
                                <strong>Tip:</strong> 
                                <?= $isEdit ? 
                                    'Los cambios se guardarán inmediatamente. Puede cancelar en cualquier momento.' : 
                                    'Una vez creada la reserva, puede editarla desde el listado principal para modificar fechas, cabaña o estado según sea necesario.' 
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('assets/js/main.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initReservas();
    
    // Configurar fecha mínima como hoy
    const fechaInputs = document.querySelectorAll('input[type="datetime-local"]');
    const hoy = new Date();
    const fechaMinima = hoy.toISOString().slice(0, 16);
    
    fechaInputs.forEach(input => {
        input.setAttribute('min', fechaMinima);
    });
    
    // Validación en tiempo real de fechas
    const fechaInicio = document.getElementById('reserva_fhinicio');
    const fechaFin = document.getElementById('reserva_fhfin');
    
    if (fechaInicio && fechaFin) {
        fechaInicio.addEventListener('change', function() {
            if (this.value) {
                fechaFin.setAttribute('min', this.value);
                if (fechaFin.value && fechaFin.value <= this.value) {
                    fechaFin.value = '';
                }
            }
        });
    }
});
</script>

<?php require_once 'app/Views/layouts/footer.php'; ?>