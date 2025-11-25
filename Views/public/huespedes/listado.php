<!-- Estilos personalizados para la gestión de huéspedes -->
<style>
    .huespedes-header {
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 0.75rem;
    }
    
    .huesped-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
        background: #fff;
    }
    
    .huesped-card:hover {
        border-left-color: #667eea;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transform: translateX(5px);
    }
    
    .info-reserva-card {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border: none;
        border-radius: 0.75rem;
    }
    
    .badge-condicion {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 500;
        margin: 0.25rem;
        cursor: pointer;
        transition: all 0.3s ease;
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
    
    .btn-accion {
        border-radius: 50px;
        padding: 0.5rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-accion:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    .estado-vacio {
        padding: 5rem 2rem;
        background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
        border-radius: 0.75rem;
    }
    
    @media (max-width: 768px) {
        .huesped-card {
            margin-bottom: 1rem;
        }
        
        .btn-group-vertical {
            width: 100%;
        }
        
        .btn-group-vertical .btn {
            margin-bottom: 0.5rem;
        }
    }
</style>

<div class="container-fluid my-5">
    <div class="row justify-content-center">
        <div class="col-12">
            
            <!-- Card Única Integrada -->
            <div class="card border-0 shadow-lg">
                <!-- Header con Detalles Integrados -->
                <div class="huespedes-header py-3 px-4">
                    <!-- Botón Mis Reservas arriba -->
                    <div class="mb-3">
                        <a href="<?= url('/mis-reservas') ?>" class="btn btn-link text-primary text-decoration-none p-0">
                            <i class="fas fa-arrow-left me-2"></i>Mis Reservas
                        </a>
                    </div>
                    
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <!-- Título -->
                        <div>
                            <h2 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-users me-2"></i>
                                Huéspedes de la Reserva #<?= htmlspecialchars($reserva_id) ?>
                            </h2>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Total de huéspedes: <strong><?= count($huespedes) ?></strong>
                            </small>
                        </div>
                        
                        <!-- Botón para agregar nuevo huésped -->
                        <div>
                            <a href="<?= url('/huespedes/create?reserva_id=' . $reserva_id) ?>" class="btn btn-success btn-accion">
                                <i class="fas fa-user-plus me-2"></i>Agregar Huésped
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Cuerpo de la Card -->
                <div class="card-body p-4 pt-0">

            <?php if (empty($huespedes)): ?>
                <!-- Estado Vacío Mejorado -->
                <div class="estado-vacio text-center shadow-sm">
                    <i class="fas fa-user-slash fa-4x text-secondary mb-4 opacity-50"></i>
                    <h4 class="text-muted mb-3">No hay huéspedes registrados</h4>
                    <p class="text-muted mb-4">Agregue los huéspedes que se alojarán en esta reserva.</p>
                    <a href="<?= url('/huespedes/create?reserva_id=' . $reserva_id) ?>" class="btn btn-primary btn-accion">
                        <i class="fas fa-user-plus me-2"></i>Agregar Primer Huésped
                    </a>
                </div>
            <?php else: ?>
                <!-- Lista de Huéspedes con Diseño Atractivo -->
                <div class="row g-4">
                    <?php foreach ($huespedes as $huesped): ?>
                        <div class="col-12">
                            <div class="huesped-card p-4 rounded-3 shadow-sm">
                                <div class="row align-items-start">
                                    <!-- Columna 1: Información del huésped -->
                                    <div class="col-12 col-md-6 col-lg-7 mb-3 mb-md-0">
                                        <div class="mb-3">
                                            <p class="mb-2">
                                                <strong>Nombre:</strong> <?= htmlspecialchars($huesped['persona_nombre'] . ' ' . $huesped['persona_apellido']) ?>
                                            </p>
                                            <?php if (!empty($huesped['persona_dni'])): ?>
                                                <p class="mb-2">
                                                    <strong>DNI:</strong> <?= number_format($huesped['persona_dni'], 0, ',', '.') ?>
                                                </p>
                                            <?php endif; ?>
                                            <p class="mb-2">
                                                <strong>Fecha de Nacimiento:</strong> <?= date('d/m/Y', strtotime($huesped['persona_fechanac'])) ?>
                                            </p>
                                            <?php if (!empty($huesped['persona_direccion'])): ?>
                                                <p class="mb-2">
                                                    <strong>Dirección:</strong> <?= htmlspecialchars($huesped['persona_direccion']) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Columna 2: Condiciones de salud -->
                                    <div class="col-12 col-md-6 col-lg-3 mb-3 mb-lg-0">
                                        <p class="mb-2">
                                            <strong>Condiciones de Salud:</strong>
                                        </p>
                                        <?php if (!empty($huesped['condiciones'])): ?>
                                            <ul class="list-unstyled mb-0">
                                                <?php foreach ($huesped['condiciones'] as $condicion): ?>
                                                    <li class="mb-1">• <?= htmlspecialchars($condicion) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <small class="text-muted">Ninguna registrada</small>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Columna 3: Botones de acción -->
                                    <div class="col-12 col-lg-2 text-end">
                                        <div class="btn-group-vertical w-100" role="group">
                                            <a href="<?= url('/huespedes/' . $huesped['id_huesped'] . '/edit?reserva_id=' . $reserva_id) ?>" 
                                               class="btn btn-sm btn-warning mb-2">
                                                <i class="fas fa-edit me-1"></i>Editar
                                            </a>
                                            
                                            <?php if (!$huesped['tiene_usuario']): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger"
                                                        onclick="eliminarHuesped(<?= $huesped['id_huesped'] ?>)">
                                                    <i class="fas fa-trash me-1"></i>Eliminar
                                                </button>
                                            <?php else: ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-secondary"
                                                        disabled
                                                        title="No se puede eliminar porque tiene usuario asociado">
                                                    <i class="fas fa-lock me-1"></i>Protegido
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Función para eliminar huésped
function eliminarHuesped(idHuesped) {
    Swal.fire({
        title: '¿Eliminar huésped?',
        text: "Se eliminará el huésped de esta reserva",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const reservaId = new URLSearchParams(window.location.search).get('reserva_id');
            fetch(`<?= url('/huespedes/') ?>${idHuesped}/delete?reserva_id=${reservaId}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message || 'No se pudo eliminar el huésped', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error al eliminar el huésped', 'error');
            });
        }
    });
}
</script>

<style>
.required::after {
    content: " *";
    color: #dc3545;
}
</style>
