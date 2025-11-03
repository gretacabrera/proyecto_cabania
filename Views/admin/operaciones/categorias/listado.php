<?php 
$perPage = (int) ($_GET['per_page'] ?? 10);
$start = (($pagination['current_page'] - 1) * $perPage) + 1;
$end = min($pagination['current_page'] * $perPage, $pagination['total']);

$renderPagination = function($showInfo = true) use ($pagination, $start, $end) {
?>
    <div class="row align-items-center">
        <?php if ($showInfo): ?>
            <!-- INFORMACIÓN DE REGISTROS (siempre visible) -->
            <div class="col-sm-6">
                <span class="text-muted small">
                    Mostrando <?= $start ?> a <?= $end ?> de <?= $pagination['total'] ?> registros
                </span>
            </div>
        <?php endif; ?>
        
        <div class="col-sm-<?= $showInfo ? '6' : '12' ?>">
            <!-- NAVEGACIÓN (solo si hay múltiples páginas) -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <nav aria-label="Paginación" class="d-flex justify-content-<?= $showInfo ? 'end' : 'center' ?>">
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Botón Anterior -->
                        <?php if ($pagination['current_page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) ?>">Anterior</a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Navegación inteligente con elipsis -->
                        <?php 
                        $startPage = max(1, $pagination['current_page'] - 2);
                        $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                        
                        // Primera página + elipsis
                        if ($startPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
                            </li>
                            <?php if ($startPage > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Páginas del rango actual -->
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                <?php if ($i == $pagination['current_page']): ?>
                                    <!-- Página actual: destacada y no clickeable -->
                                    <span class="page-link bg-primary text-white border-primary"><?= $i ?></span>
                                <?php else: ?>
                                    <!-- Otras páginas: enlaces normales -->
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Última página + elipsis -->
                        <?php if ($endPage < $pagination['total_pages']): ?>
                            <?php if ($endPage < $pagination['total_pages'] - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['total_pages']])) ?>"><?= $pagination['total_pages'] ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Botón Siguiente -->
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

<div class="container-fluid">
    <!-- Encabezado moderno similar al diseño de referencia -->
    <div class="card border-0 shadow-sm">
        <!-- Header oscuro -->
        <div class="card-header text-dark py-3 mb-0">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0">Gestión de Categorías</h4>
                </div>
                <div class="col-auto">
                    <a href="<?= url('/categorias/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Nueva Categoría
                    </a>
                </div>
            </div>
        </div>
        <!-- Filtros compactos -->
        <div class="card-body pb-0">
            <form method="GET" action="<?= url('/categorias') ?>" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label small mb-1 text-muted">Filtros de búsqueda</label>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Descripción</label>
                        <input type="text" name="categoria_descripcion" class="form-control form-control-sm" 
                               placeholder="Buscar categoría" value="<?= htmlspecialchars($_GET['categoria_descripcion'] ?? '') ?>" style="width: 200px;">
                    </div>
                    <div class="col-auto ms-auto">
                        <label class="form-label small mb-1">Estado</label>
                        <select name="categoria_estado" class="form-select form-select-sm" style="width: 120px;">
                            <option value="">Todos</option>
                            <option value="1" <?= ($_GET['categoria_estado'] ?? '') == '1' ? 'selected' : '' ?>>Activa</option>
                            <option value="0" <?= ($_GET['categoria_estado'] ?? '') == '0' ? 'selected' : '' ?>>Inactiva</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary btn-sm" title="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?= url('/categorias') ?>" class="btn btn-info btn-sm" title="Limpiar filtros">
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
                            <button type="button" onclick="exportarCategorias(event)" class="btn btn-success btn-sm" title="Exportar a Excel">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                            <button type="button" onclick="exportarCategoriasPDF(event)" class="btn btn-danger btn-sm" title="Exportar a PDF">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- PAGINACIÓN SUPERIOR -->
        <?php if (isset($pagination) && $pagination['total'] > 0): ?>
            <div class="card-header bg-light border-bottom py-2">
                <?php $renderPagination(true); ?>
            </div>
        <?php endif; ?>

        <!-- Tabla estilo moderno -->
        <div class="card-body p-0">
            <?php if (empty($categorias)): ?>
                <div class="empty-state py-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-tags fa-3x text-muted opacity-50"></i>
                    </div>
                    <h5 class="text-muted">No hay categorías para mostrar</h5>
                    <p class="text-muted mb-4">Comienza agregando la primera categoría del sistema.</p>
                    <a href="<?= url('/categorias/create') ?>" class="btn btn-primary">
                        Crear Primera Categoría
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0 py-3">Descripción</th>
                                <th class="border-0 py-3">Estado</th>
                                <th class="border-0 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorias as $categoria): ?>
                                <tr>
                                    <td class="border-0 py-3">
                                        <span class="fw-medium"><?= htmlspecialchars($categoria['categoria_descripcion']) ?></span>
                                    </td>
                                    <td class="border-0 py-3">
                                        <?php if ($categoria['categoria_estado'] == 1): ?>
                                            <span class="badge bg-success">Activa</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border-0 py-3 text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= url('/categorias/' . $categoria['id_categoria']) ?>" 
                                               class="btn btn-outline-primary" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= url('/categorias/' . $categoria['id_categoria'] . '/edit') ?>" 
                                               class="btn btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($categoria['categoria_estado'] == 1): ?>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        title="Desactivar"
                                                        onclick="cambiarEstado(<?= $categoria['id_categoria'] ?>, 0)">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-outline-success" 
                                                        title="Activar"
                                                        onclick="cambiarEstado(<?= $categoria['id_categoria'] ?>, 1)">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- PAGINACIÓN INFERIOR -->
        <?php if (isset($pagination) && $pagination['total'] > 0): ?>
            <div class="card-footer bg-white border-top py-3">
                <?php $renderPagination(true); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
/**
 * Cambiar estado de categoría
 */
function cambiarEstado(id, nuevoEstado) {
    const estadoTexto = nuevoEstado === 1 ? 'activa' : 'inactiva';
    
    Swal.fire({
        title: '¿Estás seguro?',
        text: `La categoría será marcada como ${estadoTexto}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cambiar estado',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`<?= url('/categorias') ?>/${id}/estado`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ estado: nuevoEstado })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        '¡Actualizado!',
                        data.message,
                        'success'
                    ).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire(
                        'Error',
                        data.message || 'Error al cambiar el estado',
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire(
                    'Error',
                    'Error de conexión. Inténtalo de nuevo.',
                    'error'
                );
            });
        }
    });
}

/**
 * Exportar categorías a Excel
 */
function exportarCategorias(event) {
    event.preventDefault();
    
    // Obtener parámetros de filtros actuales
    const params = new URLSearchParams(window.location.search);
    params.delete('page'); // Remover página para exportar todos los resultados
    
    const url = `<?= url('/categorias/exportar') ?>?${params.toString()}`;
    
    // Crear enlace temporal para descarga
    const link = document.createElement('a');
    link.href = url;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Exportar categorías a PDF
 */
function exportarCategoriasPDF(event) {
    event.preventDefault();
    
    // Obtener parámetros de filtros actuales
    const params = new URLSearchParams(window.location.search);
    params.delete('page'); // Remover página para exportar todos los resultados
    
    const url = `<?= url('/categorias/exportar-pdf') ?>?${params.toString()}`;
    
    // Crear enlace temporal para descarga
    const link = document.createElement('a');
    link.href = url;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>