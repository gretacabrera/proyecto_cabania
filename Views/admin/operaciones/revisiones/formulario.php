<?php
$isEdit = isset($isEdit) && $isEdit === true;
?>

<div class="content-wrapper">
    <!-- Acciones principales -->
    <div class="page-actions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= url('/revisiones') ?>" class="btn btn-primary">
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
                        <i class="fas fa-clipboard-check"></i> 
                        <?= $isEdit ? 'Modificar revisión' : 'Registrar nueva revisión' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formRevision" method="POST" 
                          action="<?= $isEdit ? url('/revisiones/' . $reserva['id_reserva'] . '/edit') : url('/revisiones/create') ?>" 
                          novalidate>
                        
                        <!-- Selección de Reserva -->
                        <?php if (!$isEdit): ?>
                            <div class="form-group">
                                <label for="reserva_id" class="required">
                                    <i class="fas fa-calendar-check"></i> Reserva Pendiente de Revisión
                                </label>
                                <select class="form-select" id="reserva_id" name="reserva_id" required>
                                    <option value="">Seleccione una reserva...</option>
                                    <?php foreach ($reservas as $res): ?>
                                        <option value="<?= $res['id_reserva'] ?>">
                                            Reserva #<?= $res['id_reserva'] ?> - 
                                            <?= date('d/m/Y', strtotime($res['reserva_fhinicio'])) ?> a 
                                            <?= date('d/m/Y', strtotime($res['reserva_fhfin'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Por favor seleccione una reserva</div>
                            </div>

                            <!-- Información de la cabaña (se carga dinámicamente) -->
                            <div id="info-cabania" class="alert alert-info d-none mt-3">
                                <h6><i class="fas fa-home"></i> Cabaña: <span id="nombre-cabania"></span></h6>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="reserva_id" value="<?= $reserva['id_reserva'] ?>">
                            
                            <div class="alert alert-info">
                                <h6><i class="fas fa-calendar-check"></i> Reserva #<?= $reserva['id_reserva'] ?></h6>
                                <p class="mb-0">
                                    <strong>Fecha:</strong> 
                                    <?= date('d/m/Y', strtotime($reserva['reserva_fhinicio'])) ?> a 
                                    <?= date('d/m/Y', strtotime($reserva['reserva_fhfin'])) ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- Lista de inventarios a revisar -->
                        <div id="lista-inventarios" class="<?= !$isEdit ? 'd-none' : '' ?>">
                            <h6 class="mt-4 mb-3">
                                <i class="fas fa-list-check"></i> Elementos a Revisar
                            </h6>

                            <?php if ($isEdit): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>Modo Edición:</strong> Puede modificar los niveles de daño o agregar nuevos elementos dañados que no se registraron en la revisión inicial.
                                </div>
                            <?php endif; ?>

                            <div id="inventarios-container">
                                <?php if (isset($inventarios)): ?>
                                    <?php if (empty($inventarios)): ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> No hay elementos en el inventario de esta cabaña.
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($inventarios as $inv): ?>
                                            <?php
                                            // Buscar si este elemento ya tiene revisión registrada
                                            $revisionExistente = null;
                                            if (isset($revisiones)) {
                                                foreach ($revisiones as $rev) {
                                                    if ($rev['rela_inventariocabania'] == $inv['id_inventariocabania']) {
                                                        $revisionExistente = $rev;
                                                        break;
                                                    }
                                                }
                                            }
                                            ?>
                                            <div class="card mb-2">
                                                <div class="card-body p-3">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-6">
                                                            <label class="form-label mb-0">
                                                                <i class="fas fa-box"></i> 
                                                                <?= htmlspecialchars($inv['inventario_descripcion']) ?>
                                                                <?php if ($revisionExistente): ?>
                                                                    <span class="badge bg-warning text-dark ms-2">
                                                                        <i class="fas fa-exclamation-triangle"></i> Con daño
                                                                    </span>
                                                                <?php endif; ?>
                                                            </label>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <select class="form-select form-select-sm nivel-danio" 
                                                                    name="inventarios[<?= $inv['id_inventariocabania'] ?>]" 
                                                                    data-inventario="<?= $inv['id_inventario'] ?>"
                                                                    data-inventariocabania="<?= $inv['id_inventariocabania'] ?>">
                                                                <option value="0">Sin daño</option>
                                                                <?php foreach ($nivelesDanio as $nivel): ?>
                                                                    <option value="<?= $nivel['id_niveldanio'] ?>"
                                                                        <?= ($revisionExistente && isset($revisionExistente['nivel_danio_id']) && $revisionExistente['nivel_danio_id'] == $nivel['id_niveldanio']) ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($nivel['niveldanio_descripcion']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2 text-end">
                                                            <span class="costo-elemento" 
                                                                  data-costo="<?= $revisionExistente ? $revisionExistente['revision_costo'] : 0 ?>">
                                                                $<span class="costo-value"><?= $revisionExistente ? number_format($revisionExistente['revision_costo'], 2) : '0.00' ?></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Total de costos -->
                            <div class="card bg-light mt-3">
                                <div class="card-body p-3">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="mb-0"><i class="fas fa-calculator"></i> Total Costos por Daños</h6>
                                        </div>
                                        <div class="col-auto">
                                            <h4 class="mb-0 text-danger">
                                                $<span id="total-costos">0.00</span>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="form-group mt-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success" id="btnGuardar">
                                    <i class="fas fa-save"></i> Guardar Revisión
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-eraser"></i> Limpiar
                                </button>
                                <a href="<?= url('/revisiones') ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
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
                                <li>• Modifique el nivel de daño según sea necesario</li>
                                <li>• Agregue elementos dañados no registrados inicialmente</li>
                                <li>• La etiqueta "Con daño" indica elementos ya revisados</li>
                                <li>• Los costos se actualizan automáticamente</li>
                            <?php else: ?>
                                <li>• Seleccione una reserva pendiente de revisión</li>
                                <li>• Revise cuidadosamente cada elemento del inventario</li>
                                <li>• Indique el nivel de daño según corresponda</li>
                                <li>• Los costos se calculan automáticamente</li>
                                <li>• Solo se guardan elementos con daño registrado</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const formRevision = document.getElementById('formRevision');
    const reservaSelect = document.getElementById('reserva_id');
    const listaInventarios = document.getElementById('lista-inventarios');
    const inventariosContainer = document.getElementById('inventarios-container');
    const infoCabania = document.getElementById('info-cabania');
    const nombreCabania = document.getElementById('nombre-cabania');
    const totalCostosElement = document.getElementById('total-costos');

    // Cargar inventarios cuando se selecciona una reserva
    if (reservaSelect) {
        reservaSelect.addEventListener('change', function() {
            const reservaId = this.value;

            if (!reservaId) {
                listaInventarios.classList.add('d-none');
                infoCabania.classList.add('d-none');
                return;
            }

            // Realizar petición AJAX para obtener inventarios
            fetch('<?= url('/revisiones/get-inventarios-cabania') ?>?reserva_id=' + reservaId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Limpiar contenedor
                        inventariosContainer.innerHTML = '';

                        // Agregar cada inventario
                        data.inventarios.forEach(inv => {
                            const card = crearCardInventario(inv);
                            inventariosContainer.appendChild(card);
                        });

                        // Mostrar lista
                        listaInventarios.classList.remove('d-none');
                        
                        // Actualizar listeners
                        actualizarListenersNivelDanio();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'No se pudieron cargar los inventarios', 'error');
                });
        });
    }

    // Crear card de inventario
    function crearCardInventario(inv) {
        const card = document.createElement('div');
        card.className = 'card mb-2';
        
        card.innerHTML = `
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <label class="form-label mb-0">
                            <i class="fas fa-box"></i> ${inv.inventario_descripcion}
                        </label>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select form-select-sm nivel-danio" 
                                name="inventarios[${inv.id_inventariocabania}]" 
                                data-inventario="${inv.id_inventario}"
                                data-inventariocabania="${inv.id_inventariocabania}">
                            <option value="0">Sin daño</option>
                            <?php foreach ($nivelesDanio as $nivel): ?>
                                <option value="<?= $nivel['id_niveldanio'] ?>">
                                    <?= htmlspecialchars($nivel['niveldanio_descripcion']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        <span class="costo-elemento" data-costo="0">
                            $<span class="costo-value">0.00</span>
                        </span>
                    </div>
                </div>
            </div>
        `;
        
        return card;
    }

    // Actualizar listeners de nivel de daño
    function actualizarListenersNivelDanio() {
        const selectsNivelDanio = document.querySelectorAll('.nivel-danio');
        
        selectsNivelDanio.forEach(select => {
            select.addEventListener('change', function() {
                const inventarioId = this.dataset.inventario;
                const nivelDanioId = this.value;
                const costoElement = this.closest('.card-body').querySelector('.costo-elemento');

                if (nivelDanioId == 0) {
                    actualizarCosto(costoElement, 0);
                    calcularTotal();
                    return;
                }

                // Obtener costo vía AJAX
                fetch(`<?= url('/revisiones/get-costo-danio') ?>?inventario_id=${inventarioId}&nivel_danio_id=${nivelDanioId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            actualizarCosto(costoElement, data.costo);
                            calcularTotal();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });
        });
    }

    // Actualizar costo en badge
    function actualizarCosto(element, costo) {
        element.dataset.costo = costo;
        element.querySelector('.costo-value').textContent = parseFloat(costo).toFixed(2);
    }

    // Calcular total de costos
    function calcularTotal() {
        let total = 0;
        document.querySelectorAll('.costo-elemento').forEach(el => {
            total += parseFloat(el.dataset.costo || 0);
        });
        totalCostosElement.textContent = total.toFixed(2);
    }

    // Validación del formulario
    formRevision.addEventListener('submit', function(e) {
        if (!formRevision.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        formRevision.classList.add('was-validated');
    });

    // Inicializar listeners si ya hay inventarios
    actualizarListenersNivelDanio();
    calcularTotal();
});
</script>
