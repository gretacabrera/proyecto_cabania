<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\PerfilModulo;
use App\Models\Perfil;
use App\Models\Modulo;

/**
 * Controlador para gestión de asignaciones entre perfiles y módulos
 */
class PerfilesModulosController extends Controller
{
    protected $perfilModuloModel;
    protected $perfilModel;
    protected $moduloModel;

    public function __construct()
    {
        parent::__construct();
        $this->perfilModuloModel = new PerfilModulo();
        $this->perfilModel = new Perfil();
        $this->moduloModel = new Modulo();
    }

    /**
     * Mostrar listado de asignaciones perfil-módulo
     */
    public function index()
    {
        // Obtener parámetros de filtros
        $filters = [
            'rela_perfil' => $_GET['rela_perfil'] ?? '',
            'rela_modulo' => $_GET['rela_modulo'] ?? '',
            'perfilmodulo_estado' => $_GET['perfilmodulo_estado'] ?? ''
        ];

        // Parámetros de paginación
        $page = intval($_GET['pagina'] ?? 1);
        $perPage = intval($_GET['registros_por_pagina'] ?? 10);

        // Solo administradores pueden ver registros inactivos
        $showInactive = $this->isAdmin();

        // Obtener registros paginados
        $registros = $this->perfilModuloModel->getAllWithDetails($filters, $page, $perPage, $showInactive);
        $totalRegistros = $this->perfilModuloModel->countWithFilters($filters, $showInactive);
        $totalPaginas = ceil($totalRegistros / $perPage);

        // Crear información de paginación
        $paginacion = [
            'pagina_actual' => $page,
            'total_paginas' => $totalPaginas,
            'total_registros' => $totalRegistros,
            'registros_por_pagina' => $perPage,
            'inicio' => ($page - 1) * $perPage + 1,
            'fin' => min($page * $perPage, $totalRegistros)
        ];

        // Obtener listas para filtros
        $perfiles = $this->perfilModel->findAll('perfil_estado = 1', 'perfil_descripcion ASC');
        $modulos = $this->moduloModel->findAll('modulo_estado = 1', 'modulo_descripcion ASC');

        $data = [
            'titulo' => 'Asignaciones de Módulos a Perfiles',
            'registros' => $registros,
            'paginacion' => $paginacion,
            'filters' => $filters,
            'perfiles' => $perfiles,
            'modulos' => $modulos,
            'registros_por_pagina' => $perPage
        ];

        return $this->render('admin/seguridad/perfiles_modulos/index', $data);
    }

    /**
     * Mostrar formulario para crear nueva asignación
     */
    public function create()
    {
        if ($this->isPost()) {
            return $this->store();
        }

        // Obtener perfiles y módulos activos
        $perfiles = $this->perfilModel->findAll('perfil_estado = 1', 'perfil_descripcion ASC');
        $modulos = $this->moduloModel->findAll('modulo_estado = 1', 'modulo_descripcion ASC');

        $data = [
            'titulo' => 'Asignar Módulo a Perfil',
            'perfiles' => $perfiles,
            'modulos' => $modulos
        ];

        return $this->render('admin/seguridad/perfiles_modulos/create', $data);
    }

    /**
     * Procesar creación de nueva asignación
     */
    public function store()
    {
        $perfilId = $_POST['rela_perfil'] ?? null;
        $moduloId = $_POST['rela_modulo'] ?? null;

        // Validaciones
        if (!$perfilId || !$moduloId) {
            return $this->redirect('/perfiles-modulos/create', 'Debe seleccionar un perfil y un módulo', 'error');
        }

        // Verificar que no exista ya la asignación
        if ($this->perfilModuloModel->exists($perfilId, $moduloId, false)) {
            return $this->redirect('/perfiles-modulos/create', 'Esta asignación ya existe y está activa', 'error');
        }

        // Crear la asignación
        $result = $this->perfilModuloModel->createAssignment($perfilId, $moduloId);

        if ($result) {
            return $this->redirect('/perfiles-modulos', 'Asignación creada exitosamente', 'success');
        } else {
            return $this->redirect('/perfiles-modulos/create', 'Error al crear la asignación', 'error');
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $id = intval($id);
        $asignacion = $this->perfilModuloModel->findWithDetails($id);

        if (!$asignacion) {
            return $this->redirect('/perfiles-modulos', 'Asignación no encontrada', 'error');
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Obtener perfiles y módulos activos
        $perfiles = $this->perfilModel->findAll('perfil_estado = 1', 'perfil_descripcion ASC');
        $modulos = $this->moduloModel->findAll('modulo_estado = 1', 'modulo_descripcion ASC');

        $data = [
            'titulo' => 'Editar Asignación',
            'asignacion' => $asignacion,
            'perfiles' => $perfiles,
            'modulos' => $modulos
        ];

        return $this->render('admin/seguridad/perfiles_modulos/edit', $data);
    }

    /**
     * Procesar actualización de asignación
     */
    public function update($id)
    {
        $id = intval($id);
        $perfilId = $_POST['rela_perfil'] ?? null;
        $moduloId = $_POST['rela_modulo'] ?? null;

        // Validaciones
        if (!$perfilId || !$moduloId) {
            return $this->redirect("/perfiles-modulos/{$id}/edit", 'Debe seleccionar un perfil y un módulo', 'error');
        }

        // Verificar que no exista otra asignación igual (excluyendo la actual)
        $existing = $this->perfilModuloModel->findAll(
            "rela_perfil = {$perfilId} AND rela_modulo = {$moduloId} AND id_perfilmodulo != {$id}"
        );

        if (!empty($existing)) {
            return $this->redirect("/perfiles-modulos/{$id}/edit", 'Ya existe una asignación con este perfil y módulo', 'error');
        }

        // Actualizar la asignación
        $data = [
            'rela_perfil' => intval($perfilId),
            'rela_modulo' => intval($moduloId)
        ];

        $result = $this->perfilModuloModel->update($id, $data);

        if ($result) {
            return $this->redirect('/perfiles-modulos', 'Asignación actualizada exitosamente', 'success');
        } else {
            return $this->redirect("/perfiles-modulos/{$id}/edit", 'Error al actualizar la asignación', 'error');
        }
    }

    /**
     * Dar de baja lógica una asignación
     */
    public function delete($id)
    {
        $id = intval($id);
        
        // Verificar si se puede eliminar
        if (!$this->perfilModuloModel->canDelete($id)) {
            return $this->redirect('/perfiles-modulos', 'No se puede eliminar esta asignación por seguridad', 'error');
        }

        $result = $this->perfilModuloModel->softDelete($id);

        if ($result) {
            return $this->redirect('/perfiles-modulos', 'Asignación eliminada exitosamente', 'success');
        } else {
            return $this->redirect('/perfiles-modulos', 'Error al eliminar la asignación', 'error');
        }
    }

    /**
     * Restaurar asignación (quitar baja lógica)
     */
    public function restore($id)
    {
        if (!$this->isAdmin()) {
            return $this->redirect('/perfiles-modulos', 'No tiene permisos para realizar esta acción', 'error');
        }

        $id = intval($id);
        $result = $this->perfilModuloModel->restore($id);

        if ($result) {
            return $this->redirect('/perfiles-modulos', 'Asignación restaurada exitosamente', 'success');
        } else {
            return $this->redirect('/perfiles-modulos', 'Error al restaurar la asignación', 'error');
        }
    }

    /**
     * Ver módulos asignados a un perfil específico
     */
    public function modulesByProfile($perfilId)
    {
        $perfilId = intval($perfilId);
        $perfil = $this->perfilModel->find($perfilId);

        if (!$perfil) {
            return $this->redirect('/perfiles-modulos', 'Perfil no encontrado', 'error');
        }

        $modulos = $this->perfilModuloModel->getModulesByProfile($perfilId);

        $data = [
            'titulo' => "Módulos del Perfil: {$perfil['perfil_descripcion']}",
            'perfil' => $perfil,
            'modulos' => $modulos
        ];

        return $this->render('admin/seguridad/perfiles_modulos/modules_by_profile', $data);
    }

    /**
     * Ver perfiles que tienen asignado un módulo específico
     */
    public function profilesByModule($moduloId)
    {
        $moduloId = intval($moduloId);
        $modulo = $this->moduloModel->find($moduloId);

        if (!$modulo) {
            return $this->redirect('/perfiles-modulos', 'Módulo no encontrado', 'error');
        }

        $perfiles = $this->perfilModuloModel->getProfilesByModule($moduloId);

        $data = [
            'titulo' => "Perfiles con acceso al módulo: {$modulo['modulo_descripcion']}",
            'modulo' => $modulo,
            'perfiles' => $perfiles
        ];

        return $this->render('admin/seguridad/perfiles_modulos/profiles_by_module', $data);
    }

    /**
     * Gestión masiva de permisos para un perfil
     */
    public function managePermissions($perfilId)
    {
        $perfilId = intval($perfilId);
        $perfil = $this->perfilModel->find($perfilId);

        if (!$perfil) {
            return $this->redirect('/perfiles-modulos', 'Perfil no encontrado', 'error');
        }

        if ($this->isPost()) {
            $modulosSeleccionados = $_POST['modulos'] ?? [];
            
            // Sincronizar módulos para el perfil
            $result = $this->perfilModuloModel->syncModulesForProfile($perfilId, $modulosSeleccionados);
            
            if ($result) {
                return $this->redirect('/perfiles-modulos', 'Permisos actualizados exitosamente', 'success');
            } else {
                return $this->redirect("/perfiles-modulos/perfil/{$perfilId}/permisos", 'Error al actualizar permisos', 'error');
            }
        }

        // Obtener todos los módulos disponibles
        $todosLosModulos = $this->moduloModel->findAll('modulo_estado = 1', 'modulo_descripcion ASC');
        
        // Obtener módulos actualmente asignados
        $modulosAsignados = $this->perfilModuloModel->getModulesByProfile($perfilId);
        $modulosAsignadosIds = array_column($modulosAsignados, 'id_modulo');

        $data = [
            'titulo' => "Gestionar Permisos - {$perfil['perfil_descripcion']}",
            'perfil' => $perfil,
            'todosLosModulos' => $todosLosModulos,
            'modulosAsignadosIds' => $modulosAsignadosIds
        ];

        return $this->render('admin/seguridad/perfiles_modulos/manage_permissions', $data);
    }

    /**
     * Buscar asignaciones (AJAX)
     */
    public function search()
    {
        $query = $_GET['q'] ?? '';
        $filters = [
            'rela_perfil' => $_GET['rela_perfil'] ?? '',
            'rela_modulo' => $_GET['rela_modulo'] ?? ''
        ];

        // Para búsquedas básicas, agregar filtro de texto
        if ($query) {
            $registros = $this->perfilModuloModel->getAllWithDetails($filters, 1, 50, $this->isAdmin());
            
            // Filtrar por texto en memoria (podría optimizarse en la consulta SQL)
            $registrosFiltrados = array_filter($registros, function($registro) use ($query) {
                return stripos($registro['perfil_descripcion'], $query) !== false ||
                       stripos($registro['modulo_descripcion'], $query) !== false;
            });
        } else {
            $registrosFiltrados = $this->perfilModuloModel->getAllWithDetails($filters, 1, 50, $this->isAdmin());
        }

        return $this->json(array_values($registrosFiltrados));
    }

    /**
     * Dashboard/estadísticas de asignaciones
     */
    public function stats()
    {
        // Estadísticas básicas
        $totalAsignaciones = $this->perfilModuloModel->count('perfilmodulo_estado = 1');
        $totalPerfiles = $this->perfilModel->count('perfil_estado = 1');
        $totalModulos = $this->moduloModel->count('modulo_estado = 1');

        // Obtener estadísticas usando métodos del modelo
        $perfilesConMasModulos = $this->perfilModuloModel->getProfilesWithMostModules(10);
        $modulosMasAsignados = $this->perfilModuloModel->getMostAssignedModules(10);

        $data = [
            'titulo' => 'Estadísticas de Permisos',
            'totalAsignaciones' => $totalAsignaciones,
            'totalPerfiles' => $totalPerfiles,
            'totalModulos' => $totalModulos,
            'perfilesConMasModulos' => $perfilesConMasModulos,
            'modulosMasAsignados' => $modulosMasAsignados
        ];

        return $this->render('admin/seguridad/perfiles_modulos/stats', $data);
    }

    /**
     * Verificar si el usuario actual es administrador
     */
    private function isAdmin()
    {
        return isset($_SESSION['usuario_perfil']) && 
               strtolower($_SESSION['usuario_perfil']) === 'administrador';
    }
}
