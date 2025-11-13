<?php
/**
 * Vista: Formulario de Huésped
 * Descripción: Formulario para crear/editar huéspedes con persona y condiciones de salud
 * Autor: Sistema MVC
 * Fecha: <?php echo date('Y-m-d'); ?>
 */

$isEdit = isset($huesped) && !empty($huesped);
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= $this->url('/huespedes') ?>" class="btn btn-primary">
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
                        <?= $isEdit ? 'Modificar datos del huésped' : 'Datos del nuevo huésped' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formHuesped" method="POST" 
                          action="<?= $isEdit ? $this->url('/huespedes/' . $huesped['id_huesped'] . '/edit') : $this->url('/huespedes/create') ?>" 
                          novalidate>
                        
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_huesped" value="<?= $huesped['id_huesped'] ?>">
                        <?php endif; ?>

                        <?php if (!$isEdit): ?>
                            <!-- Campos de Persona (solo en creación) -->
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-user-plus"></i> Información de la Persona
                            </h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="persona_nombre" class="required">
                                            <i class="fas fa-user"></i> Nombre
                                        </label>
                                        <input type="text" class="form-control form-control-sm" id="persona_nombre" 
                                               name="persona_nombre" required maxlength="45" 
                                               placeholder="Nombre de la persona">
                                        <div class="invalid-feedback">El nombre es obligatorio</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="persona_apellido" class="required">
                                            <i class="fas fa-user"></i> Apellido
                                        </label>
                                        <input type="text" class="form-control form-control-sm" id="persona_apellido" 
                                               name="persona_apellido" required maxlength="45" 
                                               placeholder="Apellido de la persona">
                                        <div class="invalid-feedback">El apellido es obligatorio</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="persona_fechanac" class="required">
                                            <i class="fas fa-calendar"></i> Fecha de Nacimiento
                                        </label>
                                        <input type="date" class="form-control form-control-sm" id="persona_fechanac" 
                                               name="persona_fechanac" required 
                                               max="<?= date('Y-m-d') ?>">
                                        <div class="invalid-feedback">La fecha de nacimiento es obligatoria</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="persona_direccion" class="required">
                                            <i class="fas fa-home"></i> Dirección
                                        </label>
                                        <input type="text" class="form-control form-control-sm" id="persona_direccion" 
                                               name="persona_direccion" required maxlength="45" 
                                               placeholder="Dirección completa">
                                        <div class="invalid-feedback">La dirección es obligatoria</div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Condiciones de Salud -->
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-heartbeat"></i> Condiciones de Salud
                            </h6>

                            <div class="form-group">
                                <div class="asignaciones-container">
                                    <?php if (!empty($condicionesSalud)): ?>
                                        <?php foreach ($condicionesSalud as $condicion): ?>
                                            <span class="badge asignacion-badge no-seleccionado" 
                                                  data-condicion-id="<?= $condicion['id_condicionsalud'] ?>" 
                                                  data-tipo="condicion">
                                                <?= htmlspecialchars($condicion['condicionsalud_descripcion']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <!-- Inputs hidden para enviar las condiciones seleccionadas -->
                                        <div id="condiciones-hidden-container"></div>
                                    <?php else: ?>
                                        <p class="text-muted small">No hay condiciones de salud disponibles</p>
                                    <?php endif; ?>
                                </div>
                                <small class="form-text text-muted d-block mt-3">
                                    Haga clic en las condiciones de salud que apliquen al huésped
                                </small>
                            </div>

                            <hr class="my-4">

                            <!-- Asociación con Reserva (opcional) -->
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-calendar-check"></i> Asociar Reserva (Opcional)
                            </h6>

                            <div class="form-group">
                                <label for="rela_reserva">
                                    <i class="fas fa-clipboard-list"></i> Reserva
                                </label>
                                <select class="form-select form-select-sm" id="rela_reserva" name="rela_reserva">
                                    <option value="">Sin reserva asociada</option>
                                    <?php if (!empty($reservas)): ?>
                                        <?php foreach ($reservas as $reserva): ?>
                                            <option value="<?= $reserva['id_reserva'] ?>">
                                                Reserva #<?= $reserva['id_reserva'] ?> - 
                                                Desde: <?= date('d/m/Y H:i', strtotime($reserva['reserva_fhinicio'])) ?> 
                                                Hasta: <?= date('d/m/Y H:i', strtotime($reserva['reserva_fhfin'])) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Solo se muestran reservas futuras (fecha de fin mayor a la fecha/hora actual)
                                </small>
                            </div>

                            <hr class="my-4">

                            <!-- Datos de Huésped -->
                            <h6 class="border-bottom pb-2 mb-3 d-none">
                                <i class="fas fa-bed"></i> Información de Alojamiento
                            </h6>
                        <?php else: ?>
                            <!-- Información de la persona (editable en modo edición) -->
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-user-edit"></i> Información de la Persona
                            </h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="persona_nombre" class="required">
                                            <i class="fas fa-user"></i> Nombre
                                        </label>
                                        <input type="text" class="form-control form-control-sm" id="persona_nombre" 
                                               name="persona_nombre" required maxlength="45" 
                                               value="<?= htmlspecialchars($huesped['persona_nombre']) ?>"
                                               placeholder="Nombre de la persona">
                                        <div class="invalid-feedback">El nombre es obligatorio</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="persona_apellido" class="required">
                                            <i class="fas fa-user"></i> Apellido
                                        </label>
                                        <input type="text" class="form-control form-control-sm" id="persona_apellido" 
                                               name="persona_apellido" required maxlength="45" 
                                               value="<?= htmlspecialchars($huesped['persona_apellido']) ?>"
                                               placeholder="Apellido de la persona">
                                        <div class="invalid-feedback">El apellido es obligatorio</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="persona_fechanac" class="required">
                                            <i class="fas fa-calendar"></i> Fecha de Nacimiento
                                        </label>
                                        <input type="date" class="form-control form-control-sm" id="persona_fechanac" 
                                               name="persona_fechanac" required 
                                               value="<?= htmlspecialchars($huesped['persona_fechanac']) ?>"
                                               max="<?= date('Y-m-d') ?>">
                                        <div class="invalid-feedback">La fecha de nacimiento es obligatoria</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="persona_direccion" class="required">
                                            <i class="fas fa-home"></i> Dirección
                                        </label>
                                        <input type="text" class="form-control form-control-sm" id="persona_direccion" 
                                               name="persona_direccion" required maxlength="45" 
                                               value="<?= htmlspecialchars($huesped['persona_direccion']) ?>"
                                               placeholder="Dirección completa">
                                        <div class="invalid-feedback">La dirección es obligatoria</div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Condiciones de Salud en Edición -->
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-heartbeat"></i> Condiciones de Salud
                            </h6>

                            <div class="form-group">
                                <div class="asignaciones-container">
                                    <?php if (!empty($condicionesSalud)): ?>
                                        <?php foreach ($condicionesSalud as $condicion): ?>
                                            <?php
                                            $idCondicion = $condicion['id_condicionsalud'];
                                            $estaSeleccionada = isset($condicionesHuesped[$idCondicion]) && $condicionesHuesped[$idCondicion] == 1;
                                            $claseEstado = $estaSeleccionada ? 'seleccionado-salud' : 'no-seleccionado';
                                            ?>
                                            <span class="badge asignacion-badge <?= $claseEstado ?>" 
                                                  data-condicion-id="<?= $idCondicion ?>" 
                                                  data-tipo="condicion">
                                                <?= htmlspecialchars($condicion['condicionsalud_descripcion']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <!-- Inputs hidden para enviar las condiciones seleccionadas -->
                                        <div id="condiciones-hidden-container">
                                            <?php foreach ($condicionesSalud as $condicion): ?>
                                                <?php if (isset($condicionesHuesped[$condicion['id_condicionsalud']]) && $condicionesHuesped[$condicion['id_condicionsalud']] == 1): ?>
                                                    <input type="hidden" name="condiciones_salud[]" value="<?= $condicion['id_condicionsalud'] ?>" id="hidden-condicion-<?= $condicion['id_condicionsalud'] ?>">
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted small">No hay condiciones de salud disponibles</p>
                                    <?php endif; ?>
                                </div>
                                <small class="form-text text-muted d-block mt-3">
                                    Haga clic en las condiciones de salud que apliquen al huésped
                                </small>
                            </div>

                            <hr class="my-4">

                            <!-- Asociar Reserva (Opcional) -->
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-calendar-check"></i> Asociar Reserva (Opcional)
                            </h6>

                            <div class="form-group">
                                <select class="form-select form-select-sm" id="rela_reserva" name="rela_reserva">
                                    <option value="">Sin reserva asociada</option>
                                    <?php if (!empty($reservas)): ?>
                                        <?php foreach ($reservas as $reserva): ?>
                                            <option value="<?= $reserva['id_reserva'] ?>" 
                                                <?= (isset($reservaActualId) && $reservaActualId == $reserva['id_reserva']) ? 'selected' : '' ?>>
                                                Reserva #<?= $reserva['id_reserva'] ?> - 
                                                Desde: <?= date('d/m/Y H:i', strtotime($reserva['reserva_fhinicio'])) ?> 
                                                Hasta: <?= date('d/m/Y H:i', strtotime($reserva['reserva_fhfin'])) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Solo se muestran reservas futuras (fecha de fin mayor a la fecha/hora actual)
                                </small>
                            </div>

                            <hr class="my-4">
                        <?php endif; ?>

                        <!-- Ubicación (solo en edición) -->
                        <?php if ($isEdit): ?>
                        <div class="form-group">
                            <label for="huesped_ubicacion">
                                <i class="fas fa-map-marker-alt"></i> Ubicación Actual
                            </label>
                            <input type="text" class="form-control form-control-sm" id="huesped_ubicacion" 
                                   name="huesped_ubicacion" 
                                   value="<?= htmlspecialchars($huesped['huesped_ubicacion'] ?? '') ?>"
                                   maxlength="100" 
                                   placeholder="Ej: Habitación 205, Cabaña Norte">
                            <small class="form-text text-muted">
                                Ubicación opcional donde se encuentra alojado actualmente el huésped
                            </small>
                        </div>
                        <?php endif; ?>

                        <!-- Botones de acción -->
                        <div class="form-group mt-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save"></i> 
                                        <?= $isEdit ? 'Actualizar Huésped' : 'Crear Huésped' ?>
                                    </button>
                                    <?php if (!$isEdit): ?>
                                        <button type="button" class="btn btn-outline-secondary btn-lg ml-2" 
                                                onclick="limpiarFormulario()">
                                            <i class="fas fa-eraser"></i> Limpiar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
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
                            <?php if ($isEdit): ?>
                                <li>• Puede modificar todos los datos personales del huésped</li>
                                <li>• La ubicación es opcional pero útil para gestión</li>
                                <li>• Actualice las condiciones de salud según sea necesario</li>
                                <li>• El huésped se puede activar/desactivar cuando sea necesario</li>
                            <?php else: ?>
                                <li>• Complete todos los campos obligatorios (*)</li>
                                <li>• Verifique que la fecha de nacimiento sea correcta</li>
                                <li>• Marque solo las condiciones de salud que apliquen</li>
                                <li>• La asociación con reserva es opcional</li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <hr>

                    <div class="info-section">
                        <h6><i class="fas fa-chart-line text-info"></i> Estadísticas</h6>
                        <br>
                        <?php if ($isEdit): ?>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value"><?= $estadisticas['reservas_totales'] ?? 0 ?></div>
                                        <div class="stat-label small text-muted">Reservas</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <div class="stat-value">$<?= number_format($estadisticas['gasto_total'] ?? 0, 0, '.', ',') ?></div>
                                        <div class="stat-label small text-muted">Gasto Total</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted">
                                Las estadísticas estarán disponibles después de crear el huésped.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para validaciones y funcionalidades -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formHuesped');
    
    if (form) {
        // Validación del formulario
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }
});

function limpiarFormulario() {
    const form = document.getElementById('formHuesped');
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
    }
}
</script>

<style>
.stat-item {
    padding: 10px 0;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
}

.stat-label {
    margin-top: 5px;
    font-size: 0.85rem;
}

.required::after {
    content: " *";
    color: #dc3545;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-check-inline {
    display: flex;
    align-items: center;
    width: 100%;
}

.form-check-input {
    margin-right: 8px;
}
</style>


<!-- JavaScript para validaciones y funcionalidades -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formHuesped');
    
    if (form) {
        // Validación del formulario
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }

    // Manejo de badges de condiciones de salud
    const badges = document.querySelectorAll('.asignacion-badge[data-tipo="condicion"]');
    const hiddenContainer = document.getElementById('condiciones-hidden-container');

    badges.forEach(function(badge) {
        badge.addEventListener('click', function() {
            const condicionId = this.getAttribute('data-condicion-id');
            const isSelected = this.classList.contains('seleccionado-salud');

            if (isSelected) {
                // Deseleccionar
                this.classList.remove('seleccionado-salud');
                this.classList.add('no-seleccionado');
                // Remover input hidden
                const hiddenInput = document.getElementById('hidden-condicion-' + condicionId);
                if (hiddenInput) {
                    hiddenInput.remove();
                }
            } else {
                // Seleccionar
                this.classList.remove('no-seleccionado');
                this.classList.add('seleccionado-salud');
                // Agregar input hidden
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'condiciones_salud[]';
                input.value = condicionId;
                input.id = 'hidden-condicion-' + condicionId;
                hiddenContainer.appendChild(input);
            }
        });
    });
});

function limpiarFormulario() {
    const form = document.getElementById('formHuesped');
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
        
        // Resetear badges
        const badges = document.querySelectorAll('.asignacion-badge[data-tipo="condicion"]');
        badges.forEach(function(badge) {
            badge.classList.remove('seleccionado-salud');
            badge.classList.add('no-seleccionado');
        });
        
        // Limpiar inputs hidden
        const hiddenContainer = document.getElementById('condiciones-hidden-container');
        if (hiddenContainer) {
            hiddenContainer.innerHTML = '';
        }
    }
}
</script>

<style>
.stat-item {
    padding: 10px 0;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
}

.stat-label {
    margin-top: 5px;
    font-size: 0.85rem;
}

.required::after {
    content: " *";
    color: #dc3545;
}

.form-group {
    margin-bottom: 1.5rem;
}

/* Estilos específicos de huéspedes (badges centralizados en main.css) */
</style>
