<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\EstadoPersona;

/**
 * Controlador para la gestión de estados de personas
 */
class EstadosPersonasController extends Controller
{
    protected $estadoPersonaModel;

    public function __construct()
    {
        parent::__construct();
        $this->estadoPersonaModel = new EstadoPersona();
    }

    /**
     * Listar todos los estados de personas
     */
    public function index()
    {
        if (!$this->hasPermission('estados_personas')) {
            return $this->view->error(403);
        }

        $page = $this->get('page', 1);
        $search = $this->get('buscar', '');

        if ($search) {
            $estados = $this->estadoPersonaModel->search($search, $page);
            $totalPages = $this->estadoPersonaModel->getTotalPages($search);
        } else {
            $estados = $this->estadoPersonaModel->paginate($page);
            $totalPages = $this->estadoPersonaModel->getTotalPages();
        }

        $data = [
            'title' => 'Gestión de Estados de Personas',
            'estados' => $estados,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search
        ];

        return $this->render('admin/configuracion/estados_personas/listado', $data);
    }

    /**
     * Mostrar formulario de nuevo estado
     */
    public function create()
    {
        if (!$this->hasPermission('estados_personas')) {
            return $this->view->error(403);
        }

        if ($this->isPost()) {
            $data = [
                'estadopersona_nombre' => $this->post('estadopersona_nombre'),
                'estadopersona_descripcion' => $this->post('estadopersona_descripcion'),
                'estadopersona_color' => $this->post('estadopersona_color', '#000000'),
                'estadopersona_estado' => 1
            ];

            if ($this->estadoPersonaModel->create($data)) {
                $this->redirect('/estados-personas', 'Estado creado exitosamente', 'exito');
            } else {
                $this->redirect('/estados-personas/create', 'Error al crear el estado', 'error');
            }
        }

        $data = [
            'title' => 'Nuevo Estado de Persona'
        ];

        return $this->render('admin/configuracion/estados_personas/formulario', $data);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        if (!$this->hasPermission('estados_personas')) {
            return $this->view->error(403);
        }

        $estado = $this->estadoPersonaModel->find($id);
        if (!$estado) {
            $this->redirect('/estados-personas', 'Estado no encontrado', 'error');
        }

        if ($this->isPost()) {
            $data = [
                'estadopersona_nombre' => $this->post('estadopersona_nombre'),
                'estadopersona_descripcion' => $this->post('estadopersona_descripcion'),
                'estadopersona_color' => $this->post('estadopersona_color', '#000000')
            ];

            if ($this->estadoPersonaModel->update($id, $data)) {
                $this->redirect('/estados-personas', 'Estado actualizado exitosamente', 'exito');
            } else {
                $this->redirect("/estados-personas/{$id}/edit", 'Error al actualizar el estado', 'error');
            }
        }

        $data = [
            'title' => 'Editar Estado de Persona',
            'estado' => $estado
        ];

        return $this->render('admin/configuracion/estados_personas/formulario', $data);
    }

    /**
     * Baja lógica
     */
    public function delete($id)
    {
        if (!$this->hasPermission('estados_personas')) {
            return $this->view->error(403);
        }

        // Verificar que no esté siendo usado por personas
        if ($this->estadoPersonaModel->isInUse($id)) {
            $this->redirect('/estados-personas', 'No se puede eliminar un estado que está en uso', 'error');
        }

        if ($this->estadoPersonaModel->softDelete($id)) {
            $this->redirect('/estados-personas', 'Estado eliminado exitosamente', 'exito');
        } else {
            $this->redirect('/estados-personas', 'Error al eliminar el estado', 'error');
        }
    }

    /**
     * Restaurar estado
     */
    public function restore($id)
    {
        if (!$this->hasPermission('estados_personas')) {
            return $this->view->error(403);
        }

        if ($this->estadoPersonaModel->restore($id)) {
            $this->redirect('/estados-personas', 'Estado restaurado exitosamente', 'exito');
        } else {
            $this->redirect('/estados-personas', 'Error al restaurar el estado', 'error');
        }
    }

    /**
     * Cambiar estado
     */
    public function toggleStatus($id)
    {
        if (!$this->hasPermission('estados_personas')) {
            return $this->view->error(403);
        }

        $estado = $this->estadoPersonaModel->find($id);
        if (!$estado) {
            $this->redirect('/estados-personas', 'Estado no encontrado', 'error');
        }

        $newStatus = $estado['estadopersona_estado'] == 1 ? 0 : 1;
        
        if ($this->estadoPersonaModel->update($id, ['estadopersona_estado' => $newStatus])) {
            $message = $newStatus ? 'Estado activado' : 'Estado desactivado';
            $this->redirect('/estados-personas', $message, 'exito');
        } else {
            $this->redirect('/estados-personas', 'Error al cambiar el estado', 'error');
        }
    }
}
