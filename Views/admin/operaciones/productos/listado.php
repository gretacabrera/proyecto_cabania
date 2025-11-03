<?php 
/**
 * Vista de listado de productos
 * Siguiendo el patrón exacto del módulo de Cabañas
 */

// Funciones auxiliares para la vista
function getStockBadgeClass($stock) {
    if ($stock <= 0) return 'badge-danger';
    if ($stock <= 10) return 'badge-warning';
    return 'badge-success';
}
?>

<div class="container-fluid">
    <!-- Encabezado moderno siguiendo patrón de Cabañas -->
    <div class="card border-0 shadow-sm">
        <!-- Header oscuro -->
        <div class="card-header text-dark py-3 mb-0">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0">Gestión de Productos</h4>
                </div>
                <div class="col-auto">
                    <a href="<?= url('/productos/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Nuevo Producto
                    </a>
                </div>
            </div>
        </div>
        <!-- Filtros compactos -->
        <div class="card-body pb-0">
            <form method="GET" action="<?= url('/productos') ?>" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label small mb-1 text-muted">Filtros de búsqueda</label>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Nombre</label>
                        <input type="text" name="producto_nombre" class="form-control form-control-sm" 
                               placeholder="" value="<?= htmlspecialchars($filters['producto_nombre'] ?? '') ?>" style="width: 150px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Categoría</label>
                        <select name="rela_categoria" class="form-select form-select-sm" style="width: 130px;">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id_categoria'] ?>" 
                                        <?= ($filters['rela_categoria'] == $categoria['id_categoria']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['categoria_descripcion']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Marca</label>
                        <select name="rela_marca" class="form-select form-select-sm" style="width: 120px;">
                            <option value="">Todas</option>
                            <?php foreach ($marcas as $marca): ?>
                                <option value="<?= $marca['id_marca'] ?>" 
                                        <?= ($filters['rela_marca'] == $marca['id_marca']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($marca['marca_descripcion']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Stock mín.</label>
                        <input type="number" name="stock_min" class="form-control form-control-sm" 
                               placeholder="Min" value="<?= htmlspecialchars($filters['stock_min'] ?? '') ?>" 
                               min="0" style="width: 80px;">
                    </div>
                    <div class="col-auto ms-auto">
                        <label class="form-label small mb-1">Estado</label>
                        <select name="rela_estadoproducto" class="form-select form-select-sm" style="width: 120px;">
                            <option value="">Todos</option>
                            <?php foreach ($estadosProducto as $estado): ?>
                                <option value="<?= $estado['id_estadoproducto'] ?>" 
                                        <?= ($filters['rela_estadoproducto'] == $estado['id_estadoproducto']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($estado['estadoproducto_descripcion']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary btn-sm" title="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?= url('/productos') ?>" class="btn btn-info btn-sm" title="Limpiar filtros">
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
                            <button type="button" onclick="exportarProductos(event)" class="btn btn-success btn-sm" title="Exportar a Excel">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                            <button type="button" onclick="exportarProductosPDF(event)" class="btn btn-danger btn-sm" title="Exportar a PDF">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla estilo moderno -->
        <div class="card-body p-0">
            <?php if (empty($productos)): ?>
                <div class="empty-state py-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-boxes fa-3x text-muted opacity-50"></i>
                    </div>
                    <h6 class="text-muted">No se encontraron productos</h6>
                    <p class="text-muted small mb-3">Intenta modificar los filtros o crea un nuevo producto.</p>
                    <a href="<?= url('/productos/create') ?>" class="btn btn-outline-dark btn-sm">
                        <i class="fas fa-plus fa-sm"></i> Crear producto
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
                    
                    <!-- Paginación superior -->
                    <div class="card-header bg-light border-bottom py-2">
                        <?php $renderPagination(true); ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 py-3">Nombre</th>
                                <th class="border-0 py-3">Categoría</th>
                                <th class="border-0 py-3">Stock</th>
                                <th class="border-0 py-3">Precio</th>
                                <th class="border-0 py-3">Estado</th>
                                <th class="border-0 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td class="border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($producto['producto_nombre']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($producto['marca_descripcion'] ?? 'Sin marca') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-tag text-info me-2"></i>
                                            <span class="text-dark"><?= htmlspecialchars($producto['categoria_descripcion'] ?? 'Sin categoría') ?></span>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-boxes text-warning me-2"></i>
                                            <span class="text-dark"><?= $producto['producto_stock'] ?></span>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <span class="fw-medium text-success">$<?= number_format($producto['producto_precio'], 2, '.', ',') ?></span>
                                        <small class="text-muted d-block">c/unidad</small>
                                    </td>                                    
                                    <td class="border-0 py-3">
                                        <?php if ($producto['rela_estadoproducto'] == 1): ?>
                                            <span class="badge bg-success text-white px-2 py-1 rounded-pill">Disponible</span>
                                        <?php elseif ($producto['rela_estadoproducto'] == 2): ?>
                                            <span class="badge bg-warning text-dark px-2 py-1 rounded-pill">Stock Mínimo</span>
                                        <?php elseif ($producto['rela_estadoproducto'] == 3): ?>
                                            <span class="badge bg-danger text-white px-2 py-1 rounded-pill">Sin Stock</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary text-white px-2 py-1 rounded-pill">Baja</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border-0 py-3 text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= url('/productos/' . $producto['id_producto']) ?>"
                                               class="btn btn-outline-primary btn-sm"
                                               title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= url('/productos/' . $producto['id_producto']) . '/edit'?>"
                                               class="btn btn-outline-warning btn-sm"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($producto['rela_estadoproducto'] != 4): ?>
                                                <!-- Producto activo: puede dar de baja -->
                                                <button class="btn btn-outline-danger btn-sm"
                                                        onclick="cambiarEstadoProducto(<?= $producto['id_producto'] ?>, 4, '<?= addslashes($producto['producto_nombre']) ?>')"
                                                        title="Dar de baja">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php else: ?>
                                                <!-- Producto dado de baja: puede reactivar -->
                                                <button class="btn btn-outline-success btn-sm"
                                                        onclick="cambiarEstadoProducto(<?= $producto['id_producto'] ?>, 1, '<?= addslashes($producto['producto_nombre']) ?>')"
                                                        title="Reactivar">
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
// Función para cambiar estado del producto
function cambiarEstadoProducto(id, nuevoEstado, nombreProducto) {
    const acciones = {
        1: 'reactivar',
        4: 'dar de baja'
    };
    
    const accion = acciones[nuevoEstado] || 'cambiar estado de';
    
    Swal.fire({
        title: '¿Está seguro?',
        text: `¿Desea ${accion} el producto "${nombreProducto}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: nuevoEstado == 4 ? '#d33' : '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?= url('/productos/') ?>' + id + '/estado', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'estado=' + nuevoEstado
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
                Swal.fire('Error', 'Ocurrió un error inesperado', 'error');
            });
        }
    });
}

// Función para exportar productos a Excel
function exportarProductos(event) {
    event.preventDefault();
    const params = new URLSearchParams(window.location.search);
    window.location.href = '<?= url('/productos/exportar') ?>?' + params.toString();
}

// Función para exportar productos a PDF
function exportarProductosPDF(event) {
    event.preventDefault();
    const params = new URLSearchParams(window.location.search);
    window.location.href = '<?= url('/productos/exportar-pdf') ?>?' + params.toString();
}


</script>


?>