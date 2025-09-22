<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Comentario;

/**
 * Controlador para la gestión de comentarios
 */
class ComentariosController extends Controller
{
    protected $comentarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->comentarioModel = new Comentario();
    }

    /**
     * Listar comentarios del usuario actual (para views públicos)
     */
    public function index()
    {
        // Verificar autenticación para comentarios públicos
        if (!isset($_SESSION["usuario_nombre"])) {
            $data = [
                'error_message' => 'Para ver sus comentarios, primero debe iniciar sesión.'
            ];
            return $this->render('public/comentarios/listado', $data);
        }

        // Obtener filtros de la request
        $filtros = [
            'fecha_desde' => $this->get('fecha_desde', ''),
            'fecha_hasta' => $this->get('fecha_hasta', ''),
            'puntuacion' => $this->get('puntuacion', ''),
            'comentario_estado' => $this->get('comentario_estado', '')
        ];

        $pagina = $this->get('pagina', 1);
        $registros_por_pagina = $this->get('registros_por_pagina', 10);
        $nombre_usuario = $_SESSION["usuario_nombre"];

        // Usar el nuevo método del modelo
        $resultado = $this->comentarioModel->getComentariosUsuarioConFiltros(
            $nombre_usuario, 
            $filtros, 
            $pagina, 
            $registros_por_pagina
        );

        $data = [
            'title' => 'Mis Comentarios',
            'comentarios' => $resultado['registros'],
            'paginacion' => $resultado['paginacion'],
            'filtros_aplicados' => $filtros
        ];

        return $this->render('public/comentarios/listado', $data);
    }

    /**
     * Gestionar comentarios (para admin)
     */
    public function admin()
    {
        if (!$this->hasPermission('comentarios')) {
            return $this->view->error(403);
        }

        $page = $this->get('page', 1);
        $search = $this->get('buscar', '');
        $estado = $this->get('estado', '');

        $filters = [
            'search' => $search,
            'estado' => $estado
        ];

        $comentarios = $this->comentarioModel->search($filters, $page);
        $totalPages = $this->comentarioModel->getTotalPages($filters);

        $data = [
            'title' => 'Gestión de Comentarios',
            'comentarios' => $comentarios,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'filters' => $filters
        ];

        return $this->render('admin/comentarios/listado', $data);
    }

    /**
     * Ver detalle del comentario
     */
    public function show($id)
    {
        if (!$this->hasPermission('comentarios')) {
            return $this->view->error(403);
        }

        $comentario = $this->comentarioModel->findWithRelations($id);
        if (!$comentario) {
            return $this->view->error(404);
        }

        $data = [
            'title' => 'Detalle del Comentario',
            'comentario' => $comentario
        ];

        return $this->render('public/comentarios/detalle', $data);
    }

    /**
     * Mostrar formulario de nuevo comentario
     */
    public function create()
    {
        // Verificar autenticación 
        if (!isset($_SESSION["usuario_nombre"])) {
            $data = [
                'error_message' => 'Debe iniciar sesión para crear un comentario.'
            ];
            return $this->render('public/comentarios/formulario', $data);
        }

        $comentario = null;
        $isEdit = false;
        $reserva_info = null;

        // Si se pasa id_reserva, obtener información para nuevo comentario
        if ($id_reserva = $this->get('id_reserva')) {
            $reserva_info = $this->comentarioModel->getInformacionReserva($id_reserva);
            if ($reserva_info) {
                // Simular estructura de comentario para el formulario
                $comentario = [
                    'id_reserva' => $id_reserva,
                    'cabania_nombre' => $reserva_info['cabania_nombre'],
                    'reserva_fechainicio' => $reserva_info['reserva_fechainicio'],
                    'reserva_fechafin' => $reserva_info['reserva_fechafin'],
                    'comentario_texto' => '',
                    'comentario_puntuacion' => 5
                ];
            }
        }

        $data = [
            'title' => 'Nuevo Comentario',
            'comentario' => $comentario,
            'reserva_info' => $reserva_info,
            'isEdit' => $isEdit
        ];

        return $this->render('public/comentarios/formulario', $data);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        // Verificar autenticación
        if (!isset($_SESSION["usuario_nombre"])) {
            $data = [
                'error_message' => 'Debe iniciar sesión para editar comentarios.'
            ];
            return $this->render('public/comentarios/formulario', $data);
        }

        $nombre_usuario = $_SESSION["usuario_nombre"];
        
        // Obtener comentario con verificación de pertenencia al usuario
        $comentario = $this->comentarioModel->getComentarioParaEdicion($id, $nombre_usuario);
        
        if (!$comentario) {
            $data = [
                'error_message' => 'No se encontró el comentario o no tiene permisos para editarlo.'
            ];
            return $this->render('public/comentarios/formulario', $data);
        }

        $data = [
            'title' => 'Editar Comentario',
            'comentario' => $comentario,
            'isEdit' => true
        ];

        return $this->render('public/comentarios/formulario', $data);
    }

    /**
     * Moderar comentario (aprobar/rechazar)
     */
    public function moderate($id)
    {
        if (!$this->hasPermission('comentarios')) {
            return $this->view->error(403);
        }

        if (!$this->isPost()) {
            return $this->view->error(405);
        }

        $comentario = $this->comentarioModel->find($id);
        if (!$comentario) {
            $this->redirect('/comentarios', 'Comentario no encontrado', 'error');
        }

        $accion = $this->post('accion');
        $observaciones = $this->post('observaciones', '');

        switch ($accion) {
            case 'aprobar':
                $estado = 2; // Aprobado
                $message = 'Comentario aprobado';
                break;
            case 'rechazar':
                $estado = 3; // Rechazado
                $message = 'Comentario rechazado';
                break;
            default:
                $this->redirect('/comentarios', 'Acción no válida', 'error');
                return;
        }

        $data = [
            'comentario_estado' => $estado,
            'comentario_observaciones' => $observaciones,
            'comentario_fecha_moderacion' => date('Y-m-d H:i:s')
        ];

        if ($this->comentarioModel->update($id, $data)) {
            $this->redirect('/comentarios', $message, 'exito');
        } else {
            $this->redirect('/comentarios', 'Error al moderar el comentario', 'error');
        }
    }

    /**
     * Baja lógica
     */
    public function delete($id)
    {
        if (!$this->hasPermission('comentarios')) {
            return $this->view->error(403);
        }

        if ($this->comentarioModel->softDelete($id)) {
            $this->redirect('/comentarios', 'Comentario eliminado exitosamente', 'exito');
        } else {
            $this->redirect('/comentarios', 'Error al eliminar el comentario', 'error');
        }
    }

    /**
     * Restaurar comentario
     */
    public function restore($id)
    {
        if (!$this->hasPermission('comentarios')) {
            return $this->view->error(403);
        }

        if ($this->comentarioModel->restore($id)) {
            $this->redirect('/comentarios', 'Comentario restaurado exitosamente', 'exito');
        } else {
            $this->redirect('/comentarios', 'Error al restaurar el comentario', 'error');
        }
    }

    /**
     * Buscar comentarios
     */
    public function search()
    {
        if (!$this->hasPermission('comentarios')) {
            return $this->view->error(403);
        }

        $query = $this->get('q', '');
        $page = $this->get('page', 1);

        if (empty($query)) {
            $this->redirect('/comentarios', 'Ingrese un término de búsqueda', 'warning');
        }

        $filters = ['search' => $query];
        $comentarios = $this->comentarioModel->search($filters, $page);
        $totalPages = $this->comentarioModel->getTotalPages($filters);

        $data = [
            'title' => 'Búsqueda de Comentarios',
            'comentarios' => $comentarios,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $query
        ];

        return $this->render('public/comentarios/busqueda', $data);
    }

    /**
     * Ver comentarios públicos (sin autenticación)
     */
    public function public()
    {
        $page = $this->get('page', 1);
        $comentarios = $this->comentarioModel->getApproved($page);

        $data = [
            'title' => 'Comentarios de Huéspedes',
            'comentarios' => $comentarios,
            'currentPage' => $page
        ];

        return $this->render('public/comentarios/publicos', $data);
    }

    /**
     * Reportar comentario inapropiado
     */
    public function report($id)
    {
        if (!$this->hasPermission('comentarios')) {
            return $this->view->error(403);
        }

        if (!$this->isPost()) {
            return $this->view->error(405);
        }

        $motivo = $this->post('motivo');
        $descripcion = $this->post('descripcion', '');

        if (empty($motivo)) {
            $this->redirect('/comentarios', 'Debe especificar un motivo', 'error');
        }

        $data = [
            'comentario_estado' => 4, // Reportado
            'comentario_motivo_reporte' => $motivo,
            'comentario_descripcion_reporte' => $descripcion,
            'comentario_fecha_reporte' => date('Y-m-d H:i:s')
        ];

        if ($this->comentarioModel->update($id, $data)) {
            $this->redirect('/comentarios', 'Comentario reportado correctamente', 'exito');
        } else {
            $this->redirect('/comentarios', 'Error al reportar el comentario', 'error');
        }
    }
}