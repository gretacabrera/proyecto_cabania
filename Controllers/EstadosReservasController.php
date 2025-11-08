<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\EstadoReserva;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use TCPDF;

/**
 * Controlador para el manejo de Estados de Reservas
 */
class EstadosReservasController extends Controller
{
    protected $estadoReservaModel;

    public function __construct()
    {
        parent::__construct();
        $this->estadoReservaModel = new EstadoReserva();
    }

    /**
     * Listar estados de reservas
     */
    public function index()
    {
        $this->requirePermission('estados-reservas');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'estadoreserva_descripcion' => $this->get('estadoreserva_descripcion'),
            'estadoreserva_estado' => $this->get('estadoreserva_estado')
        ];

        $result = $this->estadoReservaModel->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Estados de Reservas',
            'estados' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estados-reservas/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de creación o procesar creación
     */
    public function create()
    {
        $this->requirePermission('estados-reservas');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nuevo Estado de Reserva',
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estados-reservas/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo estado de reserva
     */
    protected function store()
    {
        try {
            $data = [
                'estadoreserva_descripcion' => trim($this->post('estadoreserva_descripcion')),
                'estadoreserva_estado' => 1
            ];

            if (empty($data['estadoreserva_descripcion'])) {
                $this->redirect('/estados_reservas/create', 'La descripción es obligatoria', 'error');
                return;
            }

            if (strlen($data['estadoreserva_descripcion']) > 45) {
                $this->redirect('/estados_reservas/create', 'La descripción no puede exceder 45 caracteres', 'error');
                return;
            }

            $existeEstado = $this->estadoReservaModel->findAll("estadoreserva_descripcion = '" . addslashes($data['estadoreserva_descripcion']) . "'");
            if (!empty($existeEstado)) {
                $this->redirect('/estados_reservas/create', 'Ya existe un estado de reserva con esa descripción', 'error');
                return;
            }

            $id = $this->estadoReservaModel->create($data);

            if ($id) {
                $this->redirect('/estados_reservas', 'Estado de reserva creado exitosamente', 'success');
            } else {
                $this->redirect('/estados_reservas/create', 'Error al crear el estado de reserva', 'error');
            }
        } catch (\Exception $e) {
            error_log("Error en EstadosReservasController::store: " . $e->getMessage());
            $this->redirect('/estados_reservas/create', 'Error interno del servidor', 'error');
        }
    }

    /**
     * Mostrar detalle de estado de reserva específico
     */
    public function show($id)
    {
        $this->requirePermission('estados-reservas');

        $estadoReserva = $this->estadoReservaModel->find($id);
        if (!$estadoReserva) {
            return $this->view->error(404);
        }

        $data = [
            'title' => 'Detalle del Estado de Reserva',
            'estado' => $estadoReserva,
            'estadisticas' => $this->estadoReservaModel->getStatistics($id),
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estados-reservas/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición o procesar actualización
     */
    public function edit($id)
    {
        $this->requirePermission('estados-reservas');

        $estadoReserva = $this->estadoReservaModel->find($id);
        if (!$estadoReserva) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        $reservasCount = $this->estadoReservaModel->getReservasCountByEstado($id);

        $data = [
            'title' => 'Editar Estado de Reserva',
            'estado' => $estadoReserva,
            'reservas_count' => $reservasCount,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estados-reservas/formulario', $data, 'main');
    }

    /**
     * Actualizar estado de reserva existente
     */
    protected function update($id)
    {
        try {
            $estadoReserva = $this->estadoReservaModel->find($id);
            if (!$estadoReserva) {
                $this->redirect('/estados_reservas', 'Estado de reserva no encontrado', 'error');
                return;
            }

            $data = [
                'estadoreserva_descripcion' => trim($this->post('estadoreserva_descripcion'))
            ];

            if (empty($data['estadoreserva_descripcion'])) {
                $this->redirect('/estados_reservas/' . $id . '/edit', 'La descripción es obligatoria', 'error');
                return;
            }

            if (strlen($data['estadoreserva_descripcion']) > 45) {
                $this->redirect('/estados_reservas/' . $id . '/edit', 'La descripción no puede exceder 45 caracteres', 'error');
                return;
            }

            $existeEstado = $this->estadoReservaModel->findAll("estadoreserva_descripcion = '" . addslashes($data['estadoreserva_descripcion']) . "' AND id_estadoreserva != " . (int)$id);
            if (!empty($existeEstado)) {
                $this->redirect('/estados_reservas/' . $id . '/edit', 'Ya existe otro estado de reserva con esa descripción', 'error');
                return;
            }

            $success = $this->estadoReservaModel->update($id, $data);

            if ($success) {
                $this->redirect('/estados_reservas', 'Estado de reserva actualizado exitosamente', 'success');
            } else {
                $this->redirect('/estados_reservas/' . $id . '/edit', 'Error al actualizar el estado de reserva', 'error');
            }
        } catch (\Exception $e) {
            error_log("Error en EstadosReservasController::update: " . $e->getMessage());
            $this->redirect('/estados_reservas/' . $id . '/edit', 'Error interno del servidor', 'error');
        }
    }

    /**
     * Cambiar estado mediante AJAX
     */
    public function cambiarEstado($id)
    {
        // Log para debugging
        error_log("EstadosReservas - Petición recibida en cambiarEstado - ID: $id");
        error_log("EstadosReservas - Método HTTP: " . $_SERVER['REQUEST_METHOD']);
        error_log("EstadosReservas - URL completa: " . $_SERVER['REQUEST_URI']);
        
        $this->requirePermission('estados-reservas');
        
        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            error_log("EstadosReservas - Error: No es una petición AJAX");
            error_log("EstadosReservas - Headers: " . json_encode(getallheaders()));
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        try {
            // Verificar que el estado de reserva existe
            $estadoReserva = $this->estadoReservaModel->find($id);
            if (!$estadoReserva) {
                error_log("EstadosReservas - Error: Estado de reserva no encontrado - ID: $id");
                return $this->json(['success' => false, 'message' => 'Estado de reserva no encontrado'], 404);
            }

            // Obtener el nuevo estado del cuerpo de la petición
            $input = json_decode(file_get_contents('php://input'), true);
            error_log("EstadosReservas - Datos recibidos: " . json_encode($input));
            
            $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

            if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
                error_log("EstadosReservas - Error: Estado inválido - Estado: " . var_export($nuevoEstado, true));
                return $this->json(['success' => false, 'message' => 'Estado inválido. Estados válidos: 0 (inactivo), 1 (activo)'], 400);
            }

            // Verificar si se puede cambiar el estado (solo cuando se desactiva)
            if ($nuevoEstado == 0) {
                $reservasCount = $this->estadoReservaModel->getReservasCountByEstado($id);
                if ($reservasCount > 0) {
                    error_log("EstadosReservas - Error: No se puede desactivar - tiene $reservasCount reservas asociadas");
                    $mensaje = $reservasCount === 1 
                        ? 'No se puede desactivar este estado porque hay 1 reserva que lo utiliza actualmente.' 
                        : "No se puede desactivar este estado porque hay {$reservasCount} reservas que lo utilizan actualmente.";
                    
                    return $this->json([
                        'success' => false, 
                        'message' => $mensaje,
                        'reservas_count' => $reservasCount
                    ], 400);
                }
            }

            // Actualizar el estado
            $success = $this->estadoReservaModel->update($id, ['estadoreserva_estado' => $nuevoEstado]);

            if ($success) {
                $mensaje = $nuevoEstado == 1 ? 'Estado de reserva activado correctamente' : 'Estado de reserva desactivado correctamente';
                error_log("EstadosReservas - Estado cambiado exitosamente - ID: $id, Nuevo estado: $nuevoEstado");
                return $this->json([
                    'success' => true,
                    'message' => $mensaje,
                    'nuevo_estado' => $nuevoEstado
                ]);
            } else {
                error_log("EstadosReservas - Error al actualizar el estado en la base de datos - ID: $id");
                return $this->json(['success' => false, 'message' => 'Error al cambiar el estado'], 500);
            }
        } catch (\Exception $e) {
            error_log("Error en EstadosReservasController::cambiarEstado: " . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Exportar a Excel
     */
    public function exportar()
    {
        $this->requirePermission('estados-reservas');

        try {
            $filters = [
                'estadoreserva_descripcion' => $this->get('estadoreserva_descripcion'),
                'estadoreserva_estado' => $this->get('estadoreserva_estado')
            ];

            $result = $this->estadoReservaModel->getAllWithDetailsForExport($filters);
            $datos = $result['data'];

            if (empty($datos)) {
                $this->redirect('/estados_reservas', 'No hay datos para exportar', 'error');
                return;
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Estados de Reservas');

            $headers = ['Descripción', 'Estado'];
            $sheet->fromArray($headers, null, 'A1');

            $headerRange = 'A1:B1';
            $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
            $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row = 2;
            foreach ($datos as $estado) {
                $estadoTexto = $estado['estadoreserva_estado'] == 1 ? 'Activo' : 'Inactivo';
                
                $sheet->setCellValue('A' . $row, $estado['estadoreserva_descripcion']);
                $sheet->setCellValue('B' . $row, $estadoTexto);
                $row++;
            }

            foreach (range('A', 'B') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $filename = 'estados_reservas_' . date('Y-m-d_H-i-s') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            error_log("Error en EstadosReservasController::exportar: " . $e->getMessage());
            $this->redirect('/estados_reservas', 'Error al generar el archivo Excel', 'error');
        }
    }

    /**
     * Exportar a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('estados-reservas');

        try {
            $filters = [
                'estadoreserva_descripcion' => $this->get('estadoreserva_descripcion'),
                'estadoreserva_estado' => $this->get('estadoreserva_estado')
            ];

            $result = $this->estadoReservaModel->getAllWithDetailsForExport($filters);
            $datos = $result['data'];

            if (empty($datos)) {
                $this->redirect('/estados_reservas', 'No hay datos para exportar', 'error');
                return;
            }

            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Gestión de Cabañas');
            $pdf->SetTitle('Estados de Reservas');
            $pdf->SetSubject('Listado de Estados de Reservas');
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->AddPage();

            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Estados de Reservas', 0, 1, 'C');
            $pdf->Ln(5);

            $pdf->SetFont('helvetica', '', 10);
            if (!empty($filters['estadoreserva_descripcion'])) {
                $pdf->Cell(0, 5, 'Filtro por descripción: ' . $filters['estadoreserva_descripcion'], 0, 1);
            }
            if (isset($filters['estadoreserva_estado']) && $filters['estadoreserva_estado'] !== '') {
                $estadoTexto = $filters['estadoreserva_estado'] == 1 ? 'Activos' : 'Inactivos';
                $pdf->Cell(0, 5, 'Filtro por estado: ' . $estadoTexto, 0, 1);
            }
            $pdf->Cell(0, 5, 'Total de registros: ' . count($datos), 0, 1);
            $pdf->Ln(5);

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(130, 8, 'Descripción', 1, 0, 'C');
            $pdf->Cell(40, 8, 'Estado', 1, 1, 'C');

            $pdf->SetFont('helvetica', '', 9);
            foreach ($datos as $estado) {
                $estadoTexto = $estado['estadoreserva_estado'] == 1 ? 'Activo' : 'Inactivo';
                
                $pdf->Cell(130, 6, $estado['estadoreserva_descripcion'], 1, 0, 'L');
                $pdf->Cell(40, 6, $estadoTexto, 1, 1, 'C');
            }

            $filename = 'estados_reservas_' . date('Y-m-d_H-i-s') . '.pdf';
            $pdf->Output($filename, 'D');

        } catch (\Exception $e) {
            error_log("Error en EstadosReservasController::exportarPdf: " . $e->getMessage());
            $this->redirect('/estados_reservas', 'Error al generar el archivo PDF', 'error');
        }
    }

    /**
     * Eliminar (baja lógica) un estado de reserva
     */
    public function delete($id)
    {
        $this->requirePermission('estados-reservas');

        $estadoReserva = $this->estadoReservaModel->find($id);
        if (!$estadoReserva) {
            return $this->view->error(404);
        }

        // Verificar si se puede cambiar el estado
        if (!$this->estadoReservaModel->canChangeStatus($id, 0)) {
            $this->redirect('/estados_reservas', 'No se puede eliminar este estado porque está siendo usado por reservas activas', 'error');
            return;
        }

        try {
            $data = [
                'estadoreserva_estado' => 0
            ];
            
            $success = $this->estadoReservaModel->update($id, $data);
            
            if ($success) {
                $this->redirect('/estados_reservas', 'Estado de reserva eliminado correctamente', 'success');
            } else {
                $this->redirect('/estados_reservas', 'Error al eliminar el estado de reserva', 'error');
            }
        } catch (\Exception $e) {
            error_log("Error en delete: " . $e->getMessage());
            $this->redirect('/estados_reservas', 'Error al eliminar el estado de reserva', 'error');
        }
    }

    /**
     * Restaurar (alta lógica) un estado de reserva
     */
    public function restore($id)
    {
        $this->requirePermission('estados-reservas');

        $estadoReserva = $this->estadoReservaModel->find($id);
        if (!$estadoReserva) {
            return $this->view->error(404);
        }

        try {
            $data = [
                'estadoreserva_estado' => 1
            ];
            
            $success = $this->estadoReservaModel->update($id, $data);
            
            if ($success) {
                $this->redirect('/estados_reservas', 'Estado de reserva restaurado correctamente', 'success');
            } else {
                $this->redirect('/estados_reservas', 'Error al restaurar el estado de reserva', 'error');
            }
        } catch (\Exception $e) {
            error_log("Error en restore: " . $e->getMessage());
            $this->redirect('/estados_reservas', 'Error al restaurar el estado de reserva', 'error');
        }
    }
}
