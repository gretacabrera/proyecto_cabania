<!-- Formulario para Agregar/Editar Huésped -->
<div class="container-fluid my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <!-- Header con diseño estandarizado -->
                <div class="card-header bg-white border-bottom-0">
                    <!-- Fila 1: Volver (10%) + Título (90%) -->
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div style="width: 20%; flex-shrink: 0;">
                            <a href="<?= url('/huespedes?reserva_id=' . $reserva_id) ?>" 
                               class="btn btn-outline-secondary btn-sm w-100"
                               style="min-width: auto;">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                        <div style="width: 80%;">
                            <h6 class="mb-0 text-secondary" style="font-size: 0.95rem; font-weight: 400;">
                                <?= isset($huesped['id_huesped']) ? 'Editar Huésped' : 'Nuevo Huésped' ?>
                            </h6>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form id="formHuesped" method="POST" novalidate>
                        <input type="hidden" name="id_huesped" value="<?= $huesped['id_huesped'] ?? '' ?>">
                        <input type="hidden" name="rela_reserva" value="<?= $reserva_id ?>">
                        
                        <!-- Información Personal -->
                        <h6 class="border-bottom pb-2 mb-3">Información Personal</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="persona_dni" class="form-label required">DNI</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="persona_dni" 
                                       name="persona_dni" 
                                       value="<?= htmlspecialchars($huesped['persona_dni'] ?? '') ?>"
                                       required 
                                       maxlength="8" 
                                       pattern="[0-9]{7,8}">
                                <div class="invalid-feedback">El DNI es obligatorio (7-8 dígitos)</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="persona_fechanac" class="form-label required">Fecha de Nacimiento</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="persona_fechanac" 
                                       name="persona_fechanac" 
                                       value="<?= isset($huesped['persona_fechanac']) ? date('Y-m-d', strtotime($huesped['persona_fechanac'])) : '' ?>"
                                       required 
                                       max="<?= date('Y-m-d') ?>">
                                <div class="invalid-feedback">La fecha de nacimiento es obligatoria</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="persona_nombre" class="form-label required">Nombre</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="persona_nombre" 
                                       name="persona_nombre" 
                                       value="<?= htmlspecialchars($huesped['persona_nombre'] ?? '') ?>"
                                       required 
                                       maxlength="45">
                                <div class="invalid-feedback">El nombre es obligatorio</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="persona_apellido" class="form-label required">Apellido</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="persona_apellido" 
                                       name="persona_apellido" 
                                       value="<?= htmlspecialchars($huesped['persona_apellido'] ?? '') ?>"
                                       required 
                                       maxlength="45">
                                <div class="invalid-feedback">El apellido es obligatorio</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="persona_direccion" class="form-label required">Dirección</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="persona_direccion" 
                                       name="persona_direccion" 
                                       value="<?= htmlspecialchars($huesped['persona_direccion'] ?? '') ?>"
                                       required 
                                       maxlength="45">
                                <div class="invalid-feedback">La dirección es obligatoria</div>
                            </div>
                        </div>
                        
                        <!-- Condiciones de Salud -->
                        <h6 class="border-bottom pb-2 mb-3 mt-4">Condiciones de Salud</h6>
                        
                        <div class="mb-3">
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($condicionesSalud as $condicion): ?>
                                    <?php 
                                    $isSelected = isset($condicionesSeleccionadas) && in_array($condicion['id_condicionsalud'], $condicionesSeleccionadas);
                                    ?>
                                    <label class="badge-condicion <?= $isSelected ? 'seleccionado-salud' : 'no-seleccionado' ?>">
                                        <input type="checkbox" 
                                               name="condiciones_salud[]" 
                                               value="<?= $condicion['id_condicionsalud'] ?>"
                                               <?= $isSelected ? 'checked' : '' ?>
                                               style="display: none;">
                                        <?= htmlspecialchars($condicion['condicionsalud_descripcion']) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <small class="form-text text-muted d-block mt-2">
                                Haga clic en las condiciones de salud que apliquen al huésped
                            </small>
                        </div>
                        
                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="submit" class="btn btn-outline-success">
                                <i class="fas fa-save me-2"></i>Guardar
                            </button>
                            <a href="<?= url('/huespedes?reserva_id=' . $reserva_id) ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .badge-condicion {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 500;
        margin: 0.25rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-block;
    }
    
    .badge-condicion.no-seleccionado {
        background-color: #e9ecef;
        color: #6c757d;
    }
    
    .badge-condicion.seleccionado-salud {
        background-color: #28a745;
        color: white;
    }
    
    .badge-condicion:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    .required::after {
        content: " *";
        color: #dc3545;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Autocompletar datos al ingresar DNI
    const dniInput = document.getElementById('persona_dni');
    let timeoutId;
    
    dniInput.addEventListener('input', function() {
        clearTimeout(timeoutId);
        const dni = this.value.trim();
        
        // Solo buscar si el DNI tiene 7 u 8 dígitos
        if (dni.length >= 7 && dni.length <= 8 && /^\d+$/.test(dni)) {
            timeoutId = setTimeout(() => {
                buscarPersonaPorDNI(dni);
            }, 500); // Esperar 500ms después de que el usuario deje de escribir
        }
    });
    
    function buscarPersonaPorDNI(dni) {
        fetch(`<?= url('/huespedes/buscar-por-dni') ?>?dni=${dni}&reserva_id=<?= $reserva_id ?>`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.huesped) {
                    // Mostrar confirmación para asociar huésped existente
                    Swal.fire({
                        title: '¡Huésped encontrado!',
                        html: `<p>Se encontró un huésped registrado con este DNI:</p>
                               <p><strong>${data.huesped.persona_nombre} ${data.huesped.persona_apellido}</strong></p>
                               <p>¿Desea agregar este huésped a la reserva?</p>`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, agregar a reserva',
                        cancelButtonText: 'No, crear nuevo huésped',
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            asociarHuespedExistente(data.huesped.id_huesped);
                        } else {
                            // Permitir continuar con el formulario para crear nuevo huésped
                            dniInput.focus();
                        }
                    });
                } else if (!data.success && data.message) {
                    // Mostrar mensaje de error (ej: huésped ya asociado)
                    Swal.fire({
                        title: 'Información',
                        text: data.message,
                        icon: 'info',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#007bff'
                    }).then(() => {
                        // Limpiar el campo DNI y permitir ingresar otro
                        dniInput.value = '';
                        dniInput.focus();
                    });
                }
            })
            .catch(error => {
                console.error('Error al buscar persona:', error);
            });
    }
    
    function asociarHuespedExistente(huespedId) {
        const formData = new FormData();
        formData.append('huesped_id', huespedId);
        formData.append('reserva_id', '<?= $reserva_id ?>');
        
        fetch('<?= url('/huespedes/asociar-existente') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: '¡Éxito!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '<?= url('/huespedes?reserva_id=' . $reserva_id) ?>';
                });
            } else {
                Swal.fire('Error', data.message || 'Error al asociar el huésped', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Error al procesar la solicitud', 'error');
        });
    }
    
    function autocompletarDatos(persona) {
        // Completar campos del formulario
        document.getElementById('persona_nombre').value = persona.persona_nombre || '';
        document.getElementById('persona_apellido').value = persona.persona_apellido || '';
        document.getElementById('persona_direccion').value = persona.persona_direccion || '';
        
        if (persona.persona_fechanac) {
            // Convertir fecha al formato YYYY-MM-DD
            const fecha = new Date(persona.persona_fechanac);
            const fechaFormato = fecha.toISOString().split('T')[0];
            document.getElementById('persona_fechanac').value = fechaFormato;
        }
        
        // Marcar campos como válidos
        document.getElementById('persona_nombre').classList.add('is-valid');
        document.getElementById('persona_apellido').classList.add('is-valid');
        document.getElementById('persona_direccion').classList.add('is-valid');
        document.getElementById('persona_fechanac').classList.add('is-valid');
    }
    
    // Manejo de badges de condiciones de salud
    const badges = document.querySelectorAll('.badge-condicion');
    
    badges.forEach(function(badge) {
        badge.addEventListener('click', function() {
            const checkbox = this.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            
            if (checkbox.checked) {
                this.classList.remove('no-seleccionado');
                this.classList.add('seleccionado-salud');
            } else {
                this.classList.remove('seleccionado-salud');
                this.classList.add('no-seleccionado');
            }
        });
    });
    
    // Validación del formulario
    const form = document.getElementById('formHuesped');
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        event.stopPropagation();
        
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        
        // Enviar formulario por AJAX
        const formData = new FormData(form);
        const isEdit = formData.get('id_huesped') !== '';
        
        fetch(form.action || window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: '¡Éxito!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '<?= url('/huespedes?reserva_id=' . $reserva_id) ?>';
                });
            } else {
                Swal.fire('Error', data.message || 'Error al guardar el huésped', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Error al procesar la solicitud', 'error');
        });
    });
});
</script>
