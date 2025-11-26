<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Cotizacion;
use App\Models\Producto;
use App\Models\Proveedor;

/**
 * Controlador para el manejo de cotizaciones
 */
class CotizacionesController extends Controller
{
    protected $modelo;

    public function __construct()
    {
        parent::__construct();
        $this->modelo = new Cotizacion();
    }

    /**
     * Listar últimas cotizaciones por producto-proveedor
     */
    public function index()
    {
        $this->requirePermission('cotizaciones');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar que perPage esté dentro de los valores permitidos
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'proveedor_nombre' => $this->get('proveedor_nombre'),
            'producto_nombre' => $this->get('producto_nombre'),
            'rela_proveedor' => $this->get('rela_proveedor'),
            'estado' => $this->get('estado')
        ];

        // Obtener últimas cotizaciones por producto-proveedor
        $result = $this->modelo->getLastQuotesByProductProvider($page, $perPage, $filters);

        // Obtener listas para filtros
        $proveedorModel = new Proveedor();
        $proveedores = $proveedorModel->getProveedoresActivos();

        $data = [
            'title' => 'Gestión de Cotizaciones',
            'cotizaciones' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'proveedores' => $proveedores,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cotizaciones/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nueva cotización
     */
    public function create()
    {
        $this->requirePermission('cotizaciones');

        if ($this->isPost()) {
            return $this->store();
        }

        // Obtener proveedor y producto si se pasan por parámetro (para recotizar)
        $proveedorId = $this->get('proveedor');
        $productoId = $this->get('producto');

        // Cargar listas
        $productoModel = new Producto();
        $productos = $productoModel->findAll("rela_estadoproducto = 1", "producto_nombre ASC");

        $proveedorModel = new Proveedor();
        $proveedores = $proveedorModel->getProveedoresActivos();

        $data = [
            'title' => 'Nueva Cotización',
            'cotizacion' => [],
            'productos' => $productos,
            'proveedores' => $proveedores,
            'proveedorSeleccionado' => $proveedorId,
            'productoSeleccionado' => $productoId,
            'isEdit' => false,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cotizaciones/formulario', $data, 'main');
    }

    /**
     * Guardar nueva cotización
     */
    public function store()
    {
        $this->requirePermission('cotizaciones');

        $data = [
            'rela_proveedor' => (int) $this->post('rela_proveedor'),
            'rela_producto' => (int) $this->post('rela_producto'),
            'cotizacion_monto' => (float) $this->post('cotizacion_monto'),
            'cotizacion_estado' => 1
        ];

        // Validaciones básicas
        if (empty($data['rela_proveedor']) || empty($data['rela_producto']) || empty($data['cotizacion_monto'])) {
            $this->redirect('/cotizaciones/create', 'Complete los campos obligatorios', 'error');
            return;
        }

        if ($data['cotizacion_monto'] <= 0) {
            $this->redirect('/cotizaciones/create', 'El monto debe ser mayor a cero', 'error');
            return;
        }

        try {
            $id = $this->modelo->create($data);
            if (!$id) {
                throw new \Exception('Error al crear la cotización');
            }

            $this->redirect('/cotizaciones', 'Cotización creada correctamente', 'exito');
        } catch (\Exception $e) {
            $this->redirect('/cotizaciones/create', 'Error al crear la cotización: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mostrar cotización específica
     */
    public function show($id)
    {
        $this->requirePermission('cotizaciones');

        $sql = "SELECT c.*, 
                       p.persona_denominacion as proveedor_nombre,
                       pr.producto_nombre,
                       pr.producto_descripcion,
                       pr.producto_precio as producto_precio_actual
                FROM cotizacion c
                INNER JOIN proveedor pv ON c.rela_proveedor = pv.id_proveedor
                INNER JOIN persona p ON pv.rela_persona = p.id_persona
                INNER JOIN producto pr ON c.rela_producto = pr.id_producto
                WHERE c.id_cotizacion = ?";
        
        // Usar la conexión del modelo
        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cotizacion = $result->fetch_assoc();
        $stmt->close();

        if (!$cotizacion) {
            return $this->view->error(404);
        }

        // Obtener estadísticas de la cotización
        $estadisticas = $this->modelo->getStatistics($id);

        $data = [
            'title' => 'Detalle de Cotización',
            'cotizacion' => $cotizacion,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cotizaciones/detalle', $data, 'main');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('cotizaciones');

        $cotizacion = $this->modelo->find($id);
        if (!$cotizacion) {
            return $this->view->error(404);
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        // Obtener estadísticas
        $estadisticas = $this->modelo->getStatistics($id);

        // Cargar listas
        $productoModel = new Producto();
        $productos = $productoModel->findAll("rela_estadoproducto = 1", "producto_nombre ASC");

        $proveedorModel = new Proveedor();
        $proveedores = $proveedorModel->getProveedoresActivos();

        $data = [
            'title' => 'Editar Cotización',
            'cotizacion' => $cotizacion,
            'estadisticas' => $estadisticas,
            'productos' => $productos,
            'proveedores' => $proveedores,
            'isEdit' => true,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cotizaciones/formulario', $data, 'main');
    }

    /**
     * Actualizar cotización
     */
    public function update($id)
    {
        $this->requirePermission('cotizaciones');

        $cotizacion = $this->modelo->find($id);
        if (!$cotizacion) {
            return $this->view->error(404);
        }

        $data = [
            'rela_proveedor' => (int) $this->post('rela_proveedor'),
            'rela_producto' => (int) $this->post('rela_producto'),
            'cotizacion_monto' => (float) $this->post('cotizacion_monto')
        ];

        if (empty($data['rela_proveedor']) || empty($data['rela_producto']) || empty($data['cotizacion_monto'])) {
            $this->redirect("/cotizaciones/$id/edit", 'Complete los campos obligatorios', 'error');
            return;
        }

        if ($data['cotizacion_monto'] <= 0) {
            $this->redirect("/cotizaciones/$id/edit", 'El monto debe ser mayor a cero', 'error');
            return;
        }

        try {
            if (!$this->modelo->update($id, $data)) {
                throw new \Exception('Error al actualizar la cotización');
            }

            $this->redirect('/cotizaciones', 'Cotización actualizada correctamente', 'exito');
        } catch (\Exception $e) {
            $this->redirect("/cotizaciones/$id/edit", 'Error al actualizar la cotización: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Baja lógica de cotización (anular)
     */
    public function delete($id)
    {
        $this->requirePermission('cotizaciones');

        $cotizacion = $this->modelo->find($id);
        if (!$cotizacion) {
            return $this->view->error(404);
        }

        if ($this->modelo->update($id, ['cotizacion_estado' => 0])) {
            $this->redirect('/cotizaciones', 'Cotización anulada correctamente', 'exito');
        } else {
            $this->redirect('/cotizaciones', 'Error al anular la cotización', 'error');
        }
    }

    /**
     * Restaurar cotización
     */
    public function restore($id)
    {
        $this->requirePermission('cotizaciones');

        if ($this->modelo->update($id, ['cotizacion_estado' => 1])) {
            $this->redirect('/cotizaciones', 'Cotización restaurada correctamente', 'exito');
        } else {
            $this->redirect('/cotizaciones', 'Error al restaurar la cotización', 'error');
        }
    }

    /**
     * Cambiar estado de cotización (AJAX)
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('cotizaciones');

        if (!$this->isAjax()) {
            return $this->json(['success' => false, 'message' => 'Petición inválida'], 400);
        }

        $cotizacion = $this->modelo->find($id);
        if (!$cotizacion) {
            return $this->json(['success' => false, 'message' => 'Cotización no encontrada'], 404);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $nuevoEstado = isset($input['estado']) ? (int)$input['estado'] : null;

        if ($nuevoEstado === null || !in_array($nuevoEstado, [0, 1])) {
            return $this->json(['success' => false, 'message' => 'Estado inválido'], 400);
        }

        $data = ['cotizacion_estado' => $nuevoEstado];
        $resultado = $this->modelo->update($id, $data);

        if ($resultado) {
            $estadoTexto = $nuevoEstado == 1 ? 'activa' : 'anulada';
            return $this->json([
                'success' => true, 
                'message' => "Cotización marcada como {$estadoTexto} correctamente",
                'nuevo_estado' => $nuevoEstado
            ]);
        } else {
            return $this->json([
                'success' => false, 
                'message' => 'Error al cambiar el estado de la cotización'
            ], 500);
        }
    }

    /**
     * Recotizar - Redirige al formulario con proveedor y producto preseleccionados
     */
    public function recotizar($id)
    {
        $this->requirePermission('cotizaciones');

        $cotizacion = $this->modelo->find($id);
        if (!$cotizacion) {
            return $this->view->error(404);
        }

        // Redirigir al formulario de creación con parámetros
        $this->redirect('/cotizaciones/create?proveedor=' . $cotizacion['rela_proveedor'] . '&producto=' . $cotizacion['rela_producto']);
    }

    /**
     * Ver historial de cotizaciones de un par producto-proveedor
     */
    public function historial()
    {
        $this->requirePermission('cotizaciones');

        $productoId = (int) $this->get('producto');
        $proveedorId = (int) $this->get('proveedor');

        if (!$productoId || !$proveedorId) {
            $this->redirect('/cotizaciones', 'Parámetros inválidos', 'error');
            return;
        }

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar perPage
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        $filters = [
            'fecha_desde' => $this->get('fecha_desde'),
            'fecha_hasta' => $this->get('fecha_hasta'),
            'monto_mayor' => $this->get('monto_mayor'),
            'monto_menor' => $this->get('monto_menor')
        ];

        // Obtener historial paginado
        $result = $this->modelo->getHistoryByProductProviderPaginated($productoId, $proveedorId, $page, $perPage, $filters);

        // Obtener nombres aunque no haya resultados
        $productoModel = new Producto();
        $producto = $productoModel->find($productoId);
        
        $proveedorModel = new Proveedor();
        $proveedor = $proveedorModel->getProveedorCompleto($proveedorId);

        if (!$producto || !$proveedor) {
            $this->redirect('/cotizaciones', 'Producto o proveedor no encontrado', 'error');
            return;
        }

        $data = [
            'title' => 'Historial de Cotizaciones',
            'historial' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'producto_id' => $productoId,
            'proveedor_id' => $proveedorId,
            'producto_nombre' => $producto['producto_nombre'],
            'proveedor_nombre' => $proveedor['persona_denominacion'],
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/cotizaciones/historial', $data, 'main');
    }

    /**
     * Exportar historial a Excel
     */
    public function exportarHistorial()
    {
        $this->requirePermission('cotizaciones');

        try {
            $productoId = (int) $this->get('producto');
            $proveedorId = (int) $this->get('proveedor');

            if (!$productoId || !$proveedorId) {
                $this->redirect('/cotizaciones', 'Parámetros inválidos', 'error');
                return;
            }

            $filters = [
                'fecha_desde' => $this->get('fecha_desde'),
                'fecha_hasta' => $this->get('fecha_hasta'),
                'monto_mayor' => $this->get('monto_mayor'),
                'monto_menor' => $this->get('monto_menor')
            ];

            $result = $this->modelo->getHistoryByProductProviderForExport($productoId, $proveedorId, $filters);
            $datos = $result['data'];

            if (empty($datos)) {
                $this->redirect('/cotizaciones/historial?producto=' . $productoId . '&proveedor=' . $proveedorId, 'No hay datos para exportar', 'error');
                return;
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Título
            $sheet->setCellValue('A1', 'HISTORIAL DE COTIZACIONES');
            $sheet->mergeCells('A1:B1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Información del par
            $sheet->setCellValue('A2', 'Proveedor: ' . $datos[0]['proveedor_nombre']);
            $sheet->setCellValue('A3', 'Producto: ' . $datos[0]['producto_nombre']);

            // Encabezados
            $row = 5;
            $sheet->setCellValue('A' . $row, 'Fecha/Hora');
            $sheet->setCellValue('B' . $row, 'Monto');

            $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':B' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');

            // Datos
            $row++;
            foreach ($datos as $cotizacion) {
                $sheet->setCellValue('A' . $row, date('d/m/Y H:i', strtotime($cotizacion['cotizacion_fechahora'])));
                $sheet->setCellValue('B' . $row, '$' . number_format($cotizacion['cotizacion_monto'], 2, ',', '.'));
                $row++;
            }

            // Ajustar columnas
            foreach (range('A', 'B') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Descargar
            $filename = 'historial_cotizaciones_' . date('Y-m-d_H-i-s') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            $this->redirect('/cotizaciones/historial?producto=' . $productoId . '&proveedor=' . $proveedorId, 
                'Error al generar el archivo: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar historial a PDF
     */
    public function exportarHistorialPdf()
    {
        $this->requirePermission('cotizaciones');

        try {
            $productoId = (int) $this->get('producto');
            $proveedorId = (int) $this->get('proveedor');

            if (!$productoId || !$proveedorId) {
                $this->redirect('/cotizaciones', 'Parámetros inválidos', 'error');
                return;
            }

            $filters = [
                'fecha_desde' => $this->get('fecha_desde'),
                'fecha_hasta' => $this->get('fecha_hasta'),
                'monto_mayor' => $this->get('monto_mayor'),
                'monto_menor' => $this->get('monto_menor')
            ];

            $result = $this->modelo->getHistoryByProductProviderForExport($productoId, $proveedorId, $filters);
            $datos = $result['data'];

            if (empty($datos)) {
                $this->redirect('/cotizaciones/historial?producto=' . $productoId . '&proveedor=' . $proveedorId, 'No hay datos para exportar', 'error');
                return;
            }

            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8');
            $pdf->SetCreator('Sistema de Gestión');
            $pdf->SetAuthor('Sistema');
            $pdf->SetTitle('Historial de Cotizaciones');

            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->AddPage();

            // Título
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'HISTORIAL DE COTIZACIONES', 0, 1, 'C');
            $pdf->Ln(5);

            // Información
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, 'Proveedor: ' . $datos[0]['proveedor_nombre'], 0, 1);
            $pdf->Cell(0, 6, 'Producto: ' . $datos[0]['producto_nombre'], 0, 1);
            $pdf->Ln(5);

            // Tabla
            $html = '<table border="1" cellpadding="4">
                <thead>
                    <tr style="background-color: #e0e0e0; font-weight: bold;">
                        <th width="50%" align="center">Fecha/Hora</th>
                        <th width="50%" align="center">Monto</th>
                    </tr>
                </thead>
                <tbody>';

            foreach ($datos as $cotizacion) {
                $html .= '<tr>
                    <td align="center">' . date('d/m/Y H:i', strtotime($cotizacion['cotizacion_fechahora'])) . '</td>
                    <td align="center">$' . number_format($cotizacion['cotizacion_monto'], 2, ',', '.') . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            $pdf->writeHTML($html, true, false, true, false, '');

            $filename = 'historial_cotizaciones_' . date('Y-m-d_H-i-s') . '.pdf';
            $pdf->Output($filename, 'D');
            exit;
        } catch (\Exception $e) {
            $this->redirect('/cotizaciones/historial?producto=' . $productoId . '&proveedor=' . $proveedorId, 
                'Error al generar el PDF: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar cotizaciones a Excel
     */
    public function exportar()
    {
        $this->requirePermission('cotizaciones');

        try {
            $filters = [
                'proveedor_nombre' => $this->get('proveedor_nombre'),
                'producto_nombre' => $this->get('producto_nombre'),
                'rela_proveedor' => $this->get('rela_proveedor'),
                'estado' => $this->get('estado')
            ];

            $result = $this->modelo->getAllWithDetailsForExport($filters);
            $cotizaciones = $result['data'];

            if (empty($cotizaciones)) {
                $this->redirect('/cotizaciones', 'No hay datos para exportar', 'error');
                return;
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Cotizaciones');

            // Definir encabezados
            $headers = [
                'A1' => 'Proveedor',
                'B1' => 'Producto',
                'C1' => 'Monto',
                'D1' => 'Fecha/Hora',
                'E1' => 'Estado'
            ];

            foreach ($headers as $cell => $header) {
                $worksheet->setCellValue($cell, $header);
            }

            // Aplicar estilo a los encabezados
            $worksheet->getStyle('A1:E1')->getFont()->setBold(true);
            $worksheet->getStyle('A1:E1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $worksheet->getStyle('A1:E1')->getFill()->getStartColor()->setARGB('FFE3F2FD');

            // Llenar datos
            $row = 2;
            foreach ($cotizaciones as $cotizacion) {
                $estadoTexto = $cotizacion['cotizacion_estado'] == 1 ? 'Activa' : 'Anulada';

                $worksheet->setCellValue('A' . $row, $cotizacion['proveedor_nombre']);
                $worksheet->setCellValue('B' . $row, $cotizacion['producto_nombre']);
                $worksheet->setCellValue('C' . $row, number_format($cotizacion['cotizacion_monto'], 2));
                $worksheet->setCellValue('D' . $row, $cotizacion['cotizacion_fechahora']);
                $worksheet->setCellValue('E' . $row, $estadoTexto);

                $row++;
            }

            // Ajustar ancho de columnas
            $worksheet->getColumnDimension('A')->setWidth(30);
            $worksheet->getColumnDimension('B')->setWidth(30);
            $worksheet->getColumnDimension('C')->setWidth(15);
            $worksheet->getColumnDimension('D')->setWidth(20);
            $worksheet->getColumnDimension('E')->setWidth(12);

            // Aplicar formato a la columna de monto
            $worksheet->getStyle('C2:C' . ($row - 1))->getNumberFormat()->setFormatCode('$#,##0.00');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            $fecha = date('Y-m-d');
            $nombreArchivo = "cotizaciones_{$fecha}.xlsx";

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');

            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar cotizaciones: " . $e->getMessage());
            $this->redirect('/cotizaciones', 'Error al exportar: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exportar cotizaciones a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('cotizaciones');

        try {
            $filters = [
                'proveedor_nombre' => $this->get('proveedor_nombre'),
                'producto_nombre' => $this->get('producto_nombre'),
                'rela_proveedor' => $this->get('rela_proveedor'),
                'estado' => $this->get('estado')
            ];

            $result = $this->modelo->getAllWithDetailsForExport($filters);
            $cotizaciones = $result['data'];

            if (empty($cotizaciones)) {
                $this->redirect('/cotizaciones', 'No hay datos para exportar', 'error');
                return;
            }

            $pdf = new \TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Sistema de Cabañas');
            $pdf->SetTitle('Listado de Cotizaciones');
            $pdf->SetSubject('Exportación de Cotizaciones');
            $pdf->SetKeywords('cotizaciones, listado, exportación');

            $pdf->SetMargins(8, 15, 8);
            $pdf->SetHeaderMargin(3);
            $pdf->SetFooterMargin(8);

            $pdf->SetAutoPageBreak(TRUE, 25);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->setFontSubsetting(true);
            $pdf->SetFont('helvetica', '', 9);

            $pdf->AddPage();

            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 15, 'Listado de Cotizaciones', 0, 1, 'C');
            $pdf->Ln(5);

            // Información de filtros aplicados
            $filtrosTexto = [];
            if (!empty($filters['proveedor_nombre'])) {
                $filtrosTexto[] = 'Proveedor: ' . $filters['proveedor_nombre'];
            }
            if (!empty($filters['producto_nombre'])) {
                $filtrosTexto[] = 'Producto: ' . $filters['producto_nombre'];
            }
            if (isset($filters['estado']) && $filters['estado'] !== '') {
                $estadosTexto = ['Anulada', 'Activa'];
                $filtrosTexto[] = 'Estado: ' . ($estadosTexto[$filters['estado']] ?? 'Desconocido');
            }

            if (!empty($filtrosTexto)) {
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Filtros aplicados: ' . implode(' | ', $filtrosTexto), 0, 1, 'L');
                $pdf->Ln(3);
            }

            $pdf->SetFont('helvetica', '', 8);
            $infoFormato = 'Generado el: ' . date('d/m/Y H:i:s') . ' | Total de registros: ' . count($cotizaciones);
            $pdf->Cell(0, 10, $infoFormato, 0, 1, 'L');
            $pdf->Ln(5);
            
            // Crear tabla HTML
            $html = '<style>
                table { 
                    border-collapse: collapse; 
                    width: 100%; 
                    table-layout: fixed;
                }
                th { 
                    background-color: #E3F2FD; 
                    border: 1px solid #333; 
                    padding: 3px; 
                    text-align: center; 
                    font-weight: bold; 
                    font-size: 8px;
                    word-wrap: break-word;
                }
                td { 
                    border: 1px solid #666; 
                    padding: 2px; 
                    font-size: 7px; 
                    vertical-align: top;
                    word-wrap: break-word;
                    overflow: hidden;
                }
                .nombre { width: 25%; }
                .monto { text-align: right; width: 15%; }
                .fecha { text-align: center; width: 20%; }
                .estado { text-align: center; width: 15%; }
                .estado-activa { color: #28a745; font-weight: bold; }
                .estado-anulada { color: #dc3545; font-weight: bold; }
            </style>';

            $html .= '<table>
                <thead>
                    <tr>
                        <th class="nombre">Proveedor</th>
                        <th class="nombre">Producto</th>
                        <th class="monto">Monto</th>
                        <th class="fecha">Fecha/Hora</th>
                        <th class="estado">Estado</th>
                    </tr>
                </thead>
                <tbody>';

            foreach ($cotizaciones as $cotizacion) {
                $estadoTexto = $cotizacion['cotizacion_estado'] == 1 ? 'Activa' : 'Anulada';
                $estadoClase = $cotizacion['cotizacion_estado'] == 1 ? 'estado-activa' : 'estado-anulada';

                $html .= '<tr>
                    <td class="nombre">' . htmlspecialchars($cotizacion['proveedor_nombre']) . '</td>
                    <td class="nombre">' . htmlspecialchars($cotizacion['producto_nombre']) . '</td>
                    <td class="monto">$' . number_format($cotizacion['cotizacion_monto'], 2, ',', '.') . '</td>
                    <td class="fecha">' . date('d/m/Y H:i', strtotime($cotizacion['cotizacion_fechahora'])) . '</td>
                    <td class="estado ' . $estadoClase . '">' . $estadoTexto . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';

            $pdf->writeHTML($html, true, false, true, false, '');

            $fecha = date('Y-m-d');
            $nombreArchivo = "cotizaciones_{$fecha}.pdf";

            $pdf->Output($nombreArchivo, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar cotizaciones a PDF: " . $e->getMessage());
            $this->redirect('/cotizaciones', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Importar cotizaciones desde Excel
     */
    public function importarCotizaciones()
    {
        $this->requirePermission('cotizaciones');

        if (!$this->isPost()) {
            return $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $proveedorId = (int) $this->post('proveedor_id');
        
        if (!$proveedorId) {
            return $this->json(['success' => false, 'message' => 'Proveedor no especificado'], 400);
        }

        // Validar que se subió un archivo
        if (!isset($_FILES['archivo_cotizaciones']) || $_FILES['archivo_cotizaciones']['error'] != 0) {
            return $this->json(['success' => false, 'message' => 'No se recibió el archivo o hubo un error en la subida'], 400);
        }

        try {
            $archivo = $_FILES['archivo_cotizaciones']['tmp_name'];
            
            // Cargar el archivo Excel
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivo);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();

            // Validar que hay datos (desde fila 11)
            $hayCotizaciones = false;
            for ($row = 11; $row <= $highestRow; $row++) {
                $cotizacion = $worksheet->getCell('C' . $row)->getValue();
                if (!empty($cotizacion) && is_numeric($cotizacion)) {
                    $hayCotizaciones = true;
                    break;
                }
            }

            if (!$hayCotizaciones) {
                return $this->json(['success' => false, 'message' => 'Planilla de cotizaciones vacía. Por favor, ingrese un archivo válido'], 400);
            }

            // Cargar productos con sus marcas para buscar por nombre + marca
            $productoModel = new Producto();
            $db = Database::getInstance();
            
            $sql = "SELECT p.id_producto, p.producto_nombre, m.marca_descripcion 
                    FROM producto p 
                    INNER JOIN marca m ON p.rela_marca = m.id_marca 
                    WHERE p.rela_estadoproducto != 4";
            $result = $db->query($sql);
            
            $productosPorNombreMarca = [];
            while ($prod = $result->fetch_assoc()) {
                // Crear clave compuesta: "nombre|marca"
                $nombreNormalizado = strtolower(trim($prod['producto_nombre']));
                $marcaNormalizada = strtolower(trim($prod['marca_descripcion']));
                $clave = $nombreNormalizado . '|' . $marcaNormalizada;
                $productosPorNombreMarca[$clave] = $prod['id_producto'];
                
                // También guardar solo por nombre para fallback
                if (!isset($productosPorNombreMarca[$nombreNormalizado])) {
                    $productosPorNombreMarca[$nombreNormalizado] = $prod['id_producto'];
                }
            }

            $this->modelo->beginTransaction();

            $cotizacionesCreadas = 0;
            $errores = [];
            $filasProcesadas = 0;

            // Procesar desde fila 11
            for ($row = 11; $row <= $highestRow; $row++) {
                // Obtener nombre del producto (columna A)
                $nombreProducto = trim($worksheet->getCell('A' . $row)->getValue());
                
                // Si no hay nombre de producto en columna A, detener el procesamiento (fin de la lista)
                if (empty($nombreProducto)) {
                    break;
                }
                
                $filasProcesadas++;
                
                // Obtener marca (columna B) y cotización (columna C)
                $nombreMarca = trim($worksheet->getCell('B' . $row)->getValue());
                $cotizacion = $worksheet->getCell('C' . $row)->getValue();
                
                // Si la columna C está vacía, saltar esta fila (producto sin cotizar)
                if (empty($cotizacion)) {
                    continue;
                }

                // Buscar producto por nombre + marca primero
                $productoId = null;
                if (!empty($nombreMarca)) {
                    $nombreNormalizado = strtolower($nombreProducto);
                    $marcaNormalizada = strtolower($nombreMarca);
                    $clave = $nombreNormalizado . '|' . $marcaNormalizada;
                    $productoId = $productosPorNombreMarca[$clave] ?? null;
                    
                    if (!$productoId) {
                        $errores[] = "Fila $row: Producto no encontrado - $nombreProducto (Marca: $nombreMarca)";
                        continue;
                    }
                } else {
                    // Si no hay marca, buscar solo por nombre
                    $nombreNormalizado = strtolower($nombreProducto);
                    $productoId = $productosPorNombreMarca[$nombreNormalizado] ?? null;
                    
                    if (!$productoId) {
                        $errores[] = "Fila $row: Producto no encontrado - $nombreProducto (sin marca especificada)";
                        continue;
                    }
                }

                // Validar monto
                if (!is_numeric($cotizacion) || $cotizacion <= 0) {
                    $errores[] = "Fila $row: Monto inválido";
                    continue;
                }

                // Crear cotización
                $data = [
                    'rela_proveedor' => $proveedorId,
                    'rela_producto' => $productoId,
                    'cotizacion_fechahora' => date('Y-m-d H:i:s'),
                    'cotizacion_monto' => (float) $cotizacion,
                    'cotizacion_estado' => 1
                ];

                $id = $this->modelo->create($data);
                if ($id) {
                    $cotizacionesCreadas++;
                } else {
                    $errores[] = "Fila $row: Error al guardar cotización";
                }
            }

            $this->modelo->commit();

            // Construir mensaje de respuesta
            $mensaje = "Se importaron <strong>$cotizacionesCreadas</strong> cotizaciones correctamente.";
            
            if (!empty($errores)) {
                $mensaje .= "<br><br><strong>Errores:</strong>";
                $mensaje .= "<div style='max-height: 200px; overflow-y: auto; overflow-x: auto; border: 1px solid #ddd; border-radius: 4px; padding: 10px; margin-top: 10px; background-color: #f8f9fa;'>";
                $mensaje .= "<ul class='text-start mb-0' style='white-space: nowrap;'>";
                foreach ($errores as $error) {
                    $mensaje .= "<li class='small'>$error</li>";
                }
                $mensaje .= "</ul>";
                $mensaje .= "</div>";
            }

            return $this->json([
                'success' => true,
                'message' => $mensaje,
                'cotizaciones_creadas' => $cotizacionesCreadas,
                'filas_procesadas' => $filasProcesadas,
                'errores_count' => count($errores),
                'errores_detalle' => $errores
            ]);

        } catch (\Exception $e) {
            $this->modelo->rollback();
            error_log("Error al importar cotizaciones: " . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }
}
