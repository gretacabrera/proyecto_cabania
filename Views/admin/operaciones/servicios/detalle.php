<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= url('/') ?>">
                    <i class="fas fa-home"></i> Inicio
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= url('/servicios') ?>">
                    <i class="fas fa-concierge-bell"></i> Servicios
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?= htmlspecialchars($servicio['servicio_nombre']) ?>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 class="h3 mb-2 text-gray-800">
                <i class="fas fa-concierge-bell text-primary me-2"></i>
                <?= htmlspecialchars($servicio['servicio_nombre']) ?>
            </h2>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-<?= $servicio['servicio_estado'] ? 'success' : 'danger' ?> fs-6">
                    <?= $servicio['servicio_estado'] ? 'Activo' : 'Inactivo' ?>
                </span>
                <small class="text-muted">ID: <?= $servicio['id_servicio'] ?></small>
            </div>
        </div>
        <div>
            <div class="btn-group">
                <a href="<?= url('/servicios/' . $servicio['id_servicio'] . '/edit') ?>" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i>Editar
                </a>
                <button type="button" class="btn btn-<?= $servicio['servicio_estado'] ? 'danger' : 'success' ?>" 
                        onclick="cambiarEstado(<?= $servicio['id_servicio'] ?>, <?= $servicio['servicio_estado'] ?>)">
                    <i class="fas fa-<?= $servicio['servicio_estado'] ? 'times' : 'check' ?> me-2"></i>
                    <?= $servicio['servicio_estado'] ? 'Desactivar' : 'Activar' ?>
                </button>
                <a href="<?= url('/servicios') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información principal -->
        <div class="col-lg-8">
            <!-- Datos básicos -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Información del Servicio
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-2">Nombre</h6>
                            <p class="h5 mb-0"><?= htmlspecialchars($servicio['servicio_nombre']) ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-2">Tipo de Servicio</h6>
                            <?php if (!empty($servicio['tiposervicio_descripcion'])): ?>
                                <span class="badge bg-info text-dark fs-6">
                                    <?= htmlspecialchars($servicio['tiposervicio_descripcion']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">No especificado</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-2">Precio</h6>
                            <p class="h4 text-success mb-0">
                                <i class="fas fa-dollar-sign me-2"></i>
                                <?= number_format($servicio['servicio_precio'], 2, ',', '.') ?>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-2">Estado</h6>
                            <span class="badge bg-<?= $servicio['servicio_estado'] ? 'success' : 'danger' ?> fs-6">
                                <i class="fas fa-<?= $servicio['servicio_estado'] ? 'check' : 'times' ?> me-2"></i>
                                <?= $servicio['servicio_estado'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </div>
                    </div>

                    <?php if (!empty($servicio['servicio_descripcion'])): ?>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-muted mb-2">Descripción</h6>
                                <p class="mb-0"><?= nl2br(htmlspecialchars($servicio['servicio_descripcion'])) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estadísticas de uso -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Estadísticas de Uso
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-shopping-cart fa-2x text-info mb-2"></i>
                                <h3 class="text-info mb-1"><?= number_format($estadisticas['total_consumos'] ?? 0) ?></h3>
                                <small class="text-muted">Consumos Totales</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-calculator fa-2x text-warning mb-2"></i>
                                <h3 class="text-warning mb-1"><?= number_format($estadisticas['cantidad_total'] ?? 0) ?></h3>
                                <small class="text-muted">Cantidad Consumida</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                                <h3 class="text-success mb-1">$<?= number_format($estadisticas['ingresos_total'] ?? 0, 2, ',', '.') ?></h3>
                                <small class="text-muted">Ingresos Generados</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Consumos recientes -->
            <?php if (isset($consumos_recientes) && !empty($consumos_recientes)): ?>
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Consumos Recientes
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Reserva</th>
                                        <th>Cantidad</th>
                                        <th>Total</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($consumos_recientes as $consumo): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>#<?= $consumo['id_reserva'] ?? 'N/A' ?></strong>
                                                    <?php if (!empty($consumo['consumo_descripcion'])): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($consumo['consumo_descripcion']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?= $consumo['consumo_cantidad'] ?></span>
                                            </td>
                                            <td>
                                                <strong class="text-success">$<?= number_format($consumo['consumo_total'], 2, ',', '.') ?></strong>
                                            </td>
                                            <td>
                                                <?php if (!empty($consumo['reserva_fechainicio'])): ?>
                                                    <small><?= date('d/m/Y', strtotime($consumo['reserva_fechainicio'])) ?></small>
                                                <?php else: ?>
                                                    <small class="text-muted">N/A</small>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (count($consumos_recientes) >= 5): ?>
                            <div class="text-center mt-3">
                                <a href="<?= url('/consumos?servicio=' . $servicio['id_servicio']) ?>" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-eye me-2"></i>Ver todos los consumos
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Consumos Recientes
                        </h5>
                    </div>
                    <div class="card-body text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Sin consumos registrados</h5>
                        <p class="text-muted">Este servicio aún no tiene consumos asociados.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <!-- Acciones rápidas -->
            <div class="card shadow mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= url('/servicios/' . $servicio['id_servicio'] . '/edit') ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Editar Servicio
                        </a>
                        <button type="button" class="btn btn-<?= $servicio['servicio_estado'] ? 'danger' : 'success' ?>" 
                                onclick="cambiarEstado(<?= $servicio['id_servicio'] ?>, <?= $servicio['servicio_estado'] ?>)">
                            <i class="fas fa-<?= $servicio['servicio_estado'] ? 'times' : 'check' ?> me-2"></i>
                            <?= $servicio['servicio_estado'] ? 'Desactivar' : 'Activar' ?>
                        </button>
                        <a href="<?= url('/consumos/create?servicio=' . $servicio['id_servicio']) ?>" class="btn btn-info">
                            <i class="fas fa-plus me-2"></i>Registrar Consumo
                        </a>
                        <hr>
                        <a href="<?= url('/servicios') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver al Listado
                        </a>
                    </div>
                </div>
            </div>

            <!-- Información técnica -->
            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-cog me-2"></i>Información Técnica
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">ID del Servicio</small>
                        <code>#<?= $servicio['id_servicio'] ?></code>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">ID Tipo de Servicio</small>
                        <code>#<?= $servicio['rela_tiposervicio'] ?? 'N/A' ?></code>
                    </div>

                    <?php if (isset($servicio['servicio_fecha_creacion'])): ?>
                        <div class="mb-3">
                            <small class="text-muted d-block">Fecha de creación</small>
                            <small><?= date('d/m/Y H:i', strtotime($servicio['servicio_fecha_creacion'])) ?></small>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($servicio['servicio_fecha_actualizacion'])): ?>
                        <div class="mb-3">
                            <small class="text-muted d-block">Última actualización</small>
                            <small><?= date('d/m/Y H:i', strtotime($servicio['servicio_fecha_actualizacion'])) ?></small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Resumen de rentabilidad -->
            <?php if (isset($estadisticas) && $estadisticas['total_consumos'] > 0): ?>
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-pie me-2"></i>Resumen de Rentabilidad
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php 
                        $promedio_por_consumo = $estadisticas['ingresos_total'] / $estadisticas['total_consumos'];
                        ?>
                        <div class="mb-3">
                            <small class="text-muted d-block">Promedio por consumo</small>
                            <strong class="text-success">$<?= number_format($promedio_por_consumo, 2, ',', '.') ?></strong>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted d-block">Cantidad promedio</small>
                            <strong><?= number_format($estadisticas['cantidad_total'] / $estadisticas['total_consumos'], 2, ',', '.') ?></strong>
                        </div>

                        <div class="progress mb-2" style="height: 8px;">
                            <?php 
                            $porcentaje_uso = min(100, ($estadisticas['total_consumos'] / 50) * 100); // Suponiendo 50 como máximo esperado
                            ?>
                            <div class="progress-bar bg-success" style="width: <?= $porcentaje_uso ?>%"></div>
                        </div>
                        <small class="text-muted">Nivel de uso: <?= number_format($porcentaje_uso, 1) ?>%</small>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function cambiarEstado(id, estadoActual) {
    const accion = estadoActual ? 'desactivar' : 'activar';
    const mensaje = `¿Está seguro que desea ${accion} este servicio?`;
    
    Swal.fire({
        title: '¿Confirmar acción?',
        text: mensaje,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, ' + accion,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`<?= url('/servicios') ?>/${id}/estado`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ estado: estadoActual ? 0 : 1 })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('¡Éxito!', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo completar la acción', 'error');
            });
        }
    });
}
</script>