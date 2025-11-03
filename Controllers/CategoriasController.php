<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Categoria;

/**
 * Controlador para la gestión de categorías
 */
class CategoriasController extends Controller
{
    protected $categoriaModel;

    public function __construct()
    {
        parent::__construct();
        $this->categoriaModel = new Categoria();
    }

    /**
     * Listar categorías
     */
    public function index()
    {
        $this->requirePermission('categorias');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'categoria_descripcion' => $this->get('categoria_descripcion'),
            'categoria_estado' => $this->get('categoria_estado')
        ];

        $result = $this->categoriaModel->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Categorías',
            'categorias' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/categorias/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nueva categoría
     */
    public function create()
    {
        $this->requirePermission('categorias');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nueva Categoría',
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/categorias/formulario', $data, 'main');
    }

    /**
     * Guardar nueva categoría
     */
    public function store()
    {
        $this->requirePermission('categorias');

        $data = [
            'categoria_descripcion' => $this->post('categoria_descripcion'),
            'categoria_estado' => 1
        ];

        // Validaciones básicas
        if (empty($data['categoria_descripcion'])) {
            $this->redirect('/categorias/create', 'Complete los campos obligatorios', 'error');
            return;
        }

        try {
            $id = $this->categoriaModel->create($data);
            if ($id) {
                $this->redirect('/categorias', 'Categoría creada correctamente', 'exito');
            } else {
                $this->redirect('/categorias/create', 'Error al crear la categoría', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect('/categorias/create', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar categoría específica
     */
    public function show($id)
    {
        $this->requirePermission('categorias');

        $categoria = $this->categoriaModel->find($id);
        if (!$categoria) {
            return $this->view->error(404);
        }

        // Obtener estadísticas de la categoría
        $estadisticas = $this->categoriaModel->getStatistics($id);

        $data = [
            'title' => 'Detalle de Categoría',
            'categoria' => $categoria,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/categorias/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('categorias');

        $categoria = $this->categoriaModel->find($id);
        if (!$categoria) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        $data = [
            'title' => 'Editar Categoría',
            'categoria' => $categoria,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/categorias/formulario', $data, 'main');
    }

    /**
     * Actualizar categoría
     */
    public function update($id)
    {
        $this->requirePermission('categorias');

        $categoria = $this->categoriaModel->find($id);
        if (!$categoria) {
            return $this->view->error(404);
        }

        $data = [
            'categoria_descripcion' => $this->post('categoria_descripcion')
        ];

        if (empty($data['categoria_descripcion'])) {
            $this->redirect("/categorias/$id/edit", 'Complete los campos obligatorios', 'error');
            return;
        }

        try {
            if ($this->categoriaModel->update($id, $data)) {
                $this->redirect('/categorias', 'Categoría actualizada correctamente', 'exito');
            } else {
                $this->redirect("/categorias/$id/edit", 'Error al actualizar la categoría', 'error');
            }
        } catch (\Exception $e) {
            $this->redirect("/categorias/$id/edit", 'Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de categoría
     */
    public function delete($id)
    {
        $this->requirePermission('categorias');

        $categoria = $this->categoriaModel->find($id);
        if (!$categoria) {
            return $this->view->error(404);
        }

        if ($this->categoriaModel->softDelete($id, 'categoria_estado')) {
            $this->redirect('/categorias', 'Categoría eliminada correctamente', 'exito');
        } else {
            $this->redirect('/categorias', 'Error al eliminar la categoría', 'error');
        }
    }

    /**
     * Restaurar categoría
     */
    public function restore($id)
    {
        $this->requirePermission('categorias');

        if ($this->categoriaModel->restore($id, 'categoria_estado')) {
            $this->redirect('/categorias', 'Categoría restaurada correctamente', 'exito');
        } else {
            $this->redirect('/categorias', 'Error al restaurar la categoría', 'error');
        }
    }

    /**
     * Cambiar estado de categoría (AJAX)
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('categorias');

        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        // Verificar que la categoría existe
        $categoria = $this->categoriaModel->find($id);
        if (!$categoria) {
            return $this->json(['success' => false, 'message' => 'Categoría no encontrada'], 404);
        }

        // Obtener el nuevo estado del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
            return $this->json(['success' => false, 'message' => 'Estado inválido. Estados válidos: 0 (inactiva), 1 (activa)'], 400);
        }

        // Actualizar el estado
        $data = ['categoria_estado' => $nuevoEstado];
        $resultado = $this->categoriaModel->update($id, $data);

        if ($resultado) {
            $estadoTexto = ['inactiva', 'activa'];
            $accion = $estadoTexto[$nuevoEstado] ?? 'actualizada';
            return $this->json([
                'success' => true, 
                'message' => "Categoría marcada como {$accion} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            return $this->json(['success' => false, 'message' => 'Error al actualizar el estado'], 500);
        }
    }

    /**
     * Exportar categorías a Excel
     */
    public function exportar()
    {
        $this->requirePermission('categorias');

        $filters = [
            'categoria_descripcion' => $this->get('categoria_descripcion'),
            'categoria_estado' => $this->get('categoria_estado')
        ];

        try {
            $result = $this->categoriaModel->getAllWithDetailsForExport($filters);
            $datos = $result['data'];

            if (empty($datos)) {
                $this->redirect('/categorias', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear libro de Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar propiedades
            $spreadsheet->getProperties()
                ->setCreator("Sistema de Gestión de Cabañas")
                ->setLastModifiedBy("Sistema")
                ->setTitle("Categorías")
                ->setSubject("Exportación de Categorías")
                ->setDescription("Lista de categorías exportada desde el sistema");

            // Encabezados
            $headers = [
                'A1' => 'Descripción', 
                'B1' => 'Estado'
            ];

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getFont()->setBold(true);
                $sheet->getStyle($cell)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFCCCCCC');
            }

            // Datos
            $row = 2;
            foreach ($datos as $categoria) {
                $estadoTexto = $categoria['categoria_estado'] == 1 ? 'Activa' : 'Inactiva';

                $sheet->setCellValue('A' . $row, $categoria['categoria_descripcion']);
                $sheet->setCellValue('B' . $row, $estadoTexto);
                $row++;
            }

            // Ajustar ancho de columnas
            foreach (range('A', 'B') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Crear archivo
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            $filename = 'categorias_' . date('Y-m-d_H-i-s') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'categoria_export');
            $writer->save($tempFile);

            // Descargar archivo
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            readfile($tempFile);
            unlink($tempFile);
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar categorías: " . $e->getMessage());
            $this->redirect('/categorias', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar categorías a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('categorias');

        $filters = [
            'categoria_descripcion' => $this->get('categoria_descripcion'),
            'categoria_estado' => $this->get('categoria_estado')
        ];

        try {
            $result = $this->categoriaModel->getAllWithDetailsForExport($filters);
            $datos = $result['data'];

            if (empty($datos)) {
                $this->redirect('/categorias', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear PDF
            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // Configurar información del documento
            $pdf->SetCreator('Sistema de Gestión de Cabañas');
            $pdf->SetAuthor('Sistema');
            $pdf->SetTitle('Categorías');
            $pdf->SetSubject('Lista de Categorías');

            // Configurar márgenes y fuentes
            $pdf->SetMargins(15, 20, 15);
            $pdf->SetHeaderMargin(10);
            $pdf->SetFooterMargin(10);
            $pdf->SetAutoPageBreak(TRUE, 25);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->SetFont('helvetica', '', 12);

            // Agregar página
            $pdf->AddPage();

            // Título
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Lista de Categorías', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros si se aplicaron
            if (!empty($filters['categoria_descripcion']) || !empty($filters['categoria_estado'])) {
                $pdf->SetFont('helvetica', '', 10);
                $pdf->Cell(0, 5, 'Filtros aplicados:', 0, 1, 'L');
                
                if (!empty($filters['categoria_descripcion'])) {
                    $pdf->Cell(0, 5, '• Descripción: ' . $filters['categoria_descripcion'], 0, 1, 'L');
                }
                
                if (!empty($filters['categoria_estado'])) {
                    $estadoTexto = $filters['categoria_estado'] == '1' ? 'Activas' : 'Inactivas';
                    $pdf->Cell(0, 5, '• Estado: ' . $estadoTexto, 0, 1, 'L');
                }
                
                $pdf->Ln(5);
            }

            // Tabla
            $pdf->SetFont('helvetica', 'B', 10);
            
            // Encabezados
            $pdf->Cell(130, 7, 'Descripción', 1, 0, 'C', false);
            $pdf->Cell(30, 7, 'Estado', 1, 1, 'C', false);

            // Datos
            $pdf->SetFont('helvetica', '', 9);
            foreach ($datos as $categoria) {
                $estadoTexto = $categoria['categoria_estado'] == 1 ? 'Activa' : 'Inactiva';
                $pdf->Cell(130, 6, $categoria['categoria_descripcion'], 1, 0, 'L');
                $pdf->Cell(30, 6, $estadoTexto, 1, 1, 'C');
            }

            // Total de registros
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 5, 'Total de registros: ' . count($datos), 0, 1, 'R');

            // Información adicional
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(0, 5, 'Generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'L');

            // Salida del PDF
            $filename = 'categorias_' . date('Y-m-d_H-i-s') . '.pdf';
            $pdf->Output($filename, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar categorías a PDF: " . $e->getMessage());
            $this->redirect('/categorias', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}
