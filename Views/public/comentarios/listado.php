<?php
// Variables que recibimos del controlador:
// $comentarios - array con los comentarios
// $paginacion - información de paginación (null si es vista de reserva)
// $filtros_aplicados - filtros actualmente activos
// $reserva_id - ID de reserva si estamos en vista específica
// $reserva - datos de la reserva si estamos en vista específica
// $ya_comentado - si ya comentó en esta reserva
// $puede_crear_comentario - si puede crear comentario (solo última reserva)

$esVistaReserva = !empty($reserva_id);
$registros_por_pagina = $paginacion['registros_por_pagina'] ?? 10;
$pagina_actual = $paginacion['pagina_actual'] ?? 1;
?>

<!-- Estilos personalizados para mejorar el diseño -->
<style>
    .comentarios-header {
        padding: 2.5rem 0;
        margin-bottom: 2rem;
        border-radius: 0.75rem;
    }
    
    .comentario-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
        background: #fff;
    }
    
    .comentario-card:hover {
        border-left-color: #667eea;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transform: translateX(5px);
    }
    
    .rating-stars {
        color: #ffc107;
        font-size: 1.1rem;
    }
    
    .info-reserva-card {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border: none;
        border-radius: 0.75rem;
    }
    
    .formulario-comentario {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 0.75rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }
    
    .badge-estado {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 500;
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
    
    .filtros-container {
        background: #f8f9fa;
        border-radius: 0.75rem;
        padding: 1.5rem;
    }
    
    @media (max-width: 768px) {
        .comentarios-header {
            padding: 1rem 0 !important;
        }
        
        .comentarios-header h2 {
            font-size: 1.25rem !important;
        }
        
        .btn-accion {
            padding: 0.5rem 1rem !important;
        }
    }
    
    @media (max-width: 576px) {
        .comentarios-header h2 {
            font-size: 1.1rem !important;
        }
        
        .btn-accion {
            font-size: 0.9rem;
        }
        
        .card-body {
            padding: 1rem !important;
        }
    }
</style>

<div class="container-fluid my-5">
    <div class="row justify-content-center">
        <div class="col-12">
            
            <!-- Card Única Integrada -->
            <div class="card border-0 shadow-lg">
                <!-- Header sobrio -->
                <div class="comentarios-header" style="background: #f8f9fa; padding: 0.5rem 1rem; margin: 0 0 1rem 0; border-radius: 0; overflow: visible;">
                    <!-- Estructura estandarizada: Botón Volver + Título -->
                    <div class="d-flex align-items-center gap-2 mb-2" style="flex-wrap: nowrap;">
                        <!-- Botón Volver -->
                        <div style="flex-shrink: 0;">
                            <a href="<?= url('/mis-reservas') ?>" class="btn btn-link text-secondary p-1" title="Volver a Mis Reservas">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                        
                        <!-- Título -->
                        <div class="flex-grow-1">
                            <h6 class="mb-0 text-secondary" style="font-size: 0.95rem; font-weight: 400;">
                                <?php if ($esVistaReserva): ?>
                                    <span class="d-none d-md-inline">Comentarios #<?= htmlspecialchars($reserva_id) ?></span>
                                    <span class="d-inline d-md-none">Comentarios</span>
                                <?php else: ?>
                                    <span class="d-none d-sm-inline">Mis Comentarios</span>
                                    <span class="d-inline d-sm-none">Comentarios</span>
                                <?php endif; ?>
                            </h6>
                        </div>
                    </div>
                    
                    <!-- Fila de botones -->
                    <div class="d-flex gap-2">
                        <?php if ($esVistaReserva): ?>
                            <a href="<?= url('/comentarios') ?>" class="btn btn-sm btn-outline-secondary" style="min-width: 100px;">
                                <i class="fas fa-list me-1"></i>Todos
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($esVistaReserva && $puede_crear_comentario): ?>
                            <a href="<?= url('/comentarios/create?reserva_id=' . $reserva_id) ?>" class="btn btn-sm btn-outline-primary" style="min-width: 120px;">
                                <i class="fas fa-plus me-1"></i>Agregar
                            </a>
                        <?php elseif (!$esVistaReserva && isset($ultima_reserva) && $ultima_reserva): ?>
                            <a href="<?= url('/comentarios/create?reserva_id=' . $ultima_reserva['id_reserva']) ?>" class="btn btn-sm btn-outline-primary" style="min-width: 120px;">
                                <i class="fas fa-plus me-1"></i>Agregar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Cuerpo de la Card -->
                <div class="card-body p-4 pt-3">

            <?php if (empty($comentarios)): ?>
                <!-- Estado Vacío Mejorado -->
                <div class="estado-vacio text-center shadow-sm">
                    <i class="fas fa-comment-slash fa-4x text-secondary mb-4 opacity-50"></i>
                    <h4 class="text-muted mb-3">No hay comentarios aún</h4>
                    <?php if ($esVistaReserva): ?>
                        <p class="text-muted mb-4">Esta reserva aún no tiene comentarios.</p>
                    <?php else: ?>
                        <p class="text-muted mb-4">Cuando dejes comentarios sobre tus estadías, aparecerán aquí.</p>
                        <a href="<?= url('/mis-reservas') ?>" class="btn btn-primary btn-accion">
                            <i class="fas fa-calendar-check me-2"></i>Ver Mis Reservas
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Lista de Comentarios con Diseño Atractivo -->
                <div class="row g-4">
                    <?php foreach ($comentarios as $comentario): ?>
                        <div class="col-12">
                            <div class="comentario-card p-4 rounded-3 shadow-sm">
                                <div class="row align-items-start">
                                    <!-- Primera columna: Información del usuario y comentario -->
                                    <div class="col-md-7">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 50px; height: 50px; font-size: 1.5rem; flex-shrink: 0;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 fw-bold">
                                                    <?= htmlspecialchars(($comentario['personafisica_nombre'] ?? '') . ' ' . ($comentario['personafisica_apellido'] ?? '')) ?>
                                                </h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?= date('d/m/Y H:i', strtotime($comentario['comentario_fechahora'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($comentario['comentario_titulo'])): ?>
                                            <h5 class="fw-bold mb-2 text-primary">
                                                <i class="fas fa-quote-left me-2" style="font-size: 0.8rem;"></i>
                                                <?= htmlspecialchars($comentario['comentario_titulo']) ?>
                                            </h5>
                                        <?php endif; ?>
                                        
                                        <p class="text-dark mb-0" style="line-height: 1.7;">
                                            <?= nl2br(htmlspecialchars($comentario['comentario_texto'])) ?>
                                        </p>
                                        
                                        <?php if ($comentario['comentario_estado'] == 0): ?>
                                            <div class="alert alert-warning border-0 shadow-sm mt-3 mb-0">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <small>Este comentario está siendo revisado por nuestro equipo.</small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Segunda columna: Puntuación -->
                                    <div class="col-md-2 text-center">
                                        <?php if (!empty($comentario['comentario_puntuacion'])): ?>
                                            <div class="rating-stars mb-2">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star<?= $i <= $comentario['comentario_puntuacion'] ? '' : '-o' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Tercera columna: Botones de acción -->
                                    <div class="col-md-3 text-end">
                                        <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">
                                            <a href="<?= url('/comentarios/' . $comentario['id_comentario'] . '/edit') ?>" 
                                               class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit me-1"></i>Editar
                                            </a>
                                            
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="eliminarComentario(<?= $comentario['id_comentario'] ?>)">
                                                <i class="fas fa-trash me-1"></i>Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!$esVistaReserva && $paginacion && $paginacion['total_paginas'] > 1): ?>
                <!-- Paginación Moderna -->
                <div class="mt-4">
                    <nav aria-label="Paginación">
                        <ul class="pagination justify-content-center">
                            <?php if ($paginacion['pagina_actual'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link rounded-pill me-2" 
                                       href="<?= url('/comentarios?pagina=' . ($paginacion['pagina_actual'] - 1) . '&registros_por_pagina=' . $registros_por_pagina) ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php 
                            $start_page = max(1, $paginacion['pagina_actual'] - 2);
                            $end_page = min($start_page + 4, $paginacion['total_paginas']);
                            
                            for ($i = $start_page; $i <= $end_page; $i++): 
                            ?>
                                <li class="page-item <?= $i == $paginacion['pagina_actual'] ? 'active' : '' ?>">
                                    <a class="page-link rounded-pill mx-1" 
                                       href="<?= url('/comentarios?pagina=' . $i . '&registros_por_pagina=' . $registros_por_pagina) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($paginacion['pagina_actual'] < $paginacion['total_paginas']): ?>
                                <li class="page-item">
                                    <a class="page-link rounded-pill ms-2" 
                                       href="<?= url('/comentarios?pagina=' . ($paginacion['pagina_actual'] + 1) . '&registros_por_pagina=' . $registros_por_pagina) ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
            
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación de formulario Bootstrap
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

// Animación de aparición de cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.comentario-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});

// Función para eliminar comentario con confirmación
function eliminarComentario(idComentario) {
    Swal.fire({
        title: '¿Eliminar comentario?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('/comentarios/') ?>' + idComentario + '/delete';
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>