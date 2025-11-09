<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\EstadoProducto;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use TCPDF;

/**
 * Controlador para el manejo de Estados de Productos
 */
class EstadosProductosController extends Controller
{
    protected $estadoProductoModel;

    public function __construct()
    {
        parent::__construct();
        $this->estadoProductoModel = new EstadoProducto();
    }

    /**
     * Listar estados de productos
     */
    public function index()
    {
        $this->requirePermission('estados-productos');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'estadoproducto_descripcion' => $this->get('estadoproducto_descripcion'),
            'estadoproducto_estado' => $this->get('estadoproducto_estado')
        ];

        $result = $this->estadoProductoModel->getWithDetails($page, $perPage, $filters);

        $data = [
            'title' => 'Gestión de Estados de Productos',
            'estados' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estados-productos/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de creación o procesar creación
     */
    public function create()
    {
        $this->requirePermission('estados-productos');

        if ($this->isPost()) {
            return $this->store();
        }

        $data = [
            'title' => 'Nuevo Estado de Producto',
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estados-productos/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo estado de producto
     */
    protected function store()
    {
        try {
            $data = [
                'estadoproducto_descripcion' => trim($this->post('estadoproducto_descripcion')),
                'estadoproducto_estado' => 1
            ];

            if (empty($data['estadoproducto_descripcion'])) {
                $this->redirect('/estados-productos/create', 'La descripción es obligatoria', 'error');
                return;
            }

            if (strlen($data['estadoproducto_descripcion']) > 45) {
                $this->redirect('/estados-productos/create', 'La descripción no puede exceder 45 caracteres', 'error');
                return;
            }

            $existeEstado = $this->estadoProductoModel->findAll("estadoproducto_descripcion = '" . addslashes($data['estadoproducto_descripcion']) . "'");
            if (!empty($existeEstado)) {
                $this->redirect('/estados-productos/create', 'Ya existe un estado de producto con esa descripción', 'error');
                return;
            }

            $id = $this->estadoProductoModel->create($data);

            if ($id) {
                $this->redirect('/estados-productos', 'Estado de producto creado exitosamente', 'success');
            } else {
                $this->redirect('/estados-productos/create', 'Error al crear el estado de producto', 'error');
            }
        } catch (\Exception $e) {
            error_log("Error en EstadosProductosController::store: " . $e->getMessage());
            $this->redirect('/estados-productos/create', 'Error interno del servidor', 'error');
        }
    }

    /**
     * Mostrar detalle de estado de producto específico
     */
    public function show($id)
    {
        $this->requirePermission('estados-productos');

        $estadoProducto = $this->estadoProductoModel->find($id);
        if (!$estadoProducto) {
            return $this->view->error(404);
        }

        $data = [
            'title' => 'Detalle del Estado de Producto',
            'estado' => $estadoProducto,
            'estadisticas' => $this->estadoProductoModel->getStatistics($id),
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estados-productos/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición o procesar actualización
     */
    public function edit($id)
    {
        $this->requirePermission('estados-productos');

        $estadoProducto = $this->estadoProductoModel->find($id);
        if (!$estadoProducto) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        $productosCount = $this->estadoProductoModel->getProductosCountByEstado($id);

        $data = [
            'title' => 'Editar Estado de Producto',
            'estado' => $estadoProducto,
            'productos_count' => $productosCount,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estados-productos/formulario', $data, 'main');
    }

    /**
     * Actualizar estado de producto existente
     */
    protected function update($id)
    {
        try {
            $estadoProducto = $this->estadoProductoModel->find($id);
            if (!$estadoProducto) {
                $this->redirect('/estados-productos', 'Estado de producto no encontrado', 'error');
                return;
            }

            $data = [
                'estadoproducto_descripcion' => trim($this->post('estadoproducto_descripcion'))
            ];

            if (empty($data['estadoproducto_descripcion'])) {
                $this->redirect('/estados-productos/' . $id . '/edit', 'La descripción es obligatoria', 'error');
                return;
            }

            if (strlen($data['estadoproducto_descripcion']) > 45) {
                $this->redirect('/estados-productos/' . $id . '/edit', 'La descripción no puede exceder 45 caracteres', 'error');
                return;
            }

            $existeEstado = $this->estadoProductoModel->findAll("estadoproducto_descripcion = '" . addslashes($data['estadoproducto_descripcion']) . "' AND id_estadoproducto != " . (int)$id);
            if (!empty($existeEstado)) {
                $this->redirect('/estados-productos/' . $id . '/edit', 'Ya existe otro estado de producto con esa descripción', 'error');
                return;
            }

            $success = $this->estadoProductoModel->update($id, $data);

            if ($success) {
                $this->redirect('/estados-productos', 'Estado de producto actualizado exitosamente', 'success');
            } else {
                $this->redirect('/estados-productos/' . $id . '/edit', 'Error al actualizar el estado de producto', 'error');
            }
        } catch (\Exception $e) {
            error_log("Error en EstadosProductosController::update: " . $e->getMessage());
            $this->redirect('/estados-productos/' . $id . '/edit', 'Error interno del servidor', 'error');
        }
    }

    /**
     * Cambiar estado mediante AJAX
     */
    public function cambiarEstado($id)
    {
        error_log("EstadosProductos - Petición recibida en cambiarEstado - ID: $id");
        error_log("EstadosProductos - Método HTTP: " . $_SERVER['REQUEST_METHOD']);
        error_log("EstadosProductos - URL completa: " . $_SERVER['REQUEST_URI']);
        
        $this->requirePermission('estados-productos');
        
        if (!$this->isAjax()) {
            error_log("EstadosProductos - Error: No es una petición AJAX");
            error_log("EstadosProductos - Headers: " . json_encode(getallheaders()));
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        try {
            $estadoProducto = $this->estadoProductoModel->find($id);
            if (!$estadoProducto) {
                error_log("EstadosProductos - Error: Estado de producto no encontrado - ID: $id");
                return $this->json(['success' => false, 'message' => 'Estado de producto no encontrado'], 404);
            }

            $input = json_decode(file_get_contents('php://input'), true);
            error_log("EstadosProductos - Datos recibidos: " . json_encode($input));
            
            $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

            if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
                error_log("EstadosProductos - Error: Estado inválido - Estado: " . var_export($nuevoEstado, true));
                return $this->json(['success' => false, 'message' => 'Estado inválido. Estados válidos: 0 (inactivo), 1 (activo)'], 400);
            }

            if ($nuevoEstado == 0) {
                $productosCount = $this->estadoProductoModel->getProductosCountByEstado($id);
                if ($productosCount > 0) {
                    error_log("EstadosProductos - Error: No se puede desactivar - tiene $productosCount productos asociados");
                    $mensaje = $productosCount === 1 
                        ? 'No se puede desactivar este estado porque hay 1 producto que lo utiliza actualmente.' 
                        : "No se puede desactivar este estado porque hay {$productosCount} productos que lo utilizan actualmente.";
                    
                    return $this->json([
                        'success' => false, 
                        'message' => $mensaje,
                        'productos_count' => $productosCount
                    ], 400);
                }
            }

            $success = $this->estadoProductoModel->update($id, ['estadoproducto_estado' => $nuevoEstado]);

            if ($success) {
                $mensaje = $nuevoEstado == 1 ? 'Estado de producto activado correctamente' : 'Estado de producto desactivado correctamente';
                error_log("EstadosProductos - Estado cambiado exitosamente - ID: $id, Nuevo estado: $nuevoEstado");
                return $this->json([
                    'success' => true,
                    'message' => $mensaje,
                    'nuevo_estado' => $nuevoEstado
                ]);
            } else {
                error_log("EstadosProductos - Error al actualizar el estado en la base de datos - ID: $id");
                return $this->json(['success' => false, 'message' => 'Error al cambiar el estado'], 500);
            }
        } catch (\Exception $e) {
            error_log("Error en EstadosProductosController::cambiarEstado: " . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Exportar a Excel
     */
    public function exportar()
    {
        $this->requirePermission('estados-productos');

        try {
            $filters = [
                'estadoproducto_descripcion' => $this->get('estadoproducto_descripcion'),
                'estadoproducto_estado' => $this->get('estadoproducto_estado')
            ];

            $result = $this->estadoProductoModel->getAllWithDetailsForExport($filters);
            $datos = $result['data'];

            if (empty($datos)) {
                $this->redirect('/estados-productos', 'No hay datos para exportar', 'error');
                return;
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Estados de Productos');

            $headers = ['Descripción', 'Estado'];
            $sheet->fromArray($headers, null, 'A1');

            $headerRange = 'A1:B1';
            $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
            $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row = 2;
            foreach ($datos as $estado) {
                $estadoTexto = $estado['estadoproducto_estado'] == 1 ? 'Activo' : 'Inactivo';
                
                $sheet->setCellValue('A' . $row, $estado['estadoproducto_descripcion']);
                $sheet->setCellValue('B' . $row, $estadoTexto);
                $row++;
            }

            foreach (range('A', 'B') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $filename = 'estados-productos_' . date('Y-m-d_H-i-s') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            error_log("Error en EstadosProductosController::exportar: " . $e->getMessage());
            $this->redirect('/estados-productos', 'Error al generar el archivo Excel', 'error');
        }
    }

    /**
     * Exportar a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('estados-productos');

        try {
            $filters = [
                'estadoproducto_descripcion' => $this->get('estadoproducto_descripcion'),
                'estadoproducto_estado' => $this->get('estadoproducto_estado')
            ];

            $result = $this->estadoProductoModel->getAllWithDetailsForExport($filters);
            $datos = $result['data'];

            if (empty($datos)) {
                $this->redirect('/estados-productos', 'No hay datos para exportar', 'error');
                return;
            }

            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Gestión de Cabañas');
            $pdf->SetTitle('Estados de Productos');
            $pdf->SetSubject('Listado de Estados de Productos');
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->AddPage();

            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Estados de Productos', 0, 1, 'C');
            $pdf->Ln(5);

            $pdf->SetFont('helvetica', '', 10);
            if (!empty($filters['estadoproducto_descripcion'])) {
                $pdf->Cell(0, 5, 'Filtro por descripción: ' . $filters['estadoproducto_descripcion'], 0, 1);
            }
            if (isset($filters['estadoproducto_estado']) && $filters['estadoproducto_estado'] !== '') {
                $estadoTexto = $filters['estadoproducto_estado'] == 1 ? 'Activos' : 'Inactivos';
                $pdf->Cell(0, 5, 'Filtro por estado: ' . $estadoTexto, 0, 1);
            }
            $pdf->Cell(0, 5, 'Total de registros: ' . count($datos), 0, 1);
            $pdf->Ln(5);

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(130, 8, 'Descripción', 1, 0, 'C');
            $pdf->Cell(40, 8, 'Estado', 1, 1, 'C');

            $pdf->SetFont('helvetica', '', 9);
            foreach ($datos as $estado) {
                $estadoTexto = $estado['estadoproducto_estado'] == 1 ? 'Activo' : 'Inactivo';
                
                $pdf->Cell(130, 6, $estado['estadoproducto_descripcion'], 1, 0, 'L');
                $pdf->Cell(40, 6, $estadoTexto, 1, 1, 'C');
            }

            $filename = 'estados-productos_' . date('Y-m-d_H-i-s') . '.pdf';
            $pdf->Output($filename, 'D');

        } catch (\Exception $e) {
            error_log("Error en EstadosProductosController::exportarPdf: " . $e->getMessage());
            $this->redirect('/estados-productos', 'Error al generar el archivo PDF', 'error');
        }
    }

    /**
     * Eliminar (baja lógica) un estado de producto
     */
    public function delete($id)
    {
        $this->requirePermission('estados-productos');

        $estadoProducto = $this->estadoProductoModel->find($id);
        if (!$estadoProducto) {
            return $this->view->error(404);
        }

        if (!$this->estadoProductoModel->canChangeStatus($id, 0)) {
            $this->redirect('/estados-productos', 'No se puede eliminar este estado porque está siendo usado por productos activos', 'error');
            return;
        }

        try {
            $data = [
                'estadoproducto_estado' => 0
            ];
            
            $success = $this->estadoProductoModel->update($id, $data);
            
            if ($success) {
                $this->redirect('/estados-productos', 'Estado de producto eliminado correctamente', 'success');
            } else {
                $this->redirect('/estados-productos', 'Error al eliminar el estado de producto', 'error');
            }
        } catch (\Exception $e) {
            error_log("Error en delete: " . $e->getMessage());
            $this->redirect('/estados-productos', 'Error al eliminar el estado de producto', 'error');
        }
    }

    /**
     * Restaurar (alta lógica) un estado de producto
     */
    public function restore($id)
    {
        $this->requirePermission('estados-productos');

        $estadoProducto = $this->estadoProductoModel->find($id);
        if (!$estadoProducto) {
            return $this->view->error(404);
        }

        try {
            $data = [
                'estadoproducto_estado' => 1
            ];
            
            $success = $this->estadoProductoModel->update($id, $data);
            
            if ($success) {
                $this->redirect('/estados-productos', 'Estado de producto restaurado correctamente', 'success');
            } else {
                $this->redirect('/estados-productos', 'Error al restaurar el estado de producto', 'error');
            }
        } catch (\Exception $e) {
            error_log("Error en restore: " . $e->getMessage());
            $this->redirect('/estados-productos', 'Error al restaurar el estado de producto', 'error');
        }
    }
}

