<?php
$title = 'Gestión de Reservas';
$currentModule = 'reservas';

require_once 'app/Views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Reservas</h2>
        <div>
            <a href="/reservas/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Reserva
            </a>
            <a href="/reservas/stats" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Estadísticas
            </a>
        </div>
    </div>

    <!-- Filtros de búsqueda -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filtros de búsqueda</h5>
        </div>
        <div class="card-body">
            <form method="GET" id="searchForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fecha_inicio">Fecha inicio:</label>
                            <input type="datetime-local" 
                                   class="form-control" 
                                   id="fecha_inicio" 
                                   name="fecha_inicio" 
                                   value="<?= htmlspecialchars($filters['fecha_inicio'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fecha_fin">Fecha fin:</label>
                            <input type="datetime-local" 
                                   class="form-control" 
                                   id="fecha_fin" 
                                   name="fecha_fin" 
                                   value="<?= htmlspecialchars($filters['fecha_fin'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cabania">Cabaña:</label>
                            <select class="form-control" id="cabania" name="cabania">
                                <option value="">Todas las cabañas</option>
                                <?php foreach ($cabanias as $cabania): ?>
                                    <option value="<?= $cabania['id_cabania'] ?>" 
                                            <?= ($filters['cabania'] ?? '') == $cabania['id_cabania'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cabania['cabania_codigo'] . ' - ' . $cabania['cabania_nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="estado">Estado:</label>
                            <select class="form-control" id="estado" name="estado">
                                <option value="">Todos los estados</option>
                                <?php 
                                $estados = ['pendiente' => 'Pendiente', 'confirmada' => 'Confirmada', 'cancelada' => 'Cancelada', 'finalizada' => 'Finalizada'];
                                foreach ($estados as $value => $text): ?>
                                    <option value="<?= $value ?>" 
                                            <?= ($filters['estado'] ?? '') === $value ? 'selected' : '' ?>>
                                        <?= $text ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <button type="button" class="btn btn-secondary" data-action="limpiar-filtros">
                                <i class="fas fa-times"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Selector de registros por página -->
    <div class="mb-3">
        <form method="GET" style="display: inline;">
            <!-- Mantener filtros existentes -->
            <?php foreach ($filters as $key => $value): ?>
                <?php if (!empty($value)): ?>
                    <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($value) ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            
            <label for="per_page">Mostrar:</label>
            <select name="per_page" id="per_page" class="form-control d-inline-block" style="width: auto;" data-auto-submit>
                <option value="10" <?= ($pagination['per_page'] ?? 10) == 10 ? 'selected' : '' ?>>10 registros</option>
                <option value="25" <?= ($pagination['per_page'] ?? 10) == 25 ? 'selected' : '' ?>>25 registros</option>
                <option value="50" <?= ($pagination['per_page'] ?? 10) == 50 ? 'selected' : '' ?>>50 registros</option>
            </select>
        </form>
    </div>

    <!-- Mensajes -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Información de registros -->
    <?php if (!empty($reservas)): ?>
        <div class="pagination-info mb-3">
            <small class="text-muted">
                Mostrando registros <?= $pagination['start'] ?> al <?= $pagination['end'] ?> 
                de <?= $pagination['total'] ?> registros encontrados
            </small>
        </div>
    <?php endif; ?>

    <!-- Tabla de reservas -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($reservas)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No se encontraron reservas</h4>
                    <p class="text-muted mb-4">No hay reservas que coincidan con los filtros aplicados.</p>
                    <a href="/reservas/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Primera Reserva
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Cabaña</th>
                                <th>Huésped</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $reserva): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($reserva['reserva_fechainicio'])) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($reserva['reserva_fechafin'])) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($reserva['cabania_nombre']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($reserva['cabania_codigo']) ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($reserva['persona_nombre'] . ' ' . $reserva['persona_apellido']) ?>
                                        <br>
                                        <small class="text-muted">DNI: <?= $reserva['persona_dni'] ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $estadoClass = [
                                            'pendiente' => 'warning',
                                            'confirmada' => 'success',
                                            'cancelada' => 'danger',
                                            'finalizada' => 'secondary'
                                        ];
                                        $estado = $reserva['estadoreserva_descripcion'] ?? 'desconocido';
                                        $class = $estadoClass[strtolower($estado)] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $class ?>">
                                            <?= ucfirst($estado) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="/reservas/<?= $reserva['id_reserva'] ?>" 
                                               class="btn btn-sm btn-info" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/reservas/<?= $reserva['id_reserva'] ?>/edit" 
                                               class="btn btn-sm btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <?php if ($reserva['rela_estadoreserva'] == 6): // Estado anulada ?>
                                                <?php if (isset($user_permissions['admin']) && $user_permissions['admin']): ?>
                                                    <a href="/reservas/<?= $reserva['id_reserva'] ?>/restore" 
                                                       class="btn btn-sm btn-success" title="Reactivar"
                                                       data-confirm-action="reactivar-reserva">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="/reservas/<?= $reserva['id_reserva'] ?>/cancel" 
                                                   class="btn btn-sm btn-danger" title="Anular"
                                                   data-confirm-action="anular-reserva">
                                                    <i class="fas fa-ban"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <small class="text-muted">
                                Página <?= $pagination['current_page'] ?> de <?= $pagination['total_pages'] ?>
                            </small>
                        </div>
                        <nav aria-label="Paginación de reservas">
                            <ul class="pagination pagination-sm mb-0">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $pagination['base_url'] ?>?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] - 1])) ?>">
                                            Anterior
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                    <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= $pagination['base_url'] ?>?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $pagination['base_url'] ?>?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] + 1])) ?>">
                                            Siguiente
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="<?= asset('assets/js/main.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initReservas();
    
    // Limpiar filtros
    document.querySelector('[data-action="limpiar-filtros"]')?.addEventListener('click', function() {
        limpiarFormularioReservas(document.getElementById('searchForm'));
    });
    
    // Auto-submit para selector de página
    document.querySelector('[data-auto-submit]')?.addEventListener('change', function() {
        autoSubmitPaginacion(this);
    });
});
</script>

<?php require_once 'app/Views/layouts/footer.php'; ?>