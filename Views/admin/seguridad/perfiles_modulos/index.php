<?php
/**
 * Vista: Listado de Asignaciones de Módulos a Perfiles
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-lock mr-2"></i>
                        <?= htmlspecialchars($titulo) ?>
                    </h3>
                    <div class="card-tools">
                        <a href="/perfiles-modulos/create" class="btn btn-success btn-sm">
                            <i class="fas fa-plus mr-1"></i>
                            Nueva Asignación
                        </a>
                        <a href="/perfiles-modulos/stats" class="btn btn-info btn-sm">
                            <i class="fas fa-chart-bar mr-1"></i>
                            Estadísticas
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Formulario de búsqueda y filtros -->
                    <form method="GET" class="row mb-4">
                        <div class="col-md-3">
                            <label for="rela_perfil" class="form-label">Filtrar por Perfil:</label>
                            <select name="rela_perfil" id="rela_perfil" class="form-select form-select-sm">
                                <option value="">Todos los perfiles</option>
                                <?php foreach ($perfiles as $perfil): ?>
                                    <option value="<?= $perfil['id_perfil'] ?>" 
                                            <?= ($filters['rela_perfil'] == $perfil['id_perfil']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($perfil['perfil_descripcion']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="rela_modulo" class="form-label">Filtrar por Módulo:</label>
                            <select name="rela_modulo" id="rela_modulo" class="form-select form-select-sm">
                                <option value="">Todos los módulos</option>
                                <?php foreach ($modulos as $modulo): ?>
                                    <option value="<?= $modulo['id_modulo'] ?>" 
                                            <?= ($filters['rela_modulo'] == $modulo['id_modulo']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($modulo['modulo_descripcion']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="perfilmodulo_estado" class="form-label">Estado:</label>
                            <select name="perfilmodulo_estado" id="perfilmodulo_estado" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="1" <?= ($filters['perfilmodulo_estado'] === '1') ? 'selected' : '' ?>>Activos</option>
                                <option value="0" <?= ($filters['perfilmodulo_estado'] === '0') ? 'selected' : '' ?>>Inactivos</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="registros_por_pagina" class="form-label">Mostrar:</label>
                            <select name="registros_por_pagina" id="registros_por_pagina" class="form-select form-select-sm">
                                <option value="10" <?= ($registros_por_pagina == 10) ? 'selected' : '' ?>>10</option>
                                <option value="25" <?= ($registros_por_pagina == 25) ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= ($registros_por_pagina == 50) ? 'selected' : '' ?>>50</option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm mr-2">
                                <i class="fas fa-search mr-1"></i>
                                Filtrar
                            </button>
                            <a href="/perfiles-modulos" class="btn btn-secondary btn-sm">
                                <i class="fas fa-times mr-1"></i>
                                Limpiar
                            </a>
                        </div>
                    </form>

                    <!-- Información de registros -->
                    <?php if ($paginacion['total_registros'] > 0): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            Mostrando <?= $paginacion['inicio'] ?> - <?= $paginacion['fin'] ?> 
                            de <?= $paginacion['total_registros'] ?> registros
                        </div>
                    <?php endif; ?>

                    <!-- Tabla de asignaciones -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="30%">
                                        <i class="fas fa-users mr-1"></i>
                                        Perfil
                                    </th>
                                    <th width="30%">
                                        <i class="fas fa-puzzle-piece mr-1"></i>
                                        Módulo
                                    </th>
                                    <th width="15%" class="text-center">
                                        <i class="fas fa-toggle-on mr-1"></i>
                                        Estado
                                    </th>
                                    <th width="25%" class="text-center">
                                        <i class="fas fa-cogs mr-1"></i>
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($registros)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                            No se encontraron asignaciones con los criterios especificados
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($registros as $registro): ?>
                                        <tr class="<?= $registro['perfilmodulo_estado'] ? '' : 'table-secondary' ?>">
                                            <td>
                                                <strong><?= htmlspecialchars($registro['perfil_descripcion']) ?></strong>
                                                <?php if ($registro['perfil_nombre']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($registro['perfil_nombre']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($registro['modulo_descripcion']) ?></strong>
                                                <?php if ($registro['modulo_nombre']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($registro['modulo_nombre']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($registro['perfilmodulo_estado']): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check mr-1"></i>
                                                        Activo
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-times mr-1"></i>
                                                        Inactivo
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="/perfiles-modulos/<?= $registro['id_perfilmodulo'] ?>/edit" 
                                                       class="btn btn-warning btn-sm" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    <?php if ($registro['perfilmodulo_estado']): ?>
                                                        <?php if (strtolower($registro['perfil_descripcion']) !== 'administrador'): ?>
                                                            <button class="btn btn-danger btn-sm" 
                                                                    data-action="confirmar-eliminacion"
                                                                    data-id="<?= $registro['id_perfilmodulo'] ?>"
                                                                    title="Dar de baja">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <button class="btn btn-danger btn-sm" disabled 
                                                                    title="No se puede eliminar permisos del administrador">
                                                                <i class="fas fa-lock"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    <?php elseif (isset($_SESSION['usuario_perfil']) && strtolower($_SESSION['usuario_perfil']) === 'administrador'): ?>
                                                        <button class="btn btn-success btn-sm" 
                                                                data-action="confirmar-restauracion"
                                                                data-id="<?= $registro['id_perfilmodulo'] ?>"
                                                                title="Restaurar">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <?php if ($paginacion['total_paginas'] > 1): ?>
                        <nav aria-label="Navegación de páginas">
                            <ul class="pagination justify-content-center">
                                <!-- Botón anterior -->
                                <?php if ($paginacion['pagina_actual'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $paginacion['pagina_actual'] - 1])) ?>">
                                            <i class="fas fa-chevron-left"></i>
                                            Anterior
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <!-- Números de página -->
                                <?php
                                $inicio = max(1, $paginacion['pagina_actual'] - 2);
                                $fin = min($paginacion['total_paginas'], $paginacion['pagina_actual'] + 2);
                                ?>

                                <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                    <li class="page-item <?= ($i == $paginacion['pagina_actual']) ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Botón siguiente -->
                                <?php if ($paginacion['pagina_actual'] < $paginacion['total_paginas']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $paginacion['pagina_actual'] + 1])) ?>">
                                            Siguiente
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>