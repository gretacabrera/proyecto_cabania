<?php
$this->extend('layouts/main');
$this->section('title', $title);
$this->section('content');
?>

<div class="admin-header">
    <h1><?= $title ?></h1>
    <div class="header-actions">
        <a href="/modulos/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Módulo
        </a>
    </div>
</div>

<!-- Filtros de búsqueda -->
<div class="search-filters">
    <form method="GET" action="/modulos" class="filters-form">
        <div class="filter-row">
            <div class="filter-group">
                <label for="descripcion">Descripción:</label>
                <input type="text" name="descripcion" id="descripcion" 
                       value="<?= $filters['descripcion'] ?? '' ?>" 
                       placeholder="Buscar por descripción...">
            </div>
            
            <div class="filter-group">
                <label for="menu">Menú:</label>
                <select name="menu" id="menu">
                    <option value="">Todos los menús</option>
                    <?php foreach ($menus as $menu): ?>
                        <option value="<?= $menu['id_menu'] ?>" 
                                <?= ($filters['menu'] ?? '') == $menu['id_menu'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($menu['menu_nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="estado">Estado:</label>
                <select name="estado" id="estado">
                    <option value="">Todos los estados</option>
                    <option value="1" <?= ($filters['estado'] ?? '') === '1' ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= ($filters['estado'] ?? '') === '0' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <a href="/modulos" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </div>
    </form>
</div>

<?php if (empty($modulos)): ?>
    <div class="no-results">
        <i class="fas fa-puzzle-piece"></i>
        <h3>No se encontraron módulos</h3>
        <p>No hay módulos que coincidan con los filtros especificados.</p>
        <a href="/modulos/create" class="btn btn-primary">Crear Primer Módulo</a>
    </div>
<?php else: ?>
    <div class="table-container">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Ruta</th>
                    <th>Menú</th>
                    <th>Estado</th>
                    <th>Fecha Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modulos as $modulo): ?>
                    <tr class="<?= $modulo['modulo_estado'] ? '' : 'table-row-disabled' ?>">
                        <td>
                            <div class="module-info">
                                <strong><?= htmlspecialchars($modulo['modulo_descripcion']) ?></strong>
                                <?php if (!empty($modulo['modulo_observaciones'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($modulo['modulo_observaciones']) ?></small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <code class="route-code"><?= htmlspecialchars($modulo['modulo_ruta']) ?></code>
                        </td>
                        <td>
                            <?php if ($modulo['menu_nombre']): ?>
                                <span class="badge badge-info">
                                    <i class="fas fa-bars"></i>
                                    <?= htmlspecialchars($modulo['menu_nombre']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">
                                    <i class="fas fa-minus"></i> Sin menú
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($modulo['modulo_estado']): ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle"></i> Activo
                                </span>
                            <?php else: ?>
                                <span class="badge badge-danger">
                                    <i class="fas fa-times-circle"></i> Inactivo
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="date-cell">
                                <?= !empty($modulo['modulo_fecha_creacion']) 
                                    ? date('d/m/Y', strtotime($modulo['modulo_fecha_creacion'])) 
                                    : '-' ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="/modulos/<?= $modulo['id_modulo'] ?>" 
                                   class="btn btn-sm btn-info" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <a href="/modulos/<?= $modulo['id_modulo'] ?>/edit" 
                                   class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <?php if ($modulo['modulo_estado']): ?>
                                    <?php if ($this->userCan('modulos_delete')): ?>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                data-action="desactivar-modulo"
                                                data-modulo-id="<?= $modulo['id_modulo'] ?>"
                                                data-modulo-descripcion="<?= htmlspecialchars($modulo['modulo_descripcion']) ?>"
                                                title="Desactivar">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if ($this->userCan('modulos_restore')): ?>
                                        <button type="button" class="btn btn-sm btn-success" 
                                                data-action="activar-modulo"
                                                data-modulo-id="<?= $modulo['id_modulo'] ?>"
                                                data-modulo-descripcion="<?= htmlspecialchars($modulo['modulo_descripcion']) ?>"
                                                title="Activar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Estadísticas -->
    <div class="table-summary">
        <?php 
        $totalModulos = count($modulos);
        $totalActivos = count(array_filter($modulos, function($m) { return $m['modulo_estado']; }));
        $totalInactivos = $totalModulos - $totalActivos;
        ?>
        <div class="summary-item">
            <strong>Total módulos:</strong> <?= $totalModulos ?>
        </div>
        <div class="summary-item">
            <strong>Activos:</strong> <span class="text-success"><?= $totalActivos ?></span>
        </div>
        <div class="summary-item">
            <strong>Inactivos:</strong> <span class="text-danger"><?= $totalInactivos ?></span>
        </div>
    </div>

    <!-- Paginación -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination-container">
            <nav aria-label="Paginación de módulos">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <?php 
                            $url = '/modulos?page=' . $i;
                            foreach ($filters as $key => $value) {
                                if (!empty($value)) {
                                    $url .= '&' . $key . '=' . urlencode($value);
                                }
                            }
                            ?>
                            <a class="page-link" href="<?= $url ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
<?php endif; ?>


.module-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.module-info strong {
    color: #333;
    font-weight: 600;
}

.module-info small {
    font-size: 0.85em;
    line-height: 1.3;
}

.route-code {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    padding: 4px 8px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
    color: #495057;
}

.date-cell {
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
    color: #6c757d;
}

.badge {
    padding: 6px 10px;
    border-radius: 16px;
    font-size: 0.8em;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.badge-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f1b0b7;
}

.badge-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.table-row-disabled {
    opacity: 0.6;
    background-color: #f8f9fa;
}

.action-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.table-summary {
    display: flex;
    justify-content: flex-end;
    gap: 20px;
    margin-top: 15px;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 8px;
}

.summary-item {
    font-size: 0.95em;
}

.text-success {
    color: #28a745;
    font-weight: 600;
}

.text-danger {
    color: #dc3545;
    font-weight: 600;
}

.no-results {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-results i {
    font-size: 4em;
    margin-bottom: 20px;
    color: #ddd;
}

.no-results h3 {
    margin-bottom: 10px;
    color: #333;
}

/* Responsive */
@media (max-width: 768px) {
    .filter-row {
        flex-direction: column;
        gap: 10px;
    }
    
    .action-buttons {
        justify-content: center;
    }
    
    .table-summary {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
    
    .route-code {
        display: block;
        word-break: break-all;
    }
}

/* Mejorar legibilidad de las rutas */
.route-code:hover {
    background-color: #e2e3e5;
    border-color: #d6d8db;
}

/* Destacar módulos importantes del sistema */
tr[data-system="true"] {
    border-left: 3px solid #fd7e14;
}

        }
    `;
    document.head.appendChild(style);
});

<?php $this->endSection(); ?>