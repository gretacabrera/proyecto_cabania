<?php $this->layout('layouts/main', ['title' => $title]) ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= $title ?></h2>
        <a href="/proyecto_cabania/estados-productos/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Estado
        </a>
    </div>

    <!-- Filtros de búsqueda -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/proyecto_cabania/estados-productos" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Buscar por descripción</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                           placeholder="Ingrese descripción...">
                </div>
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="1" <?= ($filters['estado'] ?? '') === '1' ? 'selected' : '' ?>>Activos</option>
                        <option value="0" <?= ($filters['estado'] ?? '') === '0' ? 'selected' : '' ?>>Inactivos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="per_page" class="form-label">Por página</label>
                    <select class="form-select" id="per_page" name="per_page">
                        <option value="10" <?= ($per_page ?? 10) == 10 ? 'selected' : '' ?>>10</option>
                        <option value="25" <?= ($per_page ?? 10) == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= ($per_page ?? 10) == 50 ? 'selected' : '' ?>>50</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label d-block">&nbsp;</label>
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="/proyecto_cabania/estados-productos" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Resultados -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Estados de Productos (<?= $total_records ?? 0 ?> registros)</span>
            <div>
                <a href="/proyecto_cabania/estados-productos/stats" class="btn btn-info btn-sm">
                    <i class="fas fa-chart-bar"></i> Estadísticas
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($estados)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estados as $estado): ?>
                                <tr>
                                    <td><?= $estado['id_estadoproducto'] ?></td>
                                    <td><?= htmlspecialchars($estado['estadoproducto_descripcion']) ?></td>
                                    <td>
                                        <?php if ($estado['estadoproducto_estado'] == 1): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="/proyecto_cabania/estados-productos/<?= $estado['id_estadoproducto'] ?>/edit" 
                                               class="btn btn-sm btn-outline-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <?php if ($estado['estadoproducto_estado'] == 1): ?>
                                                <a href="/proyecto_cabania/estados-productos/<?= $estado['id_estadoproducto'] ?>/delete" 
                                                   class="btn btn-sm btn-outline-danger" title="Desactivar"
                                                   data-action="deactivate-estado-producto" data-estado-id="<?= $estado['id_estadoproducto'] ?>">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="/proyecto_cabania/estados-productos/<?= $estado['id_estadoproducto'] ?>/restore" 
                                                   class="btn btn-sm btn-outline-success" title="Activar"
                                                   data-action="activate-estado-producto" data-estado-id="<?= $estado['id_estadoproducto'] ?>">
                                                    <i class="fas fa-check"></i>
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
                <?php if ($total_pages > 1): ?>
                    <?php $this->insert('components/pagination', [
                        'current_page' => $current_page,
                        'total_pages' => $total_pages,
                        'base_url' => '/proyecto_cabania/estados-productos',
                        'query_params' => array_filter($filters)
                    ]) ?>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No se encontraron estados de productos</p>
                    <a href="/proyecto_cabania/estados-productos/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Primer Estado
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>