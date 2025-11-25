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
     * Si recibe reserva_id, filtra por esa reserva específica
     */
    public function index()
    {
        // Verificar autenticación para comentarios públicos
        if (!\App\Core\Auth::check()) {
            $this->redirect('/auth/login', 'Debe iniciar sesión para ver sus comentarios', 'error');
            return;
        }
        
        $userId = $_SESSION['usuario_id'] ?? null;
        if (!$userId) {
            $this->redirect('/auth/login', 'Debe iniciar sesión para ver sus comentarios', 'error');
            return;
        }
        
        $nombre_usuario = $_SESSION["usuario_nombre"];
        $reservaId = $this->get('reserva_id');
        
        // Variables para la vista
        $reserva = null;
        $yaComentado = false;
        $puedeCrearComentario = false;
        
        // Obtener última reserva del usuario para el botón de agregar comentario
        $reservaModel = new \App\Models\Reserva();
        $ultimaReserva = $reservaModel->getUltimaReservaUsuario($userId);
        
        // Si hay reserva_id, obtener información de la reserva
        if ($reservaId) {
            $reserva = $reservaModel->find($reservaId);
            
            if (!$reserva) {
                $this->redirect('/comentarios', 'Reserva no encontrada', 'error');
                return;
            }
            
            // Verificar que es el propietario de la reserva
            if (!$reservaModel->isReservaOwner($reservaId, $userId)) {
                $this->redirect('/comentarios', 'No tiene permisos para ver esta información', 'error');
                return;
            }
            
            // Obtener comentarios de la reserva específica
            $comentarios = $this->comentarioModel->getComentariosByReserva($reservaId);
            $yaComentado = !empty($comentarios);
            
            // El botón de agregar comentario siempre está visible si es la última reserva
            // La restricción de "solo un comentario" se maneja en el método store()
            $puedeCrearComentario = $ultimaReserva && $ultimaReserva['id_reserva'] == $reservaId;
            
            // Para vista de reserva específica, no aplicamos filtros ni paginación
            $data = [
                'title' => 'Comentarios de la Reserva #' . $reservaId,
                'comentarios' => $comentarios,
                'paginacion' => null,
                'filtros_aplicados' => [],
                'reserva_id' => $reservaId,
                'reserva' => $reserva,
                'ya_comentado' => $yaComentado,
                'puede_crear_comentario' => $puedeCrearComentario,
                'ultima_reserva' => $ultimaReserva,
                'isPublicArea' => true
            ];
        } else {
            // Vista general: todos los comentarios del usuario con filtros
            $filtros = [
                'fecha_desde' => $this->get('fecha_desde', ''),
                'fecha_hasta' => $this->get('fecha_hasta', ''),
                'puntuacion' => $this->get('puntuacion', ''),
                'comentario_estado' => $this->get('comentario_estado', '')
            ];

            $pagina = $this->get('pagina', 1);
            $registros_por_pagina = $this->get('registros_por_pagina', 10);

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
                'filtros_aplicados' => $filtros,
                'reserva_id' => null,
                'reserva' => null,
                'ya_comentado' => false,
                'puede_crear_comentario' => false,
                'ultima_reserva' => $ultimaReserva,
                'isPublicArea' => true
            ];
        }

        return $this->render('public/comentarios/listado', $data, 'main');
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
                'error_message' => 'Debe iniciar sesión para crear un comentario.',
                'isPublicArea' => true
            ];
            return $this->render('public/comentarios/formulario', $data, 'main');
        }

        $comentario = null;
        $isEdit = false;
        $reserva_info = null;
        $reserva_id = null;

        // Si se pasa id_reserva por GET, obtener información para nuevo comentario
        if ($id_reserva_get = $this->get('reserva_id')) {
            $reserva_id = (int) $id_reserva_get;
            $reserva_info = $this->comentarioModel->getInformacionReserva($reserva_id);
            if ($reserva_info) {
                // Simular estructura de comentario para el formulario
                $comentario = [
                    'id_reserva' => $reserva_id,
                    'cabania_nombre' => $reserva_info['cabania_nombre'],
                    'reserva_fechainicio' => $reserva_info['reserva_fhinicio'],
                    'reserva_fechafin' => $reserva_info['reserva_fhfin'],
                    'comentario_texto' => '',
                    'comentario_puntuacion' => 5
                ];
            }
        } else {
            // Si no hay reserva_id, obtener la última reserva del usuario
            $usuario_id = $_SESSION["usuario_id"];
            $reservaModel = new \App\Models\Reserva();
            $ultimaReserva = $reservaModel->getUltimaReservaUsuario($usuario_id);
            
            if ($ultimaReserva) {
                $reserva_id = $ultimaReserva['id_reserva'];
                $reserva_info = $this->comentarioModel->getInformacionReserva($reserva_id);
                if ($reserva_info) {
                    $comentario = [
                        'id_reserva' => $reserva_id,
                        'cabania_nombre' => $reserva_info['cabania_nombre'],
                        'reserva_fechainicio' => $reserva_info['reserva_fhinicio'],
                        'reserva_fechafin' => $reserva_info['reserva_fhfin'],
                        'comentario_texto' => '',
                        'comentario_puntuacion' => 5
                    ];
                }
            }
        }

        $data = [
            'title' => 'Nuevo Comentario',
            'comentario' => $comentario,
            'reserva_info' => $reserva_info,
            'reserva_id' => $reserva_id,
            'isEdit' => $isEdit,
            'isPublicArea' => true
        ];

        return $this->render('public/comentarios/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo comentario
     */
    public function store()
    {
        // Verificar autenticación
        if (!isset($_SESSION["usuario_id"])) {
            $this->redirect('/login', 'Debe iniciar sesión para crear comentarios', 'error');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/comentarios', 'Método no permitido', 'error');
            return;
        }

        $usuario_id = $_SESSION["usuario_id"];
        $id_reserva = (int) $this->post('id_reserva');
        $puntuacion = (int) $this->post('puntuacion', 5);
        $texto = trim($this->post('comentario_texto', ''));

        // Validaciones básicas
        if (empty($id_reserva)) {
            $this->redirect('/comentarios/create', 'Debe seleccionar una reserva', 'error');
            return;
        }

        if (empty($texto)) {
            $this->redirect('/comentarios/create?reserva_id=' . $id_reserva, 'Debe ingresar un comentario', 'error');
            return;
        }

        if ($puntuacion < 1 || $puntuacion > 5) {
            $this->redirect('/comentarios/create?reserva_id=' . $id_reserva, 'La puntuación debe ser entre 1 y 5', 'error');
            return;
        }

        // Obtener el ID del huésped desde la reserva
        $reservaModel = new \App\Models\Reserva();
        $huesped_id = $reservaModel->getHuespedIdFromReserva($id_reserva, $usuario_id);
        
        if (!$huesped_id) {
            $this->redirect('/comentarios', 'No se pudo vincular el comentario con la reserva', 'error');
            return;
        }

        // Preparar datos
        $data = [
            'rela_reserva' => $id_reserva,
            'rela_huesped' => $huesped_id,
            'comentario_texto' => $texto,
            'comentario_puntuacion' => $puntuacion,
            'comentario_fechahora' => date('Y-m-d H:i:s'),
            'comentario_estado' => 1 // Pendiente de moderación
        ];

        try {
            $this->comentarioModel->create($data);
            $this->redirect('/comentarios?reserva_id=' . $id_reserva, 'Comentario registrado correctamente. Será visible una vez aprobado.', 'success');
        } catch (\Exception $e) {
            $this->redirect('/comentarios/create?reserva_id=' . $id_reserva, 'Error al guardar el comentario: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Actualizar comentario existente
     */
    public function update($id)
    {
        // Verificar autenticación
        if (!isset($_SESSION["usuario_id"])) {
            $this->redirect('/login', 'Debe iniciar sesión para editar comentarios', 'error');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/comentarios', 'Método no permitido', 'error');
            return;
        }

        $usuario_nombre = $_SESSION["usuario_nombre"];
        
        // Verificar que el comentario pertenece al usuario
        if (!$this->comentarioModel->verificarComentarioUsuario($id, $usuario_nombre)) {
            $this->redirect('/comentarios', 'No tiene permisos para editar este comentario', 'error');
            return;
        }

        $puntuacion = (int) $this->post('puntuacion', 5);
        $texto = trim($this->post('comentario_texto', ''));

        // Validaciones
        if (empty($texto)) {
            $this->redirect('/comentarios/' . $id . '/edit', 'Debe ingresar un comentario', 'error');
            return;
        }

        if ($puntuacion < 1 || $puntuacion > 5) {
            $this->redirect('/comentarios/' . $id . '/edit', 'La puntuación debe ser entre 1 y 5', 'error');
            return;
        }

        // Preparar datos
        $data = [
            'comentario_texto' => $texto,
            'comentario_puntuacion' => $puntuacion,
            'comentario_estado' => 1 // Vuelve a estado pendiente tras edición
        ];

        try {
            $this->comentarioModel->update($id, $data);
            $this->redirect('/comentarios', 'Comentario actualizado correctamente', 'success');
        } catch (\Exception $e) {
            $this->redirect('/comentarios/' . $id . '/edit', 'Error al actualizar el comentario: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        // Verificar autenticación
        if (!isset($_SESSION["usuario_nombre"])) {
            $data = [
                'error_message' => 'Debe iniciar sesión para editar comentarios.',
                'isPublicArea' => true
            ];
            return $this->render('public/comentarios/formulario', $data, 'main');
        }

        $nombre_usuario = $_SESSION["usuario_nombre"];
        
        // Verificar que el comentario pertenece al usuario
        if (!$this->comentarioModel->verificarComentarioUsuario($id, $nombre_usuario)) {
            $data = [
                'error_message' => 'No se encontró el comentario o no tiene permisos para editarlo.',
                'isPublicArea' => true
            ];
            return $this->render('public/comentarios/formulario', $data, 'main');
        }
        
        // Obtener comentario (sin validar usuario ya que ya se validó arriba)
        $comentario = $this->comentarioModel->getComentarioParaEdicion($id, $nombre_usuario);
        
        if (!$comentario) {
            $data = [
                'error_message' => 'No se encontró el comentario.',
                'isPublicArea' => true
            ];
            return $this->render('public/comentarios/formulario', $data, 'main');
        }

        $data = [
            'title' => 'Editar Comentario',
            'comentario' => $comentario,
            'isEdit' => true,
            'isPublicArea' => true
        ];

        return $this->render('public/comentarios/formulario', $data, 'main');
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
     * Eliminar comentario
     */
    public function delete($id)
    {
        // Verificar autenticación
        if (!isset($_SESSION["usuario_nombre"])) {
            $this->redirect('/login', 'Debe iniciar sesión para eliminar comentarios', 'error');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/comentarios', 'Método no permitido', 'error');
            return;
        }

        $usuario_nombre = $_SESSION["usuario_nombre"];
        
        // Verificar que el comentario pertenece al usuario
        if (!$this->comentarioModel->verificarComentarioUsuario($id, $usuario_nombre)) {
            $this->redirect('/comentarios', 'No tiene permisos para eliminar este comentario', 'error');
            return;
        }

        if ($this->comentarioModel->softDelete($id)) {
            $this->redirect('/comentarios', 'Comentario eliminado exitosamente', 'success');
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