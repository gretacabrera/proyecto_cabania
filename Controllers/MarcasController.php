<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Marca;

/**
 * Controlador para el manejo de marcas
 */
class MarcasController extends Controller
{
    protected $marcaModel;

    public function __construct()
    {
        parent::__construct();
        $this->marcaModel = new Marca();
    }

    /**
     * Listar marcas
     */
    public function index()
    {
        $this->requirePermission('marcas');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'marca_descripcion' => $this->get('marca_descripcion'),
            'marca_estado' => $this->get('marca_estado')
        ];

        $result = $this->marcaModel->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Marcas',
            'marcas' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/marcas/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nueva marca
     */
    public function create()
    {
        $this->requirePermission('marcas');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nueva Marca',
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/marcas/formulario', $data, 'main');
    }

    /**
     * Guardar nueva marca
     */
    public function store()
    {
        $this->requirePermission('marcas');

        $data = [
            'marca_descripcion' => $this->post('marca_descripcion'),
            'marca_estado' => 1
        ];

        // Validaciones básicas
        if (empty($data['marca_descripcion'])) {
            $this->redirect('/marcas/create', 'Complete los campos obligatorios', 'error');
        }

        try {
            $id = $this->marcaModel->create($data);
            if ($id) {
                $this->redirect('/marcas', 'Marca creada correctamente', 'exito');
            } else {
                $this->redirect('/marcas/create', 'Error al crear la marca', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/marcas/create', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar marca específica
     */
    public function show($id)
    {
        $this->requirePermission('marcas');

        $marca = $this->marcaModel->find($id);
        if (!$marca) {
            return $this->view->error(404);
        }

        // Obtener estadísticas de la marca
        $estadisticas = $this->marcaModel->getStatistics($id);

        $data = [
            'title' => 'Detalle de Marca',
            'marca' => $marca,
            'statistics' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/marcas/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('marcas');

        $marca = $this->marcaModel->find($id);
        if (!$marca) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Obtener estadísticas para el panel lateral
        $estadisticas = $this->marcaModel->getStatistics($id);

        $data = [
            'title' => 'Editar Marca',
            'marca' => $marca,
            'statistics' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/marcas/formulario', $data, 'main');
    }

    /**
     * Actualizar marca
     */
    public function update($id)
    {
        $this->requirePermission('marcas');

        $marca = $this->marcaModel->find($id);
        if (!$marca) {
            $this->redirect('/marcas', 'Marca no encontrada', 'error');
        }

        $data = [
            'marca_descripcion' => $this->post('marca_descripcion')
        ];

        // Validaciones básicas
        if (empty($data['marca_descripcion'])) {
            $this->redirect('/marcas/' . $id . '/edit', 'Complete los campos obligatorios', 'error');
        }

        try {
            $success = $this->marcaModel->update($id, $data);
            if ($success) {
                $this->redirect('/marcas', 'Marca actualizada correctamente', 'exito');
            } else {
                $this->redirect('/marcas/' . $id . '/edit', 'Error al actualizar la marca', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/marcas/' . $id . '/edit', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de marca
     */
    public function delete($id)
    {
        $this->requirePermission('marcas');

        try {
            $marca = $this->marcaModel->find($id);
            if (!$marca) {
                $this->redirect('/marcas', 'Marca no encontrada', 'error');
            }

            // Verificar si está en uso antes de desactivar
            if ($this->marcaModel->isInUse($id)) {
                $this->redirect('/marcas', 'No se puede desactivar la marca porque está siendo utilizada por productos', 'error');
            }

            $success = $this->marcaModel->update($id, ['marca_estado' => 0]);
            
            if ($success) {
                $this->redirect('/marcas', 'Marca desactivada correctamente', 'exito');
            } else {
                $this->redirect('/marcas', 'Error al desactivar la marca', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/marcas', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Alta lógica de marca
     */
    public function restore($id)
    {
        $this->requirePermission('marcas');

        try {
            $marca = $this->marcaModel->find($id);
            if (!$marca) {
                $this->redirect('/marcas', 'Marca no encontrada', 'error');
            }

            $success = $this->marcaModel->update($id, ['marca_estado' => 1]);
            
            if ($success) {
                $this->redirect('/marcas', 'Marca activada correctamente', 'exito');
            } else {
                $this->redirect('/marcas', 'Error al activar la marca', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/marcas', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Cambiar estado vía AJAX
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('marcas');
        
        header('Content-Type: application/json');

        try {
            $marca = $this->marcaModel->find($id);
            if (!$marca) {
                echo json_encode(['success' => false, 'message' => 'Marca no encontrada']);
                exit;
            }

            // Obtener el nuevo estado del body JSON
            $input = json_decode(file_get_contents('php://input'), true);
            $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

            if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
                echo json_encode(['success' => false, 'message' => 'Estado no válido']);
                exit;
            }

            // Verificar si está en uso antes de desactivar
            if ($nuevoEstado == 0 && $this->marcaModel->isInUse($id)) {
                echo json_encode(['success' => false, 'message' => 'No se puede desactivar la marca porque está siendo utilizada por productos']);
                exit;
            }

            $success = $this->marcaModel->update($id, ['marca_estado' => $nuevoEstado]);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Exportar marcas a Excel
     */
    public function exportar()
    {
        $this->requirePermission('marcas');

        $filters = [
            'marca_descripcion' => $this->get('marca_descripcion'),
            'marca_estado' => $this->get('marca_estado')
        ];

        $result = $this->marcaModel->getAllWithDetailsForExport($filters);
        $datos = $result['data'];

        if (empty($datos)) {
            $this->redirect('/marcas', 'No hay datos para exportar', 'error');
            return;
        }

        try {
            require 'vendor/autoload.php';
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Estilos del encabezado
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]
            ];
            
            // Encabezados
            $sheet->setCellValue('A1', 'Descripción');
            $sheet->setCellValue('B1', 'Estado');
            
            $sheet->getStyle('A1:B1')->applyFromArray($headerStyle);
            $sheet->getRowDimension(1)->setRowHeight(25);
            
            // Datos
            $row = 2;
            foreach ($datos as $marca) {
                $sheet->setCellValue('A' . $row, $marca['marca_descripcion']);
                $sheet->setCellValue('B' . $row, $marca['marca_estado'] == 1 ? 'Activa' : 'Inactiva');
                
                $row++;
            }
            
            // Ajustar anchos de columna
            foreach (range('A', 'B') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Generar archivo
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'marcas_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            $this->redirect('/marcas', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar marcas a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('marcas');

        $filters = [
            'marca_descripcion' => $this->get('marca_descripcion'),
            'marca_estado' => $this->get('marca_estado')
        ];

        $result = $this->marcaModel->getAllWithDetailsForExport($filters);
        $datos = $result['data'];

        if (empty($datos)) {
            $this->redirect('/marcas', 'No hay datos para exportar', 'error');
            return;
        }

        try {
            require 'vendor/autoload.php';
            
            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8');
            
            $pdf->SetCreator('Sistema de Cabañas');
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Marcas');
            
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);
            
            $pdf->AddPage();
            
            // Título
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Listado de Marcas', 0, 1, 'C');
            $pdf->Ln(5);
            
            // Información de filtros
            $pdf->SetFont('helvetica', '', 10);
            if (!empty($filters['marca_descripcion'])) {
                $pdf->Cell(0, 6, 'Descripción: ' . $filters['marca_descripcion'], 0, 1);
            }
            if (isset($filters['marca_estado']) && $filters['marca_estado'] !== '') {
                $estadoTexto = $filters['marca_estado'] == 1 ? 'Activa' : 'Inactiva';
                $pdf->Cell(0, 6, 'Estado: ' . $estadoTexto, 0, 1);
            }
            $pdf->Cell(0, 6, 'Total de registros: ' . count($datos), 0, 1);
            $pdf->Ln(5);
            
            // Tabla
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(68, 114, 196);
            $pdf->SetTextColor(255, 255, 255);
            
            $pdf->Cell(130, 8, 'Descripción', 1, 0, 'C', true);
            $pdf->Cell(50, 8, 'Estado', 1, 1, 'C', true);
            
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(0, 0, 0);
            
            $fill = false;
            foreach ($datos as $marca) {
                $pdf->SetFillColor(240, 240, 240);
                
                $estadoTexto = $marca['marca_estado'] == 1 ? 'Activa' : 'Inactiva';
                
                $pdf->Cell(130, 7, $marca['marca_descripcion'], 1, 0, 'L', $fill);
                $pdf->Cell(50, 7, $estadoTexto, 1, 1, 'C', $fill);
                
                $fill = !$fill;
            }
            
            $filename = 'marcas_' . date('Y-m-d_H-i-s') . '.pdf';
            $pdf->Output($filename, 'D');
            exit;
            
        } catch (\Exception $e) {
            $this->redirect('/marcas', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}
