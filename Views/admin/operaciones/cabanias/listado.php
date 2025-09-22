<div class="container-fluid">
    <!-- Encabezado moderno similar al diseño de referencia -->
    <div class="card border-0 shadow-sm">
        <!-- Header oscuro -->
        <div class="card-header text-dark py-3 mb-0">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0">Gestión de Cabañas</h4>
                </div>
                <div class="col-auto">
                    <a href="<?= url('/cabanias/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Nueva Cabaña
                    </a>
                </div>
            </div>
        </div>
        <!-- Filtros compactos -->
        <div class="card-body pb-0">
            <form method="GET" action="<?= url('/cabanias') ?>" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label small mb-1 text-muted">Filtros de búsqueda</label>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Nombre</label>
                        <input type="text" name="cabania_nombre" class="form-control form-control-sm" 
                               placeholder="" value="<?= htmlspecialchars($_GET['cabania_nombre'] ?? '') ?>" style="width: 150px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Capacidad</label>
                        <input type="number" name="cabania_capacidad" class="form-control form-control-sm" 
                               placeholder="Personas" value="<?= htmlspecialchars($_GET['cabania_capacidad'] ?? '') ?>" 
                               min="1" max="20" style="width: 120px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Habitaciones</label>
                        <input type="number" name="cabania_habitaciones" class="form-control form-control-sm" 
                               placeholder="Hab." value="<?= htmlspecialchars($_GET['cabania_habitaciones'] ?? '') ?>" 
                               min="1" max="10" style="width: 100px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Baños</label>
                        <input type="number" name="cabania_banios" class="form-control form-control-sm" 
                               placeholder="Baños" value="<?= htmlspecialchars($_GET['cabania_banios'] ?? '') ?>" 
                               min="1" max="10" style="width: 90px;">
                    </div>
                    <div class="col-auto ms-auto">
                        <label class="form-label small mb-1">Estado</label>
                        <select name="cabania_estado" class="form-select form-select-sm" style="width: 120px;">
                            <option value="">Todos</option>
                            <option value="1" <?= ($_GET['cabania_estado'] ?? '') == '1' ? 'selected' : '' ?>>Activa</option>
                            <option value="2" <?= ($_GET['cabania_estado'] ?? '') == '2' ? 'selected' : '' ?>>Ocupada</option>
                            <option value="0" <?= ($_GET['cabania_estado'] ?? '') == '0' ? 'selected' : '' ?>>Inactiva</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary btn-sm" title="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?= url('/cabanias') ?>" class="btn btn-info btn-sm" title="Limpiar filtros">
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
                        <button onclick="exportarCabanias()" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i> Exportar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla estilo moderno -->
        <div class="card-body p-0">
            <?php if (empty($cabanias)): ?>
                <div class="empty-state py-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-home fa-3x text-muted opacity-50"></i>
                    </div>
                    <h6 class="text-muted">No se encontraron cabañas</h6>
                    <p class="text-muted small mb-3">Intenta modificar los filtros o crea una nueva cabaña.</p>
                    <a href="<?= url('/admin/cabanias/formulario') ?>" class="btn btn-outline-dark btn-sm">
                        <i class="fas fa-plus fa-sm"></i> Crear cabaña
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table id="tablaCabanias" class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 py-3">Código</th>
                                <th class="border-0 py-3">Cabaña</th>
                                <th class="border-0 py-3">Capacidad</th>
                                <th class="border-0 py-3">Habitaciones</th>
                                <th class="border-0 py-3">Baños</th>
                                <th class="border-0 py-3">Estado</th>
                                <th class="border-0 py-3">Precio</th>
                                <th class="border-0 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cabanias as $index => $cabania): ?>
                                <tr>
                                    <td class="border-0 py-3">
                                        <div class="small text-muted">
                                            <?= htmlspecialchars($cabania['cabania_codigo']) ?>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div class="fw-medium text-dark"><?= htmlspecialchars($cabania['cabania_nombre']) ?></div>
                                                <div class="small text-muted">
                                                    <?= htmlspecialchars(substr($cabania['cabania_descripcion'], 0, 40)) ?>
                                                    <?= strlen($cabania['cabania_descripcion']) > 40 ? '...' : '' ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-users text-primary me-2"></i>
                                            <span class="text-dark ml-2"><?= $cabania['cabania_capacidad'] ?></span>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-bed text-info me-2"></i>
                                            <span class="text-dark ml-2"><?= $cabania['cabania_cantidadhabitaciones'] ?></span>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-bath text-warning me-2"></i>
                                            <span class="text-dark ml-2"><?= $cabania['cabania_cantidadbanios'] ?></span>
                                        </div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <span class="fw-medium text-success">$<?= number_format($cabania['cabania_precio'], 0, '.', ',') ?></span>
                                        <small class="text-muted d-block">p/Noche</small>
                                    </td>                                    
                                    <td class="border-0 py-3">
                                        <?php if ($cabania['cabania_estado'] == 1): ?>
                                            <span class="badge bg-success text-white px-2 py-1 rounded-pill">Activa</span>
                                        <?php elseif ($cabania['cabania_estado'] == 2): ?>
                                            <span class="badge bg-warning text-dark px-2 py-1 rounded-pill">Ocupada</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-white px-2 py-1 rounded-pill">Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border-0 py-3 text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= url('/cabanias/' . $cabania['id_cabania']) ?>"
                                               class="btn btn-outline-primary btn-sm"
                                               title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= url('/cabanias/' . $cabania['id_cabania']) . '/edit'?>"
                                               class="btn btn-outline-warning btn-sm"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($cabania['cabania_estado'] == 1): ?>
                                                <!-- Cabaña activa: puede marcar como ocupada o desactivar -->
                                                <button class="btn btn-outline-warning btn-sm"
                                                        onclick="cambiarEstadoCabania(<?= $cabania['id_cabania'] ?>, 2, '<?= addslashes($cabania['cabania_nombre']) ?>')"
                                                        title="Marcar como ocupada">
                                                    <i class="fas fa-home"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm"
                                                        onclick="cambiarEstadoCabania(<?= $cabania['id_cabania'] ?>, 0, '<?= addslashes($cabania['cabania_nombre']) ?>')"
                                                        title="Desactivar">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php elseif ($cabania['cabania_estado'] == 2): ?>
                                                <!-- Cabaña ocupada: puede liberar (activar) o desactivar -->
                                                <button class="btn btn-outline-success btn-sm"
                                                        onclick="cambiarEstadoCabania(<?= $cabania['id_cabania'] ?>, 1, '<?= addslashes($cabania['cabania_nombre']) ?>')"
                                                        title="Liberar cabaña">
                                                    <i class="fas fa-unlock"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm"
                                                        onclick="cambiarEstadoCabania(<?= $cabania['id_cabania'] ?>, 0, '<?= addslashes($cabania['cabania_nombre']) ?>')"
                                                        title="Desactivar">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php else: ?>
                                                <!-- Cabaña inactiva: solo puede activar -->
                                                <button class="btn btn-outline-success btn-sm"
                                                        onclick="cambiarEstadoCabania(<?= $cabania['id_cabania'] ?>, 1, '<?= addslashes($cabania['cabania_nombre']) ?>')"
                                                        title="Activar">
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

        <!-- Paginación estilo moderno -->
        <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
            <div class="card-footer bg-white border-top py-3">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <?php 
                        $perPage = (int) ($_GET['per_page'] ?? 10);
                        $start = (($pagination['current_page'] - 1) * $perPage) + 1;
                        $end = min($pagination['current_page'] * $perPage, $pagination['total']);
                        ?>
                        <span class="text-muted small">
                            Mostrando <?= $start ?> a <?= $end ?> de <?= $pagination['total'] ?> entradas
                        </span>
                    </div>
                    <div class="col-sm-6">
                        <nav aria-label="Paginación" class="d-flex justify-content-end">
                            <ul class="pagination pagination-sm mb-0">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) ?>">Anterior</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= min(5, $pagination['total_pages']); $i++): ?>
                                    <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])) ?>">Siguiente</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript para funcionalidades -->
<script>
function cambiarEstadoCabania(id, nuevoEstado, nombre) {
    let accion, mensaje, color;
    
    switch(nuevoEstado) {
        case 1:
            accion = 'activar';
            mensaje = 'La cabaña estará disponible para reservas';
            color = '#28a745';
            break;
        case 2:
            accion = 'marcar como ocupada';
            mensaje = 'La cabaña se marcará como ocupada por huéspedes';
            color = '#ffc107';
            break;
        case 0:
            accion = 'desactivar';
            mensaje = 'La cabaña no estará disponible para reservas';
            color = '#dc3545';
            break;
        default:
            accion = 'cambiar estado';
            mensaje = '';
            color = '#6c757d';
    }
    
    console.log('Cambiando estado:', { id, nuevoEstado, nombre, accion });
    
    // Usar SweetAlert si está disponible, sino usar confirm simple
    const confirmar = typeof Swal !== 'undefined' ? 
        Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} cabaña?`,
            text: `¿Está seguro que desea ${accion} la cabaña "${nombre}"? ${mensaje}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: color
        }).then(result => result.isConfirmed) :
        Promise.resolve(confirm(`¿Está seguro que desea ${accion} la cabaña "${nombre}"?`));
    
    confirmar.then(confirmed => {
        if (confirmed) {
            const url = `<?= url('/cabanias') ?>/${id}/estado`;
            console.log('URL de petición:', url);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({estado: nuevoEstado})
            })
            .then(response => {
                console.log('Respuesta recibida:', response.status, response.statusText);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                
                if (data.success) {
                    // Usar SweetAlert para éxito si está disponible
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: `Cabaña ${accion}da correctamente`,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        alert(`Cabaña ${accion}da correctamente`);
                        location.reload();
                    }
                } else {
                    const errorMsg = 'Error al cambiar el estado: ' + (data.message || 'Error desconocido');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', errorMsg, 'error');
                    } else {
                        alert(errorMsg);
                    }
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                const errorMsg = 'Error al cambiar el estado de la cabaña: ' + error.message;
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', errorMsg, 'error');
                } else {
                    alert(errorMsg);
                }
            });
        }
    });
}

// Función para exportar cabañas a Excel (.xlsx)
function exportarCabanias() {
    const tabla = document.getElementById('tablaCabanias');
    if (!tabla) {
        alert('No se encontró la tabla para exportar');
        return;
    }
    
    // Extraer datos de la tabla
    const filas = tabla.querySelectorAll('tbody tr');
    if (filas.length === 0) {
        alert('No hay datos para exportar');
        return;
    }
    
    // Preparar datos para Excel
    const datos = [];
    
    // Agregar encabezados
    datos.push(['Código', 'Cabaña', 'Capacidad', 'Habitaciones', 'Baños', 'Precio', 'Estado']);
    
    // Extraer datos de cada fila
    filas.forEach(fila => {
        const celdas = fila.querySelectorAll('td');
        if (celdas.length >= 7) {
            const fila_datos = [
                celdas[0].textContent.trim(), // Código
                celdas[1].querySelector('.fw-medium') ? celdas[1].querySelector('.fw-medium').textContent.trim() : celdas[1].textContent.trim(), // Cabaña
                celdas[2].textContent.trim(), // Capacidad
                celdas[3].textContent.trim(), // Habitaciones
                celdas[4].textContent.trim(), // Baños
                celdas[5].querySelector('.fw-medium') ? celdas[5].querySelector('.fw-medium').textContent.trim() : celdas[5].textContent.trim(), // Precio
                celdas[6].textContent.trim().replace(/\s+/g, ' ') // Estado
            ];
            
            // Limpiar datos
            const fila_limpia = fila_datos.map(dato => 
                dato.replace(/[\n\r\t]/g, ' ').replace(/\s+/g, ' ').trim()
            );
            
            datos.push(fila_limpia);
        }
    });
    
    // Verificar si SheetJS está disponible
    if (typeof XLSX === 'undefined') {
        // Fallback: exportar como CSV si no está disponible SheetJS
        exportarComoCSV(datos);
        return;
    }
    
    try {
        // Crear libro de trabajo Excel
        const libro = XLSX.utils.book_new();
        const hoja = XLSX.utils.aoa_to_sheet(datos);
        
        // Configurar ancho de columnas
        hoja['!cols'] = [
            { wch: 12 }, // Código
            { wch: 30 }, // Cabaña
            { wch: 12 }, // Capacidad
            { wch: 15 }, // Habitaciones
            { wch: 10 }, // Baños
            { wch: 15 }, // Precio
            { wch: 12 } // Estado
        ];
        
        // Estilo para encabezados
        const rango = XLSX.utils.decode_range(hoja['!ref']);
        for (let col = rango.s.c; col <= rango.e.c; col++) {
            const celda = XLSX.utils.encode_cell({r: 0, c: col});
            if (!hoja[celda]) continue;
            
            hoja[celda].s = {
                font: { bold: true },
                fill: { fgColor: { rgb: "E3F2FD" } },
                alignment: { horizontal: "center" }
            };
        }
        
        // Agregar la hoja al libro
        XLSX.utils.book_append_sheet(libro, hoja, "Cabañas");
        
        // Generar nombre de archivo con fecha
        const fecha = new Date().toISOString().split('T')[0];
        const nombreArchivo = `cabanias_${fecha}.xlsx`;
        
        // Descargar archivo
        XLSX.writeFile(libro, nombreArchivo);
        
        // Mostrar mensaje de éxito
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¡Exportación exitosa!',
                text: `Se han exportado ${filas.length} registros de cabañas a Excel`,
                icon: 'success',
                timer: 2500,
                showConfirmButton: false
            });
        } else {
            alert(`Exportación exitosa: ${filas.length} registros exportados a Excel`);
        }
        
    } catch (error) {
        console.error('Error al exportar a Excel:', error);
        // Fallback a CSV en caso de error
        exportarComoCSV(datos);
    }
}

// Función fallback para exportar como CSV
function exportarComoCSV(datos) {
    let csv = datos.map(fila => 
        fila.map(celda => '"' + String(celda).replace(/"/g, '""') + '"').join(',')
    ).join('\n');
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    
    const fecha = new Date().toISOString().split('T')[0];
    link.setAttribute('download', `cabanias_${fecha}.csv`);
    
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Exportado como CSV',
            text: 'No se pudo exportar a Excel, se descargó como CSV',
            icon: 'info',
            timer: 2500,
            showConfirmButton: false
        });
    } else {
        alert('Exportado como CSV (Excel no disponible)');
    }
}
</script>
