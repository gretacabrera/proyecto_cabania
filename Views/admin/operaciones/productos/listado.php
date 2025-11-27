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
                <?php if (isset($_SESSION['perfil_nombre']) && $_SESSION['perfil_nombre'] !== 'encargado bar'): ?>
                <div class="col-auto">
                    <a href="<?= url('/productos/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Nuevo Producto
                    </a>
                </div>
                <?php endif; ?>
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
                            <?php if (!isset($_SESSION['perfil_nombre']) || $_SESSION['perfil_nombre'] !== 'encargado bar'): ?>
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalCotizacion" title="Plantilla de Cotización">
                                <i class="fas fa-file-invoice-dollar me-1"></i> Cotización
                            </button>
                            <?php endif; ?>
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
                    <p class="text-muted small mb-3">Intenta modificar los filtros<?php if (isset($_SESSION['perfil_nombre']) && $_SESSION['perfil_nombre'] !== 'encargado bar'): ?> o crea un nuevo producto<?php endif; ?>.</p>
                    <?php if (isset($_SESSION['perfil_nombre']) && $_SESSION['perfil_nombre'] !== 'encargado bar'): ?>
                    <a href="<?= url('/productos/create') ?>" class="btn btn-outline-dark btn-sm">
                        <i class="fas fa-plus fa-sm"></i> Crear producto
                    </a>
                    <?php endif; ?>
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
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0 py-3">Nombre</th>
                                <th class="border-0 py-3">Categoría</th>
                                <th class="border-0 py-3">Stock</th>
                                <?php if (!isset($_SESSION['perfil_nombre']) || $_SESSION['perfil_nombre'] !== 'encargado bar'): ?>
                                <th class="border-0 py-3">Precio</th>
                                <?php endif; ?>
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
                                            <span class="text-dark"><?= htmlspecialchars($producto['categoria_descripcion'] ?? 'Sin categoría') ?></span>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <span class="text-dark"><?= $producto['producto_stock'] ?></span>
                                        </div>
                                    </td>
                                    <?php if (!isset($_SESSION['perfil_nombre']) || $_SESSION['perfil_nombre'] !== 'encargado bar'): ?>
                                    <td class="border-0 py-3">
                                        <span class="fw-medium text-success">$<?= number_format($producto['producto_precio'], 2, '.', ',') ?></span>
                                        <small class="text-muted d-block">c/unidad</small>
                                    </td>
                                    <?php endif; ?>                                    
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
                                    
                                    <!-- Columna de acciones -->
                                    <td class="border-0 py-3 text-center">
                                        <?php if (isset($_SESSION['perfil_nombre']) && $_SESSION['perfil_nombre'] === 'encargado bar'): ?>
                                            <!-- Encargado de bar: solo historial -->
                                            <a href="<?= url('/productos/' . $producto['id_producto'] . '/historial-stock') ?>"
                                               class="btn btn-outline-info btn-sm"
                                               title="Historial de movimientos">
                                                <i class="fas fa-history"></i>
                                            </a>
                                        <?php else: ?>
                                            <!-- Otros usuarios: todas las acciones -->
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="<?= url('/productos/' . $producto['id_producto']) ?>"
                                                   class="btn btn-outline-primary btn-sm"
                                                   title="Ver detalle">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= url('/productos/' . $producto['id_producto'] . '/historial-stock') ?>"
                                                   class="btn btn-outline-info btn-sm"
                                                   title="Historial de movimientos">
                                                    <i class="fas fa-history"></i>
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
                                        <?php endif; ?>
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

<!-- Modal 1: Selección de Acción -->
<div class="modal" id="modalCotizacion" tabindex="-1" role="dialog" style="display: none;">
    <div class="modal-dialog" role="document" style="max-width: 370px; width: 370px; max-height: 300px; height: 300px;">
        <div class="modal-content" style="border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); border: none; width: 100%; height: 100%;">
            <!-- Header simple -->
            <div class="modal-header border-0 pb-1 pt-2 px-3 position-relative" style="background: #ffffff;">
                <h5 class="modal-title w-100 text-left font-weight-normal d-flex align-items-center" style="font-size: 15px; color: #333; font-weight: 400;">
                    <i class="fas fa-file-invoice-dollar mr-2" style="color: #17a2b8; font-size: 16px;"></i>
                    Generar Plantilla de Cotizaciones
                </h5>
                <button type="button" class="close position-absolute" data-dismiss="modal" style="right: 12px; top: 8px; font-size: 22px; opacity: 0.5; outline: none;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='0.5'">
                    <span>&times;</span>
                </button>
            </div>
            
            <!-- Descripción -->
            <div class="px-3 pb-2 text-left" style="background: #ffffff;">
                <p class="mb-0 text-muted" style="font-size: 12px; line-height: 1.3; color: #777;">
                    Seleccione una opción para generar la solicitud de cotización
                </p>
                <div class="alert alert-info mt-2 mb-0 py-2 px-2" style="font-size: 11px;">
                    <i class="fas fa-info-circle"></i> <strong>Nota:</strong> No se incluirán productos que estén de baja.
                </div>
            </div>
            
            <!-- Body con botones -->
            <div class="modal-body text-center px-3 pt-2 pb-3" style="background: #ffffff;">
                <div class="d-flex flex-row justify-content-center align-items-stretch" style="gap: 15px;">
                    <!-- Botón Excel -->
                    <button type="button" class="btn btn-cotizacion d-flex flex-column align-items-center justify-content-center" onclick="exportarPlantillaCotizacion()" style="width: 140px; height: 120px; border-radius: 8px; background: #28a745; border: none; transition: all 0.2s ease; color: white; box-shadow: 0 1px 4px rgba(40, 167, 69, 0.3);">
                        <div class="icon-wrapper mb-2" style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-file-excel" style="font-size: 24px;"></i>
                        </div>
                        <div class="text-wrapper">
                            <div style="font-size: 14px; font-weight: 500; line-height: 1.2;">Exportar</div>
                            <div style="font-size: 13px; opacity: 0.95;">Excel</div>
                        </div>
                    </button>
                    
                    <!-- Botón Email -->
                    <button type="button" class="btn btn-cotizacion d-flex flex-column align-items-center justify-content-center" onclick="abrirModalProveedor()" style="width: 140px; height: 120px; border-radius: 8px; background: #007bff; border: none; transition: all 0.2s ease; color: white; box-shadow: 0 1px 4px rgba(0, 123, 255, 0.3);">
                        <div class="icon-wrapper mb-2" style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-envelope" style="font-size: 22px;"></i>
                        </div>
                        <div class="text-wrapper">
                            <div style="font-size: 14px; font-weight: 500; line-height: 1.2;">Enviar por</div>
                            <div style="font-size: 13px; opacity: 0.95;">Email</div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Backdrop personalizado para el modal -->
<div class="modal-backdrop" id="modalCotizacionBackdrop" style="display: none; background-color: rgba(0, 0, 0, 0.5); position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1040;"></div>

<style>
/* Estilos para centrado perfecto del modal */
#modalCotizacion {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    display: none !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 1050 !important;
}

#modalCotizacion.show {
    display: flex !important;
}

#modalCotizacion .modal-dialog {
    margin: 0 !important;
    max-height: 90vh !important;
    overflow-y: auto !important;
}

/* Efectos hover para botones de cotización */
.btn-cotizacion:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.25) !important;
    opacity: 0.95;
}

.btn-cotizacion:active {
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0,0,0,0.2) !important;
}

/* Animación de entrada del modal */
#modalCotizacion.show .modal-content {
    animation: modalFadeIn 0.2s ease-out;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Responsive para pantallas pequeñas */
@media (max-width: 576px) {
    #modalCotizacion .modal-dialog {
        max-width: 95vw !important;
    }
    
    #modalCotizacion .modal-body .d-flex {
        flex-direction: column;
    }
    
    .btn-cotizacion {
        width: 100% !important;
        max-width: 200px !important;
        height: 110px !important;
    }
}

/* Mejora visual del backdrop */
#modalCotizacionBackdrop {
    transition: opacity 0.15s ease;
}

/* Estilos para modal de proveedores */
#modalProveedor {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    display: none !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 1050 !important;
}

#modalProveedor.show {
    display: flex !important;
}

#modalProveedor .modal-dialog {
    margin: 0 !important;
}

#modalProveedor .list-group-item {
    cursor: pointer;
    transition: background-color 0.15s ease;
}

#modalProveedor .list-group-item:hover {
    background-color: #f8f9fa;
}
</style>

<script>
// Función personalizada para mostrar/ocultar modal (estilo SweetAlert)
function toggleModalCotizacion(show) {
    const modal = document.getElementById('modalCotizacion');
    const backdrop = document.getElementById('modalCotizacionBackdrop');
    
    if (show) {
        backdrop.style.display = 'block';
        modal.style.display = 'block';
        setTimeout(() => {
            modal.classList.add('show');
            modal.style.paddingRight = '0px';
        }, 10);
    } else {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
            backdrop.style.display = 'none';
        }, 150);
    }
}

// Reemplazar el comportamiento del botón de abrir modal
document.addEventListener('DOMContentLoaded', function() {
    // Interceptar el clic en el botón "Plantilla de Cotización"
    const btnCotizacion = document.querySelector('[data-target="#modalCotizacion"]');
    if (btnCotizacion) {
        btnCotizacion.removeAttribute('data-toggle');
        btnCotizacion.removeAttribute('data-target');
        btnCotizacion.addEventListener('click', function(e) {
            e.preventDefault();
            toggleModalCotizacion(true);
        });
    }
    
    // Interceptar el clic en el botón de cerrar
    document.querySelectorAll('#modalCotizacion [data-dismiss="modal"]').forEach(btn => {
        btn.removeAttribute('data-dismiss');
        btn.addEventListener('click', function() {
            toggleModalCotizacion(false);
        });
    });
    
    // Cerrar al hacer clic en el backdrop
    document.getElementById('modalCotizacionBackdrop').addEventListener('click', function() {
        toggleModalCotizacion(false);
    });
});
</script>

<!-- Backdrop para modal de proveedores -->
<div class="modal-backdrop" id="modalProveedorBackdrop" style="display: none; background-color: rgba(0, 0, 0, 0.5); position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1040;"></div>

<!-- Modal 2: Selección de Proveedor -->
<div class="modal" id="modalProveedor" tabindex="-1" role="dialog" style="display: none;">
    <div class="modal-dialog" role="document" style="max-width: 450px; width: 450px;">
        <div class="modal-content" style="border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); border: none; width: 100%;">
            <div class="modal-header py-2 px-3">
                <h5 class="modal-title" style="font-size: 15px;">
                    <i class="fas fa-building mr-2"></i>Seleccionar Proveedor
                </h5>
                <button type="button" class="close" onclick="toggleModalProveedor(false)" style="font-size: 22px; opacity: 0.8;">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <div class="form-group mb-3">
                    <label for="buscar_proveedor" class="small mb-1">
                        <i class="fas fa-search mr-1"></i>Buscar por CUIT o Denominación
                    </label>
                    <input type="text" class="form-control form-control-sm" id="buscar_proveedor" placeholder="Escriba para buscar...">
                </div>
                
                <div id="lista_proveedores" class="list-group" style="max-height: 300px; overflow-y: auto;">
                    <?php foreach ($proveedores as $proveedor): ?>
                        <a href="javascript:void(0)" 
                           class="list-group-item list-group-item-action proveedor-item py-2 px-3"
                           data-id="<?= $proveedor['id_proveedor'] ?>"
                           data-denominacion="<?= htmlspecialchars($proveedor['persona_denominacion']) ?>"
                           data-cuit="<?= htmlspecialchars($proveedor['personajuridica_cuit'] ?? '') ?>"
                           data-email="<?= htmlspecialchars($proveedor['contacto_correo'] ?? '') ?>"
                           onclick="seleccionarProveedor(this)"
                           style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong style="font-size: 13px;"><?= htmlspecialchars($proveedor['persona_denominacion']) ?></strong>
                                    <?php if (!empty($proveedor['personajuridica_cuit'])): ?>
                                        <br><small class="text-muted" style="font-size: 11px;">CUIT: <?= htmlspecialchars($proveedor['personajuridica_cuit']) ?></small>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($proveedor['contacto_correo'])): ?>
                                    <i class="fas fa-check-circle text-success" title="Tiene email" style="font-size: 16px;"></i>
                                <?php else: ?>
                                    <i class="fas fa-exclamation-triangle text-warning" title="Sin email" style="font-size: 16px;"></i>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                
                <!-- Paginación -->
                <div class="d-flex justify-content-between align-items-center mt-3 px-2">
                    <small class="text-muted" id="proveedor-info"></small>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary" id="proveedor-prev" onclick="cambiarPaginaProveedor(-1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="proveedor-next" onclick="cambiarPaginaProveedor(1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
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

// Función para exportar plantilla de cotización
function exportarPlantillaCotizacion() {
    toggleModalCotizacion(false);
    const params = new URLSearchParams(window.location.search);
    window.location.href = '<?= url('/productos/exportar-cotizacion') ?>?' + params.toString();
}

// Función para abrir modal de selección de proveedor
function abrirModalProveedor() {
    toggleModalCotizacion(false);
    setTimeout(() => {
        toggleModalProveedor(true);
    }, 300);
}

// Variables para paginación de proveedores
let proveedoresFiltrados = [];
let paginaActualProveedor = 1;
const itemsPorPaginaProveedor = 5;

// Función para mostrar/ocultar modal de proveedores
function toggleModalProveedor(show) {
    const modal = document.getElementById('modalProveedor');
    const backdrop = document.getElementById('modalProveedorBackdrop');
    
    if (show) {
        backdrop.style.display = 'block';
        modal.style.display = 'block';
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
        
        // Resetear búsqueda y mostrar todos los proveedores
        document.getElementById('buscar_proveedor').value = '';
        filtrarProveedores();
        
        // Vincular evento de búsqueda cuando se abre el modal
        const searchInput = document.getElementById('buscar_proveedor');
        searchInput.oninput = function() {
            filtrarProveedores();
        };
    } else {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
            backdrop.style.display = 'none';
        }, 150);
    }
}

// Función para filtrar proveedores
function filtrarProveedores() {
    const searchInput = document.getElementById('buscar_proveedor');
    const searchText = searchInput ? searchInput.value.toLowerCase() : '';
    proveedoresFiltrados = [];
    
    const items = document.querySelectorAll('.proveedor-item');
    items.forEach(function(item) {
        const denominacion = item.getAttribute('data-denominacion').toLowerCase();
        const cuit = item.getAttribute('data-cuit').toLowerCase();
        
        if (denominacion.includes(searchText) || cuit.includes(searchText)) {
            proveedoresFiltrados.push(item);
        }
    });
    
    paginaActualProveedor = 1;
    mostrarPaginaProveedor();
}

// Función para mostrar página actual de proveedores
function mostrarPaginaProveedor() {
    // Ocultar todos
    const items = document.querySelectorAll('.proveedor-item');
    items.forEach(item => item.style.display = 'none');
    
    const inicio = (paginaActualProveedor - 1) * itemsPorPaginaProveedor;
    const fin = inicio + itemsPorPaginaProveedor;
    const totalPaginas = Math.ceil(proveedoresFiltrados.length / itemsPorPaginaProveedor);
    
    // Mostrar items de la página actual
    for (let i = inicio; i < fin && i < proveedoresFiltrados.length; i++) {
        proveedoresFiltrados[i].style.display = 'block';
    }
    
    // Actualizar información
    const total = proveedoresFiltrados.length;
    const mostrando = Math.min(fin, total) - inicio;
    const infoElement = document.getElementById('proveedor-info');
    if (infoElement) {
        infoElement.textContent = `Mostrando ${mostrando} de ${total}`;
    }
    
    // Actualizar botones
    const btnPrev = document.getElementById('proveedor-prev');
    const btnNext = document.getElementById('proveedor-next');
    if (btnPrev) btnPrev.disabled = paginaActualProveedor === 1;
    if (btnNext) btnNext.disabled = paginaActualProveedor >= totalPaginas;
}

// Función para cambiar de página
function cambiarPaginaProveedor(direccion) {
    paginaActualProveedor += direccion;
    mostrarPaginaProveedor();
}

// Función de búsqueda en tiempo real de proveedores
$(document).ready(function() {
    // Cerrar modal al hacer clic en el backdrop
    $('#modalProveedorBackdrop').on('click', function() {
        toggleModalProveedor(false);
    });
});

// Función para seleccionar proveedor y enviar cotización
function seleccionarProveedor(element) {
    const proveedorId = $(element).data('id');
    const denominacion = $(element).data('denominacion');
    const email = $(element).data('email');
    
    // Verificar si tiene email
    if (!email || email === '') {
        Swal.fire({
            title: 'Proveedor sin email',
            text: 'El proveedor seleccionado no tiene un correo electrónico registrado.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Exportar Excel',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745'
        }).then((result) => {
            if (result.isConfirmed) {
                toggleModalProveedor(false);
                exportarPlantillaCotizacion();
            }
        });
        return;
    }
    
    // Confirmar envío
    Swal.fire({
        title: '¿Enviar cotización?',
        html: `Se enviará la plantilla de cotización a:<br><strong>${denominacion}</strong><br>${email}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#007bff'
    }).then((result) => {
        if (result.isConfirmed) {
            enviarCotizacionEmail(proveedorId, denominacion);
        }
    });
}

// Función para enviar cotización por email
function enviarCotizacionEmail(proveedorId, denominacion) {
    Swal.fire({
        title: 'Enviando cotización...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Obtener los parámetros de filtro actuales de la URL
    const params = new URLSearchParams(window.location.search);
    params.append('proveedor_id', proveedorId);
    
    fetch('<?= url('/productos/enviar-cotizacion') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: params.toString()
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toggleModalProveedor(false);
            Swal.fire('¡Éxito!', data.message, 'success');
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error', 'Ocurrió un error al enviar la cotización', 'error');
    });
}

</script>