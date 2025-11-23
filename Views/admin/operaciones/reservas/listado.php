<?php
$pageTitle = $title ?? 'Gestión de Reservas';
$currentModule = 'reservas';
$pageStyles = ['admin.css', 'dashboard.css'];
require_once __DIR__ . '/../../../shared/layouts/header.php';
?>

<div class="container-fluid">
    <!-- Encabezado moderno similar al diseño de referencia -->
    <div class="card border-0 shadow-sm">
        <!-- Header oscuro -->
        <div class="card-header text-dark py-3 mb-0">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0">Gestión de Reservas</h4>
                </div>
                <div class="col-auto">
                    <a href="<?= url('/reservas/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Nueva Reserva
                    </a>
                </div>
            </div>
        </div>
        <!-- Filtros compactos -->
        <div class="card-body pb-0">
            <form method="GET" action="<?= url('/reservas') ?>" class="mb-3">
                <div class="row mb-2">
                    <div class="col-auto">
                        <label class="form-label small mb-1 text-muted">Filtros de búsqueda</label>
                    </div>
                </div>
                
                <!-- Primera fila: N° Reserva, Cabaña, Huésped, Estado -->
                <div class="row g-2 mb-2">
                    <div class="col-md-2">
                        <label class="form-label small mb-1">N° Reserva</label>
                        <input type="text" name="reserva_nro" class="form-control form-control-sm" 
                               placeholder="Nro" value="<?= htmlspecialchars($_GET['reserva_nro'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Cabaña</label>
                        <select name="cabania" class="form-select form-select-sm">
                            <option value="">Todas las cabañas</option>
                            <?php if (!empty($cabanias)): ?>
                                <?php foreach ($cabanias as $cabania): ?>
                                    <option value="<?= $cabania['id_cabania'] ?>" 
                                            <?= ($_GET['cabania'] ?? '') == $cabania['id_cabania'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cabania['cabania_nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Huésped</label>
                        <input type="text" name="persona" class="form-control form-control-sm" 
                               placeholder="Nombre o apellido" value="<?= htmlspecialchars($_GET['persona'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Estado</label>
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Todos los estados</option>
                            <?php if (!empty($estados_reserva)): ?>
                                <?php foreach ($estados_reserva as $estado): ?>
                                    <option value="<?= $estado['id_estadoreserva'] ?>" 
                                            <?= ($_GET['estado'] ?? '') == $estado['id_estadoreserva'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($estado['estadoreserva_descripcion']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Segunda fila: Fechas y botones -->
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Fecha Alta</label>
                        <input type="date" name="fecha_alta" class="form-control form-control-sm" 
                               value="<?= htmlspecialchars($_GET['fecha_alta'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control form-control-sm" 
                               value="<?= htmlspecialchars($_GET['fecha_inicio'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control form-control-sm" 
                               value="<?= htmlspecialchars($_GET['fecha_fin'] ?? '') ?>">
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary btn-sm" title="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?= url('/reservas') ?>" class="btn btn-info btn-sm" title="Limpiar filtros">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-auto">
                        <label class="form-label small mb-1 text-muted">Registros por página</label>
                    </div>
                    <div class="col-auto">
                        <select name="per_page" class="form-select form-select-sm" style="width: 80px;" 
                                onchange="this.form.submit()">
                            <option value="5" <?= ($_GET['per_page'] ?? '10') == '5' ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= ($_GET['per_page'] ?? '10') == '10' ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= ($_GET['per_page'] ?? '10') == '25' ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= ($_GET['per_page'] ?? '10') == '50' ? 'selected' : '' ?>>50</option>
                        </select>
                    </div>
                    <div class="col"></div> <!-- Espaciador para empujar el botón a la derecha -->
                    <div class="col-auto">
                        <div class="btn-group" role="group">
                            <button type="button" onclick="exportarReservas(event)" class="btn btn-success btn-sm" title="Exportar a Excel">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                            <button type="button" onclick="exportarReservasPDF(event)" class="btn btn-danger btn-sm" title="Exportar a PDF">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla estilo moderno -->
        <div class="card-body p-0">
            <?php if (empty($reservas)): ?>
                <div class="empty-state py-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-calendar-check fa-3x text-muted opacity-50"></i>
                    </div>
                    <h6 class="text-muted">No se encontraron reservas</h6>
                    <p class="text-muted small mb-3">Intenta modificar los filtros o crea una nueva reserva.</p>
                    <a href="<?= url('/reservas/create') ?>" class="btn btn-outline-dark btn-sm">
                        <i class="fas fa-plus fa-sm"></i> Crear reserva
                    </a>
                </div>
            <?php else: ?>
                <!-- Información de paginación y navegación superior -->
                <?php if (isset($pagination) && $pagination['total'] > 0): ?>
                    <?php 
                    $perPage = (int) ($_GET['per_page'] ?? 10);
                    $start = (($pagination['current_page'] - 1) * $perPage) + 1;
                    $end = min($pagination['current_page'] * $perPage, $pagination['total']);
                    
                    // Función para renderizar la paginación
                    $renderPagination = function($showInfo = true) use ($pagination, $start, $end) {
                    ?>
                        <div class="row align-items-center">
                            <?php if ($showInfo): ?>
                                <div class="col-sm-6">
                                    <span class="text-muted small">
                                        Mostrando <?= $start ?> a <?= $end ?> de <?= $pagination['total'] ?> registros
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="col-sm-<?= $showInfo ? '6' : '12' ?>">
                                <?php if ($pagination['total_pages'] > 1): ?>
                                    <nav aria-label="Paginación" class="d-flex justify-content-<?= $showInfo ? 'end' : 'center' ?>">
                                        <ul class="pagination pagination-sm mb-0">
                                            <?php if ($pagination['current_page'] > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) ?>">Anterior</a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <?php 
                                            $startPage = max(1, $pagination['current_page'] - 2);
                                            $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                            
                                            if ($startPage > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
                                                </li>
                                                <?php if ($startPage > 2): ?>
                                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            
                                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                                <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                                    <?php if ($i == $pagination['current_page']): ?>
                                                        <span class="page-link bg-primary text-white border-primary"><?= $i ?></span>
                                                    <?php else: ?>
                                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <?php if ($endPage < $pagination['total_pages']): ?>
                                                <?php if ($endPage < $pagination['total_pages'] - 1): ?>
                                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                                <?php endif; ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['total_pages']])) ?>"><?= $pagination['total_pages'] ?></a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])) ?>">Siguiente</a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php }; ?>
                    
                    <div class="card-header bg-light border-bottom py-2">
                        <?php $renderPagination(true); ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0 py-3">N° Reserva</th>
                                <th class="border-0 py-3">Fecha/Hora</th>
                                <th class="border-0 py-3">Cabaña</th>
                                <th class="border-0 py-3">Inicio</th>
                                <th class="border-0 py-3">Fin</th>
                                <th class="border-0 py-3">Estado</th>
                                <th class="border-0 py-3">Huésped</th>
                                <th class="border-0 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $reserva): ?>
                                <tr>
                                    <td class="border-0 py-3">
                                        <strong><?= htmlspecialchars($reserva['reserva_nro']) ?></strong>
                                    </td>
                                    <td class="border-0 py-3">
                                        <?= date('d/m/Y H:i', strtotime($reserva['reserva_fechahora'])) ?>
                                    </td>
                                    <td class="border-0 py-3">
                                        <?= htmlspecialchars($reserva['cabania_nombre'] ?? 'Sin cabaña') ?>
                                    </td>
                                    <td class="border-0 py-3">
                                        <?= date('d/m/Y H:i', strtotime($reserva['reserva_fhinicio'])) ?>
                                    </td>
                                    <td class="border-0 py-3">
                                        <?= date('d/m/Y H:i', strtotime($reserva['reserva_fhfin'])) ?>
                                    </td>
                                    <td class="border-0 py-3">
                                        <?php
                                        $estadoBadge = 'secondary';
                                        $estadoDescripcion = $reserva['estadoreserva_descripcion'] ?? 'Sin estado';
                                        
                                        if (stripos($estadoDescripcion, 'confirmada') !== false || stripos($estadoDescripcion, 'activa') !== false) {
                                            $estadoBadge = 'success';
                                        } elseif (stripos($estadoDescripcion, 'pendiente') !== false) {
                                            $estadoBadge = 'warning';
                                        } elseif (stripos($estadoDescripcion, 'cancelada') !== false) {
                                            $estadoBadge = 'danger';
                                        } elseif (stripos($estadoDescripcion, 'finalizada') !== false) {
                                            $estadoBadge = 'info';
                                        }
                                        ?>
                                        <span class="badge bg-<?= $estadoBadge ?>"><?= htmlspecialchars($estadoDescripcion) ?></span>
                                    </td>
                                    <td class="border-0 py-3">
                                        <?php
                                        $nombreCompleto = trim(($reserva['persona_nombre'] ?? '') . ' ' . ($reserva['persona_apellido'] ?? ''));
                                        echo htmlspecialchars($nombreCompleto ?: 'Sin datos');
                                        ?>
                                    </td>
                                    <td class="border-0 py-3 text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= url('/reservas/' . $reserva['id_reserva']) ?>" 
                                               class="btn btn-outline-primary" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= url('/reservas/' . $reserva['id_reserva'] . '/edit') ?>" 
                                               class="btn btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" onclick="anularReserva(<?= $reserva['id_reserva'] ?>)" 
                                                    class="btn btn-outline-danger" title="Anular reserva">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación inferior -->
                <?php if (isset($pagination) && $pagination['total'] > 0): ?>
                    <div class="card-footer bg-white border-top py-3">
                        <?php $renderPagination(true); ?>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function exportarReservas(event) {
    event.preventDefault();
    const params = new URLSearchParams(window.location.search);
    window.location.href = '<?= url('/reservas/exportar') ?>?' + params.toString();
}

function exportarReservasPDF(event) {
    event.preventDefault();
    const params = new URLSearchParams(window.location.search);
    window.location.href = '<?= url('/reservas/exportar-pdf') ?>?' + params.toString();
}

function anularReserva(id) {
    Swal.fire({
        title: '¿Anular reserva?',
        text: "Esta acción cambiará el estado de la reserva a cancelada",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('/reservas/') ?>' + id + '/delete';
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<?php require_once __DIR__ . '/../../../shared/layouts/footer.php'; ?>
