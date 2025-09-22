<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Servicio;

/**
 * Controlador para la gestión de servicios
 */
class ServiciosController extends Controller
{
    protected $servicioModel;

    public function __construct()
    {
        parent::__construct();
        $this->servicioModel = new Servicio();
    }

    /**
     * Listar servicios (admin)
     */
    public function index()
    {
        if (!$this->hasPermission('servicios')) {
            return $this->view->error(403);
        }

        // Obtener filtros desde la request
        $filtros = [
            'servicio_nombre' => $this->get('servicio_nombre', ''),
            'servicio_descripcion' => $this->get('servicio_descripcion', ''),
            'rela_tiposervicio' => $this->get('rela_tiposervicio', ''),
            'servicio_estado' => $this->get('servicio_estado', '')
        ];

        $pagina = (int) $this->get('pagina', 1);
        $registros_por_pagina = (int) $this->get('registros_por_pagina', 10);

        // Usar método del modelo que reemplaza el SQL de la View
        $resultado = $this->servicioModel->getWithFilters($filtros, $pagina, $registros_por_pagina);
        
        // Obtener tipos de servicio para el select
        $tipos_servicio = $this->servicioModel->getTiposServicio();

        $data = [
            'title' => 'Gestión de Servicios',
            'servicios' => $resultado['data'],
            'paginacion' => [
                'pagina_actual' => $pagina,
                'registros_por_pagina' => $registros_por_pagina,
                'total_registros' => $resultado['totalRecords'],
                'total_paginas' => $resultado['totalPages'],
                'desde' => (($pagina - 1) * $registros_por_pagina) + 1,
                'hasta' => min($pagina * $registros_por_pagina, $resultado['totalRecords'])
            ],
            'tipos_servicio' => $tipos_servicio,
            'filtros_aplicados' => $filtros,
            'stats' => $this->servicioModel->getStats()
        ];

        return $this->render('admin/operaciones/servicios/listado', $data);
    }

    /**
     * Mostrar formulario de nuevo servicio
     */
    public function create()
    {
        if (!$this->hasPermission('servicios')) {
            return $this->view->error(403);
        }

        if ($this->isPost()) {
            $data = [
                'servicio_nombre' => trim($this->post('servicio_nombre')),
                'servicio_descripcion' => trim($this->post('servicio_descripcion')),
                'servicio_precio' => (float) $this->post('servicio_precio', 0),
                'rela_tiposervicio' => (int) $this->post('rela_tiposervicio'),
                'servicio_estado' => 1
            ];

            $validation = $this->servicioModel->validate($data);
            if ($validation !== true) {
                $this->redirect('/admin/operaciones/servicios/create', $validation, 'error');
                return;
            }

            if ($this->servicioModel->create($data)) {
                $this->redirect('/servicios', 'Servicio creado exitosamente', 'exito');
            } else {
                $this->redirect('/admin/operaciones/servicios/create', 'Error al crear el servicio', 'error');
            }
            return;
        }

        // Obtener tipos de servicio para el select
        $tipos = $this->servicioModel->getTiposServicio();

        $data = [
            'title' => 'Nuevo Servicio',
            'servicio' => null,
            'tipos' => $tipos
        ];

        return $this->render('admin/operaciones/servicios/formulario', $data);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        if (!$this->hasPermission('servicios')) {
            return $this->view->error(403);
        }

        $servicio = $this->servicioModel->find($id);
        if (!$servicio) {
            $this->redirect('/servicios', 'Servicio no encontrado', 'error');
            return;
        }

        if ($this->isPost()) {
            $data = [
                'servicio_nombre' => trim($this->post('servicio_nombre')),
                'servicio_descripcion' => trim($this->post('servicio_descripcion')),
                'servicio_precio' => (float) $this->post('servicio_precio', 0),
                'rela_tiposervicio' => (int) $this->post('rela_tiposervicio')
            ];

            $validation = $this->servicioModel->validate($data, $id);
            if ($validation !== true) {
                $this->redirect("/admin/operaciones/servicios/{$id}/edit", $validation, 'error');
                return;
            }

            if ($this->servicioModel->update($id, $data)) {
                $this->redirect('/servicios', 'Servicio actualizado exitosamente', 'exito');
            } else {
                $this->redirect("/admin/operaciones/servicios/{$id}/edit", 'Error al actualizar el servicio', 'error');
            }
            return;
        }

        $tipos = $this->servicioModel->getTiposServicio();

        $data = [
            'title' => 'Editar Servicio',
            'servicio' => $servicio,
            'tipos' => $tipos
        ];

        return $this->render('admin/operaciones/servicios/formulario', $data);
    }

    /**
     * Baja lógica
     */
    public function delete($id)
    {
        if (!$this->hasPermission('servicios')) {
            return $this->view->error(403);
        }

        $servicio = $this->servicioModel->find($id);
        if (!$servicio) {
            $this->redirect('/servicios', 'Servicio no encontrado', 'error');
            return;
        }

        // Verificar si está siendo usado
        if ($this->servicioModel->isInUse($id)) {
            $this->redirect('/servicios', 'No se puede eliminar un servicio que está siendo utilizado en consumos', 'error');
            return;
        }

        if ($this->servicioModel->update($id, ['servicio_estado' => 0])) {
            $this->redirect('/servicios', 'Servicio desactivado exitosamente', 'exito');
        } else {
            $this->redirect('/servicios', 'Error al desactivar el servicio', 'error');
        }
    }

    /**
     * Restaurar servicio
     */
    public function restore($id)
    {
        if (!$this->hasPermission('servicios')) {
            return $this->view->error(403);
        }

        $servicio = $this->servicioModel->find($id);
        if (!$servicio) {
            $this->redirect('/servicios', 'Servicio no encontrado', 'error');
            return;
        }

        if ($this->servicioModel->update($id, ['servicio_estado' => 1])) {
            $this->redirect('/servicios', 'Servicio reactivado exitosamente', 'exito');
        } else {
            $this->redirect('/servicios', 'Error al reactivar el servicio', 'error');
        }
    }

    /**
     * Buscar servicios
     */
    public function search()
    {
        if (!$this->hasPermission('servicios')) {
            return $this->view->error(403);
        }

        $query = trim($this->get('q', ''));
        $page = (int) $this->get('page', 1);

        if (empty($query)) {
            $this->redirect('/servicios', 'Ingrese un término de búsqueda', 'warning');
            return;
        }

        $filters = [
            'search' => $query,
            'orderBy' => 'servicio_nombre',
            'orderDir' => 'ASC'
        ];

        $result = $this->servicioModel->getWithFilters($filters, $page);

        $data = [
            'title' => 'Búsqueda de Servicios',
            'servicios' => $result['data'],
            'currentPage' => $page,
            'totalPages' => $result['totalPages'],
            'totalRecords' => $result['totalRecords'],
            'search' => $query
        ];

        return $this->render('admin/operaciones/servicios/busqueda', $data);
    }

    /**
     * Ver detalle de un servicio
     */
    public function show($id)
    {
        if (!$this->hasPermission('servicios')) {
            return $this->view->error(403);
        }

        $servicio = $this->servicioModel->getWithDetails($id);
        if (!$servicio) {
            return $this->view->error(404);
        }

        // Obtener consumos relacionados
        $consumos = $this->servicioModel->getConsumos($id);

        $data = [
            'title' => 'Detalle del Servicio: ' . $servicio['servicio_nombre'],
            'servicio' => $servicio,
            'consumos' => $consumos
        ];

        return $this->render('admin/operaciones/servicios/detalle', $data);
    }

    /**
     * Mostrar estadísticas
     */
    public function stats()
    {
        if (!$this->hasPermission('servicios')) {
            return $this->view->error(403);
        }

        $stats = $this->servicioModel->getStats();

        $data = [
            'title' => 'Estadísticas de Servicios',
            'stats' => $stats
        ];

        return $this->render('admin/operaciones/servicios/stats', $data);
    }
}
