<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\EstadoProducto;

/**
 * Controlador para Estados de Productos
 */
class EstadosProductosController extends Controller
{
    private $estadoProductoModel;

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
        if (!$this->hasPermission('estadosproductos')) {
            return $this->view->error(403);
        }

        $page = $this->get('page', 1);
        $perPage = $this->get('per_page', 10);
        
        $filters = [
            'search' => $this->get('search', ''),
            'estado' => $this->get('estado', '')
        ];

        if (!empty($filters['search']) || $filters['estado'] !== '') {
            $result = $this->estadoProductoModel->search($filters, $page, $perPage);
            $totalPages = $this->estadoProductoModel->getTotalPages($filters, $perPage);
        } else {
            $result = $this->estadoProductoModel->paginate($page, $perPage, "1=1", "estadoproducto_descripcion");
            $totalPages = $this->estadoProductoModel->getTotalPages([], $perPage);
        }

        $data = [
            'title' => 'Estados de Productos',
            'estados' => $result['data'] ?? [],
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $perPage,
            'filters' => $filters,
            'total_records' => $result['total'] ?? 0,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estadosproductos/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        if (!$this->hasPermission('estadosproductos')) {
            return $this->view->error(403);
        }

        if ($this->isPost()) {
            $data = [
                'estadoproducto_descripcion' => $this->post('estadoproducto_descripcion'),
            ];
            
            // Validar datos
            $validator = \App\Core\Validator::make($data);
            $validator->required('estadoproducto_descripcion', 'La descripción del estado es requerida')
                     ->minLength('estadoproducto_descripcion', 3, 'La descripción debe tener al menos 3 caracteres')
                     ->maxLength('estadoproducto_descripcion', 100, 'La descripción no puede exceder 100 caracteres');
            
            if ($validator->fails()) {
                $this->redirect('/estadosproductos/create', $validator->firstError(), 'error');
                return;
            }
            
            // Agregar estado por defecto
            $data['estadoproducto_estado'] = 1;

            if ($this->estadoProductoModel->create($data)) {
                $this->redirect('/estadosproductos', 'Estado de producto creado exitosamente', 'exito');
            } else {
                $this->redirect('/estadosproductos/create', 'Error al crear el estado de producto', 'error');
            }
        }

        $data = ['title' => 'Nuevo Estado de Producto', 'isAdminArea' => true];
        return $this->render('admin/configuracion/estadosproductos/formulario', $data, 'main');
    }

    /**
     * Mostrar estado específico
     */
    public function show($id)
    {
        if (!$this->hasPermission('estadosproductos')) {
            return $this->view->error(403);
        }

        $estado = $this->estadoProductoModel->find($id);
        if (!$estado) {
            return $this->view->error(404);
        }

        // Obtener estadísticas del estado
        $estadisticas = $this->estadoProductoModel->getStatistics($id);

        $data = [
            'title' => 'Detalle de Estado de Producto',
            'estado' => $estado,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estadosproductos/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        if (!$this->hasPermission('estadosproductos')) {
            return $this->view->error(403);
        }

        $estadoProducto = $this->estadoProductoModel->findById($id);
        if (!$estadoProducto) {
            $this->redirect('/estadosproductos', 'Estado de producto no encontrado', 'error');
            return;
        }

        if ($this->isPost()) {
            $data = [
                'estadoproducto_descripcion' => $this->post('estadoproducto_descripcion')
            ];
            
            // Validar datos
            $validator = \App\Core\Validator::make($data);
            $validator->required('estadoproducto_descripcion', 'La descripción del estado es requerida')
                     ->minLength('estadoproducto_descripcion', 3, 'La descripción debe tener al menos 3 caracteres')
                     ->maxLength('estadoproducto_descripcion', 100, 'La descripción no puede exceder 100 caracteres');
            
            if ($validator->fails()) {
                $this->redirect("/estadosproductos/{$id}/edit", $validator->firstError(), 'error');
                return;
            }

            if ($this->estadoProductoModel->update($id, $data)) {
                $this->redirect('/estadosproductos', 'Estado de producto actualizado exitosamente', 'exito');
            } else {
                $this->redirect("/estadosproductos/{$id}/edit", 'Error al actualizar el estado de producto', 'error');
            }
        }

        $data = [
            'title' => 'Editar Estado de Producto',
            'estado_producto' => $estadoProducto,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estadosproductos/formulario', $data, 'main');
    }

    /**
     * Baja lógica
     */
    public function delete($id)
    {
        if (!$this->hasPermission('estadosproductos')) {
            return $this->view->error(403);
        }

        // Verificar si está en uso
        if ($this->estadoProductoModel->isInUse($id)) {
            $this->redirect('/estadosproductos', 'No se puede eliminar: el estado está siendo utilizado por productos', 'error');
            return;
        }

        if ($this->estadoProductoModel->update($id, ['estadoproducto_estado' => 0])) {
            $this->redirect('/estadosproductos', 'Estado de producto eliminado exitosamente', 'exito');
        } else {
            $this->redirect('/estadosproductos', 'Error al eliminar el estado de producto', 'error');
        }
    }

    /**
     * Restaurar (quitar baja lógica)
     */
    public function restore($id)
    {
        if (!$this->hasPermission('estadosproductos')) {
            return $this->view->error(403);
        }

        if ($this->estadoProductoModel->update($id, ['estadoproducto_estado' => 1])) {
            $this->redirect('/estadosproductos', 'Estado de producto restaurado exitosamente', 'exito');
        } else {
            $this->redirect('/estadosproductos', 'Error al restaurar el estado de producto', 'error');
        }
    }

    /**
     * Cambiar estado (toggle)
     */
    public function toggleStatus($id)
    {
        if (!$this->hasPermission('estadosproductos')) {
            return $this->view->error(403);
        }

        $estadoProducto = $this->estadoProductoModel->findById($id);
        if (!$estadoProducto) {
            $this->redirect('/estadosproductos', 'Estado de producto no encontrado', 'error');
            return;
        }

        // Si está activo y se intenta desactivar, verificar uso
        if ($estadoProducto['estadoproducto_estado'] == 1 && $this->estadoProductoModel->isInUse($id)) {
            $this->redirect('/estadosproductos', 'No se puede desactivar: el estado está siendo utilizado por productos', 'error');
            return;
        }

        if ($this->estadoProductoModel->toggleStatus($id)) {
            $action = $estadoProducto['estadoproducto_estado'] == 1 ? 'desactivado' : 'activado';
            $this->redirect('/estadosproductos', "Estado de producto {$action} exitosamente", 'exito');
        } else {
            $this->redirect('/estadosproductos', 'Error al cambiar el estado', 'error');
        }
    }

    /**
     * Búsqueda de estados
     */
    public function search()
    {
        if (!$this->hasPermission('estadosproductos')) {
            return $this->view->error(403);
        }

        $query = $this->get('q', '');
        $page = $this->get('page', 1);
        $perPage = $this->get('per_page', 10);

        if (empty($query)) {
            $this->redirect('/estadosproductos');
            return;
        }

        $filters = ['search' => $query];
        $result = $this->estadoProductoModel->search($filters, $page, $perPage);
        $totalPages = $this->estadoProductoModel->getTotalPages($filters, $perPage);

        $data = [
            'title' => 'Búsqueda de Estados de Productos',
            'estados' => $result['data'] ?? [],
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $perPage,
            'filters' => $filters,
            'search_query' => $query,
            'total_records' => $result['total'] ?? 0,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estadosproductos/listado', $data, 'main');
    }

    /**
     * Ver estadísticas
     */
    public function stats()
    {
        if (!$this->hasPermission('estadosproductos')) {
            return $this->view->error(403);
        }

        $estadosConConteo = $this->estadoProductoModel->getWithProductCount();

        $data = [
            'title' => 'Estadísticas de Estados de Productos',
            'estados_stats' => $estadosConConteo,
            'isAdminArea' => true
        ];

        return $this->render('admin/configuracion/estadosproductos/stats', $data, 'main');
    }

    /**
     * Exportar estados a Excel
     */
    public function exportar()
    {
        if (!$this->hasPermission('estadosproductos')) {
            return $this->view->error(403);
        }

        try {
            // Obtener filtros aplicados
            $filters = [
                'search' => $this->get('search', ''),
                'estado' => $this->get('estado', '')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->estadoProductoModel->getAllForExport($filters);
            $estados = $result['data'] ?? [];

            if (empty($estados)) {
                $this->redirect('/estadosproductos', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Estados de Productos');

            // Definir encabezados
            $headers = [
                'A1' => 'Descripción',
                'B1' => 'Estado'
            ];

            // Establecer encabezados
            foreach ($headers as $cell => $header) {
                $worksheet->setCellValue($cell, $header);
            }

            // Aplicar estilo a los encabezados
            $worksheet->getStyle('A1:B1')->getFont()->setBold(true);
            $worksheet->getStyle('A1:B1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $worksheet->getStyle('A1:B1')->getFill()->getStartColor()->setARGB('FFE3F2FD');

            // Llenar datos
            $row = 2;
            foreach ($estados as $estado) {
                $estadoTexto = $estado['estadoproducto_estado'] == 1 ? 'Activo' : 'Inactivo';

                $worksheet->setCellValue('A' . $row, $estado['estadoproducto_descripcion']);
                $worksheet->setCellValue('B' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(60);
            $worksheet->getColumnDimension('B')->setWidth(15);

            // Crear writer y preparar descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "estados_productos_{$fecha}.xlsx";

            // Headers para descarga
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');

            // Enviar archivo
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar estados de productos: " . $e->getMessage());
            $this->redirect('/estadosproductos', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar estados a PDF
     */
    public function exportarPdf()
    {
        if (!$this->hasPermission('estadosproductos')) {
            return $this->view->error(403);
        }

        try {
            // Obtener filtros aplicados
            $filters = [
                'search' => $this->get('search', ''),
                'estado' => $this->get('estado', '')
            ];

            // Obtener TODOS los registros sin paginación
            $result = $this->estadoProductoModel->getAllForExport($filters);
            $estados = $result['data'] ?? [];

            if (empty($estados)) {
                $this->redirect('/estadosproductos', 'No hay datos para exportar', 'error');
                return;
            }

            // Crear nuevo PDF
            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            // Configurar información del documento
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Estados de Productos');
            $pdf->SetSubject('Exportación de Estados de Productos');

            // Configurar márgenes
            $pdf->SetMargins(15, 20, 15);
            $pdf->SetHeaderMargin(5);
            $pdf->SetFooterMargin(10);

            // Configurar auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 25);

            // Establecer fuente
            $pdf->setFontSubsetting(true);
            $pdf->SetFont('helvetica', '', 10);

            // Agregar página
            $pdf->AddPage();

            // Título del documento
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 15, 'Listado de Estados de Productos', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros aplicados
            $filtrosTexto = [];
            if (!empty($filters['search'])) {
                $filtrosTexto[] = 'Búsqueda: ' . $filters['search'];
            }
            if (isset($filters['estado']) && $filters['estado'] !== '') {
                $estadosTexto = ['Inactivo', 'Activo'];
                $filtrosTexto[] = 'Estado: ' . ($estadosTexto[$filters['estado']] ?? 'Desconocido');
            }

            if (!empty($filtrosTexto)) {
                $pdf->SetFont('helvetica', 'I', 9);
                $pdf->Cell(0, 10, 'Filtros aplicados: ' . implode(' | ', $filtrosTexto), 0, 1, 'L');
                $pdf->Ln(3);
            }

            // Información de generación
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 10, 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($estados), 0, 1, 'L');
            $pdf->Ln(5);

            // Crear tabla HTML
            $html = '<style>
                table { 
                    border-collapse: collapse; 
                    width: 100%; 
                }
                th { 
                    background-color: #E3F2FD; 
                    border: 1px solid #333; 
                    padding: 8px; 
                    text-align: center; 
                    font-weight: bold; 
                }
                td { 
                    border: 1px solid #666; 
                    padding: 6px; 
                    vertical-align: middle;
                }
                .descripcion { width: 70%; }
                .estado { text-align: center; width: 30%; }
                .estado-activo { color: #28a745; font-weight: bold; }
                .estado-inactivo { color: #dc3545; font-weight: bold; }
            </style>';

            $html .= '<table>
                <thead>
                    <tr>
                        <th class="descripcion">Descripción</th>
                        <th class="estado">Estado</th>
                    </tr>
                </thead>
                <tbody>';

            // Llenar datos
            foreach ($estados as $estado) {
                $estadoTexto = $estado['estadoproducto_estado'] == 1 ? 'Activo' : 'Inactivo';
                $estadoClase = $estado['estadoproducto_estado'] == 1 ? 'estado-activo' : 'estado-inactivo';

                $html .= '<tr>
                    <td class="descripcion">' . htmlspecialchars($estado['estadoproducto_descripcion']) . '</td>
                    <td class="estado ' . $estadoClase . '">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            // Escribir HTML al PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Generar nombre de archivo con fecha
            $fecha = date('Y-m-d');
            $nombreArchivo = "estados_productos_{$fecha}.pdf";

            // Enviar el PDF al navegador
            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar estados de productos a PDF: " . $e->getMessage());
            $this->redirect('/estadosproductos', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }
}

