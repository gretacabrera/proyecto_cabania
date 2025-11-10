<div class="container-fluid">
    <!-- Encabezado -->
    <div class="card border-0 shadow-sm">
        <div class="card-header text-dark py-3 mb-0">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0">Gestión de Estados de Productos</h4>
                </div>
                <div class="col-auto">
                    <a href="<?= url('/estadosproductos/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Nuevo Estado
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtros compactos -->
        <div class="card-body pb-0">
            <form method="GET" action="<?= url('/estadosproductos') ?>" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label small mb-1 text-muted">Filtros de búsqueda</label>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Descripción</label>
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Buscar..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>" style="width: 200px;">
                    </div>
                    <div class="col-auto ms-auto">
                        <label class="form-label small mb-1">Estado</label>
                        <select name="estado" class="form-select form-select-sm" style="width: 120px;">
                            <option value="">Todos</option>
                            <option value="1" <?= ($filters['estado'] ?? '') === '1' ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= ($filters['estado'] ?? '') === '0' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary btn-sm" title="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?= url('/estadosproductos') ?>" class="btn btn-info btn-sm" title="Limpiar filtros">
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
                            <option value="5" <?= ($per_page ?? 10) == 5 ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= ($per_page ?? 10) == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= ($per_page ?? 10) == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= ($per_page ?? 10) == 50 ? 'selected' : '' ?>>50</option>
                        </select>
                    </div>
                    <div class="col"></div> <!-- Espaciador para empujar botones a la derecha -->
                    <div class="col-auto">
                        <div class="btn-group" role="group">
                            <button type="button" onclick="exportarEstados(event)" class="btn btn-success btn-sm" title="Exportar a Excel">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                            <button type="button" onclick="exportarEstadosPDF(event)" class="btn btn-danger btn-sm" title="Exportar a PDF">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla -->
        <div class="card-body p-0">
            <?php if (empty($estados)): ?>
                <div class="empty-state py-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-tag fa-3x text-muted opacity-50"></i>
                    </div>
                    <h6 class="text-muted">No se encontraron estados de productos</h6>
                    <p class="text-muted small mb-3">Intenta modificar los filtros o crea un nuevo estado.</p>
                    <a href="<?= url('/estadosproductos/create') ?>" class="btn btn-outline-dark btn-sm">
                        <i class="fas fa-plus fa-sm"></i> Crear estado
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
                            <?php foreach ($estados as $estado): ?>
                                <tr>
                                    <td class="border-0 py-3">
                                        <div class="fw-medium text-dark"><?= htmlspecialchars($estado['estadoproducto_descripcion']) ?></div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <?php if ($estado['estadoproducto_estado'] == 1): ?>
                                            <span class="badge bg-success text-white px-2 py-1 rounded-pill">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-white px-2 py-1 rounded-pill">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border-0 py-3 text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= url('/estadosproductos/' . $estado['id_estadoproducto']) ?>"
                                               class="btn btn-outline-primary btn-sm"
                                               title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= url('/estadosproductos/' . $estado['id_estadoproducto'] . '/edit') ?>"
                                               class="btn btn-outline-warning btn-sm"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($estado['estadoproducto_estado'] == 1): ?>
                                                <a href="<?= url('/estadosproductos/' . $estado['id_estadoproducto'] . '/delete') ?>"
                                                   class="btn btn-outline-danger btn-sm"
                                                   title="Desactivar"
                                                   onclick="return confirm('¿Está seguro de desactivar este estado?')">
                                                    <i class="fas fa-ban"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= url('/estadosproductos/' . $estado['id_estadoproducto'] . '/restore') ?>"
                                                   class="btn btn-outline-success btn-sm"
                                                   title="Activar"
                                                   onclick="return confirm('¿Está seguro de activar este estado?')">
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

                <!-- Paginación simple -->
                <?php if (($total_pages ?? 1) > 1): ?>
                    <div class="card-footer bg-white border-top py-3">
                        <div class="row align-items-center">
                            <div class="col-sm-6">
                                <span class="text-muted small">
                                    Mostrando página <?= $current_page ?> de <?= $total_pages ?>
                                </span>
                            </div>
                            <div class="col-sm-6">
                                <nav aria-label="Paginación" class="d-flex justify-content-end">
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php if ($current_page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>">Anterior</a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                                <?php if ($i == $current_page): ?>
                                                    <span class="page-link bg-primary text-white border-primary"><?= $i ?></span>
                                                <?php else: ?>
                                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                                <?php endif; ?>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($current_page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>">Siguiente</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript para funcionalidades -->
<script>
function exportarEstados(event) {
    if (event) {
        event.preventDefault();
    }
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Generando archivo...',
            text: 'Por favor espere mientras se procesa la exportación',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
    
    const urlParams = new URLSearchParams(window.location.search);
    const baseExportUrl = '<?= url('/estadosproductos/exportar') ?>';
    const exportUrl = baseExportUrl + '?' + urlParams.toString();
    
    const link = document.createElement('a');
    link.href = exportUrl;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    setTimeout(() => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¡Exportación iniciada!',
                text: 'El archivo se descargará automáticamente',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    }, 1000);
}

function exportarEstadosPDF(event) {
    if (event) {
        event.preventDefault();
    }
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Generando PDF...',
            text: 'Por favor espere mientras se procesa la exportación',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
    
    const urlParams = new URLSearchParams(window.location.search);
    const basePdfUrl = '<?= url('/estadosproductos/exportar-pdf') ?>';
    const pdfUrl = basePdfUrl + '?' + urlParams.toString();
    
    const link = document.createElement('a');
    link.href = pdfUrl;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    setTimeout(() => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¡PDF generado!',
                text: 'El archivo PDF se descargará automáticamente',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    }, 1000);
}
</script>
