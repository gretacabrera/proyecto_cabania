<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\EmailService;
use App\Models\Usuario;

/**
 * Controlador para la gestión de usuarios
 */
class UsuariosController extends Controller
{
    protected $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->usuarioModel = new Usuario();
    }

    /**
     * Listar todos los usuarios
     */
    public function index()
    {
        $this->requirePermission('usuarios');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'usuario_nombre' => $this->get('usuario_nombre'),
            'persona_nombre' => $this->get('persona_nombre'),
            'perfil_descripcion' => $this->get('perfil_descripcion'),
            'usuario_estado' => $this->get('usuario_estado')
        ];

        $result = $this->usuarioModel->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Usuarios',
            'usuarios' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/usuarios/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo usuario
     */
    public function create()
    {
        $this->requirePermission('usuarios');

        if ($this->isPost()) {
            return $this->store();
        }

        // Obtener perfiles para el select
        $perfiles = $this->usuarioModel->getPerfiles();

        $data = [
            'title' => 'Nuevo Usuario',
            'perfiles' => $perfiles,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/usuarios/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo usuario
     */
    public function store()
    {
        $this->requirePermission('usuarios');

        // Datos de usuario
        $usuarioData = [
            'usuario_nombre' => $this->post('usuario_nombre'),
            'usuario_contrasenia' => $this->post('usuario_contrasenia'),
            'confirmar_contrasenia' => $this->post('confirmar_contrasenia'),
            'rela_perfil' => $this->post('rela_perfil')
        ];

        // Datos de persona
        $personaData = [
            'persona_nombre' => $this->post('persona_nombre'),
            'persona_apellido' => $this->post('persona_apellido'),
            'persona_fechanac' => $this->post('persona_fechanac'),
            'persona_direccion' => $this->post('persona_direccion'),
            'rela_estadopersona' => 1 // Estado activo por defecto
        ];

        // Validaciones de persona
        if (empty($personaData['persona_nombre'])) {
            $this->redirect('/usuarios/create', 'El nombre de la persona es obligatorio', 'error');
            return;
        }
        if (empty($personaData['persona_apellido'])) {
            $this->redirect('/usuarios/create', 'El apellido de la persona es obligatorio', 'error');
            return;
        }
        if (empty($personaData['persona_fechanac'])) {
            $this->redirect('/usuarios/create', 'La fecha de nacimiento es obligatoria', 'error');
            return;
        }
        if (empty($personaData['persona_direccion'])) {
            $this->redirect('/usuarios/create', 'La dirección es obligatoria', 'error');
            return;
        }

        // Validar datos de usuario
        $errors = $this->usuarioModel->validateUserData($usuarioData);
        if (!empty($errors)) {
            $this->redirect('/usuarios/create', implode('. ', $errors), 'error');
            return;
        }

        try {
            // Iniciar transacción
            $this->usuarioModel->beginTransaction();

            // 1. Crear persona
            $personaModel = new \App\Models\Persona();
            $idPersona = $personaModel->create($personaData);
            if (!$idPersona) {
                throw new \Exception('Error al crear la persona');
            }

            // 2. Crear usuario
            $insertData = [
                'usuario_nombre' => $usuarioData['usuario_nombre'],
                'usuario_contrasenia' => password_hash($usuarioData['usuario_contrasenia'], PASSWORD_DEFAULT),
                'rela_perfil' => $usuarioData['rela_perfil'],
                'rela_persona' => $idPersona,
                'usuario_estado' => 2 // Pendiente de verificación de email
            ];

            $userId = $this->usuarioModel->create($insertData);
            if (!$userId) {
                throw new \Exception('Error al crear el usuario');
            }

            // Commit de la transacción
            $this->usuarioModel->commit();

            // Enviar email de verificación
            $this->sendVerificationEmail($userId);
            $this->redirect('/usuarios', 'Usuario creado exitosamente. Se ha enviado un email de verificación.', 'exito');
        } catch (\Exception $e) {
            // Rollback en caso de error
            $this->usuarioModel->rollback();
            $this->redirect('/usuarios/create', 'Error al crear el usuario: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar usuario específico
     */
    public function show($id)
    {
        $this->requirePermission('usuarios');

        $usuario = $this->usuarioModel->findWithRelations($id);
        if (!$usuario) {
            return $this->view->error(404);
        }

        // Obtener estadísticas del usuario
        $estadisticas = $this->usuarioModel->getStatistics($id);

        $data = [
            'title' => 'Detalle de Usuario',
            'usuario' => $usuario,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/usuarios/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('usuarios');

        if ($this->isPost()) {
            return $this->update($id);
        }

        $usuario = $this->usuarioModel->findWithRelations($id);
        if (!$usuario) {
            $this->redirect('/usuarios', 'Usuario no encontrado', 'error');
            return;
        }

        $perfiles = $this->usuarioModel->getPerfiles();

        // Obtener estadísticas
        $estadisticas = $this->usuarioModel->getStatistics($id);

        $data = [
            'title' => 'Editar Usuario',
            'usuario' => $usuario,
            'perfiles' => $perfiles,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/usuarios/formulario', $data, 'main');
    }

    /**
     * Actualizar usuario existente
     */
    public function update($id)
    {
        $this->requirePermission('usuarios');

        $usuario = $this->usuarioModel->find($id);
        if (!$usuario) {
            $this->redirect('/usuarios', 'Usuario no encontrado', 'error');
            return;
        }

        // Datos de usuario
        $usuarioData = [
            'usuario_nombre' => $this->post('usuario_nombre'),
            'usuario_contrasenia' => $this->post('usuario_contrasenia'),
            'confirmar_contrasenia' => $this->post('confirmar_contrasenia'),
            'rela_perfil' => $this->post('rela_perfil')
        ];

        // Datos de persona
        $personaData = [
            'persona_nombre' => $this->post('persona_nombre'),
            'persona_apellido' => $this->post('persona_apellido'),
            'persona_fechanac' => $this->post('persona_fechanac'),
            'persona_direccion' => $this->post('persona_direccion')
        ];

        // Validaciones de persona
        if (empty($personaData['persona_nombre'])) {
            $this->redirect("/usuarios/{$id}/edit", 'El nombre de la persona es obligatorio', 'error');
            return;
        }
        if (empty($personaData['persona_apellido'])) {
            $this->redirect("/usuarios/{$id}/edit", 'El apellido de la persona es obligatorio', 'error');
            return;
        }
        if (empty($personaData['persona_fechanac'])) {
            $this->redirect("/usuarios/{$id}/edit", 'La fecha de nacimiento es obligatoria', 'error');
            return;
        }
        if (empty($personaData['persona_direccion'])) {
            $this->redirect("/usuarios/{$id}/edit", 'La dirección es obligatoria', 'error');
            return;
        }

        // Validar datos de usuario (para actualización)
        $errors = $this->usuarioModel->validateUserData($usuarioData, true, $id);
        if (!empty($errors)) {
            $this->redirect("/usuarios/{$id}/edit", implode('. ', $errors), 'error');
            return;
        }

        try {
            // Iniciar transacción
            $this->usuarioModel->beginTransaction();

            // 1. Actualizar datos de la persona
            $idPersona = $usuario['rela_persona'];
            $personaModel = new \App\Models\Persona();
            if (!$personaModel->update($idPersona, $personaData)) {
                throw new \Exception('Error al actualizar los datos de la persona');
            }

            // 2. Preparar datos de usuario para actualizar
            $updateData = [
                'usuario_nombre' => $usuarioData['usuario_nombre'],
                'rela_perfil' => $usuarioData['rela_perfil']
            ];

            // Solo actualizar contrasena si se proporciona
            if (!empty($usuarioData['usuario_contrasenia'])) {
                $updateData['usuario_contrasenia'] = password_hash($usuarioData['usuario_contrasenia'], PASSWORD_DEFAULT);
            }

            // 3. Actualizar usuario
            if (!$this->usuarioModel->update($id, $updateData)) {
                throw new \Exception('Error al actualizar el usuario');
            }

            // Commit de la transacción
            $this->usuarioModel->commit();

            $this->redirect('/usuarios', 'Usuario actualizado exitosamente', 'exito');
        } catch (\Exception $e) {
            // Rollback en caso de error
            $this->usuarioModel->rollback();
            $this->redirect("/usuarios/{$id}/edit", 'Error al actualizar el usuario: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica
     */
    public function delete($id)
    {
        $this->requirePermission('usuarios');

        $success = $this->usuarioModel->update($id, ['usuario_estado' => 0]);
        
        if ($success) {
            $this->redirect('/usuarios', 'Usuario eliminado correctamente', 'exito');
        } else {
            $this->redirect('/usuarios', 'Error al eliminar el usuario', 'error');
        }
    }

    /**
     * Restaurar usuario eliminado
     */
    public function restore($id)
    {
        $this->requirePermission('usuarios');

        $success = $this->usuarioModel->update($id, ['usuario_estado' => 1]);
        
        if ($success) {
            $this->redirect('/usuarios', 'Usuario restaurado correctamente', 'exito');
        } else {
            $this->redirect('/usuarios', 'Error al restaurar el usuario', 'error');
        }
    }

    /**
     * Cambiar estado del usuario via AJAX
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('usuarios');

        if (!$this->isPost()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $nuevoEstado = (int) $this->post('estado');
        $usuario = $this->usuarioModel->find($id);
        
        if (!$usuario) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            return;
        }

        $success = $this->usuarioModel->update($id, ['usuario_estado' => $nuevoEstado]);
        
        header('Content-Type: application/json');
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Estado actualizado correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar el estado'
            ]);
        }
    }

    /**
     * Exportar usuarios a Excel
     */
    public function exportar()
    {
        $this->requirePermission('usuarios');

        $filters = [
            'usuario_nombre' => $this->get('usuario_nombre'),
            'persona_nombre' => $this->get('persona_nombre'),
            'perfil_descripcion' => $this->get('perfil_descripcion'),
            'usuario_estado' => $this->get('usuario_estado')
        ];

        $result = $this->usuarioModel->getAllWithDetailsForExport($filters);
        $datos = $result['data'];

        if (empty($datos)) {
            $this->redirect('/usuarios', 'No hay datos para exportar', 'error');
            return;
        }

        require_once 'vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Título
        $sheet->setCellValue('A1', 'Listado de Usuarios');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Encabezados
        $headers = ['Usuario', 'Persona', 'Email', 'Perfil', 'Estado'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '3', $header);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $sheet->getStyle($col . '3')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E2E8F0');
            $col++;
        }

        // Datos
        $row = 4;
        foreach ($datos as $dato) {
            $sheet->setCellValue('A' . $row, $dato['usuario_nombre']);
            $sheet->setCellValue('B' . $row, trim(($dato['persona_nombre'] ?? '') . ' ' . ($dato['persona_apellido'] ?? '')));
            $sheet->setCellValue('C' . $row, $dato['persona_email'] ?? '');
            $sheet->setCellValue('D' . $row, $dato['perfil_descripcion'] ?? '');
            
            $estado = $dato['usuario_estado'] == 1 ? 'Activo' : ($dato['usuario_estado'] == 2 ? 'Pendiente' : 'Inactivo');
            $sheet->setCellValue('E' . $row, $estado);
            $row++;
        }

        // Ajustar columnas
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Descargar
        $filename = 'usuarios_' . date('Y-m-d_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Exportar usuarios a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('usuarios');

        $filters = [
            'usuario_nombre' => $this->get('usuario_nombre'),
            'persona_nombre' => $this->get('persona_nombre'),
            'perfil_descripcion' => $this->get('perfil_descripcion'),
            'usuario_estado' => $this->get('usuario_estado')
        ];

        $result = $this->usuarioModel->getAllWithDetailsForExport($filters);
        $datos = $result['data'];

        if (empty($datos)) {
            $this->redirect('/usuarios', 'No hay datos para exportar', 'error');
            return;
        }

        require_once 'vendor/autoload.php';

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator('Sistema de Gestión');
        $pdf->SetAuthor('Sistema');
        $pdf->SetTitle('Listado de Usuarios');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();

        // Título
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Listado de Usuarios', 0, 1, 'C');
        $pdf->Ln(5);

        // Tabla
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(40, 7, 'Usuario', 1, 0, 'C');
        $pdf->Cell(50, 7, 'Persona', 1, 0, 'C');
        $pdf->Cell(50, 7, 'Email', 1, 0, 'C');
        $pdf->Cell(30, 7, 'Perfil', 1, 0, 'C');
        $pdf->Cell(20, 7, 'Estado', 1, 1, 'C');

        $pdf->SetFont('helvetica', '', 9);
        foreach ($datos as $dato) {
            $pdf->Cell(40, 6, $dato['usuario_nombre'], 1, 0, 'L');
            $pdf->Cell(50, 6, substr(trim(($dato['persona_nombre'] ?? '') . ' ' . ($dato['persona_apellido'] ?? '')), 0, 25), 1, 0, 'L');
            $pdf->Cell(50, 6, substr($dato['persona_email'] ?? '', 0, 25), 1, 0, 'L');
            $pdf->Cell(30, 6, substr($dato['perfil_descripcion'] ?? '', 0, 15), 1, 0, 'L');
            
            $estado = $dato['usuario_estado'] == 1 ? 'Activo' : ($dato['usuario_estado'] == 2 ? 'Pendiente' : 'Inactivo');
            $pdf->Cell(20, 6, $estado, 1, 1, 'C');
        }

        $pdf->Output('usuarios_' . date('Y-m-d_His') . '.pdf', 'D');
        exit;
    }

    /**
     * Ver perfil de usuario
     */
    public function profile($id = null)
    {
        // Si no se proporciona ID, mostrar perfil del usuario actual
        if (!$id) {
            $id = $_SESSION['usuario_id'] ?? null;
        }

        if (!$id) {
            $this->redirect('/login', 'Debe iniciar sesión', 'error');
        }

        $usuario = $this->usuarioModel->findWithRelations($id);
        if (!$usuario) {
            $this->redirect('/usuarios', 'Usuario no encontrado', 'error');
        }

        $data = [
            'title' => 'Perfil de Usuario',
            'usuario' => $usuario,
            'isAdminArea' => true
        ];

        return $this->render('admin/seguridad/usuarios/perfil', $data);
    }

    /**
     * Enviar email de verificación al usuario
     */
    private function sendVerificationEmail($userId)
    {
        try {
            // Obtener datos del usuario para el email
            $userData = $this->usuarioModel->getUserForEmail($userId);
            
            if (!$userData || !$userData['persona_email']) {
                error_log("No se pudo obtener el email del usuario ID: $userId");
                return false;
            }
            
            // Generar token de verificación
            $verificationToken = $this->usuarioModel->generateVerificationToken($userId);
            
            if (!$verificationToken) {
                error_log("Error al generar token de verificación para usuario ID: $userId");
                return false;
            }
            
            // Preparar datos para el email
            $recipientEmail = $userData['persona_email'];
            $recipientName = trim($userData['persona_nombre'] . ' ' . $userData['persona_apellido']);
            $userName = $userData['usuario_nombre'];
            
            // Enviar email usando EmailService
            $emailService = new EmailService();
            $result = $emailService->sendUserVerificationEmail(
                $recipientEmail,
                $recipientName,
                $userName,
                $verificationToken
            );
            
            if ($result['success']) {
                error_log("Email de verificación enviado exitosamente a: $recipientEmail (Usuario: $userName)");
                return true;
            } else {
                error_log("Error al enviar email de verificación: " . $result['message']);
                return false;
            }
            
        } catch (\Exception $e) {
            error_log("Excepción al enviar email de verificación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar token de verificación de email
     */
    public function verify()
    {
        $token = $this->get('token');
        
        if (!$token) {
            $this->redirect('/', 'Token de verificación no válido', 'error');
            return;
        }
        
        $usuario = $this->usuarioModel->verifyToken($token);
        
        if ($usuario) {
            $nombreCompleto = trim($usuario['persona_nombre'] . ' ' . $usuario['persona_apellido']);
            $this->redirect('/', "¡Email verificado exitosamente! Bienvenido/a $nombreCompleto", 'exito');
        } else {
            $this->redirect('/', 'Token de verificación inválido o expirado', 'error');
        }
    }

    /**
     * Reenviar email de verificación
     */
    public function resendVerification($id = null)
    {
        if (!$this->hasPermission('usuarios') && !$id) {
            return $this->view->error(403);
        }
        
        // Si no se proporciona ID, usar el usuario actual
        if (!$id) {
            $id = $_SESSION['usuario_id'] ?? null;
        }
        
        if (!$id) {
            $this->redirect('/login', 'Debe iniciar sesión', 'error');
            return;
        }
        
        // Verificar si el email ya está verificado
        if ($this->usuarioModel->isEmailVerified($id)) {
            $this->redirect('/usuarios', 'El email ya está verificado', 'info');
            return;
        }
        
        // Reenviar email de verificación
        if ($this->sendVerificationEmail($id)) {
            $this->redirect('/usuarios', 'Email de verificación reenviado exitosamente', 'exito');
        } else {
            $this->redirect('/usuarios', 'Error al reenviar el email de verificación', 'error');
        }
    }
}
