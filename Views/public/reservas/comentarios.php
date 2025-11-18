<?php
$pageTitle = 'Comentarios de la Reserva';
$pageStyles = ['public.css'];
require_once __DIR__ . '/../../shared/layouts/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/reservas') ?>">Mis Reservas</a></li>
                    <li class="breadcrumb-item active">Comentarios</li>
                </ol>
            </nav>
            
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-comments text-primary me-2"></i>
                    Comentarios de la Reserva
                </h2>
                <a href="<?= url('/reservas') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver
                </a>
            </div>
            
            <!-- Información de la reserva -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información de la Reserva</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Cabaña:</strong> <?= htmlspecialchars($reserva['cabania_nombre'] ?? 'No disponible') ?>
                        </div>
                        <div class="col-md-3 mb-3">
                            <strong>Entrada:</strong> <?= date('d/m/Y', strtotime($reserva['reserva_fhinicio'])) ?>
                        </div>
                        <div class="col-md-3 mb-3">
                            <strong>Salida:</strong> <?= date('d/m/Y', strtotime($reserva['reserva_fhfin'])) ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Columna: Comentarios existentes -->
                <div class="col-lg-<?= $ya_comentado ? '12' : '8' ?>">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                Comentarios (<?= count($comentarios) ?>)
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($comentarios)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No hay comentarios</h5>
                                    <p class="text-muted">Aún no has dejado ningún comentario sobre esta reserva.</p>
                                    <?php if (!$ya_comentado): ?>
                                        <p class="small text-muted">Utiliza el formulario de la derecha para agregar tu primer comentario.</p>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <?php foreach ($comentarios as $comentario): ?>
                                    <div class="card mb-3 border-start border-primary border-4">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <i class="fas fa-user-circle text-primary me-2"></i>
                                                        <?= htmlspecialchars($comentario['persona_nombre'] . ' ' . $comentario['persona_apellido']) ?>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?= date('d/m/Y H:i', strtotime($comentario['comentario_fechahora'])) ?>
                                                    </small>
                                                </div>
                                                <div>
                                                    <?php if (!empty($comentario['comentario_puntuacion'])): ?>
                                                        <div class="text-warning">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <i class="fas fa-star<?= $i <= $comentario['comentario_puntuacion'] ? '' : '-o' ?>"></i>
                                                            <?php endfor; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <?php if (!empty($comentario['comentario_titulo'])): ?>
                                                <h6 class="mt-3"><?= htmlspecialchars($comentario['comentario_titulo']) ?></h6>
                                            <?php endif; ?>
                                            
                                            <p class="mb-0"><?= nl2br(htmlspecialchars($comentario['comentario_texto'])) ?></p>
                                            
                                            <?php if ($comentario['comentario_estado'] == 0): ?>
                                                <div class="alert alert-warning mt-3 mb-0 small">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Este comentario está pendiente de moderación.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Columna: Nuevo comentario (solo si no ha comentado) -->
                <?php if (!$ya_comentado): ?>
                <div class="col-lg-4">
                    <div class="card shadow">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-plus-circle me-2"></i>
                                Nuevo Comentario
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= url('/comentarios/store') ?>">
                                <input type="hidden" name="id_reserva" value="<?= $reserva['id_reserva'] ?>">
                                
                                <div class="mb-3">
                                    <label for="comentario_titulo" class="form-label">Título</label>
                                    <input type="text" name="comentario_titulo" id="comentario_titulo" 
                                           class="form-control form-control-sm" 
                                           placeholder="Breve resumen de tu experiencia" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="comentario_puntuacion" class="form-label">Puntuación</label>
                                    <select name="comentario_puntuacion" id="comentario_puntuacion" 
                                            class="form-select form-select-sm" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="1">⭐ 1 - Muy malo</option>
                                        <option value="2">⭐⭐ 2 - Malo</option>
                                        <option value="3">⭐⭐⭐ 3 - Regular</option>
                                        <option value="4">⭐⭐⭐⭐ 4 - Bueno</option>
                                        <option value="5" selected>⭐⭐⭐⭐⭐ 5 - Excelente</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="comentario_texto" class="form-label">Comentario</label>
                                    <textarea name="comentario_texto" id="comentario_texto" 
                                              class="form-control form-control-sm" 
                                              rows="6" 
                                              placeholder="Comparte tu experiencia sobre tu estadía..." required></textarea>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-paper-plane me-2"></i>Enviar Comentario
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Nota informativa -->
                    <div class="alert alert-info mt-3">
                        <h6><i class="fas fa-info-circle me-2"></i>Importante</h6>
                        <p class="mb-0 small">
                            Tu comentario será revisado por el equipo de administración antes de ser publicado. 
                            Recibirás una notificación cuando sea aprobado.
                        </p>
                    </div>
                </div>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-success mt-3">
                            <h6><i class="fas fa-check-circle me-2"></i>Ya has comentado</h6>
                            <p class="mb-0 small">
                                Gracias por compartir tu experiencia. Ya has dejado un comentario sobre esta reserva.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../shared/layouts/footer.php'; ?>
