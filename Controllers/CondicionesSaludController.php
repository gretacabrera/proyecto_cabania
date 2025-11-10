<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CondicionSalud;

/**
 * Controlador para el manejo de condiciones de salud
 */
class CondicionesSaludController extends Controller
{
    protected $condicionSaludModel;

    public function __construct()
    {
        parent::__construct();
        $this->condicionSaludModel = new CondicionSalud();
    }

    /**
     * Listar condiciones de salud
     */
    public function index()
    {
        $this->requirePermission('condicionessalud');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'condicionsalud_descripcion' => $this->get('condicionsalud_descripcion'),
            'condicionsalud_estado' => $this->get('condicionsalud_estado')
        ];

        $result = $this->condicionSaludModel->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Condiciones de Salud',
            'condiciones' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/condicionessalud/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nueva condición de salud
     */
    public function create()
    {
        $this->requirePermission('condicionessalud');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nueva Condición de Salud',
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/condicionessalud/formulario', $data, 'main');
    }

    /**
     * Guardar nueva condición de salud
     */
    public function store()
    {
        $this->requirePermission('condicionessalud');

        $data = [
            'condicionsalud_descripcion' => $this->post('condicionsalud_descripcion'),
            'condicionsalud_estado' => 1 // Nueva condición siempre activa
        ];

        if (empty($data['condicionsalud_descripcion'])) {
            $this->redirect('/condicionessalud/create', 'Complete los campos obligatorios', 'error');
        }

        try {
            if ($this->condicionSaludModel->create($data)) {
                $this->redirect('/condicionessalud', 'Condición de salud creada correctamente', 'exito');
            } else {
                $this->redirect('/condicionessalud/create', 'Error al crear la condición de salud', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/condicionessalud/create', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Ver detalle de condición de salud
     */
    public function show($id)
    {
        $this->requirePermission('condicionessalud');

        $condicion = $this->condicionSaludModel->find($id);
        if (!$condicion) {
            return $this->view->error(404);
        }

        // Obtener estadísticas de la condición de salud (si falla, estadísticas vacías)
        try {
            $estadisticas = $this->condicionSaludModel->getStatistics($id);
        } catch (\Exception $e) {
            $estadisticas = [
                'total_huespedes' => 0,
                'huespedes_activos' => 0,
                'total_reservas' => 0,
                'reservas_activas' => 0,
                'porcentaje_uso' => 0
            ];
        }

        $data = [
            'title' => 'Detalle de Condición de Salud',
            'condicion' => $condicion,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/condicionessalud/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario para editar condición
     */
    public function edit($id)
    {
        $this->requirePermission('condicionessalud');

        $condicion = $this->condicionSaludModel->find($id);
        if (!$condicion) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Obtener estadísticas de la condición de salud (si falla, estadísticas vacías)
        try {
            $estadisticas = $this->condicionSaludModel->getStatistics($id);
        } catch (\Exception $e) {
            $estadisticas = [
                'total_huespedes' => 0,
                'huespedes_activos' => 0,
                'total_reservas' => 0,
                'reservas_activas' => 0,
                'porcentaje_uso' => 0
            ];
        }

        $data = [
            'title' => 'Editar Condición de Salud',
            'condicion' => $condicion,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/condicionessalud/formulario', $data, 'main');
    }

    /**
     * Actualizar condición de salud
     */
    public function update($id)
    {
        $this->requirePermission('condicionessalud');

        $condicion = $this->condicionSaludModel->find($id);
        if (!$condicion) {
            return $this->view->error(404);
        }

        $data = [
            'condicionsalud_descripcion' => $this->post('condicionsalud_descripcion'),
            'condicionsalud_estado' => $this->post('condicionsalud_estado')
        ];

        if (empty($data['condicionsalud_descripcion'])) {
            $this->redirect('/condicionessalud/' . $id . '/edit', 'Complete los campos obligatorios', 'error');
        }

        try {
            if ($this->condicionSaludModel->update($id, $data)) {
                $this->redirect('/condicionessalud/' . $id, 'Condición de salud actualizada correctamente', 'exito');
            } else {
                $this->redirect('/condicionessalud/' . $id . '/edit', 'Error al actualizar la condición de salud', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/condicionessalud/' . $id . '/edit', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de condición de salud
     */
    public function delete($id)
    {
        $this->requirePermission('condicionessalud');

        $condicion = $this->condicionSaludModel->find($id);
        if (!$condicion) {
            return $this->view->error(404);
        }

        if ($this->condicionSaludModel->softDelete($id, 'condicionsalud_estado')) {
            $this->redirect('/condicionessalud', 'Condición de salud eliminada correctamente', 'exito');
        } else {
            $this->redirect('/condicionessalud', 'Error al eliminar la condición de salud', 'error');
        }
    }

    /**
     * Restaurar condición de salud
     */
    public function restore($id)
    {
        $this->requirePermission('condicionessalud');

        if ($this->condicionSaludModel->restore($id, 'condicionsalud_estado')) {
            $this->redirect('/condicionessalud', 'Condición de salud restaurada correctamente', 'exito');
        } else {
            $this->redirect('/condicionessalud', 'Error al restaurar la condición de salud', 'error');
        }
    }

    /**
     * Cambiar estado (AJAX)
     */
    public function cambiarEstado($id)
    {
        if (!$this->isAjax()) {
            return $this->view->error(404);
        }

        $this->requirePermission('condicionessalud');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
            return $this->json(['error' => 'Estado inválido'], 400);
        }

        $condicion = $this->condicionSaludModel->find($id);
        if (!$condicion) {
            return $this->json(['error' => 'Condición de salud no encontrada'], 404);
        }

        if ($this->condicionSaludModel->update($id, ['condicionsalud_estado' => $nuevoEstado])) {
            $estadoTexto = $nuevoEstado == 1 ? 'Activo' : 'Inactivo';
            return $this->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'nuevo_estado' => $nuevoEstado,
                'estado_texto' => $estadoTexto
            ]);
        } else {
            return $this->json(['error' => 'Error al actualizar el estado'], 500);
        }
    }

    /**
     * Exportar condiciones de salud a Excel
     */
    public function exportar()
    {
        $this->requirePermission('condicionessalud');

        $filters = [
            'condicionsalud_descripcion' => $this->get('condicionsalud_descripcion'),
            'condicionsalud_estado' => $this->get('condicionsalud_estado')
        ];

        try {
            $result = $this->condicionSaludModel->getAllWithDetailsForExport($filters);
            $datos = $result['data'];

            if (empty($datos)) {
                $this->redirect('/condicionessalud', 'No hay datos para exportar', 'error');
                return;
            }

            require_once 'vendor/autoload.php';
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Encabezados
            $headers = ['Descripción', 'Estado'];
            $sheet->fromArray($headers, null, 'A1');
            
            // Estilo para encabezados
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];
            $sheet->getStyle('A1:B1')->applyFromArray($headerStyle);
            
            // Datos
            $row = 2;
            foreach ($datos as $condicion) {
                $estadoTexto = $condicion['condicionsalud_estado'] == 1 ? 'Activo' : 'Inactivo';
                
                $sheet->setCellValue('A' . $row, $condicion['condicionsalud_descripcion']);
                $sheet->setCellValue('B' . $row, $estadoTexto);
                
                // Estilo alternado para filas
                if ($row % 2 == 0) {
                    $sheet->getStyle('A' . $row . ':B' . $row)->getFill()
                          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                          ->getStartColor()->setRGB('F8F9FA');
                }
                
                $row++;
            }
            
            // Ajustar ancho de columnas
            foreach (range('A', 'B') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Configurar respuesta
            $filename = 'condicionessalud_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            
        } catch (\Exception $e) {
            $this->redirect('/condicionessalud', 'Error al generar el archivo: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar condiciones de salud a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('condicionessalud');

        $filters = [
            'condicionsalud_descripcion' => $this->get('condicionsalud_descripcion'),
            'condicionsalud_estado' => $this->get('condicionsalud_estado')
        ];

        try {
            $result = $this->condicionSaludModel->getAllWithDetailsForExport($filters);
            $datos = $result['data'];

            if (empty($datos)) {
                $this->redirect('/condicionessalud', 'No hay datos para exportar', 'error');
                return;
            }

            require_once 'vendor/autoload.php';
            
            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            // Información del documento
            $pdf->SetCreator('Sistema de Cabañas');
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Condiciones de Salud');
            
            // Configuraciones
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(15, 27, 15);
            $pdf->SetHeaderMargin(5);
            $pdf->SetFooterMargin(10);
            $pdf->SetAutoPageBreak(TRUE, 25);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            
            $pdf->AddPage();
            
            // Título
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 15, 'Listado de Condiciones de Salud', 0, 1, 'C');
            $pdf->Ln(5);
            
            // Información de filtros
            $pdf->SetFont('helvetica', '', 10);
            if (!empty($filters['condicionsalud_descripcion'])) {
                $pdf->Cell(0, 5, 'Filtro descripción: ' . $filters['condicionsalud_descripcion'], 0, 1);
            }
            if ($filters['condicionsalud_estado'] !== '') {
                $estadoTexto = $filters['condicionsalud_estado'] == '1' ? 'Activo' : 'Inactivo';
                $pdf->Cell(0, 5, 'Filtro estado: ' . $estadoTexto, 0, 1);
            }
            $pdf->Cell(0, 5, 'Total registros: ' . count($datos), 0, 1);
            $pdf->Cell(0, 5, 'Generado: ' . date('d/m/Y H:i:s'), 0, 1);
            $pdf->Ln(5);
            
            // Encabezados de tabla
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetFillColor(68, 114, 196);
            $pdf->SetTextColor(255);
            $pdf->Cell(150, 8, 'Descripción', 1, 0, 'C', 1);
            $pdf->Cell(30, 8, 'Estado', 1, 1, 'C', 1);
            
            // Datos
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(0);
            foreach ($datos as $i => $condicion) {
                $fill = ($i % 2 == 0) ? 1 : 0;
                $pdf->SetFillColor(248, 249, 250);
                
                $estadoTexto = $condicion['condicionsalud_estado'] == 1 ? 'Activo' : 'Inactivo';
                
                $pdf->Cell(150, 6, $condicion['condicionsalud_descripcion'], 1, 0, 'L', $fill);
                $pdf->Cell(30, 6, $estadoTexto, 1, 1, 'C', $fill);
            }
            
            // Salida del PDF
            $filename = 'condicionessalud_' . date('Y-m-d_H-i-s') . '.pdf';
            $pdf->Output($filename, 'D');
            
        } catch (\Exception $e) {
            $this->redirect('/condicionessalud', 'Error al generar el PDF: ' . $e->getMessage(), 'error');
        }
    }
}
