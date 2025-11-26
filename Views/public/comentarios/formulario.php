<?php
// Los datos ya vienen preparados desde el controlador
// Variables disponibles:
// $comentario - datos del comentario (si es edición) o null (si es nuevo)
// $reserva_info - información de la reserva (si aplica)
// $isEdit - boolean indicando si es edición
// $error_message - mensaje de error (si aplica)

if (isset($error_message)) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
    exit;
}

$pageTitle = $isEdit ? 'Editar Comentario' : 'Nuevo Comentario';
$actionUrl = $isEdit ? url("/comentarios/{$comentario['id_comentario']}/update") : url("/comentarios/store");
?>

<div class="container-fluid my-5">
    <div class="row justify-content-center">
        <div class="col-12">
            
            <!-- Card Principal -->
            <div class="card border-0 shadow-lg">
                <!-- Header con diseño estandarizado -->
                <div class="card-header bg-white border-bottom-0">
                    <!-- Fila 1: Volver (10%) + Título (90%) -->
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div style="width: 20%; flex-shrink: 0;">
                            <a href="<?= url('/comentarios') ?>" 
                               class="btn btn-outline-secondary btn-sm w-100" 
                               style="min-width: auto;">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                        <div style="width: 80%;">
                            <h6 class="mb-0 text-secondary" style="font-size: 0.95rem; font-weight: 400;">
                                <?= $pageTitle ?>
                            </h6>
                        </div>
                    </div>
                </div>
                
                <!-- Body -->
                <div class="card-body p-4">
                    
                    <?php if ($comentario && isset($comentario['cabania_nombre'])): ?>
                        <!-- Información de la Reserva -->
                        <div class="alert alert-info border-0 mb-4">
                            <h5 class="alert-heading mb-3">
                                <i class="fas fa-info-circle me-2"></i>Información de tu Estadía
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <strong><i class="fas fa-home me-2"></i>Cabaña:</strong><br>
                                    <?= htmlspecialchars($comentario['cabania_nombre']) ?>
                                </div>
                                <?php if (isset($comentario['reserva_fhinicio'])): ?>
                                    <div class="col-md-4">
                                        <strong><i class="fas fa-calendar-alt me-2"></i>Check-in:</strong><br>
                                        <?= date('d/m/Y', strtotime($comentario['reserva_fhinicio'])) ?>
                                    </div>
                                    <div class="col-md-4">
                                        <strong><i class="fas fa-calendar-check me-2"></i>Check-out:</strong><br>
                                        <?= date('d/m/Y', strtotime($comentario['reserva_fhfin'])) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($isEdit && isset($comentario['comentario_fechahora'])): ?>
                                    <div class="col-md-12 mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            Comentario creado el <?= date('d/m/Y H:i', strtotime($comentario['comentario_fechahora'])) ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Formulario -->
                    <form method="POST" action="<?= $actionUrl ?>" class="needs-validation" novalidate>
                        
                        <!-- Calificación -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-star me-2 text-warning"></i>
                                ¿Cómo calificarías tu estadía?
                                <span class="text-danger">*</span>
                            </label>
                            <div class="rating-stars-selector">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" 
                                           id="star<?= $i ?>" 
                                           name="puntuacion" 
                                           value="<?= $i ?>"
                                           <?= ($isEdit && $comentario['comentario_puntuacion'] == $i) || (!$isEdit && $i == 5) ? 'checked' : '' ?>
                                           required>
                                    <label for="star<?= $i ?>" title="<?= $i ?> estrella<?= $i > 1 ? 's' : '' ?>">
                                        <i class="fas fa-star"></i>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <!-- Comentario -->
                        <div class="mb-4">
                            <label for="comentario_texto" class="form-label fw-bold">
                                <i class="fas fa-comment-dots me-2 text-info"></i>
                                Cuéntanos sobre tu experiencia
                                <span class="text-danger">*</span>
                            </label>
                            <textarea id="comentario_texto" 
                                      name="comentario_texto" 
                                      class="form-control" 
                                      rows="6" 
                                      maxlength="400" 
                                      placeholder="Comparte los detalles de tu estadía, qué te gustó, qué destacarías..." 
                                      required><?= $isEdit ? htmlspecialchars($comentario['comentario_texto']) : '' ?></textarea>
                            <div class="form-text text-end">
                                <span id="contador"><?= $isEdit ? strlen($comentario['comentario_texto']) : 0 ?></span>/400 caracteres
                            </div>
                        </div>
                        
                        <!-- Campos ocultos -->
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_comentario" value="<?= $comentario['id_comentario'] ?>">
                            <input type="hidden" name="id_reserva" value="<?= $comentario['rela_reserva'] ?>">
                            <input type="hidden" name="id_huesped" value="<?= $comentario['rela_huesped'] ?>">
                        <?php else: ?>
                            <?php if (isset($reserva_id) && $reserva_id): ?>
                                <input type="hidden" name="id_reserva" value="<?= $reserva_id ?>">
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Botones de Acción -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-outline-success">
                                <i class="fas fa-save me-2"></i>
                                Guardar
                            </button>
                            
                            <a href="<?= url('/comentarios') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancelar
                            </a>
                        </div>
                    </form>
                    
                </div>
            </div>
            
        </div>
    </div>
</div>

<style>
.rating-stars-selector {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 5px;
}

.rating-stars-selector input[type="radio"] {
    display: none;
}

.rating-stars-selector label {
    font-size: 2rem;
    color: #ddd;
    cursor: pointer;
    transition: all 0.2s ease;
}

.rating-stars-selector label:hover,
.rating-stars-selector label:hover ~ label,
.rating-stars-selector input[type="radio"]:checked ~ label,
.rating-stars-selector input[type="radio"]:checked + label {
    color: #ffc107;
    transform: scale(1.1);
}
</style>

<script>
// Contador de caracteres
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('comentario_texto');
    const contador = document.getElementById('contador');
    
    if (textarea && contador) {
        textarea.addEventListener('input', function() {
            contador.textContent = this.value.length;
        });
    }
    
    // Validación Bootstrap
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
});